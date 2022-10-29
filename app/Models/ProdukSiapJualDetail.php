<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukSiapJualDetail extends Model
{
    use HasFactory;

    protected $table = 'produk_siap_jual_detail';

    public function getPetani() {
        return $this->hasOne('App\Models\User', 'id', 'petani');
    }

    public function produkPetani() {
        return $this->hasOne('App\Models\ProdukPetani', 'id', 'produk_petani');
    }

    public function ProdukSiapJual() {
        return $this->hasOne('App\Models\ProdukSiapJual', 'id', 'produk_siap_jual');
    }

    public function ProdukSiapJualImage() {
        return $this->hasMany('App\Models\ProdukSiapJualImage', 'produk_siap_jual', 'produk_siap_jual');
    }
}
