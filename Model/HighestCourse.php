<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HighestCourse extends Model
{
    protected $table = "highest_course";
    public $timestamps = true;
    public $primaryKey  = 'course_id';

    public function specialization_value()
    {
        return $this->hasMany('App\Model\HighestSpecialization','hc_id_fk','course_id');
    }
}
