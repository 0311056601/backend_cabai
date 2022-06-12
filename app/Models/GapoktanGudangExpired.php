<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapoktanGudangExpired extends Model
{
    use HasFactory;

    protected $table = 'gapoktan_gudang_expired';

    public function getGapoktan() {
        return $this->hasOne('App\Models\User', 'id', 'gapoktan_id');
    }

    public function getDetail() {
        return $this->hasMany('App\Models\GapoktanGudangExpiredDetail', 'gapoktan_gudang_expired_id', 'id');
    }

}
