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
use App\Model\Bulk_Cvs;
use App\Model\Docs;
use App\Model\Job_post_package;
use App\Model\Job_seeker_cv;
use App\Helper\FiletoText;
use DB;
use App\Model\Notice;
use App\Model\Job_post;
use App\Model\Cv_downloads;
use App\Model\Profile_views;
use App\Model\Emails_sent;


class EmployerProfileController extends Controller{
    public function profile_profile_update_mode(){
         $user_id = Auth::user()->id;
         $industry = IndustryType::orderBy('industry_type_name')->get();
         $country = Countries::all();
         $data = Employer::where('user_id_fk',$user_id)->get();
         $states = States::where('country_id',$data[0]->country)->get();
         $cities = Cities::where('state_id',$data[0]->state)->get();
         $edit = "edit";
         $page = "profile";
         $user = User::where('id',$user_id)->get();
         $notice = Notice::first();
         //echo json_encode($data);

        
       //return view('admin/employer/employer_list',compact('employer'));
        return view('employer/employer',compact('user','data','edit','industry','country','states','cities','page','notice'));
    }
    public function profile_photo_update(Request $request){
        //print_r($request->employer_id);
        $validation = $this->validate($request, [
        'change-profile-name' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($request->hasFile('change-profile-name')){
           $destination = public_path('/uploads/employer');  
           $relativepath = '/uploads/employer';  
           $file = $request->file('change-profile-name');
           $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
           $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
           Employer::where('employer_id',$request->employer_id)->update(['profile_picture'=>$filelocation]);
           echo 1;die();
           //$user->profile_picture = $filelocation;
        }
    }
    public function employer_update(Request $request){
        // print_r($request->all());exit();
        $user_id = Auth::user()->id;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $designation = $request->designation;
        $companyname = $request->company_name;
        $website = $request->website_url;
        $landline = $request->landline;
        $mobile = $request->mobile;
        $industrytype = $request->industry_type;
        $zipcode = $request->zip;
        $officeAddr = $request->office_address;
        $country = $request->country;
        $state = $request->state;
        $new_state = $request->new_state;
        if(!empty($new_state)){
            $ip_state = $new_state;
        }
        else
            $ip_state = $state;
        $city = $request->city;
        $new_city = $request->new_city;
        if(!empty($new_city)){
            $ip_city = $new_city;
        }
        else
            $ip_city = $city;
        // print_r($request->industry_type);die;
        if($industrytype != "0")
        {
        // print_r($request->industry_type);die;
           Employer::where('employer_id',$request->employer_id)->update(['first_name'=>$first_name,'last_name'=>$last_name,'designation'=>$designation,'company_name'=>$companyname,'website_url'=>$website,'landline'=>$landline,'mobile'=>$mobile,'industry_type'=>$industrytype,'zip'=>$zipcode,'office_address'=>$officeAddr,'country'=>$country,'state'=>$ip_state,'city'=>$ip_city]);
        }else{
        // print_r($request->employer_id);die;
             $industry = new IndustryType();
            $industry->industry_type_name = $request->industries;
            $industry->save();

             Employer::where('employer_id',$request->employer_id)->update(['first_name'=>$first_name,'last_name'=>$last_name,'designation'=>$designation,'company_name'=>$companyname,'website_url'=>$website,'landline'=>$landline,'mobile'=>$mobile,'industry_type'=>$request->industries,'zip'=>$zipcode,'office_address'=>$officeAddr,'country'=>$country,'state'=>$ip_state,'city'=>$ip_city]);
            
        }

        
        $name = $first_name." ".$last_name;
        User::where('id',$user_id)->update(['name'=>$name]);
        echo 3;die();
    }
    public function manage_users(){
        $user_id = Auth::user()->id;
        $country = Countries::all();
        $data = Employer::where('user_id_fk',$user_id)->get();
        $users = Employer::where('parent_id',$user_id)->where('activate','<>',3)->get();
		$user = User::where('id',$user_id)->get();
        $package = User_package::where('user_id_fk',$user_id)->get();
        $page = "manageusers";
         $package = User::whereId(Auth::user()->id)->first()->user_active_packages;
        $total=0;
        $packages=[];
        foreach ($package as $key) {
            if(isset($key->packa))
            {
                if($key->packa->type==3)
                {
                    $packages[]=$key->packa;
                }
            }
        }
        foreach ($packages as $key) {
            $job=Job_post_package::where('package_id_fk',$key->package_id)->first();
        // dd($job);
            if($job->pack_type==1)
            {
                $total++;
            }
        }
        // dd($total);
        //echo json_encode($package);
        return view('employer/manage_users',compact('data','country','user','users','package','page'));
    }

    public function employer_user_save(Request $request){
         //print_r($request->all());exit();
        $pwd_validate = Validator::make($request->all(),['pwd' => 'required|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/']);
        if ($pwd_validate->fails()){
            echo 8;die();
        }
        $user_id = Auth::user()->id;
        $data = Employer::where('user_id_fk',$user_id)->get();
        $first_name = $request->uname;
        $email_id = $request->email_id;
        $designation = $request->design;
        $password = $request->pwd;
        $mobile = $request->contno;
        $country_code = $request->country_code;
        $limitvalue = $request->limit;
        $role_id = $request->role_radio;
        $check_email = User::where('email',$email_id)->first();
        $check_employer_email = Employer::where('email',$email_id)->first();
        if(!empty($check_email)){
            echo 1;die();
        }
        if(!empty($check_employer_email)){
            echo 5;die();
        }
        $check_mobile = User::where('mobile',$mobile)->first();
        if(!empty($check_mobile)){
            echo 2;die();
        }
        $total_cv_search=Helper::cv_search_access();
        $total_profile_views=Helper::profile_view_access();
        $total_send_mails=Helper::send_mail_access();

        $cv_searched=count(Helper::total_cv_searched());
        $profile_views=count(Helper::total_profile_viewed());
        $send_mails=count(Helper::total_send_mailed());
        if(isset($request->cv_search))
        {
            if(!empty($request->cv_limit) && ($total_cv_search-$cv_searched) < $request->cv_limit)
            {
                echo 12;die();
            }
            if(!empty($request->view_limit) && ($total_profile_views-$profile_views) < $request->view_limit)
            {
                echo 13;die();
            }
            if(!empty($request->mail_limit) && ($total_send_mails-$send_mails) < $request->mail_limit)
            {
                echo 14;die();
            }
        }
        $total_job_posts=Helper::job_post_access();
        $job_posted=count(Helper::total_job_posted());

        if(isset($request->job_post))
        {
            if(($total_job_posts-$job_posted) < $request->job_limit)
            {
                echo 11;die();
            }
        }
        $employer=Employer::where('user_id_fk',Auth::user()->id)->first();
        if(!empty($request->cv_limit))
            $employer->assigned_cv_search=$employer->assigned_cv_search+$request->cv_limit;
        if(!empty($request->view_limit))
            $employer->assigned_cv_search=$employer->assigned_profile_views+$request->view_limit;
        if(!empty($request->mail_limit))
            $employer->assigned_cv_search=$employer->assigned_send_mails+$request->mail_limit;
        $employer->assigned_job_post=$employer->assigned_job_post+$request->job_limit;
        $employer->save();
        // print_r(1111);die;
        $employer = new Employer;
        //start - default values insert - setup for local dev environment
        $employer->user_id_fk = 0;
        $employer->website_url = "www.example.com";
        $employer->last_name = "";
        $employer->toll_free = "18004357686";
        $employer->otp = "";
        $employer->profile_picture = "";
        //End
        $employer->first_name = $first_name;
        $employer->designation = $designation;
        $employer->mobile = $mobile;
        $employer->password = $password;
        $employer->company_name = $data[0]->company_name;
        $employer->industry_type = $data[0]->industry_type;
        $employer->office_address = $data[0]->office_address;
        $employer->landline = $data[0]->landline;
        $employer->country_code = $country_code;
        $employer->country = $data[0]->country;
        $employer->state = $data[0]->state;
        $employer->city = $data[0]->city;
        $employer->zip = $data[0]->zip;
        $employer->role_id = $role_id;
        $employer->limitvalue = $limitvalue;
        //$employer->type = $comp_consult;
        $employer->email = $email_id;
        $employer->activate = '1';
        //echo Helper::current_job_package();die();
        if(!empty(Helper::current_cv_package())){
        	$cv_package=User_package::where('user_id_fk',Auth::user()->id)->where('package_id_fk',Helper::current_cv_package()->package_id)->where('status',2)->first();	
        	$employer->cv_package_id = Helper::current_cv_package()->package_id;
        	$employer->cv_exp_date = $cv_package->expiry_date;
        }
        
        $job_package=User_package::where('user_id_fk',Auth::user()->id)->where('package_id_fk',Helper::current_job_package()->package_id)->where('status',2)->first();

        
        $employer->job_package_id = Helper::current_job_package()->package_id;
        $employer->user_package_id = Helper::current_user_package()->user_package_id;
        
        //dd($job_package);
        $employer->job_exp_date = $job_package->expiry_date;
        $employer->created_at = date("Y-m-d H:i:s");
        $employer->updated_at = date("Y-m-d H:i:s");
        $employer->parent_id = $user_id;
        //print_r($user);exit();
        if(isset($request->cv_search))
        { 
            
                $employer->cv_search_limit=empty($request->cv_limit)?0:$request->cv_limit;
                $employer->profile_view_limit=empty($request->view_limit)?0:$request->view_limit;
                $employer->send_mail_limit=empty($request->mail_limit)?0:$request->mail_limit;
        }
        if(isset($request->job_post))
        {
                    $employer->job_post_limit=$request->job_limit;
        }
        if(isset($request->job_regular))
        {
            $employer->regular_job_access=$request->job_regular;
        }
        if(isset($request->job_enterprise))
        {
            $employer->enterprise_job_access=$request->job_enterprise;
        }
        $employer->save();
        $id = $employer->employer_id;

        $user = new User;
        $user->name = $first_name;
        $user->email = $email_id;
        $user->mobile = $mobile;
        $user->role = '3';
        $user->password = Hash::make($password);
        $user->link_expiry = date("Y-m-d H:i:s");
        $user->created_at = date("Y-m-d H:i:s");
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        $user_id = $user->id;
        Employer::where('employer_id',$id)->update(['password'=>$password,'user_id_fk'=>$user_id]);
        if(!empty($user_id)){
           $encrypted = Crypt::encryptString($email_id);
            $mail_data = array(
                            'email' => $email_id,
                            'link' => $encrypted,
                         );
            Mail::send('email.verify', $mail_data, function ($message) use ($mail_data) {
                    $message->subject('Email Verification')
                            ->from('developer10@indglobal-consulting.com')
                            ->to($mail_data['email']);
            });
            echo 3;die();
        }else{
            echo 4;die();
        }
    }

    public function employer_user_details(Request $request){
        $employer_id = $request->id;
        $userdata = Employer::where('employer_id',$employer_id)->first();
        // $response =  array("status" => 1,
        //      "uservals" =>  $userdata[0]);
        // print_r($userdata->email);die();
        $data['status']=1;
        $data['view']=view('employer.new_user_modal',compact('userdata'))->render();
        echo json_encode($data); die();
    }

    public function employer_user_update(Request $request){
        $first_name = $request->uname;
        $designation = $request->design;
        $email_id = $request->email_id;
        $mobile = $request->contno;
        $limitvalue = $request->limit;
        $employer_id=$request->employer_id;

        $employer=Employer::where('employer_id',$request->employer_id)->first();

        $check_email = User::where('id','<>',$employer->user_id_fk)->where('email',$email_id)->first();
        $check_employer_email = Employer::where('email',$email_id)->where('employer_id','<>',$request->employer_id)->first();
        if(!empty($check_email)){
            echo 2;die();
        }
        if(!empty($check_employer_email)){
            echo 3;die();
        }
        $check_mobile = User::where('id','<>',$employer->user_id_fk)->where('mobile',$mobile)->first();
        if(!empty($check_mobile)){
            echo 4;die();
        }
        // Employer::where('employer_id',$request->employer_id)->update(['first_name'=>$first_name,'designation'=>$designation,'email'=>$email,'mobile'=>$contno,'limitvalue'=>$limitvalue]);
        $employer=Employer::where('employer_id',$request->employer_id)->first();
        //start - default values insert - setup for local dev environment
        
        $total_cv_search=Helper::cv_search_access();
        $total_profile_views=Helper::profile_view_access();
        $total_send_mails=Helper::send_mail_access();
        $cv_searched=count(Helper::total_cv_searched());
        $profile_views=count(Helper::total_profile_viewed());
        $send_mails=count(Helper::total_send_mailed());
        if(isset($request->cv_search))
        {
            if(!empty($request->cv_limit) && ($total_cv_search-$cv_searched) < $request->cv_limit)
            {
                echo 12;die();
            }
            if(!empty($request->view_limit) && ($total_profile_views-$profile_views) < $request->view_limit)
            {
                echo 13;die();
            }
            if(!empty($request->mail_limit) && ($total_send_mails-$send_mails) < $request->mail_limit)
            {
                echo 14;die();
            }
        }

        if($request->cv_limit1!=$employer->cv_search_limit || $request->view_limit1!=$employer->profile_view_limit || $request->mail_limit1!=$employer->send_mail_limit)
        {
            if(isset($request->cv_search1))
            {
                
                if(!empty($request->cv_limit1) && ($total_cv_search-$cv_searched+$employer->cv_search_limit) < $request->cv_limit1)
                {
                    echo 12;die();
                }
                if(!empty($request->view_limit1) && ($total_profile_views-$profile_views+$employer->profile_view_limit) < $request->view_limit1)
                {
                    echo 13;die();
                }
                if(!empty($request->mail_limit1) && ($total_send_mails-$send_mails+$employer->send_mail_limit) < $request->mail_limit1)
                {
                    echo 14;die();
                }
            }
        }

        $total_job_posts = 0;
        if(Helper::job_post_access() > 0)
        {
            $total_job_posts=Helper::job_post_access();
        }
        $job_posted=count(Helper::total_job_posted());
        if($request->job_limit1!=$employer->job_post_limit)
        {
            if(isset($request->job_post1))
            {

                if(($total_job_posts-$job_posted+$employer->job_post_limit) < $request->job_limit1)
                {
                    echo 11;die();
                }
            }
        }
        $user = User::where('id',$employer->user_id_fk)->first();
        $user->email=$email_id;
        $user->save();
        $employer->first_name = $first_name;
        $employer->designation = $designation;
        $employer->mobile = $mobile;
        $employer->email = $email_id;
        //print_r($user);exit();
        $cv_limit=$employer->cv_search_limit;
        $view_limit=$employer->profile_view_limit;
        $mail_limit=$employer->send_mail_limit;
        $job_post=$employer->job_post_limit;
        
        if(!empty(Helper::current_cv_package())){
        	$cv_package=User_package::where('user_id_fk',Auth::user()->id)->where('package_id_fk',Helper::current_cv_package()->package_id)->first();
        	$employer->cv_package_id = Helper::current_cv_package()->package_id;
        	$employer->cv_exp_date = $cv_package->expiry_date;
        }
        $job_package=User_package::where('user_id_fk',Auth::user()->id)->where('package_id_fk',Helper::current_job_package()->package_id)->where('user_package_id',Helper::current_user_package()->user_package_id)->first();
         
        $employer->job_exp_date = $job_package->expiry_date;
        
        //print_r($employer->job_exp_date);exit();
       
        $employer->job_package_id = Helper::current_job_package()->package_id;
        $employer->user_package_id = Helper::current_user_package()->user_package_id;
        if(isset($request->cv_search1))
        { 
            if($request->cv_limit1!=$employer->cv_search_limit  )
                $employer->cv_search_limit=$request->cv_limit1;
            if( $request->view_limit1!=$employer->profile_view_limit)
                $employer->profile_view_limit=$request->view_limit1;
            if($request->mail_limit1!=$employer->send_mail_limit)
                $employer->send_mail_limit=$request->mail_limit1;
        }
        else
        {
            $employer->cv_search_limit=0;
            $employer->profile_view_limit=0;
            $employer->send_mail_limit=0;
        }
        if(isset($request->job_post1))
        {
                    $employer->job_post_limit=$request->job_limit1;
        }
        else
        {
            $employer->job_post_limit=0;
        }
        if(isset($request->job_regular1))
        {
            $employer->regular_job_access=$request->job_regular1;
        }
        else{
            $employer->regular_job_access=0;
        }
        if(isset($request->job_enterprise1))
        {
            $employer->enterprise_job_access=$request->job_enterprise1;
        }
        else{
            $employer->enterprise_job_access=0;
        }
        $employer->save();

        $employer=Employer::where('user_id_fk',Auth::user()->id)->first();
        if($request->cv_limit1!=$cv_limit)
        {
            $employer->assigned_cv_search=($employer->assigned_cv_search-$cv_limit)+$request->cv_limit1;
        }
        if($request->view_limit1!=$view_limit)
        {
            $employer->assigned_profile_views=($employer->assigned_profile_views-$view_limit)+$request->view_limit1;
        }
        if($request->mail_limit1!=$mail_limit)
        {
            $employer->assigned_send_mails=($employer->assigned_send_mails-$mail_limit)+$request->mail_limit1;
        }
        if($request->job_limit1!=$job_post)
        {
            $employer->assigned_job_post=($employer->assigned_job_post-$job_post)+$request->job_limit1;
        }
        $employer->save();
        // echo $employer->assigned_job_post;
        // echo $job_post;
        // echo $request->job_limit1;
        // echo ($employer->assigned_job_post-$job_post)+$request->job_limit1;
        echo 1;die();
    }

    public function employer_user_delete(Request $request){
        //print_r($request->all());exit();
        $emp = Employer::where('employer_id',$request->employer_id)->first();
        $job_post = Job_post::where('jp_type','<>','1')->where('user_id_fk',$emp->user_id_fk)->where('package_id',$emp->job_package_id)->where('type',1)->get();
        $cv=Cv_downloads::where('user_id_fk',$emp->user_id_fk)->where('package_id',$emp->job_package_id)->get();
        $profile_view=Profile_views::where('user_id_fk',$emp->user_id_fk)->where('package_id',$emp->job_package_id)->get();
        $email_sent=Emails_sent::where('user_id_fk',$emp->user_id_fk)->where('package_id',$emp->job_package_id)->get();
        //print_r($emp->job_post_limit);exit();
        //echo $emp->job_post_limit."<".count($job_post)."&&".$emp->cv_search_limit."<".count($cv)."&&".$emp->profile_view_limit."<".count($profile_view)."&&".$emp->send_mail_limit."<".count($email_sent);
        //exit();
        if($emp->job_post_limit <= count($job_post) && $emp->cv_search_limit <= count($cv) && $emp->profile_view_limit <= count($profile_view) && $emp->send_mail_limit <= count($email_sent) )
        {
            Employer::where('employer_id',$request->employer_id)->update(['activate'=>3]);
            echo 1;die();
        }else{
          echo 2;die();
        }
        
        //
        //User::where('id',$request->user_id_fk)->update(['enabled'=>2]);
        
    }
    public function search_bulkcv(){
         $results = [];
         $is_searched = 1;
         return view('employer/search_bulkcv',compact('results','is_searched'));
    }
    public function bulkcv_filter(Request $request){
        //print_r($request->keywords); exit();
         $keywords = $request->keywords;
         $cvs = Bulk_Cvs::all();
         $is_searched = 2;
         foreach($cvs as $cv){
            $check = Docs::where('bulk_cv_id',$cv->cv_id)->get();
        
            if(count($check) == 0){

                
            // public_path
            $url=public_path($cv->cv_path);
            // $url = $cv->cv_path;
            // $url="http://phplaravel-177908-517350.cloudwaysapps.com/".$cv->cv_path;
            
            $fileArray = pathinfo($url);
            $file_ext  = $fileArray['extension'];
            if($file_ext == "doc" || $file_ext == "docx")
            {
                $docObj = new FiletoText($url);
                $return = $docObj->convertToText();
                $return = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$return);
            } 
            else if( $file_ext == "pdf" ){
            $parser = new \Smalot\PdfParser\Parser();
              $pdf    = $parser->parseFile($url);
              $text = $pdf->getText();
              $return=$text;
                $return = str_replace("  "," ",$return);
                $return = str_replace("\t","",$return);
                $return = str_replace("\n","",$return);
            } 
            else {
                 $return="";
            }
            /*$fileHandle = fopen($url, "rb");
            // print_r($fileHandle);
            $line = @fread($fileHandle, filesize($url));
            $lines = explode(chr(0x0D),$line);
            $outtext = "";
            foreach($lines as $thisline)
              {
                $pos = strpos($thisline, chr(0x00));
                if ((strlen($thisline)==0))
                  {
                  } else {
                    $outtext .= $thisline." ";
                  }
              }*/
              // print_r($return);
         // $return = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
                $docs = new Docs();
                $docs->filename = $cv->cv_path;
                $docs->bulk_cv_id = $cv->cv_id;
                $docs->file_contents = $return;
                $docs->save();
            }
            
         }
         $cvs = Job_seeker_cv::all();
         foreach($cvs as $cv){
            $check = Docs::where('user_cv_id',$cv->js_cv_id)->get();
        
            if(count($check) == 0){

                
            // public_path
            $url=public_path($cv->cv);
            // $url = $cv->cv_path;
            // $url="http://phplaravel-177908-517350.cloudwaysapps.com/".$cv->cv_path;
            
            $fileArray = pathinfo($url);
            $file_ext  = $fileArray['extension'];
            if($file_ext == "doc" || $file_ext == "docx")
            {
                $docObj = new FiletoText($url);
                $return = $docObj->convertToText();
                $return = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$return);
            } 
            else if( $file_ext == "pdf" ){
            $parser = new \Smalot\PdfParser\Parser();
              $pdf    = $parser->parseFile($url);
         // dd($pdf->getPages());
              $text="";
              try {
                    $text = $pdf->getText();
                } 
                catch (\Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                }
              $return=$text;
                $return = str_replace("  "," ",$return);
                $return = str_replace("\t","",$return);
                $return = str_replace("\n","",$return);
            } 
            else {
                 $return="";
            }
                $docs = new Docs();
                $docs->filename = $cv->cv;
                $docs->user_cv_id = $cv->js_cv_id;
                $docs->file_contents = $return;
                $docs->save();
            }
            
         }
         $rawQry = "MATCH (contents) AGAINST ('".$keywords."' IN NATURAL LANGUAGE MODE)";
         //$results = Docs::whereRaw($rawQry)->get();
         $results=array();
         $rr=Docs::select('file_contents')->pluck('file_contents')->toArray();
         // $rr=Docs::select('file_contents')->get();
         // dd($rr);
         if(!empty($keywords))
         {
            $results = Docs::where('file_contents', 'LIKE', "%".$keywords."%")->get();
            }
        // dd($results);die;
         return view('employer/search_bulkcv',compact('results','is_searched'));
    }
}