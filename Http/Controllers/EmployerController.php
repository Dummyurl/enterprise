<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
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
use App\Model\Folder;
use App\Model\Faqs;
use App\Model\Question_Answers;
use App\Model\Testimonials;
use App\Model\Landing_Menus;
use DB;
use Helper;
use CaptchaTrait;
use ReCaptcha\ReCaptcha;
use App\Model\Notice;
use App\Model\Interest_reply;
use App\Model\Free_jobpost_validity;


class EmployerController extends Controller{
	public function registration(){
        if(Session::get('otp') != "")
        {
            Session::forget('otp');
        }
		$country = Countries::all();
		$industry = IndustryType::orderBy('industry_type_name')->get();
        if((Auth::check()) && (Auth::user()->role == 3)){
            $path = url('/employer-profile');
            return redirect($path);
        }
        else{
            return view('employer/register',compact('country','industry'));
        }
	}
	 public function getStateList(Request $request){
        $id = $request->id;
        $state = States::where('country_id',$id)->get();
        $str = "<option value=''>Select State</option>";
        foreach ($state as $s) {
            $str .= "<option  value=".$s->id.">".$s->name."</option>";
        }

        echo $str; die();
    }

    public function getCityList(Request $request){
        $id = $request->id;
        $city = Cities::where('state_id',$id)->get();
        $str = "<option value=''>Select City</option>";
        foreach ($city as $c) {
            $str .= "<option  value=".$c->id.">".$c->name."</option>";
        }
        echo $str; die();
    }
    public function employer_save(Request $request){
        //print_r($request['g-recaptcha-response']);exit();
         $response = $request['g-recaptcha-response'];
            $remoteip = $_SERVER['REMOTE_ADDR'];
            $secret   = env('RE_CAP_SECRET');

            $recaptcha = new ReCaptcha($secret);
            $resp = $recaptcha->verify($response, $remoteip);
            //print_r($resp->isSuccess());exit();
        if ($resp->isSuccess()) {
            } else {
                echo 9;die();
            }
         //print_r($request->all());exit();
                // print_r(Session::get('otp'));die;
    	$first_name = $request->first_name;
    	$last_name = $request->last_name;
    	$email_id = $request->email_id;
        $personal_domains = array("gmail","yahoo");
        foreach($personal_domains as $pd){
            if (strpos($email_id, $pd) !== false) {
                echo 6;die();
            }
        }
    	$company_name = $request->company_name;
    	$type = $request->comp_consult;
    	$industry = $request->industry;
    	$designation = $request->designation;
    	$office_address = $request->office_address;
    	$landline = $request->landline;
    	$country_code = $request->country_code;
    	$mobile = $request->mobile;
    	$otp = Session::get('otp');
    	$country = $request->country;
    	$state = $request->state;
    	$city = $request->city;
    	$zip = $request->zip;
    	$check_email = User::where('email',$email_id)->first();
        $check_employer_email = Employer::where('email',$email_id)->first();
    	if(!empty($check_email)){
    		echo 1;die();
    	}
        if(!empty($check_employer_email)){
            echo 5;die();
        }
    	/*$check_mobile = User::where('mobile',$mobile)->first();
    	if(!empty($check_mobile)){
    		echo 2;die();
    	}*/
    	$user = new Employer;
        //start - default values insert - setup for local dev environment
        $user->user_id_fk = 0;
        $user->website_url = "www.example.com";
        $user->toll_free = "18004357686";
        $user->password = "enterprise";
        //End
    	$user->first_name = $first_name;
    	$user->last_name = $last_name;
    	$user->designation = $designation;
    	$user->company_name = $company_name;
    	$user->office_address = $office_address;
        if($industry == 'other'){
            $user->industry_type = $request->other;
            $itnew = new IndustryType();
            $itnew->industry_type_name = $request->other;
            $itnew->save();
        }
        else{
            $user->industry_type = $industry;
        }
    	$user->landline = $landline;
    	$user->mobile = $mobile;
    	$user->country_code = $country_code;
    	$user->country = $country;
    	$user->state = $state;
    	$user->city = $city;
    	$user->zip = $zip;
    	$user->type = $type;
    	$user->email = $email_id;
    	$user->otp = $otp;
    	$user->activate = '1';
    	$user->created_at = date("Y-m-d H:i:s");
    	$user->updated_at = date("Y-m-d H:i:s");
        //print_r($user);exit();

          if($request->hasFile('profile_picture'))
          {
                $destination = public_path('/uploads/employer');  
                $relativepath = '/uploads/employer';  
                $file = $request->file('profile_picture');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
                $user->profile_picture = $filelocation;
           }

    	$user->save();
    	$id = $user->employer_id;
    	if(!empty($id)){
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
    public function employer_interest(Request $request){
       // print_r($request->email);exit();
        /*if(User::where('email',$request->email)->where('role',3)->first())
            {*/
                $interest = new Interest;
                $interest->name = $request->name;
                $interest->email = $request->email;
                //$interest->type = $request->comp_consult;
        		$interest->type = '1';
                $interest->phone = $request->mobile;
                $interest->company_name = $request->company_name;
                $interest->country_code = $request->country_code;
                $interest->interest = $request->interested;
                if(!empty($request->message)){
                    $interest->message = $request->message;
                }
                
                $interest->location = $request->location;
                $interest->created_at = date("Y-m-d H:i:s");
                $interest->updated_at = date("Y-m-d H:i:s");
                $interest->save();
                $id = $interest->interest_id;
                if(!empty($id)){
                    echo 1;die();
                }
                else{
                    echo 2;die();
                }
            /*}else{
                echo 3;die();
            }*/
    }
    public function employer_list(){
        //$employer = Employer::where('parent_id',0)->get();
        $employer = Employer::all();
        return view('admin/employer/employer_list',compact('employer'));
    }
    public function interest_list(){
        $employer = Interest::where('active',1)->get();
        /*$user = User::all();
        foreach ($user as $u) {
           $user_email[] = $u->email;
        }*/
        return view('admin/employer/interest_list',compact('employer'));
    }
    public function activate_employer($id){
        $employer = Employer::where('employer_id',$id)->first();
        $email = $employer->email;
        $name = $employer->first_name .' '.$employer->last_name;
        $random_pwd = Str::random(10);
        //$email = "admin@gmail.com";
        $user = User::where('email',$email)->first();
        if(empty($user)){
            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->mobile = $employer->mobile;
            $user->role = '3';
            $user->password = Hash::make($random_pwd);
            $user->link_expiry = date("Y-m-d H:i:s");
            $user->created_at = date("Y-m-d H:i:s");
            $user->updated_at = date("Y-m-d H:i:s");
            $user->save();
            $user_id = $user->id;

            //Create a default cv folder for every employer
            $folder = new Folder();
            $folder->user_id_fk = $user_id;
            $folder->folder_name = "Folder";
            $folder->usedfor = "1";
            $folder->created_at = date("Y-m-d H:i:s");
            $folder->updated_at = date("Y-m-d H:i:s");
            $folder->save();
            //Create a default job folder for every employer
            $folder = new Folder();
            $folder->user_id_fk = $user_id;
            $folder->folder_name = "Folder";
            $folder->usedfor = "2";
            $folder->created_at = date("Y-m-d H:i:s");
            $folder->updated_at = date("Y-m-d H:i:s");
            $folder->save();
            Employer::where('employer_id',$id)->update(['password'=>$random_pwd,'user_id_fk'=>$user_id]);
            $mail_data = array(
                            'email' => $email,
                            'pwd' => $random_pwd,
                         );
            //print_r($user);exit();
            Mail::send('email.send-password', $mail_data, function ($message) use ($mail_data) {
                    $message->subject('Your account credentials')
                            ->from('developer10@indglobal-consulting.com')
                            ->to($mail_data['email']);
            });
        }
        //print_r($user);exit();
        Employer::where('employer_id',$id)->update(['activate'=>'2']);
        return redirect('admin/employer/list');
    }
    public function deactivate_employer($id){
        Employer::where('employer_id',$id)->update(['activate'=>'1']);
        return redirect('admin/employer/list');
    }
    public function delete_employer($id){
        Employer::where('employer_id',$id)->delete();
        User::where('id',$id)->delete();
        return redirect('admin/employer/list');
    }
    public function interest_remove($id){
        Interest::where('interest_id',$id)->delete();
        return redirect('admin/employer/interest');
    }
    public function interest_reply($id){
        $interest_data = Interest::where('interest_id',$id)->first();
        return view('admin/employer/interest_reply',compact('interest_data'));
    }
    public function reply_send(Request $request){
        $insid = $request->interest_id;
        $interest_data = Interest::where('interest_id',$insid)->first();
        $email_to = $interest_data->email;

        

        $mail_data = array(
                         'email' => $email_to,
                         'data' => $request->data,
                     );
        Mail::send('email.send-reply', $mail_data, function ($message) use ($mail_data) {
                             $message->subject("Reply to your message")
                                     ->from("developer10@indglobal-consulting.com")
                                     ->to($mail_data['email']);
                });

        $reply = new Interest_reply();
        $reply->interest_id = $interest_data->interest_id;
        $reply->email = $interest_data->email;
        $reply->message = $request->data;
        $reply->save();
        return redirect('admin/employer/interest');
    }
    public function view_interest_reply($id)
    {
        $interest_data = Interest_reply::where('interest_id',$id)->get();
        return view('admin/employer/view_interest_reply',compact('interest_data'));
    }
    public function delete_interest_reply($id)
    {
        $interest_data = Interest_reply::where('id',$id)->delete();
        return back();
    }
    public function employer_login(Request $request){
        //print_r($request->all());exit();
         $password = $request->password;
         $email = $request->user_name;
		 $active_employer = Employer::where('email',$email)->where('activate','2')->first();
         $login = User::where('email',$email)->where('enabled','1')->where('email_verify','2')->first();
         if((!empty($login)) && (!empty($active_employer))){
            
           
                if (Hash::check($password, $login->password)) {
                     if(Auth::attempt(['email'=>$email,'password'=>$password,'role'=>3])){
                        Auth::user(); 
                        $id = Auth::user()->id;
                        Last_login::where('user_id_fk',$id)->update(['status'=>'2']);
                        $last = new Last_login;
                        $last->user_id_fk = $id;
                        $last->login_time = date("Y-m-d H:i:s");
                        //start - default values insert - setup for local dev environment
                        $last->logout_time = date("Y-m-d H:i:s");
                        //End
                        $last->status = '1';
                        $last->created_at = date("Y-m-d H:i:s");
                        $last->updated_at = date("Y-m-d H:i:s");
                        $last->save();
                    //echo 2;die();
                        return Auth::user(); die();
                    }
                    else{
                      
                        echo 3;die();
                    }
             } 
             else{
                echo 4;die();
             }
         }else{
            echo 5;die();
         }
        
   
    }

    public function employer_profile(){
         $user_id = Auth::user()->id;
         //echo $user_id;
         $data = Employer::where('user_id_fk',$user_id)->get();
         $page = "profile";
        // echo json_encode($data);
         $user = User::where('id',$user_id)->get();
         $notice = Notice::first();

        
       // return view('admin/employer/employer_list',compact('employer'));
        return view('employer/employer',compact('user','data','page','notice'));
    }
    public function emp_faqs_list(){
        $emp_faq = Faqs::where('used_for',2)->get();
        return view('admin.employer.faqs',compact('emp_faq'));
    }
    public function emp_faqs_view($id){
        $faq_data = Faqs::where('faq_id',$id)->first();
        return view('admin.employer.faqs_view',compact('faq_data'));
    }
    public function emp_faqs_update(Request $request){
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
        $redirect_to = url('/admin/employer/faqs/list');
        $request->session()->flash('message','Succesfully Updated Record');
        return redirect($redirect_to);
    }
    public function emp_faqs_add1get(){
         return view('admin.employer.faqs_add1');
    }
    public function emp_faqs_add1post(Request $request){
        $faqnew = new Faqs();
        $faqnew->topic = $request->topic;
        $faqnew->used_for = 2;
        $faqnew->type = 1;
        $faqnew->content = $request->content;
        $faqnew->save();

        $redirect_to = url('/admin/employer/faqs/list');
        $request->session()->flash('message','Succesfully Added Record');
        return redirect($redirect_to);
    }
    public function emp_faqs_add2get(){
         return view('admin.employer.faqs_add2');
    }
    public function emp_faqs_add2post(Request $request){
        $faqnew = new Faqs();
        $faqnew->topic = $request->faq_topic;
        $faqnew->used_for = 2;
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

        $redirect_to = url('/admin/employer/faqs/list');
        $request->session()->flash('message','Succesfully Added Record');
        return back();
    }
    public function emp_newspeak(){
         return view('employer.new_speak');
    }
    public function save_newspeak(Request $request){
        $user_id = Auth::user()->id;

        $empspeak = new Testimonials();
        $empspeak->emp_id_fk = $user_id;
        $empspeak->title = $request->title;
        $empspeak->desc = $request->content;
        $empspeak->status = 2;
        $empspeak->save();
        $redirect_to = url('/emp-newspeak');
        return redirect($redirect_to)->with('newspeakmsg','message');;
    }
    public function landing_menus(){
        $menus = Landing_Menus::where('menu_id',4)->get();
        return view('admin.employer.menus',compact('menus'));
    }
    public function menus_save(Request $request){
        $cnt = Landing_Menus::where('menu_id',4)->get()->count();
        for($mn=1; $mn<=$cnt; $mn++){
            $name = "menu_name_".$mn;
            $id = "hdnid_".$mn;
            $statval = $request->$name;
            $idval = $request->$id;
            Landing_Menus::where('id',$idval)
                         ->where('menu_id',4)->update(['status'=>$statval]);
        }
      return redirect('admin/employer/landing-menus')->with('successmsg','Your selection saved successfully');
    }
    public function sent_otp(Request $request)
    {
        $mobile=request()->mobile;
        $check_mobile = User::where('mobile',$mobile)->first();
        /*if(!empty($check_mobile)){
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
       // }
    }
    public function verify_otp(Request $request)
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
    }
    public function fjp_expiry()
    {
        //print_r(Free_jobpost_validity::first()->days);exit();
        $data = Free_jobpost_validity::first();
        return view('admin.employer.job_expiry_date',compact('data'));
    }
    public function fjp_expiry_save(Request $request)
    {

       $data = Free_jobpost_validity::first();
       $data->days = $request->days;
       $data->save();
       return redirect('admin/employer/fjp_expiry')->with('successmsg','Your Selected Record saved successfully');
    }
}