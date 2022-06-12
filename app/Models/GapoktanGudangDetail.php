<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapoktanGudangDetail extends Model
{
    use HasFactory;

    protected $table = 'gapoktan_gudang_detail';

    public function getGudang() {
        return $this->hasOne('App\Models\GapoktanGudang', 'id', 'gapoktan_gudang_id');
    }

    public function getPetani() {
        return $this->hasOne('App\Models\User', 'id', 'petani_id');
    }

    public function getGapoktan() {
        return $this->hasOne('App\Models\User', 'id', 'gapoktan_id');
    }

    public function getKonsumen() {
        return $this->hasOne('App\Models\User', 'id', 'konsumen_id');
    }
}
