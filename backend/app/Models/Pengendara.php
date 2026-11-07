<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Pengendara extends Authenticatable
{
    use HasApiTokens;
    protected $table = 'pengendara';
    protected $primaryKey = 'id_pengendara';
    public $timestamps = false;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'no_telepon',
        'username',
        'password',
        'skor_reputasi',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'skor_reputasi'  => 'integer',
            'status_aktif'   => 'integer',
        ];
    }

    // ── Relations ──

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'id_pengendara', 'id_pengendara');
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_pengendara', 'id_pengendara');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_pengendara', 'id_pengendara');
    }

    public function pengajuanBanned(): HasMany
    {
        return $this->hasMany(PengajuanBanned::class, 'id_pengendara', 'id_pengendara');
    }

    public function klaimDiajukan(): HasMany
    {
        return $this->hasMany(PengajuanKlaimKendaraan::class, 'id_pengaju', 'id_pengendara');
    }

    public function klaimDiterima(): HasMany
    {
        return $this->hasMany(PengajuanKlaimKendaraan::class, 'id_pengendara_target', 'id_pengendara');
    }
}

