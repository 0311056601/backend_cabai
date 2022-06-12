<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    public function getPenerima() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    
    public function getProfile() {
        return $this->hasOne('App\Models\Profile', 'user_id', 'user_id');
    }
}
