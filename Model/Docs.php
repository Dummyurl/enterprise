<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Docs extends Model
{
    protected $table = "documents";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
