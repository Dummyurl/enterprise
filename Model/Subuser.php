<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Subuser extends Model
{
    protected $table = "sub_user";
    public $timestamps = false;
    public $primaryKey  = 'sub_user_id';
}
