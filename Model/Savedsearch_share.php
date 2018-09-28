<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Savedsearch_share extends Model
{
    protected $table = "savedsearch_share";
    public $timestamps = true;
    public $primaryKey  = 'share_id';
}
