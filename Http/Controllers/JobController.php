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
use App\Model\Academic_details;
use App\Model\Personal_details;
use App\Model\Last_login;
use App\Model\Career_history;
use App\Model\Applied_job;
use Carbon\Carbon;
use App\Model\Countries;
use App\Model\Language;
use App\Model\Job_preference;
use App\Model\IndustryType;
use App\Model\Job_post_keyskills;
use App\Model\Job_seeker_technical_skills;
use App\Model\Job_seeker_certificate;
use App\Model\Job_seeker_cv;
use App\Model\Cv;
use App\Model\Cover_letter;
use App\Model\Seminar_details;
use Helper;
use App\Model\Project;
use App\Model\Application_reply;
use App\Model\Saved_job;
use App\Model\Job_post;
use App\Model\Course;
use App\Model\Employer;

class JobController extends Controller{
		
	public function job_details($id)
	{
        //print_r($id); exit();
	$job = Job_post::where('job_id',$id)->first();

        $res = explode(',', $job->qualification_degree);
        
        foreach ($res as $r) {
                $course[] = Course::where('course_id',$r)->first();
        }
      // print_r($course);exit();
        $title = $job->job_title;
        $view_count = $job->view_count;
        $vc_pone = $view_count + 1;
        $pdate = date("Y-m-d H:i:s");
        Job_post::where('job_id',$id)->update(['view_count'=>$vc_pone,'last_seen'=>$pdate]);
        $similar = Job_post::where('job_id','!=',$id)->where('type',1)->where('status',1)->where('job_title','LIKE', '%'.$title.'%')->get();
        $consultant = Employer::where('type','2')->get();
        return view('job_details',compact('job','course','similar','consultant'));
	}
	
   
   
}