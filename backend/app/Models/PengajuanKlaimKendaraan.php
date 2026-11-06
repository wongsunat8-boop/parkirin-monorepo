<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanKlaimKendaraan extends Model
{
    protected $table = 'pengajuan_klaim_kendaraan';
    protected $primaryKey = 'id_klaim';
    public $timestamps = false;

    protected $fillable = [
        'id_kendaraan',
        'id_pengaju',
        'id_pengendara_target',
        'status_klaim',
        'tanggal_pengajuan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
        ];
    }

    // ── Relations ──

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengaju', 'id_pengendara');
    }

    public function pengendaraTarget(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengendara_target', 'id_pengendara');
    }
}

