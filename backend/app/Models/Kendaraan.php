<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';
    protected $primaryKey = 'id_kendaraan';
    public $timestamps = false;

    protected $fillable = [
        'plat_nomor', 'jenis_kendaraan', 'warna', 'id_pengendara'
    ];

    public function pengendara() {
        return $this->belongsTo(Pengendara::class, 'id_pengendara');
    }
}