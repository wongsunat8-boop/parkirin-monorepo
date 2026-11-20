<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booking';
    protected $primaryKey = 'id_booking';
    public $timestamps = false;

    protected $fillable = [
        'kode_booking', 'id_tempat_parkir', 'id_pengendara', 'id_kendaraan', 
        'id_user_verifikasi', 'waktu_booking', 'waktu_mulai_inap', 
        'waktu_rencana_keluar', 'biaya_inap_dibayar', 'bukti_bayar', 
        'waktu_unggah_bukti', 'status_pembayaran', 'status'
    ];

    public function tempatParkir() { return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir'); }
    public function pengendara() { return $this->belongsTo(Pengendara::class, 'id_pengendara'); }
    public function kendaraan() { return $this->belongsTo(Kendaraan::class, 'id_kendaraan'); }
    public function transaksi() { return $this->hasOne(Transaksi::class, 'id_booking'); }
    public function userVerifikasi() { return $this->belongsTo(User::class, 'id_user_verifikasi'); }
}
