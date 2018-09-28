<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Profile_views extends Model
{
    protected $table = "profile_views";
    public $timestamps = false;
    public $primaryKey  = 'profileview_id';
}
