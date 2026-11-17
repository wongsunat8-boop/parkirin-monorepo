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
        'nama_tempat', 'kapasitas_reguler', 'kuota_inap', 
        'sediakan_booking_inap', 'sediakan_member', 'qris_image', 'status_aktif'
    ];

    public function users() { return $this->hasMany(User::class, 'id_tempat_parkir'); }
    public function tarifs() { return $this->hasMany(Tarif::class, 'id_tempat_parkir'); }
    public function paketLangganans() { return $this->hasMany(PaketLangganan::class, 'id_tempat_parkir'); }
}