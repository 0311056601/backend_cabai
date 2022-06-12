<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRequestData extends Model
{
    use HasFactory;

    protected $table = 'log_request_data';

    public function getRequestData() {
        return $this->hasOne('App\Models\RequestData', 'id', 'id_request_data');
    }
}
