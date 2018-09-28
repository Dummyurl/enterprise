<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Emails_sent extends Model
{
    protected $table = "emails_sent";
    public $timestamps = false;
    public $primaryKey  = 'sent_id';

    public function employer_details(){
        return $this->hasOne('App\Model\Employer','user_id_fk','user_id_fk');
    }
    public function user_details(){
        return $this->hasOne('App\Model\User','user_id','job_seeker_id_fk');
    }
}
