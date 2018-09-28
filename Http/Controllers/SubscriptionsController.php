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
use App\Model\Profile_views;
use App\Model\Cv_downloads;
use App\Model\Emails_sent;
use DB;
use Helper;
//use Maatwebsite\Excel\Facades\Excel;


class SubscriptionsController extends Controller{
    public function home(){
        $user_id = Auth::user()->id;
        $page = "subscriptions";
        $user = User::where('id',$user_id)->get();

        $profilesviewed = Profile_views::where('user_id_fk',$user_id)->get();
        $profileviewcnt = count($profilesviewed);
        $cvsdownloaded = Cv_downloads::where('user_id_fk',$user_id)->get();
        $downloadcnt = count($cvsdownloaded);
        $regjobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->where('jp_type','3')->get();
        $regjobpostcnt = count($regjobsposted);
        $erpjobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->where('jp_type','2')->get();
        $erpjobpostcnt = count($erpjobsposted);
        $emails_sent = Emails_sent::where('user_id_fk',$user_id)->get();
        $emails_sentcnt = count($emails_sent);

        return view('employer/subscriptions',compact('page','user','downloadcnt','profileviewcnt','regjobpostcnt','erpjobpostcnt','emails_sentcnt'));
    }
   /* public function test(){
        //print_r(count(Helper::total_job_posted()));exit();
        if( Helper::job_post_access() == count(Helper::total_job_posted()) )
            {
               $pack = Helper::current_job_package();
               $user =User_package::where('user_id_fk',Auth::user()->id)->where('package_id_fk',$pack->package_id)->first();
               $user->expiry_date = date('Y-m-d');
               $user->status = 3;
               $user->save();
            }
            return $user;
    }*/
}