<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketLangganan extends Model
{
    use HasFactory;

    protected $table = 'paket_langganan';
    protected $primaryKey = 'id_paket';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir', 'nama_paket', 'jenis_kendaraan', 
        'durasi_hari', 'batas_masuk_harian', 'harga_paket', 'status_aktif'
    ];

    public function tempatParkir() { return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir'); }
    public function langgananMember() { return $this->hasMany(LanggananMember::class, 'id_paket'); }
}
