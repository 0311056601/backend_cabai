<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestData extends Model
{
    use HasFactory;

    protected $table = 'request_data';

    public function getManyGapoktan() {
        return $this->hasMany('App\Models\User', 'gapoktan', 'gapoktan');
    }

    public function getGapoktan() {
        return $this->hasOne('App\Models\User', 'id', 'gapoktan');
    }

}
