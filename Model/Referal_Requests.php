<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Referal_Requests extends Model
{
    protected $table = "referal_requests";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
