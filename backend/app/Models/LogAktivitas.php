<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir',
        'id_user',
        'aktivitas',
        'waktu_aktivitas',
    ];

    protected function casts(): array
    {
        return [
            'waktu_aktivitas' => 'datetime',
        ];
    }

    // ── Relations ──

    public function tempatParkir(): BelongsTo
    {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir', 'id_tempat_parkir');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // ── Static Helper ──

    /**
     * Record an activity log entry.
     */
    public static function catat(int $idUser, string $aktivitas, ?int $idTempatParkir = null): self
    {
        return self::create([
            'id_tempat_parkir' => $idTempatParkir,
            'id_user'          => $idUser,
            'aktivitas'        => $aktivitas,
            'waktu_aktivitas'  => now(),
        ]);
    }
}

