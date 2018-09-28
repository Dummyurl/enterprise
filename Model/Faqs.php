<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Faqs extends Model
{
    protected $table = "faqs";
    public $timestamps = true;
    public $primaryKey  = 'faq_id';
}
