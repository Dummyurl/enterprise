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
use DB;
//use Maatwebsite\Excel\Facades\Excel;


class DashboardController extends Controller{
    public function training_home(){
        $user_id = Auth::user()->id;
        $page = "trainings";

        return view('employer/trainings',compact('page'));
    }
}