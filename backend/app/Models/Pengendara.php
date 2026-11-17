<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengendara extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengendara';
    protected $primaryKey = 'id_pengendara';
    public $timestamps = false;

    protected $fillable = [
        'nama_lengkap', 'email', 'no_telepon', 'username', 'password', 'status_aktif'
    ];

    protected $hidden = ['password'];

    public function kendaraans() { return $this->hasMany(Kendaraan::class, 'id_pengendara'); }
}