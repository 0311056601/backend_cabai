<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukPetani extends Model
{
    use HasFactory;

    protected $table = 'produk_petani';

    public function getImage() {
        return $this->hasMany('App\Models\ProdukPetaniImg', 'produk_id', 'id');
    }

    public function getCreator() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
