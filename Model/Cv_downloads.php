<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cv_downloads extends Model
{
    protected $table = "cv_downloads";
    public $timestamps = false;
    public $primaryKey  = 'download_id';
}
