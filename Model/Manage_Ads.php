<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Manage_Ads extends Model
{
    protected $table = "manage_ads";
    public $timestamps = true;
    public $primaryKey  = 'ad_id';
}
