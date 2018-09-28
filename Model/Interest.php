<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $table = "employer_interest";
    public $timestamps = false;
    public $primaryKey  = 'interest_id';
}
