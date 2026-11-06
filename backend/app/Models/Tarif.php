<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarif extends Model
{
    protected $table = 'tarif';
    protected $primaryKey = 'id_tarif';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir',
        'jenis_kendaraan',
        'tipe_tarif',
        'tarif_per_jam',
        'tarif_jam_pertama',
        'tarif_berikutnya',
        'tarif_inap',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tarif_per_jam'      => 'decimal:2',
            'tarif_jam_pertama'  => 'decimal:2',
            'tarif_berikutnya'   => 'decimal:2',
            'tarif_inap'         => 'decimal:2',
            'status_aktif'       => 'integer',
        ];
    }

    // ── Relations ──

    public function tempatParkir(): BelongsTo
    {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_tarif', 'id_tarif');
    }
}

