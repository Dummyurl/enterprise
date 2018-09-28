<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Content_Contact extends Model
{
    protected $table = "content_contact";
    public $timestamps = true;
    public $primaryKey  = 'content_id';
}
