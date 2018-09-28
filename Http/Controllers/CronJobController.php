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
use DB;
//use Maatwebsite\Excel\Facades\Excel;


class CronJobController extends Controller{
    public function daily_alerts(){
        $this->generate_alert(3);
    }
    public function weekly_alerts(){
        $this->generate_alert(4);
    }
    public function generate_alert($alerttype){
      $savedsearches = Cvsearch_save::all();
      if(count($savedsearches)>0){
        foreach($savedsearches as $savedsearch){
            if(($savedsearch->alert_opt == 1) && ($savedsearch->cv_frequency == $alerttype)){
                $searchID = $savedsearch->search_id_fk;
                $search_data = Cv_search_details::where('search_id_fk', $searchID)->get();

                $jsids = array();
                //$job_title[0]['jp_type'] == 1
                $title = $search_data[0]['keyword'];
                $farea = $search_data[0]['farea'];
                $itype = $search_data[0]['industry'];
                $visaval = $search_data[0]['visa_status'];
                $locations = $search_data[0]['cur_loc'];
                $genderval = $search_data[0]['gender'];
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
                if(!empty($locations)){
                    $jobseekers = Job_seeker_personal_details::where('current_location',$locations)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($genderval)){
                    $jobseekers = Job_seeker_personal_details::where('gender',$genderval)->get();
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
                $mail_data = array(
                         'email' => $savedsearch->email,
                         'data' => $profilematches,
                     );
                Mail::send('email.js_details', $mail_data, function ($message) use ($mail_data) {
                             $message->subject('Matching Profiles for your search criteria')
                                     ->from('developer10@indglobal-consulting.com')
                                     ->bcc("dev85@indglobal-consulting.com")
                                     ->to($mail_data['email']);
                });
            }
        }
      }
    }
}