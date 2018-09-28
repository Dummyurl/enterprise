<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Chart_data extends Model
{
    protected $table = "chart_data";
    public $timestamps = false;
    public $primaryKey  = 'data_id';
}
