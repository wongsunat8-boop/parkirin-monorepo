<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir', 'id_user', 'aktivitas', 'waktu_aktivitas'
    ];

    protected $casts = [
        'waktu_aktivitas' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (LogAktivitas $log) {
            if (empty($log->waktu_aktivitas)) {
                $log->waktu_aktivitas = now();
            }
        });
    }

    public function user() 
    { 
        return $this->belongsTo(User::class, 'id_user'); 
    }

    public function tempatParkir() 
    { 
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir'); 
    }
}
