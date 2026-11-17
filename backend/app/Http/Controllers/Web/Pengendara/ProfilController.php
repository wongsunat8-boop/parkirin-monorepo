<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    private function phoneRules(): array
    {
        return ['required', 'string', 'regex:/^08[0-9]{8,11}$/'];
    }

    private function sendOtp(string $phone, string $otp): void
    {
        $message = "Kode OTP Parkirin Anda: {$otp}. Berlaku 5 menit. Jangan bagikan kode ini.";
        $token = config('services.fonnte.token') ?: env('FONNTE_TOKEN');
        $endpoint = config('services.fonnte.endpoint') ?: env('FONNTE_ENDPOINT', 'https://api.fonnte.com/send');

        if ($token) {
            $target = preg_replace('/^0/', '62', $phone); // 08xx -> 628xx (lebih kompatibel)
            try {
                $response = Http::asForm()
                    ->withHeaders(['Authorization' => $token])
                    ->timeout(15)
                    ->acceptJson()
                    ->post($endpoint, [
                        'target' => $target,
                        'message' => $message,
                        'countryCode' => '62',
                    ]);
            } catch (\Throwable $e) {
                Log::warning('OTP Fonnte request error', ['error' => $e->getMessage(), 'phone' => $phone]);
                throw new \RuntimeException('Gagal menghubungi layanan pengirim OTP. Coba lagi beberapa saat.');
            }

            $json = $response->json();
            $ok = $response->successful();
            // Fonnte bisa HTTP 200 namun status:false (device disconnect, kuota habis, target invalid, dll)
            if (is_array($json) && array_key_exists('status', $json)) {
                $ok = $ok && filter_var($json['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
            }

            if ($ok) {
                Log::info('OTP Fonnte accepted', ['phone' => $phone, 'id' => $json['id'] ?? null]);
                return;
            }

            $reason = '';
            if (is_array($json)) {
                $reason = (string) ($json['reason'] ?? $json['detail'] ?? $json['message'] ?? '');
            }

            // Jika token ditolak karena 'invalid token', cek apakah token yang diinput adalah Account Token Fonnte
            // Jika ya, coba resolve otomatis Device Token dari API Fonnte get-devices
            if (strtolower(trim($reason)) === 'invalid token') {
                try {
                    $devRes = Http::withHeaders(['Authorization' => $token])
                        ->timeout(10)
                        ->acceptJson()
                        ->post('https://api.fonnte.com/get-devices');

                    $devices = $devRes->json('data') ?? [];
                    $resolvedToken = null;
                    if (is_array($devices)) {
                        foreach ($devices as $dev) {
                            if (!empty($dev['token']) && ($dev['status'] ?? '') === 'connect') {
                                $resolvedToken = $dev['token'];
                                break;
                            }
                        }
                        if (!$resolvedToken && !empty($devices[0]['token'])) {
                            $resolvedToken = $devices[0]['token'];
                        }
                    }

                    if ($resolvedToken) {
                        $retry = Http::asForm()
                            ->withHeaders(['Authorization' => $resolvedToken])
                            ->timeout(15)
                            ->acceptJson()
                            ->post($endpoint, [
                                'target' => $target,
                                'message' => $message,
                                'countryCode' => '62',
                            ]);

                        $retryJson = $retry->json();
                        $retryOk = $retry->successful();
                        if (is_array($retryJson) && array_key_exists('status', $retryJson)) {
                            $retryOk = $retryOk && filter_var($retryJson['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
                        }

                        if ($retryOk) {
                            Log::info('OTP Fonnte accepted via auto-resolved device token', ['phone' => $phone]);
                            return;
                        }
                    }
                } catch (\Throwable $e) {
                    // Lanjut ke penanganan error standar jika fallback gagal
                }
            }

            Log::warning('OTP Fonnte rejected', ['http' => $response->status(), 'body' => $response->body(), 'phone' => $phone]);
            $hint = $reason !== '' ? ' Alasan: ' . $reason . '.' : '';
            throw new \RuntimeException('OTP gagal dikirim ke nomor tujuan.' . $hint . ' Pastikan token Fonnte valid, device terhubung, dan kuota tersedia.');
        }

        // Fallback provider generik (webhook SMS gateway internal)
        $webhook = config('services.otp.webhook') ?: env('OTP_WEBHOOK_URL');
        $webhookToken = config('services.otp.token') ?: env('OTP_WEBHOOK_TOKEN');
        if ($webhook) {
            try {
                $req = Http::timeout(15)->acceptJson();
                if ($webhookToken) $req = $req->withToken($webhookToken);
                $response = $req->post($webhook, ['phone' => $phone, 'message' => $message, 'otp' => $otp]);
            } catch (\Throwable $e) {
                Log::warning('OTP webhook request error', ['error' => $e->getMessage(), 'phone' => $phone]);
                throw new \RuntimeException('Gagal menghubungi layanan pengirim OTP. Coba lagi beberapa saat.');
            }

            $json = $response->json();
            $ok = $response->successful();
            if (is_array($json)) {
                foreach (['status', 'success', 'ok'] as $k) {
                    if (array_key_exists($k, $json)) {
                        $v = $json[$k];
                        $ok = $ok && ($v === true || $v === 1 || $v === 'true' || $v === 'success' || $v === 'ok');
                        break;
                    }
                }
            }
            if ($ok) {
                Log::info('OTP webhook accepted', ['phone' => $phone]);
                return;
            }
            Log::warning('OTP webhook rejected', ['http' => $response->status(), 'body' => $response->body(), 'phone' => $phone]);
            $reason = is_array($json) ? (string) ($json['reason'] ?? $json['detail'] ?? $json['message'] ?? '') : '';
            throw new \RuntimeException('OTP gagal dikirim ke nomor tujuan.' . ($reason ? ' Alasan: ' . $reason . '.' : ' Periksa konfigurasi layanan OTP.'));
        }

        Log::error('OTP tidak terkirim: layanan OTP belum dikonfigurasi (FONNTE_TOKEN/OTP_WEBHOOK_URL).', ['phone' => $phone]);
        throw new \RuntimeException('Layanan pengiriman OTP belum dikonfigurasi. Hubungi administrator (set FONNTE_TOKEN atau OTP_WEBHOOK_URL di file .env, lalu jalankan php artisan config:clear).');
    }
    public function index(): View
    {
        return view('pengendara.profil.index');
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100', Rule::unique('pengendara', 'email')->ignore($pengendara->id_pengendara, 'id_pengendara')],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('pengendara', 'username')->ignore($pengendara->id_pengendara, 'id_pengendara')],
        ]);
        $pengendara->update($validated);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (!$pengendara->password || !Hash::check($validated['password_lama'], $pengendara->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
        }
        $pengendara->update(['password' => Hash::make($validated['password'])]);
        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function kirimOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_telepon' => $this->phoneRules(),
        ], [
            'no_telepon.regex' => 'Format nomor telepon tidak valid. Gunakan awalan 08 dan panjang 10-13 digit (contoh: 081234567890).',
        ]);
        $otp = (string) random_int(100000, 999999);
        // Pastikan OTP lama tidak bisa dipakai jika pengiriman baru gagal
        session()->forget(['profil_otp_hash', 'profil_otp_phone', 'profil_otp_expires']);

        try {
            $this->sendOtp($validated['no_telepon'], $otp);
        } catch (\Throwable $e) {
            Log::warning('OTP send failed (controller)', ['phone' => $validated['no_telepon'], 'error' => $e->getMessage()]);
            return back()->withErrors(['no_telepon' => $e->getMessage()])->withInput();
        }

        session([
            'profil_otp_hash' => Hash::make($otp),
            'profil_otp_phone' => $validated['no_telepon'],
            'profil_otp_expires' => now()->addMinutes(5)->timestamp,
        ]);
        return back()->with('success', 'Kode OTP berhasil dikirim ke ' . $validated['no_telepon'] . '. Periksa pesan masuk, lalu masukkan kode verifikasi.');
    }

    public function verifikasiTelepon(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        $validated = $request->validate([
            'no_telepon' => $this->phoneRules(),
            'kode_otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ], [
            'no_telepon.regex' => 'Format nomor telepon tidak valid. Gunakan awalan 08 dan panjang 10-13 digit.',
            'kode_otp.regex' => 'Kode OTP harus 6 digit angka.',
        ]);
        $otpHash = session('profil_otp_hash');
        $phone = session('profil_otp_phone');
        $expires = session('profil_otp_expires');

        if (!$otpHash || !$phone || !$expires || $phone !== $validated['no_telepon']) {
            return back()->withErrors(['kode_otp' => 'Silakan kirim OTP terlebih dahulu untuk nomor ini.'])->withInput();
        }
        if (now()->timestamp > $expires) {
            session()->forget(['profil_otp_hash', 'profil_otp_phone', 'profil_otp_expires']);
            return back()->withErrors(['kode_otp' => 'Kode OTP kedaluwarsa. Kirim ulang OTP.'])->withInput();
        }
        if (!Hash::check($validated['kode_otp'], $otpHash)) {
            return back()->withErrors(['kode_otp' => 'Kode OTP tidak valid.'])->withInput();
        }

        $pengendara->update(['no_telepon' => $validated['no_telepon']]);
        session()->forget(['profil_otp_hash', 'profil_otp_phone', 'profil_otp_expires']);
        return back()->with('success', 'Nomor telepon berhasil diverifikasi dan diperbarui.');
    }
}
