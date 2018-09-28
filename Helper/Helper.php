<?php

namespace App\Helper;
use DB;
use App\User;
Use Illuminate\Http\Request; 
use App\Model\States;
use App\Model\Countries;
use App\Model\Cities;
use App\Model\Applied_job;
use App\Model\Job_post;
use App\Model\Profile_views;
use App\Model\Cv_downloads;
use App\Model\Folder_move;
use App\Model\Job_seeker_technical_skills;
use App\Model\Job_seeker_cover_letter;
use App\Model\Job_seeker_personal_details;
use App\Model\Job_seeker_certificate;
use App\Model\Seminar_details;
use App\Model\Project;
use App\Model\Cv;
use App\Model\Cover_letter;
use App\Model\Job_preference;
use App\Model\SubIndustryType;
use App\Model\IndustryType;
use App\Model\Footer_Skills;
use App\Model\Footer_Locations;
use App\Model\Chart_data;
use App\Model\Refine_inputs;
use App\Model\Top_Employer;
use App\Model\Cv_search;
use App\Model\Landing_Menus;
use App\Model\Emails_sent;
use App\Model\Branding_enquiries;
use App\Model\Manage_Content;
use App\Model\Content_Careers;
use App\Model\Content_About;
use App\Model\Content_Contact;
use App\Model\Question_Answers;
use App\Model\Manage_Ads;
use App\Model\Staff_details;
use App\Model\Staff_Menus;
use App\Model\Staff_Mappings;
use App\Model\Personal_details;
use App\Model\Academic_details;
use App\Model\Career_history;
use App\Model\Application_reply;
use App\Model\Block_company;
use App\Model\Job_post_keyskills;
use App\Model\Job_post_package;
use App\Model\Employer;
use App\Model\Cv_search_details;
use App\Model\Jobseeker_details_comments;
use App\Model\Saver_package;
use App\Model\Package;
use Auth;
use Carbon\Carbon;
use App\Model\Saved_job;
use App\Model\Microsite_details;
use App\Model\Branding_package;
use App\Model\User_package;
use App\Model\Addon_package;
use App\Model\Last_login;
use App\Model\Nationality;
use App\Model\Banners;
use App\Model\About_us_offers;
use App\Model\Package_rules;
use App\Model\Package_content;


class Helper{

    public static function check_job_applied($job_id)
    {
        if(Auth::user())
            {
                $job=Applied_job::where('user_id_fk',Auth::user()->id)->where('job_id_fk',$job_id)->first();
                return count($job);
         }   
        else
            return 0;
    }
    public static function check_job_saved($job_id)
    {
        if(Auth::user())
            {
                $job=Saved_job::where('user_id_fk',Auth::user()->id)->where('job_id_fk',$job_id)->first();
                return count($job);
         }   
        else
            return 0;
    }  
    public static function total_job_saved()
    {
        $job=Saved_job::where('user_id_fk',Auth::user()->id)->where('Active',1)->pluck('job_id_fk')->toArray();
        $f=0;
        foreach ($job as $key => $value) {
            if(count(Applied_job::where('user_id_fk',Auth::user()->id)->where('job_id_fk',$value)->first())==0)
            {
                $f++;
            }
        }
        return $f;
    }           
    public static function check_employer_mail($employer_id)
    {
        $user_id=Auth::user()->id;
        $emails = Emails_sent::where('user_id_fk',$employer_id)->where('job_seeker_id_fk',$user_id)->orderBy('created_at','desc')->where('source',0)->get();
        return $emails;
    }         
    public static function get_expertise($degree)
    {
        $id_obj = HighestCourse::where('course_name',$degree)->first();
        if(!empty($id_obj))
        {        
            $branches = HighestSpecialization::where('hc_id_fk',$id_obj->course_id)->get();
        }
        else{
            $branches=array();
        }
        return $branches;
    }
    public static function get_functional_area($industry)
    {
        $industry_obj = IndustryType::where('industry_type_name',$industry)->first();
        if(!empty($industry_obj))
        {        
            $fareas = SubIndustryType::where('industry_type_id_fk',$industry_obj->industry_type_id)->get();
        }
        else{
            $fareas=array();
        }
        return $fareas;
    }
    public static function job_search_preffered_loc(){
        $emp_type = Cv_search_details::distinct()->pluck('exp_loc')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result1=array_filter(array_unique($r));

        $emp_type = Job_preference::distinct()->pluck('preferred_job_location')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode('|', $value),$result);
        }
        $results=[];
        foreach ($result as $key => $value) {
            $results=array_merge(explode(',', $value),$results);
        }
        $r=[];
        foreach ($results as $key) {
           $r[]=trim($key);
        }
        $result2=array_filter(array_unique($r));
        $result=array_merge($result1,$result2);
        $result=array_filter(array_unique($result));
        return $result;
    }
    public static function job_search_degree(){
        $emp_type = Cv_search_details::distinct()->pluck('degree')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result1=array_filter(array_unique($r));

        $emp_type = Academic_details::where('qualification_type',1)->distinct()->pluck('qualification')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result2=array_filter(array_unique($r));
        $result=array_merge($result1,$result2);
        return $result;
    }
    public static function job_search_industry(){
        $emp_type = Cv_search_details::distinct()->pluck('industry')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        } 

        $result1=array_filter(array_unique($r));

        $emp_type = Job_preference::distinct()->pluck('preferred_industry_type')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result2=array_filter(array_unique($r));
        $result=array_merge($result1,$result2);
        return $result;
    }
    public static function job_search_function(){
        $emp_type = Cv_search_details::distinct()->pluck('farea')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        } 

        $result1=array_filter(array_unique($r));

        $emp_type = Job_preference::distinct()->pluck('preferred_job_function')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result2=array_filter(array_unique($r));
        $result=array_merge($result1,$result2);

        return $result;
    }
    public static function job_search_designation(){
        $emp_type = Cv_search_details::distinct()->pluck('job_title')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result1=array_filter(array_unique($r));

        $emp_type = Career_history::distinct()->pluck('job_title')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result2=array_filter(array_unique($r));
        $result=array_merge($result1,$result2);
        return $result;
    }
    public static function job_search_company(){
        $emp_type = Cv_search_details::distinct()->pluck('employer_name')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result1=array_filter(array_unique($r));

        $emp_type = Career_history::distinct()->pluck('employer_name')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result2=array_filter(array_unique($r));
        $result=array_merge($result1,$result2);

        return $result;
    }
    public static function job_search_keyword(){
        $emp_type = Cv_search_details::distinct()->pluck('keyword')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result=array_filter(array_unique($r));
        return $result;
    }
    
    public static function banding_total_job_post()
    {
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if( $key->packa->type==4)
                {
                    $expiry_date = date('d M Y', strtotime($key->expiry_date));
                        $expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);
                        // return $length;
                        if($expiry_date->gte($now))
                        {
                            $packages[]=$key->packa;
                        }
                }
            }
        }
        $ids=array();
        foreach ($packages as $key) {
                        
        if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $ids[]=$key->branding_pack->package_id;
                    return $key->branding_pack->job_posting;
                }
            }
        }
        //$jobs=Branding_package::where('package_type',1)->whereIn('package_id_fk',$ids)->first();
        return 0;
    }
    public static function banding_total_job_posted()
    {
        $package = User_package::where('user_id_fk',Auth::user()->id)->get();
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if( $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        /*$expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);*/
                        // return $length;
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                        }
                }
            }
        }
        $ids=array();
        foreach ($packages as $key) {
                    
        if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $ids[]=$key->branding_pack->package_id;

                }
            }
        }
        $jobs=Job_post::where('jp_type','<>',1)->where('user_id_fk',Auth::user()->id)->whereIn('package_id',$ids)->where('type',1)->get();
        return count($jobs);
    }
    public static function sub_user_job_post_access()
    {
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $assigned_job=Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('job_exp_date','>=',date('Y-m-d'))->sum('job_post_limit');
        return $assigned_job;
    }
    public static function total_job_post_access()
    {
        $total_job_posts=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 3 || $up->packa->type == 1 || $up->packa->type == 4)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        //$expiry_date=Carbon::parse($expiry_date);
                        // $expiry_date=Carbon::parse($expiry_date)->format('d M Y');
                        //$expiry_date=Carbon::parse($expiry_date);
                        $now = date('Y-m-d H:i:s');
                        // $length = $now->diffInDays($expiry_date);
                        // return $length;
                        if($expiry_date>=$now)
                        {   if($up->packa->type == 3)
                            {
                                foreach($up->packa->job_posting_pack as $jp)
                                {
                                    $total_job_posts = $total_job_posts +  $jp->job_posting;
                                }
                            }
                            else if($up->packa->type == 1)
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_job_posts = $total_job_posts +  $up->packa->saver_pack->enterprise_pack + $up->packa->saver_pack->regular_pack;
                                }
                            }
                            else if($up->packa->type == 4)
                            {
                                if(!empty($up->packa->branding_pack))
                                {
                                    if($up->packa->branding_pack->package_type==1)
                                    {
                                        $total_job_posts = $total_job_posts +  $up->packa->branding_pack->job_posting ;
                                    }
                                }
                            }
                        }
                    }
            }
        }

        return $total_job_posts;
    }
    public static function job_post_access1()
    {
        $total_job_posts=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 3 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        /*$expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);*/
                        // return $length;
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            // return 1;
                            if($up->packa->type == 3)
                            {
                                foreach($up->packa->job_posting_pack as $jp)
                                {
                                    $total_job_posts = $total_job_posts +  $jp->job_posting;
                                }
                            }
                            else if($up->packa->type == 1)
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_job_posts = $total_job_posts +  $up->packa->saver_pack->enterprise_pack + $up->packa->saver_pack->regular_pack;
                                }
                            }
                            else if($up->packa->type == 4)
                            {
                                if(!empty($up->packa->branding_pack))
                                {
                                    if($up->packa->branding_pack->package_type==1)
                                    {
                                        $total_job_posts = $total_job_posts +  $up->packa->branding_pack->job_posting ;
                                    }
                                }
                            }
                        }
                    }
            }
        }
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $assigned_job=Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('job_exp_date','>=',date('Y-m-d'))->sum('job_post_limit');
        return $assigned_job;
        // $total_job_posts=$total_job_posts-$assigned_job->assigned_job_post;
        
        $assigned_job=Employer::where('user_id_fk',Auth::user()->id)->first();
        $total_job_posts=$total_job_posts-$assigned_job->assigned_job_post;
        return $total_job_posts;
    }
    public static function job_post_access()
    {
        $total_job_posts=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 3 || $up->packa->type == 1 || $up->packa->type == 4)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        /*$expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);
                        // return $length;*/
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            // return 1;
                            if($up->packa->type == 3)
                            {
                                foreach($up->packa->job_posting_pack as $jp)
                                {
                                    $total_job_posts = $total_job_posts +  $jp->job_posting;
                                }
                            }
                            else if($up->packa->type == 1)
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_job_posts = $total_job_posts +  $up->packa->saver_pack->enterprise_pack + $up->packa->saver_pack->regular_pack;
                                }
                            }
                            else if($up->packa->type == 4)
                            {
                                if(!empty($up->packa->branding_pack))
                                {
                                    if($up->packa->branding_pack->package_type==1)
                                    {
                                        $total_job_posts = $total_job_posts +  $up->packa->branding_pack->job_posting ;
                                    }
                                }
                            }
                        }
                    }
            }
        }
        // return $total_job_posts;
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $assigned_job=Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('job_exp_date','>=',date('Y-m-d'))->sum('job_post_limit');
        // $total_job_posts=$total_job_posts-$assigned_job->assigned_job_post;
        
        // $assigned_job=Employer::where('user_id_fk',Auth::user()->id)->first();
        $total_job_posts=$total_job_posts - $assigned_job;
        return $total_job_posts;
    }

    public static function total_cv_search_access()
    {
        $total_cv_search=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        /*$expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);*/
                        // return $length;
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $total_cv_search = $total_cv_search +$up->packa->cv_pack->cv_access;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_cv_search = $total_cv_search +  $up->packa->saver_pack->cv_access;
                                }
                            }
                        }
                        
                    }
            }
        }
        $assigned_cv=Employer::where('user_id_fk',Auth::user()->id)->first();
        $total_cv_search=$total_cv_search;
        return $total_cv_search;
    }
    public static function cv_search_access()
    {
        $total_cv_search=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        /*$expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);*/
                        // return $length;
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $total_cv_search = $total_cv_search +$up->packa->cv_pack->cv_access;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_cv_search = $total_cv_search +  $up->packa->saver_pack->cv_access;
                                }
                            }
                        }
                        
                    }
            }
        }
        $assigned_cv=Employer::where('user_id_fk',Auth::user()->id)->first();
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $assigned_cv=Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('cv_exp_date','>=',date('Y-m-d'))->sum('cv_search_limit');
        $total_cv_search=$total_cv_search-$assigned_cv;
        return $total_cv_search;
    }

    public static function total_profile_view_access()
    {
        $total_cv_search=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        /*$expiry_date=Carbon::parse($expiry_date);
                        $now = Carbon::now();
                        $length = $now->diffInDays($expiry_date);*/
                        // return $length;
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $total_cv_search = $total_cv_search +$up->packa->cv_pack->profile_views;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_cv_search = $total_cv_search +  $up->packa->saver_pack->profile_views;
                                }
                            }
                        }
                        
                    }
            }
        }
        $assigned_cv=Employer::where('user_id_fk',Auth::user()->id)->first();
        $total_cv_search=$total_cv_search;
        return $total_cv_search;
    }
    public static function profile_view_access()
    {
        $total_cv_search=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                            $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                            $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $total_cv_search = $total_cv_search +$up->packa->cv_pack->profile_views;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_cv_search = $total_cv_search +  $up->packa->saver_pack->profile_views;
                                }
                            }
                        }
                        
                    }
            }
        }
        $assigned_cv=Employer::where('user_id_fk',Auth::user()->id)->first();
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $assigned_cv=Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('cv_exp_date','>=',date('Y-m-d'))->sum('profile_view_limit');
        $total_cv_search=$total_cv_search-$assigned_cv;
        return $total_cv_search;
    }

    public static function total_send_mail_access()
    {
        $total_cv_search=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                         $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $total_cv_search = $total_cv_search +$up->packa->cv_pack->email;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_cv_search = $total_cv_search +  $up->packa->saver_pack->email;
                                }
                            }
                        }
                        
                    }
            }
        }
        $assigned_cv=Employer::where('user_id_fk',Auth::user()->id)->first();
        $total_cv_search=$total_cv_search;
        return $total_cv_search;
    }
    public static function send_mail_access()
    {
        $total_cv_search=0;
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $total_cv_search = $total_cv_search +$up->packa->cv_pack->email;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $total_cv_search = $total_cv_search +  $up->packa->saver_pack->email;
                                }
                            }
                        }
                        
                    }
            }
        }
        $assigned_cv=Employer::where('user_id_fk',Auth::user()->id)->first();
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $assigned_cv=Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('cv_exp_date','>=',date('Y-m-d'))->sum('send_mail_limit');
        $total_cv_search=$total_cv_search-$assigned_cv;
        return $total_cv_search;
    }

    public static function check_cv_search(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==2 || $key->packa->type==1)
                {
                    //$expiry_date = date('d M Y', strtotime($key->expiry_date));
                        $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==2)
                            {
                                $packages[]=$key;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->cv_access) || !empty($key->packa->saver_pack->profile_views) || !empty($key->packa->saver_pack->email)))
                            {
                                $packages[]=$key;
                                $total++;
                            }
                        }
                }
            }
        }
        return $total;
    }
    public static function check_job_post(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==3)
                            {
                                $packages[]=$key;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->enterprise_pack) || !empty($key->packa->saver_pack->regular_pack)))
                            {
                                $packages[]=$key;
                                $total++;
                            }
                            elseif($key->packa->type==4 && !empty($key->packa->branding_pack)  )
                            {
                                if($key->packa->branding_pack->package_type==1 && !empty($key->packa->branding_pack->job_posting))
                                {
                                    $packages[]=$key;
                                    $total++;
                                }
                            }
                        }
                }
            }
        }
        return $total;
    }
    public static function check_regular(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        $packages2=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                                $packages[]=$key->packa;
                        }
                }
            }
        }
        foreach ($packages as $key) {
            if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==2 || $job->pack_type==1)
                {
                    $total++;
                }
            }
            else if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job!='')
                {
                    if(!empty($job->regular_pack))
                        $total++;
                    if(!empty($job->enterprise_pack))
                        $total++;
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $total++;
                }
            }
        }
        return $total;
    }
    public static function check_enterprise(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                            //$total++;
                        }
                }
            }
        }
        // return json_encode($package);
        foreach ($packages as $key) {
            if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==1)
                {
                    $total++;
                }
            }
            else if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job!='')
                {
                   if(!empty($job->enterprise_pack))
                        $total++;
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $total++;
                }
            }
        }
        return $total;
    }

    public static function total_regular(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                        }
                }
            }
        }
        foreach ($packages as $key) {
            
            if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==2 || $job->pack_type==1)
                {
                   $total+=$job->job_posting;
                }
            }
            else if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job!='')
                {
                    
                    if(!empty($job->regular_pack))
                        $total+=$job->regular_pack;
                    if(!empty($job->enterprise_pack))
                        $total+=$job->enterprise_pack;
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $total+=$key->branding_pack->job_posting;
                }
            }
        }
        return $total;
    }
    public static function total_regular1(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                        }
                }
            }
        }
        foreach ($packages as $key) {
            
            if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==2 || $job->pack_type==1)
                {
                   $total+=$job->job_posting;
                }
            }
            else if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job!='')
                {
                    
                    if(!empty($job->regular_pack))
                        $total+=$job->regular_pack;
                    /*if(!empty($job->enterprise_pack))
                        $total+=$job->enterprise_pack;*/
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $total+=$key->branding_pack->job_posting;
                }
            }
        }
        return $total;
    }
    public static function total_enterprise(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                        }
                }
            }
        }
        foreach ($packages as $key) {
             if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==1)
                {
                   $total+=$job->job_posting;
                }
            }
            else  if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->where('enterprise_pack','<>','')->first();
                // dd($job);
                if($job!='')
                {
                    $total+=$job->enterprise_pack;
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                
                if($key->branding_pack->package_type==1)
                {
                    $total+=$key->branding_pack->job_posting;
                }
            }
        }
        return $total;
    }

    public static function check_exp_date($type,$validity,$date)
    {
        $day_month_year = $type;
        $validity_no = $validity;
        $days_gap = 0;
        if($day_month_year == 3){
            $days_gap = 365 * $validity_no;
        }
        else if($day_month_year == 2){
            $days_gap = 30 * $validity_no;
        }
        else{
            $days_gap = 1 * $validity_no;
        }
        $OldDate = strtotime($date);
        $NewDate = date('M j, Y', $OldDate);
        $diff = date_diff(date_create($NewDate),date_create(date("M j, Y")));
        $daysdiff = $diff->format('%a');
        $soma = strtotime($date.'+'.$days_gap.'days');
        $expiry_date = date('d M Y', $soma);
        $dass = date('d M Y');
        return $expiry_date;
    }

    public static function total_job_posted(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        $user_pack_id=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==3)
                            {
                                $packages[]=$key->packa->package_id;
                                $user_pack_id[] = $key->user_package_id;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->enterprise_pack) || !empty($key->packa->saver_pack->regular_pack)))
                            {
                                $packages[]=$key->packa->package_id;
                                $user_pack_id[] = $key->user_package_id;
                                $total++;
                            }
                            elseif($key->packa->type==4 && !empty($key->packa->branding_pack)  )
                            {
                                if($key->packa->branding_pack->package_type==1 && !empty($key->packa->branding_pack->job_posting))
                                {
                                    $packages[]=$key->packa->package_id;
                                    $user_pack_id[] = $key->user_package_id;
                                    $total++;
                                }
                            }
                        }
                }
            }
        }
        $jobs=Job_post::where('user_id_fk',Auth::user()->id)->where('jp_type','<>',1)->whereIn('package_id',$packages)->whereIn('user_package_id',$user_pack_id)->where('type',1)->get();
        return $jobs;
    }
    public static function total_regular_job_posted(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        $user_pack_id=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                            $user_pack_id[] = $key->user_package_id;
                        }
                }
            }
        }
        $ids=array();
        foreach ($packages as $key) {
            
            if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==2 || $job->pack_type==1)
                {
                   $ids[]=$key->package_id;
                   $user_pack_id[] = $key->user_package_id;
                }
            }
            else if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job!='')
                {
                    
                    if(!empty($job->regular_pack) || !empty($job->enterprise_pack))
                       $ids[]=$key->package_id;
                   $user_pack_id[] = $key->user_package_id;
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $ids[]=$key->branding_pack->package_id_fk;
                    $user_pack_id[] = $key->user_package_id;
                }
            }
        }
        $jobs=Job_post::where('user_id_fk',Auth::user()->id)->where('jp_type',3)->whereIn('package_id',$ids)->whereIn('user_package_id',$user_pack_id)->where('type',1)->get();
        return $jobs;
    }
    public static function total_enterprise_job_posted(){
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        $user_pack_id=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            $packages[]=$key->packa;
                            $user_pack_id[] = $key->user_package_id;
                        }
                }
            }
        }
        $ids=array();
        foreach ($packages as $key) {
             if($key->type==3)
            {
                $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
                // dd($job);
                if($job->pack_type==1)
                {
                   $ids[]=$key->package_id;
                   $user_pack_id[] = $key->user_package_id;
                }
            }
            else  if($key->type==1)
            {
                $job=Saver_package::where('package_id_fk',$key->package_id)->where('enterprise_pack','<>','')->first();
                // dd($job);
                if($job!='')
                {
                    $ids[]=$key->package_id;
                    $user_pack_id[] = $key->user_package_id;
                }
            }
            else if($key->type==4 && !empty($key->branding_pack) )
            {
                if($key->branding_pack->package_type==1)
                {
                    $ids[]=$key->branding_pack->package_id_fk;
                    $user_pack_id[] = $key->user_package_id;
                }
            }
        }

        $jobs=Job_post::where('user_id_fk',Auth::user()->id)->where('jp_type',2)->whereIn('package_id',$ids)->whereIn('user_package_id',$user_pack_id)->where('type',1)->get();
        
        return $jobs;
    }
    public static function total_branding_job_posted($id)
    {
        $jobs=Job_post::where('user_id_fk',Auth::user()->id)->where('jp_type','<>',1)->where('package_id',$id)->where('type',1)->get();
        return $jobs;
    }
    public static function total_cv_searched()
    {
        $total_cv_search=0;
        $ids=array();
        $user_pack_id=[];
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $ids[]=$up->packa->package_id;
                                $user_pack_id[] = $up->user_package_id;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $ids[]=$up->packa->package_id;
                                    $user_pack_id[] = $up->user_package_id;
                                }
                            }
                        }
                        
                    }
            }
        }
        $Cv_downloads=Cv_downloads::where('user_id_fk',Auth::user()->id)->whereIn('package_id',$ids)->whereIn('user_package_id',$user_pack_id)->get();
        return $Cv_downloads;
    }
    public static function total_profile_viewed()
    {
        $total_cv_search=0;
        $ids=array();
        $user_pack_id = [];
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $ids[]=$up->packa->package_id;
                                $user_pack_id[] = $up->user_package_id;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $ids[]=$up->packa->package_id;
                                    $user_pack_id[] = $up->user_package_id;
                                }
                            }
                        }
                        
                    }
            }
        }
        $Profile_views=Profile_views::where('user_id_fk',Auth::user()->id)->whereIn('package_id',$ids)->whereIn('user_package_id',$user_pack_id)->get();
        return $Profile_views;
    }
    public static function total_send_mailed()
    {
        $total_cv_search=0;
        $ids=array();
        $user_pack_id = [];
        foreach(Auth::user()->user_active_packages as $up)
        {
            if(isset($up->packa))
            {
                // print_r($up->packa);
                    if($up->packa->type == 2 || $up->packa->type == 1)
                    {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($up->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($up->packa->type == 2)
                            {
                                $ids[]=$up->packa->package_id;
                                $user_pack_id[] = $up->user_package_id;
                            }
                            else
                            {
                                if(!empty($up->packa->saver_pack))
                                {
                                    $ids[]=$up->packa->package_id;
                                    $user_pack_id[] = $up->user_package_id;
                                }
                            }
                        }
                        
                    }
            }
        }
        $Emails_sent=Emails_sent::where('user_id_fk',Auth::user()->id)->whereIn('package_id',$ids)->whereIn('user_package_id',$user_pack_id)->get();
        return $Emails_sent;
    }
    public static function job_country(){
        $emp_type = Job_post::distinct()->pluck('loc_country')->toArray();
        $result=[];
        /*foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }*/
        foreach ($emp_type as $key => $value) {
            $loc_country = explode(',', $value);
            $result[] = end($loc_country);
        }

        $result=array_filter(array_unique($result));
        return $result;
    }
    public static function current_job_package(){
        if(Auth::user()->employer_details->parent_id>0)
            {

                $package=Package::where('package_id',Auth::user()->employer_details->job_package_id)->first();
                return $package;
            }
        else
        {
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==3)
                            {
                                $packages[]=$key->packa->package_id;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->enterprise_pack) || !empty($key->packa->saver_pack->regular_pack)))
                            {
                                $packages[]=$key->packa->package_id;
                                $total++;
                            }
                            elseif($key->packa->type==4 && !empty($key->packa->branding_pack)  )
                            {
                                if($key->packa->branding_pack->package_type==1 && !empty($key->packa->branding_pack->job_posting))
                                {
                                    $packages[]=$key->packa->package_id;
                                    $total++;
                                }
                            }
                        }
                }
            }
        }
        $package=Package::whereIn('package_id',$packages)->orderBy('created_at','DESC')->first();
        return $package;
        }
    }
    public static function get_expiry_date($id){
            $package=Package::where('package_id',$id)->first();
            $day_month_year = $package->validity_type;
            $validity_no = $package->validity;
            $days_gap = 0;
            if($day_month_year == 3){
                $days_gap = 365 * $validity_no;
            }
            else if($day_month_year == 2){
                $days_gap = 30 * $validity_no;
            }
            else{
                $days_gap = 1 * $validity_no;
            }
            $date=date('Y-m-d H:i:s');
            $soma = strtotime($date.'+'.$days_gap.'days');
            $expiry_date = date('d M Y H:i:s', $soma);
            $expiry_date=Carbon::parse($expiry_date);
            return $expiry_date;
    }
    public static function current_cv_package(){
        if(Auth::user()->employer_details->parent_id>0)
        {

            $package=Package::where('package_id',Auth::user()->employer_details->cv_package_id)->first();
            return $package;
        }
    else
        {
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $ids=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==2 || $key->packa->type==1)
                {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==2)
                            {
                                $ids[]=$key->packa->package_id;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->cv_access) || !empty($key->packa->saver_pack->profile_views) || !empty($key->packa->saver_pack->email)))
                            {
                                $ids[]=$key->packa->package_id;
                                $total++;
                            }
                        }
                }
            }
        }
        // return $packages;
        $package=Package::whereIn('package_id',$ids)->orderBy('created_at','DESC')->first();
        return $package;
        }
    }

    public static function sub_user_job(){
        $package=Auth::user()->employer_details->job_exp_date;
        $expiry_date = date('Y-m-d H:i:s', strtotime($package));
        $now = date('Y-m-d H:i:s');
        if($expiry_date>=$now)
        {
            //return $expiry_date;
            if(User_package::where('user_package_id', Auth::user()->employer_details->user_package_id)->where('status',2)->first())
            {
                return Auth::user()->employer_details->job_post_limit;
            }else{
                return 0;
            }
            
        }else{
            return 0;
        }
    }
    public static function sub_user_cv_dowload(){
        $package=Auth::user()->employer_details->cv_exp_date;
         $expiry_date = date('Y-m-d H:i:s', strtotime($package));
        $now = date('Y-m-d H:i:s');
        if($expiry_date>=$now)
        {
            if(User_package::where('user_package_id', Auth::user()->employer_details->user_package_id)->where('status',2)->first())
            {
            return Auth::user()->employer_details->cv_search_limit;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }
    public static function sub_user_profile_view(){
        $package=Auth::user()->employer_details->cv_exp_date;
         $expiry_date = date('Y-m-d H:i:s', strtotime($package));
        $now = date('Y-m-d H:i:s');
        if($expiry_date>=$now)
        {
            if(User_package::where('user_package_id', Auth::user()->employer_details->user_package_id)->where('status',2)->first())
            {
            return Auth::user()->employer_details->profile_view_limit;
            }else{
                    return 0;
                }
        }else{
            return 0;
        }
    }
    public static function sub_user_send_mail(){
        $package=Auth::user()->employer_details->cv_exp_date;
        $expiry_date = date('Y-m-d H:i:s', strtotime($package));
        $now = date('Y-m-d H:i:s');
        if($expiry_date>=$now)
        {
            if(User_package::where('user_package_id', Auth::user()->employer_details->user_package_id)->where('status',2)->first())
            {
            return Auth::user()->employer_details->send_mail_limit;
            }else{
                    return 0;
                }
        }else{
            return 0;
        }
    }
    public static function sub_user_total_job(){
        $package_id=Auth::user()->employer_details->job_package_id;
        $id = Auth::user()->employer_details->user_package_id;
        $result=Job_post::where('jp_type','<>','1')->where('user_id_fk',Auth::user()->id)->where('package_id',$package_id)->where('type',1)
                        ->where(function($sq) use ($id)
                        {
                           $sq->whereIn('user_package_id',function($sq) use ($id)
                                {
                                   $sq->select('user_package_id')
                                    ->from('user_package')
                                    ->where('user_package_id',$id)
                                    ->where('expiry_date','>=',date('Y-m-d'))
                                    ->where('status','2');
                                });
                        })
                        ->get();
        return $result;
    }
    public static function sub_user_total_cv_dowload(){
        $package_id=Auth::user()->employer_details->cv_package_id;
        $id = Auth::user()->employer_details->user_package_id;
        $result=Cv_downloads::where('user_id_fk',Auth::user()->id)->where('package_id',$package_id)
                                ->where(function($sq) use ($id)
                                {
                                    $sq->whereIn('user_package_id',function($sq) use ($id)
                                    {
                                    $sq->select('user_package_id')
                                    ->from('user_package')
                                    ->where('user_package_id',$id)
                                    ->where('expiry_date','>=',date('Y-m-d'))
                                    ->where('status','2');
                                    });
                                })
                                ->get();
        return $result;
    }
    public static function sub_user_total_profile_view(){
        $package_id=Auth::user()->employer_details->cv_package_id;
        $id = Auth::user()->employer_details->user_package_id;
        $result=Profile_views::where('user_id_fk',Auth::user()->id)->where('package_id',$package_id)
                            ->where(function($sq) use ($id)
                        {
                           $sq->whereIn('user_package_id',function($sq) use ($id)
                                {
                                   $sq->select('user_package_id')
                                    ->from('user_package')
                                    ->where('user_package_id',$id)
                                    ->where('expiry_date','>=',date('Y-m-d'))
                                    ->where('status','2');
                                });
                        })
                                ->get();
        return $result;
    }
    public static function sub_user_total_send_mail(){
        $package_id=Auth::user()->employer_details->cv_package_id;
        $id = Auth::user()->employer_details->user_package_id;
        $result=Emails_sent::where('user_id_fk',Auth::user()->id)->where('package_id',$package_id)
                            ->where(function($sq) use ($id)
                        {
                           $sq->whereIn('user_package_id',function($sq) use ($id)
                                {
                                   $sq->select('user_package_id')
                                    ->from('user_package')
                                    ->where('user_package_id',$id)
                                    ->where('expiry_date','>=',date('Y-m-d'))
                                    ->where('status','2');
                                });
                        })
                                ->get();
        return $result;
    }
    public static function job_city(){
        $emp_type = Job_post::where('type',1)->distinct()->pluck('location')->toArray();
        $result=[];
        /*foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }*/
        foreach ($emp_type as $key => $value) {
            if(!empty($value))
            $loc = explode('|', $value);
            foreach ($loc as $loc1) {
                if(!empty($loc1))
                $locs = explode(',', $loc1);
                if(count($locs)>=2)
                $result[] = current($locs);
            }
            
        }
        $result=array_filter(array_unique($result));
        return $result;
    }
    public static function job_titles(){
        $title = Job_post::where('type',1)->distinct()->pluck('job_title')->toArray();
        return $title;
    } 
    public static function job_function(){
        $function = Job_post::where('type',1)->distinct()->pluck('functional_area')->toArray();
        return $function;
    }
    public static function job_industry(){
        $industry = Job_post::where('type',1)->distinct()->pluck('industry_type')->toArray();
        return $industry;
    }
    public static function job_company(){
        $company = Job_post::where('type',1)->distinct()->pluck('employer_company_name')->toArray();
        return $company;
    }
    public static function job_emp_type(){
        $emp_type = Job_post::where('type',1)->distinct()->pluck('employer_industry_type')->toArray();
        return $emp_type;
    }
    public static function job_experience(){
        $emp_type = Job_post::where('type',1)->distinct()->pluck('employer_industry_type')->toArray();
        return $emp_type;
    }
    public static function job_salary(){
        $emp_type = Job_post::where('type',1)->distinct()->pluck('employer_industry_type')->toArray();
        return $emp_type;
    }
    public static function job_nationality(){
        $nationality = Job_post::where('type',1)->where('nationality','<>',"")->distinct()->pluck('nationality')->toArray();
        return $nationality;
    }
    public static function job_skils(){
        $emp_type = Job_post_keyskills::distinct()->pluck('skill')->toArray();
        $result=[];
        foreach ($emp_type as $key => $value) {
            $result=array_merge(explode(',', $value),$result);
        }
        $r=[];
        foreach ($result as $key) {
           $r[]=trim($key);
        }
        $result=array_filter(array_unique($r));
        return $result;
        return array_unique($result);
    }
	public static function getMonthsInRange($startDate) {
        $endDate = time(); 
        $months = array();
        while (strtotime($startDate) <= $endDate) {
            $months[] = array('year' => date('Y', strtotime($startDate)) , 'month' => date('m', strtotime($startDate)) );
            $startDate = date('d M Y', strtotime($startDate.
                '+ 1 month'));
        }
        return $months;
    }
    public static function get_state_by_country($country_id){
        $state = States::where('country_id',$country_id)->get();
        return $state;
    }
    public static function get_apply_job($apply_id){
        $a_job = Applied_job::where('apply_id',$apply_id)->first();
        return $a_job;
    }
    public static function get_rec_details($emp){
        $det = Job_post::where('user_id_fk',$emp)->first();
        return $det;
    }
    public static function get_countries(){
        $det = Countries::all();
        return $det;
    }
     public static function get_view_history($job,$user){
        $det = Profile_views::where('job_seeker_id',$user)->where('user_id_fk',$job)->get();
        return $det;
    }
    public static function get_last_view($job,$user){
        $det = Profile_views::where('job_seeker_id',$user)->where('user_id_fk',$job)->orderBy('created_at','desc')->first();
        return $det;
    }
     public static function get_resume_download($job,$user){
        $det = Cv_downloads::where('job_seeker_id',$user)->where('user_id_fk',$job)->get();
        return $det;
    }
    public static function get_down_history($job,$user){
        $det = Cv_downloads::where('job_seeker_id',$user)->where('user_id_fk',$job)->get();
        return $det;
    }


    public static function get_city_by_state($state_id){
        $city = Cities::where('state_id',$state_id)->get();
        return $city;
    }
    public static function get_city_by_id($state_id){
        $city = Cities::where('id',$state_id)->first();
        return $city;
    }
    public static function get_contnet($id){
        $data = Manage_Content::where('content_type',$id)->first();
        return $data;
    }

    public static function sendSms2($number , $message){
    		   //Your authentication key
		        $authKey = "178141AV3Ug3thuY59d76120";
		        //Sender ID,While using route4 sender id should be 6 characters long.
		        $senderId = "NMYJOB";
		        //Define route 
		        $route = "4";

		        //Prepare you post parameters
		        $postData = array(
		            'authkey' => $authKey,
		            'mobiles' => $number,
		            'message' => $message,
		            'sender' => $senderId,
		            'route' => $route
		        );

		        //API URL
		        $url="http://my.msgwow.com/api/sendhttp.php";

		        // init the resource
		        $ch = curl_init();
		        curl_setopt_array($ch, array(
		            CURLOPT_URL => $url,
		            CURLOPT_RETURNTRANSFER => true,
		            CURLOPT_POST => true,
		            CURLOPT_POSTFIELDS => $postData
		            //,CURLOPT_FOLLOWLOCATION => true
		        ));

		        //Ignore SSL certificate verification
		        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		        //get response
		        curl_exec($ch);
		        curl_close($ch);
    }

    public static function aaa(){
    	return 'aaa';
    }

    public static function calculateBasicInfo($profile,$no_of_fields){
    	$per = (int) (100/$no_of_fields); 
    	
    	$count = 0;
    	 if(!empty($profile->name)){
    	 	$count++;
    	 }
         if(!empty($profile->email)){
         	$count++;
         }
         if(!empty($profile->phone)){
         	$count++;
         }
         if(!empty($profile->experience)){
         	$count += 2;
         }
         if(!empty($profile->functional_area)){
         	$count++;
         }
         if(!empty($profile->sub_functional_area)){
         	$count++;
         }

         if($count == $no_of_fields){
         	return 100;
         } else{
         	return (int)($count*$per);
         }
        
    }

    public static function calculateJobPreference($profile,$no_of_fields){
    	$per = (int) (100/$no_of_fields); 
    	$count = 0;
    	 if(!empty($profile->current_salary)){
    	 	$count += 2;
    	 }
         if(!empty($profile->expected_salary)){
         	$count += 2;
         }
         if(!empty($profile->state)){
         	$count++;
         }
         if(!empty($profile->location)){
         	$count++;
         }

         if($count == $no_of_fields){
         	return 100;
         } else{
         	return (int)($count*$per);
         }
    }

    public static function calculateEmployement($emp,$no_of_fields){
    	$per = (int) (100/$no_of_fields); 
    	
    	$count = 0;
    	 if(!empty($emp->current_company)){
    	 	$count++;
    	 }
         if(!empty($emp->designation)){
         	$count++;
         }
         if(!empty($emp->working_since)){
         	$count++;
         }

         if($count == $no_of_fields){
         	return 100;
         } else{
         	return (int)($count*$per);
         }
        
    }

    public static function calculateEducation($emp,$no_of_fields){
    	$per = (int) (100/$no_of_fields); 
    	
    	$count = 0;
    	 if(!empty($emp->graduation)){
    	 	$count++;
    	 }
         if(!empty($emp->institute)){
         	$count++;
         }
         if(!empty($emp->specialization)){
         	$count++;
         }
         if(!empty($emp->year_of_passing)){
         	$count++;
         }
         if(!empty($emp->marks)){
         	$count++;
         }

         if($count == $no_of_fields){
         	return 100;
         } else{
         	return (int)($count*$per);
         }      
    }

    public static function jobs_applied_user_count($jobId){
         $users_applied = Applied_job::where('job_id_fk',$jobId)->get();
         return count($users_applied);
    }
    public static function folder_count($userId,$folderId){
        $folder_moves = Folder_move::where('user_id_fk',$userId)
                                    ->where('folder_id_fk',$folderId)->get();
        return count($folder_moves);
    }
   
    public static function user_data($userId){
        $user_data = User::where('id',$userId)->get();
        return $user_data;
    }
    public static function applied_on($user_id, $job_id)
    {
        $users_applied = Applied_job::where('job_id_fk',$job_id)->where('user_id_fk',$user_id)->first();
         return $users_applied;
    }
    public static function comments($userId){
       //$employer = Employer::where('parent_id',$userId)->pluck('user_id_fk')->toarray();
        $user_data = User::where('id',$userId)
                    ->orWhere(function($sq) use ($userId)
                    {
                       $sq->whereIn('id',function($sq) use ($userId)
                            {
                               $sq->select('user_id_fk')
                                    ->from('employer_details')
                                    ->where('parent_id',$userId)
                                    ->pluck('user_id_fk')->toarray();
                            });
                    })
                    ->orWhere(function($sq) use ($userId)
                    {
                       $sq->whereIn('id',function($sq) use ($userId)
                            {
                               $sq->select('parent_id')
                                    ->from('employer_details')
                                    ->where('user_id_fk',$userId)
                                    ->pluck('parent_id')->toarray();
                            });
                    })
                   ->pluck('id')->toarray();
        return $user_data;
    }
	public static function branding_enquiry_data($userId){
        $bp_data = Branding_enquiries::where('employer_user_id',$userId)->first();
        return $bp_data;
    }

    public static function folder_contents($userId,$folderId){
        $folder_data = Folder_move::where('user_id_fk',$userId)
                                    ->where('folder_id_fk',$folderId)->get();
        return $folder_data;
    }
    public static function check_comments($userId){
        $folder_data = Jobseeker_details_comments::where('jobseeker_id_fk',$userId)
                                    ->where('user_id_fk',Auth::user()->id)->get();
        return count($folder_data);
    }  
    public static function locations_by_country($countryId){
        $stateIds = States::where('country_id',$countryId)->get();
        if(count($stateIds)>0){
            foreach ($stateIds as $sId) {
                $s_id[] = $sId->id;
            }
        }
        else{
            $s_id[] = "";
        }
        $location_data = Cities::whereIn('state_id',$s_id)->get();
        return $location_data;
    } 
    public static function profilesmatches_by_post($jpid){
        $jobdetails = Job_post::where('job_id',$jpid)->get();
        $jsids = array();
        //$job_title[0]['jp_type'] == 1
        $title = $jobdetails[0]['job_title'];
        $farea = $jobdetails[0]['functional_area'];
        $itype = $jobdetails[0]['preferred_industry_type'];
        $itype = $jobdetails[0]['preferred_industry_type'];
        $visaval = $jobdetails[0]['visa_status'];
        $locs = $jobdetails[0]['location'];
        $maritalval = $jobdetails[0]['marital_status'];
        $genderval = $jobdetails[0]['gender'];
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
            $arr_locations = explode(",", $locs);
            if(sizeof($arr_locations)>0){
                foreach ($arr_locations as $location) {
                    $jobseekers = Job_seeker_personal_details::where('current_location',$location)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
            }
        }
        if(!empty($maritalval)){
            $jobseekers = Job_seeker_personal_details::where('marital_status',$maritalval)->get();
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
        $profilematches = User::whereIn('id',$jsids)->get();
        return $profilematches;
    }
    public static function profilesmatches_by_post1($jpid){
        $jobdetails = Job_post::where('job_id',$jpid)->get();
        $jsids = array();
        //$job_title[0]['jp_type'] == 1
        $title = $jobdetails[0]['job_title'];
        $farea = $jobdetails[0]['functional_area'];
        $itype = $jobdetails[0]['preferred_industry_type'];
        $itype = $jobdetails[0]['preferred_industry_type'];
        $visaval = $jobdetails[0]['visa_status'];
        $locs = $jobdetails[0]['location'];
        $maritalval = $jobdetails[0]['marital_status'];
        $genderval = $jobdetails[0]['gender'];
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
            $arr_locations = explode(",", $locs);
            if(sizeof($arr_locations)>0){
                foreach ($arr_locations as $location) {
                    $jobseekers = Job_seeker_personal_details::where('current_location',$location)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
            }
        }
        if(!empty($maritalval)){
            $jobseekers = Job_seeker_personal_details::where('marital_status',$maritalval)->get();
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
        $profilematches = User::whereIn('id',$jsids)->get();
        return $jsids;
    }
    public static function cities_by_statename($sname){
        $stateInfo = States::where('name',$sname)->first();
        $stateId = $stateInfo->id;
        $cities = Cities::where('state_id',$stateId)->get();
        return $cities;
    } 
    public static function specialization_by_industrytypeId($itypeid){
        $branches = SubIndustryType::where('industry_type_id_fk',$itypeid)->get();
        return $branches;
    } 
    public static function footer_locations(){
        $locations = Footer_Locations::all();
        return $locations;
    }
    public static function footer_skills(){
        $skills = Footer_Skills::all();
        return $skills;
    } 
    public static function footer_categories(){
        $itypes = IndustryType::where('footer_status',1)->get();
        return $itypes;
    } 
    public static function chart_data($user_id){
        $exist = Chart_data::where('user_id_fk',$user_id)->get();
        $exist_count = count($exist);
        if($exist_count > 0){
            $data = Chart_data::where('user_id_fk',$user_id)->first();
        }
        else{
            $data = Chart_data::where('user_id_fk',$user_id)->first();
        }
        return $data;
    } 
    public static function refine_data(){
        $data = Refine_inputs::all();
        return $data;
    }
    public static function industry_data(){
        $data = IndustryType::orderBy('industry_type_name')->get();
        return $data;
    }
    public static function funcational_data(){
        $data = SubIndustryType::all();
        return $data;
    }
    public static function company_logo($userId){
        $data = Top_Employer::where('user_id_fk',$userId)->first();
        return $data;
    }
    public static function searchesby_empuser($userId){
        $quick_adv_arr = array('2','3');
        $data = Cv_search::where('user_id_fk',$userId)->where('search_type',$quick_adv_arr)->get();
        return count($data);
    }
    public static function jobsby_empuser($userId){
        $data = Job_post::where('user_id_fk',$userId)->where('type',1)->get();
        return count($data);
    }
    public static function landingmenus_one(){
        $data = Landing_Menus::where('menu_id','1')->where('status',1)->get();
        return $data;
    }
    public static function landingmenus_two(){
        $data = Landing_Menus::where('menu_id','2')->where('status',1)->get();
        return $data;
    }
    public static function landingmenus_three(){
        $data = Landing_Menus::where('menu_id','3')->where('status',1)->first();
        return $data;
    }
    public static function landingmenus_post_job_free(){
        $data = Landing_Menus::where('id','15')->where('status',1)->first();
        return $data;
    }
    public static function menus_try_free_cv(){
        $data = Landing_Menus::where('id','14')->where('status',1)->first();
        return $data;
    }
    public static function menusfour_part1(){
        $marr = array(12,13);
        $data = Landing_Menus::whereIn('id',$marr)->where('status',1)->get();
        return $data;
    }
    public static function menusfour_part2(){
        $marr2 = array(14,15);
        $data = Landing_Menus::whereIn('id',$marr2)->where('status',1)->get();
        return $data;
    }
    public static function es_contents($id){
        $data = Emails_sent::where('sent_id',$id)->first();
        return $data;
    }
    public static function get_career_contnet(){
        $data = Content_Careers::where('content_id',1)->first();
        return $data;
    }
    public static function get_about_contnet(){
        $data = Content_About::where('content_id',1)->first();
        return $data;
    }
    public static function get_top_viewed_jobs(){
        $job_post=Job_post::where('job_expire','>=',date('Y-m-d'))->where('status',1)->orderBy('view_count','DESC')->offset(0)->limit(9)->get();
        return $job_post;
    }
    public static function get_contact1_contnet($id){
        $data=Content_Contact::where('type',$id)->get();
        return $data;
    }
    public static function get_contact2_contnet($id){
        $data=Content_Contact::where('type',$id)->get();
        return $data;
    }
    public static function faq_qna($id){
        $data=Question_Answers::where('faq_id_fk',$id)->get();
        return $data;
    }
    public static function user_exist_byemail($ip){
        $data=User::where('email',$ip)->first();
        return $data;
    }
    public static function get_Ad_data(){
        $ads = Manage_Ads::all();
        return $ads;
    }
    public static function get_admin_data(){
        $admin_id = Auth::user()->id;
        $data = Staff_details::where('user_id_fk',$admin_id)->first();
        return $data;
    }
    public static function check_blick_lis($employer_id){
        $user_id = Auth::user()->id;
        return Block_company::where('user_id_fk',$user_id)->where('employer_id_fk',$employer_id)->first();
    }
    public static function get_staff_menus(){
        $staff_id = Auth::user()->id;
        $staff_data = Staff_details::where('user_id_fk',$staff_id)->first();
        $data = Staff_Mappings::where('group_id_fk',$staff_data['group_id_fk'])->get();
        return $data;
    }
    public static function get_sub_menus($menuid){
        $data = Staff_Menus::where('parent_id',$menuid)->get();
        return $data;
    }
    public static function get_no_replie_received(){

        $apply = Applied_job::where('user_id_fk',Auth::user()->id)->get();
        $a_id = array();
        foreach ($apply as $a) {
            $a_id[] = $a->apply_id;
        }
        $reply = Application_reply::whereIn('apply_id_fk',$a_id)->where('Active',1)->get();
         return count($reply);
    }
    public static function check_response_mail($a_id){
        $reply = Application_reply::where('apply_id_fk',$a_id)->get();
         return count($reply);
    }
    public static function check_response_mail2($user_id,$job_id){
        $apply = Applied_job::where('user_id_fk',$user_id)->where('job_id_fk',$job_id)->get();
        $a_id = array();
        foreach ($apply as $a) {
            $a_id[] = $a->apply_id;
        }
        $reply = Application_reply::whereIn('apply_id_fk',$a_id)->get();
         return count($reply);
    }
    public static function check_response_mail3($user_id){
        $employer_id=Auth::user()->id;
        $emails = Emails_sent::where('user_id_fk',$employer_id)->where('job_seeker_id_fk',$user_id)->orderBy('created_at','desc')->where('source',0)->get();
         return count($emails);
    }


    public static function check_status($apply_id){
        $result = Applied_job::where('apply_id',$apply_id)->first();
         return $result->status;
    }
    public static function check_status2($user_id,$job_id){
        $result = Applied_job::where('user_id_fk',$user_id)->where('job_id_fk',$job_id)->first();
        if(count($result)>0)
         return $result->status;
        else
            return 0;
    }
    public static function get_last_updated(){
        $emp_id = Auth::user()->id;
        $js = User::where('id',$emp_id)->first();
        $lutime_list = [];
        foreach($js->academic as $aca){
          array_push($lutime_list, $aca->updated_at);
        }
        foreach($js->career_history as $cah){
          array_push($lutime_list, $cah->updated_at);
        }
        foreach($js->certificates as $cer){
          array_push($lutime_list, $cer->updated_at);
        }
        if(!empty($js->js_cover_letter->updated_at)){
            array_push($lutime_list, $js->js_cover_letter->updated_at);
        }
        if(!empty($js->cvs->updated_at)){
            array_push($lutime_list, $js->cvs->updated_at);
        }
        if(!empty($js->job_preference)){
            array_push($lutime_list, $js->job_preference->updated_at);
        }
        if(!empty($js->personal_details->updated_at)){
            array_push($lutime_list, $js->personal_details->updated_at);
        }
        foreach($js->projects as $proj){
          array_push($lutime_list, $proj->updated_at);
        }
        if(sizeof($js->seminar_details)>0){
          foreach($js->seminar_details as $semi){
            array_push($lutime_list, $semi->updated_at);
          }
        }
        if(sizeof($js->js_technical)>0){
          foreach($js->js_technical as $tech){
            array_push($lutime_list, $tech->updated_at);
          }
        }
        if(!empty($lutime_list))
        {
        $last = date("Y-m-d g:i:s",max(array_map('strtotime',$lutime_list)));     
        }
        else
        {
            $last=date("Y-m-d g:i:s");
        }
        return $last;
    }
    public static function profile_meter(){
        if(empty(Auth::user()))
        {
            return array();
        }
        $total=0;
        $emp_id = Auth::user()->id;
        $user = User::where('id',$emp_id)->first();
        $js = User::where('id',$emp_id)->first();
        $personal = Job_seeker_personal_details::select('first_name','last_name','gender','dob','marital_status','nationality','current_location','mobile_number','landline','email_id','alternative_email_id','current_mailing_address','country_id','state_id','city_id','zip','driving_liicence','current_visa_status','visa_valid_upto','notice_period','known_languages')->where('user_id_fk',$emp_id)->first();
        // dd(sizeof($personal));
        $total_personal=21;
        $i=21;
        if(!empty($personal))
        {
            $personal=$personal->toArray();
            foreach ($personal as $key => $value) {
                if(!empty($value))
                {
                    $i--;
                }
            }
        }
        $unfilled_personal=$i;
        $data['personal']=round((10/$total_personal)*($total_personal-$unfilled_personal),1);
        //print_r($academic);exit();$
        $max = Academic_details::where('user_id_fk',$emp_id)->max('year_of_passing');
        
        $highest_academic = Academic_details::select('qualification','year_of_passing','specialization','institute_name','city_id','state_id','country_id')->where('user_id_fk',$emp_id)->where('year_of_passing',$max)->first();

        $total_academic=7;
        $i=7;
        if(!empty($highest_academic))
        {
            $highest_academic=$highest_academic->toArray();
            foreach ($highest_academic as $key => $value) {
                if(!empty($value))
                {
                    $i--;
                }
            }
        }
        $unfilled_academic=$i;
        // dd($unfilled_academic);

         $academic = Academic_details::select('qualification','year_of_passing','specialization','institute_name','city_id','state_id','country_id')->where('user_id_fk',$emp_id)->where('year_of_passing','!=',$max)->get()->toArray();
         // dd($academic);
         foreach ($academic as $value) {
            $total_academic+=7;
            // $result=$value->toArray();
            $i=7;
             foreach ($value as $key2 => $value2) {
                // dd($value2);
                  if(!empty($value2))
                    {
                        $i--;
                    }
             }
            $unfilled_academic+=$i;
         }
        $data['academic']=round((10/$total_academic)*($total_academic-$unfilled_academic),1);
         // dd($unfilled_academic);
        $current_company = Career_history::select('current_company','employer_name','city_id','state_id','country_id','from_date','to_date','job_title','employement_type','monthly_salary','description')->where('user_id_fk',$emp_id)->where('current_company','2')->first(); 
        // dd($current_company);
        $total_company=11;
        $i=11;
        if(!empty($current_company))
        {
            $current_company=$current_company->toArray();
            foreach ($current_company as $key => $value) {
                if(!empty($value))
                {
                    $i--;
                }
            }
        }
        $unfilled_company=$i;
        $career = Career_history::select('current_company','employer_name','city_id','state_id','country_id','from_date','to_date','job_title','employement_type','monthly_salary','description')->where('user_id_fk',$emp_id)->where('current_company','1')->get()->toArray();
        foreach ($career as $values) {
            $total_company+=11;
            $i=11;
             foreach ($values as $key => $value3) {
                  if(!empty($value3))
                    {
                        $i--;
                    }
             }
            $unfilled_company+=$i;
         }

        $data['company']=round((10/$total_company)*($total_company-$unfilled_company),1);

         $job_preference = Job_preference::select('preferred_job_title','preferred_job_location','preferred_job_function','preferred_industry_type','preferred_job_type','preferred_monthly_salary')->where('user_id_fk',$emp_id)->first(); 
        // dd($job_preference);
        $total_preference=6;
        $i=6;
        if(!empty($job_preference))
        {
            $job_preference=$job_preference->toArray();
            foreach ($job_preference as $key => $value) {
                if(!empty($value))
                {
                    $i--;
                }
            }
        }
        $unfilled_preference=$i;

        $data['preference']=round((10/$total_preference)*($total_preference-$unfilled_preference),1);

        $skils = Job_seeker_technical_skills::select('skill','level_of_expertise','years_of_experience','year_last_used')->where('user_id_fk',$emp_id)->get()->toArray();
        $total_skils=0;
        $unfilled_skils=0;
        // dd($skils);
        if(empty($skils))
        {
            $total_skils=4;
            $unfilled_skils=4;        
        }
        foreach ($skils as $values) {
            $total_skils+=4;
            $i=4;
             foreach ($values as $key => $value3) {
                  if(!empty($value3))
                    {
                        $i--;
                    }
             }
            $unfilled_skils+=$i;
         }
        $data['skils']=round((10/$total_skils)*($total_skils-$unfilled_skils),1);

         $certificate = Job_seeker_certificate::select('certificate_name','issued_by','valid_from')->where('user_id_fk',$emp_id)->get()->toArray();
        $total_certificate=0;
        $unfilled_certificate=0;
        // dd($certificate);
        if(empty($certificate))
        {
            $total_certificate=3;
            $unfilled_certificate=3;        
        }
        foreach ($certificate as $values) {
            $total_certificate+=3;
            $i=3;
             foreach ($values as $key => $value3) {
                  if(!empty($value3))
                    {
                        $i--;
                    }
             }
            $unfilled_certificate+=$i;
         }
        $data['certificate']=round((10/$total_certificate)*($total_certificate-$unfilled_certificate),1);
        $seminar=$user->seminar_detail();
        // return json_encode($seminar);
         $seminar = Seminar_details::select('seminar_name','year')->where('user_id_fk',$emp_id)->get()->toArray();
        // return $data;
        $total_seminar=0;
        $unfilled_seminar=0;
        // dd($seminar);
        if(empty($seminar))
        {
            $total_seminar=2;
            $unfilled_seminar=2;        
        }
        foreach ($seminar as $values) {
            $total_seminar+=2;
            $i=2;
             foreach ($values as $key => $value3) {
                  if(!empty($value3))
                    {
                        $i--;
                    }
             }
            $unfilled_seminar+=$i;
         }
        $data['seminar']=round((10/$total_seminar)*($total_seminar-$unfilled_seminar),1);

         $projects = Project::select('project_name','duration_from','duration_to','organization','team_size','location','project_description','role','responsibilities','technology','operating_system')->where('user_id_fk',$emp_id)->get()->toArray();
        $total_projects=0;
        $unfilled_projects=0;
        // dd($projects);
        if(empty($projects))
        {
            $total_projects=11;
            $unfilled_projects=11;        
        }
        foreach ($projects as $values) {
            $total_projects+=11;
            $i=11;
             foreach ($values as $key => $value3) {
                  if(!empty($value3))
                    {
                        $i--;
                    }
             }
            $unfilled_projects+=$i;
         }
        $data['projects']=round((10/$total_projects)*($total_projects-$unfilled_projects),1);

         $cv = Cv::select('cv_title','cv','cv_text')->where('user_id_fk',$emp_id)->get()->toArray();
        $total_cv=0;
        $unfilled_cv=0;
        // dd($cv);
        if(empty($cv))
        {
            $total_cv=2;
            $unfilled_cv=2;        
        }
        foreach ($cv as $values) {
            $total_cv+=2;
            $i=2;
            $f=0;
             foreach ($values as $key => $value3) {
                  if(!empty($value3))
                    {
                        if($key=='cv' || $key=='cv_text')
                        {
                            if($f==0 && !empty($value3) )
                            {
                                $i--;
                                $f=1;
                            } 
                        }
                        else
                        {
                            $i--;
                        }
                    }
             }
            $unfilled_cv+=$i;
         }
        $data['cv']=round((10/$total_cv)*($total_cv-$unfilled_cv),1);

           $cover_letter = Cover_letter::select('cover_letter_name','cover_letter')->where('user_id_fk',$emp_id)->first(); 
        // dd($cover_letter);
        $total_cover=2;
        $i=2;
        if(!empty($cover_letter))
        {
            $cover_letter=$cover_letter->toArray();
            foreach ($cover_letter as $key => $value) {
                if(!empty($value))
                {
                    $i--;
                }
            }
        }
        $unfilled_cover=$i;
        $data['cover']=round((10/$total_cover)*($total_cover-$unfilled_cover),1);
        foreach ($data as $key) {
            $total+=$key;
        }
        $data['total']=$total;
        return $data;
    }
    public static function sendSMS($data){
        try{
                $ch = curl_init();
                $user="developer2@indglobal-consulting.com:indglobal123";

                $sender="TEST SMS";
                $receipientno=$data['recipient_no']; 
                $senderID="TEST SMS"; 
                $msgtxt=$data['message']; 
                curl_setopt($ch,CURLOPT_URL,  "http://api.mVaayoo.com/mvaayooapi/MessageCompose");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$user&senderID=$senderID&receipientno=$receipientno&msgtxt=$msgtxt");
                $buffer = curl_exec($ch);

                if(empty ($buffer))
                { echo " buffer is empty "; }
                else
                { return true; } 
                curl_close($ch);
        }catch(Exception $e){
            return false;
        }       
    }
    public static function branding_pkg()
    {
        $user_id = Auth::user()->id;
        //return $user_id;
        $user = User::where('id',$user_id)->first();
        //return count($user->user_active_packages);
        if(count($user->user_active_packages) > 0){
            $pack = array();
            foreach ($user->user_active_packages as $b) {
                if(!empty($b->packa))
                $pack[]= $b->packa->type;
            }
            if(in_array("4", $pack)){
                return 1;
            }else{
                return 0;
            }
        }
    }
    public static function microsite($id)
    {
        $micro = Microsite_details::where('site_id',$id)->first();
        $employe = Employer::where('user_id_fk',$micro->user_id_fk)->orWhere('parent_id',$micro->user_id_fk)->get();
        $job_count = 0;
        foreach ($employe as $e) {
           $job_count = $job_count+count($e->jobsposted);
        }
        return $job_count;
    }
    public static function current_user_package(){
        if(Auth::user()->employer_details->parent_id>0)
            {

                $package=User_package::where('user_package_id',Auth::user()->employer_details->user_package_id)->first();
                return $package;
            }
        else
        {
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        $user_pack_id=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                   
                        $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==3)
                            {
                                $packages[]=$key->packa->package_id;
                                $user_pack_id[] = $key->user_package_id;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->enterprise_pack) || !empty($key->packa->saver_pack->regular_pack)))
                            {
                                $packages[]=$key->packa->package_id;
                                $user_pack_id[] = $key->user_package_id;
                                $total++;
                            }
                            elseif($key->packa->type==4 && !empty($key->packa->branding_pack)  )
                            {
                                if($key->packa->branding_pack->package_type==1 && !empty($key->packa->branding_pack->job_posting))
                                {
                                    $packages[]=$key->packa->package_id;
                                    $user_pack_id[] = $key->user_package_id;
                                    $total++;
                                }
                            }
                        }
                }
            }
        }
        $user_pack=User_package::whereIn('user_package_id',$user_pack_id)->orderBy('created_at','DESC')->first();
        return $user_pack;
        }
    }

    public static function current_cv_user_package(){
        if(Auth::user()->employer_details->parent_id>0)
        {

            $package=User_package::where('user_package_id',Auth::user()->employer_details->user_package_id)->first();
                return $package;
        }
    else
        {
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $ids=[];
        $user_pack_id=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==2 || $key->packa->type==1)
                {
                        $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            if($key->packa->type==2)
                            {
                                $ids[]=$key->packa->package_id;
                                $user_pack_id[] = $key->user_package_id;
                                $total++;
                            }
                            elseif($key->packa->type==1 && (!empty($key->packa->saver_pack->cv_access) || !empty($key->packa->saver_pack->profile_views) || !empty($key->packa->saver_pack->email)))
                            {
                                $ids[]=$key->packa->package_id;
                                $user_pack_id[] = $key->user_package_id;
                                $total++;
                            }
                        }
                }
            }
        }
        // return $packages;
        $user_pack=User_package::whereIn('user_package_id',$user_pack_id)->orderBy('created_at','DESC')->first();
        return $user_pack;
        }
    }

    public static function expire_package(){
        if(Auth::user()->employer_details->parent_id==0)
        {
            $s_profile_views= 0;
            $s_cv_search = 0;
            $s_emails_sent = 0;
            $s_job_post= 0;
            $user_package_id = "";
            if(!empty(Helper::current_user_package()))
            {
                $user_package_id = Helper::current_user_package()->user_package_id;
            }
            
            $userid = Auth::user()->id;
            $parent_user = Auth::user()->id;
             $sub_users = User::where(function($sq) use ($parent_user)
                                {
                                   $sq->whereIn('id',function($sq) use ($parent_user)
                                        {
                                           $sq->select('user_id_fk')
                                                ->from('employer_details')
                                                ->where('parent_id',$parent_user)
                                                ->pluck('user_id_fk')->toarray();
                                        });
                                })
                                ->pluck('id')->toarray();
            
             foreach ($sub_users as $s) {
                $s_profile_views = $s_profile_views + count(Profile_views::where('user_id_fk',$s)->where('user_package_id',$user_package_id)->get());
                $s_cv_search = $s_cv_search + count(Cv_downloads::where('user_id_fk', $s)->where('user_package_id',$user_package_id)->get());
                $s_emails_sent = $s_emails_sent + count(Emails_sent::where('user_id_fk',$s)->where('user_package_id',$user_package_id)->get());
                $s_job_post = $s_job_post + count(Job_post::where('user_id_fk',$s)->where('jp_type','<>',1)->where('user_package_id',$user_package_id)->where('type',1)->get());
             }

            
            $p_profile_views= count(Profile_views::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->get());
            $p_cv_search = count(Cv_downloads::where('user_id_fk', $parent_user)->where('user_package_id',$user_package_id)->get());
            $p_emails_sent = count(Emails_sent::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->get());
            $p_job_post = count(Job_post::where('user_id_fk',$parent_user)->where('jp_type','<>',1)->where('user_package_id',$user_package_id)->where('type',1)->get());

            $total_job = 0;
            $total_cv = 0;
            $total_profile_views = 0;
            $total_email = 0;


            $package = User::whereId(Auth::user()->id)->first()->user_active_packages;

            foreach ($package as $user_pack) {
                if(isset($user_pack->packa))
                {
                     if($user_pack->packa->type==1)
                        {
                            $total_job = $user_pack->packa->saver_pack->enterprise_pack + $user_pack->packa->saver_pack->regular_pack;
                            $total_cv = $user_pack->packa->saver_pack->cv_access;
                            $total_profile_views = $user_pack->packa->saver_pack->profile_views;
                            $total_email = $user_pack->packa->saver_pack->email;
                            if($total_job == ($p_job_post + $s_job_post) && $total_cv == ($p_cv_search + $s_cv_search) && $total_profile_views == ($p_profile_views + $s_profile_views) && $total_email == ($p_emails_sent + $s_emails_sent) )
                            {
                                 $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                                   $user->expiry_date = date('Y-m-d');
                                   $user->status = 3;
                                   $user->save();
                            }
                        }elseif($user_pack->packa->type==2)
                        {
                            $total_cv = $user_pack->packa->cv_pack->cv_access;
                            $total_profile_views = $user_pack->packa->cv_pack->profile_views;
                            $total_email = $user_pack->packa->cv_pack->email;
                            if($total_cv == ($p_cv_search + $s_cv_search) && $total_profile_views == ($p_emails_sent + $s_emails_sent) && $total_email == ($p_emails_sent + $s_emails_sent) )
                            {
                                 $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                                   $user->expiry_date = date('Y-m-d');
                                   $user->status = 3;
                                   $user->save();
                            }
                            
                        }elseif($user_pack->packa->type==3)
                        {
                            $total_job = $user_pack->packa->job_post_pack->job_posting;
                            if($total_job == ($p_job_post + $s_job_post)  )
                            {
                                 $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                                   $user->expiry_date = date('Y-m-d');
                                   $user->status = 3;
                                   $user->save();
                            }

                        }elseif($user_pack->packa->type==4 && $user_pack->packa->branding_pack->package_type == 1)
                        {
                            $total_job = $user_pack->packa->branding_pack->job_posting;
                            if($total_job == ($p_job_post + $s_job_post)  )
                            {
                                 $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                                   $user->expiry_date = date('Y-m-d');
                                   $user->status = 3;
                                   $user->save();
                            }
                        }
                }
            }
        
        }elseif(Auth::user()->employer_details->parent_id > 0)
        {
            $s_profile_views= 0;
            $s_cv_search = 0;
            $s_emails_sent = 0;
            $s_job_post= 0;

            $user_package_id = User::where('id',Auth::user()->id)->first()->employer_details->user_package_id;
            $userid = Auth::user()->id;
            $parent_user = Auth::user()->employer_details->parent_id;
            $user_pack = User_package::where('user_package_id',$user_package_id)->orderBy('created_at','DESC')->first();
             $sub_users = User::where(function($sq) use ($parent_user)
                                {
                                   $sq->whereIn('id',function($sq) use ($parent_user)
                                        {
                                           $sq->select('user_id_fk')
                                                ->from('employer_details')
                                                ->where('parent_id',$parent_user)
                                                ->pluck('user_id_fk')->toarray();
                                        });
                                })
                                ->pluck('id')->toarray();
            
             foreach ($sub_users as $s) {
                $s_profile_views = $s_profile_views + count(Profile_views::where('user_id_fk',$s)->where('user_package_id',$user_package_id)->get());
                $s_cv_search = $s_cv_search + count(Cv_downloads::where('user_id_fk', $s)->where('user_package_id',$user_package_id)->get());
                $s_emails_sent = $s_emails_sent + count(Emails_sent::where('user_id_fk',$s)->where('user_package_id',$user_package_id)->get());
                $s_job_post = $s_job_post + count(Job_post::where('user_id_fk',$s)->where('jp_type','<>',1)->where('user_package_id',$user_package_id)->where('type',1)->get());
             }

            
            $p_profile_views= count(Profile_views::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->get());
            $p_cv_search = count(Cv_downloads::where('user_id_fk', $parent_user)->where('user_package_id',$user_package_id)->get());
            $p_emails_sent = count(Emails_sent::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->get());
            $p_job_post = count(Job_post::where('user_id_fk',$parent_user)->where('jp_type','<>',1)->where('user_package_id',$user_package_id)->where('type',1)->get());

            $total_job = 0;
            $total_cv = 0;
            $total_profile_views = 0;
            $total_email = 0;


                    if($user_pack->packa->type==1)
                    {
                        $total_job = $user_pack->packa->saver_pack->enterprise_pack + $user_pack->packa->saver_pack->regular_pack;
                        $total_cv = $user_pack->packa->saver_pack->cv_access;
                        $total_profile_views = $user_pack->packa->saver_pack->profile_views;
                        $total_email = $user_pack->packa->saver_pack->email;
                        if($total_job == ($p_job_post + $s_job_post) && $total_cv == ($p_cv_search + $s_cv_search) && $total_profile_views == ($p_emails_sent + $s_emails_sent) && $total_email == ($p_emails_sent + $s_emails_sent) )
                        {
                             $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                               $user->expiry_date = date('Y-m-d');
                               $user->status = 3;
                               $user->save();
                        }
                    }elseif($user_pack->packa->type==2)
                    {
                        $total_cv = $user_pack->packa->cv_pack->cv_access;
                        $total_profile_views = $user_pack->packa->cv_pack->profile_views;
                        $total_email = $user_pack->packa->cv_pack->email;
                        if($total_cv == ($p_cv_search + $s_cv_search) && $total_profile_views == ($p_emails_sent + $s_emails_sent) && $total_email == ($p_emails_sent + $s_emails_sent) )
                        {
                             $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                               $user->expiry_date = date('Y-m-d');
                               $user->status = 3;
                               $user->save();
                        }
                        
                    }elseif($user_pack->packa->type==3)
                    {
                        $total_job = $user_pack->packa->job_post_pack->job_posting;
                        if($total_job == ($p_job_post + $s_job_post)  )
                        {
                             $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                               $user->expiry_date = date('Y-m-d');
                               $user->status = 3;
                               $user->save();
                        }

                    }elseif($user_pack->packa->type==4 && $user_pack->packa->branding_pack->package_type == 1)
                    {
                        $total_job = $user_pack->packa->branding_pack->job_posting;
                        if($total_job == ($p_job_post + $s_job_post)  )
                        {
                             $user =User_package::where('user_id_fk',$parent_user)->where('user_package_id',$user_package_id)->first();
                               $user->expiry_date = date('Y-m-d');
                               $user->status = 3;
                               $user->save();
                        }
                    }
        }
    }
    public static function menus()
    {
    	$menus = Landing_Menus::where('id',16)->first();
    	return $menus->status;
    }
    public static function see_our_product()
    {
    	$menus = Landing_Menus::where('id',17)->first();
    	return $menus->status;
    }
    public static function last_login()
    {
        $last_login = Last_login::where('user_id_fk',Auth::user()->id)->orderBy('login_id','DESC')->first();
        return $last_login->login_time;
    }
    public static function nationality()
    {
       $nat = Nationality::all();
       return $nat;
    }
    public static function buy_upgrade_menu(){ 
        $data = Landing_Menus::where('id',18)->first();
        return $data;
    }
    public static function free_job_menu(){
        $data = Landing_Menus::where('id',19)->first();
        return $data;
    }
    public static function free_cv_menu(){
        $data = Landing_Menus::where('id',14)->first();
        return $data;
    }
    public static function job_menu(){
        $data = Landing_Menus::where('id',20)->first();
        return $data;
    }
    public static function user_last_update($id)
    {
        $emp_id = $id;
        $js = User::where('id',$emp_id)->first();
        $lutime_list = [];
        foreach($js->academic as $aca){
          array_push($lutime_list, $aca->updated_at);
        }
        foreach($js->career_history as $cah){
          array_push($lutime_list, $cah->updated_at);
        }
        foreach($js->certificates as $cer){
          array_push($lutime_list, $cer->updated_at);
        }
        if(!empty($js->js_cover_letter->updated_at)){
            array_push($lutime_list, $js->js_cover_letter->updated_at);
        }
        if(!empty($js->cvs->updated_at)){
            array_push($lutime_list, $js->cvs->updated_at);
        }
        if(!empty($js->job_preference)){
            array_push($lutime_list, $js->job_preference->updated_at);
        }
        if(!empty($js->personal_details->updated_at)){
            array_push($lutime_list, $js->personal_details->updated_at);
        }
        foreach($js->projects as $proj){
          array_push($lutime_list, $proj->updated_at);
        }
        if(sizeof($js->seminar_details)>0){
          foreach($js->seminar_details as $semi){
            array_push($lutime_list, $semi->updated_at);
          }
        }
        if(sizeof($js->js_technical)>0){
          foreach($js->js_technical as $tech){
            array_push($lutime_list, $tech->updated_at);
          }
        }
        if(!empty($lutime_list))
        {
        $last = date("Y-m-d g:i:s",max(array_map('strtotime',$lutime_list)));     
        }
        else
        {
            $last=date("Y-m-d g:i:s");
        }
        return $last;
    }
    public static function file_size($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    public static function banners()
    {
        $b = Banners::first();
        return $b;
    }
    public static function about_us_offers()
    {
        $list = About_us_offers::where('id',1)->first();
        return $list;
    }
    public static function testimonial_menu()
    {
         $data = Landing_Menus::where('id',21)->where('status',1)->get();
        return $data;
    }
    public static function company_menu()
    {
         $data = Landing_Menus::where('id',4)->where('status',1)->get();
        return $data;
    }
    public static function sector_menu()
    {
         $data = Landing_Menus::where('id',3)->where('status',1)->get();
        return $data;
    }
    public static function location_menu()
    {
         $data = Landing_Menus::where('id',1)->where('status',1)->get();
        return $data;
    }
    public static function agency_menu()
    {
         $data = Landing_Menus::where('id',5)->where('status',1)->get();
        return $data;
    }
    public static function part_time_menu()
    {
         $data = Landing_Menus::where('id',6)->where('status',1)->get();
        return $data;
    }
    public static function last_logins($id)
    {
        $last_login = Last_login::where('user_id_fk',$id)->orderBy('login_id','DESC')->first();
        return $last_login;
    }
    public static function get_last_updates($id){
        $emp_id = $id;
        $js = User::where('id',$emp_id)->first();
        $lutime_list = [];
        foreach($js->academic as $aca){
          array_push($lutime_list, $aca->updated_at);
        }
        foreach($js->career_history as $cah){
          array_push($lutime_list, $cah->updated_at);
        }
        foreach($js->certificates as $cer){
          array_push($lutime_list, $cer->updated_at);
        }
        if(!empty($js->js_cover_letter->updated_at)){
            array_push($lutime_list, $js->js_cover_letter->updated_at);
        }
        if(!empty($js->cvs->updated_at)){
            array_push($lutime_list, $js->cvs->updated_at);
        }
        if(!empty($js->job_preference)){
            array_push($lutime_list, $js->job_preference->updated_at);
        }
        if(!empty($js->personal_details->updated_at)){
            array_push($lutime_list, $js->personal_details->updated_at);
        }
        foreach($js->projects as $proj){
          array_push($lutime_list, $proj->updated_at);
        }
        if(sizeof($js->seminar_details)>0){
          foreach($js->seminar_details as $semi){
            array_push($lutime_list, $semi->updated_at);
          }
        }
        if(sizeof($js->js_technical)>0){
          foreach($js->js_technical as $tech){
            array_push($lutime_list, $tech->updated_at);
          }
        }
        if(!empty($lutime_list))
        {
        $last = date("Y-m-d g:i:s",max(array_map('strtotime',$lutime_list)));     
        }
        else
        {
            $last=date("Y-m-d g:i:s");
        }
        return $last;
    }
    public static function delete_expired_job($id)
    {
        Job_post::where('job_id',$id)->update(['status'=>2]);
    }
    public static function item_code()
    {
        $alpha_key = '';
        $keys = range('A', 'Z');

        for ($i = 0; $i < 3; $i++) {
            $alpha_key .= $keys[array_rand($keys)];
        }           

        $key = '';
        $keys = range(0, 9);

        for ($i = 0; $i < 3; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        $aa = $alpha_key . $key;
        $order_number = str_shuffle($aa);
        return $order_number;
    }

    public static function sub_users_access_limit($userid)
    {
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $data['profile_views']=Profile_views::where('user_id_fk',$userid)->where('user_package_id',$user_package_id)->get();
        $data['cv_search'] = Cv_downloads::where('user_id_fk', $userid)->where('user_package_id',$user_package_id)->get();
        $data['emails_sent']=Emails_sent::where('user_id_fk',$userid)->where('user_package_id',$user_package_id)->get();
        $data['job_post']=Job_post::where('user_id_fk',$userid)->where('jp_type','<>',1)->where('user_package_id',$user_package_id)->where('type',1)->get();
        return $data;
    }
    public static function sub_user_job_posted_count($userid)
    {
        $jobcount = 0;
         $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $subuser = Employer::where('parent_id',$userid)->get();
        foreach ($subuser as $s) {
            $job = Job_post::where('user_id_fk',$s->user_id_fk)->where('type','1')->where('jp_type','<>','1')->where('user_package_id',$user_package_id)->get();
            $jobcount = $jobcount + count($job);
        }
        return $jobcount;
    }
    public static function package_rules()
    {
        $rules = Package_rules::all();
        return $rules;
    }
    public static function package_content()
    {
        $content = Package_content::first();
        return $content;
    }

    public static function current_microsite_pack()
    {
        if(Auth::user()->employer_details->parent_id>0)
        {
        }
        else
        {
        $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        $user_pack_id=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3 || $key->packa->type==1 || $key->packa->type==4)
                {
                    $expiry_date = date('Y-m-d H:i:s', strtotime($key->expiry_date));
                        $now = date('Y-m-d H:i:s');
                        if($expiry_date>=$now)
                        {
                            
                            if($key->packa->type==4 && !empty($key->packa->branding_pack)  )
                            {
                                if($key->packa->branding_pack->package_type==2 )
                                {
                                    $packages[]=$key->packa->package_id;
                                    $user_pack_id[] = $key->user_package_id;
                                    $total++;
                                }
                            }
                        }
                }
            }
        }
        $user_pack=User_package::whereIn('user_package_id',$user_pack_id)->orderBy('created_at','DESC')->first();
        return $user_pack;
        }
    }
    public static function job_assigned_to_sub_user()
    {
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $employer = Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->get();
        $job_count = 0;
        foreach ($employer as $e) {
           $job_count = $job_count + $e->job_post_limit;
        }
        return $job_count;
    }
    public static function enter_job_to_sub_user()
    {
        $user_package_id = "";
        if(!empty(Helper::current_user_package()->user_package_id))
        {
            $user_package_id = Helper::current_user_package()->user_package_id;
        }
        $employer = Employer::where('parent_id',Auth::user()->id)->where('user_package_id',$user_package_id)->where('enterprise_job_access',1)->get();
        $job_count = 0;
        foreach ($employer as $e) {
           $job_count = $job_count + $e->job_post_limit;
        }
        return $job_count;
    }
    public static function check_reg_user($email)
    {
        $user = User::where('email',$email)->first();
        return $user;
    }
    

}