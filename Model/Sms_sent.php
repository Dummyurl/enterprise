<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Sms_sent extends Model
{
    protected $table = "sms_sent";
    public $timestamps = false;
    public $primaryKey  = 'sent_id';

    public function employer_details(){
        return $this->hasOne('App\Model\Employer','user_id_fk','user_id_fk');
    }
    public function user_details(){
        return $this->hasOne('App\Model\User','user_id','job_seeker_id_fk');
    }
}
