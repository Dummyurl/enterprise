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
use App\Model\Emp_dashboard_contact;
use Carbon\Carbon;
use DB;
//use Maatwebsite\Excel\Facades\Excel;


class EmpDashboardController extends Controller{
    public function home(){
        $user_id = Auth::user()->id;
        $page = "dashboard";
        $user = User::where('id',$user_id)->get();
        $profilesviewed = Profile_views::where('user_id_fk',$user_id)->get();
        $profileviewcnt = count($profilesviewed);
        $cvsdownloaded = Cv_downloads::where('user_id_fk',$user_id)->get();
        $downloadcnt = count($cvsdownloaded);
        $jobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->get();
        $jobpostcnt = count($jobsposted);
        $activejobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->where('created_at', '>=', Carbon::now()->subMonth())->get();
        $activejobpostcnt = count($activejobsposted);
        $now = Carbon::now();
		$yearnow = $now->year;
		$responses = array("0","0","0","0","0","0","0","0","0","0","0","0");
		$jobIds = array();
		$jobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->get();
		foreach($jobsposted as $jp){
			array_push($jobIds, $jp->job_id);
		}
		//print_r($jobIds);exit();
		$from1 = $yearnow."-01-"."01";
		$to1 = $yearnow."-01-"."30";

		$from2 = $yearnow."-02-"."01";
		$to2 = $yearnow."-02-"."28";

		$from3 = $yearnow."-03-"."01";
		$to3 = $yearnow."-03-"."30";

		$from4 = $yearnow."-04-"."01";
		$to4 = $yearnow."-04-"."30";

		$from5 = $yearnow."-05-"."01";
		$to5 = $yearnow."-05-"."30";

		$from6 = $yearnow."-06-"."01";
		$to6 = $yearnow."-06-"."30";

		$from7 = $yearnow."-07-"."01";
		$to7 = $yearnow."-07-"."30";

		$from8 = $yearnow."-08-"."01";
		$to8 = $yearnow."-08-"."30";

		$from9 = $yearnow."-09-"."01";
		$to9 = $yearnow."-09-"."30";

		$from10 = $yearnow."-10-"."01";
		$to10 = $yearnow."-10-"."30";

		$from11 = $yearnow."-11-"."01";
		$to11 = $yearnow."-11-"."30";

		$from12 = $yearnow."-12-"."01";
		$to12 = $yearnow."-12-"."30";

		$appliedJobs1 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from1, $to1))->get();
		$appliedJobs2 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from2, $to2))->get();
		$appliedJobs3 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from3, $to3))->get();	
		$appliedJobs4 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from4, $to4))->get();
		$appliedJobs5 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from5, $to5))->get();
		$appliedJobs6 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from6, $to6))->get();
		$appliedJobs7 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from7, $to7))->get();		
		$appliedJobs8 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from8, $to8))->get();
		$appliedJobs9 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from9, $to9))->get();
		$appliedJobs10 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from10, $to10))->get();
		$appliedJobs11 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from11, $to11))->get();
		$appliedJobs12 = Applied_job::whereIn('job_id_fk',$jobIds)
									   ->whereBetween('created_at',array($from12, $to12))->get();
		$responses[0] = count($appliedJobs1);
		$responses[1] = count($appliedJobs2);
		$responses[2] = count($appliedJobs3);
		$responses[3] = count($appliedJobs4);
		$responses[4] = count($appliedJobs5);
		$responses[5] = count($appliedJobs6);
		$responses[6] = count($appliedJobs7);
		$responses[7] = count($appliedJobs8);
		$responses[8] = count($appliedJobs9);
		$responses[9] = count($appliedJobs10);
		$responses[10] = count($appliedJobs11);	
		$responses[11] = count($appliedJobs12);	
		$cdata = Emp_dashboard_contact::where('id',1)->first();						   					   				
		//print_r($responses);
		//exit();

        return view('employer/dashboard',compact('page','profileviewcnt','downloadcnt','jobpostcnt','user','activejobpostcnt','responses','cdata'));
    }
}