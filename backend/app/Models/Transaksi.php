<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_booking',
        'id_kendaraan',
        'id_tempat_parkir',
        'id_tarif',
        'id_pengendara',
        'id_petugas',
        'waktu_masuk',
        'waktu_keluar',
        'durasi_jam',
        'biaya_total',
        'status_pembayaran',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'waktu_masuk'   => 'datetime',
            'waktu_keluar'  => 'datetime',
            'durasi_jam'    => 'integer',
            'biaya_total'   => 'decimal:2',
        ];
    }

    // ── Relations ──

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function tempatParkir(): BelongsTo
    {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class, 'id_tarif', 'id_tarif');
    }

    public function pengendara(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengendara', 'id_pengendara');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_petugas', 'id_user');
    }

    // ── Helpers ──

    /**
     * Calculate total fee using the MySQL stored function fn_hitung_biaya_parkir.
     */
    public function hitungBiaya(): ?float
    {
        if (!$this->waktu_keluar || !$this->id_tarif) {
            return null;
        }

        $result = DB::selectOne(
            'SELECT fn_hitung_biaya_parkir(?, ?, ?) AS biaya',
            [$this->id_tarif, $this->waktu_masuk, $this->waktu_keluar]
        );

        return $result ? (float) $result->biaya : null;
    }
}

