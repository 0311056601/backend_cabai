<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;

    protected $table = 'saldo';

    public function getDetail() {
        return $this->hasOne('App\Models\SaldoDetail', 'saldo_id', 'id');
    }
}
