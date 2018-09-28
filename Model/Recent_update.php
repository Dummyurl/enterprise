<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Recent_update extends Model
{
    protected $table = "recent_update";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
