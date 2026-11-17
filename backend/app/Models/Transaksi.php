<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function kendaraan() { return $this->belongsTo(Kendaraan::class, 'id_kendaraan'); }
    public function tarif() { return $this->belongsTo(Tarif::class, 'id_tarif'); }
    public function petugasKeluar() { return $this->belongsTo(User::class, 'id_petugas_keluar'); }
}