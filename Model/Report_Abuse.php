<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Report_Abuse extends Model
{
    protected $table = "report_abuse";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
