<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Interest_reply extends Model
{
    protected $table = "employer_interest_reply";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
