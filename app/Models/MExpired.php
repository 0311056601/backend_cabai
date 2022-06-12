<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MExpired extends Model
{
    use HasFactory;

    protected $table = 'm_expired';

    public function getGapoktan() {
        return $this->hasOne('App\Models\User', 'id', 'gapoktan_id');
    }
}
