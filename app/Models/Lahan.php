<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    use HasFactory;

    protected $table = 'lahan';

    public function getUser() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getImg() {
        return $this->hasMany('App\Models\LahanImg', 'lahan_id', 'id');
    }
}
