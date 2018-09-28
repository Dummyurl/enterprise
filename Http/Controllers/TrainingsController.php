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
use App\Model\Training;
use DB;
//use Maatwebsite\Excel\Facades\Excel;


class TrainingsController extends Controller{
    public function training_home(){
        $user_id = Auth::user()->id;
        $page = "trainings";
        $jobpost_content = Training::where('type',1)->where('status',1)->get();
        $cvsearch_content = Training::where('type',2)->where('status',1)->get();
        $premium_content = Training::where('type',3)->where('status',1)->get();
        $other_content = Training::where('type',4)->where('status',1)->get();
        //echo json_encode($jobpostings); exit();

        return view('employer/trainings',compact('page','jobpost_content','cvsearch_content','premium_content','other_content'));
    }
    public function play_video($id){
        $video_details = Training::where('training_id',$id)->get();
        $page = "trainings";
        return view('play_video',compact('page','video_details'));
    }
    
}