<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Mail_merge extends Model
{
    protected $table = "mail_merge";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
