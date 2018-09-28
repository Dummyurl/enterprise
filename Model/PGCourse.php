<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PGCourse extends Model
{
    protected $table = "pgcourse";
    public $timestamps = false;
    public $primaryKey  = 'pgc_id';

    public function pgspecialization_value()
    {
        return $this->hasMany('App\Model\PGSpecialization','pgc_id_fk','pgc_id');
    }
}
