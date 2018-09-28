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
use App\Model\Profile_views;
use Carbon\Carbon;
use App\Model\Countries;
use App\Model\States;
use App\Model\Cities;
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
use App\Model\Cv_downloads;
use App\Model\Chart_data;
use App\Model\Employer;
use App\Model\Block_company;
use App\Model\Faqs;
use App\Model\Question_Answers;
use App\Model\Referal_Requests;
use App\Model\Job_post;
use App\Model\Job_seeker_personal_details;
use App\Model\Emails_sent;
use App\Model\Application_re_reply;
use ReCaptcha\ReCaptcha;
use DateTime;
use DatePeriod;
use DateInterval;
class EmployeeController extends Controller{
		
	public function index()
	{
		echo "welcome";
	}
	public function employee_store(Request $request)
    {
        $response = $request['g-recaptcha-response'];
        $remoteip = $_SERVER['REMOTE_ADDR'];
        $secret   = env('RE_CAP_SECRET');
        $recaptcha = new ReCaptcha($secret);
        $resp = $recaptcha->verify($response, $remoteip);
            //print_r($resp->isSuccess());exit();
        if ($resp->isSuccess()) {
            } else {
                        echo 5;die();
                    }
        //print_r($request->all());exit();
        $pwd_validate = Validator::make($request->all(),['reg_password' => 'required|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/']);
        $file_validator ="";
        if($request->hasFile('cv_upload')){
            $file_validator = Validator::make($request->all(), ['cv_upload' => 'max:2048|mimes:doc,pdf,docx',]);
            
            if ($file_validator->fails()){
            echo 10;die();
            }
        }
        
        if ($pwd_validate->fails()){
            echo 4;die();
        }
        
    	
    	$name = $request->reg_firstname;
        $lname = $request->reg_lastname;
        $email = $request->reg_email_id;
    	$password = $request->reg_password;
        $mobile = $request->mobile;
        $nationality = $request->nationality;
        $location = $request->location;
        $otp = $request->otp;
    	$check_email = User::where('email',$email)->first();
    	if(!empty($check_email)){
    		echo 3;die();
    	}
        if( $otp != Session::get('otp') )
            {
                echo 6;die();
            }

    	$user = new User;
    	$user->name = $name;
    	$user->email = $email;
    	$user->role = '2';
    	$user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->link_expiry = date("Y-m-d H:i:s");
    	$user->created_at = date("Y-m-d H:i:s");
    	$user->updated_at = date("Y-m-d H:i:s");
    	$user->save();
    	$id = $user->id;
    	if(!empty($id)){
            if($request->hasFile('cv_upload'))
            {
                $destination = public_path('/uploads/resumes');  
                $relativepath = '/uploads/resumes';  
                $file = $request->file('cv_upload');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
                
                $user_id = $id;
                $cov = new Cv();
                $cov->user_id_fk = $user_id;
                $cov->created_at = date("Y-m-d H:i:s");
                $cov->updated_at = date("Y-m-d H:i:s");
                $cov->cv = $filelocation;
                $cov->cv_title = "";
                $cov->cv_text = "";
                $cov->save();

            }
                $pdtls = new Personal_details();
                $pdtls->user_id_fk = $user->id;
                $pdtls->first_name = $name;
                $pdtls->last_name = $lname;
                $pdtls->nationality = $nationality;
                $pdtls->current_location = $location;
                $pdtls->country_code = $request->country_code;
                $pdtls->mobile_number = $mobile;
                $pdtls->email_id = $email;
                $pdtls->total_exp = $request->exp;
                $pdtls->exp_type = $request->exp_type;
                $pdtls->otp = $otp;
                $pdtls->created_at = date("Y-m-d H:i:s");
                $pdtls->updated_at = date("Y-m-d H:i:s");
                $pdtls->save();

            $encrypted = Crypt::encryptString($email);
            $mail_data = array(
                            'email' => $email,
                            'link' => $encrypted,
                         );
            Mail::send('email.verify', $mail_data, function ($message) use ($mail_data) {
                    $message->subject('Email Verification')
                            ->from('developer10@indglobal-consulting.com')
                            ->to($mail_data['email']);
            });
    		echo 1;die();
    	}else{
    		echo 2;die();
    	}
    
    }
    public function verify(){
        $req = request()->segment(2);
        $decrypted = Crypt::decryptString($req);
        $check = User::where('email',$decrypted)->first();
        if(empty($check))
        {
            return view('link_expire');
        }
        //print_r($decrypted );exit();
        $link_time=date('Y-m-d H:i:s',strtotime($check->link_expiry.'+ 1 day'));
        $now = date('Y-m-d H:i:s');
        //print_r($now ."----". $link_time."--------".$data->link_expiry);exit();
        if( $now <= $link_time )
        {
            if(!empty($check)){
            User::where('email',$decrypted)->update(['email_verify'=>'2']);
            }
            return redirect('/')->with('verify-msg','message');
        }else{
            return view('link_expire');
        }
        
    }
    public function employee_login(Request $request){
    	 $password = $request->password;
         $email = $request->email_id;
         $login = User::where('email',$email)->first();
         //print_r($request->all());
         if(!empty($login)){
                if($login->email_verify != '2'){
                    echo 7;die();
                }
                if($login->enabled != '1'){
                    echo 8;die();
                }
                if (Hash::check($password, $login->password)) {
                     if(Auth::attempt(['email'=>$email,'password'=>$password,'role'=>2])){
                        $id = Auth::user()->id;
                        Last_login::where('user_id_fk',$id)->update(['status'=>'2']);
                        $last = new Last_login;
                        $last->user_id_fk = $id;
                        $last->login_time = date("Y-m-d H:i:s");
                        $last->status = '1';
                        $last->created_at = date("Y-m-d H:i:s");
                        $last->updated_at = date("Y-m-d H:i:s");
                        $last->logout_time = date("Y-m-d H:i:s");
                        $last->server_ip = $_SERVER["REMOTE_ADDR"];
                        $last->system_ip = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));
                        $last->save();
                       return Auth::user(); die();
                        
                    }
                    else{
                      
                        echo 3;die();
                    }
             }else{
                echo 3;die();
             } 
         }else{
            echo 4;die();
         }
    }
    public static function profile_meter(){
        if(empty(Auth::user()))
        {
            return array();
        }
        $emp_id = Auth::user()->id;
        $user = User::where('id',$emp_id)->first();
        $js = User::where('id',$emp_id)->first();
        $personal = Personal_details::select('first_name','last_name','gender','dob','marital_status','nationality','current_location','mobile_number','landline','email_id','alternative_email_id','current_mailing_address','country_id','state_id','city_id','zip','driving_liicence','current_visa_status','visa_valid_upto','notice_period','known_languages')->where('user_id_fk',$emp_id)->first()->toArray();
        // dd(sizeof($personal));
        $total_personal=sizeof($personal);
        $i=0;
        foreach ($personal as $key => $value) {
            if(empty($value))
            {
                $i++;
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
        // echo json_encode($user->seminar_detail);
         $seminar = Seminar_details::select('seminar_name','year')->where('user_id_fk',$emp_id)->get()->toArray();
        // die;
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
        echo json_encode($data);die;
    }
    public function dashboard(){
        // $this->profile_meter();
    	$emp_id = Auth::user()->id;
        $user = User::where('id',$emp_id)->first();
        $js = User::where('id',$emp_id)->first();
        $personal = Personal_details::where('user_id_fk',$emp_id)->first();
        //print_r($academic);exit();$
        $max = Academic_details::where('user_id_fk',$emp_id)->max('year_of_passing');
        
        $highest_academic = Academic_details::where('user_id_fk',$emp_id)->where('year_of_passing',$max)->first();

        $academic = Academic_details::where('user_id_fk',$emp_id)->where('year_of_passing','!=',$max)->get();
        //$academic = Academic_details::where('user_id_fk',$emp_id)->where('qualification_type','2')->get();
        //$highest_academic = Academic_details::where('user_id_fk',$emp_id)->where('qualification_type','1')->first();
        $current_company = Career_history::where('user_id_fk',$emp_id)->where('current_company','2')->first();
        $career = Career_history::where('user_id_fk',$emp_id)->where('current_company','1')->get();
        $app_last = Applied_job::where('user_id_fk',$emp_id)->where('created_at', '>=', Carbon::now()->subMonth())->get();
        $rec_last = Profile_views::where('job_seeker_id',$emp_id)->where('created_at', '>=', Carbon::now()->subMonth())->groupBy('user_id_fk')->get();
        $view_last = Profile_views::where('job_seeker_id',$emp_id)->where('created_at', '>=', Carbon::now()->subMonth())->sum('total');
        // print_r($view_last);die;
        $t_count = count($app_last);
        $r_count = count($rec_last);
        $v_count = $view_last;
        $cv = Cv::where('user_id_fk',$emp_id)->get();
        //print_r($current_company);exit();
    	return view('employee.employee_dashboard',compact('user','personal','academic','highest_academic','current_company','career','t_count','r_count','v_count','cv'));
    }
    public function academic_edit(){
    	$emp_id = Auth::user()->id;
        $country = Countries::all();
    	$academic = Academic_details::where('user_id_fk',$emp_id)->orderBy('year_of_passing','desc')->get();
    	$highest = Academic_details::where('user_id_fk',$emp_id)->where('year_of_passing','max(year_of_passing)')->first();
        
    	return view('employee.academic_edit',compact('academic','highest','country'));
    }
    public function personal_edit(){
    	$emp_id = Auth::user()->id;
    	//$emp_id = "3";
        $country = Countries::all();
        $language = Language::orderBy('name','ASC')->get();
    	$personal = Personal_details::where('user_id_fk',$emp_id)->first();
        //echo $personal->state_id; exit();
        if(!empty($personal)){
            $states = States::where('country_id',$personal->country_id)->get();
            $cities = Cities::where('state_id',$personal->state_id)->get();
        }
        else{
             $states = [];
             $cities = [];
        }
        return view('employee.personal_edit',compact('personal','country','language','states','cities'));
    }
    public function career_edit(){
        $user_id = Auth::user()->id;
        $country = Countries::all();
        $career = Career_history::where('user_id_fk',$user_id)->orderBy('current_company','desc')->get();
        return view('employee.career_history',compact('career','country'));
    }
    public function logout(){
        if(!Auth::user())
            {
                return redirect('/');
            }
         $id = Auth::user()->id;
         $log_time = date("Y-m-d H:i:s");
        Last_login::where('user_id_fk',$id)->where('status','1')->update(['status'=>'2','logout_time'=>$log_time,'updated_at'=>$log_time]);
         /*$last_login = Last_login::where('user_id_fk',$id)->where('status','1')->first();
         $last_login->status = "2";
         $last_login->logout_time = date("Y-m-d H:i:s");
         $last_login->updated_at = date("Y-m-d H:i:s");
         $last_login->save();*/

         Auth::logout();
        return redirect('/')->with('logout-msg','message');
    }
    public function personal_update(Request $request){
       // print_r($request->all());exit();
        $id = Auth::user()->id;
        $personal = Personal_details::where('user_id_fk',$id)->first();
        if(empty($personal)){
            $user = new Personal_details;
            $user->created_at = date("Y-m-d H:i:s");
        }else{
            $user = $personal;
        }
        $user->user_id_fk = $id;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->gender = $request->optionsRadios;
        $user->dob = date("Y-m-d",strtotime($request->dob));
        $user->marital_status = $request->optionsRadios2;
        $user->nationality = $request->nationality;
        $user->current_location = $request->current_location;
        $user->country_code = $request->country_code;
        $user->mobile_number = $request->mobile;
        User::where('id',Auth::user()->id)->update(['mobile'=>$request->mobile]);
        //if(!empty($request->landline)){
            $user->landline = $request->landline;
        
        $user->email_id = $request->email;
        $user->alternative_email_id = $request->alternate_email;
        $user->current_mailing_address = $request->current_email;
        $user->country_id = $request->country;
        $user->state_id = $request->state;
        $user->city_id = $request->city;
        $user->zip = $request->zip;
        $user->total_exp = $request->total_exp;
        $user->exp_type = $request->exp_type;
        $user->driving_liicence = $request->optionsRadios3;
        $user->vtype = $request->vehicle_type;
        if(!empty($request->current_visa) && $request->current_visa == "Other" ){
            $user->current_visa_status = $request->current_visa;
            $user->other_visa_status = $request->other_visa;
        }elseif(!empty($request->current_visa)){
            $user->current_visa_status = $request->current_visa;
            $user->other_visa_status = NULL;
        }elseif(empty($request->current_visa)){
            $user->current_visa_status = NULL;
        }
        if(!empty($request->valid_upto)){
            $user->visa_valid_upto = $request->valid_upto;
        }
        if(!empty($request->notice_period)){
            $user->notice_period = $request->notice_period;
        }
        $lang[] = $request->language1;
        $lang[] = $request->language2;
        $lang[] = $request->language3;
        $lang[] = $request->language4;
        $lang[] = $request->language5;
        $lang[] = $request->language6;
        $new_arr = array_values($lang);
        $language = implode(",", $new_arr);
        //print_r($language);

        $user->known_languages = $language;
        
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        $id = $user->id;
        $fname = $request->first_name;
        User::where('id',Auth::user()->id)->update(['name'=>$fname]);                                                             
        if(!empty($id)){
            echo 1;die();
        }else{
            echo 2;die();
        }
    }
    public function job_preference_edit(){
        $id = Auth::user()->id;
        $country = Countries::all();
        $job = Job_preference::where('user_id_fk',$id)->first();
        // dd($job);
        $industry = IndustryType::orderBy('industry_type_name')->get();
        return view('employee.job_preference_edit',compact('country','job','industry'));
    }
    public function preference_update(Request $request){
        
        $ids = Auth::user()->id;
        $personal = Job_preference::where('user_id_fk',$ids)->first();
        if(empty($personal)){
            $user = new Job_preference;
            $user->created_at = date("Y-m-d H:i:s");
        }else{
            $user = $personal;
        }
        $user->user_id_fk = $ids;
        $user->preferred_job_title = $request->title;
        // if(!empty($request->country)){
        //     $user->country_id = $request->country;
        // }
        
        // if(!empty($request->state)){
        //     $user->state_id = $request->state;
        // }
        
        // $lang[] = $request->location1;
        // $lang[] = $request->location2;
        // $lang[] = $request->location3;
        // if(!empty($lang))
        // {
        //      foreach ($lang as $key=>$val) {
        //         if (empty($val))
        //            unset($lang[$key]);
        //     }

        //     $new_arr = array_values($lang);
        //     $language = implode(",", $new_arr);
        //     $user->preferred_job_location =$language; 
        // }
        $loc = $request->job_location;
        foreach ($loc as $l) {
            $loca[] = $l;
                   }
                   if(!empty($loca)){
                    $lll = implode("|", $loca);
                     $user->preferred_job_location =$lll; 
                   }
        if(!empty($request->job_function)){
            $user->preferred_job_function  = $request->job_function;
        }
        if(!empty($request->industry)){
            $user->preferred_industry_type = $request->industry;
        }
        if(!empty($request->job_type)){
            $user->preferred_job_type = $request->job_type;
        }
        $currency ="";
            if($request->currency == 1) {
                 $currency = "$";
            }elseif($request->currency == 2){
                $currency = "₹";
            }elseif($request->currency == 3){
                $currency = "AED";
            }elseif($request->currency == 4){
                $currency = "€";
            }elseif($request->currency == 5){
                $currency = "£";
            }

        if(!empty($request->min_salary)){

            $sal = $currency.' '.$request->min_salary .'-'.$request->max_salary;
            $user->preferred_monthly_salary = $sal;
        }
        if(!empty($request->currency)){
            $user->currency_type = $request->currency;
        }
        if(!empty($request->min_salary)){
            $user->min_sal = $request->min_salary;
        }
        if(!empty($request->min_salary)){
            $user->max_sal = $request->max_salary;
        }
        
        
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        $id = $user->js_job_preference_id;
        if(!empty($id)){
            echo 2;die();
        }else{
            echo 1;die();
        }

    }
    public function keyskills_edit(){
        $user_id = Auth::user()->id;
        $keyskills = Job_seeker_technical_skills::where('user_id_fk',$user_id)->get();
        return view('employee.keyskills_edit',compact('keyskills'));
    }
    public function skills_update(Request $request){
        $user_id = Auth::user()->id;
        $skill = $request->skill;
        $level = $request->level;
        $yoe = $request->yoe;
        $last_used = $request->last_used;
        $key = Job_seeker_technical_skills::where('skill',$skill)->where('level_of_expertise','level')->where('years_of_experience',$yoe)->where('year_last_used',$last_used)->first();
        if(!empty($key)){
            $data['skills'] = $key;
        }else{
            $sk = new Job_seeker_technical_skills;
            $sk->user_id_fk = $user_id;
            $sk->skill = $request->skill;
            $sk->level_of_expertise = $request->level;
            $sk->years_of_experience = $request->yoe;
            $sk->year_last_used = $request->last_used;
            $sk->created_at = date("Y-m-d H:i:s");
            $sk->updated_at = date("Y-m-d H:i:s");
            $sk->save();
            $id = $sk->js_skills_id;
            $data['skills'] = Job_seeker_technical_skills::where('js_skills_id',$id)->first();
        }
        echo json_encode($data);die();
    }
    public function certificate_edit(){
        $user_id = Auth::user()->id;
        $certificates = Job_seeker_certificate::where('user_id_fk',$user_id)->get();
       
        return view('employee.certificate_edit',compact('certificates'));
    }
    public function certificate_update(Request $request){
       // print_r($request->all());exit();
        $user_id = Auth::user()->id;
        $id = $request->certificate_no;
        $certname = $request->certificate;
        $certiby = $request->issued_by;
        $validfrom = $request->valid_from;
        $validto = $request->valid_to;
        if(!empty($request->unlimited)){
            $unlimited = $request->unlimited;
        }else{
            $unlimited = array();
        }
        
        $other = $request->other;
        if(count(array_filter($validfrom)) != count($validfrom)) {
            echo 3;die();
        }

        /*if(count(array_filter($validfrom)) != count($validfrom)) {
            echo 3;die();
        }*/

        for($i=0;$i<count($certname);$i++){
             if($id[$i] == ""){
                $certificate = new Job_seeker_certificate;
                $certificate->user_id_fk =  $user_id;
                if(!empty($certname[$i])){
                    $certificate->certificate_name = $certname[$i];
                }
                if(!empty($certiby[$i])){
                    $certificate->issued_by = $certiby[$i];
                }

                if(in_array($request->iteration[$i],$unlimited))
                { 
                        if(!empty($validfrom[$i])){
                        $certificate->valid_from = date('Y-m-d',strtotime($validfrom[$i]));
                        
                        }else{
                            echo 4;die();
                        }
                    
                }else{
                    
                        if(!empty($validfrom[$i]) && !empty($validto[$i]) && ( date('Y-m-d',strtotime($validfrom[$i])) <= date('Y-m-d',strtotime($validto[$i])) ) )
                        {
                           $certificate->valid_from = date('Y-m-d',strtotime($validfrom[$i]));
                            $certificate->valid_to = date('Y-m-d',strtotime($validto[$i]));
                        }else{
                            echo 5;die();
                        }
                    
                }
                
                if(in_array($request->iteration[$i],$unlimited)){
                    $certificate->unlimited_validity = "2";
                }else{
                    $certificate->unlimited_validity = "1";
                }
                if(!empty($other[$i])){
                    $certificate->other = $other[$i];
                }
                $certificate->created_at = date("Y-m-d H:i:s");
                $certificate->updated_at = date("Y-m-d H:i:s");
                $certificate->save();
             }
             else{
                $certificate = Job_seeker_certificate::find($id[$i]);
                $certificate->js_certificate_id = $id[$i];
                if(!empty($certname[$i])){
                    $certificate->certificate_name = $certname[$i];
                }
                if(!empty($certiby[$i])){
                    $certificate->issued_by = $certiby[$i];
                }
             if(in_array($request->iteration[$i],$unlimited))
                    {
                        if(!empty($validfrom[$i])){
                        $certificate->valid_from = date('Y-m-d',strtotime($validfrom[$i]));
                        
                        }else{
                            echo 4;die();
                        }
                    
                }else{
                    
                        if(!empty($validfrom[$i]) && !empty($validto[$i]) && ( date('Y-m-d',strtotime($validfrom[$i])) <= date('Y-m-d',strtotime($validto[$i])) ) )
                        {
                           $certificate->valid_from = date('Y-m-d',strtotime($validfrom[$i]));
                            $certificate->valid_to = date('Y-m-d',strtotime($validto[$i]));
                        }else{
                            echo 5;die();
                        }
                    
                }
                /*if(!empty($validfrom[$i])){
                    $certificate->valid_from = $validfrom[$i];
                }
                if(!empty($validto[$i])){
                    $certificate->valid_to = $validto[$i];
                }*/
                /*if(!empty($unlimited[$i])){
                    $certificate->unlimited_validity = $unlimited[$i];
                }*/
                if(in_array($request->iteration[$i],$unlimited)){
                    $certificate->unlimited_validity = "2";
                }else{
                    $certificate->unlimited_validity = "1";
                }
                if(!empty($other[$i])){
                    $certificate->other = $other[$i];
                }
                $certificate->updated_at = date("Y-m-d H:i:s");
                $certificate->save();
             }
        }
        if($certificate)
        {
          echo 1;die();
        }else{
          echo 2;die();
        }
       
    }
    public function seminar_edit(){
        $user_id = Auth::user()->id;
        $seminar = Seminar_details::where('user_id_fk',$user_id)->get();
        return view('employee.seminar',compact('seminar'));
    }
    public function project_edit(){
        $user_id = Auth::user()->id;
        $project = Project::where('user_id_fk',$user_id)->get();
        return view('employee.project_edit',compact('project'));
    }
    public function cv_edit(){
        $user=Auth::user();
        $user_id = Auth::user()->id;
        $cv = Cv::where('user_id_fk',$user_id)->get();
        return view('employee.cv_edit',compact('cv','user'));
    }
    public function cover_edit(){
        $user_id = Auth::user()->id;
        $cover = Cover_letter::where('user_id_fk',$user_id)->first();
        return view('employee.cover_letter',compact('cover'));
    }
    public function cover_update(Request $request){
        //print_r($request->all());exit();
        $user_id = Auth::user()->id;
        $cover = Cover_letter::where('user_id_fk',$user_id)->first();
        if(!empty($cover)){
            $cov = $cover;
        }else{
            $cov = new Cover_letter;
            $cov->created_at = date("Y-m-d H:i:s");
        }
        $cov->user_id_fk = $user_id;
        $cov->cover_letter_name = $request->cover_letter;
        $cov->cover_letter = $request->cover;
        /*if($request->hasFile('cover_file'))
            {
                $destination = public_path('/uploads/resumes');  
                $relativepath = '/uploads/resumes';  
                $file = $request->file('cover_file');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
                
                $cov->file = $filelocation;
            }else{
                 echo 3;die();
            }*/
        
        $cov->updated_at = date("Y-m-d H:i:s");
        $cov->save();
        $id = $cov->js_cover_id;
        if(!empty($id)){
            echo 2;die();
        }
        else{
            echo 1;die();
        }
    }
    public function cv_update(Request $request){
       // print_r($request->hasFile('upload_resume'));exit();
        $user_id = Auth::user()->id;
        /*$mime_validate = Validator::make($request->all(),['upload_resume.*' => 'required|mimes:doc,pdf,docx|max:4096']);
        if ($mime_validate->fails()){
           echo 3;die();
        }*/
        $mime_validate = Validator::make($request->all(),['upload_resume' => 'required|mimes:doc,pdf,docx|max:2048']);
        if ($mime_validate->fails()){
           echo 3;die();
        }
        $cover = Cv::where('user_id_fk',$user_id)->first();
        if( Cv::where('user_id_fk',$user_id)->first()){
            $cov =Cv::where('user_id_fk',$user_id)->first();
             if($request->hasFile('upload_resume'))
            {
                $cov->user_id_fk = $user_id;
                $cov->cv_title = $request->title;
                
                $destination = public_path('/uploads/resumes');  
                $relativepath = '/uploads/resumes';  
                $file = $request->file('upload_resume');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
                

                $cov->cv = $filelocation;
                if(!empty($request->cv_text)){
                    $cov->cv_text = $request->cv_text;
                }
                $cov->updated_at = date("Y-m-d H:i:s");
                $cov->save();
                 $id = $cov->js_cv_id;
            }else{
                echo 3;die();
            }
        }else{
                $files = $request->file('upload_resume');

            if($request->hasFile('upload_resume'))
            {
               // foreach ($files as $file) {
                    // print_r($file);die();
                    $cov = new Cv;
                    $cov->created_at = date("Y-m-d H:i:s");
                    $cov->user_id_fk = $user_id;
                    $cov->cv_title = $request->title;


                   $destination = public_path('/uploads/resumes');  
                $relativepath = '/uploads/resumes';  
                $file = $request->file('upload_resume');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
                

                    $cov->cv = $filelocation;


                    if(!empty($request->cv_text)){
                        $cov->cv_text = $request->cv_text;
                    }
                    $cov->updated_at = date("Y-m-d H:i:s");
                    $cov->save();
                    $id = $cov->js_cv_id;
                   // $file->store('users/' . $this->user->id . '/messages');
                    //dump($file);
                //}
            }
        }
        
        /*$user_id = Auth::user()->id;
        $cover = Cv::where('user_id_fk',$user_id)->first();
        if(!empty($cover)){
            $cov = $cover;
        }else{
            $cov = new Cv;
            $cov->created_at = date("Y-m-d H:i:s");
        }
        $cov->user_id_fk = $user_id;
        $cov->cv_title = $request->title;
         if($request->hasFile('upload_resume'))
        {
                $destination = 'uploads/resume';  
                $file = $request->file('upload_resume');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                $cov->cv = $filelocation;
               
        }
        if(!empty($request->cv_text)){
            $cov->cv_text = $request->cv_text;
        }
        $cov->updated_at = date("Y-m-d H:i:s");
         $cov->save();
        $id = $cov->js_cv_id;*/
        if(!empty($id)){
            echo 2;die();
        }
        else{
            echo 1;die();
        }
    }
    public function edit_education(Request $request){
       // print_r($request->all());exit();
        $user_id = Auth::user()->id;
        $yop = $request->m_yop;
        $qualification = $request->qualification;
        $id = $request->academic_id;
        $m_specialization = $request->m_specialization;
        $m_institute = $request->m_institute;
        $country = $request->m_country;
        $state = $request->m_state;
        $city = $request->m_city;
        if(count(array_filter($yop)) != count($yop)) {
            echo 3;die();
        } 
        if(count(array_filter($qualification)) != count($qualification)) {
            echo 4;die();
        }
        if(count(array_filter($m_specialization)) != count($m_specialization)) {
            echo 5;die();
        }
        if(count(array_filter($m_institute)) != count($m_institute)) {
            echo 6;die();
        }
        if(count(array_filter($country)) != count($country)) {
            echo 7;die();
        }
          if(count(array_filter($state)) != count($state)) {
            echo 8;die();
        }
       
         if(count(array_filter($city)) != count($city)) {
            echo 9;die();
        }
       
         for($i=0;$i<count($qualification);$i++)
        {
          if($id[$i] == "")
          {
              $academic = new Academic_details;
              $academic->user_id_fk =  $user_id;
              $academic->qualification = $qualification[$i];
              $academic->year_of_passing = $yop[$i];
              $academic->specialization =  $m_specialization[$i];
              $academic->institute_name =  $m_institute[$i];
              $academic->city_id =  $city[$i];
              $academic->state_id =  $state[$i];
              $academic->country_id =  $country[$i];
              $academic->created_at = date("Y-m-d H:i:s");
              $academic->updated_at = date("Y-m-d H:i:s");
              $academic_check = Academic_details::where('user_id_fk',$user_id)->get();
              if(count($academic_check)>0){
                $academic->qualification_type = 2;
              }
              else{
                $academic->qualification_type = 1;
              }
              $academic->save();
          }else{
              $academic = Academic_details::find($id[$i]);
              $academic->js_academic_id =  $id[$i];
              $academic->qualification = $qualification[$i];
              $academic->year_of_passing = $yop[$i];
              $academic->specialization =  $m_specialization[$i];
              $academic->institute_name =  $m_institute[$i];
              $academic->city_id =  $city[$i];
              $academic->state_id =  $state[$i];
              $academic->country_id =  $country[$i];
              $academic->updated_at = date("Y-m-d H:i:s");
              $academic->save();
          }
        }
        if($academic)
        {
          echo 1;die();
        }else{
          echo 2;die();
        }
    }
    public function delete_education(Request $request){
        $id = $request->id;
        Academic_details::where('js_academic_id',$id)->delete();
        echo 1;die();
    }
    public function update_career(Request $request){
       // print_r($request->all());exit();

        $user_id = Auth::user()->id;
        $current = $request->work_yes_no;
        $id = $request->career_id;
        $duties = $request->duties;
        $monthly_salary = $request->monthly_salary;
        $type = $request->type;
        $job_title = $request->job_title;
        $country = $request->country;
        $state = $request->state;
        $city = $request->city;
        $from_date = $request->from_date;
        $to_date=$request->to_date;
        $employer = $request->employer;
         if(count(array_filter($employer)) != count($employer)) {
            echo 4;die();
        }
        if(count(array_filter($country)) != count($country)) {
            echo 7;die();
        }
        if(count(array_filter($state)) != count($state)) {
            echo 8;die();
        }
        if(count(array_filter($city)) != count($city)) {
            echo 9;die();
        }
        if(count(array_filter($from_date)) != count($from_date)) {
            echo 10;die();
        }
        if($current == 1)
        {
            if(count(array_filter($to_date)) != count($to_date)) {
                echo 13;die();
            }
        }
        if(count(array_filter($job_title)) != count($job_title)) {
            echo 3;die();
        } 
        if(count(array_filter($monthly_salary)) != count($monthly_salary)) {
            echo 11;die();
        }
        if(count(array_filter($duties)) != count($duties)) {
            echo 12;die();
        } 
        for($i=0;$i<count($employer);$i++)
        {
            $date_array=array();
            for($j=0;$j<count($employer);$j++)
            {
               if($i!=$j)
               {
                    $period = new DatePeriod(
                    new DateTime($from_date[$j]),  //2010-10-01
                    new DateInterval('P1D'),
                    new DateTime($to_date[$j])     //2010-10-05
                    );


                    foreach ($period as $key => $value) {
                        $date_array[] = $value->format('Y-m-d');       
                    }
               } 
            }
            // print_r($date_array);
            $period = new DatePeriod(
                    new DateTime($from_date[$i]),  //2010-10-01
                    new DateInterval('P1D'),
                    new DateTime($to_date[$i])     //2010-10-05
                    );


                    foreach ($period as $key => $value) {
                        $comingDates = $value->format('Y-m-d'); 
                        $date2[] = $value->format('Y-m-d'); 
                        if(in_array($comingDates, $date_array))
                        {
                            echo 16;die;
                        }      
                    }
        }
        // print_r($date_array);die;
        for($i=0;$i<count($employer);$i++)
        {
            //print_r($request->all());exit();
          if($id[$i] == "")
          {
             if(!empty($request->from_date[$i])){
            $f_d = date("Y-m-d",strtotime($from_date[$i]));
        }else{
            $f_d = NULL;
        }
        if(!empty($request->to_date[$i])){
            //$da = $request->to_date[$i];
           $t_d = date("Y-m-d",strtotime($to_date[$i]));
        }else{
            $t_d = NULL;
        }
              $career = new Career_history;
              $career->user_id_fk =  $user_id;
              if(!empty($request->work_yes_no[$i])){
                $career->current_company = $request->work_yes_no[$i];
              }
              else{
                $career->current_company = '1';
              }
              $career->employer_name = $employer[$i];
              if(!empty($city[$i])){
                $career->city_id = $city[$i];
              }
              if(!empty($state[$i])){
                $career->state_id =  $state[$i];
              }
               if(!empty($country[$i])){
                $career->country_id =  $country[$i];
              }
                //$career->from_date =  $f_d;
                //$career->to_date =  $t_d;
              if(empty($request->work_yes_no[$i])||$request->work_yes_no[$i] ==2){
                    $career->from_date =  $f_d;
                    $career->to_date =  $t_d;
                }else{
                    if($f_d <= $t_d){
                    $career->from_date =  $f_d;
                    $career->to_date =  $t_d;
                  }else{
                    echo 14;die();
                  }
                }
              
               if(!empty($job_title[$i])){
                $career->job_title =  $job_title[$i];
              }
              if(!empty($type[$i])){
                 $career->employement_type =  $type[$i];
              }
              if(!empty($monthly_salary[$i])){
                $career->monthly_salary =  $monthly_salary[$i];
                $career->currency_type =  $request->currency_type[$i];
              }
              if(!empty($duties[$i])){
                $career->description =  $duties[$i];
              }
              
              $career->created_at = date("Y-m-d H:i:s");
              $career->updated_at = date("Y-m-d H:i:s");
              $career->save();
          }else{



             if(!empty($request->from_date[$i])){
            $f_d = date("Y-m-d",strtotime($from_date[$i]));
        }else{
            $f_d = NULL;
        }
        if(!empty($request->to_date[$i])){
            //$da = $request->to_date[$i];
           $t_d = date("Y-m-d",strtotime($to_date[$i]));
        }else{
            $t_d = NULL;
        }
            //print_r($request->all());exit();
              $career = Career_history::where('js_career_id',$id[$i])->first();
              $career->js_career_id =  $id[$i];
              $career->user_id_fk =  $user_id;
              $career->employer_name = $employer[$i];
              $career->current_company = $request->current[$i];
             if(!empty($city[$i])){
                $career->city_id = $city[$i];
              }
              if(!empty($state[$i])){
                $career->state_id =  $state[$i];
              }
               if(!empty($country[$i])){
                $career->country_id =  $country[$i];
              }
             if($request->current[$i] == "2"){
                $career->from_date =  $f_d;
            }elseif($request->current[$i] == "1"){
                if($f_d <= $t_d){
                $career->from_date =  $f_d;
                $career->to_date =  $t_d;
              }else{
                echo 14;die();
              }
            }
              
              //$career->from_date =  $f_d;
              //$career->to_date =  $t_d;
               if(!empty($job_title[$i])){
                $career->job_title =  $job_title[$i];
              }
              if(!empty($type[$i])){
                 $career->employement_type =  $type[$i];
              }
              if(!empty($monthly_salary[$i])){
                $career->monthly_salary =  $monthly_salary[$i];
              }
              if(!empty($duties[$i])){
                $career->description =  $duties[$i];
              }
              $career->updated_at = date("Y-m-d H:i:s");
              $career->save();
          }
        }
        if($career)
        {
          echo 1;die();
        }else{
          echo 2;die();
        }
    }
    public function update_seminar(Request $request){
        //print_r($request->all());exit();
        $user_id = Auth::user()->id;
        $id = $request->seminar_id;
        $seminar = $request->seminar;
        $year = $request->year;
        if(count(array_filter($seminar)) != count($seminar)) {
            echo 3;die();
        } 
        if(count(array_filter($year)) != count($year)) {
            echo 4;die();
        }
        $dtnow = Carbon::now();
        $yearnow = $dtnow->year;
       
        for($i=0;$i<count($seminar);$i++)
        {
          if($year[$i] > $yearnow){
            echo 5;die();
          }
          if($id[$i] == "")
          {
              $academic = new Seminar_details;
              $academic->user_id_fk =  $user_id;
              $academic->seminar_name = $seminar[$i];
              $academic->year = $year[$i];
              $academic->created_at = date("Y-m-d H:i:s");
              $academic->updated_at = date("Y-m-d H:i:s");
              $academic->save();
          }else{
              $academic = Seminar_details::find($id[$i]);
              $academic->js_seminar_id =  $id[$i];
                $academic->seminar_name = $seminar[$i];
              $academic->year = $year[$i];
            
              $academic->updated_at = date("Y-m-d H:i:s");
              $academic->save();
          }
        }
        if($academic)
        {
          echo 1;die();
        }else{
          echo 2;die();
        }
    }
    public function update_project(Request $request){
         $user_id = Auth::user()->id;
        $id = $request->project_id;
        $project = $request->project;
        $duration_from = $request->duration_from;
        $duration_to = $request->duration_to;
        $organization = $request->organization;
        $team_size = $request->team_size;
        $location = $request->location;
        $description = $request->description;
        $role = $request->role;
        $responsibilities = $request->responsibilities;
        $technology = $request->technology;
        $operating_system = $request->operating_system;
        if(count(array_filter($project)) != count($project)) {
            echo 3;die();
        } 
       
       
         for($i=0;$i<count($project);$i++)
        {
          if($id[$i] == "")
          {
              $academic = new Project;
              $academic->user_id_fk =  $user_id;
              $academic->project_name = $project[$i];
              /*if(!empty($duration_from[$i])){
                $academic->duration_from = $duration_from[$i];
              }*/

              if(date("Y-m-d",strtotime($duration_from[$i])) <= date("Y-m-d",strtotime($duration_to[$i])) )
              {
                if(!empty($duration_from[$i])){
                  $academic->duration_from = $duration_from[$i];
                  }
                  if(!empty($duration_to[$i])){
                    $academic->duration_to = $duration_to[$i];
                  }
              }else{
                 echo 4;die();
              }
              

              if(!empty($organization[$i])){
                $academic->organization = $organization[$i];
              }
              if(!empty($team_size[$i])){
                $academic->team_size = $team_size[$i];
              }
              if(!empty($location[$i])){
                 $academic->location = $location[$i];
              }
              if(!empty($description[$i])){
                 $academic->project_description = $description[$i];
              }
              if(!empty($role[$i])){
               $academic->role = $role[$i];
              }
              if(!empty($responsibilities[$i])){
                 $academic->responsibilities = $responsibilities[$i];
              }
              if(!empty($technology[$i])){
                $academic->technology = $technology[$i];
              }
               if(!empty($operating_system[$i])){
               $academic->operating_system = $operating_system[$i];
              }

              $academic->created_at = date("Y-m-d H:i:s");
              $academic->updated_at = date("Y-m-d H:i:s");
              $academic->save();
          }else{
              $academic = Project::find($id[$i]);
              $academic->js_project_id =  $id[$i];
                $academic->project_name = $project[$i];
              /*if(!empty($duration_from[$i])){
                $academic->duration_from = $duration_from[$i];
              }

              if(!empty($duration_from[$i])){
                $academic->duration_from = $duration_from[$i];
              }
              if(!empty($duration_to[$i])){
                $academic->duration_to = $duration_to[$i];
              }*/
              if(date("Y-m-d",strtotime($duration_from[$i])) <= date("Y-m-d",strtotime($duration_to[$i])) )
              {
                if(!empty($duration_from[$i])){
                  $academic->duration_from = $duration_from[$i];
                  }
                  if(!empty($duration_to[$i])){
                    $academic->duration_to = $duration_to[$i];
                  }
              }else{
                 echo 4;die();
              }
              if(!empty($organization[$i])){
                $academic->organization = $organization[$i];
              }
              if(!empty($team_size[$i])){
                $academic->team_size = $team_size[$i];
              }
              if(!empty($location[$i])){
                 $academic->location = $location[$i];
              }
              if(!empty($description[$i])){
                 $academic->project_description = $description[$i];
              }
              if(!empty($role[$i])){
               $academic->role = $role[$i];
              }
              if(!empty($responsibilities[$i])){
                 $academic->responsibilities = $responsibilities[$i];
              }
              if(!empty($technology[$i])){
                $academic->technology = $technology[$i];
              }
               if(!empty($operating_system[$i])){
               $academic->operating_system = $operating_system[$i];
              }

            
              $academic->updated_at = date("Y-m-d H:i:s");
              $academic->save();
          }
        }
        if($academic)
        {
          echo 1;die();
        }else{
          echo 2;die();
        }
    }
    public function delete_skills(Request $request){
        $user_id = Auth::user()->id;
        $id = $request->id;
        $jsk = Job_seeker_technical_skills::where('user_id_fk',$user_id)->where('js_skills_id',$id)->first();
        if(!empty($jsk)){
            Job_seeker_technical_skills::where('js_skills_id',$id)->delete();
        }
        echo 1;die();
    }
    public function search_keyskills(Request $request){
        $user_id = Auth::user()->id;
        $term = $request->term;
        $id = Job_seeker_technical_skills::where('skill','like','%' . $term . '%')->get();
        return json_encode($id);die();
    }
    public function facebook_login(Request $request){
     $user = $request->response;
     $auth_user = User::where('facebook_id',$user['id'])->first();
     if(User::where('email', '=',$user['email'])->count() > 0){
                 $exist_user = User::where('email', '=',$user['email'])->first();
                 $exist_user->facebook_id = $user['id'];
                 $exist_user->profile_image_facebook = $user['picture']['data']['url'];
                 $exist_user->updated_at = date("Y-m-d H:i:s");
                 $exist_user->save();

                 Auth::loginUsingId($exist_user->id, true);
     } else if(!$auth_user){
                 $users = new User;
                 $users->role = 2;
                 $users->name = $user['name'];
                 $users->email= $user['email'];
                 //$users->status = 1;
                 $users->email_verify = 2;
                 $users->facebook_id = $user['id'];
                 $users->profile_image_facebook = $user['picture']['data']['url'];
                 $users->created_at = date("Y-m-d H:i:s");
                 $users->updated_at = date("Y-m-d H:i:s");
                 $users->save();
                 
                 Auth::loginUsingId($users->id, true);
     }
     $id = Auth::user()->id;
                        Last_login::where('user_id_fk',$id)->update(['status'=>'2']);
                        $last = new Last_login;
                        $last->user_id_fk = $id;
                        $last->login_time = date("Y-m-d H:i:s");
                        $last->status = '1';
                        $last->created_at = date("Y-m-d H:i:s");
                        $last->updated_at = date("Y-m-d H:i:s");
                        $last->save();
     return Auth::user();
     //echo 1; die();
   }
   public function apply_job(Request $request){
   // print_r($request->all());exit();
        if((isset(Auth::user()->id)) && (Auth::user()->role ==2)){
            $user_id = Auth::user()->id;
            //$user_id = "26";
            $job_id = $request->id;
            $check = Applied_job::where('user_id_fk',$user_id)->where('job_id_fk',$job_id)->first();
            if(!empty($check)){
                echo 1;die();
            }else{
                $apply = new Applied_job;
                $apply->user_id_fk = $user_id;
                $apply->job_id_fk = $job_id;
                $apply->created_at = date("Y-m-d H:i:s");
                $apply->updated_at = date("Y-m-d H:i:s");
                $apply->save();
                $job_data = Job_post::where('job_id',$job_id)->first();
                if($job_data->job_response_email == 2){
                    $job_user_info = User::where('id',$job_data->user_id_fk)->first();
                    $job_url = url('job-detail/'.$job_id);
                    $user_data = User::where('id',$user_id)->first();
                    $mail_data = array(
                        'email' => $job_user_info->email,
                        'data' => $user_data,
                        'url' => $job_url
                    );
                    Mail::send('email.job_responses', $mail_data, function ($message) use ($mail_data) {
                         $message->subject('Responses for your job post')
                                 ->from('developer10@indglobal-consulting.com')
                                 ->bcc("dev85@indglobal-consulting.com")
                                 ->to($mail_data['email']);
                        });
                }
                $save_job=Saved_job::where('user_id_fk',$user_id)->where('job_id_fk',$job_id)->first();
                if(!empty($save_job))
                {
                    $save_job->active=2;
                    $save_job->save();
                }
                echo 2;die();
            }
        }
        else if((isset(Auth::user()->id)) && (Auth::user()->role ==3)){
            echo 4;die();
        }
        else{
            //print_r($request->all());
            echo 3;die();
        }
    }
	public function save_job(Request $request){
   // print_r($request->all());exit();
        if((isset(Auth::user()->id)) && (Auth::user()->role ==2)){
            $user_id = Auth::user()->id;
            //$user_id = "26";
            $job_id = $request->id;
            $check = Saved_job::where('user_id_fk',$user_id)->where('job_id_fk',$job_id)->first();
            if(!empty($check)){
                echo 1;die();
            }else{
                $apply = new Saved_job;
                $apply->user_id_fk = $user_id;
                $apply->job_id_fk = $job_id;
                $apply->created_at = date("Y-m-d H:i:s");
                $apply->updated_at = date("Y-m-d H:i:s");
                $apply->save();
                echo 2;die();
            }
        }
        else{
            //print_r($request->all());
            echo 3;die();
        }
    }
    public function employee_list(Request $request){
        //$user = User::where('role','2')->get();
        $user = Job_seeker_personal_details::all();
        $inputs = array();
        return view('admin.employee.list',compact('user','inputs'));
    }
    public function employee_filter(Request $request){
        $status = $request->status;
        $inputs = array($status);
        if($status == 'all'){
            $user = User::where('role','2')->get();
        }
        else if($status == 1){
            $user = User::where('role','2')->where('email_verify',1)->get();
        }
        else if($status == 2){
            $user = User::where('role','2')->where('email_verify',2)->get();
        }
        else if($status == 3){
            $user = User::where('role','2')->get();
            $user_arr = array();
            foreach($user as $u){
                if(count($u->academic)>0){
                    if(count($u->career_history)>0){
                        if(count($u->certificates)>0){
                            if(!empty($u->js_cover_letter)){
                                if(!empty($u->cvs)){
                                    if(!empty($u->job_preference)){
                                        if(!empty($u->personal_details)){
                                            if(count($u->projects)>0){
                                                if(count($u->seminar_detail)>0){
                                                    if(count($u->js_technical)>0){
                                                        array_push($user_arr,$u->id);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if(count($user_arr)>0){
                $user = User::where('role','2')->whereIn('id',$user_arr)->get();
            }
            else{
                $user = [];
            }
        }
        else if($status == 4){
            $user = User::where('role','2')->get();
            $user_arr = array();
            foreach($user as $u){
                if(count($u->academic)>0){
                    if(count($u->career_history)>0){
                        if(count($u->certificates)>0){
                            if(!empty($u->js_cover_letter)){
                                if(!empty($u->cvs)){
                                    if(!empty($u->job_preference)){
                                        if(!empty($u->personal_details)){
                                            if(count($u->projects)>0){
                                                if(count($u->seminar_detail)>0){
                                                    if(count($u->js_technical)>0){
                                                        array_push($user_arr,$u->id);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if(count($user_arr)>0){
                $user = User::where('role','2')->whereNotIn('id',$user_arr)->get();
            }
            else{
                $user = [];
            }
        }
        //dd($user);
        return view('admin.employee.list',compact('user','inputs'));
    }
    public function view_employee($emp_id){
        $user = User::where('id',$emp_id)->first();
        $personal = Personal_details::where('user_id_fk',$emp_id)->first();
        $last = Last_login::where('user_id_fk',$emp_id)->orderBy('created_at','DESC')->first();
        
        //print_r($academic);exit();
        $max = Academic_details::where('user_id_fk',$emp_id)->max('year_of_passing');
        
        $highest_academic = Academic_details::where('user_id_fk',$emp_id)->where('year_of_passing',$max)->first();

        $academic = Academic_details::where('user_id_fk',$emp_id)->where('year_of_passing','!=',$max)->get();
        //$academic = Academic_details::where('user_id_fk',$emp_id)->where('qualification_type','2')->get();
        //$highest_academic = Academic_details::where('user_id_fk',$emp_id)->where('qualification_type','1')->first();
        $current_company = Career_history::where('user_id_fk',$emp_id)->where('current_company','2')->first();
        $career = Career_history::where('user_id_fk',$emp_id)->where('current_company','1')->get();
        $app_last = Applied_job::where('user_id_fk',$emp_id)->where('created_at', '>=', Carbon::now()->subMonth())->get();
        $rec_last = Profile_views::where('job_seeker_id',$emp_id)->where('created_at', '>=', Carbon::now()->subMonth())->groupBy('user_id_fk')->get();
        $view_last = Profile_views::where('job_seeker_id',$emp_id)->where('created_at', '>=', Carbon::now()->subMonth())->get();
        $t_count = count($app_last);
        $r_count = count($rec_last);
        $v_count = count($view_last);
        $last_update = Helper::user_last_update($emp_id);
        return view('admin.employee.view',compact('user','personal','last','academic','highest_academic','current_company','career','t_count','r_count','v_count','last_update'));
    }
    public function forget_pass(Request $request){
        $email = $request->forgot_email;
        $check = User::where('email',$email)->first();
        if(!empty($check)){
            if($check->email_verify !='2'){
                echo 3;die();
            }
            $check->link_expiry = date('Y-m-d H:i:s');
            $check->save();
            //print_r($check);exit();
            $user_id = $check->id;
            $encrypted = Crypt::encryptString($user_id);
            $mail_data = array(
                        'email' => $email,
                        'link' => $encrypted,
                    );
        //print_r($mail_data['link']);exit();
                    Mail::send('email.reset-password', $mail_data, function ($message) use ($mail_data) {
                            $message->subject('Email Verification')
                                    ->from('developer10@indglobal-consulting.com')
                                    ->to($mail_data['email']);
                    });
                    echo 1;die();
        }else{
            echo 2;die();
        }
    }
    public function reset_pass(){
        $dec = request()->segment(3);
        $user = Crypt::decryptString($dec);
        $data = User::where('id',$user)->first();
        $link_time=date('Y-m-d H:i:s',strtotime($data->link_expiry.'+1 hour'));
        $now = date('Y-m-d H:i:s');
        //print_r($now ."----". $link_time."--------".$data->link_expiry);exit();
        if( $now <= $link_time )
        {
            return view('forgot',compact('user'));
        }else{
            return view('link_expire');
        }
        
    }
    public function update_password(Request $request){
        $pwd_validate = Validator::make($request->all(),['password' => 'required|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/']);
        if ($pwd_validate->fails()){
            echo 4;die();
        }
        $user_id = $request->user_id;
        $password = Hash::make($request->password);
        $check = User::where('id',$user_id)->first();
        if(!empty($check)){
            User::where('id',$user_id)->update(['password'=>$password]);
            echo 1;die();
        }else{
            echo 2;die();
        }
    }
    public function my_dashboard(){
        // $this->profile_meter();
        $user_id = Auth::user()->id;
        $user = User::where('id',$user_id)->first();
        $app_last = Applied_job::where('user_id_fk',$user_id)->where('created_at', '>=', Carbon::now()->subMonth())->get();
        $rec_last = Profile_views::where('job_seeker_id',$user_id)->where('created_at', '>=', Carbon::now()->subMonth())->groupBy('user_id_fk')->get();
        $view_last = Profile_views::where('job_seeker_id',$user_id)->where('created_at', '>=', Carbon::now()->subMonth())->sum('total');
        // print_r($view_last);die;
        $t_count = count($app_last);
        $r_count = count($rec_last);
        $v_count = $view_last;
        $apply = Applied_job::where('user_id_fk',$user_id)->orderBy('created_at','DESC')->get();
        $a_id = array();
        foreach ($apply as $a) {
            $a_id[] = $a->apply_id;
        }
        $reply = Application_reply::whereIn('apply_id_fk',$a_id)->where('Active',1)->orderBy('created_at','DESC')->get();
        $save = Saved_job::where('user_id_fk',$user_id)->where('Active',1)->orderBy('created_at','DESC')->get();
        $view = Profile_views::where('job_seeker_id',$user_id)->groupBy('user_id_fk')->get();
        // dd($view);
        $down = Cv_downloads::where('job_seeker_id',$user_id)->groupBy('user_id_fk')->get();
        $last = Last_login::where('user_id_fk',$user_id)->where('status','1')->first();
        return view('employee/my_dashboard',compact('user','app_last','rec_last','view','down','view_last','t_count','save','r_count','v_count','apply','reply','last'));
    }
    public function profile_photo_update(Request $request){
        //print_r($request->employer_id);
        $user_id = Auth::user()->id;
        $js_pd =  Personal_details::where('user_id_fk',$user_id)->get();
        if(count($js_pd)>0){
            $validation = $this->validate($request, [
            'edit-profile-pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            if($request->hasFile('edit-profile-pic')){
               $destination = public_path('/uploads/employee');  
               $relativepath = '/uploads/employee';  
               $file = $request->file('edit-profile-pic');
               $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
               $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
               Personal_details::where('user_id_fk',$user_id)->update(["profile_image"=>$filelocation]);
               $u=User::where('id',$user_id)->first();
               $u->profile_pic = $filelocation;
               $u->save();
               echo 1;
            }
        }
        else{
            echo 2;
        }
        die();
    }
    public function cv_upload(Request $request){
        // print_r("expression");exit();
        $mime_validate = Validator::make($request->all(),['cv_file' => 'required|mimes:doc,pdf,docx|max:2048']);
        if ($mime_validate->fails()){
           echo 3;die();
        }
        $user_id = Auth::user()->id;
        $cover = Cv::where('user_id_fk',$user_id)->first();
        if(!empty($cover)){
            $cov = Cv::where('user_id_fk',$user_id)->first();
        }else{
            $cov = new Cv;
            $cov->created_at = date("Y-m-d H:i:s");
        }
        $cov->user_id_fk = $user_id;
        if($request->hasFile('cv_file'))
        {
                $destination = 'uploads/resume';  
                $file = $request->file('cv_file');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                $cov->cv = $filelocation;
               
        }
        if(!empty($cover)){
            
        }
        $cov->updated_at = date("Y-m-d H:i:s");
        $cov->save();
        
        $id = $cov->js_cv_id;
        if(!empty($id)){
            echo 2;die();
        }
        else{
            echo 1;die();
        }
    }
    public function reply_delete(Request $request){
        Application_reply::whereIn('reply_id', $request->ids)->update(['Active'=>'2']);
        echo 1; die();
    }
    public function savedjobs_delete(Request $request){
        // Saved_job::whereIn('saved_id', $request->ids)->update(['Active'=>'2']);
        Saved_job::whereIn('saved_id', $request->ids)->delete();
        echo 1; die();
    }
    public function applyjobs_multiple(Request $request){
        $user_id = Auth::user()->id;
        $jobids = $request->ids;
        foreach($jobids as $jobid){
        $exist = Applied_job::where('user_id_fk',$user_id)->where('job_id_fk',$jobid)->get();
        $exist_count = count($exist);
            if($exist_count == 0){
                $apply_job = new Applied_job();
                $apply_job->user_id_fk = $user_id;
                $apply_job->job_id_fk = $jobid;
                $apply_job->created_at = date("Y-m-d H:i:s");
                $apply_job->created_at = date("Y-m-d H:i:s");
                $apply_job->save();
            }
        }
        echo 1; die();
    }
    public function get_chartvals(Request $request){
        $user_id = Auth::user()->id;
        $exist = Chart_data::where('user_id_fk',$user_id)->get();
        $exist_count = count($exist);
        if($exist_count > 0){
            $data = Chart_data::where('user_id_fk',$user_id)->first();
            $response =  array("status" => 1,
            "pval" => $data->pval, "jval" => $data->jval, "aval" => $data->aval, 
            "oval" => $data->oval);
            echo json_encode($response);
        }
        else{
            $data = Chart_data::where('user_id_fk',$user_id)->first();
            $response =  array("status" => 1,
            "pval" => 0, "jval" => 0, "aval" => 0, "oval" => 0);
            echo json_encode($response);
        }
        die();
    }
    public function set_chartvals(Request $request){
        $user_id = Auth::user()->id;
        $exist = Chart_data::where('user_id_fk',$user_id)->get();
        $exist_count = count($exist);
        if($exist_count>0){
            Chart_data::where('user_id_fk',$user_id)
                       ->update(["pval"=>$request->pval,
                                "jval"=>$request->jval,
                                "aval"=>$request->aval,
                                "oval"=>$request->oval,
                                "updated_at" => date("Y-m-d H:i:s")
                                ]);
        }
        else{
            $chartdata = new Chart_data();
            $chartdata->pval = $request->pval;
            $chartdata->jval = $request->jval;
            $chartdata->aval = $request->aval;
            $chartdata->oval = $request->oval;
            $chartdata->user_id_fk =  $user_id;
            $chartdata->created_at = date("Y-m-d H:i:s");
            $chartdata->updated_at = date("Y-m-d H:i:s");
            $chartdata->save();
        }
        echo 1;die();
    }
    public function my_settings(){
        $user_id = Auth::user()->id;
        $user = User::where('id',$user_id)->first();
        $js = User::where('id',$user_id)->first();
        $bemployers = Employer::select('*')
                              ->join('block_company_list','block_company_list.employer_id_fk','=','employer_details.employer_id')
                              ->where('block_company_list.user_id_fk',$user_id)
                              ->where('parent_id',0)
                              ->get();
        $bemps = array();      
        if(count($bemployers) > 0){
            foreach($bemployers as $bemp){
              array_push($bemps, $bemp->employer_id);
            }
        }
        $ubemployers = Employer::WhereNotIn('employer_id',$bemps)->where('parent_id',0)->get();
        //print_r($ubemployers);exit();
        //$last = Last_login::where('user_id_fk',$user_id)->where('status','1')->first();
        return view('employee/my_settings',compact('user','bemployers','ubemployers'));   
    }
    public function account_settings(Request $request){
        $status = $request->id;
        $user_id = Auth::user()->id;
        $js_pd =  Personal_details::where('user_id_fk',$user_id)->get();
        if(count($js_pd)>0){
        Personal_details::where('user_id_fk',$user_id)->update(["visibilty"=>$status]);
            echo 1;
        }
        else{
            echo 2;
        }
        die();
    }
    public function block_company(Request $request){
         $user_id = Auth::user()->id;
         $employers = $request->blockids;
         if(!empty($employers)){
            $emparr = explode(",", $employers);
            foreach($emparr as $emp){
                $block_company = new Block_company();
                $employers = $request->blockids;
                $block_company->user_id_fk = $user_id;
                $block_company->employer_id_fk = $emp;
                $block_company->save();
            }
         }
        $redirect_to = url('/my-settings');
        return redirect($redirect_to);
    }
    public function unblock_company(Request $request){
         $user_id = Auth::user()->id;
         $employers = $request->unblockids;
         $emparr = array();
         if(!empty($employers)){
            $emparr = explode(",", $employers);
         }
    Block_company::where('user_id_fk',$user_id)->whereIn('employer_id_fk',$emparr)->delete();
        $redirect_to = url('/my-settings');
        return redirect($redirect_to);
    }
    public function block_one_company(Request $request){
        // print_r("expression");exit();
        $user_id = Auth::user()->id;
        if(count(Block_company::where('user_id_fk',$user_id)->where('employer_id_fk',$request->id)->first())>0)
            {
                echo 2;die;
            }
        $block_company = new Block_company();
        $block_company->user_id_fk = $user_id;
        $block_company->employer_id_fk = $request->id;
        $block_company->save();
        echo 1;die;
    }
    public function unblock_one_company(Request $request){
        // print_r("expression");exit();
        $user_id = Auth::user()->id;
        Block_company::where('user_id_fk',$user_id)->where('employer_id_fk',$request->id)->delete();
        echo 1;die;
    }
    public function remove_ch(Request $request){
        $jsch_id = $request->jschid;
        Career_history::where('js_career_id',$jsch_id)->delete();
    }
    public function remove_cer(Request $request){
        $jsch_id = $request->jschid;
        Job_seeker_certificate::where('js_certificate_id',$jsch_id)->delete();
    }
    public function remove_proj(Request $request){
        $jsch_id = $request->jschid;
        Project::where('js_project_id',$jsch_id)->delete();
    }
    public function enable($id){
        User::where('id',$id)->update(['enabled'=>'1']);
        return redirect('admin/employee/list');
    }
    public function disable($id){
        User::where('id',$id)->update(['enabled'=>'2']);
        return redirect('admin/employee/list');
    }
    public function js_faqs_list(){
        $emp_faq = Faqs::where('used_for',1)->get();
        return view('admin.employee.faqs',compact('emp_faq'));
    }
    public function js_faqs_view($id){
        $faq_data = Faqs::where('faq_id',$id)->first();
        return view('admin.employee.faqs_view',compact('faq_data'));
    }
    public function js_faqs_delete($id){
        $faq_data = Faqs::where('faq_id',$id)->delete();
        return back();
    }
    public function q_delete($id)
    {
        $data=Question_Answers::where('id',$id)->delete();
        return back();
    }
    public function js_faqs_update(Request $request){
        $faqid = $request->faqid;
        $topic = $request->faq_topic;
        $content = $request->faq_content;
        $questions = $request->faq_question;
        $answers = $request->faq_answer;
        $faq_data = Faqs::where('faq_id',$faqid)->first();
        $faq_data->topic = $topic;
        $faq_data->content = $content;
        $faq_data->save();
        if(count($questions)>0){
            Question_Answers::where('faq_id_fk',$faqid)->delete();
            foreach($request->faq_question as $key => $n ) {
                $qna = new Question_Answers();
                $qna->faq_id_fk = $faqid;
                $qna->quetion = $questions[$key];
                $qna->answer = $answers[$key];
                $qna->save();
             }
        }
        if($faq_data->used_for == 1){
            $redirect_to = url('/admin/employee/faqs/list');
        }elseif(($faq_data->used_for == 2)){
            $redirect_to = url('/admin/employer/faqs/list');
        }
        
        $request->session()->flash('message','Succesfully Updated Record');
        return redirect($redirect_to);
    }
    public function js_faqs_add1get(){
         return view('admin.employee.faqs_add1');
    }
    public function js_faqs_add1post(Request $request){
        $faqnew = new Faqs();
        $faqnew->topic = $request->topic;
        $faqnew->used_for = 1;
        $faqnew->type = 1;
        $faqnew->content = $request->content;
        $faqnew->save();

        $redirect_to = url('/admin/employee/faqs/list');
        $request->session()->flash('message','Succesfully Added Record');
        return redirect($redirect_to);
    }
    public function js_faqs_add2get(){
         return view('admin.employee.faqs_add2');
    }
    public function js_faqs_add2post(Request $request){
        $faqnew = new Faqs();
        $faqnew->topic = $request->faq_topic;
        $faqnew->used_for = 1;
        $faqnew->type = 2;
        $faqnew->save();
        $faqid = $faqnew->faq_id;

        $questions = $request->faq_question;
        $answers = $request->faq_answer;
        
        foreach($request->faq_question as $key => $n ) {
            $qna = new Question_Answers();
            $qna->faq_id_fk = $faqid;
            $qna->quetion = $questions[$key];
            $qna->answer = $answers[$key];
            $qna->save();
        }

        $redirect_to = url('/admin/employee/faqs/list');
        $request->session()->flash('message','Succesfully Added Record');
        return redirect($redirect_to);
    }
    public function refer_friend(Request $request){
        $user_id = Auth::user()->id;
        $name = $request->name;
        $email = $request->email;
        $email_check1 = User::where('email',$email)->first();
        if(!empty($email_check1)){
            echo 2; die();
        }
        
        $email_check2 = Referal_Requests::where('email',$email)->orderBy('created_at','desc')->first();
         

        if(!empty($email_check2)){
            $d1 =date('Y-m-d H:i:s',strtotime($email_check2->created_at. ' + 2 month')) ;
            $d2 = date("Y-m-d H:i:s");
            if($d1 >= $d2){
            echo 3; die();
            }
        }
        //print_r(date("Y-m-d H:i:s", strtotime($email_check2->created_at. ' + 2 month') ));exit();
        $ref_req = new Referal_Requests();
        $ref_req->name = $name;
        $ref_req->email = $email;
        $ref_req->user_id_fk = $user_id;
        $ref_req->save();

        $user_info = User::where('id',$user_id)->first();
        $mail_data = array(
                 'email' => $request->email,
                 'data' => $user_info,
             );
        Mail::send('email.employee_refaral', $mail_data, function ($message) use ($mail_data) {
                     $message->subject('Referral request from Enterprise Jobs')
                             ->from('developer10@indglobal-consulting.com')
                             ->bcc("dev85@indglobal-consulting.com")
                             ->to($mail_data['email']);
        });
        echo 1; die();
    }
    public function referals_list(){
        $list = Referal_Requests::all();
        return view('admin.employee.reflist',compact('list'));
    }
    function compareByTimeStamp($time1, $time2)
    {
    if (strtotime($time1) < strtotime($time2))
        return 1;
    else if (strtotime($time1) > strtotime($time2)) 
        return -1;
    else
        return 0;
    }

    public function cv_delete(Request $request){
        Cv::where('js_cv_id',$request->key)->delete();
        echo 1;die; 
    }
    public function employer_emails(Request $request){
        $data['content'] = Emails_sent::where('sent_id',$request->id)->first();
        $content=$data['content'];
        $data['view']=view('employee.mail_popup',compact('content'))->render();
        echo json_encode($data);die;
    }
    public function employer_reply_emails(Request $request){
        $data['content'] = Application_reply::where('reply_id',$request->id)->first();
        $content=$data['content'];
        $job_detail=Applied_job::where('apply_id',$content->apply_id_fk)->first();
        $data['view']=view('employee.reply_mail_popup',compact('content','job_detail'))->render();
        echo json_encode($data);die;
    }
    public function replying_email(Request $request)
    {
        //print_r($request->all());exit();
        $reply = Application_reply::where('reply_id',$request->id)->first();
        $job = User::where('id',$reply->apply->job->user_id_fk)->first();
        //dd($job->email);exit();
        $data['apply_id'] = $reply->apply_id_fk;
        $data['email'] = $job->email;
        echo json_encode($data);die;
    }
    public function save_reply_mail(Request $request)
    {
        //print_r($request->all());exit();
        $subject = $request->subject;
        $msg = $request->message;
        $to_email = $request->to_email;
        $reply = new Application_re_reply();
        if($request->hasFile('resume')){
            $file_validator = Validator::make($request->all(), ['resume' => 'mimes:doc,pdf,docx|max:2048',]);
            
            if ($file_validator->fails()){
            echo 10;die();
            }
        }
        if($request->hasFile('resume'))
            {
                $destination = public_path('/uploads/resumes');  
                $relativepath = '/uploads/resumes';  
                $file = $request->file('resume');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
                
                $reply->resume = $filelocation;
            }
               
               $reply->reply_user_id = Auth::user()->id;
               $reply->apply_id_fk = $request->apply_id;
               $reply->reply_subject = $request->subject;
               $reply->reply_message = $request->message;
               $reply->save();
                
               $data = array(
                        'subject' => $request->subject,
                        'msg'=> $request->message,
                        'to_email' => $request->to_email,
                        'file' => url($filelocation),
                    ); 

            Mail::raw($msg, function ($message) use ($data,$file) {
                    $message->subject($data['subject'])
                            ->from('developer10@indglobal-consulting.com')
                            ->to($data['to_email'])
                            ->attach($data['file']);
                            
                        });

            if($reply->id)
            {
                $data = 1;
                
            }else
            {
                $data = 2;
            }
            echo json_encode($data);die;
    }

    public function sent_otp(Request $request)
    {//print_r($request->all());exit();
        $mobile=$request->country_code.request()->mobile;
        //print_r($mobile);exit();
        /*$check_mobile = User::where('mobile',$mobile)->first();
        if(!empty($check_mobile)){
            echo 2;die();
        }
        else{*/
              $string = '0123456789';
              $string_shuffled = str_shuffle($string);
              $otp = substr($string_shuffled, 1, 6);
       
              $msgtxt = "Your otp is ". $otp ." :ENTERPRISE";  

              $msgData = array(
                'recipient_no' => $mobile,
                'message' => $msgtxt
              );
              $sendsms = Helper::sendSMS($msgData);
              Session::put('otp',$otp);
              echo $sendsms;
        //}
    }
    /*public function verify_otp(Request $request)
    {
        $otp=$request->otp;
        // print_r(Session::get('otp'));die;
        if($otp==Session::get('otp'))
            {
                Session::flush();
                Session::put('otp_status',true);
                // print_r(Session::get('otp'));
                echo 1;die;
            }
        else
        {
            echo 2;die;
        }
    }*/
    public function remove_seminar(Request $request)
    {
        //print_r($request->all());
        $jsch_id = $request->jschid;
        Seminar_details::where('js_seminar_id',$jsch_id)->delete();
    }
}