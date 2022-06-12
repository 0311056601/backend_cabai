<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapoktanGudangExpiredDetail extends Model
{
    use HasFactory;

    protected $table = 'gapoktan_gudang_expired_detail';

    public function getGapoktan() {
        return $this->hasOne('App\Models\User', 'id', 'gapoktan_id');
    }

    public function getExpired() {
        return $this->hasOne('App\Models\GapoktanGudangExpired', 'id', 'gapoktan_gudang_expired_id');
    }

    public function getGudang() {
        return $this->hasOne('App\Models\GapoktanGudang', 'id', 'gudang_id');
    }

    public function getPetani() {
        return $this->hasOne('App\Models\User', 'id', 'petani');
    }
}
