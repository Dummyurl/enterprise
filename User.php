<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use App\Model\Profile_views;
use App\Model\Cv_downloads;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function personal_details(){
        return $this->hasOne('App\Model\Personal_details','user_id_fk','id');
    }
    public function academic(){
        return $this->hasMany('App\Model\Academic_details','user_id_fk','id');
    }
    public function career_history(){
        return $this->hasMany('App\Model\Career_history','user_id_fk','id');
    }
    public function career_details(){
        return $this->hasOne('App\Model\Career_history','user_id_fk','id');
    }
    public function certificates(){
        return $this->hasMany('App\Model\Job_seeker_certificate','user_id_fk','id');
    }
    public function js_cover_letter(){
        return $this->hasOne('App\Model\Cover_letter','user_id_fk','id');
    }
    public function cvs(){
        return $this->hasOne('App\Model\Cv','user_id_fk','id');
    }
    public function cvss(){
        return $this->hasMany('App\Model\Cv','user_id_fk','id');
    }
    public function job_preference(){
        return $this->hasOne('App\Model\Job_preference','user_id_fk','id');
    }
    public function projects(){
        return $this->hasMany('App\Model\Project','user_id_fk','id');
    }
    public function seminar_detail(){
        return $this->hasMany('App\Model\Seminar_details','user_id_fk','id');
    }
    public function js_technical(){
        return $this->hasMany('App\Model\Job_seeker_technical_skills','user_id_fk','id');
    }
    public function last_login(){
        return $this->hasMany('App\Model\Last_login','user_id_fk','id')->orderBy('created_at','DESC');
    }
    public function applied(){
        return $this->hasMany('App\Model\Applied_job','user_id_fk','id');
    }
    public function viewd(){
        return $this->hasMany('App\Model\Profile_views','job_seeker_id','id');
    }
    public function downloaded(){
        return $this->hasMany('App\Model\Cv_downloads','job_seeker_id','id');
    }
    public function userpackages(){
        return $this->hasMany('App\Model\User_package','user_id_fk','id');
    }
    public function user_active_packages(){
        return $this->hasMany('App\Model\User_package','user_id_fk','id')->where('status',2)->where('expiry_date','>=',date('Y-m-d'));
    }
    public function employersearches(){
        return $this->hasMany('App\Model\Cv_search','user_id_fk','id');
    }
    public function resume_view_history(){
        return $this->hasMany('App\Model\Profile_views','job_seeker_id','id');
    }
    public function resume_download_history(){
        return $this->hasMany('App\Model\Cv_downloads','job_seeker_id','id');
    }
    public function employer_details(){
        return $this->hasOne('App\Model\Employer','user_id_fk','id');
    }
    public function jobs_posted(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','id')
                    ->where('type',1)
                    ->orderBy('created_at','DESC');
    }
    public function regular_jobs_posted(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','id')
                    ->where('type',1)->where('jp_type',3)
                    ->orderBy('created_at','DESC');
    }
    public function enterprise_jobs_posted(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','id')
                    ->where('type',1)->where('jp_type',2)
                    ->orderBy('created_at','DESC');
    }
    public function jobs_posted_active(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','id')->where('type',1)->where('status',1) 
                    ->where('job_expire', '>=', date('Y-m-d'))
                    ->orderBy('created_at','DESC');
    }
    public function jobs_posted_inactive(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','id')->where('type',1)->where('status',2)
                    ->where('job_expire', '<', date('Y-m-d H:i:s'))
                    ->orderBy('created_at','DESC');
    }
    public function jobs_posted_deleted(){
        return $this->hasMany('App\Model\Job_post','user_id_fk','id')->where('type',1)
                    ->where('status',2)  
                    ->where('job_expire', '>', date('Y-m-d H:i:s'))                 
                    ->orderBy('created_at','DESC');
    }
    public function cv_comments(){
        return $this->hasMany('App\Model\Jobseeker_details_comments','jobseeker_id_fk','id');
    }
    public function microsite_details(){
        return $this->hasOne('App\Model\Microsite_details','user_id_fk','id');
    }
    public function cvs_downloaded(){
        return $this->hasMany('App\Model\Cv_downloads','user_id_fk','id');
    }
    public function profiles_viewed(){
        return $this->hasMany('App\Model\Profile_views','user_id_fk','id');
    }
    public function job_seeker(){
        return $this->hasOne('App\Model\Job_seeker_personal_details','user_id_fk','id');
    }
    public function email_sent(){
        return $this->hasMany('App\Model\Emails_sent','user_id_fk','id');
    }
    public function user_expired_packages(){
        return $this->hasMany('App\Model\User_package','user_id_fk','id')->where('status',3)->orderBy('activated_at','DESC');
    }
    public function job_seeker_skills(){
        return $this->hasMany('App\Model\Job_seeker_technical_skills','user_id_fk','id');
    }
}
