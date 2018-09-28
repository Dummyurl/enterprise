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
use Helper;
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
use App\Model\HighestCourse;
use App\Model\PGSpecialization;
use App\Model\Refine_values;
use App\Model\Profile_views;
use App\Model\Cv_downloads;
use App\Model\Emails_sent;
use App\Model\Feedback;
use App\Model\Project;
use App\Model\Career_history;
use App\Model\Block_company;
use App\Model\Folder_share;
use App\Model\HighestSpecialization;
use DB;
use App\Model\Microsite_details_preview;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\Savedsearch_share;
use App\Model\Sr_forwards;
use App\Model\Academic_details;
use App\Model\Seminar_details;
use App\Model\Job_seeker_certificate;
use App\Model\Application_reply;
use App\Model\Sms_sent;
use Image;
use App\Model\Recent_update;
use App\Model\Addon_package;
use App\Model\Free_jobpost_validity;

class EmployerPackageController extends Controller{
    private $jsids = array();
    private $search_employer_email;
    private $mail_search_subject;
    public function package345(){
         return view('employer/package345');
    }
    public function employer_packages(){
        $user_id = Auth::user()->id;
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $country = Countries::all();

        $savedsearches = Cvsearch_save::where('user_id_fk',$user_id)->get();
        // shared saved searches
        $shared_ss = Cvsearch_save::select("*")->join('savedsearch_share','cvsearch_save.id','=','savedsearch_share.savedsearch_id')->where('employer_id_fk',$user_id)->get();
        //$shared_ss = Savedsearch_share::where('employer_id_fk',$user_id)->get();

        $page = "packages";
        $selectedsearch =[];
        //$othercvfolders = Folder::where('user_id_fk','<>',$user_id)->where('usedfor',1)->get();
        $othercvfolders = Folder::select('*')
            ->join('folder_share', 'folder.folder_id', '=', 'folder_share.folder_id_fk')
            ->where('folder_share.employer_id_fk',$user_id)
            ->where('folder_share.status',1)
            ->join('users','folder.user_id_fk','=','users.id')
            ->where('folder.usedfor',1)->get();
        $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        //$otherjobfolders = Folder::where('user_id_fk','<>',$user_id)->where('usedfor',2)->get();
        $otherjobfolders = Folder::select('*')
            ->join('folder_share', 'folder.folder_id', '=', 'folder_share.folder_id_fk')
            ->where('folder_share.employer_id_fk',$user_id)
            ->where('folder_share.status',1)
            ->join('users','folder.user_id_fk','=','users.id')
            ->where('folder.usedfor',2)->get();
        $myjobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $allcvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $alljobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $jobsposted =  Job_post::where('user_id_fk',$user_id)->where('status',1)->orderBy('updated_at','desc')->get();
        $inactive = Job_post::where('user_id_fk',$user_id)->where('type',1)->where('status',2)
                    ->where('job_expire', '<', date('Y-m-d H:i:s'))
                    ->orderBy('created_at','DESC');
        $jobsdeleted =  Job_post::where('user_id_fk',$user_id)->where('status',2)->where('type',1)->orderBy('updated_at','desc')->get();
        $user = User::where('id',$user_id)->get();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        $employer_f_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        if(count($employer_f_users)>0){
        $employer_folder_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        }
        else{
          $parent_data = Employer::where('user_id_fk',$user_id)->first();
            $employer_folder_users = Employer::where('user_id_fk',$parent_data->parent_id)->get();
        }
        $micosite_details = Microsite_details::where('user_id_fk',$user_id)->where('status',1)->get();
        $top_employer = Top_Employer::where('user_id_fk',$user_id)->get();
        $courses = Course::orderBy('course_name')->get();
        $pgcourses = PGCourse::orderBy('pgc_name')->get();

        $highest_courses = Academic_details::where('qualification_type',1)->get();
        $highestcourses = HighestCourse::orderBy('course_name')->get();
        $profilesviews = Profile_views::where('user_id_fk',$user_id)->get();
        $forwardviews = Sr_forwards::where('employer_id_fk',$user_id)
                                    ->where('js_id_fk','<>',0)->get();
        $emails_sent = Emails_sent::where('user_id_fk',$user_id)->get();
        // dd($user_id);
        $regjobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->where('jp_type','3')->get();
        $regjobpostcnt = count($regjobsposted);
        $erpjobsposted = Job_post::where('user_id_fk',$user_id)->where('status',1)->where('jp_type','2')->get();
        $erpjobpostcnt = count($erpjobsposted);
        $fb = Recent_update::where('user_id',$user_id)->where('type',1)->first();
        $li = Recent_update::where('user_id',$user_id)->where('type',2)->first();
        $tw = Recent_update::where('user_id',$user_id)->where('type',3)->first();
        $parent_data = Employer::where('user_id_fk',$user_id)->first();
        $addon = Addon_package::all();
        //echo json_encode($employer_users); 
       //return view('admin/employer/employer_list',compact('employer'));
        //dd(count($inactive));
        return view('employer/packages',compact('user','page','industry','country','savedsearches','selectedsearch','jobsposted','employer_users','othercvfolders','mycvfolders','micosite_details','top_employer','myjobfolders','profilesviews','otherjobfolders','allcvfolders','alljobfolders','courses','pgcourses','emails_sent','regjobpostcnt','erpjobpostcnt','employer_folder_users','shared_ss','forwardviews','highestcourses','highest_courses','fb','li','tw','parent_data','addon','jobsdeleted','inactive'));
    }
    public function employer_search_edit($id){
        $user_id = Auth::user()->id;
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $country = Countries::all();
        $courses = Course::orderBy('course_name')->get();
        $pgcourses = PGCourse::orderBy('pgc_name')->get();

        $savedsearches = Cvsearch_save::where('user_id_fk',$user_id)->get();
        $jobsposted =  Job_post::where('user_id_fk',$user_id)->where('status',1)->get();
        $selectedsearch = Cv_search::where('cv_search_id',$id)->get();
        $selectedsearch_details = Cv_search_details::where('search_id_fk',$id)->first();
        $selectedjob = [];
        $othercvfolders = Folder::where('user_id_fk','<>',$user_id)->where('usedfor',1)->get();
        $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $user = User::where('id',$user_id)->get();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        $micosite_details = Microsite_details::where('user_id_fk',$user_id)->get();
        $top_employer = Top_Employer::where('user_id_fk',$user_id)->get();
        $otherjobfolders = Folder::where('user_id_fk','<>',$user_id)->where('usedfor',2)->get();
        $myjobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $allcvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $alljobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $page = "packages";
        $edit = "edit";
        $highest_courses = Academic_details::where('qualification_type',1)->get();
        $addon = Addon_package::all();
       //return view('admin/employer/employer_list',compact('employer'));
        return view('employer/packages-edit',compact('user','page','industry','country','savedsearches','selectedsearch','jobsposted','employer_users','edit','othercvfolders','mycvfolders','micosite_details','top_employer','myjobfolders','otherjobfolders','allcvfolders','alljobfolders','courses','pgcourses','selectedjob','selectedsearch_details','highest_courses','addon'));
    }
    public function employer_job_edit($id){
        $user_id = Auth::user()->id;
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $country = Countries::all();
        $courses = Course::orderBy('course_name')->get();
        $pgcourses = PGCourse::orderBy('pgc_name')->get();

        $savedsearches = Cvsearch_save::where('user_id_fk',$user_id)->get();
        $jobsposted =  Job_post::where('user_id_fk',$user_id)->where('status',1)->get();
        $selectedsearch = [];
        $selectedsearch_details=[];
        $selectedjob = Job_post::where('job_id',$id)->get();
        $othercvfolders = Folder::where('user_id_fk','<>',$user_id)->where('usedfor',1)->get();
        $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $user = User::where('id',$user_id)->get();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        $micosite_details = Microsite_details::where('user_id_fk',$user_id)->get();
        $top_employer = Top_Employer::where('user_id_fk',$user_id)->get();
        $otherjobfolders = Folder::where('user_id_fk','<>',$user_id)->where('usedfor',2)->get();
        $myjobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $allcvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $alljobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $page = "packages";
        $edit = "edit";
        $highest_courses = Academic_details::where('qualification_type',1)->get();
        $addon = Addon_package::all();
       //return view('admin/employer/employer_list',compact('employer'));
        return view('employer/packages-edit',compact('user','page','industry','country','savedsearches','selectedsearch','jobsposted','employer_users','edit','othercvfolders','mycvfolders','micosite_details','top_employer','myjobfolders','otherjobfolders','allcvfolders','alljobfolders','courses','pgcourses','selectedjob','highest_courses','addon'));
    }
    public function getfunctionalarealist(Request $request){
        // echo 1;die;
        if(!empty($request->id))
        {
            $id = $request->id;
            $industry_obj = IndustryType::where('industry_type_name',$id)->first();
            $fareas = SubIndustryType::where('industry_type_id_fk',$industry_obj->industry_type_id)->get();
            $str = "<option value=''>Functional area</option>";

            foreach ($fareas as $fa) {
                $str .= "<option  value='".$fa->sub_industry_type_name."'>".$fa->sub_industry_type_name."</option>";
            }
            $str.="<option value='0'>Other</option>";
            echo "$str"; die();
        }
        else
        {
            $str = "<option value=''>Functional area</option>";
            $str.="<option value='0'>Other</option>";
            echo "$str";die;
        }
    }
    public function ug_branches(Request $request){
        $id = $request->id;
        $id_obj = Course::where('course_name',$id)->first();
        $branches = Specialization::where('course_id_fk',$id_obj->course_id)->get();
        $str = "<option value=''>Specialization</option>";
        foreach ($branches as $branch) {
            $str .= "<option  value=".$branch->specialization_name.">".$branch->specialization_name."</option>";
        }
         $str .= "<option value='0'>Other</option>";
        echo $str; die();
    }
    public function pg_branches(Request $request){
        $id = $request->id;
        $id_obj = PGCourse::where('pgc_name',$id)->first();
        $branches = PGSpecialization::where('pgc_id_fk',$id_obj->pgc_id)->get();
        $str = "<option value=''>Specialization</option>";
        foreach ($branches as $branch) {
            $str .= "<option  value=".$branch->pgs_name.">".$branch->pgs_name."</option>";
        }
         $str .= "<option value='0'>Other</option>";
        echo $str; die();
    }
    public function hs_branches(Request $request){
        $id = $request->id;
        $id_obj = HighestCourse::where('course_name',$id)->first();
        $branches = HighestSpecialization::where('hc_id_fk',$id_obj->course_id)->get();
        $str = "<option value=''>Specialization</option>";
        foreach ($branches as $branch) {
            $str .= "<option  value=".$branch->hs_name.">".$branch->hs_name."</option>";
        }
         $str .= "<option value='0'>Other</option>";
        echo $str; die();
    }
    public function searchresults_home($id){
        $user_id = Auth::user()->id;
        $page = "packages";
        $searchId = Crypt::decryptString($id);
        //echo $searchId;
        $searchdata = Cv_search::where('cv_search_id',$searchId)->get();
        $matchingId = $searchdata[0]['relavance'];
        $cv_search = Cv_search::where('cv_search_id',$searchId)->get();
        
        //if($matchingId == 1)
        $cv_search_details = Cv_search_details::where('search_id_fk',$searchId)->get();
        $srchval_keyword = $cv_search_details[0]['keyword'];
        $srchval_exkeyword = $cv_search_details[0]['ex_keyword'];
        $srchval_minexp = $cv_search_details[0]['min_exp'];
        $srchval_maxexp = $cv_search_details[0]['max_exp'];
        $from_age = $cv_search_details[0]['from_age'];
        $to_age = $cv_search_details[0]['to_age'];
        $srchval_cloc = $cv_search_details[0]['cur_loc'];
        $srchval_eloc = $cv_search_details[0]['exp_loc'];
        $srchval_minsal = $cv_search_details[0]['min_sal'];
        $srchval_maxsal = $cv_search_details[0]['max_sal'];
        $currency_type = $cv_search_details[0]['currency_type'];
        $itype =  $cv_search_details[0]['industry'];
        $farea =  $cv_search_details[0]['farea'];
        $nation =  $cv_search_details[0]['nation'];
        $visaval =  $cv_search_details[0]['visa_status'];
        $genderval =  $cv_search_details[0]['gender'];
        $last_active =  $cv_search_details[0]['last_active'];
        $last_updated =  $cv_search_details[0]['last_updated'];
        $notice_period =  $cv_search_details[0]['notice_period'];
        $vehicle_type =  $cv_search_details[0]['vehicle_type'];
        $current_job_title =  $cv_search_details[0]['job_title'];
        $qualification =  $cv_search_details[0]['degree'];
        $specialization =  $cv_search_details[0]['specialization'];
        $current_employer_name =  $cv_search_details[0]['employer_name'];
        $certification =  $cv_search_details[0]['certification'];
        $certification_name =  $cv_search_details[0]['certification_name'];
        $dl =  $cv_search_details[0]['has_dl'];
        if(!empty($cv_search_details[0]['marital_status']))
            $maritalvals =  explode(',',$cv_search_details[0]['marital_status']);
        else
            $maritalvals =  $cv_search_details[0]['marital_status'];

        if(!empty($cv_search_details[0]['jobtype']))
            $jobtype =  explode(',',$cv_search_details[0]['jobtype']);
        else
            $jobtype =  $cv_search_details[0]['jobtype']; 

        $langs = $cv_search_details[0]['languages'];

        $search['keywords']=explode(',',$srchval_keyword);
        $search['min_exp']=$srchval_minexp;
        $search['max_exp']=$srchval_maxexp;
        $search['pref_loc']=explode(',',$srchval_eloc);
        $search['min_salary']=$srchval_minsal;
        $search['max_salary']=$srchval_maxsal;
        $search['currency_type']=$currency_type;
        $search['industry']=explode(',',$itype);
        $search['function']=explode(',',$farea);
        $search['visa_status']=explode(',',$visaval);
        $search['maritalvals']=empty($maritalvals)?array():$maritalvals;
        $search['gender']=explode(',',$genderval);
        $search['notice_period']=explode(',',$notice_period);
        $search['designation']=explode(',',$current_job_title);
        $search['qualification']=explode(',',$qualification);
        $search['company']=explode(',',$current_employer_name);
        $skils=Cv_search_techskills::where('search_id_fk',$searchId)->get();
        //echo json_encode($cv_search_details[0]['keyword']);
        $matchedusers= User::where('role',2)->where('email_verify',2)->where('enabled',1)->pluck('id')->toArray();
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Job_seeker_technical_skills::where('skill','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Career_history::where('job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Project::where('role','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Job_preference::where('preferred_job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }

// dd($matchedusers);
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Job_seeker_technical_skills::where('skill','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        // dd($result);
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Career_history::where('job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Project::where('role','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Job_preference::where('preferred_job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
   // dd($matchedusers);
        DB::connection()->enableQueryLog();
        if((!empty($srchval_minexp))&&(!empty($srchval_maxexp))){
            // print_r("expression");
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->whereBetween('total_exp',[$srchval_minexp,$srchval_maxexp])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
        elseif((!empty($srchval_minexp))&&(empty($srchval_maxexp))){
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','>=',$srchval_minexp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        else if((empty($srchval_minexp))&&(!empty($srchval_maxexp))){
            // dd($matchedusers);

        // DB::connection()->enableQueryLog();
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','<=',$srchval_maxexp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
        // dd($matchedusers);

            // dd($srchval_cloc);
        if(!empty($srchval_cloc)){
            $result = Job_seeker_personal_details::where('current_location','like','%'.$srchval_cloc.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            // $matchedusers=array_merge($result,$matchedusers);
            $data['query']=DB::getQueryLog();
        }
        if(!empty($srchval_cloc)){
            $result1 = Job_seeker_personal_details::where('zip',$srchval_cloc)->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=array_merge($result1,$result);
            // $data['query']=DB::getQueryLog();
        }
        // dd($result);

        if(!empty($srchval_eloc)){
            $result = Job_preference::where('preferred_job_location','like','%'.$srchval_eloc.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($currency_type)){
            $result = Job_preference::where('currency_type',$currency_type)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if((!empty($srchval_minsal))&&(!empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if($value->min_sal>=$srchval_minsal && $value->max_sal<=$srchval_maxsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((!empty($srchval_minsal))&&(empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if($value->min_sal>=$srchval_minsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((empty($srchval_minsal))&&(!empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if($value->max_sal<=$srchval_maxsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
// dd($to_age);

        if((!empty($from_age))&&(!empty($to_age))){
            $date1=date('Y-m-d',strtotime('-'.$from_age.' years ', strtotime(date('Y-m-d'))));
            $date2=date('Y-m-d',strtotime('-'.$to_age.' years ', strtotime(date('Y-m-d'))));
            // dd($date1);
            $result = Job_seeker_personal_details::whereBetween('dob',[$date2,$date1])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            // dd($matchedusers);
            $data['query']=DB::getQueryLog();
            // dd($data);
            $matchedusers=$result;
        }
        elseif((!empty($from_age))&&(empty($to_age))){
            $date1=date('Y-m-d',strtotime('-'.$from_age.' years ', strtotime(date('Y-m-d'))));
            $result = Job_seeker_personal_details::where('dob','<=',$date1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((empty($from_age))&&(!empty($to_age))){
            $date2=date('Y-m-d',strtotime('-'.$to_age.' years ', strtotime(date('Y-m-d'))));
            $result = Job_seeker_personal_details::where('dob','>=',$date2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
// dd($matchedusers);
        if(!empty($itype)){
            $result = Job_preference::where('preferred_industry_type',$itype)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($farea)){
            $result = Job_preference::where('preferred_job_function',$farea)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($farea);
        if(!empty($nation)){
            $result = Job_seeker_personal_details::where('nationality',$nation)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($visaval) ){
            $result = Job_seeker_personal_details::where('current_visa_status','<>',"")->where('current_visa_status',$visaval)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif(!empty($visaval) ){
            $result = Job_seeker_personal_details::where(function ($query) {$query->orwhereNull('current_visa_status')->orwhere('current_visa_status',"")->orwhere('current_visa_status',$visaval);})->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($genderval)){
            $result = Job_seeker_personal_details::where('gender',$genderval)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($dl)){
            $result = Job_seeker_personal_details::where('driving_liicence',$dl)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($current_job_title)){
            $result = Career_history::where('job_title','like','%'.$current_job_title.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($current_employer_name)){
            $result = Career_history::where('employer_name','like','%'.$current_employer_name.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($maritalvals)){
            $result = Job_seeker_personal_details::whereIn('marital_status',$maritalvals)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($vehicle_type)){
            $result = Job_seeker_personal_details::whereIn('vtype',$vehicle_type)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($jobtype);
        if(!empty($jobtype) && count($jobtype)<3){
            $result = Career_history::whereIn('employement_type',$jobtype)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        
        if((!empty($qualification))&&(!empty($specialization))){
            $result = Academic_details::where('qualification',$qualification)->where('specialization',$specialization)->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((!empty($qualification))&&(empty($specialization))){
            $result = Academic_details::where('qualification',$qualification)->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((empty($qualification))&&(!empty($specialization))){
            $result = Academic_details::where('specialization',$specialization)->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($notice_period)){
            $result = Job_seeker_personal_details::whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $value) {

                if($value->notice_period=="Immediate" )
                {
                    $r[]=$value->user_id_fk;
                }
                elseif (explode(' ',$value->notice_period)[0] <= $notice_period) {
                   $r[]=$value->user_id_fk;
                }
            }
            $matchedusers=$r;
        }

        if(!empty($langs)){
            $lang_arr = explode(",",$langs);
            if(count($lang_arr) > 0){
                $result=[];
                foreach($lang_arr as $lng){
                    $data = Job_seeker_personal_details::where('known_languages','like','%'.$lng.'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                    $result=array_merge($data,$result);
                }
                $matchedusers=$result;
            }
        }

        if(!empty($last_active))
        {
            $date=date('Y-m-d 00:00:00',strtotime('-'.$last_active.'days ', strtotime(date('Y-m-d'))));
            // print_r($date);die;
            // dd($matchedusers); 
            $result=Last_login::where('login_time','>=',$date)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($last_updated))
        {
            $date=date('Y-m-d 00:00:00',strtotime('-'.$last_updated.'days ', strtotime(date('Y-m-d'))));
            // print_r($date);die;
            // dd($matchedusers); 
            $result=array();
            $result1=Academic_details::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Career_history::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_certificate::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_cover_letter::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_cv::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_personal_details::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_technical_skills::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Seminar_details::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Project::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_preference::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $matchedusers=$result;
        }

        if(!empty($skils))
        {
            foreach ($skils as $key) {
                $result=Job_seeker_technical_skills::where('skill','like','%'.$key->skill.'%')->where('level_of_expertise',$key->expertise)->where('years_of_experience','>=',$key->experience)->where('year_last_used','>=',$key->last_used)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
                $matchedusers=$result;
            }
        }
        if(!empty($certification)){
            // dd(explode(',', $location));
            $certification1=explode(',', $certification);

            $certification_name1=explode(',', $certification_name);
            $result=[];
            foreach ($certification1 as $i=>$key) {
                if($key=='certification')
                {
                    $data=Job_seeker_certificate::where('certificate_name','like','%'.$certification_name1[$i].'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                }
                elseif($key=='training'){
                    $data=Seminar_details::where('seminar_name','like','%'.$certification_name1[$i].'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                }
                    $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }

        $matchedusers = Job_seeker_personal_details::where('visibilty','<>','3')
                                                      ->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        $fbjsids = array();
        foreach($matchedusers as $a){
            $employer_data = Employer::where('user_id_fk',$user_id)->first();
            $check_block = Block_company::where('employer_id_fk',  $employer_data->employer_id)
                                        ->where('user_id_fk',$a)->get();
            if(count($check_block) == 0){
                array_push($fbjsids, $a); 
            }             
        }
        // dd($matchedusers);

        // dd($matchedusers);
        // dd($fbjsids);
        if($matchingId == 1){
        $jobseekers = User::whereIn('id',$fbjsids)
                            ->orderBy('name')
                            ->get();
                            /*->sortBy(function($js, $key) {
            return $js->personal_details->first_name;
                            });*/
        }
        else if($matchingId == 2){
        $jobseekers = User::whereIn('id',$fbjsids)
                            ->get()
                            ->sortBy(function($js, $key) {
            return $js->personal_details->total_exp;
                            });
        }
        else{
            $jobseekers = User::whereIn('id',$fbjsids)->get();
        }
        //exit();
        $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $latestsearch = Cv_search::orderBy("created_at","desc")->first();
        $profilesviewed = Profile_views::where('user_id_fk',$user_id)->get();
        $profileviewcnt = count($profilesviewed);
        $cvsdownloaded = Cv_downloads::where('user_id_fk',$user_id)->get();
        $downloadcnt = count($cvsdownloaded);
        $user = User::where('id',$user_id)->get();
        $cvsearch_dtls = Cv_search_details::all();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $subindustry = SubIndustryType::all();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        $employer_f_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        if(count($employer_f_users)>0){
        $employer_folder_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        }
        else{
          $parent_data = Employer::where('user_id_fk',$user_id)->first();
            $employer_folder_users = Employer::where('user_id_fk',$parent_data->parent_id)->get();
        }
        //print_r($latestsearch); exit();
        Cv_search::where('cv_search_id',$searchId)->update(['result_count'=>sizeof($matchedusers)]);
        return view('employer/employer_search_results',compact('page','cv_search','searchId','jobseekers','mycvfolders','latestsearch','downloadcnt','profileviewcnt','user','cvsearch_dtls','industry','subindustry','employer_users','search','employer_folder_users','srchval_keyword'));
    }

    public function get_matched_results($searchId){
        $user_id = Auth::user()->id;
        $page = "packages";
        //echo $searchId;
        $searchdata = Cv_search::where('cv_search_id',$searchId)->get();
        $matchingId = $searchdata[0]['relavance'];
        $cv_search = Cv_search::where('cv_search_id',$searchId)->get();
        
        //if($matchingId == 1)
        $cv_search_details = Cv_search_details::where('search_id_fk',$searchId)->get();
        $srchval_keyword = $cv_search_details[0]['keyword'];
        $srchval_exkeyword = $cv_search_details[0]['ex_keyword'];
        $srchval_minexp = $cv_search_details[0]['min_exp'];
        $srchval_maxexp = $cv_search_details[0]['max_exp'];
        $from_age = $cv_search_details[0]['from_age'];
        $to_age = $cv_search_details[0]['to_age'];
        $srchval_cloc = $cv_search_details[0]['cur_loc'];
        $srchval_eloc = $cv_search_details[0]['exp_loc'];
        $srchval_minsal = $cv_search_details[0]['min_sal'];
        $srchval_maxsal = $cv_search_details[0]['max_sal'];
        $itype =  $cv_search_details[0]['industry'];
        $farea =  $cv_search_details[0]['farea'];
        $nation =  $cv_search_details[0]['nation'];
        $visaval =  $cv_search_details[0]['visa_status'];
        $genderval =  $cv_search_details[0]['gender'];
        $last_active =  $cv_search_details[0]['last_active'];
        $last_updated =  $cv_search_details[0]['last_updated'];
        $notice_period =  $cv_search_details[0]['notice_period'];
        $vehicle_type =  $cv_search_details[0]['vehicle_type'];
        $current_job_title =  $cv_search_details[0]['job_title'];
        $qualification =  $cv_search_details[0]['degree'];
        $specialization =  $cv_search_details[0]['specialization'];
        $current_employer_name =  $cv_search_details[0]['employer_name'];
        $certification =  $cv_search_details[0]['certification'];
        $certification_name =  $cv_search_details[0]['certification_name'];
        $dl =  $cv_search_details[0]['has_dl'];
        if(!empty($cv_search_details[0]['marital_status']))
            $maritalvals =  explode(',',$cv_search_details[0]['marital_status']);
        else
            $maritalvals =  $cv_search_details[0]['marital_status'];

        if(!empty($cv_search_details[0]['jobtype']))
            $jobtype =  explode(',',$cv_search_details[0]['jobtype']);
        else
            $jobtype =  $cv_search_details[0]['jobtype']; 

        $langs = $cv_search_details[0]['languages'];

        $search['keywords']=explode(',',$srchval_keyword);
        $search['min_exp']=$srchval_minexp;
        $search['max_exp']=$srchval_maxexp;
        $search['pref_loc']=explode(',',$srchval_eloc);
        $search['min_salary']=$srchval_minsal;
        $search['max_salary']=$srchval_maxsal;
        $search['industry']=explode(',',$itype);
        $search['function']=explode(',',$farea);
        $search['visa_status']=explode(',',$visaval);
        $search['maritalvals']=empty($maritalvals)?array():$maritalvals;
        $search['gender']=explode(',',$genderval);
        $search['notice_period']=explode(',',$notice_period);
        $search['designation']=explode(',',$current_job_title);
        $search['qualification']=explode(',',$qualification);
        $search['company']=explode(',',$current_employer_name);
// dd($cv_search_details);
        $skils=Cv_search_techskills::where('search_id_fk',$searchId)->get();
        //echo json_encode($cv_search_details[0]['keyword']);
        $matchedusers= User::where('role',2)->where('email_verify',2)->where('enabled',1)->pluck('id')->toArray();
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Job_seeker_technical_skills::where('skill','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Career_history::where('job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Project::where('role','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Job_preference::where('preferred_job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }

// dd($matchedusers);
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Job_seeker_technical_skills::where('skill','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        // dd($result);
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Career_history::where('job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Project::where('role','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Job_preference::where('preferred_job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
   // dd($matchedusers);
        DB::connection()->enableQueryLog();
        if((!empty($srchval_minexp))&&(!empty($srchval_maxexp))){
            // print_r("expression");
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->whereBetween('total_exp',[$srchval_minexp,$srchval_maxexp])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
        elseif((!empty($srchval_minexp))&&(empty($srchval_maxexp))){
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','>=',$srchval_minexp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        else if((empty($srchval_minexp))&&(!empty($srchval_maxexp))){
            // dd($matchedusers);

        // DB::connection()->enableQueryLog();
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','<=',$srchval_maxexp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
        // dd($matchedusers);

            // dd($srchval_cloc);
        if(!empty($srchval_cloc)){
            $result = Job_seeker_personal_details::where('current_location','like','%'.$srchval_cloc.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            // $matchedusers=array_merge($result,$matchedusers);
            $data['query']=DB::getQueryLog();
        }
        if(!empty($srchval_cloc)){
            $result1 = Job_seeker_personal_details::where('zip',$srchval_cloc)->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=array_merge($result1,$result);
            // $data['query']=DB::getQueryLog();
        }
        // dd($result);

        if(!empty($srchval_eloc)){
            $result = Job_preference::where('preferred_job_location','like','%'.$srchval_eloc.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if((!empty($srchval_minsal))&&(!empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[0]>=$srchval_minsal && explode('-',$value->preferred_monthly_salary)[1]<=$srchval_maxsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((!empty($srchval_minsal))&&(empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[0]>=$srchval_minsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((empty($srchval_minsal))&&(!empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[1]<=$srchval_maxsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
// dd($to_age);

        if((!empty($from_age))&&(!empty($to_age))){
            $date1=date('Y-m-d',strtotime('-'.$from_age.' years ', strtotime(date('Y-m-d'))));
            $date2=date('Y-m-d',strtotime('-'.$to_age.' years ', strtotime(date('Y-m-d'))));
            // dd($date1);
            $result = Job_seeker_personal_details::whereBetween('dob',[$date2,$date1])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            // dd($matchedusers);
            $data['query']=DB::getQueryLog();
            // dd($data);
            $matchedusers=$result;
        }
        elseif((!empty($from_age))&&(empty($to_age))){
            $date1=date('Y-m-d',strtotime('-'.$from_age.' years ', strtotime(date('Y-m-d'))));
            $result = Job_seeker_personal_details::where('dob','<=',$date1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((empty($from_age))&&(!empty($to_age))){
            $date2=date('Y-m-d',strtotime('-'.$to_age.' years ', strtotime(date('Y-m-d'))));
            $result = Job_seeker_personal_details::where('dob','>=',$date2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
// dd($matchedusers);
        if(!empty($itype)){
            $result = Job_preference::where('preferred_industry_type',$itype)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($farea)){
            $result = Job_preference::where('preferred_job_function',$farea)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($farea);
        if(!empty($nation)){
            $result = Job_seeker_personal_details::where('nationality',$nation)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($visaval) && $visaval=='Yes'){
            $result = Job_seeker_personal_details::where('current_visa_status','<>',"")->where('current_visa_status','<>','no')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif(!empty($visaval) && $visaval=='No'){
            $result = Job_seeker_personal_details::where(function ($query) {$query->orwhereNull('current_visa_status')->orwhere('current_visa_status',"")->orwhere('current_visa_status','no');})->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($genderval)){
            $result = Job_seeker_personal_details::where('gender',$genderval)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($dl)){
            $result = Job_seeker_personal_details::where('driving_liicence',$dl)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($current_job_title)){
            $result = Career_history::where('job_title','like','%'.$current_job_title.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($current_employer_name)){
            $result = Career_history::where('employer_name','like','%'.$current_employer_name.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($maritalvals)){
            $result = Job_seeker_personal_details::whereIn('marital_status',$maritalvals)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($vehicle_type)){
            $result = Job_seeker_personal_details::whereIn('vtype',$vehicle_type)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($jobtype);
        if(!empty($jobtype)){
            $result = Career_history::whereIn('employement_type',$jobtype)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        
        if((!empty($qualification))&&(!empty($specialization))){
            $result = Academic_details::where('qualification',$qualification)->where('specialization',$specialization)->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((!empty($qualification))&&(empty($specialization))){
            $result = Academic_details::where('qualification',$qualification)->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((empty($qualification))&&(!empty($specialization))){
            $result = Academic_details::where('specialization',$specialization)->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($notice_period)){
            $result = Job_seeker_personal_details::whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $value) {

                if($value->notice_period=="Immediate" )
                {
                    $r[]=$value->user_id_fk;
                }
                elseif (explode(' ',$value->notice_period)[0] <= $notice_period) {
                   $r[]=$value->user_id_fk;
                }
            }
            $matchedusers=$r;
        }

        if(!empty($langs)){
            $lang_arr = explode(",",$langs);
            if(count($lang_arr) > 0){
                $result=[];
                foreach($lang_arr as $lng){
                    $data = Job_seeker_personal_details::where('known_languages','like','%'.$lng.'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                    $result=array_merge($data,$result);
                }
                $matchedusers=$result;
            }
        }

        if(!empty($last_active))
        {
            $date=date('Y-m-d 00:00:00',strtotime('-'.$last_active.'days ', strtotime(date('Y-m-d'))));
            // print_r($date);die;
            // dd($matchedusers); 
            $result=Last_login::where('login_time','>=',$date)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($last_updated))
        {
            $date=date('Y-m-d 00:00:00',strtotime('-'.$last_updated.'days ', strtotime(date('Y-m-d'))));
            // print_r($date);die;
            // dd($matchedusers); 
            $result=array();
            $result1=Academic_details::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Career_history::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_certificate::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_cover_letter::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_cv::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_personal_details::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_seeker_technical_skills::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Seminar_details::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Project::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $result1=Job_preference::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->distinct()->pluck('user_id_fk')->toArray();
            $result=array_merge($result1,$result);
            $matchedusers=$result;
        }

        if(!empty($skils))
        {
            foreach ($skils as $key) {
                $result=Job_seeker_technical_skills::where('skill','like','%'.$key->skill.'%')->where('level_of_expertise',$key->expertise)->where('years_of_experience','>=',$key->experience)->where('year_last_used','>=',$key->last_used)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
                $matchedusers=$result;
            }
        }
        if(!empty($certification)){
            // dd(explode(',', $location));
            $certification1=explode(',', $certification);

            $certification_name1=explode(',', $certification_name);
            $result=[];
            foreach ($certification1 as $i=>$key) {
                if($key=='certification')
                {
                    $data=Job_seeker_certificate::where('certificate_name','like','%'.$certification_name1[$i].'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                }
                elseif($key=='training'){
                    $data=Seminar_details::where('seminar_name','like','%'.$certification_name1[$i].'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                }
                    $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }

        $matchedusers = Job_seeker_personal_details::where('visibilty','<>','3')
                                                      ->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        $fbjsids = array();
        foreach($matchedusers as $a){
            $employer_data = Employer::where('user_id_fk',$user_id)->first();
            $check_block = Block_company::where('employer_id_fk',  $employer_data->employer_id)
                                        ->where('user_id_fk',$a)->get();
            if(count($check_block) == 0){
                array_push($fbjsids, $a); 
            }             
        }
        // dd($matchedusers);

        // dd($matchedusers);
        // dd($fbjsids);
        if($matchingId == 1){
        $jobseekers = User::whereIn('id',$fbjsids)
                            ->get()
                            ->sortBy(function($js, $key) {
                                return $js->personal_details->first_name;
                            });
        }
        else if($matchingId == 2){
        $jobseekers = User::whereIn('id',$fbjsids)
                            ->get()
                            ->sortBy(function($js, $key) {
                                return $js->personal_details->total_exp;
                            });
        }
        else{
            $jobseekers = User::whereIn('id',$fbjsids)->get();
        }
        //exit();
        $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $latestsearch = Cv_search::orderBy("created_at","desc")->first();
        $profilesviewed = Profile_views::where('user_id_fk',$user_id)->get();
        $profileviewcnt = count($profilesviewed);
        $cvsdownloaded = Cv_downloads::where('user_id_fk',$user_id)->get();
        $downloadcnt = count($cvsdownloaded);
        $user = User::where('id',$user_id)->get();
        $cvsearch_dtls = Cv_search_details::all();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $subindustry = SubIndustryType::all();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        //print_r($latestsearch); exit();
        // Cv_search::where('cv_search_id',$searchId)->update(['result_count'=>sizeof($jobseekers)]);
        return $fbjsids;
    }
    public function languages_list(Request $request){
        $txt = $request->term;
        $results = array();
        $langs = Language::where('name','like','%'.$txt.'%')->get(['name']);
        foreach ($langs as $lang)
        {
            $results[] = [ 'id' => $lang->name, 'value' => $lang->name];
        }
        echo json_encode($results);
    }
    public function searchlist_download($ids){
        $jobseekerids = $ids;
        $arr_jobseekers = explode(",", $jobseekerids);
        $jobseekers = User::whereIn('id', $arr_jobseekers)->get()->toArray();
        //$jobseekers = Countries::get()->toArray();
        // dd($jobseekers);
        $p_dt = date("Y-m-d H:i:s");
        Excel::create('search_results_'.$p_dt, function($excel) use ($jobseekers) {
        $excel->sheet('mySheet', function($sheet) use ($jobseekers)
        {
            $sheet->fromArray($jobseekers);
        });
        })->download('xlsx');
        $searchId = substr($arr_jobseekers[0], 1);
        $redirect_to = url('/employer-search-results/'.Crypt::encryptString($searchId));
        return redirect($redirect_to);
        //exit();
        //die();
    }
    public function searchlist_sendmail(Request $request){
        $jobseekerids =  $request->jsids;
        $arr_jobseekers = explode(",", $jobseekerids);
        $userId = Auth::user()->id;
        $employer_data = Employer::where('user_id_fk',$userId)->get();
        $employer_fullname = $employer_data[0]['first_name']." ".$employer_data[0]['last_name'];
        $this->search_employer_email = $employer_data[0]['email'];
        $this->mail_search_subject = 'New Message from Employer '.$employer_fullname;
        // 1st value in array is search id. 
        if(count($arr_jobseekers) > 0){
            if(Auth::user()->employer_details->parent_id>0)
            {
                $total_send_mails=Helper::sub_user_send_mail();
                $send_mails=count(Helper::sub_user_total_send_mail());
                if($total_send_mails<=$send_mails)
                {
                    echo 3;die();
                }
            }
            else
            {
                $total_send_mails=Helper::send_mail_access();
                $send_mails=count(Helper::total_send_mailed());
                if($send_mails>=$total_send_mails)
                {
                    echo 3;die;
                }
            }
            // print_r(Helper::current_cv_package());
            // echo "ss".Helper::send_mail_access();die;
            $jobseekers = User::whereIn('id', $arr_jobseekers)->get();
            if(Helper::sub_user_send_mail() >= count($jobseekers) || Helper::send_mail_access()>=count($jobseekers))
               {
            foreach($jobseekers as $jobseeker){
               
                    $emailId = $jobseeker->email;
                    $emails_sent = new Emails_sent();
                    $emails_sent->user_id_fk = $userId;
                    $emails_sent->job_seeker_id_fk = $jobseeker->id;
                    $emails_sent->msg_desc = $request->desc;
                    $emails_sent->source = 0;
                    $emails_sent->package_id = Helper::current_cv_package()->package_id;
                    $emails_sent->user_package_id = Helper::current_cv_user_package()->user_package_id;
                    $emails_sent->created_at = date("Y-m-d H:i:s");
                    $emails_sent->updated_at = date("Y-m-d H:i:s");
                    $emails_sent->save();
                    $mail_data = array(
                             'email' => $emailId,
                             'data' => $request->desc,
                         );
                    Mail::send('email.msg_tojseeker', $mail_data, function ($message) use ($mail_data) {
                                 $message->subject($this->mail_search_subject)
                                         ->from($this->search_employer_email)
                                         ->to($mail_data['email']);
                    });
               }
               } else{
                echo 5;die;
                
            }

            Helper::expire_package();

            echo 1;
            die();
        }
        //$jobseekers = Countries::get()->toArray();
    }
    public function searchlist_sendsms(Request $request){
        $jobseekerids =  $request->jsids;
        $arr_jobseekers = explode(",", $jobseekerids);
        $userId = Auth::user()->id;
        $employer_data = Employer::where('user_id_fk',$userId)->get();
        $employer_fullname = $employer_data[0]['first_name']." ".$employer_data[0]['last_name'];
        $sms_subject = 'New Message from Employer '.$employer_fullname;
        // 1st value in array is search id. 
        if(count($arr_jobseekers) > 0){
            $jobseekers = Job_seeker_personal_details::whereIn('user_id_fk', $arr_jobseekers)->get();
            foreach($jobseekers as $jobseeker){
                // $jobseeker->mobile="9895337514";
                if(!empty($jobseeker->mobile_number))
                {
                    $data['recipient_no']=$jobseeker->mobile_number;
                    $data['message']=$request->desc;
                    $result=Helper::sendSMS($data);
                    // dd($result);
                    if($result)
                    {
                        $emails_sent = new Sms_sent();
                        $emails_sent->user_id_fk = $userId;
                        $emails_sent->job_seeker_id_fk = $jobseeker->user_id_fk;
                        $emails_sent->msg_desc = $request->desc;
                        $emails_sent->source = 0;
                        $emails_sent->created_at = date("Y-m-d H:i:s");
                        $emails_sent->updated_at = date("Y-m-d H:i:s");
                        $emails_sent->save();
                    }
                }
               
                
            }
            echo 1;
            die();
        }
        //$jobseekers = Countries::get()->toArray();
    }
    public function job_response_sendmail(Request $request){
        $jobseekerids =  $request->jsids;
        $arr_jobseekers = explode(",", $jobseekerids);
        $apply_ids =  $request->aplyids;
        $arr_apply_id = explode(",", $apply_ids);
        $userId = Auth::user()->id;
        $employer_data = Employer::where('user_id_fk',$userId)->get();
        $employer_fullname = $employer_data[0]['first_name']." ".$employer_data[0]['last_name'];
        $this->search_employer_email = $employer_data[0]['email'];
        $this->mail_search_subject = 'New Message from Employer '.$employer_fullname;
        // 1st value in array is search id.
        // print_r($arr_jobseekers);die; 
        if(count($arr_jobseekers) > 0){
            $jobseekers = User::whereIn('id', $arr_jobseekers)->get();
            if(Helper::sub_user_send_mail() >= count($jobseekers) || Helper::send_mail_access()>=count($jobseekers))
               {
            foreach($jobseekers as $key => $jobseeker){
                $emailId = $jobseeker->email;
                $emails_sent = new Application_reply();
                $emails_sent->apply_id_fk = $arr_apply_id[$key];
                $emails_sent->reply_message = $request->desc;
                $emails_sent->active = 1;
                $emails_sent->created_at = date("Y-m-d H:i:s");
                $emails_sent->updated_at = date("Y-m-d H:i:s");
                $emails_sent->save();
                $mail_data = array(
                         'email' => $emailId,
                         'data' => $request->desc
                     );
                Mail::send('email.msg_tojseeker', $mail_data, function ($message) use ($mail_data) {
                             $message->subject($this->mail_search_subject)
                                     ->from($this->search_employer_email)
                                     ->to($mail_data['email']);
                });

            }
        }else{
          echo 5;
            die();  
        }
            echo 1;
            die();
        }
        //$jobseekers = Countries::get()->toArray();
    }
    public function cv_search(Request $request){
         //print_r($request->all());die;
        $user_id = Auth::user()->id;
        $cv_search =  new Cv_search();
        $searchType = $request->searchtype;
        $cv_search->search_type = $searchType;
        // Start - Default values for NOT NULL fields
        $cv_search->user_id_fk = $user_id;
        $cv_search->created_at = date("Y-m-d H:i:s");
        $cv_search->updated_at = date("Y-m-d H:i:s");
        //End - Default values for NOT NULL fields
        $cv_search->save();

        $id = $cv_search->cv_search_id;
        $cv_search_details = new Cv_search_details();

        if($searchType == 3){ // Advanced search
            // Start - Default values for NOT NULL fields
            $cv_search_details->search_id_fk = $id;
            $cv_search_details->created_at = date("Y-m-d H:i:s");
            $cv_search_details->updated_at = date("Y-m-d H:i:s");
            //End - Default values for NOT NULL fields
            $cv_search_details->keyword = $request->keywords;
            $cv_search_details->ex_keyword = $request->ex_keywords;
            $cv_search_details->total_resume = $request->cb_resume;
            $cv_search_details->min_exp = $request->min_exp_year;
            $cv_search_details->max_exp = $request->max_exp_year;
            $cv_search_details->min_sal = $request->min_sal;
            $cv_search_details->max_sal = $request->max_sal;
            $cv_search_details->currency_type = $request->currency_type;
            $cv_search_details->gender = $request->gender;
            $cv_search_details->cur_loc = $request->geo_adv_cloc;
            $cv_search_details->exp_loc = $request->geo_adv_ploc;
            $cv_search_details->nation = $request->nation;
            // $cv_search_details->jobtype = $request->hdn_course;
            $cv_search_details->languages = $request->hdn_langs;
            $cv_search_details->from_age = $request->age_range_from;
            $cv_search_details->to_age = $request->age_range_to;
            $cv_search_details->has_dl = $request->dl;
            $cv_search_details->vehicle_type = $request->vehicle_type;
            $cv_search_details->degree = $request->degree;
            $cv_search_details->specialization = $request->qualification;
            $cv_search_details->industry = $request->adv_indsutry;
            $cv_search_details->farea = $request->adv_farea;
            $cv_search_details->job_title = $request->job_title;
            $cv_search_details->notice_period = $request->notice_period;
            $cv_search_details->employer_name = $request->employer_name;
            $cv_search_details->certification = $request->hdn_certification;
            $cv_search_details->last_active = $request->last_active;
            $cv_search_details->last_updated = $request->last_updated;
            // $cv_search_details->verify_stat = $request->hdn_verification;
            if(!empty($request->verification_type))
                $cv_search_details->verify_stat = implode(',',$request->verification_type);
            if(!empty($request->marital_status))
                $cv_search_details->marital_status = implode(',',$request->marital_status);
            if(!empty($request->course_type))
            $cv_search_details->jobtype = implode(',',$request->course_type);
            $certification_name=array();
            foreach (explode(',',$request->hdn_certification) as $key => $value) {
                $certification_name[]=$request->certificate_name[$key];
            }
            $cv_search_details->certification_name=implode(',',$certification_name);
            $cv_search_details->page_no=$request->page_no;
            $cv_search_details->save();
            if($request->hdn_tech_skills != ""){
                $arr_tech_skills = json_decode($request->hdn_tech_skills);
            $tech_skills_cnt = count($arr_tech_skills);
            foreach ($arr_tech_skills as $skills_data) {
                        //echo $skills_data[$m]->skill.$m;
                         // Start - Default values for NOT NULL fields
                        $cv_search_techskills = new Cv_search_techskills();
                        $cv_search_techskills->search_id_fk = $id;
                        $cv_search_techskills->created_at = date("Y-m-d H:i:s");
                        $cv_search_techskills->updated_at = date("Y-m-d H:i:s");
                        //End - Default values for NOT NULL fields
                        $cv_search_techskills->skill = $skills_data->skill;
                        $cv_search_techskills->expertise = $skills_data->level;
                        $cv_search_techskills->experience = $skills_data->yoe;
                        $cv_search_techskills->last_used = $skills_data->ylu;
                        $cv_search_techskills->save();
                    }
            }
        }
        else if($searchType == 2){ // Quick search
            // Start - Default values for NOT NULL fields
            $cv_search_details->search_id_fk = $id;
            $cv_search_details->created_at = date("Y-m-d H:i:s");
            $cv_search_details->updated_at = date("Y-m-d H:i:s");
            //End - Default values for NOT NULL fields
            $cv_search_details->keyword = $request->keywords;
            $cv_search_details->ex_keyword = $request->ex_keywords;
            $cv_search_details->total_resume = $request->cb_resume;
            $cv_search_details->min_exp = $request->min_exp_year;
            $cv_search_details->max_exp = $request->max_exp_year;
            $cv_search_details->min_sal = $request->min_sal;
            $cv_search_details->max_sal = $request->max_sal;
            $cv_search_details->currency_type = $request->currency_type;
            $cv_search_details->cur_loc = $request->geo_quick_cloc;
            $cv_search_details->industry = $request->quick_industry;
            $cv_search_details->farea = $request->quick_farea;
            $cv_search_details->nation = $request->nation;
            $cv_search_details->last_active = $request->last_active;
            $cv_search_details->last_updated = $request->last_updated;
            $cv_search_details->verify_stat = $request->hdn_verification;
            $cv_search_details->visa_status = $request->visa_status;
            $cv_search_details->notice_period = $request->notice_period;
            if(!empty($request->marital_status))
            $cv_search_details->marital_status = implode(',',$request->marital_status);
            if(!empty($request->job_type))
            $cv_search_details->jobtype = implode(',',$request->job_type);
        $cv_search_details->page_no=$request->page_no;
        //print_r($cv_search_details);exit();
            $cv_search_details->save();
        }
        else{
            // Start - Default values for NOT NULL fields
            $cv_search_details->search_id_fk = $id;
            $cv_search_details->created_at = date("Y-m-d H:i:s");
            $cv_search_details->updated_at = date("Y-m-d H:i:s");
            //End - Default values for NOT NULL fields
            $cv_search_details->keyword = $request->keywords;
            $cv_search_details->min_exp = $request->min_exp_year;
            $cv_search_details->max_exp = $request->max_exp_year;
            $cv_search_details->cur_loc = $request->geo_adv_cloc;
            $cv_search_details->exp_loc = "";
            $cv_search_details->page_no=$request->page_no;
            $cv_search_details->save();
        }
        $enrypt_id =  Crypt::encryptString($id);
        $response =  array("status" => 1,
        "searchId" => $enrypt_id );
        //print_r($response); exit();
        echo json_encode($response);
        die();
    }
    public function post_job(Request $request){
       // print_r($request->all());exit();
        $user_id = Auth::user()->id;
        $job_post = new Job_post();
        $job_post->user_id_fk = $user_id;
        $job_post->jp_type = $request->jp_type;
        // Start - Default values for NOT NULL fields
        $job_post->employer_address_2 = "";
        $job_post->featured_job = 1;
        $job_post->interview_venue = "";
        $job_post->notice_period = "";
        $job_post->walk_in_interview = 2;
        $job_post->company_logo = "";
        //$job_post->interview_from_date = "";
        //$job_post->interview_to_date = "";
        $job_post->type = $request->type;
        $job_post->company_url = "";
        $job_post->job_alert = 1;
        $job_post->top_search = 1;
        $job_post->job_bolding = 1;
        $job_post->social_media = 1;
        $job_post->job_response_email = 1;
        $job_post->qualification_degree = "";
        
        if(!empty($request->confidential))
        {
            $job_post->confidential = $request->confidential;
        }else{
            $job_post->confidential = 1;
        }
        //$job_post->country_id_fk = "";
        // /$job_post->state_id_fk = "";
        //print_r(Helper::current_user_package());exit();
        $job_post->status = 1;
         
        
        $job_post->created_at = date("Y-m-d H:i:s");
        $job_post->updated_at = date("Y-m-d H:i:s");
        //End - Default values for NOT NULL fields
        if($request->jp_type == 1){
            //Start - Employer Info details
            $job_post->employer_company_name = $request->company_name;
            $job_post->employer_designation = $request->designation;
            $job_post->employer_industry_type = $request->industry;
            $job_post->employer_address_1 = $request->office_address;
            $job_post->employer_website_url = $request->website_url;
            $job_post->office_landline = $request->landline;
            $job_post->about_company = $request->about_company;
            
            $current_date = date("Y-m-d H:i:s");
            //print_r(Free_jobpost_validity::first());exit();
            $exp_days =  Free_jobpost_validity::first()->days;
            $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            //print_r($exp_days);exit();
            //End - Employer Info details
            //Start - Recruiter Info details
            $job_post->recruiter_user_name = "";
            //$job_post->recruiter_user_id = "";
            $job_post->recruiter_designation = "";
            $job_post->recruiter_mobile = "";
            $job_post->recruiter_office_landline = "";
            //End - Recruiter Info details
            //Start - Job description details
            $job_post->job_title = $request->job_title;
            $job_post->no_of_vacancy = $request->vacancy_no;
            $job_post->job_description = $request->job_desptn;
            $job_post->primary_responsibilities = $request->job_duties;
            $job_post->other_requirements = $request->other_req;
            $xyz = explode(",", $request->locs);
            $location = implode("|", $xyz);
            $xyz = explode(".", $location);
            // dd(implode(",", $xyz));
            $job_post->location = implode(",", $xyz);
            //$job_post->location = $request->jpfree_job_location;
            $areaarr = ""; $cityarr = ""; $statearr = ""; $countryarr = "";
            $locsarr = explode(".", $request->locs);
            foreach($locsarr as $locarr){
                $placearr = explode(",", $locarr);
                $placecnt = count($placearr);
                if($placecnt == 4){
                     $areaarr = $areaarr.",".$placearr[0];
                     $cityarr = $cityarr.",".$placearr[1];
                     $statearr = $statearr.",".$placearr[2];
                     $countryarr = $countryarr.",".$placearr[3];
                }
                else if($placecnt == 3){
                     $cityarr = $cityarr.",".$placearr[0];
                     $statearr = $statearr.",".$placearr[1];
                     $countryarr = $countryarr.",".$placearr[2];
                }
                else if($placecnt == 2){
                     $statearr = $statearr.",".$placearr[0];
                     $countryarr = $countryarr.",".$placearr[1];
                }
                else{
                     $countryarr = $countryarr.",".$placearr[0];
                }
            }
            $job_post->loc_area =  $areaarr;
            $job_post->loc_city =  $cityarr;
            $job_post->loc_state =  $statearr;
            $job_post->loc_country =  $countryarr;

            $job_post->other_benefits = $request->other_benifits;
            $salary = $request->min_salary."-".$request->max_salary;
            $job_post->currency_type = $request->currency_type;
            $job_post->salary_min = $request->min_salary;
            $job_post->salary_max = $request->max_salary;
            $job_post->salary_per_month = $salary;
            $job_post->hide_salary = $request->jpfree_hide_sal;
            //End - Job description details
            //Start - Desired candidate details
            $industry = new IndustryType();
            //dd(IndustryType::where('industry_type_name',$request->jpfree_indsutry)->first());
            if($request->jpfree_indsutry == "0" && (!empty($request->jpfree_indsutry1)))
            {
                if(!IndustryType::where('industry_type_name','LIKE','%'.$request->jpfree_indsutry1.'%')->get() )
                {
                    $industry->industry_type_name = $request->jpfree_indsutry1;
                    $industry->save();
                }
                
                $job_post->industry_type = $request->jpfree_indsutry1;
            }else{
                $job_post->industry_type = $request->jpfree_indsutry;
                $industry=IndustryType::where('industry_type_name',$request->jpfree_indsutry)->first();
            }

            $area = new SubIndustryType();
            //dd(SubIndustryType::where('sub_industry_type_name','LIKE','%'.$request->jpfree_farea1.'%')->get());
            if($request->jpfree_farea == "0" && (!empty($request->jpfree_farea1))){
                //dd(SubIndustryType::where('sub_industry_type_name','LIKE','%'.$request->jpfree_farea1.'%')->count());
                if(SubIndustryType::where('sub_industry_type_name','LIKE','%'.$request->jpfree_farea1.'%')->count() == 0 )
                {
                    //dd($industry);
                    $area->sub_industry_type_name = $request->jpfree_farea1;
                    $area->industry_type_id_fk = $industry->industry_type_id;
                    $area->save();
                }
                $job_post->functional_area = $request->jpfree_farea1;
            }else{
                $job_post->functional_area = $request->jpfree_farea;
            }
            $job_post->min_experience = $request->min_exp_year;
            $job_post->max_experience = $request->max_exp_year;
            $job_post->nationality = $request->nation;
            $job_post->candidate_current_location = $request->geo_jpfree_cloc;
            $job_post->gender = $request->gender;
            $job_post->marital_status = 1;
            $job_post->job_type = $request->job_type;
            $job_post->visa_status = $request->visa_status;

            if($request->visa_status == "1")
            {
                if($request->visa_type == "Other")
                {
                    $job_post->visa_type = $request->other_visa_status;
                }else{
                    $job_post->visa_type = $request->visa_type;
                }
            }
            //End - Desired candidate details
            //Start - Required candidate details
            //$job_post->qualification_basic = $request->jpfree_basic_course.",".$request->jpfree_basic_branch;

           /* $job_post->qualification_degree = $request->jpfree_ug_course.",".$request->jpfree_ug_branch;
            $job_post->qualification_pg = $request->jpfree_pg_course.",".$request->jpfree_pg_branch;
            $job_post->qualification_expertise = $request->jpfree_adv_course.",".$request->jpfree_adv_branch;*/

            $job_post->qualification_other =  $request->jpfree_other_course.",".$request->jpfree_other_branch;


            $ug_course = new Course();
            $ug_spe = new Specialization();
            
            if($request->jpfree_ug_course == "0" && !empty($request->jpfree_ug_course1))
            {    
                $ug_course->course_name = $request->jpfree_ug_course1;
                $ug_course->save();
                if($request->jpfree_ugbranch == "0" && !empty($request->jpfree_ug_branch1))
                {
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->jpfree_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->jpfree_ug_course1.",".$request->jpfree_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->jpfree_ug_course1.",".$request->jpfree_ugbranch;
                }
                
            }elseif(!empty($request->jpfree_ug_course)){
                if($request->jpfree_ugbranch == "0" && !empty($request->jpfree_ug_branch1))
                {

                	$ug_course=Course::where('course_name',$request->jpfree_ug_course)->first();
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->jpfree_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->jpfree_ug_course.",".$request->jpfree_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->jpfree_ug_course.",".$request->jpfree_ugbranch;
                }
            }
            $pg_course = new PGCourse();
            $pg_spe = new PGSpecialization();
            if($request->jpfree_pg_course == "0" && !empty($request->jpfree_pg_course1))
            {    
                $pg_course->pgc_name = $request->jpfree_pg_course1;
                $pg_course->save();
                if($request->jpfree_pgbranch == "0" && !empty($request->jpfree_pg_branch1))
                {
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->jpfree_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->jpfree_pg_course1.",".$request->jpfree_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->jpfree_pg_course1.",".$request->jpfree_pgbranch;
                }
                
            }elseif(!empty($request->jpfree_pg_course)){
                if($request->jpfree_pgbranch == "0" && !empty($request->jpfree_pg_branch1))
                {
                	$pg_course=PGCourse::where('pgc_name',$request->jpfree_pg_course)->first();
                    $pg_spe->pgc_id_fk = $ug_course->pgc_id;
                    $pg_spe->pgs_name = $request->jpfree_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->jpfree_pg_course.",".$request->jpfree_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->jpfree_pg_course.",".$request->jpfree_pgbranch;
                }
            }

            $h_course = new HighestCourse();
            $h_spe = new HighestSpecialization();
            if($request->jpfree_adv_course == "0" && !empty($request->jpfree_adv_course1))
            {    
                $h_course->course_name = $request->jpfree_adv_course1;
                $h_course->save();
                if($request->jpfree_adv_branch == "0" && !empty($request->jpfree_adv_branch1))
                {
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->jpfree_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->jpfree_adv_course1.",".$request->jpfree_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->jpfree_adv_course1.",".$request->jpfree_adv_branch;
                }
                
            }elseif(!empty($request->jpfree_adv_course)){
                if($request->jpfree_adv_branch == "0" && !empty($request->jpfree_adv_branch1))
                {
                	$h_course=HighestCourse::where('course_name',$request->jpfree_adv_course)->first();
                    $h_spe->hc_id_fk = $ug_course->course_id;
                    $h_spe->hs_name = $request->jpfree_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->jpfree_adv_course.",".$request->jpfree_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->jpfree_adv_course.",".$request->jpfree_adv_branch;
                }
            }

            $job_post->language = $request->langs;
            $job_post->from_age = $request->age_from;
            $job_post->to_age = $request->age_to;
            $job_post->driving_licence = $request->jfree_drlic;
            $job_post->vehicle_type = $request->vehicle_type;
            $job_post->job_response_email = $request->jpfree_responses;
            //End - Required candidate details
        }
        else if($request->jp_type == 3){  // Saving Regular Pack details
            //Start - Employer Info details
            $job_package=Helper::current_job_package()->package_id;
            $user_pack_id = Helper::current_user_package()->user_package_id;
            $job_post->package_id = $job_package;
            $job_post->user_package_id = $user_pack_id;

            $job_post->employer_company_name = $request->company_name;
            $job_post->employer_designation = $request->designation;
            $job_post->employer_industry_type = $request->industry;
            $job_post->employer_address_1 = $request->office_address;
            $job_post->employer_website_url = $request->website_url;
            $job_post->office_landline = $request->landline;
            $job_post->about_company = $request->about_company;
            //$job_post->job_expire = date("Y-m-d",strtotime($request->job_expire));  
            $current_date = date("Y-m-d H:i:s");
            
            if(Helper::current_job_package()->type == 1){
                $exp_days = Helper::current_job_package()->saver_pack->job_expire;
                $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            }elseif(Helper::current_job_package()->type == 3){
                $exp_days = Helper::current_job_package()->job_post_pack->job_expire;
                $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            }elseif (Helper::current_job_package()->type == 4) {
                $exp_days = Helper::current_job_package()->branding_pack->job_expire;
                $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            }          
            //End - Employer Info details
            //Start - Recruiter Info details
            $job_post->recruiter_user_name = $request->hr_name;
            $job_post->recruiter_user_id = $request->hr_id;
            $job_post->recruiter_designation = $request->hr_desgn;
            $job_post->recruiter_mobile = $request->hr_mobile;
            $job_post->recruiter_office_landline = $request->hr_landline;
            //End - Recruiter Info details
            //Start - Job description details
            $job_post->job_title = $request->job_title;
            $job_post->no_of_vacancy = $request->vacancy_no;
            $job_post->job_description = $request->job_desptn;
            $job_post->primary_responsibilities = $request->job_duties;
            $job_post->other_requirements = $request->other_req;
            $xyz = explode(",", $request->locs);
            $location = implode("|", $xyz);
            $xyz = explode(".", $location);
            // dd(implode(",", $xyz));
            $job_post->location = implode(",", $xyz);
            //$job_post->location = $request->regpack_job_location;
            $areaarr = ""; $cityarr = ""; $statearr = ""; $countryarr = "";
            $locsarr = explode(".", $request->locs);
            foreach($locsarr as $locarr){
                $placearr = explode(",", $locarr);
                $placecnt = count($placearr);
                if($placecnt == 4){
                     $areaarr = $areaarr.",".$placearr[0];
                     $cityarr = $cityarr.",".$placearr[1];
                     $statearr = $statearr.",".$placearr[2];
                     $countryarr = $countryarr.",".$placearr[3];
                }
                else if($placecnt == 3){
                     $cityarr = $cityarr.",".$placearr[0];
                     $statearr = $statearr.",".$placearr[1];
                     $countryarr = $countryarr.",".$placearr[2];
                }
                else if($placecnt == 2){
                     $statearr = $statearr.",".$placearr[0];
                     $countryarr = $countryarr.",".$placearr[1];
                }
                else{
                     $countryarr = $countryarr.",".$placearr[0];
                }
            }
            $job_post->loc_area =  $areaarr;
            $job_post->loc_city =  $cityarr;
            $job_post->loc_state =  $statearr;
            $job_post->loc_country =  $countryarr;

            $job_post->other_benefits = $request->other_benifits;
            $salary = $request->min_salary."-".$request->max_salary;
            $job_post->salary_per_month = $salary;
            $job_post->currency_type = $request->currency_type;
            $job_post->salary_min = $request->min_salary;
            $job_post->salary_max = $request->max_salary;
            $job_post->job_type = $request->job_type;
            $job_post->hide_salary = $request->regpack_hide_sal;
            //End - Job description details
            //Start - Desired candidate details
           // $job_post->industry_type = $request->regpack_indsutry;
           // $job_post->functional_area = $request->regpack_farea;

            $industry = new IndustryType();
            if($request->regpack_indsutry == "0" && !empty($request->regpack_indsutry1)){
                if(!IndustryType::where('industry_type_name','like','%'.$request->regpack_indsutry1.'%')->get() )
                {
                    $industry->industry_type_name = $request->regpack_indsutry1;
                    $industry->save();
                }
                $job_post->industry_type = $request->regpack_indsutry1;
                // $industry_type_id_fk=$industry->industry_type_id;
            }elseif(!empty($request->regpack_indsutry)){
                $job_post->industry_type = $request->regpack_indsutry;
                $industry=IndustryType::where('industry_type_name',$request->regpack_indsutry)->first();
            }

            $area = new SubIndustryType();
            if($request->regpack_farea == "0" && !empty($request->regpack_farea1)){
                if(SubIndustryType::where('sub_industry_type_name','like','%'.$request->regpack_farea1.'%')->count() == 0 )
                {
                    $area->sub_industry_type_name = $request->regpack_farea1;
                    $area->industry_type_id_fk = $industry->industry_type_id;
                    $area->save();
                }
                $job_post->functional_area = $request->regpack_farea1;
            }else{
                $job_post->functional_area = $request->regpack_farea;
            }

            $job_post->min_experience = $request->min_exp_year;
            $job_post->max_experience = $request->max_exp_year;
            $job_post->nationality = $request->nation;
            $job_post->candidate_current_location = $request->geo_regpack_cloc;
            $job_post->gender = $request->gender;
            $job_post->marital_status = $request->marital_status;
            $job_post->from_age = $request->age_from;
            $job_post->to_age = $request->age_to;
            $job_post->visa_status = $request->visa_status;
            if($request->visa_status == "1")
            {
                if($request->visa_type == "Other")
                {
                    $job_post->visa_type = $request->other_visa_status;
                }else{
                    $job_post->visa_type = $request->visa_type;
                }
            }
            //End - Desired candidate details
            //Start - Required candidate details
           // $job_post->qualification_basic = $request->regpack_basic_course.",".$request->regpack_basic_branch;
            /*$job_post->qualification_degree = $request->regpack_ug_course.",".$request->regpack_ug_branch;
            $job_post->qualification_pg = $request->regpack_pg_course.",".$request->regpack_pg_branch;
            $job_post->qualification_expertise = $request->regpack_adv_course.",".$request->regpack_adv_branch;*/

            $ug_course = new Course();
            $ug_spe = new Specialization();
            
            if($request->regpack_ug_course == "0" && !empty($request->regpack_ug_course1))
            {    
                $ug_course->course_name = $request->regpack_ug_course1;
                $ug_course->save();
                if($request->regpack_ugbranch == "0" && !empty($request->regpack_ug_branch1))
                {
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->regpack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->regpack_ug_course1.",".$request->regpack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->regpack_ug_course1.",".$request->regpack_ugbranch;
                }
                
            }elseif(!empty($request->regpack_ug_course)){
                if($request->regpack_ugbranch == "0" && !empty($request->regpack_ug_branch1))
                {
                	$ug_course=Course::where('course_name',$request->regpack_ug_course)->first();
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->regpack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->regpack_ug_course.",".$request->regpack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->regpack_ug_course.",".$request->regpack_ugbranch;
                }
            }
            $pg_course = new PGCourse();
            $pg_spe = new PGSpecialization();
            if($request->regpack_pg_course == "0" && !empty($request->regpack_pg_course1))
            {    
                $pg_course->pgc_name = $request->regpack_pg_course1;
                $pg_course->save();
                if($request->regpack_pgbranch == "0" && !empty($request->regpack_pg_branch1))
                {
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->regpack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->regpack_pg_course1.",".$request->regpack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->regpack_pg_course1.",".$request->regpack_pgbranch;
                }
                
            }elseif(!empty($request->regpack_pg_course)){
                if($request->regpack_pgbranch == "0" && !empty($request->regpack_pg_branch1))
                {
                	$pg_course=PGCourse::where('pgc_name',$request->regpack_pg_course)->first();
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->regpack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->regpack_pg_course.",".$request->regpack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->regpack_pg_course.",".$request->regpack_pgbranch;
                }
            }

            $h_course = new HighestCourse();
            $h_spe = new HighestSpecialization();
            if($request->regpack_adv_course == "0" && !empty($request->regpack_adv_course1))
            {    
                $h_course->course_name = $request->regpack_adv_course1;
                $h_course->save();
                if($request->regpack_adv_branch == "0" && !empty($request->regpack_adv_branch1))
                {
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->regpack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->regpack_adv_course1.",".$request->regpack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->regpack_adv_course1.",".$request->regpack_adv_branch;
                }
                
            }elseif(!empty($request->regpack_adv_course)){
                if($request->regpack_adv_branch == "0" && !empty($request->regpack_adv_branch1))
                {
                	$h_course=HighestCourse::where('course_name',$request->regpack_adv_course)->first();
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->regpack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->regpack_adv_course.",".$request->regpack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->regpack_adv_course.",".$request->regpack_adv_branch;
                }
            }


            $job_post->qualification_other =  $request->regpack_other_course.",".$request->regpack_other_branch;
            $job_post->language = $request->langs;
            $job_post->driving_licence = $request->regpack_drlic;
            $job_post->vehicle_type = $request->vehicle_type;
            $job_post->notice_period = $request->notice_prd;
            $job_post->walk_in_interview = $request->regpack_walkin_interview;
            if($request->regpack_walkin_interview == 1)
            {
                $job_post->interview_from_date = $request->date_from.",".$request->fromtime;
                $job_post->interview_to_date = $request->date_to.",".$request->totime;
                $job_post->interview_venue = $request->int_venue;
            }else{
                $job_post->interview_from_date = ",";
                $job_post->interview_to_date = ",";
                $job_post->interview_venue = "";
            }
            

            $job_post->job_response_email = $request->regpack_responses;
            $job_post->company_url = $request->comp_url;
            //End - Required candidate details
        }
        else if($request->jp_type == 2){  // Saving Enterprise Pack details
            //Start - Employer Info details
            $job_package=Helper::current_job_package()->package_id;
            $user_pack_id = Helper::current_user_package()->user_package_id;
            $job_post->package_id = $job_package;
            $job_post->user_package_id = $user_pack_id;

            $job_post->employer_company_name = $request->company_name;
            $job_post->employer_designation = $request->designation;
            $job_post->employer_industry_type = $request->industry;
            $job_post->employer_address_1 = $request->office_address;
            $job_post->employer_website_url = $request->website_url;
            $job_post->office_landline = $request->landline;
            $job_post->about_company = $request->about_company;
            //$job_post->job_expire = date("Y-m-d",strtotime($request->job_expire));
            $current_date = date("Y-m-d H:i:s");
            
            if(Helper::current_job_package()->type == 1){
                $exp_days = Helper::current_job_package()->saver_pack->job_expire;
                $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            }elseif(Helper::current_job_package()->type == 3){
                $exp_days = Helper::current_job_package()->job_post_pack->job_expire;
                $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            }elseif (Helper::current_job_package()->type == 4) {
                $exp_days = Helper::current_job_package()->branding_pack->job_expire;
                $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            }
            //End - Employer Info details
            //Start - Recruiter Info details
            $job_post->recruiter_user_name = $request->hr_name;
            $job_post->recruiter_user_id = $request->hr_id;
            $job_post->recruiter_designation = $request->hr_desgn;
            $job_post->recruiter_mobile = $request->hr_mobile;
            $job_post->recruiter_office_landline = $request->hr_landline;
            //End - Recruiter Info details
            //Start - Job description details
            $job_post->job_title = $request->job_title;
            $job_post->no_of_vacancy = $request->vacancy_no;
            $job_post->job_description = $request->job_desptn;
            $job_post->primary_responsibilities = $request->job_duties;
            $job_post->other_requirements = $request->other_req;
            $xyz = explode(",", $request->locs);
            $location = implode("|", $xyz);
            $xyz = explode(".", $location);
            // dd(implode(",", $xyz));
            $job_post->location = implode(",", $xyz);
            //$job_post->location = $request->propack_job_location;
            $areaarr = ""; $cityarr = ""; $statearr = ""; $countryarr = "";
            $locsarr = explode(".", $request->locs);
            foreach($locsarr as $locarr){
                $placearr = explode(",", $locarr);
                $placecnt = count($placearr);
                if($placecnt == 4){
                     $areaarr = $areaarr.",".$placearr[0];
                     $cityarr = $cityarr.",".$placearr[1];
                     $statearr = $statearr.",".$placearr[2];
                     $countryarr = $countryarr.",".$placearr[3];
                }
                else if($placecnt == 3){
                     $cityarr = $cityarr.",".$placearr[0];
                     $statearr = $statearr.",".$placearr[1];
                     $countryarr = $countryarr.",".$placearr[2];
                }
                else if($placecnt == 2){
                     $statearr = $statearr.",".$placearr[0];
                     $countryarr = $countryarr.",".$placearr[1];
                }
                else{
                     $countryarr = $countryarr.",".$placearr[0];
                }
            }
            $job_post->loc_area =  $areaarr;
            $job_post->loc_city =  $cityarr;
            $job_post->loc_state =  $statearr;
            $job_post->loc_country =  $countryarr;
            
            $job_post->other_benefits = $request->other_benifits;
            $salary = $request->min_salary."-".$request->max_salary;
            $job_post->salary_per_month = $salary;
            $job_post->currency_type = $request->currency_type;
            $job_post->salary_min = $request->min_salary;
            $job_post->salary_max = $request->max_salary;
            $job_post->job_type = $request->job_type;
            $job_post->hide_salary = $request->propack_hide_sal;
            //End - Job description details
            //Start - Desired candidate details
           // $job_post->industry_type = $request->propack_indsutry;
           // $job_post->functional_area = $request->propack_farea;

            $industry = new IndustryType();
            if($request->propack_indsutry == "0" && !empty($request->propack_indsutry1)){
                if(!IndustryType::where('industry_type_name','like','%'.$request->propack_indsutry1.'%')->get() )
                {
                    $industry->industry_type_name = $request->propack_indsutry1;
                    $industry->save();
                }
                $job_post->industry_type = $request->propack_indsutry1;
            }else{
                $job_post->industry_type = $request->propack_indsutry;
                $industry=IndustryType::where('industry_type_name',$request->propack_indsutry)->first();
            }
            $area = new SubIndustryType();
            if($request->propack_farea == "0" && !empty($request->propack_farea1)){
                if(SubIndustryType::where('sub_industry_type_name','like','%'.$request->propack_farea1.'%')->count() == 0 )
                {
                    $area->sub_industry_type_name = $request->propack_farea1;
                    $area->industry_type_id_fk = $industry->industry_type_id;
                    $area->save();
                }
                $job_post->functional_area = $request->propack_farea1;
            }else{
                $job_post->functional_area = $request->propack_farea;
            }

            $job_post->min_experience = $request->min_exp_year;
            $job_post->max_experience = $request->max_exp_year;
            $job_post->nationality = $request->nation;
            $job_post->candidate_current_location = $request->geo_propack_cloc;
            $job_post->gender = $request->gender;
            $job_post->marital_status = $request->marital_status;
            $job_post->from_age = $request->age_from;
            $job_post->to_age = $request->age_to;
            $job_post->visa_status = $request->visa_status;
            if($request->visa_status == "1")
            {
                if($request->visa_type == "Other")
                {
                    $job_post->visa_type = $request->other_visa_status;
                }else{
                    $job_post->visa_type = $request->visa_type;
                }
            }
            //End - Desired candidate details
            //Start - Required candidate details
            //$job_post->qualification_basic = $request->propack_basic_course.",".$request->propack_basic_branch;
           /* $job_post->qualification_degree = $request->propack_ug_course.",".$request->propack_ug_branch;
            $job_post->qualification_pg = $request->propack_pg_course.",".$request->propack_pg_branch;
            $job_post->qualification_expertise = $request->propack_adv_course.",".$request->propack_adv_branch;*/


            $ug_course = new Course();
            $ug_spe = new Specialization();
            
            if($request->propack_ug_course == "0" && (!empty($request->propack_ug_course1)))
            {    
                $ug_course->course_name = $request->propack_ug_course1;
                $ug_course->save();
                if($request->propack_ugbranch == "0" && !empty($request->propack_ug_branch1))
                {
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->propack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->propack_ug_course1.",".$request->propack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->propack_ug_course1.",".$request->propack_ugbranch;
                }
                
            }else{
                if($request->propack_ugbranch == "0" && !empty($request->propack_ug_branch1))
                {
                	$ug_course=Course::where('course_name',$request->propack_ug_course)->first();
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->propack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->propack_ug_course.",".$request->propack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->propack_ug_course.",".$request->propack_ugbranch;
                }
            }
            $pg_course = new PGCourse();
            $pg_spe = new PGSpecialization();
            if($request->propack_pg_course == "0" && !empty($request->propack_pg_course1))
            {    
                $pg_course->pgc_name = $request->propack_pg_course1;
                $pg_course->save();
                if($request->propack_pgbranch == "0" && !empty($request->propack_pg_branch1))
                {
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->propack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->propack_pg_course1.",".$request->propack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->propack_pg_course1.",".$request->propack_pgbranch;
                }
                
            }else{
                if($request->propack_pgbranch == "0" && !empty($request->propack_pg_branch1))
                {
                	$pg_course=PGCourse::where('pgc_name',$request->propack_pg_course)->first();
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->propack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->propack_pg_course.",".$request->propack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->propack_pg_course.",".$request->propack_pgbranch;
                }
            }

            $h_course = new HighestCourse();
            $h_spe = new HighestSpecialization();
            if($request->propack_adv_course == "0" && !empty($request->propack_adv_course1))
            {    
                $h_course->course_name = $request->propack_adv_course1;
                $h_course->save();
                if($request->propack_adv_branch == "0" && !empty($request->propack_adv_branch1))
                {
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->propack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->propack_adv_course1.",".$request->propack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->propack_adv_course1.",".$request->propack_adv_branch;
                }
                
            }else{
                if($request->propack_adv_branch == "0" && !empty($request->propack_adv_branch1))
                {
                	$h_course=HighestCourse::where('course_name',$request->propack_adv_course)->first();
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->propack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->propack_adv_course.",".$request->propack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->propack_adv_course.",".$request->propack_adv_branch;
                }
            }




            $job_post->qualification_other =  $request->propack_other_course.",".$request->propack_other_branch;
            $job_post->language = $request->langs;
            $job_post->driving_licence = $request->propack_drlic;
            $job_post->vehicle_type = $request->vehicle_type;
            $job_post->notice_period = $request->notice_prd;
            $job_post->walk_in_interview = $request->propack_walkin_interview;
            /*$job_post->interview_from_date = $request->date_from.",".$request->fromtime;
            $job_post->interview_to_date = $request->date_to.",".$request->totime;
            $job_post->interview_venue = $request->int_venue;*/
            if($request->propack_walkin_interview == 1)
            {
                $job_post->interview_from_date = $request->date_from.",".$request->fromtime;
                $job_post->interview_to_date = $request->date_to.",".$request->totime;
                $job_post->interview_venue = $request->int_venue;
            }else{
                $job_post->interview_from_date = ",";
                $job_post->interview_to_date = ",";
                $job_post->interview_venue = "";
            }

            //Company Logo company_logo
            $job_post->job_response_email = $request->propack_responses;
            $job_post->job_alert = $request->job_alert;
            $job_post->top_search = $request->top_search_list;
            $job_post->job_bolding = $request->bold_job;
            $job_post->company_url = $request->comp_url;
            if(!empty($request->job_featured)){
                $job_post->featured_job = $request->job_featured;
            }
            $job_post->social_media = $request->entr_social_media;
            if($request->hasFile('comp_logo'))
            {
                $destination = 'uploads/company_logo';  
                $file = $request->file('comp_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                $job_post->company_logo = $filelocation;
            }
            //End - Required candidate details
        }
        else{

        }
        //print_r($job_post);exit();
        $job_post->save();
        $jobpost_id = $job_post->job_id;

        if($request->tech_skills != ""){
            $arr_tech_skills = json_decode($request->tech_skills);
            $tech_skills_cnt = count($arr_tech_skills);
            Job_post_keyskills::where('job_id_fk',$jobpost_id)->delete();
            foreach ($arr_tech_skills as $skills_data) {
                    // print_r($skills_data->skill);
                    // echo $skills_data[$m]->skill.$m;
                     // Start - Default values for NOT NULL fields
                    $jp_keyskills = new Job_post_keyskills();
                    $jp_keyskills->job_id_fk = $jobpost_id;
                    $jp_keyskills->created_at = date("Y-m-d H:i:s");
                    $jp_keyskills->updated_at = date("Y-m-d H:i:s");
                    //End - Default values for NOT NULL fields
                    $jp_keyskills->skill = $skills_data->skill;
                    $jp_keyskills->expertise = $skills_data->level;
                    $jp_keyskills->experience = $skills_data->yoe;
                    $jp_keyskills->last_used = $skills_data->ylu;
                    $jp_keyskills->save();
            }
        }
        /*if(Employer::where('user_id_fk',Auth::user()->id)->where('parent_id',0)->first()){
            if( Helper::job_post_access() == count(Helper::total_job_posted()))
            {
               $pack = Helper::current_user_package()->user_package_id;
               $user =User_package::where('user_id_fk',Auth::user()->id)->where('user_package_id',$pack)->first();
               $user->expiry_date = date('Y-m-d');
               $user->status = 3;
               $user->save();
            }
        }*/
        Helper::expire_package();
        
        if($request->type == 1){
            echo 1; die();
        }
        elseif($request->type == 2){
            echo 2; die();
        }
    }

    public function post_job_edit(Request $request){
       //  $arr=$request->tech_skills;
      // print_r($request->confidential);exit();
       // print_r(2);die();
        $user_id = Auth::user()->id;
        $job_post = Job_post::where('job_id',$request->job_id)->first();
        $job_post->user_id_fk = $user_id;
        $job_post->jp_type = $request->jp_type;
        // Start - Default values for NOT NULL fields
        $job_post->employer_address_2 = "";
        $job_post->featured_job = 1;
        $job_post->interview_venue = "";
        $job_post->notice_period = "";
        $job_post->walk_in_interview = 2;
        $job_post->company_logo = "";
        //$job_post->interview_from_date = "";
        //$job_post->interview_to_date = "";
        $job_post->type = $request->type;
        $job_post->company_url = "";
        $job_post->job_alert = 1;
        $job_post->top_search = 1;
        $job_post->job_bolding = 1;
        $job_post->social_media = 1;
        $job_post->job_response_email = 1;
        $job_post->qualification_degree = "";
        //$job_post->confidential = $request->confidential;
        if(!empty($request->confidential))
        {
            $job_post->confidential = $request->confidential;
        }else{
            $job_post->confidential = 1;
        }
        //$job_post->country_id_fk = "";
        // /$job_post->state_id_fk = "";
        $job_post->status = 1;
        //$job_post->created_at = date("Y-m-d H:i:s");
        //$job_post->updated_at = date("Y-m-d H:i:s");
        //End - Default values for NOT NULL fields
        if($request->jp_type == 1){
            //Start - Employer Info details
            $job_post->employer_company_name = $request->company_name;
            $job_post->employer_designation = $request->designation;
            $job_post->employer_industry_type = $request->industry;
            $job_post->employer_address_1 = $request->office_address;
            $job_post->employer_website_url = $request->website_url;
            $job_post->office_landline = $request->landline;
            $job_post->about_company = $request->about_company;

            $current_date = date("Y-m-d H:i:s");
            $exp_days = 30;
            $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
            //$job_post->job_expire = date("Y-m-d",strtotime($request->job_expire));
            //End - Employer Info details
            //Start - Recruiter Info details
            $job_post->recruiter_user_name = "";
            //$job_post->recruiter_user_id = "";
            $job_post->recruiter_designation = "";
            $job_post->recruiter_mobile = "";
            $job_post->recruiter_office_landline = "";
            //End - Recruiter Info details
            //Start - Job description details
            $job_post->job_title = $request->job_title;
            $job_post->no_of_vacancy = $request->vacancy_no;
            $job_post->job_description = $request->job_desptn;
            $job_post->primary_responsibilities = $request->job_duties;
            $job_post->other_requirements = $request->other_req;
            $xyz = explode(",", $request->locs);
            $location = implode("|", $xyz);
            $xyz = explode(".", $location);
            // dd(implode(",", $xyz));
            $job_post->location = implode(",", $xyz);
            //$job_post->location = $request->jpfree_job_location;
            $areaarr = ""; $cityarr = ""; $statearr = ""; $countryarr = "";
            $locsarr = explode(".", $request->locs);
            foreach($locsarr as $locarr){
                $placearr = explode(",", $locarr);
                $placecnt = count($placearr);
                if($placecnt == 4){
                     $areaarr = $areaarr.",".$placearr[0];
                     $cityarr = $cityarr.",".$placearr[1];
                     $statearr = $statearr.",".$placearr[2];
                     $countryarr = $countryarr.",".$placearr[3];
                }
                else if($placecnt == 3){
                     $cityarr = $cityarr.",".$placearr[0];
                     $statearr = $statearr.",".$placearr[1];
                     $countryarr = $countryarr.",".$placearr[2];
                }
                else if($placecnt == 2){
                     $statearr = $statearr.",".$placearr[0];
                     $countryarr = $countryarr.",".$placearr[1];
                }
                else{
                     $countryarr = $countryarr.",".$placearr[0];
                }
            }
            $job_post->loc_area =  $areaarr;
            $job_post->loc_city =  $cityarr;
            $job_post->loc_state =  $statearr;
            $job_post->loc_country =  $countryarr;

            $job_post->other_benefits = $request->other_benifits;
            $salary = $request->min_salary."-".$request->max_salary;
            $job_post->currency_type = $request->currency_type;
            $job_post->salary_min = $request->min_salary;
            $job_post->salary_max = $request->max_salary;
            $job_post->salary_per_month = $salary;
            $job_post->hide_salary = $request->jpfree_hide_sal;
            //End - Job description details
            //Start - Desired candidate details
            $industry = new IndustryType();
            if($request->jpfree_indsutry == "0" && (!empty($request->jpfree_indsutry1)))
            {
                if(!IndustryType::where('industry_type_name','like','%'.$request->jpfree_indsutry1.'%')->get() )
                {
                    $industry->industry_type_name = $request->jpfree_indsutry1;
                    $industry->save();
                }
                $job_post->industry_type = $request->jpfree_indsutry1;
            }else{
                $job_post->industry_type = $request->jpfree_indsutry;
                $industry=IndustryType::where('industry_type_name',$request->jpfree_indsutry)->first();
            }
            $area = new SubIndustryType();
            if($request->jpfree_farea == "0" && (!empty($request->jpfree_farea1))){
                if(SubIndustryType::where('sub_industry_type_name','like','%'.$request->jpfree_farea1.'%')->count() == 0 )
                {
                    $area->sub_industry_type_name = $request->jpfree_farea1;
                    $area->industry_type_id_fk = $industry->industry_type_id;
                }
                $area->save();
                $job_post->functional_area = $request->jpfree_farea1;
            }else{
                $job_post->functional_area = $request->jpfree_farea;
            }
            
            $job_post->min_experience = $request->min_exp_year;
            $job_post->max_experience = $request->max_exp_year;
            $job_post->nationality = $request->nation;
            $job_post->candidate_current_location = $request->geo_jpfree_cloc;
            $job_post->gender = $request->gender;
            $job_post->marital_status = 1;
            $job_post->job_type = $request->job_type;
            //$job_post->visa_status = $request->visa_status;
            $job_post->qualification_other =  $request->jpfree_other_course.",".$request->jpfree_other_branch;
            $job_post->visa_status = $request->visa_status;

            if($request->visa_status == "1")
            {
                if($request->visa_type == "Other")
                {
                    $job_post->visa_type = $request->other_visa_status;
                }else{
                    $job_post->visa_type = $request->visa_type;
                }
            }

            $ug_course = new Course();
            $ug_spe = new Specialization();
            // print_r($request->all());die;
            if($request->jpfree_ug_course == "0" && !empty($request->jpfree_ug_course1))
            {    
                $ug_course->course_name = $request->jpfree_ug_course1;
                $ug_course->save();
                if($request->jpfree_ugbranch == "0" && !empty($request->jpfree_ug_branch1))
                {
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->jpfree_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->jpfree_ug_course1.",".$request->jpfree_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->jpfree_ug_course1.",".$request->jpfree_ugbranch;
                }
                
            }elseif(!empty($request->jpfree_ug_course)){
                if($request->jpfree_ugbranch == "0" && !empty($request->jpfree_ug_branch1))
                {
                	$ug_course=Course::where('course_name',$request->jpfree_ug_course)->first();
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->jpfree_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->jpfree_ug_course.",".$request->jpfree_ug_branch1;  
            // print_r($request->jpfree_ug_course.",dd".$request->jpfree_ug_branch1);die;
                }else{
                    $job_post->qualification_degree = $request->jpfree_ug_course.",".$request->jpfree_ugbranch;
                }
            }
            // print_r($request->jpfree_ug_course.",".$request->jpfree_ug_branch1);die;
            $pg_course = new PGCourse();
            $pg_spe = new PGSpecialization();
            if($request->jpfree_pg_course == "0" && !empty($request->jpfree_pg_course1))
            {    
                $pg_course->pgc_name = $request->jpfree_pg_course1;
                $pg_course->save();
                if($request->jpfree_pgbranch == "0" && !empty($request->jpfree_pg_branch1))
                {
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->jpfree_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->jpfree_pg_course1.",".$request->jpfree_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->jpfree_pg_course1.",".$request->jpfree_pgbranch;
                }
                
            }elseif(!empty($request->jpfree_pg_course)){
                if($request->jpfree_pgbranch == "0" && !empty($request->jpfree_pg_branch1))
                {
                	$pg_course=PGCourse::where('pgc_name',$request->jpfree_pg_course)->first();
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->jpfree_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->jpfree_pg_course.",".$request->jpfree_pg_branch1;  
                }else{
                    // print_r($request->jpfree_pg_course.",".$request->jpfree_pg_branch);
                    $job_post->qualification_pg = $request->jpfree_pg_course.",".$request->jpfree_pgbranch;
                }
            }

            $h_course = new HighestCourse();
            $h_spe = new HighestSpecialization();
            if($request->jpfree_adv_course == "0" && !empty($request->jpfree_adv_course1))
            {    
                $h_course->course_name = $request->jpfree_adv_course1;
                $h_course->save();
                if($request->jpfree_adv_branch == "0" && !empty($request->jpfree_adv_branch1))
                {
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->jpfree_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->jpfree_adv_course1.",".$request->jpfree_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->jpfree_adv_course1.",".$request->jpfree_adv_branch;
                }
                
            }elseif(!empty($request->jpfree_adv_course)){
                if($request->jpfree_adv_branch == "0" && !empty($request->jpfree_adv_branch1))
                {
                	$h_course=HighestCourse::where('course_name',$request->jpfree_adv_course)->first();
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->jpfree_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->jpfree_adv_course.",".$request->jpfree_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->jpfree_adv_course.",".$request->jpfree_adv_branch;
                }
            }

            $job_post->language = $request->langs;
            $job_post->from_age = $request->age_from;
            $job_post->to_age = $request->age_to;
            $job_post->driving_licence = $request->jfree_drlic;
            $job_post->vehicle_type = $request->vehicle_type;
            $job_post->job_response_email = $request->jpfree_responses;
            //End - Required candidate details
        }
        else if($request->jp_type == 3){  // Saving Regular Pack details

            if($job_post->type == 2 && $request->type == 1)
            {
                $job_package=Helper::current_job_package()->package_id;
                $user_pack_id = Helper::current_user_package()->user_package_id;
                $job_post->package_id = $job_package;
                $job_post->user_package_id = $user_pack_id;

                $current_date = date("Y-m-d H:i:s");
                
                if(Helper::current_job_package()->type == 1){
                    $exp_days = Helper::current_job_package()->saver_pack->job_expire;
                    $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
                }elseif(Helper::current_job_package()->type == 3){
                    $exp_days = Helper::current_job_package()->job_post_pack->job_expire;
                    $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
                }elseif (Helper::current_job_package()->type == 4) {
                    $exp_days = Helper::current_job_package()->branding_pack->job_expire;
                    $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
                }    
            }
            //Start - Employer Info details
            $job_post->employer_company_name = $request->company_name;
            $job_post->employer_designation = $request->designation;
            $job_post->employer_industry_type = $request->industry;
            $job_post->employer_address_1 = $request->office_address;
            $job_post->employer_website_url = $request->website_url;
            $job_post->office_landline = $request->landline;
            $job_post->about_company = $request->about_company;

            
            //$job_post->job_expire = date("Y-m-d",strtotime($request->job_expire));
            //End - Employer Info details
            //Start - Recruiter Info details
            $job_post->recruiter_user_name = $request->hr_name;
            $job_post->recruiter_user_id = $request->hr_id;
            $job_post->recruiter_designation = $request->hr_desgn;
            $job_post->recruiter_mobile = $request->hr_mobile;
            $job_post->recruiter_office_landline = $request->hr_landline;
            //End - Recruiter Info details
            //Start - Job description details
            $job_post->job_title = $request->job_title;
            $job_post->no_of_vacancy = $request->vacancy_no;
            $job_post->job_description = $request->job_desptn;
            $job_post->primary_responsibilities = $request->job_duties;
            $job_post->other_requirements = $request->other_req;
            $xyz = explode(",", $request->locs);
            $location = implode("|", $xyz);
            $xyz = explode(".", $location);
            // dd(implode(",", $xyz));
            $job_post->location = implode(",", $xyz);
            //$job_post->location = $request->regpack_job_location;
            $areaarr = ""; $cityarr = ""; $statearr = ""; $countryarr = "";
            $locsarr = explode(".", $request->locs);
            foreach($locsarr as $locarr){
                $placearr = explode(",", $locarr);
                $placecnt = count($placearr);
                if($placecnt == 4){
                     $areaarr = $areaarr.",".$placearr[0];
                     $cityarr = $cityarr.",".$placearr[1];
                     $statearr = $statearr.",".$placearr[2];
                     $countryarr = $countryarr.",".$placearr[3];
                }
                else if($placecnt == 3){
                     $cityarr = $cityarr.",".$placearr[0];
                     $statearr = $statearr.",".$placearr[1];
                     $countryarr = $countryarr.",".$placearr[2];
                }
                else if($placecnt == 2){
                     $statearr = $statearr.",".$placearr[0];
                     $countryarr = $countryarr.",".$placearr[1];
                }
                else{
                     $countryarr = $countryarr.",".$placearr[0];
                }
            }
            $job_post->loc_area =  $areaarr;
            $job_post->loc_city =  $cityarr;
            $job_post->loc_state =  $statearr;
            $job_post->loc_country =  $countryarr;

            $job_post->other_benefits = $request->other_benifits;
            $salary = $request->min_salary."-".$request->max_salary;
            $job_post->salary_per_month = $salary;
            $job_post->currency_type = $request->currency_type;
            $job_post->salary_min = $request->min_salary;
            $job_post->salary_max = $request->max_salary;
            $job_post->job_type = $request->job_type;
            $job_post->hide_salary = $request->regpack_hide_sal;
            $job_post->visa_status = $request->visa_status;

            if($request->visa_status == "1")
            {
                if($request->visa_type == "Other")
                {
                    $job_post->visa_type = $request->other_visa_status;
                }else{
                    $job_post->visa_type = $request->visa_type;
                }
            }
            //End - Job description details
            //Start - Desired candidate details
           // $job_post->industry_type = $request->regpack_indsutry;
           // $job_post->functional_area = $request->regpack_farea;

            $industry = new IndustryType();
            if($request->regpack_indsutry == "0" && !empty($request->regpack_indsutry1)){
                if(!IndustryType::where('industry_type_name','like','%'.$request->regpack_indsutry1.'%')->get() )
                {
                    $industry->industry_type_name = $request->regpack_indsutry1;
                    $industry->save();
                }
                $job_post->industry_type = $request->regpack_indsutry1;
            }elseif(!empty($request->regpack_indsutry)){
                $job_post->industry_type = $request->regpack_indsutry;
                $industry=IndustryType::where('industry_type_name',$request->regpack_indsutry)->first();
            }
            $area = new SubIndustryType();
            if($request->regpack_farea == "0" && !empty($request->regpack_farea1)){
                if(SubIndustryType::where('sub_industry_type_name','like','%'.$request->regpack_farea1.'%')->count() == 0 )
                {
                    $area->sub_industry_type_name = $request->regpack_farea1;
                    $area->industry_type_id_fk = $industry->industry_type_id;
                }
                $area->save();
                $job_post->functional_area = $request->regpack_farea1;
            }else{
                $job_post->functional_area = $request->regpack_farea;
            }

            $job_post->min_experience = $request->min_exp_year;
            $job_post->max_experience = $request->max_exp_year;
            $job_post->nationality = $request->nation;
            $job_post->candidate_current_location = $request->geo_regpack_cloc;
            $job_post->gender = $request->gender;
            $job_post->marital_status = $request->marital_status;
            $job_post->from_age = $request->age_from;
            $job_post->to_age = $request->age_to;
            //$job_post->visa_status = $request->visa_status;
            //End - Desired candidate details
            //Start - Required candidate details
           // $job_post->qualification_basic = $request->regpack_basic_course.",".$request->regpack_basic_branch;
            /*$job_post->qualification_degree = $request->regpack_ug_course.",".$request->regpack_ug_branch;
            $job_post->qualification_pg = $request->regpack_pg_course.",".$request->regpack_pg_branch;
            $job_post->qualification_expertise = $request->regpack_adv_course.",".$request->regpack_adv_branch;*/

            $ug_course = new Course();
            $ug_spe = new Specialization();
            
            if($request->regpack_ug_course == "0" && !empty($request->regpack_ug_course1))
            {    
                $ug_course->course_name = $request->regpack_ug_course1;
                $ug_course->save();
                if($request->regpack_ugbranch == "0" && !empty($request->regpack_ug_branch1))
                {
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->regpack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->regpack_ug_course1.",".$request->regpack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->regpack_ug_course1.",".$request->regpack_ugbranch;
                }
                
            }elseif(!empty($request->regpack_ug_course)){
                if($request->regpack_ugbranch == "0" && !empty($request->regpack_ug_branch1))
                {
                	$ug_course=Course::where('course_name',$request->regpack_ug_course)->first();
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->regpack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->regpack_ug_course.",".$request->regpack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->regpack_ug_course.",".$request->regpack_ugbranch;
                }
            }
            $pg_course = new PGCourse();
            $pg_spe = new PGSpecialization();
            if($request->regpack_pg_course == "0" && !empty($request->regpack_pg_course1))
            {    
                $pg_course->pgc_name = $request->regpack_pg_course1;
                $pg_course->save();
                if($request->regpack_pgbranch == "0" && !empty($request->regpack_pg_branch1))
                {
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->regpack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->regpack_pg_course1.",".$request->regpack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->regpack_pg_course1.",".$request->regpack_pgbranch;
                }
                
            }elseif(!empty($request->regpack_pg_course)){
                if($request->regpack_pgbranch == "0" && !empty($request->regpack_pg_branch1))
                {
                	$pg_course=PGCourse::where('pgc_name',$request->regpack_pg_course)->first();
                    $pg_spe->pgc_id_fk = $ug_course->pgc_id;
                    $pg_spe->pgs_name = $request->regpack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->regpack_pg_course.",".$request->regpack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->regpack_pg_course.",".$request->regpack_pgbranch;
                }
            }

            $h_course = new HighestCourse();
            $h_spe = new HighestSpecialization();
            if($request->regpack_adv_course == "0" && !empty($request->regpack_adv_course1))
            {    
                $h_course->course_name = $request->regpack_adv_course1;
                $h_course->save();
                if($request->regpack_adv_branch == "0" && !empty($request->regpack_adv_branch1))
                {
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->regpack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->regpack_adv_course1.",".$request->regpack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->regpack_adv_course1.",".$request->regpack_adv_branch;
                }
                
            }elseif(!empty($request->regpack_adv_course)){
                if($request->regpack_adv_branch == "0" && !empty($request->regpack_adv_branch1))
                {
                	$h_course=HighestCourse::where('course_name',$request->regpack_adv_course)->first();
                    $h_spe->hc_id_fk = $ug_course->course_id;
                    $h_spe->hs_name = $request->regpack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->regpack_adv_course.",".$request->regpack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->regpack_adv_course.",".$request->regpack_adv_branch;
                }
            }

            
            $job_post->qualification_other =  $request->regpack_other_course.",".$request->regpack_other_branch;
            $job_post->language = $request->langs;
            $job_post->driving_licence = $request->regpack_drlic;
            $job_post->vehicle_type = $request->vehicle_type;
            $job_post->notice_period = $request->notice_prd;
            $job_post->walk_in_interview = $request->regpack_walkin_interview;
            if($request->regpack_walkin_interview == 1)
            {
                $job_post->interview_from_date = $request->date_from.",".$request->fromtime;
                $job_post->interview_to_date = $request->date_to.",".$request->totime;
                $job_post->interview_venue = $request->int_venue;
            }else{
                $job_post->interview_from_date = ",";
                $job_post->interview_to_date = ",";
                $job_post->interview_venue = "";
            }
            
            $job_post->job_response_email = $request->regpack_responses;
            $job_post->company_url = $request->comp_url;
            //End - Required candidate details
        }
        else if($request->jp_type == 2){  // Saving Enterprise Pack details
            if($job_post->type == 2 && $request->type == 1)
            {
                $job_package=Helper::current_job_package()->package_id;
                $user_pack_id = Helper::current_user_package()->user_package_id;
                $job_post->package_id = $job_package;
                $job_post->user_package_id = $user_pack_id;

                $current_date = date("Y-m-d H:i:s");
            
                if(Helper::current_job_package()->type == 1){
                    $exp_days = Helper::current_job_package()->saver_pack->job_expire;
                    $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
                }elseif(Helper::current_job_package()->type == 3){
                    $exp_days = Helper::current_job_package()->job_post_pack->job_expire;
                    $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
                }elseif (Helper::current_job_package()->type == 4) {
                    $exp_days = Helper::current_job_package()->branding_pack->job_expire;
                    $job_post->job_expire = date("Y-m-d H:i:s",strtotime($current_date. ' + '.$exp_days.' days'));
                }
            }
            
            //Start - Employer Info details
            $job_post->employer_company_name = $request->company_name;
            $job_post->employer_designation = $request->designation;
            $job_post->employer_industry_type = $request->industry;
            $job_post->employer_address_1 = $request->office_address;
            $job_post->employer_website_url = $request->website_url;
            $job_post->office_landline = $request->landline;
            $job_post->about_company = $request->about_company;

            
            //$job_post->job_expire = date("Y-m-d",strtotime($request->job_expire));
            //End - Employer Info details
            //Start - Recruiter Info details
            $job_post->recruiter_user_name = $request->hr_name;
            $job_post->recruiter_user_id = $request->hr_id;
            $job_post->recruiter_designation = $request->hr_desgn;
            $job_post->recruiter_mobile = $request->hr_mobile;
            $job_post->recruiter_office_landline = $request->hr_landline;
            //End - Recruiter Info details
            //Start - Job description details
            $job_post->job_title = $request->job_title;
            $job_post->no_of_vacancy = $request->vacancy_no;
            $job_post->job_description = $request->job_desptn;
            $job_post->primary_responsibilities = $request->job_duties;
            $job_post->other_requirements = $request->other_req;
            $xyz = explode(",", $request->locs);
            $location = implode("|", $xyz);
            $xyz = explode(".", $location);
            // dd(implode(",", $xyz));
            $job_post->location = implode(",", $xyz);
            //$job_post->location = $request->propack_job_location;
            $areaarr = ""; $cityarr = ""; $statearr = ""; $countryarr = "";
            $locsarr = explode(".", $request->locs);
            foreach($locsarr as $locarr){
                $placearr = explode(",", $locarr);
                $placecnt = count($placearr);
                if($placecnt == 4){
                     $areaarr = $areaarr.",".$placearr[0];
                     $cityarr = $cityarr.",".$placearr[1];
                     $statearr = $statearr.",".$placearr[2];
                     $countryarr = $countryarr.",".$placearr[3];
                }
                else if($placecnt == 3){
                     $cityarr = $cityarr.",".$placearr[0];
                     $statearr = $statearr.",".$placearr[1];
                     $countryarr = $countryarr.",".$placearr[2];
                }
                else if($placecnt == 2){
                     $statearr = $statearr.",".$placearr[0];
                     $countryarr = $countryarr.",".$placearr[1];
                }
                else{
                     $countryarr = $countryarr.",".$placearr[0];
                }
            }
            $job_post->loc_area =  $areaarr;
            $job_post->loc_city =  $cityarr;
            $job_post->loc_state =  $statearr;
            $job_post->loc_country =  $countryarr;
            
            $job_post->other_benefits = $request->other_benifits;
            $salary = $request->min_salary."-".$request->max_salary;
            $job_post->salary_per_month = $salary;
            $job_post->currency_type = $request->currency_type;
            $job_post->salary_min = $request->min_salary;
            $job_post->salary_max = $request->max_salary;
            $job_post->job_type = $request->job_type;
            $job_post->hide_salary = $request->propack_hide_sal;
            $job_post->visa_status = $request->visa_status;
            if($request->visa_status == "1")
            {
                if($request->visa_type == "Other")
                {
                    $job_post->visa_type = $request->other_visa_status;
                }else{
                    $job_post->visa_type = $request->visa_type;
                }
            }
            //End - Job description details
            //Start - Desired candidate details
           // $job_post->industry_type = $request->propack_indsutry;
           // $job_post->functional_area = $request->propack_farea;

            $industry = new IndustryType();
            if($request->propack_indsutry == "0" && !empty($request->propack_indsutry1)){
                if(!IndustryType::where('industry_type_name','like','%'.$request->propack_indsutry1.'%')->get() )
                {
                    $industry->industry_type_name = $request->propack_indsutry1;
                    $industry->save();
                }
                $job_post->industry_type = $request->propack_indsutry1;
            }else{
                $job_post->industry_type = $request->propack_indsutry;
                $industry=IndustryType::where('industry_type_name',$request->propack_indsutry)->first();
            }
            $area = new SubIndustryType();
            if($request->propack_farea == "0" && !empty($request->propack_farea1)){
                if(SubIndustryType::where('sub_industry_type_name','like','%'.$request->propack_farea1.'%')->count() == 0 )
                {
                    $area->sub_industry_type_name = $request->propack_farea1;
                    $area->industry_type_id_fk = $industry->industry_type_id;
                    $area->save();
                }
                $job_post->functional_area = $request->propack_farea1;
            }else{
                $job_post->functional_area = $request->propack_farea;
            }

            $job_post->min_experience = $request->min_exp_year;
            $job_post->max_experience = $request->max_exp_year;
            $job_post->nationality = $request->nation;
            $job_post->candidate_current_location = $request->geo_propack_cloc;
            $job_post->gender = $request->gender;
            $job_post->marital_status = $request->marital_status;
            $job_post->from_age = $request->age_from;
            $job_post->to_age = $request->age_to;
            //$job_post->visa_status = $request->visa_status;
            //End - Desired candidate details
            //Start - Required candidate details
            //$job_post->qualification_basic = $request->propack_basic_course.",".$request->propack_basic_branch;
           /* $job_post->qualification_degree = $request->propack_ug_course.",".$request->propack_ug_branch;
            $job_post->qualification_pg = $request->propack_pg_course.",".$request->propack_pg_branch;
            $job_post->qualification_expertise = $request->propack_adv_course.",".$request->propack_adv_branch;*/


            $ug_course = new Course();
            $ug_spe = new Specialization();
            
            if($request->propack_ug_course == "0" && (!empty($request->propack_ug_course1)))
            {    
                $ug_course->course_name = $request->propack_ug_course1;
                $ug_course->save();
                if($request->propack_ugbranch == "0" && !empty($request->propack_ug_branch1))
                {
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->propack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->propack_ug_course1.",".$request->propack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->propack_ug_course1.",".$request->propack_ugbranch;
                }
                
            }else{
                if($request->propack_ugbranch == "0" && !empty($request->propack_ug_branch1))
                {
                	$ug_course=Course::where('course_name',$request->propack_ug_course)->first();
                    $ug_spe->course_id_fk = $ug_course->course_id;
                    $ug_spe->specialization_name = $request->propack_ug_branch1;
                    $ug_spe->save();
                  $job_post->qualification_degree = $request->propack_ug_course.",".$request->propack_ug_branch1;  
                }else{
                    $job_post->qualification_degree = $request->propack_ug_course.",".$request->propack_ugbranch;
                }
            }
            $pg_course = new PGCourse();
            $pg_spe = new PGSpecialization();
            if($request->propack_pg_course == "0" && !empty($request->propack_pg_course1))
            {    
                $pg_course->pgc_name = $request->propack_pg_course1;
                $pg_course->save();
                if($request->propack_pgbranch == "0" && !empty($request->propack_pg_branch1))
                {
                    $pg_spe->pgc_id_fk = $pg_course->pgc_id;
                    $pg_spe->pgs_name = $request->propack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->propack_pg_course1.",".$request->propack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->propack_pg_course1.",".$request->propack_pgbranch;
                }
                
            }else{
                if($request->propack_pgbranch == "0" && !empty($request->propack_pg_branch1))
                {
                	$pg_course=PGCourse::where('pgc_name',$request->propack_pg_course)->first();
                    $pg_spe->pgc_id_fk = $ug_course->pgc_id;
                    $pg_spe->pgs_name = $request->propack_pg_branch1;
                    $pg_spe->save();
                  $job_post->qualification_pg = $request->propack_pg_course.",".$request->propack_pg_branch1;  
                }else{
                    $job_post->qualification_pg = $request->propack_pg_course.",".$request->propack_pgbranch;
                }
            }

            $h_course = new HighestCourse();
            $h_spe = new HighestSpecialization();
            if($request->propack_adv_course == "0" && !empty($request->propack_adv_course1))
            {    
                $h_course->course_name = $request->propack_adv_course1;
                $h_course->save();
                if($request->propack_adv_branch == "0" && !empty($request->propack_adv_branch1))
                {
                    $h_spe->hc_id_fk = $h_course->course_id;
                    $h_spe->hs_name = $request->propack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->propack_adv_course1.",".$request->propack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->propack_adv_course1.",".$request->propack_adv_branch;
                }
                
            }else{
                if($request->propack_adv_branch == "0" && !empty($request->propack_adv_branch1))
                {
                	$h_course=HighestCourse::where('course_name',$request->propack_adv_course)->first();
                    $h_spe->hc_id_fk = $ug_course->course_id;
                    $h_spe->hs_name = $request->propack_adv_branch1;
                    $h_spe->save();
                  $job_post->qualification_expertise = $request->propack_adv_course.",".$request->propack_adv_branch1;  
                }else{
                    $job_post->qualification_expertise = $request->propack_adv_course.",".$request->propack_adv_branch;
                }
            }


            $job_post->qualification_other =  $request->propack_other_course.",".$request->propack_other_branch;
            $job_post->language = $request->langs;
            $job_post->driving_licence = $request->propack_drlic;
            $job_post->vehicle_type = $request->vehicle_type;
            $job_post->notice_period = $request->notice_prd;
            $job_post->walk_in_interview = $request->propack_walkin_interview;

            if($request->propack_walkin_interview == 1)
            {
                $job_post->interview_from_date = $request->date_from.",".$request->fromtime;
                $job_post->interview_to_date = $request->date_to.",".$request->totime;
                $job_post->interview_venue = $request->int_venue;
            }else{
                $job_post->interview_from_date = ",";
                $job_post->interview_to_date = ",";
                $job_post->interview_venue = "";
            }
            /*$job_post->interview_from_date = $request->date_from.",".$request->fromtime;
            $job_post->interview_to_date = $request->date_to.",".$request->totime;
            $job_post->interview_venue = $request->int_venue;*/
            //Company Logo company_logo
            $job_post->company_url = $request->comp_url;
            $job_post->job_response_email = $request->propack_responses;
            $job_post->job_alert = $request->job_alert;
            $job_post->top_search = $request->top_search_list;
            $job_post->job_bolding = $request->bold_job;
            if(!empty($request->job_featured)){
                $job_post->featured_job = $request->job_featured;
            }
            $job_post->social_media = $request->entr_social_media;
            if($request->hasFile('comp_logo'))
            {
                $destination = 'uploads/company_logo';  
                $file = $request->file('comp_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                $job_post->company_logo = $filelocation;
            }
            //End - Required candidate details
        }
        else{

        }
        $job_post->save();
        $jobpost_id = $job_post->job_id;
        if($request->tech_skills != ""){
            $arr_tech_skills = json_decode($request->tech_skills);
            $tech_skills_cnt = count($arr_tech_skills);
            Job_post_keyskills::where('job_id_fk',$jobpost_id)->delete();
            foreach ($arr_tech_skills as $skills_data) {
                    // print_r($skills_data->skill);
                    // echo $skills_data[$m]->skill.$m;
                     // Start - Default values for NOT NULL fields
                    $jp_keyskills = new Job_post_keyskills();
                    $jp_keyskills->job_id_fk = $jobpost_id;
                    $jp_keyskills->created_at = date("Y-m-d H:i:s");
                    $jp_keyskills->updated_at = date("Y-m-d H:i:s");
                    //End - Default values for NOT NULL fields
                    $jp_keyskills->skill = $skills_data->skill;
                    $jp_keyskills->expertise = $skills_data->level;
                    $jp_keyskills->experience = $skills_data->yoe;
                    $jp_keyskills->last_used = $skills_data->ylu;
                    $jp_keyskills->save();
            }
        }
        Helper::expire_package();
        if($request->type == 1){
            echo 1; die();
        }
        elseif($request->type == 2){
            echo 2; die();
        }
    }
    public function post_job_again(Request $request){
        $existingJob = Job_post::find($request->id);
        $newJob =  $existingJob->replicate();
        $newJob->created_at = date("Y-m-d H:i:s");
        $newJob->updated_at = date("Y-m-d H:i:s");  
        $newJob->save();
        echo 1;die();
    }
    public function search_remove(Request $request){
        Cv_search::where('cv_search_id',$request->id)->delete();
        Cv_search_details::where('search_id_fk',$request->id)->delete();
        Cvsearch_save::where('id',$request->id)->delete();
        Savedsearch_share::where('savedsearch_id',$request->id)->delete();
        echo 1;die();
    }
    public function searchlist_printcv(Request $request){
        $jsid = $request->jsids;
        $jsid = trim($jsid,",");
        $cvpath = job_seeker_cv::where('user_id_fk', $jsid)->get(['cv']);
        if(count($cvpath)==0)
        {
            $response=array("status" => 5);
            echo json_encode($response);die();
        }
        $response =  array("status" => 1,
        "path" => $cvpath[0]['cv']);
        $user_id = Auth::user()->id;
        $exist = Cv_downloads::where('user_id_fk',$user_id)
                                ->where('job_seeker_id',$jsid)->get();
        $exist_count = count($exist);
        if($exist_count == 0){
            if(Auth::user()->employer_details->parent_id>0)
            {
                $total_cv_search=Helper::sub_user_cv_dowload();
                $cv_searched=count(Helper::sub_user_total_cv_dowload());
                if($total_cv_search<=$cv_searched)
                {
                    $response =  array("status" => 3);
                    echo json_encode($response);die();
                }
            }
            else
            {
                $total_cv_search=Helper::cv_search_access();
                $cv_searched=count(Helper::total_cv_searched());
                if($total_cv_search<=$cv_searched)
                {
                    $response =  array("status" => 3);
                    echo json_encode($response);die();
                }
            }
            $cvdownloads = new Cv_downloads();
            $cvdownloads->user_id_fk = $user_id;
            $cvdownloads->job_seeker_id = $jsid;
            $cvdownloads->package_id = Helper::current_cv_package()->package_id;
            $cvdownloads->user_package_id = Helper::current_cv_user_package()->user_package_id;
            $cvdownloads->created_at = date("Y-m-d H:i:s");
            $cvdownloads->updated_at = date("Y-m-d H:i:s");
            $cvdownloads->save();
        }
        if($exist_count > 0){

            
            $downloadId = $exist[0]['download_id'];
            Cv_downloads::where('download_id',$downloadId)
                        ->update(['updated_at'=>date("Y-m-d H:i:s")]);
        }

        Helper::expire_package();
        echo json_encode($response);die();
    }
    public function searchlist_printcv1(Request $request){
        $jsid = $request->values;
        // $jsid = trim($jsid,",");
        $cvpath = job_seeker_cv::whereIn('user_id_fk', $jsid)->get(['cv']);
        if(count($cvpath)==0)
        {
            $response=array("status" => 5);
            echo json_encode($response);die();
        }
        // print_r(count($cvpath));die;
        $response =  array("status" => 1,
        "path" => $cvpath[0]['cv']);
        $user_id = Auth::user()->id;
        $exist = Cv_downloads::where('user_id_fk',$user_id)
                                ->whereIn('job_seeker_id',$jsid)->get();
        $exist_count = count($exist);
        if($exist_count == 0){
            $cvdownloads = new Cv_downloads();
            $cvdownloads->user_id_fk = $user_id;
            $cvdownloads->job_seeker_id = $jsid[0];
            $cvdownloads->created_at = date("Y-m-d H:i:s");
            $cvdownloads->updated_at = date("Y-m-d H:i:s");
            $cvdownloads->save();
        }
        if($exist_count > 0){
            $downloadId = $exist[0]['download_id'];
            Cv_downloads::where('download_id',$downloadId)
                        ->update(['updated_at'=>date("Y-m-d H:i:s")]);
        }
        echo json_encode($response);die();
    }
    public function jpost_regular(Request $request){
        $user_id = Auth::user()->id;
        echo 1;die();
    }
    public function jpost_free_action(Request $request){
        //print_r($request->all());exit();
        function array_map_assoc($array){
          $r = array();
          foreach ($array as $key=>$value)
            $r[$key] = "$key=$value";
          return $r;
        }
        $arr = $request->toArray(); 
        //print_r($arr);
        $requestStr = implode(',',array_map_assoc($arr));
        //print_r($requestStr);
        //exit();
        $response =  array("status" => 1,
        "msg" => Crypt::encryptString(json_encode($arr)));
        echo json_encode($response);
        die();
    }
    public function jpost_free_display($message){
         $data = Crypt::decryptString($message);
         //print_r($data);exit();
         $var = "1";
         return view('employer/jp_free_preview',compact('data','var'));   
    }
    public function preview_display_db($id){
         $jobid = Crypt::decryptString($id);
         $data = Job_post::where('job_id',$jobid)->get();
         $var = "2";
         if($data[0]['jp_type'] == 1){
            return view('employer/jp_free_preview',compact('data','var'));  
         }
         else if($data[0]['jp_type'] == 2){
            return view('employer/jp_enterprise_preview',compact('data','var'));  
         }
         else{
            return view('employer/jp_regular_preview',compact('data','var'));  
         } 
    }
    public function jpost_enterprise(Request $request){
        $user_id = Auth::user()->id;
        echo 1;die();
    }
    public function jpost_regular_action(Request $request){
        function array_map_assoc($array){
          $r = array();
          foreach ($array as $key=>$value)
            $r[$key] = "$key=$value";
          return $r;
        }
        $arr = $request->toArray(); 
        //print_r($arr);
        $requestStr = implode(',',array_map_assoc($arr));
        $response =  array("status" => 1,
        "msg" => Crypt::encryptString(json_encode($arr)));
        echo json_encode($response);
        die();
    }
    public function jpost_regular_display($message){
         $data = Crypt::decryptString($message);
         $var = "1";
         return view('employer/jp_regular_preview',compact('data','var'));   
    }
    public function jpost_enterprise_action(Request $request){
        function array_map_assoc($array){
          $r = array();
          foreach ($array as $key=>$value)
            $r[$key] = "$key=$value";
          return $r;
        }
        $arr = $request->toArray(); 
        //print_r($arr);exit();
        $requestStr = implode(',',array_map_assoc($arr));
        //print_r($requestStr);
        $response =  array("status" => 1,
        "msg" => Crypt::encryptString(json_encode($arr)));
        echo json_encode($response);
        die();
    }
    public function jpost_enterprise_display($message){
         // dd($message);
         $data = Crypt::decryptString($message);
         $var = "1";
         return view('employer/jp_enterprise_preview',compact('data','var'));   
    }
    public function job_delete(Request $request){
         Job_post::where('job_id',$request->id)->update(['status'=>2]);
         echo 1;
         die();
    }
    public function new_folder(Request $request){
        $user_id = Auth::user()->id;
        $check_foldername = Folder::where('folder_name',$request->folder_name)->where('usedfor',$request->used_for)->first();
        if(!empty($check_foldername)){
            echo 2;die();
        }
        $folder = new Folder();
        $folder->folder_name = $request->folder_name;
        $folder->usedfor = $request->used_for;
        $folder->user_id_fk = $user_id;
        $folder->created_at = date("Y-m-d H:i:s");
        $folder->updated_at = date("Y-m-d H:i:s");
        $folder->save();
        echo 1;
        die();
    }
    public function top_employer(Request $request){
        //print_r($request->hdn_logopath);exit();
         $user_id = Auth::user()->id;
         $exist = Top_Employer::where('user_id_fk',$user_id)->get();
         $exist_count = count($exist);
         $employer_data = Employer::where('user_id_fk',$user_id)->get(['website_url']);
         $website_url = $employer_data[0]['website_url'];
         if (!strpos($website_url,"://")) {
            $website_url = "http://".$website_url;
         }
         if($exist_count == 0){
             $top_employer = new Top_Employer();
             $top_employer->user_id_fk = $user_id;
             $top_employer->nologo_txt = $request->nologo_text;
             $top_employer->redirect_to = $request->employer_redirect;
             $top_employer->status = 1;
             $top_employer->created_at = date("Y-m-d H:i:s");
             $top_employer->updated_at = date("Y-m-d H:i:s");
             $top_employer->web_url = $website_url;
             //$top_employer->web_url = $request->url;
             if($request->hasFile('top_employer_logo'))
             {
                $destination = 'uploads/employer';  
                $file = $request->file('top_employer_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                $top_employer->logo_path = $filelocation;
             }
             $top_employer->save();
             echo 1;
             die();
         }
         else{
            $filelocation = "";
            if($request->hasFile('top_employer_logo'))
             {
                $destination = 'uploads/employer';  
                $file = $request->file('top_employer_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
             }
             else{
                if($request->hdn_logopath != ""){
                    //print_r($request->hdn_logopath);exit();
                    $filelocation = $request->hdn_logopath;
                }
             }
             
            Top_Employer::where('user_id_fk',$user_id)->update(['nologo_txt'=>$request->nologo_text,'logo_path'=>$filelocation,'redirect_to'=>$request->employer_redirect,'updated_at'=>date("Y-m-d H:i:s"),'web_url'=>$request->url]);
            echo 2;
            die();
         }
    }
    public function microsite_save(Request $request){
        //print_r($request->all());exit();
         $user_id = Auth::user()->id;
         $exist = Microsite_details::where('user_id_fk',$user_id)->get();
         $exist_count = count($exist);
        if($exist_count == 0)
        {
             $microsite_details = new Microsite_details();
             $videolocation = "";
             if($request->hasFile('DynamicVideo'))
                {
                    $videos = $request->file('DynamicVideo');
                    foreach($videos as $video)
                    {
                    $destination = 'uploads/video';  
                    $video->move($destination, $destination. "/" .time().'-'.$video->getClientOriginalName());
                    $videolocation = $videolocation.','.$destination. "/" .time().'-'.$video->getClientOriginalName();
                    }
                }
                 $microsite_details->user_id_fk = $user_id;
                 $microsite_details->company_name = $request->company_name;
                 $microsite_details->web_url = $request->website_url;
                 $microsite_details->about_company = $request->about_company;
                 $microsite_details->found_in = $request->founded_in;
                 $microsite_details->total_emp = $request->total_emps;
                 //print_r(Helper::current_user_package());exit();
                 //$microsite_details->user_package_id=Helper::current_user_package()->user_package_id;
                 $microsite_details->user_package_id=Helper::current_microsite_pack()->user_package_id;
                if($request->industry == "0")
                {
                    $microsite_details->industry = $request->industry1;
                    $ind = new IndustryType();
                    $ind->industry_type_name = $request->industry1;
                    $ind->save();
                }else{
                    $microsite_details->industry = $request->industry;
                }
             
                 $microsite_details->resume_option = $request->microsite_confidential;
                 $microsite_details->company_video = $videolocation;
                 $microsite_details->social_sync = $request->sync_media;
                 $microsite_details->fb_url = $request->fb_url;
                 $microsite_details->twiter_url = $request->twitter_url;
                 $microsite_details->linked_url = $request->linked_url;
                 $microsite_details->cont_addr = $request->company_addr;
                 $microsite_details->show_map = $request->show_map;
                 $microsite_details->status = 1;
                 $microsite_details->created_at = date("Y-m-d H:i:s");
                 $microsite_details->updated_at = date("Y-m-d H:i:s");
                if($request->hasFile('microsite_logo'))
                {
                    $destination = 'uploads/employer';  
                    $file = $request->file('microsite_logo');
                    $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                    $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                    $microsite_details->logo_path = $filelocation;
                }
                $sliderslocation = "";
                if($request->hasFile('sliderimgs'))
                {
                    $slider_imgs = $request->file('sliderimgs');
                    foreach($slider_imgs as $slider_img)
                    {
                        $destination = 'uploads/slider';  
                        $slider_img->move($destination, $destination. "/" .time().'-'.$slider_img->getClientOriginalName());
                        $sliderslocation = $sliderslocation.','.$destination. "/" .time().'-'.$slider_img->getClientOriginalName();
                    }
                    $microsite_details->slider_image = $sliderslocation;
                }
                 $microsite_details->save();
                 $countries = $request->country;
                 $citys = $request->city;
                 $addrs = $request->address;
                if(sizeof($request->country)>0)
                {
                 foreach($request->country as $key => $n ) {
                    $microsite_locs = new Microsite_locations();
                    $microsite_locs->detail_fk_id = $microsite_details->site_id;
                    $microsite_locs->country = $countries[$key];
                    $microsite_locs->city = $citys[$key];
                    $microsite_locs->office_addr = $addrs[$key];
                    $microsite_locs->status = 1;
                    $microsite_locs->created_at = date("Y-m-d H:i:s");
                    $microsite_locs->updated_at = date("Y-m-d H:i:s");
                    $microsite_locs->save();
                 }
                }
                $response =  array("status" => 1,
                "id" => $microsite_details->site_id);
                echo json_encode($response);die();
        }else{
            $locs_exist = Microsite_locations::where('detail_fk_id',$exist[0]['site_id'])->get();
            $locs_exist_count = count($exist);
            if($locs_exist_count > 0){
                Microsite_locations::where('detail_fk_id',$exist[0]['site_id'])->delete();
            }
            $filelocation = "";
            if($request->hasFile('microsite_logo'))
             {
                $destination = 'uploads/employer';  
                $file = $request->file('microsite_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
             }
             else{
                if($request->hdn_logopath != ""){
                    $filelocation = $request->hdn_logopath;
                }
             }
             $countries = $request->country;
             $citys = $request->city;
             $addrs = $request->address;
             if(sizeof($request->country)>0){
                 foreach($request->country as $key => $n ) {
                    $microsite_locs = new Microsite_locations();
                    $microsite_locs->detail_fk_id = $exist[0]['site_id'];
                    $microsite_locs->country = $countries[$key];
                    $microsite_locs->city = $citys[$key];
                    $microsite_locs->office_addr = $addrs[$key];
                    $microsite_locs->status = 1;
                    $microsite_locs->created_at = date("Y-m-d H:i:s");
                    $microsite_locs->updated_at = date("Y-m-d H:i:s");
                    $microsite_locs->save();
                 }
             }
             $videolocation = "";
             $dbvideos = $request->DynamicVideo;
             if(sizeof($dbvideos)>0){
                foreach($dbvideos as $dbvideo){
                    if(!empty($dbvideo)){
                        $videolocation = $videolocation.','.$dbvideo;
                    }
                }
             }
             if($request->hasFile('DynamicVideo'))
             {
                $videos = $request->file('DynamicVideo');
                foreach($videos as $video){
                $destination = 'uploads/video';  
                $video->move($destination, $destination. "/" .time().'-'.$video->getClientOriginalName());
               $videolocation = $videolocation.','.$destination. "/" .time().'-'.$video->getClientOriginalName();
                }
             }
             //print_r($videolocation);exit();
             $sliderslocation = "";
             $slider_db_imgs = $request->sliderdbimgs;
             if(count($slider_db_imgs)>0){
                 foreach($slider_db_imgs as $slider_db_img){
                    $sliderslocation = $sliderslocation.','.$slider_db_img;
                }
             }
             if($request->hasFile('sliderimgs'))
             {
                $slider_imgs = $request->file('sliderimgs');
                foreach($slider_imgs as $slider_img){
                $destination = 'uploads/slider';  
                $thumb = $slider_img;
                $img = Image::make($thumb->getRealPath())->resize(1280,290);
                $path = $destination. "/" .time().'-'.$slider_img->getClientOriginalName();
                $img->save($path);
           $sliderslocation = $sliderslocation.','.$destination. "/" .time().'-'.$slider_img->getClientOriginalName();
                }
             }
             $indus = "";
             if($request->industry == "0"){
                $indus = $request->industry1;
                $ind = new IndustryType();
                $ind->industry_type_name = $request->industry1;
                $ind->save();
             }else{
                $indus = $request->industry;
             }
             Microsite_details::where('site_id',$exist[0]['site_id'])
                        ->update([
                            'logo_path' => $filelocation,
                            'company_name' =>  $request->company_name, 
                            'web_url' => $request->website_url,
                            'about_company' => $request->about_company,
                            'found_in' => $request->founded_in,
                            'total_emp' => $request->total_emps,
                            'industry' => $indus,
                            'resume_option' => $request->microsite_confidential,
                            'social_sync' => $request->sync_media,
                            'fb_url' => $request->fb_url,
                            'twiter_url' => $request->twitter_url,
                            'linked_url' => $request->linked_url,
                            'cont_addr' => $request->company_addr,
                            'show_map' => $request->show_map,
                            'company_video' => $videolocation,
                            'slider_image' => $sliderslocation,
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
            $response =  array("status" => 2,
            "id" => $exist[0]['site_id']);
            echo json_encode($response);die();
         }
    }
    public function microsite_preview(Request $request){
         $user_id = Auth::user()->id;
         $exist = Microsite_details_preview::where('user_id_fk',$user_id)->get();
         $exist_count = count($exist);
         if($exist_count == 0){
             $microsite_details = new Microsite_details_preview();
             $videolocation = "";
             if($request->hasFile('DynamicVideo'))
             {
                $videos = $request->file('DynamicVideo');
                foreach($videos as $video){
                $destination = 'uploads/video';  
                $video->move($destination, $destination. "/" .time().'-'.$video->getClientOriginalName());
               $videolocation = $videolocation.','.$destination. "/" .time().'-'.$video->getClientOriginalName();
                }
             }
             $microsite_details->user_id_fk = $user_id;
             $microsite_details->company_name = $request->company_name;
             $microsite_details->web_url = $request->website_url;
             $microsite_details->about_company = $request->about_company;
             $microsite_details->found_in = $request->founded_in;
             $microsite_details->total_emp = $request->total_emps;
             $microsite_details->industry = $request->industry;
             $microsite_details->resume_option = $request->microsite_confidential;
             $microsite_details->company_video = $videolocation;
             $microsite_details->social_sync = $request->sync_media;
             $microsite_details->fb_url = $request->fb_url;
             $microsite_details->twiter_url = $request->twitter_url;
             $microsite_details->linked_url = $request->linked_url;
             $microsite_details->cont_addr = $request->company_addr;
             $microsite_details->show_map = $request->show_map;
             $microsite_details->status = 1;
             $microsite_details->created_at = date("Y-m-d H:i:s");
             $microsite_details->updated_at = date("Y-m-d H:i:s");
             if($request->hasFile('microsite_logo'))
             {
                $destination = 'uploads/employer';  
                $file = $request->file('microsite_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                $microsite_details->logo_path = $filelocation;
             }
             $sliderslocation = "";
             if($request->hasFile('sliderimgs'))
             {
                $slider_imgs = $request->file('sliderimgs');
                foreach($slider_imgs as $slider_img){
                $destination = 'uploads/slider';  
              $slider_img->move($destination, $destination. "/" .time().'-'.$slider_img->getClientOriginalName());
           $sliderslocation = $sliderslocation.','.$destination. "/" .time().'-'.$slider_img->getClientOriginalName();
                }
                $microsite_details->slider_image = $sliderslocation;
             }
             $microsite_details->save();
             $countries = $request->country;
             $citys = $request->city;
             $addrs = $request->address;
             if(sizeof($request->country)>0){
                 foreach($request->country as $key => $n ) {
                    $microsite_locs = new Microsite_locations();
                    $microsite_locs->detail_fk_id = $microsite_details->site_id;
                    $microsite_locs->country = $countries[$key];
                    $microsite_locs->city = $citys[$key];
                    $microsite_locs->office_addr = $addrs[$key];
                    $microsite_locs->status = 1;
                    $microsite_locs->created_at = date("Y-m-d H:i:s");
                    $microsite_locs->updated_at = date("Y-m-d H:i:s");
                    $microsite_locs->save();
                 }
             }
            $response =  array("status" => 1,
            "id" => $microsite_details->site_id);
            echo json_encode($response);die();
         }
         else{
            $locs_exist = Microsite_locations::where('detail_fk_id',$exist[0]['site_id'])->get();
            $locs_exist_count = count($exist);
            if($locs_exist_count > 0){
                Microsite_locations::where('detail_fk_id',$exist[0]['site_id'])->delete();
            }
            $filelocation = "";
            if($request->hasFile('microsite_logo'))
             {
                $destination = 'uploads/employer';  
                $file = $request->file('microsite_logo');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
             }
             else{
                if($request->hdn_logopath != ""){
                    $filelocation = $request->hdn_logopath;
                }
             }
             $countries = $request->country;
             $citys = $request->city;
             $addrs = $request->address;
             if(sizeof($request->country)>0){
                 foreach($request->country as $key => $n ) {
                    $microsite_locs = new Microsite_locations();
                    $microsite_locs->detail_fk_id = $exist[0]['site_id'];
                    $microsite_locs->country = $countries[$key];
                    $microsite_locs->city = $citys[$key];
                    $microsite_locs->office_addr = $addrs[$key];
                    $microsite_locs->status = 1;
                    $microsite_locs->created_at = date("Y-m-d H:i:s");
                    $microsite_locs->updated_at = date("Y-m-d H:i:s");
                    $microsite_locs->save();
                 }
             }
             $videolocation = "";
             $dbvideos = $request->DynamicVideo;
             if(sizeof($dbvideos)>0){
                foreach($dbvideos as $dbvideo){
                    if(!empty($dbvideo)){
                        $videolocation = $videolocation.','.$dbvideo;
                    }
                }
             }
             if($request->hasFile('DynamicVideo'))
             {
                $videos = $request->file('DynamicVideo');
                foreach($videos as $video){
                $destination = 'uploads/video';  
                $video->move($destination, $destination. "/" .time().'-'.$video->getClientOriginalName());
               $videolocation = $videolocation.','.$destination. "/" .time().'-'.$video->getClientOriginalName();
                }
             }
             $sliderslocation = "";
             $dbsliders = $request->sliderimgs;
             if(sizeof($dbsliders)>0){
                foreach($dbsliders as $dbslider){
                    if(!empty($dbslider)){
                        $sliderslocation = $sliderslocation.','.$dbslider;
                    }
                }
             }
             if($request->hasFile('sliderimgs'))
             {
                $slider_imgs = $request->file('sliderimgs');
                foreach($slider_imgs as $slider_img){
                $destination = 'uploads/slider';  
              $slider_img->move($destination, $destination. "/" .time().'-'.$slider_img->getClientOriginalName());
           $sliderslocation = $sliderslocation.','.$destination. "/" .time().'-'.$slider_img->getClientOriginalName();
                }
             }
             Microsite_details_preview::where('site_id',$exist[0]['site_id'])
                        ->update([
                            'logo_path' => $filelocation,
                            'company_name' =>  $request->company_name, 
                            'web_url' => $request->website_url,
                            'about_company' => $request->about_company,
                            'found_in' => $request->founded_in,
                            'total_emp' => $request->total_emps,
                            'industry' => $request->industry,
                            'resume_option' => $request->microsite_confidential,
                            'social_sync' => $request->sync_media,
                            'fb_url' => $request->fb_url,
                            'twiter_url' => $request->twitter_url,
                            'linked_url' => $request->linked_url,
                            'cont_addr' => $request->company_addr,
                            'show_map' => $request->show_map,
                            'company_video' => $videolocation,
                            'slider_image' => $sliderslocation,
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
            $response =  array("status" => 2,
            "id" => $exist[0]['site_id']);
            echo json_encode($response);die();
         }
    }
    public function folder_move(Request $request){
        
        $user_id = Auth::user()->id;
        $folder_id = $request->id;
        //$jobseekerids = str_replace('S1717', '', $request->jsids);
        $jobseekers = explode(",", $request->jsids);
        unset($jobseekers[0]);
        //print_r($jobseekers);exit();
        foreach($jobseekers as $jobseeker)
        {
        if(!empty($jobseeker))
        if(Folder_move::where('user_id_fk',$user_id)->where('folder_id_fk',$folder_id)->where('content_id',$jobseeker)->first())
            {
                 echo 3;die();
            }
        }
        foreach($jobseekers as $jobseeker)
        {
            if(!empty($jobseeker)){
            $folder_move = new Folder_move();
                    $folder_move->user_id_fk = $user_id;
                    $folder_move->folder_id_fk = $folder_id;
                    $folder_move->content_id = $jobseeker;
                    $folder_move->created_at = date("Y-m-d H:i:s");
                    $folder_move->updated_at = date("Y-m-d H:i:s");
                    $folder_move->save();
            }
        }
                /*if(count($exist) == 0){
                    $folder_move = new Folder_move();
                    $folder_move->user_id_fk = $user_id;
                    $folder_move->folder_id_fk = $folder_id;
                    $folder_move->content_id = $jobseeker;
                    $folder_move->created_at = date("Y-m-d H:i:s");
                    $folder_move->updated_at = date("Y-m-d H:i:s");
                    $folder_move->save();
                }*/
            
        
        echo 1;
        die();
    }
    public function folder_move1(Request $request){
        $user_id = Auth::user()->id;
        $folder_id = $request->id;
        $jobseekers = $request->uids;
        $apply_ids=$request->values;
        // $jobseekers = explode(",", $jobseekerids);
        //print_r($jobseekers);exit();
        foreach($jobseekers as $key => $jobseeker)
        {
            if(Folder_move::where('user_id_fk',$user_id)->where('folder_id_fk',$folder_id)->where('content_id',$jobseeker)->first()){
                /*$exist = Folder_move::where('user_id_fk',$user_id)
                                    ->where('folder_id_fk',$folder_id)
                                    ->where('content_id',$jobseeker)->get();*/
                    echo 3;die();
                }
        }
        foreach($jobseekers as $key => $jobseeker)
        {
            if(!empty($jobseeker) ){
                    $folder_move = new Folder_move();
                    $folder_move->user_id_fk = $user_id;
                    $folder_move->folder_id_fk = $folder_id;
                    $folder_move->content_id = $jobseeker;
                    $folder_move->apply_id = $apply_ids[$key];
                    $folder_move->created_at = date("Y-m-d H:i:s");
                    $folder_move->updated_at = date("Y-m-d H:i:s");
                    $folder_move->save();
                }
        }
        
        echo 1;
        die();
    }
    public function job_seeker_details($id){
        $jbsid = Crypt::decryptString($id);
        $page = "packages";
        $user_id = Auth::user()->id;
        $user = User::where('id',$jbsid)->get();
        $exist = Profile_views::where('user_id_fk',$user_id)
                                ->where('job_seeker_id',$jbsid)->get();
        $exist_count = count($exist);
        if($exist_count == 0){
            $profileviews = new Profile_views();
            $profileviews->user_id_fk = $user_id;
            $profileviews->job_seeker_id = $jbsid;
            $profileviews->package_id = Helper::current_cv_package()->package_id;
            $profileviews->user_package_id = Helper::current_cv_user_package()->user_package_id;
            $profileviews->created_at = date("Y-m-d H:i:s");
            $profileviews->updated_at = date("Y-m-d H:i:s");
            $profileviews->save();
        }
        if($exist_count > 0){
            $viewId = $exist[0]['profileview_id'];
            Profile_views::where('profileview_id',$viewId)
                        ->update(['updated_at'=>date("Y-m-d H:i:s"),'total'=>$exist[0]['total']+1]);
        }
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
         $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
         $redisp = 1;

         Helper::expire_package();
        return view('employer/job_seeker_details',compact('user','page','mycvfolders','employer_users','redisp'));
    }
    public function job_seeker_details1($id,$apply_id){
        $apply_id=Crypt::decryptString($apply_id);
        $jbsid = Crypt::decryptString($id);
        $page = "packages";
        $user_id = Auth::user()->id;
        $user = User::where('id',$jbsid)->get();
        $exist = Profile_views::where('user_id_fk',$user_id)
                                ->where('job_seeker_id',$jbsid)->get();
        $exist_count = count($exist);
        if($exist_count == 0){
            $profileviews = new Profile_views();
            $profileviews->user_id_fk = $user_id;
            $profileviews->job_seeker_id = $jbsid;
            $profileviews->created_at = date("Y-m-d H:i:s");
            $profileviews->updated_at = date("Y-m-d H:i:s");
            $profileviews->save();
        }
        if($exist_count > 0){
            $viewId = $exist[0]['profileview_id'];
            Profile_views::where('profileview_id',$viewId)
                        ->update(['updated_at'=>date("Y-m-d H:i:s"),'total'=>$exist[0]['total']+1]);
        }
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
         $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
         $redisp = 1;
        return view('employer/job_seeker_details2',compact('user','page','mycvfolders','employer_users','redisp','apply_id'));
    }
    public function sr_js_details($id){
        $jbsid = Crypt::decryptString($id);
        $page = "packages";
        $user_id = Auth::user()->id;
        $user = User::where('id',$jbsid)->get();
        $exist = Profile_views::where('user_id_fk',$user_id)
                                ->where('job_seeker_id',$jbsid)->get();
        $exist_count = count($exist);
        if($exist_count == 0){
            $profileviews = new Profile_views();
            $profileviews->user_id_fk = $user_id;
            $profileviews->job_seeker_id = $jbsid;
            $profileviews->created_at = date("Y-m-d H:i:s");
            $profileviews->updated_at = date("Y-m-d H:i:s");
            $profileviews->save();
        }
        if($exist_count > 0){
            $viewId = $exist[0]['profileview_id'];
            Profile_views::where('profileview_id',$viewId)
                        ->update(['updated_at'=>date("Y-m-d H:i:s")]);
        }
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        $redisp = 2;
         $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        return view('employer/job_seeker_details',compact('user','page','mycvfolders','employer_users','redisp'));
    }
    public function cv_detail_comments(Request $request){
        $comments = new Jobseeker_details_comments();
        $user_id = Auth::user()->id;
        $comments->comment_text = $request->comment;
        $comments->user_id_fk = $user_id;
        $comments->jobseeker_id_fk = $request->jobseekerid;
        $comments->created_at = date("Y-m-d H:i:s");
        $comments->updated_at = date("Y-m-d H:i:s");
        $comments->save();
        echo 1;
        die();
    }
    public function job_response_details($id){
        $page = "packages";
        $jobid = Crypt::decryptString($id);
        $keywords = Cv_search_details::all();
        $jobdetails = Job_post::where('job_id',$jobid)->get();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $subindustry = SubIndustryType::all();
        $user_id = Auth::user()->id;
        $user = User::where('id',$user_id)->get();
        $cvsearch_dtls = Cv_search_details::all();
        $jobId = $jobid;
        $myjobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $matchingId = $jobdetails[0]['relavance'];
        if($matchingId == 1){
            $jobresponses = Applied_job::where('job_id_fk',$jobid)
                            ->get()
                            ->sortBy(function($jr, $key) {
                                $appliedUser = $jr->applied_user2;
                                if(!empty($appliedUser->personal_details)){
                                    return $appliedUser->personal_details->first_name;
                                }
                            });
        }
        else if($matchingId == 2){
            $jobresponses = Applied_job::where('job_id_fk',$jobid)
                            ->get()
                            ->sortBy(function($jr, $key) {
                                $appliedUser = $jr->applied_user2;
                                if(!empty($appliedUser->personal_details)){
                                    return $appliedUser->personal_details->total_exp;
                                }
                            });
        }
        else{
            $jobresponses = Applied_job::where('job_id_fk',$jobid)->get();
        }
        $country = Countries::all();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        $search['keywords']=array();
        $search['min_exp']=array();
        $search['max_exp']=array();
        $search['pref_loc']=array();
        $search['min_salary']=array();
        $search['max_salary']=array();
        $search['industry']=array();
        $search['function']=array();
        $search['visa_status']=array();
        $search['maritalvals']=array();
        $search['gender']=array();
        $search['notice_period']=array();
        $search['designation']=array();
        $search['qualification']=array();
        $search['company']=array();
        return view('employer/job_response_details',compact('page','keywords','jobId','jobresponses','jobdetails','myjobfolders','industry','subindustry','user','cvsearch_dtls','country','employer_users','search'));
    }
    public function save_search(Request $request){
        $user_id = Auth::user()->id;
        if(Cvsearch_save::where('search_name',$request->search_name)->where('email',Auth::user()->email)->first())
        {
            echo 2; die();
        }
        $cvsearchsave = new Cvsearch_save();
        $cvsearchsave->search_id_fk = $request->search_id;
        $cvsearchsave->user_id_fk = $user_id;
        $cvsearchsave->search_name = $request->search_name;
        $cvsearchsave->alert_opt =  $request->set_alert;
        $cvsearchsave->email =  $request->email_id;
        $cvsearchsave->cv_frequency =  $request->alert_radio;
        $cvsearchsave->created_at = date("Y-m-d H:i:s");
        $cvsearchsave->updated_at =  date("Y-m-d H:i:s");
        $cvsearchsave->save();
        if(($request->set_alert == 1) && ($request->alert_radio == 2)){
                $searchID = $request->search_id;
                $search_data = Cv_search_details::where('search_id_fk', $searchID)->get();

                $jobseekerids = $this->get_matched_results($searchID);
                //print_r($jobseekerids);
                /*$data = User::whereIn('id',$jobseekerids)->get();
                return view('email/js_details',compact('data'));*/
                $profilematches = User::whereIn('id',$jobseekerids)->get();
                $mail_data = array(
                         'email' => $request->email_id,
                         'data' => $profilematches,
                     );
                Mail::send('email.js_details', $mail_data, function ($message) use ($mail_data) {
                             $message->subject('Matching Profiles for your search criteria')
                                     ->from('developer10@indglobal-consulting.com')
                                     ->to($mail_data['email']);
                });
        }
        echo 1; die();
    }
    public function refine_action(Request $request){
        $user_id = Auth::user()->id;
        $searchId = $request->searchId;
        $jobId = substr($searchId, 1);
        $keywords =  $request->keywords;
        $fareas = $request->fareas;
        $itypes = $request->itypes;
        $visavals = $request->visavals;
        $locations = $request->locations;
        $maritalvals = $request->maritalvals;
        $gendervals = $request->gendervals;
        $minexp = $request->minexpvals;
        $maxexp = $request->maxexpvals;
        $minmsal = $request->minmsalvals;
        $maxmsal = $request->maxmsalvals;
        $company = $request->company;
        $designation = $request->designation;
        $degree = $request->degree;
        $notice_period = $request->notice_period;
        $source = $request->source;

        $jobresponse_refines = new Refine_values();
        $jobresponse_refines->user_id_fk = $user_id;
        $jobresponse_refines->search_id_fk = $jobId;
        $jobresponse_refines->refine_keywords =  $keywords;
        $jobresponse_refines->fareas = $fareas;
        $jobresponse_refines->itypes = $itypes;
        $jobresponse_refines->visavals = $visavals;
        $jobresponse_refines->locations = $locations;
        $jobresponse_refines->maritalvals = $maritalvals;
        $jobresponse_refines->gendervals = $gendervals;
        $jobresponse_refines->min_exp = $minexp;
        $jobresponse_refines->max_exp = $maxexp;
        $jobresponse_refines->min_salary = $minmsal;
        $jobresponse_refines->max_salary = $maxmsal;
        $jobresponse_refines->company = $company;
        $jobresponse_refines->designation = $designation;
        $jobresponse_refines->degree = $degree;
        $jobresponse_refines->notice_period = $notice_period;
        $jobresponse_refines->source = $source;
        $jobresponse_refines->created_at = date("Y-m-d H:i:s");
        $jobresponse_refines->updated_at = date("Y-m-d H:i:s");
        $jobresponse_refines->save();
        $encryptedId = Crypt::encryptString($jobresponse_refines->refine_id);
        $response =  array("status" => 1,
            "refineId" => $encryptedId);
            echo json_encode($response);die();
    }
    public function job_responses_search(Request $request){
        $user_id = Auth::user()->id;
        $searchId = $request->hdn_searchId;
        $jobId = substr($searchId, 1);
        $keywords =  $request->keywords;
        $min_exp = $request->min_experience;
        $max_exp = $request->max_experience;
        $country = $request->country;
        $nation = $request->nationality;
        $includewords = $request->include_words;

        $jobresponse_refines = new Refine_values();
        $jobresponse_refines->user_id_fk = $user_id;
        $jobresponse_refines->search_id_fk = $jobId;
        $jobresponse_refines->search_keywords =  $keywords;
        $jobresponse_refines->min_exp = $min_exp;
        $jobresponse_refines->max_exp = $max_exp;
        $jobresponse_refines->country = $country;
        $jobresponse_refines->nation = $nation;
        $jobresponse_refines->include_words = $includewords;
        $jobresponse_refines->created_at = date("Y-m-d H:i:s");
        $jobresponse_refines->updated_at = date("Y-m-d H:i:s");
        $jobresponse_refines->save();

        $encryptedId = Crypt::encryptString($jobresponse_refines->refine_id);
        $response =  array("status" => 1,
            "refineId" => $encryptedId);
            echo json_encode($response);die();
    }
    public function refine_job_responses($id){
        $page = "packages";
        $keywords = Cv_search_details::all();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $subindustry = SubIndustryType::all();
        $user_id = Auth::user()->id;
        $user = User::where('id',$user_id)->get();
        $cvsearch_dtls = Cv_search_details::all();
        $myjobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $country = Countries::all();

        $refineId = Crypt::decryptString($id);
        $refine_data = Refine_values::where('refine_id',$refineId)->get();
        $jobId =  $refine_data[0]['search_id_fk'];
        $keywords = $refine_data[0]['refine_keywords'];
        $fareas = $refine_data[0]['fareas'];
        $itypes = $refine_data[0]['itypes'];
        $visavals = $refine_data[0]['visavals'];
        $locations = $refine_data[0]['locations'];
        $maritalvals = $refine_data[0]['maritalvals'];
        $gendervals = $refine_data[0]['gendervals'];
        $countryval = $refine_data[0]['country'];
        $nation = $refine_data[0]['nation'];
        $min_exp = $refine_data[0]['min_exp'];
        $max_exp = $refine_data[0]['max_exp'];
        $include_words = $refine_data[0]['include_words'];
        $company = $refine_data[0]['company'];
        $designation = $refine_data[0]['designation'];
        $degree = $refine_data[0]['degree'];
        $notice_period = $refine_data[0]['notice_period'];
        $min_salary = $refine_data[0]['min_salary'];
        $max_salary = $refine_data[0]['max_salary'];
        $source = $refine_data[0]['source'];
        $search_keywords = $refine_data[0]['search_keywords'];

        $srchval_maxexp=explode(',', $max_exp);
        $srchval_maxsal=explode(',', $max_salary);
        $search['keywords']=explode(',',$keywords);
        $search['min_exp']=explode(',', $min_exp)[0];
        $search['max_exp']=$srchval_maxexp[count($srchval_maxexp)-1];
        $search['pref_loc']=explode(',',$locations);
        $search['min_salary']=explode(',', $min_salary)[0];
        $search['max_salary']=$srchval_maxsal[count($srchval_maxsal)-1];
        $search['industry']=explode(',',$itypes);
        $search['function']=explode(',',$fareas);
        $search['visa_status']=explode(',',$visavals);
        $search['maritalvals']=empty($maritalvals)?array():explode(',',$maritalvals);
        $search['gender']=explode(',',$gendervals);
        $search['notice_period']=explode(',',$notice_period);
        $search['designation']=explode(',',$designation);
        $search['qualification']=explode(',',$degree);
        $search['company']=explode(',',$company);
// print_r($search['maritalvals']);die;
        if(explode(',', $source)[0]==1)
        {
            $matchedusers = Applied_job::where('job_id_fk',$jobId)->pluck('user_id_fk')->toArray();
        }
        elseif (explode(',', $source)[0]==2) {
            $matchedusers=Helper::profilesmatches_by_post1($jobId);
        }
        elseif(explode(',', $source)[0]==3)
        {
            $matchedusers=Folder_move::where('user_id_fk',$user_id)
                                    ->where('folder_id_fk',$folderId)->pluck('content_id')->toArray();
        }
        else{
        	$matchedusers = Applied_job::where('job_id_fk',$jobId)->pluck('user_id_fk')->toArray();
        }

        if($include_words==2)
        {
        	$matchedusers2=array();
        	if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            $result=[];
	            foreach ($search_keywords1 as $key) {
	                $data=Job_seeker_technical_skills::where('skill','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            foreach ($search_keywords1 as $key) {
	                $data=Career_history::where('job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            foreach ($search_keywords1 as $key) {
	                $data=Project::where('role','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            foreach ($search_keywords1 as $key) {
	                $data=Job_preference::where('preferred_job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	            $matchedusers2=$result;
	        }
	        $matchedusers=User::whereIn('id',$matchedusers)->whereNotIn('id',$matchedusers2)->pluck('id')->toArray();
			/*if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            $result=[];
	            foreach ($search_keywords1 as $key) {
	                $data=Job_seeker_technical_skills::where('skill','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	            $matchedusers=$result;
	        }

	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            $result=[];
	            foreach ($search_keywords1 as $key) {
	                $data=Career_history::where('job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	            $matchedusers=$result;
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            $result=[];
	            foreach ($search_keywords1 as $key) {
	                $data=Project::where('role','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	            $matchedusers=$result;
	        }
	        dd($matchedusers);
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            $result=[];
	            foreach ($search_keywords1 as $key) {
	                $data=Job_preference::where('preferred_job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	            $matchedusers=$result;
	        }*/
	    }
	    else {
	    	
			if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            $result=[];
	            foreach ($search_keywords1 as $key) {
	                $data=Job_seeker_technical_skills::where('skill','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            foreach ($search_keywords1 as $key) {
	                $data=Career_history::where('job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            foreach ($search_keywords1 as $key) {
	                $data=Project::where('role','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	        }
	        if(!empty($search_keywords)){
	            $search_keywords1=explode(',', $search_keywords);
	            foreach ($search_keywords1 as $key) {
	                $data=Job_preference::where('preferred_job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
	                $result=array_merge($data,$result);
	            }
	            $matchedusers=$result;
	        }
	    }


        // if(!empty($countryval)){
        //     $result = Job_seeker_personal_details::where('country_id',$countryval)->get();
        //     $matchedusers=$result;
        // }
        // dd($nation);
        if(!empty($nation)){
            $result = Job_seeker_personal_details::where('nationality','like','%'.$nation.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($keywords)){
            $arr_keywords = explode(",", $keywords);
            $result=array();
            if(sizeof($arr_keywords)>0){
                foreach ($arr_keywords as $keyword) {
                   $data = Job_seeker_technical_skills::where('skill','like','%'.$keyword.'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                   $result=array_merge($data,$result);

                }
            }
            $matchedusers=$result;
        }
        if(!empty($company)){
            // dd($company);
            $company1=explode(',', $company);
            $result=[];
            foreach ($company1 as $key) {         
               $data = Career_history::where('employer_name','like','%'.$key.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }

        if(!empty($designation)){

            $designation1=explode(',', $designation);
            $result=[];
            foreach ($designation1 as $key) {         
               $data = Career_history::where('job_title','like','%'.$key.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            $matchedusers=$result;
        }
        if(!empty($fareas)){
            $arr_fareas = explode(",", $fareas);
            $result=[];
            if(sizeof($arr_fareas)>0){
                foreach ($arr_fareas as $farea) {
                    $data = Job_preference::where('preferred_job_function','like','%'.$farea.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
                }
            }
            $matchedusers=$result;
        }
        if(!empty($itypes)){
            $arr_itypes = explode(",", $itypes);
            $result=[];
            if(sizeof($arr_itypes)>0){
                foreach ($arr_itypes as $itype) {
                    $data = Job_preference::where('preferred_industry_type','like','%'.$itype.'%')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
                }
            }
            $matchedusers=$result;
        }
        // if((!empty($min_salary))&&(!empty($max_salary))){
            
        //     $min_salary = explode(',', $min_salary)[0];
        //     $max_salary=explode(',', $max_salary);
        //     $max_salary = $max_salary[count($max_salary)-1];
        //     $result = Career_history::whereBetween('monthly_salary',[$min_salary,$max_salary])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        //     $matchedusers=$result;
        // }
        // elseif((!empty($min_salary))&&(empty($max_salary))){
        //     $min_salary = explode(',', $min_salary)[0];
        //     $result = Career_history::where('monthly_salary','>=',$min_salary)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        //     $matchedusers=$result;
        // }
        // elseif((empty($min_salary))&&(!empty($max_salary))){
        //     $max_salary=explode(',', $max_salary);
        //     $max_salary = $max_salary[count($max_salary)-1];
        // dd($matchedusers2);
        //     $result = Career_history::where('monthly_salary','<=',$max_salary)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        //     $matchedusers=$result;
        // }
            if((!empty($min_salary))&&(!empty($max_salary))){
            
            $min_salary = explode(',', $min_salary)[0];
            $max_salary=explode(',', $max_salary);
            $max_salary = $max_salary[count($max_salary)-1];
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[0]>=$min_salary && explode('-',$value->preferred_monthly_salary)[1]<=$max_salary)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((!empty($min_salary))&&(empty($max_salary))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[0]>=$min_salary)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((empty($min_salary))&&(!empty($max_salary))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[1]<=$max_salary)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        // dd($max_salary);

        if(!empty($visavals) && count(explode(',',$visavals))!=2 && explode(',',$visavals)[0]=='Yes' ){
            $result = Job_seeker_personal_details::where('current_visa_status','<>',"")->where('current_visa_status','<>','no')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif(!empty($visavals) && count(explode(',',$visavals))!=2 && explode(',',$visavals)[0]=='No' ){
            $result = Job_seeker_personal_details::where(function ($query) {$query->orwhereNull('current_visa_status')->orwhere('current_visa_status',"")->orwhere('current_visa_status','no');})->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($degree)){
            $result = Academic_details::whereIn('qualification',explode(',',$degree))->whereIn('user_id_fk',$matchedusers)->where('qualification_type',1)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($locations)){
            $locations1 = explode(',',$locations);
            $result=array();
            foreach ($locations1 as $key => $value) {
                    $data = Job_preference::where('preferred_job_location','like','%'.$value.'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                    // dd($result);
                $result=array_merge($data,$result);
            }

            $matchedusers=$result;
        }

        if(!empty($maritalvals)){
            $maritalvals=explode(',', $maritalvals);
            $result = Job_seeker_personal_details::whereIn('marital_status',$maritalvals)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($gendervals)){
            $gendervals = explode(",", $gendervals);
            $result = Job_seeker_personal_details::whereIn('gender',$gendervals)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if((!empty($min_exp))&&(!empty($max_exp))){

            $min_exp= explode(',', $min_exp)[0];
            $max_exp=explode(',', $max_exp);
            $max_exp=$max_exp[count($max_exp)-1];
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->whereBetween('total_exp',[$min_exp,$max_exp])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        else if((empty($min_exp))&&(!empty($max_exp))){
            $max_exp=explode(',', $max_exp);
            $max_exp=$max_exp[count($max_exp)-1];
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','<=',$max_exp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((!empty($min_exp))&&(empty($max_exp))){
            $min_exp= explode(',', $min_exp)[0];
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','>=',$min_exp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($notice_period)){
            $results=array();
            foreach (explode(',',$notice_period) as $key => $value) {
                if($value=="Immediate")
                {

                    $r = Job_seeker_personal_details::where('notice_period','Immediate')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                }
                else{
                    $result = Job_seeker_personal_details::whereIn('user_id_fk',$matchedusers)->get();
                    $r=array();
                    foreach ($result as $value) {

                        if($value->notice_period=="Immediate" )
                        {
                            $r[]=$value->user_id_fk;
                        }
                        elseif (explode(' ',$value->notice_period)[0] <= $value) {
                           $r[]=$value->user_id_fk;
                        }
                    }
                }
                $results=array_merge($r,$results);
            }
            $matchedusers=$results;
        }

        //print_r($this->jsids);
        $jobdetails = Job_post::where('job_id',$jobId)->get();
        $matchingId = $jobdetails[0]['relavance'];
        if($matchingId == 1){
            $jobresponses = Applied_job::where('job_id_fk',$jobId)->whereIn('user_id_fk',$matchedusers)->get()
                            ->sortBy(function($jr, $key) {
                                $appliedUser = $jr->applied_user2;
                                if(!empty($appliedUser->personal_details)){
                                    return $appliedUser->personal_details->first_name;
                                }
                            });
        }
        else if($matchingId == 2){
            $jobresponses = Applied_job::where('job_id_fk',$jobId)->whereIn('user_id_fk',$matchedusers)->get()
                            ->sortBy(function($jr, $key) {
                                $appliedUser = $jr->applied_user2;
                                if(!empty($appliedUser->personal_details)){
                                    return $appliedUser->personal_details->total_exp;
                                }
                            });
        }
        else{
        $jobresponses = Applied_job::where('job_id_fk',$jobId)
                                    ->whereIn('user_id_fk',$matchedusers)->get();
        }
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        return view('employer/job_response_details',compact('page','keywords','jobId','jobresponses','jobdetails','myjobfolders','industry','subindustry','user','cvsearch_dtls','country','employer_users','search'));
    }
    public function search_relavance_action(Request $request){
        $relavanceId = $request->id;
        $hdnsearchId = $request->searchid;
        $searchId = substr($hdnsearchId, 1);
        Cv_search::where('cv_search_id',$searchId)->update(['relavance'=>$relavanceId]);
        //print_r(Cv_search::where('cv_search_id',$searchId)->first());exit();
        echo 1; die();
    }
    public function jp_relavance_action(Request $request){
        $relavanceId = $request->id;
        $hdnsearchId = $request->searchid;
        $searchId = substr($hdnsearchId, 1);
        Job_post::where('job_id',$searchId)->update(['relavance'=>$relavanceId]);
        echo 1; die();
    }
    public function refine_searchresult_responses($id){
        $page = "packages";
        $keywords = Cv_search_details::all();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $subindustry = SubIndustryType::all();
        $user_id = Auth::user()->id;
        $user = User::where('id',$user_id)->get();
        $cvsearch_dtls = Cv_search_details::all();
        $myjobfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',2)->get();
        $country = Countries::all();

        $refineId = Crypt::decryptString($id);
        $refine_data = Refine_values::where('refine_id',$refineId)->get();
        $searchId =  $refine_data[0]['search_id_fk'];
        $keywords = $refine_data[0]['refine_keywords'];
        $fareas = $refine_data[0]['fareas'];
        $itypes = $refine_data[0]['itypes'];
        $visavals = $refine_data[0]['visavals'];
        $locations = $refine_data[0]['locations'];
        $maritalvals = $refine_data[0]['maritalvals'];
        $gendervals = $refine_data[0]['gendervals'];
        // $country = $refine_data[0]['country'];
        // $nation = $refine_data[0]['nation'];
        $min_exp = $refine_data[0]['min_exp'];
        $max_exp = $refine_data[0]['max_exp'];
        // $include_words = $refine_data[0]['include_words'];


        $company = $refine_data[0]['company'];
        $designation = $refine_data[0]['designation'];
        $degree = $refine_data[0]['degree'];
        $notice_period = $refine_data[0]['notice_period'];
        $min_salary = $refine_data[0]['min_salary'];
        $max_salary = $refine_data[0]['max_salary'];
        

        //echo $searchId;
        $searchdata = Cv_search::where('cv_search_id',$searchId)->get();
        $matchingId = $searchdata[0]['relavance'];
        $cv_search = Cv_search::where('cv_search_id',$searchId)->get();
        
        //if($matchingId == 1)
        $cv_search_details = Cv_search_details::where('search_id_fk',$searchId)->get();
        $srchval_keyword=$keywords;

        if(!empty($min_exp))
        {
            $srchval_minexp = explode(',', $min_exp)[0];
        }
        else
        {
            $srchval_minexp = '';
        }

        if(!empty($max_exp))
        {
            $max_exp=explode(',', $max_exp);
            $srchval_maxexp = $max_exp[count($max_exp)-1];
        }
        else
        {
            $srchval_maxexp = '';
        }

        $from_age = $cv_search_details[0]['from_age'];
        $to_age = $cv_search_details[0]['to_age'];
        $srchval_cloc = $cv_search_details[0]['cur_loc'];

        $srchval_eloc = $locations;

        if(!empty($min_salary))
        {
            $srchval_minsal = explode(',', $min_salary)[0];
        }
        else
        {
            $srchval_minsal = '';
        }

        if(!empty($max_salary))
        {
            $max_salary=explode(',', $max_salary);
            $srchval_maxsal = $max_salary[count($max_salary)-1];
        }
        else
        {
            $srchval_maxsal = '';
        }

// dd($srchval_minsal);
        $itype = $itypes;
        $farea = $fareas;

        $nation =  $cv_search_details[0]['nation'];
        $visaval = $visavals;
        $genderval = $gendervals;
        $last_active =  $cv_search_details[0]['last_active'];
        $last_updated =  $cv_search_details[0]['last_updated'];

        $notice_period = $notice_period;
        // dd($notice_period);
        $vehicle_type =  $cv_search_details[0]['vehicle_type'];

        $current_job_title = $designation;
        $qualification = $degree;
        $specialization =  $cv_search_details[0]['specialization'];

        $current_employer_name = $company;
        $dl =  $cv_search_details[0]['has_dl'];


        if(!empty($maritalvals))
        {
            $maritalvals =  explode(',',$maritalvals);
        }
        else
        {
            $maritalvals =  '';
        }

        

        if(!empty($cv_search_details[0]['jobtype']))
            $jobtype =  explode(',',$cv_search_details[0]['jobtype']);
        else
            $jobtype =  $cv_search_details[0]['jobtype'];
        $langs = $cv_search_details[0]['languages'];



        $search['keywords']=explode(',',$srchval_keyword);
        $search['min_exp']=$srchval_minexp;
        $search['max_exp']=$srchval_maxexp;
        $search['pref_loc']=explode(',',$srchval_eloc);
        $search['min_salary']=$srchval_minsal;
        $search['max_salary']=$srchval_maxsal;
        $search['industry']=explode(',',$itype);
        $search['function']=explode(',',$farea);
        $search['visa_status']=explode(',',$visaval);
        $search['maritalvals']=empty($maritalvals)?array():$maritalvals;
        $search['gender']=explode(',',$genderval);
        $search['notice_period']=explode(',',$notice_period);
        $search['designation']=explode(',',$current_job_title);
        $search['qualification']=explode(',',$qualification);
        $search['company']=explode(',',$current_employer_name);


        $skils=Cv_search_techskills::where('search_id_fk',$searchId)->get();
        //echo json_encode($cv_search_details[0]['keyword']);
        $matchedusers= User::where('role',2)->where('email_verify',2)->where('enabled',1)->pluck('id')->toArray();
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Job_seeker_technical_skills::where('skill','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Career_history::where('job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        // dd($matchedusers);
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Project::where('role','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            // $matchedusers=$result;
        }
        if(!empty($srchval_keyword)){
            // dd(explode(',', $location));
            $srchval_keyword1=explode(',', $srchval_keyword);
            // $result=[];
            foreach ($srchval_keyword1 as $key) {
                $data=Job_preference::where('preferred_job_title','like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }

// dd($matchedusers);
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Job_seeker_technical_skills::where('skill','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        // dd($result);
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Career_history::where('job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Project::where('role','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($srchval_exkeyword)){
            // dd(explode(',', $location));
            $srchval_exkeyword1=explode(',', $srchval_exkeyword);
            $result=[];
            foreach ($srchval_exkeyword1 as $key) {
                $data=Job_preference::where('preferred_job_title','not like','%'.$key.'%')->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
   // dd($matchedusers);
        DB::connection()->enableQueryLog();
        if((!empty($srchval_minexp))&&(!empty($srchval_maxexp))){
            // print_r("expression");
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->whereBetween('total_exp',[$srchval_minexp,$srchval_maxexp])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
        elseif((!empty($srchval_minexp))&&(empty($srchval_maxexp))){
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','>=',$srchval_minexp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        else if((empty($srchval_minexp))&&(!empty($srchval_maxexp))){
            // dd($matchedusers);

        // DB::connection()->enableQueryLog();
            $result = Job_seeker_personal_details::where('total_exp','<>',"")->where('total_exp','<=',$srchval_maxexp)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
        // dd($matchedusers);

            // dd($srchval_cloc);
        if(!empty($srchval_cloc)){
            $result = Job_seeker_personal_details::where('current_location',$srchval_cloc)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            // $matchedusers=array_merge($result,$matchedusers);
            $data['query']=DB::getQueryLog();
        }
        if(!empty($srchval_cloc)){
            $result = Job_seeker_personal_details::where('zip',$srchval_cloc)->whereNotIn('user_id_fk',$result)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
            // $data['query']=DB::getQueryLog();
        }
// dd($srchval_eloc);
        if(!empty($srchval_eloc)){
            $srchval_eloc1 = explode(',',$srchval_eloc);
            $result=array();
            foreach ($srchval_eloc1 as $key => $value) {
                    $data = Job_preference::where('preferred_job_location','like','%'.$value.'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                    // dd($result);
                $result=array_merge($data,$result);
            }

            $matchedusers=$result;
        }
        if((!empty($srchval_minsal))&&(!empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[0]>=$srchval_minsal && explode('-',$value->preferred_monthly_salary)[1]<=$srchval_maxsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((!empty($srchval_minsal))&&(empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[0]>=$srchval_minsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        elseif((empty($srchval_minsal))&&(!empty($srchval_maxsal))){
            $result = Job_preference::where('preferred_monthly_salary','<>','')->whereIn('user_id_fk',$matchedusers)->get();
            $r=array();
            foreach ($result as $key => $value) {
               if(explode('-',$value->preferred_monthly_salary)[1]<=$srchval_maxsal)
               {
                $r[]=$value->user_id_fk;
               }
            }
            $matchedusers=$r;
        }
        // if((!empty($srchval_minsal))&&(!empty($srchval_maxsal))){
        //     $result = Career_history::whereBetween('monthly_salary',[$srchval_minsal,$srchval_maxsal])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        //     $matchedusers=$result;
        // }
        // elseif((!empty($srchval_minsal))&&(empty($srchval_maxsal))){
        //     $result = Career_history::where('monthly_salary','>=',$srchval_minsal)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        //     $matchedusers=$result;
        // }
        // elseif((empty($srchval_minsal))&&(!empty($srchval_maxsal))){
        //     $result = Career_history::where('monthly_salary','<=',$srchval_maxsal)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        //     $matchedusers=$result;
        // }
// dd($matchedusers);

        if((!empty($from_age))&&(!empty($to_age))){
            $date1=date('Y-m-d',strtotime('-'.$from_age.' years ', strtotime(date('Y-m-d'))));
            $date2=date('Y-m-d',strtotime('-'.$to_age.' years ', strtotime(date('Y-m-d'))));
            // dd($date2);
            $result = Job_seeker_personal_details::whereBetween('dob',[$date1,$date2])->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((!empty($from_age))&&(empty($to_age))){
            $date1=date('Y-m-d',strtotime('-'.$from_age.' years ', strtotime(date('Y-m-d'))));
            $result = Job_seeker_personal_details::where('dob','>=',$date1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((empty($from_age))&&(!empty($to_age))){
            $date2=date('Y-m-d',strtotime('-'.$to_age.' years ', strtotime(date('Y-m-d'))));
            $result = Job_seeker_personal_details::where('dob','<=',$date2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($itype)){
            $result = Job_preference::whereIn('preferred_industry_type',explode(',',$itype))->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($farea)){
            $result = Job_preference::whereIn('preferred_job_function',explode(',',$farea))->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        // dd($farea);
        if(!empty($nation)){
            $result = Job_seeker_personal_details::where('nationality',$nation)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($visaval) && count(explode(',',$visaval))!=2 && explode(',',$visaval)[0]=='Yes' ){
            $result = Job_seeker_personal_details::where('current_visa_status','<>',"")->where('current_visa_status','<>','no')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif(!empty($visaval) && count(explode(',',$visaval))!=2 && explode(',',$visaval)[0]=='No' ){
            $result = Job_seeker_personal_details::where(function ($query) {$query->orwhereNull('current_visa_status')->orwhere('current_visa_status',"")->orwhere('current_visa_status','no');})->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($genderval)){
            $result = Job_seeker_personal_details::whereIn('gender',explode(',',$genderval))->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($dl) && $dl!=2){
            $result = Job_seeker_personal_details::where('driving_liicence',$dl)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($current_job_title)){

            $current_job_title1=explode(',', $current_job_title);
            $result=[];
            foreach ($current_job_title1 as $key) {         
               $data = Career_history::where('job_title','like','%'.$key.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            $matchedusers=$result;
        }
        if(!empty($current_employer_name)){
            $current_employer_name1=explode(',', $current_employer_name);
            $result=[];
            foreach ($current_employer_name1 as $key) {         
               $data = Career_history::where('employer_name','like','%'.$key.'%')->where('current_company',2)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                $result=array_merge($data,$result);
            }
            // dd($result);
            $matchedusers=$result;
        }
        if(!empty($maritalvals)){
            $result = Job_seeker_personal_details::whereIn('marital_status',$maritalvals)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($vehicle_type)){
            $result = Job_seeker_personal_details::whereIn('vtype',$vehicle_type)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($jobtype)){
            $result = Career_history::whereIn('employement_type',$jobtype)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        
        if((!empty($qualification))&&(!empty($specialization))){
            $result = Academic_details::whereIn('qualification',explode(',',$qualification))->whereIn('specialization',explode(',',$specialization))->where('qualification_type',1)->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((!empty($qualification))&&(empty($specialization))){
            $result = Academic_details::whereIn('qualification',explode(',',$qualification))->whereIn('user_id_fk',$matchedusers)->where('qualification_type',1)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        elseif((empty($qualification))&&(!empty($specialization))){
            $result = Academic_details::whereIn('specialization',explode(',',$specialization))->whereIn('user_id_fk',$matchedusers)->where('qualification_type',1)->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($notice_period)){
            $results=array();
            foreach (explode(',',$notice_period) as $key => $value) {
                if($value=="Immediate")
                {

                    $r = Job_seeker_personal_details::where('notice_period','Immediate')->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
                }
                else{
                    $result = Job_seeker_personal_details::whereIn('user_id_fk',$matchedusers)->get();
                    $r=array();
                    foreach ($result as $value) {

                        if($value->notice_period=="Immediate" )
                        {
                            $r[]=$value->user_id_fk;
                        }
                        elseif (explode(' ',$value->notice_period)[0] <= $value) {
                           $r[]=$value->user_id_fk;
                        }
                    }
                }
                $results=array_merge($r,$results);
            }
            $matchedusers=$results;
        }

        if(!empty($langs)){
            $lang_arr = explode(",",$langs);
            if(count($lang_arr) > 0){
                $result=[];
                foreach($lang_arr as $lng){
                    $data = Job_seeker_personal_details::where('known_languages','like','%'.$lng.'%')->whereIn('user_id_fk',$matchedusers)->whereNotIn('user_id_fk',$result)->pluck('user_id_fk')->toArray();
                    $result=array_merge($data,$result);
                }
                $matchedusers=$result;
            }
        }

        if(!empty($last_active))
        {
            $date=date('Y-m-d 00:00:00',strtotime('-'.$last_active.'days ', strtotime(date('Y-m-d'))));
            // print_r($date);die;
            // dd($matchedusers); 
            $result=Last_login::where('login_time','>=',$date)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }
        if(!empty($last_updated))
        {
            $date=date('Y-m-d 00:00:00',strtotime('-'.$last_updated.'days ', strtotime(date('Y-m-d'))));
            // print_r($date);die;
            // dd($matchedusers); 
            $result=Job_seeker_cv::where('updated_at','>=',$date)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
            $matchedusers=$result;
        }

        if(!empty($skils))
        {
            foreach ($skils as $key) {
                $result=Job_seeker_technical_skills::where('skill','like','%'.$key->skill.'%')->where('level_of_expertise',$key->expertise)->where('years_of_experience','>=',$key->experience)->where('year_last_used','>=',$key->last_used)->whereIn('user_id_fk',$matchedusers)->distinct()->pluck('user_id_fk')->toArray();
                $matchedusers=$result;
            }
        }

        $matchedusers = Job_seeker_personal_details::where('visibilty','<>','3')
                                                      ->whereIn('user_id_fk',$matchedusers)->pluck('user_id_fk')->toArray();
        $fbjsids = array();
        foreach($matchedusers as $a){
            $employer_data = Employer::where('user_id_fk',$user_id)->first();
            $check_block = Block_company::where('employer_id_fk',  $employer_data->employer_id)
                                        ->where('user_id_fk',$a)->get();
            if(count($check_block) == 0){
                array_push($fbjsids, $a); 
            }             
        }
        // dd($fbjsids);
        if($matchingId == 1){
        $jobseekers = User::whereIn('id',$fbjsids)
                            ->get()
                            ->sortBy(function($js, $key) {
                                return $js->personal_details->first_name;
                            });
        }
        else if($matchingId == 2){
        $jobseekers = User::whereIn('id',$fbjsids)
                            ->get()
                            ->sortBy(function($js, $key) {
                                return $js->personal_details->total_exp;
                            });
        }
        else{
            $jobseekers = User::whereIn('id',$fbjsids)->get();
        }
        //exit();
        $mycvfolders = Folder::where('user_id_fk',$user_id)->where('usedfor',1)->get();
        $latestsearch = Cv_search::orderBy("created_at","desc")->first();
        $profilesviewed = Profile_views::where('user_id_fk',$user_id)->get();
        $profileviewcnt = count($profilesviewed);
        $cvsdownloaded = Cv_downloads::where('user_id_fk',$user_id)->get();
        $downloadcnt = count($cvsdownloaded);
        $user = User::where('id',$user_id)->get();
        $cvsearch_dtls = Cv_search_details::all();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $subindustry = SubIndustryType::all();
        $employer_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
         $employer_f_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        if(count($employer_f_users)>0){
        $employer_folder_users = Employer::where('parent_id',$user_id)->where('activate',2)->get();
        }
        else{
          $parent_data = Employer::where('user_id_fk',$user_id)->first();
            $employer_folder_users = Employer::where('user_id_fk',$parent_data->parent_id)->get();
        }
        //print_r($latestsearch); exit();
        Cv_search::where('cv_search_id',$searchId)->update(['result_count'=>sizeof($matchedusers)]);
        return view('employer/employer_search_results',compact('page','cv_search','searchId','jobseekers','mycvfolders','latestsearch','downloadcnt','profileviewcnt','user','cvsearch_dtls','industry','subindustry','employer_users','search','employer_folder_users'));
    }
    public function addtoarr($arr){
        if(count($arr)>0){
            foreach($arr as $a){
                array_push($this->jsids, $a->user_id_fk);              
            }
        }
    }
    public function savedsearch_dtls(Request $request){
        $search_id = $request->id;
        $datasaved = Cvsearch_save::where('id',$search_id)->get();
        $response =  array("status" => 1,
             "savedvals" =>  $datasaved[0]);
        echo json_encode($response); die();
    }
    public function folder_dtls(Request $request){
        $folder_id = $request->id;
        $datasaved = Folder::where('folder_id',$folder_id)->get();
        $response =  array("status" => 1,
             "savedvals" =>  $datasaved[0]);
        echo json_encode($response); die();
    }
    public function edit_savedsearch(Request $request){
        $search_id = $request->searchId;
        $s_name = $request->s_name;
        if(Cvsearch_save::where('search_name',$s_name)->where('email',Auth::user()->email)->first())
        {
            echo 2; die();
        }else{
            Cvsearch_save::where('id',$search_id)->update(['search_name'=>$s_name]);
            echo 1; die();
        }
        
    }
    public function rename_folder(Request $request){
        //print_r($request->all());exit();
        $folder_id = $request->modal_folderid;
        $f_name = $request->rf_name;
        $usedfor = $request->rf_type;
        if(Folder::where('folder_name',$f_name)->where('usedfor',$usedfor)->first())
            {
                echo 2; die();
            }else{
               Folder::where('folder_id',$folder_id)->update(['folder_name'=>$f_name]);
               
                echo 1; die();
            }
        
    }
    public function responses_download($id){
        $jobId = Crypt::decryptString($id);
        // dd($jobId);
        $jobresponses = Applied_job::where('job_id_fk',$jobId)->pluck('user_id_fk')->toArray();
        $p_dt = date("Y-m-d H:i:s");
        $job_seeker=Job_seeker_personal_details::whereIn('user_id_fk',$jobresponses)->where('visibilty','<>',3)->get();
        // dd($jobresponses);
        $job_seeker_details=array();
        foreach ($job_seeker as $key => $value) {
            $job_seeker_details[$key+1]['first_name']=$value->first_name;
            $job_seeker_details[$key+1]['last_name']=$value->last_name;
            $job_seeker_details[$key+1]['gender']=$value->gender;
            $job_seeker_details[$key+1]['dob']=$value->dob;
            $job_seeker_details[$key+1]['marital_status']=$value->marital_status;
            $job_seeker_details[$key+1]['nationality']=$value->nationality;
            $job_seeker_details[$key+1]['current_location']=$value->current_location;
            $job_seeker_details[$key+1]['mobile_number']=$value->mobile_number;
            $job_seeker_details[$key+1]['email_id']=$value->email_id;
            $job_seeker_details[$key+1]['alternative_email_id']=$value->alternative_email_id;
            $job_seeker_details[$key+1]['notice_period']=$value->notice_period;
            $job_seeker_details[$key+1]['total_exp']=$value->total_exp;
        }
        // dd($job_seeker_details);
        Excel::create('job_responses_'.$p_dt, function($excel) use ($job_seeker_details) {
        $excel->sheet('mySheet', function($sheet) use ($job_seeker_details)
        {
            $sheet->fromArray($job_seeker_details);
        });
        })->download('xlsx');
        $redirect_to = url('/employer-packages/');
        return redirect($redirect_to);
    }
    public function share_unshare_folder(Request $request){
        $user_selected = $request->share_user;
        $folder_id = $request->modal_shareid;
        $user_id = Auth::user()->id;
        $share_type = $request->share_type;
        if($share_type == 2){
            $check = Folder_share::where('user_id_fk',$user_id)
                    ->where('employer_id_fk',$user_selected)
                    ->where('folder_id_fk',$folder_id)
                    ->where('status',1)
                    ->first();
            if(!empty($check)){
                Folder_share::where('share_id',$check->share_id)
                             ->update(['status'=>'2']);
                echo 4; die();
            }
            else{
                 echo 3; die();
            }
        }
        else{
        $check = Folder_share::where('user_id_fk',$user_id)
                    ->where('employer_id_fk',$user_selected)
                    ->where('folder_id_fk',$folder_id)
                    ->first();
        if(!empty($check)){
            if($check->status == 1){
            echo 2; die();  
            }
            else{
            Folder_share::where('share_id',$check->share_id)
                             ->update(['status'=>'1']);
            echo 1; die();  
            }
        }
        else{
            $fshare = new Folder_share();
            $fshare->user_id_fk = $user_id;
            $fshare->employer_id_fk = $user_selected;
            $fshare->folder_id_fk = $folder_id;
            $fshare->status = 1;
            $fshare->save();
            echo 1; die();
        }
      }
    }
    public function share_search(Request $request){
        $user_selected = $request->share_user;
        $search_id = $request->modal_shareid;
        $user_id = Auth::user()->id;
        
        $check = Savedsearch_share::where('user_id_fk',$user_id)
                    ->where('employer_id_fk',$user_selected)
                    ->where('savedsearch_id',$search_id)
                    ->first();
        if(!empty($check)){
             echo 2; die();  
        }
        else{
            $sshare = new Savedsearch_share();
            $sshare->user_id_fk = $user_id;
            $sshare->employer_id_fk = $user_selected;
            $sshare->savedsearch_id = $search_id;
            $sshare->save();
            echo 1; die();
        }
    }
    public function forward_profiles(Request $request){
        $user_selected = $request->forward_user;
        $js_ids = $request->modal_forwardid;
        $js_arr = explode(",", $js_ids);
        $user_id = Auth::user()->id;
        if(count($js_arr)>0){
            foreach($js_arr as $js){
                if(!empty($js)){
                    $check = Sr_forwards::where('js_id_fk','like','%'.$js.'%')
                    ->where('user_id_fk',$user_id)
                    ->where('employer_id_fk',$user_selected)
                    ->first();
                    if(!empty($check)){
                         echo 2; die();  
                    }
                    else{
                        $sshare = new Sr_forwards();
                        $sshare->js_id_fk = $js;
                        $sshare->user_id_fk = $user_id;
                        $sshare->employer_id_fk = $user_selected;
                        $sshare->save();
                    }
                }
            }
        }
        echo 1; die();
    }
    public function forward_profiles1(Request $request){
        $user_selected = $request->forward_user;
        $js_ids = $request->modal_forwardid;
        $js_arr = explode(",", $js_ids);
        $user_id = Auth::user()->id;
        if(count($js_arr)>0){
            foreach($js_arr as $js){
                if(!empty($js)){
                    $check = Sr_forwards::where('js_id_fk','like','%'.$js.'%')
                    ->where('user_id_fk',$user_id)
                    ->where('employer_id_fk',$user_selected)
                    ->first();
                    if(!empty($check)){
                         echo 2; die();  
                    }
                    else{
                        $sshare = new Sr_forwards();
                        $sshare->js_id_fk = $js;
                        $sshare->user_id_fk = $user_id;
                        $sshare->employer_id_fk = $user_selected;
                        $sshare->save();
                    }
                }
            }
        }
        echo 1; die();
    }
    public function folder_remove(Request $request){
        $folderid = $request->id;
        Folder::where('folder_id',$folderid)->delete();
        Folder_move::where('folder_id_fk',$folderid)->delete();
        Folder_share::where('folder_id_fk',$folderid)->delete();
        echo 1; die();
    }

    public function edit_draft($id)
    {
        // print_r($id);exit();
        $job = Job_post::where('job_id',$id)->first();
        $id_obj = Course::where('course_name',explode(',',$job->qualification_degree)[0])->first();
        if(!empty($id_obj))
            $branches = Specialization::where('course_id_fk',$id_obj->course_id)->get();
        else
            $branches=array();

        $id_obj = PGCourse::where('pgc_name',explode(',',$job->qualification_pg)[0])->first();
        if(!empty($id_obj))
            $branches_pg = PGSpecialization::where('pgc_id_fk',$id_obj->pgc_id)->get();
        else
            $branches_pg=array();

        $id_obj = HighestCourse::where('course_name',explode(',',$job->qualification_expertise)[0])->first();
        if(!empty($id_obj))
            $branches_exp = HighestSpecialization::where('hc_id_fk',$id_obj->course_id)->get();
        else
            $branches_exp=array();


        $skils=Job_post_keyskills::where('job_id_fk',$job->job_id)->get();
        $user = User::where('id',Auth::user()->id)->get();
        $industry = IndustryType::orderBy('industry_type_name')->get();
        $industry_obj = IndustryType::where('industry_type_name',$job->industry_type)->first();
        $fareas = SubIndustryType::where('industry_type_id_fk',$industry_obj->industry_type_id)->get();
        $country = Countries::all();
        $courses = Course::orderBy('course_name')->get();
        $pgcourses = PGCourse::orderBy('pgc_name')->get();
        $highestcourses = HighestCourse::orderBy('course_name')->get();
        $addon = Addon_package::all();
        // dd($job);
         return view('employer/edit_draft',compact('job','user','industry','country','courses','pgcourses','highestcourses','fareas','skils','branches','branches_pg','branches_exp','addon'));
    }
    public function job_response_status(Request $request){
        $apply_ids =  $request->values;
        // $arr_jobseekers = explode(",", $jobseekerids);
        $userId = Auth::user()->id;
        // print_r($request->status);die;
        $apply_job = Applied_job::whereIn('apply_id',$apply_ids)->update(['status'=>$request->status]);
        

       echo 1;die;
    }
    public function check_profile_view_limit(Request $request){
        // echo 2;die;
        $id=$request->id;
        $jbsid = Crypt::decryptString($id);
        $user_id = Auth::user()->id;
        $user = User::where('id',$jbsid)->get();
        $exist = Profile_views::where('user_id_fk',$user_id)
                                ->where('job_seeker_id',$jbsid)->get();
        $exist_count = count($exist);
        if($exist_count == 0){

            if(Auth::user()->employer_details->parent_id>0)
                {

                    $total_profile_views=Helper::sub_user_profile_view();
                    $profile_views=count(Helper::sub_user_total_profile_view());
                    if($total_profile_views<=$profile_views)
                    {
                        echo 2;die();
                    }
                }
                else
                {
                    $total_profile_views=Helper::profile_view_access();
                    $profile_views=count(Helper::total_profile_viewed());

                    if($total_profile_views<=$profile_views)
                    {
                        echo 2;die;
                    }
                }
        }
        echo 1;die;
    }
    public function recent_update(Request $request)
    {
        //print_r($request->facebook1);exit();
        $user_id = Auth::user()->id;
        $facebook = "";
        $old_fb = "";
        $twitter = "";
        $old_tw = "";
        $linkedin = "";
        $old_li = "";
             if(Recent_update::where('user_id',$user_id)->where('type','1')->first()){
                    $fb = Recent_update::where('user_id',$user_id)->where('type','1')->first();
                   $fb->user_id =  $user_id;
                   $fb->link = $request->f_link;
                   $fb->description = $request->f_description;
                   if(!empty($request->hasFile('facebook')))
                     {
                         $videos = $request->file('facebook');
                        foreach($videos as $video){
                        $destination = 'uploads/updates';  
                        $video->move($destination, $destination. "/" .time().'-'.$video->getClientOriginalName());
                       $facebook = $facebook.'||'.$destination. "/" .time().'-'.$video->getClientOriginalName();
                        }
                     }
                     if(!empty($request->facebook1))
                     foreach ($request->facebook1 as $ff) {
                         $old_fb = $old_fb.'||'.$ff;
                     }
                     $fb->image =  $facebook.'||'.$old_fb;
                     $fb->save();
                     

               }else{
                    $fb = new Recent_update();
                   $fb->user_id =  $user_id;
                   $fb->parent_id = $request->parent_data;
                   $fb->type = 1;
                   $fb->link = $request->f_link;
                   $fb->description = $request->f_description;
                   if(!empty($request->hasFile('facebook')))
                     {
                         $videos = $request->file('facebook');
                        foreach($videos as $video){
                        $destination = 'uploads/updates';  
                        $video->move($destination, $destination. "/" .time().'-'.$video->getClientOriginalName());
                       $facebook = $facebook.'||'.$destination. "/" .time().'-'.$video->getClientOriginalName();
                        }
                     }
                     
                     $fb->image =  $facebook;
                     $fb->save();
                     
               }
               if(Recent_update::where('user_id',$user_id)->where('type','2')->first()){
                    $li = Recent_update::where('user_id',$user_id)->where('type','2')->first();
                   $li->user_id =  $user_id;

                   $li->link = $request->l_link;
                   $li->description = $request->l_description;
                  if($request->hasFile('linkedin'))
                     {
                        $l = $request->file('linkedin');
                        foreach($l as $l){
                        $destination = 'uploads/updates';  
                        $l->move($destination, $destination. "/" .time().'-'.$l->getClientOriginalName());
                       $linkedin = $linkedin.'||'.$destination. "/" .time().'-'.$l->getClientOriginalName();
                        }
                     }
                     if(!empty($request->linkedin1))
                     foreach ($request->linkedin1 as $ff) {
                         $old_li = $old_li.'||'.$ff;
                     }
                     $li->image =  $linkedin.'||'.$old_li;
                     $li->save();
                     
               }else{
                    $li = new Recent_update();
                   $li->user_id =  $user_id;
                   $li->parent_id = $request->parent_data;
                   $li->type = 2;
                   $li->link = $request->l_link;
                   $li->description = $request->l_description;
                  if($request->hasFile('linkedin'))
                     {
                        $l = $request->file('linkedin');
                        foreach($l as $l){
                        $destination = 'uploads/updates';  
                        $l->move($destination, $destination. "/" .time().'-'.$l->getClientOriginalName());
                       $linkedin = $linkedin.'||'.$destination. "/" .time().'-'.$l->getClientOriginalName();
                        }
                     }
                     $li->image =  $linkedin;
                     $li->save();
                     
               }
               if(Recent_update::where('user_id',$user_id)->where('type','3')->first()){
                    $tw = Recent_update::where('user_id',$user_id)->where('type','3')->first();
                   $tw->user_id =  $user_id;
                   $tw->link = $request->t_link;
                   $tw->description = $request->t_description;
                  if($request->hasFile('twitter'))
                     {
                        $t = $request->file('twitter');
                        foreach($t as $t){
                        $destination = 'uploads/updates';  
                        $t->move($destination, $destination. "/" .time().'-'.$t->getClientOriginalName());
                       $twitter = $twitter.'||'.$destination. "/" .time().'-'.$t->getClientOriginalName();
                        }
                     }
                     if(!empty($request->twitter1))
                     foreach ($request->twitter1 as $ff) {
                         $old_tw = $old_tw.'||'.$ff;
                     }
                     $tw->image =  $twitter.'||'.$old_tw;
                     $tw->save();
                     
               }else{
                    $tw = new Recent_update();
                   $tw->user_id =  $user_id;
                   $tw->parent_id = $request->parent_data;
                   $tw->type = 3;
                   $tw->link = $request->t_link;
                   $tw->description = $request->t_description;
                  if($request->hasFile('twitter'))
                     {
                        $t = $request->file('twitter');
                        foreach($t as $t){
                        $destination = 'uploads/updates';  
                        $t->move($destination, $destination. "/" .time().'-'.$t->getClientOriginalName());
                       $twitter = $twitter.'||'.$destination. "/" .time().'-'.$t->getClientOriginalName();
                        }
                     }
                     $tw->image =  $twitter;
                     $tw->save();
                     
               }
                   
        echo 1; die();   
    }
}