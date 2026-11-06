<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $table = 'booking';
    protected $primaryKey = 'id_booking';
    public $timestamps = false;

    protected $fillable = [
        'id_pengendara',
        'id_tempat_parkir',
        'id_kendaraan',
        'waktu_booking',
        'waktu_rencana_kedatangan',
        'kode_booking',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'waktu_booking'             => 'datetime',
            'waktu_rencana_kedatangan'  => 'datetime',
        ];
    }

    // ── Relations ──

    public function pengendara(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengendara', 'id_pengendara');
    }

    public function tempatParkir(): BelongsTo
    {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function transaksi(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'id_booking', 'id_booking');
    }

    // ── Helpers ──

    public function isExpired(): bool
    {
        return $this->status === 'Menunggu'
            && now()->greaterThan($this->waktu_rencana_kedatangan->addMinutes(10));
    }
}

