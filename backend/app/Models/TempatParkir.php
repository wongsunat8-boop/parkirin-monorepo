<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TempatParkir extends Model
{
    protected $table = 'tempat_parkir';
    protected $primaryKey = 'id_tempat_parkir';
    public $timestamps = false;

    protected $fillable = [
        'nama_tempat',
        'kapasitas',
        'terisi',
        'sediakan_booking',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas'        => 'integer',
            'terisi'           => 'integer',
            'sediakan_booking' => 'integer',
            'status_aktif'     => 'integer',
        ];
    }

    // ── Relations ──

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function tarif(): HasMany
    {
        return $this->hasMany(Tarif::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function pengajuanBanned(): HasMany
    {
        return $this->hasMany(PengajuanBanned::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    // ── Helpers ──

    public function isSlotAvailable(): bool
    {
        return $this->terisi < $this->kapasitas;
    }

    public function isBookingEnabled(): bool
    {
        return $this->sediakan_booking === 1 && $this->status_aktif === 1;
    }
}

