<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Top_Employer extends Model
{
    protected $table = "top_employer";
    public $timestamps = false;
    public $primaryKey  = 'id';
}
