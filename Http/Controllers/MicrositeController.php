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
use App\Model\Microsite_details;
use App\Model\Microsite_details_preview;
use App\Model\Folder;
use App\Model\Microsite_resumes;
use DB;
use App\Model\Recent_update;
//use Maatwebsite\Excel\Facades\Excel;


class MicrositeController extends Controller{
    private $jids = array();
    public function home($id){
        $details = Microsite_details::where('site_id',$id)->get();
        $page = "dashboard";
        $siteid = $id;
        $user_id = Microsite_details::where('site_id',$id)->first();
        $fb = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',1)->first();
        $li = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',2)->first();
        $tw = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',3)->first();
        return view('microsite/dashboard',compact('details','page','siteid','fb','li','tw','user_id'));
    }
    public function contact_us($id){
        $details = Microsite_details::where('site_id',$id)->get();
        $page = "contact_us";
        $siteid = $id;
        $user_id = Microsite_details::where('site_id',$id)->first();
        $fb = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',1)->first();
        $li = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',2)->first();
        $tw = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',3)->first();
        return view('microsite/contact_us',compact('details','page','siteid','fb','li','tw','user_id'));
    }
    public function preview($id){
        $details = Microsite_details_preview::where('site_id',$id)->get();
        $page = "dashboard";
        $siteid = $id;
        $user_id = Microsite_details::where('site_id',$id)->first();
        $fb = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',1)->first();
        $li = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',2)->first();
        $tw = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',3)->first();
        return view('microsite/dashboard',compact('details','page','siteid','fb','li','tw','user_id'));
    }
    public function upload_resume(Request $request){
        $resume = new Microsite_resumes();
        $resume->site_id_fk = $request->siteid;
        $resume->name = $request->name;
        $resume->email = $request->email;
        $filelocation = "";

         $file_validator = Validator::make($request->all(), ['resume_upload' => 'max:4096',]);
        if ($file_validator->fails()){
            echo 10;die();
        }
        if($request->hasFile('resume_upload')){

                $destination = 'uploads/resumes';  
                $file = $request->file('resume_upload');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
        }
        $resume->path = $filelocation;
        $resume->created_at = date("Y-m-d H:i:s");
        $resume->updated_at = date("Y-m-d H:i:s");
        $resume->save();
        echo 1;
        die();
    }
    public function jobs_list($id){

    	$siteid = $id;
    	$details = Microsite_details::where('site_id',$id)->get();
        $micro = Microsite_details::where('site_id',$id)->first();
        $details1 = Employer::where('user_id_fk',$micro->user_id_fk)->orWhere('parent_id',$micro->user_id_fk)->get();

        /**/
    	$page = "jobs";
        $user_id = Microsite_details::where('site_id',$id)->first();

        $fb = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',1)->first();
        $li = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',2)->first();
        $tw = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',3)->first();
        //print_r(count($details1));exit();
        return view('microsite/jobs',compact('details','details1','page','siteid','fb','li','tw','user_id'));
    }
    public function microsite_search_job(Request $request){
        $siteid = $request->site_id;
        $details = Microsite_details::where('site_id',$siteid)->get();
        $page = "jobs";
        $keywords = $request->keywords;
        $location = $request->location;
        $exp = $request->experience;
        $locationarr = explode(",", $location);
        //print_r($loc);exit();
        if(!empty($location)){
            if(!empty($exp) && !empty($keywords)){
                $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('user_id_fk',$request->user_id)
                                    ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->get();
            }elseif($exp!=""){
                $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('user_id_fk',$request->user_id)->get();
            }elseif(!empty($keywords)){
                $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('user_id_fk',$request->user_id)
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->get();
            }else{
                $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('user_id_fk',$request->user_id)->get();
            }
            
            $this->addtoarr($matchedJobs);
        }
        if($exp!=""){
           // ;
            if(!empty($location) && !empty($keywords)){
                $expri = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('user_id_fk',$request->user_id)
                                    ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->get();
            }elseif(!empty($location)){
                $expri = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('user_id_fk',$request->user_id)->get();
            }elseif(!empty($keywords)){
                $expri = Job_post::where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('user_id_fk',$request->user_id)
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->get();
            }else{
                $expri = Job_post::where('max_experience','>=',$exp)->where('min_experience','<=',$exp)->where('user_id_fk',$request->user_id)->get();
            }
            $this->addtoarr($expri);
        }
        
        if(!empty($keywords)){
            //
            if(!empty($location) && $exp!=""){
                $key = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('user_id_fk',$request->user_id)
                                    ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->get();
            }elseif(!empty($location)){
                $key = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->where('user_id_fk',$request->user_id)->get();
            }elseif($exp!=""){
                $key = Job_post::where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                    ->where('user_id_fk',$request->user_id)
                                    ->where('job_title','like','%'.$keywords.'%')
                                    ->get();
            }else{
                $key = Job_post::where('job_title','like','%'.$keywords.'%')->where('user_id_fk',$request->user_id)->get();
            }
            $this->addtoarr($key);
        }


        $employer = Employer::where('parent_id',$request->user_id)->get();
        if(count($employer)>0)
        {
            foreach ($employer as $e) 
            {
                if(!empty($location)){
                        if($exp!="" && !empty($keywords)){
                            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('user_id_fk',$e->user_id_fk)
                                                ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->get();
                        }elseif($exp!=""){
                            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('user_id_fk',$e->user_id_fk)->get();
                        }elseif(!empty($keywords)){
                            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('user_id_fk',$e->user_id_fk)
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->get();
                        }else{
                            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('user_id_fk',$e->user_id_fk)->get();
                        }
                        
                        $this->addtoarr($matchedJobs);
                    }
                    if($exp!=""){
                       // ;
                        if(!empty($location) && !empty($keywords)){
                            $expri = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('user_id_fk',$e->user_id_fk)
                                                ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->get();
                        }elseif(!empty($location)){
                            $expri = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('user_id_fk',$e->user_id_fk)->get();
                        }elseif(!empty($keywords)){
                            $expri = Job_post::where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('user_id_fk',$e->user_id_fk)
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->get();
                        }else{
                            $expri = Job_post::where('max_experience','>=',$exp)->where('min_experience','<=',$exp)->where('user_id_fk',$e->user_id_fk)->get();
                        }
                        $this->addtoarr($expri);
                    }
                    
                    if(!empty($keywords)){
                        //
                        if(!empty($location) && $exp!=""){
                            $key = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('user_id_fk',$e->user_id_fk)
                                                ->where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->get();
                        }elseif(!empty($location)){
                            $key = Job_post::where('location','like','%'.$locationarr[0].'%')
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->where('user_id_fk',$e->user_id_fk)->get();
                        }elseif($exp!=""){
                            $key = Job_post::where('max_experience','>=',$exp)->where('min_experience','<=',$exp)
                                                ->where('user_id_fk',$e->user_id_fk)
                                                ->where('job_title','like','%'.$keywords.'%')
                                                ->get();
                        }else{
                            $key = Job_post::where('job_title','like','%'.$keywords.'%')->where('user_id_fk',$e->user_id_fk)->get();
                        }
                        $this->addtoarr($key);
                    }
            }
        }
     $search = Job_post::whereIn('job_id',$this->jids)->where('type',1)->where('status',1)->where('job_expire','>=',date('Y-m-d'))->get();
     $user_id = Microsite_details::where('site_id',$siteid)->first();
     $fb = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',1)->first();
        $li = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',2)->first();
        $tw = Recent_update::where('user_id',$user_id->user_id_fk)->where('type',3)->first();
        //print_r($this);exit();
        return view('microsite/search_jobs',compact('search','details','siteid','fb','li','tw'));
    }
    public function addtoarr($arr){
        if(count($arr)>0){
            foreach($arr as $a){
                array_push($this->jids, $a->job_id);              
            }
        }
    }
}