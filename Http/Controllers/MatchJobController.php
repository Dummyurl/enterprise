<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use Mail;
use App\User;
use App\Model\Countries;
use App\Model\States;
use App\Model\Cities;
use App\Model\IndustryType;
use App\Model\SubIndustryType;
use App\Model\Employer;
use App\Model\Interest;
use App\Model\Last_login;
use App\Model\User_package;
use App\Model\Cv_search;
use App\Model\Cv_search_details;
use App\Model\Cv_search_techskills;
use App\Model\Job_seeker_technical_skills;
use App\Model\Job_seeker_cover_letter;
use App\Model\Job_seeker_personal_details;
use App\Model\Job_preference;
use App\Model\job_seeker_cv;
use App\Model\Job_post;
use App\Model\Job_post_keyskills;
use App\Model\Language;
use App\Model\Applied_job;
use App\Model\Folder;
use App\Model\Top_Employer;
use App\Model\Microsite_details;
use App\Model\Microsite_locations;
use App\Model\Folder_move;
use App\Model\Jobseeker_details_comments;
use App\Model\Cvsearch_save;
use App\Model\Course;
use App\Model\Specialization;
use App\Model\PGCourse;
use App\Model\PGSpecialization;
use Carbon\Carbon;
use DB;
//use Maatwebsite\Excel\Facades\Excel;


class MatchJobController extends Controller{
    public function job_alerts_efm(){
        $now_time = Carbon::now();
        $before_15mins = Carbon::now()->subMinutes(30);
        $rawQry = "job_alert=2 and created_at between '".$before_15mins."' and  '".$now_time."'";
        //echo $rawQry;
        $jobs = Job_post::whereRaw($rawQry)->get();
        foreach($jobs as $job){
            $this->generate_alert($job->job_id);
        }
    }
    public function generate_alert($job_id){
      $job_data = Job_post::where('job_id',$job_id)->first();
      if(!empty($job_data)>0){
                $jsids = array();
                //$job_title[0]['jp_type'] == 1
                $title = $job_data->job_title;
                $farea = $job_data->functional_area;
                $itype = $job_data->industry_type;
                $visaval = $job_data->visa_status;
                $genderval = $job_data->gender;
                if(!empty($title)){
                    $jobseekers = Job_seeker_technical_skills::where('skill','like','%'.$title.'%')->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($farea)){
                    $jobseekers = Job_preference::where('preferred_job_function',$farea)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($itype)){
                    $jobseekers = Job_preference::where('preferred_industry_type',$itype)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($visaval)){
                    $jobseekers = Job_seeker_personal_details::where('current_visa_status',$visaval)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                //print_r($jsids);
                /*$data = User::whereIn('id',$jsids)->get();
                return view('email/js_details',compact('data'));*/
                $profilematches = User::whereIn('id',$jsids)->get();
                $job_url = url('job-detail/'.$job_id);
                $job_user_info = User::where('id',$job_data->user_id_fk)->first();
            foreach($profilematches as $match){
                    $mail_data = array(
                         'email' => $match->email,
                         'url' => $job_url
                     );
                Mail::send('email.jpost_matched', $mail_data, function ($message) use ($mail_data) {
                             $message->subject('Job alert')
                                     ->from('developer10@indglobal-consulting.com')
                                     ->bcc("dev85@indglobal-consulting.com")
                                     ->to($mail_data['email']);
                });
            }
      }
    }
}