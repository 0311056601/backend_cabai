<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapoktanGudang extends Model
{
    use HasFactory;

    protected $table = 'gapoktan_gudang';

    public function getProduk() {
        return $this->hasOne('App\Models\ProdukPetani', 'id', 'produk_petani');
    }

    public function getGapoktan() {
        return $this->hasOne('App\Models\User', 'id', 'user_gapoktan');
    }

    public function getPetani() {
        return $this->hasOne('App\Models\User', 'id', 'petani');
    }
}
