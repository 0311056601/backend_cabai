<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiCabai extends Model
{
    use HasFactory;

    protected $table = 'transaksi_cabai';

    public function getPembeli() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getKeranjang() {
        return $this->hasOne('App\Models\Keranjang', 'id', 'keranjang_id');
    }

    public function getProduk() {
        return $this->hasOne('App\Models\ProdukSiapJual', 'id', 'produk_id');
    }
}
