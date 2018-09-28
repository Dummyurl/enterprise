<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IndustryType extends Model
{
    protected $table = "industry_type";
    public $timestamps = false;
    public $primaryKey  = 'industry_type_id';

    public function sub_industry_value()
    {
        return $this->hasMany('App\Model\SubIndustryType','industry_type_id_fk','industry_type_id');
    }
}
