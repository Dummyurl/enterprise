<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Testimonials extends Model
{
    protected $table = "testimonials";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
