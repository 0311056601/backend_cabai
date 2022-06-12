<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukSiapJual extends Model
{
    use HasFactory;

    protected $table = 'produk_siap_jual';

    public function getDetail() {
        return $this->hasMany('App\Models\ProdukSiapJualDetail', 'produk_siap_jual', 'id');
    }

    public function getImg() {
        return $this->hasMany('App\Models\ProdukSiapJualImage', 'produk_siap_jual', 'id');
    }
}