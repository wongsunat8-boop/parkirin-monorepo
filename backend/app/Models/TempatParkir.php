<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempatParkir extends Model
{
    use HasFactory;

    protected $table = 'tempat_parkir';
    protected $primaryKey = 'id_tempat_parkir';
    public $timestamps = false; 

    protected $fillable = [
        'nama_tempat', 'kode_qr_gate', 'qr_generated_at', 'kapasitas_reguler', 'kuota_inap', 
        'sediakan_booking_inap', 'sediakan_member', 'qris_image', 'status_aktif'
    ];

    /**
     * Check if tenant already has gate QR generated.
     */
    public function hasGateQr(): bool
    {
        return !empty($this->kode_qr_gate);
    }

    public function getKapasitasAttribute(): int
    {
        return (int) ($this->attributes['kapasitas_reguler'] ?? 0);
    }

    public function setKapasitasAttribute($value): void
    {
        $this->attributes['kapasitas_reguler'] = (int) $value;
    }

    public function getTerisiAttribute(): int
    {
        return Transaksi::where('id_tempat_parkir', $this->id_tempat_parkir)
            ->where('status', 'masuk')
            ->count();
    }

    public function getSediakanBookingAttribute(): bool
    {
        return (bool) ($this->attributes['sediakan_booking_inap'] ?? false);
    }

    public function setSediakanBookingAttribute($value): void
    {
        $this->attributes['sediakan_booking_inap'] = (int) $value;
    }

    /**
     * Generate gate QR code. Only allowed ONCE per tenant.
     */
    public function generateGateQr(): string
    {
        if ($this->hasGateQr()) {
            return $this->kode_qr_gate;
        }

        $code = 'PKR-GATE-' . str_pad((string)$this->id_tempat_parkir, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        $this->update([
            'kode_qr_gate'    => $code,
            'qr_generated_at' => now(),
        ]);

        return $code;
    }

    public function users() { return $this->hasMany(User::class, 'id_tempat_parkir'); }
    public function tarifs() { return $this->hasMany(Tarif::class, 'id_tempat_parkir'); }
    public function tarif() { return $this->hasMany(Tarif::class, 'id_tempat_parkir'); }
    public function transaksi() { return $this->hasMany(Transaksi::class, 'id_tempat_parkir'); }
    public function booking() { return $this->hasMany(Booking::class, 'id_tempat_parkir'); }
    public function langgananMember() { return $this->hasMany(LanggananMember::class, 'id_tempat_parkir'); }
    public function paketLangganans() { return $this->hasMany(PaketLangganan::class, 'id_tempat_parkir'); }
    public function logAktivitas() { return $this->hasMany(LogAktivitas::class, 'id_tempat_parkir'); }
}