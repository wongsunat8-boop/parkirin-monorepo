<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $table = 'tarif';
    protected $primaryKey = 'id_tarif';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir', 'jenis_kendaraan', 'tipe_tarif', 
        'tarif_per_jam', 'tarif_jam_pertama', 'tarif_berikutnya', 
        'tarif_inap_harian', 'denda_karcis_hilang', 'status_aktif'
    ];
}