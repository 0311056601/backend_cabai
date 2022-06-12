<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestProduk extends Model
{
    use HasFactory;

    protected $table = 'request_produk';

    public function getKonsumen() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
