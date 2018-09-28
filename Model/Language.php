<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = "languages";
    public $timestamps = false;
    public $primaryKey  = 'id';
}
