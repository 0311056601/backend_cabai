<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profile';

    public function getUser() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
