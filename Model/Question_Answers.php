<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Question_Answers extends Model
{
    protected $table = "faq_qna";
    public $timestamps = true;
    public $primaryKey  = 'id';
}
