<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    protected $table = "banners";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
