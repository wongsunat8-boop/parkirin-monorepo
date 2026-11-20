<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'kode_karcis', 'id_tempat_parkir', 'id_kendaraan', 'id_tarif', 'id_booking', 
        'id_langganan', 'id_petugas_masuk', 'id_petugas_keluar', 'waktu_masuk', 
        'waktu_keluar', 'biaya_dasar', 'denda_overstay', 'denda_karcis_hilang', 
        'identitas_pengambil', 'metode_pembayaran', 'status_pembayaran', 'status'
    ];

    protected $casts = [
        'waktu_masuk'          => 'datetime',
        'waktu_keluar'         => 'datetime',
        'biaya_dasar'          => 'decimal:2',
        'denda_overstay'       => 'decimal:2',
        'denda_karcis_hilang'  => 'decimal:2',
    ];

    public function tempatParkir(): BelongsTo
    {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan');
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class, 'id_tarif');
    }

    public function pengendara(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Pengendara::class,
            Kendaraan::class,
            'id_kendaraan',  // Foreign key on Kendaraan table (primary key)
            'id_pengendara', // Foreign key on Pengendara table (primary key)
            'id_kendaraan',  // Local key on Transaksi table
            'id_pengendara'  // Local key on Kendaraan table
        );
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_petugas_masuk');
    }

    public function getBiayaTotalAttribute(): float
    {
        return (float) ($this->biaya_dasar + $this->denda_overstay + $this->denda_karcis_hilang);
    }

    public function getDurasiJamAttribute(): int
    {
        if (!$this->waktu_masuk) return 0;
        $end = $this->waktu_keluar ? \Carbon\Carbon::parse($this->waktu_keluar) : now();
        $start = \Carbon\Carbon::parse($this->waktu_masuk);
        return max(1, (int) ceil($start->floatDiffInHours($end)));
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking');
    }

    public function langganan(): BelongsTo
    {
        return $this->belongsTo(LanggananMember::class, 'id_langganan');
    }

    public function petugasMasuk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_petugas_masuk');
    }

    public function petugasKeluar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_petugas_keluar');
    }
}
