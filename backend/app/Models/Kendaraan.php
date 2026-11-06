<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';
    protected $primaryKey = 'id_kendaraan';
    public $timestamps = false;

    protected $fillable = [
        'plat_nomor',
        'jenis_kendaraan',
        'warna',
        'id_pengendara',
        'id_user_input',
    ];

    // ── Relations ──

    public function pengendara(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengendara', 'id_pengendara');
    }

    public function userInput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_input', 'id_user');
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function pengajuanBanned(): HasMany
    {
        return $this->hasMany(PengajuanBanned::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function pengajuanKlaim(): HasMany
    {
        return $this->hasMany(PengajuanKlaimKendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }

    // ── Helpers ──

    public function hasTransactionHistory(): bool
    {
        return $this->transaksi()->exists();
    }
}

