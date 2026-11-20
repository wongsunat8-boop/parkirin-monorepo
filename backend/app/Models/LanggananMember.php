<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanggananMember extends Model
{
    use HasFactory;

    protected $table = 'langganan_member';
    protected $primaryKey = 'id_langganan';
    public $timestamps = false;

    protected $fillable = [
        'kode_member', 'id_tempat_parkir', 'id_paket', 'id_pengendara', 'id_kendaraan', 
        'id_user_verifikasi', 'tanggal_mulai', 'biaya_langganan', 'bukti_bayar', 
        'waktu_unggah_bukti', 'status_pembayaran', 'status_langganan', 'waktu_registrasi'
    ];

    public function tempatParkir() { return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir'); }
    public function paket() { return $this->belongsTo(PaketLangganan::class, 'id_paket'); }
    public function pengendara() { return $this->belongsTo(Pengendara::class, 'id_pengendara'); }
    public function kendaraan() { return $this->belongsTo(Kendaraan::class, 'id_kendaraan'); }
    public function userVerifikasi() { return $this->belongsTo(User::class, 'id_user_verifikasi'); }
}
