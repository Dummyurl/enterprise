<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use Hash;
use Validator;
use App\Model\Employer;
use App\Model\EmployerReg;
use App\Model\Resume;
use App\Model\Job_post_package;
use App\Model\Job_post;
use Carbon\Carbon;
use App\Model\Referal_Requests;
use App\Model\Job_seeker_personal_details;
use App\Model\Addon_package;
use App\Model\User_package;
class AdminLoginController extends Controller
{   
    public function adminLogin()
    {
    	if(!empty(Auth::user()) && Auth::user()->role == 1){
      

    		return redirect('admin/dashboard');
      	}else{
      		return view('admin/admin_login');
      	}
    }
    public function dashboard(){
      //echo "123";exit();
      $val_arr = array();
      $date_today =  \Carbon\Carbon::parse(Carbon::now())->format('Y-m-d');

      $tot_js_reg_today = User::where('role',2)
                          ->where('created_at','like','%' . $date_today . '%')
                          ->get();
      array_push($val_arr,count($tot_js_reg_today));

      $tot_emp_reg_today = User::where('role',3)
                          ->where('created_at','like','%' . $date_today . '%')
                          ->get();
      array_push($val_arr,count($tot_emp_reg_today));

      //$tot_jpost_today = Job_post_package::where('created_at','like','%' . $date_today . '%')->get();
      $tot_jpost_today = User_package::where('created_at','like','%' . $date_today . '%')->where('status','<>','1')->get();
      array_push($val_arr,count($tot_jpost_today));

      $now = \Carbon\Carbon::now();
      $yearnow = $now->year;
      $monthnow = $now->month;
      $onemonthold =  $now->subMonth();
      
      $tot_jobs_expired = Job_post::where('job_expire', '<=', date('y-m-d'))->where('type',1)->get();
      array_push($val_arr,count($tot_jobs_expired));

  $tot_jobs_expiredtoday = Job_post::where('job_expire', '=', date('y-m-d'))->where('type',1)->get();
      array_push($val_arr,count($tot_jobs_expiredtoday));

      $tot_job_views_today = Job_post::where('last_seen','like','%' . $date_today . '%')
                                      ->get();
      array_push($val_arr,count($tot_job_views_today));

      // Referal Requests
      $tot_ref_requests = Referal_Requests::all();
      array_push($val_arr,count($tot_ref_requests));

      $tot_ref_requests_today = Referal_Requests::where('created_at','like','%' . $date_today . '%')->get();
      array_push($val_arr,count($tot_ref_requests_today));

      return view('admin/admin_dashboard',compact('val_arr'));
    }

    public function referals_list(){
        $list = Referal_Requests::where('created_at','like','%' . date('Y-m-d') . '%')->get();
        return view('admin.employee.reflist',compact('list'));
    }
    public function view_job()
    {
      $job_post=Job_post::orderBy('job_id','DESC')->where('type',1)->where('job_expire','<=',date('Y-m-d'))->get();
      return view('admin/job_post',compact('job_post'));
    }
    public function jobexpire_today()
    {
      $job_post=Job_post::where('job_expire', '=', date('y-m-d'))->where('type',1)->get();
      return view('admin/job_post',compact('job_post'));
    }
    public function employer_list()
    {
      $employer = Employer::where('created_at','like','%' . date('y-m-d') . '%')
                          ->get();
        return view('admin/employer/employer_list',compact('employer'));
    }
    public function employee_list(Request $request){
        //$user = User::where('role','2')->get();
        $user = Job_seeker_personal_details::where('created_at','like','%' . date('y-m-d') . '%')
                          ->get();
        $inputs = array();
        return view('admin.employee.list',compact('user','inputs'));
    }
    public function job_view()
    {
      $job_post=Job_post::where('last_seen','like','%' . date('Y-m-d') . '%')
                                      ->get();
      return view('admin/job_post',compact('job_post'));
    }
    public function package_bought()
    {
        $package = User_package::where('created_at','like','%' . date('y-m-d') . '%')
                          ->get();
        $addon = Addon_package::all();
        return view('admin.package.package',compact('package','addon'));
    }

    public function postAdminLogin(Request $request){
      $rules=array('email'=>'required','password'=>'required');	
      $this->validate($request,$rules);

      $email=$request->email;
      $password=$request->password;
      $users=User::where('email',$email)->first(); 
      if(!empty($users)){ 
        if (Hash::check($password, $users->password)) {
             if(Auth::attempt(['email'=>$email,'password'=>$password,'role'=>1])){
                  return redirect('admin/dashboard');
             }
             else
                return redirect('/admin')->with('message','Incorrect Email id or Password');
        }
        else 
           return redirect('/admin')->with('message','Incorrect Email id or Password');
      }
      else
        return redirect('/admin')->with('message','Incorrect Email id or Password');
      
    }
    public function changepassword(){
       return view('admin.change_password');
   }

   public function updatepassword(Request $request){
       //dd($request->all());
       $rules=array('password'=>'required|min:6','rpassword'=>'required|min:6|same:password'); 
       $this->validate($request,$rules);
       $admin = User::where('role',1)->first();
       $admin->password = Hash::make($request->password);
       $admin->save();
       return redirect('/admin');
   }

    public function adminLogout()
    {
    	Auth::logout();
    	return redirect('/admin');
    }
}
