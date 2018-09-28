<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = "course";
    public $timestamps = true;
    public $primaryKey  = 'course_id';

    public function specialization_value()
    {
        return $this->hasMany('App\Model\Specialization','course_id_fk','course_id');
    }
}

