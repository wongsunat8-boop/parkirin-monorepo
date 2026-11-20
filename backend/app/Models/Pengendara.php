<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengendara extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengendara';
    protected $primaryKey = 'id_pengendara';
    public $timestamps = false;

    protected $fillable = [
        'nama_lengkap', 'email', 'no_telepon', 'username', 'password', 'status_aktif'
    ];

    protected $hidden = ['password'];

    public function kendaraan(): HasMany 
    { 
        return $this->hasMany(Kendaraan::class, 'id_pengendara'); 
    }

    public function kendaraans(): HasMany 
    { 
        return $this->hasMany(Kendaraan::class, 'id_pengendara'); 
    }

    public function transaksi(): HasManyThrough
    {
        return $this->hasManyThrough(
            Transaksi::class,
            Kendaraan::class,
            'id_pengendara',
            'id_kendaraan',
            'id_pengendara',
            'id_kendaraan'
        );
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_pengendara');
    }

    public function langgananMember(): HasMany
    {
        return $this->hasMany(LanggananMember::class, 'id_pengendara');
    }
}
