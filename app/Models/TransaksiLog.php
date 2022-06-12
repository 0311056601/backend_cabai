<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiLog extends Model
{
    use HasFactory;

    protected $table = 'transaksi_log';

    // public function getProduk() {
    //     return $this->hasOne('App\Modes\ProdukSiapJual', 'id', 'produk_id');
    // }
}
