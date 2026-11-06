<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBanned extends Model
{
    protected $table = 'pengajuan_banned';
    protected $primaryKey = 'id_banned';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir',
        'id_pengaju',
        'id_pengendara',
        'id_kendaraan',
        'alasan',
        'status_pengajuan',
        'tanggal_pengajuan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
        ];
    }

    // ── Relations ──

    public function tempatParkir(): BelongsTo
    {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengaju', 'id_user');
    }

    public function pengendara(): BelongsTo
    {
        return $this->belongsTo(Pengendara::class, 'id_pengendara', 'id_pengendara');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }
}

