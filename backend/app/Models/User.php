<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'id_tempat_parkir', 'nama_lengkap', 'username', 'password', 'role', 'status_aktif'
    ];

    protected $hidden = ['password'];

    public function tempatParkir() {
        return $this->belongsTo(TempatParkir::class, 'id_tempat_parkir');
    }
}