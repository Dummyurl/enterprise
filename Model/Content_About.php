<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Content_About extends Model
{
    protected $table = "content_aboutus";
    public $timestamps = true;
    public $primaryKey  = 'content_id';
}
