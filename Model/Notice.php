<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = "notice";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
