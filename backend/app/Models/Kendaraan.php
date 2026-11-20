<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';
    protected $primaryKey = 'id_kendaraan';
    public $timestamps = false;

    protected $fillable = [
        'plat_nomor',
        'jenis_kendaraan',
        'warna',
        'id_pengendara',
    ];

    public function pengendara(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengendara');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_kendaraan');
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_kendaraan');
    }

    public function langgananMember(): HasMany
    {
        return $this->hasMany(LanggananMember::class, 'id_kendaraan');
    }

    public function hasTransactionHistory(): bool
    {
        return $this->transaksi()->exists();
    }

    /**
     * Format license plate according to Indonesian TNKB standards (Perpol No. 7 Tahun 2021):
     * [1-2 Letters Wilayah] [1-4 Digits Nomor] [1-3 Letters Seri]
     * Example: "B 6767 IYU", "DK 1234 XY", "AB 123 CD"
     */
    public static function formatPlatNomor(?string $plat): string
    {
        if (empty($plat)) {
            return '';
        }

        $clean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $plat)));

        // Match standard Indonesian TNKB format:
        // 1-2 letters (Wilayah), 1-4 digits (Nomor), 0-3 letters (Seri)
        if (preg_match('/^([A-Z]{1,2})(\d{1,4})([A-Z]{1,3})?$/', $clean, $matches)) {
            $wilayah = $matches[1];
            $nomor   = $matches[2];
            $seri    = $matches[3] ?? '';
            return trim("{$wilayah} {$nomor} {$seri}");
        }

        return strtoupper(trim(preg_replace('/\s+/', ' ', $plat)));
    }

    public function setPlatNomorAttribute($value): void
    {
        $this->attributes['plat_nomor'] = self::formatPlatNomor($value);
    }

    public function getPlatNomorAttribute($value): string
    {
        return self::formatPlatNomor($value ?? '');
    }

    /**
     * Backward-compatible fallback for legacy calls to userInput
     */
    public function userInput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengendara', 'id_user');
    }
}
