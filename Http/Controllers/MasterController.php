<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\Course;
use App\Model\Specialization;
use App\Model\PGCourse;
use App\Model\PGSpecialization;
use App\Model\Feedback;
use App\Model\Language;
use App\Model\Last_login;
use App\Model\Contact_Enquiries;
use App\Model\HighestCourse;
use App\Model\HighestSpecialization;
use App\User;
use App\Model\Contact_enquiries_reply;
use Hash;
use Validator;
use Mail;

class MasterController extends Controller
{
    public function courselist()
    {
        $course = Course::orderby('course_id','DESC')->get();
        return view('admin.course.listcourse')->with('course',$course);
    }

    public function course_create()
    {
        return view('admin.course.addcourse');
    }

    public function course_store(Request $request)
    {
         $rules=array('course_name'=>'required');
         $this->validate($request,$rules);
         $check = Course::where('course_name',$request->course_name)->first();
         if(!empty($check)){
                $request->session()->flash('errormsg','Record already exists');
                return redirect('admin/course/list');
         }
         $course = new Course;
         $course->course_name = $request->course_name;
         $course->created_at = date("Y-m-d H:i:s");
         $course->updated_at = date("Y-m-d H:i:s");
         $course->save();

         if(!empty($request->specialization_name[0])){
             $insertId = $course->course_id;
             foreach($request->specialization_name as $sub){
                $specization = new Specialization;
                $specization->course_id_fk = $insertId;
                $specization->specialization_name = $sub;
                $specization->created_at = date("Y-m-d H:i:s");
                $specization->updated_at = date("Y-m-d H:i:s");
                $specization->save();    
             }
         }

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/course/list');
    }

    public function course_edit($id)
    {
        $course = Course::where('course_id',$id)->first();
        $specization = Specialization::where('course_id_fk',$id)->get();
        return view('admin.course.addcourse',compact('course','specization'));
    }

    public function course_update(Request $request,$id)
    {
        $rules = array('course_name'=>'required');
        $this->validate($request,$rules);
        $course =Course::where('course_id',$id)->first();
        $course->course_name = $request->course_name;
         $course->updated_at = date("Y-m-d H:i:s");
        $course->save();

        Specialization::where('course_id_fk',$id)->delete();

        if(!empty($request->specialization_name[0])){
            foreach($request->specialization_name as $sub){
                $specization = new Specialization;
                $specization->course_id_fk = $id;
                $specization->specialization_name = $sub;
                $specization->created_at = date("Y-m-d H:i:s");
                $specization->updated_at = date("Y-m-d H:i:s");
                $specization->save();    
            }
        }

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/course/list');
    }

    public function course_delete($id)
    {
        Specialization::where('course_id_fk',$id)->delete();
        $course = Course::where('course_id',$id)->first();
        $course->delete();

        return redirect('admin/course/list')->with('message','Succesfully Deleted Record');
    }

    public function pgcourselist()
    {
        $course = PGCourse::orderBy('pgc_name')->get();
        return view('admin.course.listpgcourse')->with('course',$course);
    }

    public function pgcourse_create()
    {
        return view('admin.course.addpgcourse');
    }

    public function pgcourse_store(Request $request)
    {
         $rules=array('pgc_name'=>'required');
         $this->validate($request,$rules);
         $check = PGCourse::where('pgc_name',$request->pgc_name)->first();
         if(!empty($check)){
                $request->session()->flash('errormsg','Record already exists');
                return redirect('admin/pgcourse/list');
         }
         $course = new PGCourse;
         $course->pgc_name = $request->pgc_name;
         $course->created_at = date("Y-m-d H:i:s");
         $course->updated_at = date("Y-m-d H:i:s");
         $course->save();

         if(!empty($request->pgs_name[0])){
             $insertId = $course->pgc_id;
             foreach($request->pgs_name as $sub){
                $specization = new PGSpecialization;
                $specization->pgc_id_fk = $insertId;
                $specization->pgs_name = $sub;
                $specization->created_at = date("Y-m-d H:i:s");
                $specization->updated_at = date("Y-m-d H:i:s");
                $specization->save();    
             }
         }

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/pgcourse/list');
    }

    public function pgcourse_edit($id)
    {
        $course = PGCourse::where('pgc_id',$id)->first();
        $specization = PGSpecialization::where('pgc_id_fk',$id)->get();
        return view('admin.course.addpgcourse',compact('course','specization'));
    }

    public function pgcourse_update(Request $request,$id)
    {
        $rules = array('pgc_name'=>'required');
        $this->validate($request,$rules);
        $course =PGCourse::where('pgc_id',$id)->first();
        $course->pgc_name = $request->pgc_name;
         $course->updated_at = date("Y-m-d H:i:s");
        $course->save();

        PGSpecialization::where('pgc_id_fk',$id)->delete();

        if(!empty($request->pgs_name[0])){
            foreach($request->pgs_name as $sub){
                $specization = new PGSpecialization;
                $specization->pgc_id_fk = $id;
                $specization->pgs_name = $sub;
                $specization->updated_at = date("Y-m-d H:i:s");
                $specization->save();    
            }
        }

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/pgcourse/list');
    }

    public function pgcourse_delete($id)
    {
        PGSpecialization::where('pgc_id_fk',$id)->delete();
        $course = PGCourse::where('pgc_id',$id)->first();
        $course->delete();
        return redirect('admin/pgcourse/list')->with('message','Succesfully Deleted Record');
    }
    public function feedback_list(){
        $feedbacks = Feedback::orderBy('created_at', 'DESC')->get();
        /*$user = User::all();
        foreach($user as $u){
            $emails[] =$u->email;
        } */
        return view('admin.feedbackList',compact('feedbacks'));
    }
    public function lnglist(){
        $lng_list = Language::all();
        return view('admin/langs/list',compact('lng_list'));
    }
    public function lng_add_get(){
        return view('admin/langs/add');
    }
    public function lng_add_store(Request $request){
         $rules=array('title'=>'required');
         $this->validate($request,$rules);

         $check = Language::where('name',$request->title)->first();
         if(empty($check)){
             $language = new Language();
             $language->name = $request->title;
             $language->save();

             $request->session()->flash('sucessmsg','Succesfully Inserted Record');
             return redirect('admin/languages/list');
         }
         else{
            $request->session()->flash('errormsg','Record already exists');
             return redirect('admin/languages/list');
         }
    }
    public function lng_edit($id)
    {
        $language = Language::where('id',$id)->first();
        return view('admin/langs/add',compact('language'));
    }
    public function lng_update(Request $request,$id)
    {
        $language = Language::where('id',$id)->first();
        $rules=array('title'=>'required');
        $this->validate($request,$rules);
        $language->name = $request->title;
        $language->save();

        $request->session()->flash('sucessmsg','Succesfully Updated Record');
             return redirect('admin/languages/list');
    }
    public function lng_delete(Request $request,$id)
    {
        Language::where('id',$id)->delete();

        $request->session()->flash('sucessmsg','Succesfully deleted Record');
             return redirect('admin/languages/list');
    }
    public function login_track(){
        $list = Last_login::select('*')
                             ->join('users', 'users.id', '=', 'last_login.user_id_fk')
                             ->get();
        $inputs = array();
        $users = User::orderBy('name')->get();

        return view('admin/login_track',compact('list','users','inputs'));
    }
    public function filter_logins(Request $request){
        $user = $request->user;
        if($user == 'all'){
            $list = Last_login::select('*')
                     ->join('users', 'users.id', '=', 'last_login.user_id_fk')
                     ->get();
        }
        else{
            $list = Last_login::select('*')
                     ->join('users', 'users.id', '=', 'last_login.user_id_fk')
                     ->where('last_login.user_id_fk','=',$user)
                     ->get();
        }

        $inputs = array($user);
        $users = User::orderBy('name')->get();

        return view('admin/login_track',compact('list','users','inputs'));
    }
    public function resetpwd($id){
        return view('admin.change_password')->with('id',$id);
    }
    public function resetpwdadmin(Request $request){
        $userid = $request->user_id;
        $new_pwd = $request->new_password;
        $retype_pwd = $request->c_password;
        //print_r($request->all());exit();
        if($new_pwd == $retype_pwd){
         $pwd_validate = Validator::make($request->all(),['new_password' => 'required|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/']);
            if($pwd_validate->fails()){
                $request->session()->flash('errormsg','Password criteria should match');
                return redirect('admin/employee/resetpwd/'.$userid);
            }
            else{
                $new_pass = Hash::make($new_pwd);
                User::where('id',$userid)->update(['password'=>$new_pass]);
                $request->session()->flash('successmsg','Password changed successfully');
                if(User::where('id',$userid)->where('role',4)->first())
                {
                    return redirect('admin/staff/list');
                }elseif(User::where('id',$userid)->where('role',2)->first()){
                    //return redirect('admin/employee/resetpwd/'.$userid);
                    return redirect('admin/employee/list');
                }elseif(User::where('id',$userid)->where('role',3)->first()){
                    return redirect('admin/employer/list');
                }elseif(User::where('id',$userid)->where('role',1)->first()){
                    return back();
                }
                
            }
        }
        else{
            $request->session()->flash('errormsg','Passwords should match');
            return redirect('admin/employee/resetpwd/'.$userid);
        }
    }
    public function contact_enquiries(){
        $list = Contact_Enquiries::where('type','0')->get();
        $id=0;
        //print_r($list);exit();
        return view('admin.enquiries.list',compact('list','id'));
    }
    public function contact_delete($id){
        Contact_Enquiries::where('id',$id)->delete();
        return back();
    }
    public function contact_reply($id){
        $enq = Contact_Enquiries::where('id',$id)->first();
         return view('admin.enquiries.reply',compact('enq'));
    }
    public function contact_reply_save(Request $request)
    {
        $enq = Contact_Enquiries::where('id',$request->id)->first();
        $email_to = $enq->email;

        $mail_data = array(
                         'email' => $email_to,
                         'data' => $request->data,
                     );
        Mail::send('email.send-reply', $mail_data, function ($message) use ($mail_data) {
                             $message->subject("Reply to your message")
                                     ->from("admin@enterprisejobs.com")
                                     ->to($mail_data['email']);
                });

        $reply = new Contact_enquiries_reply();
        $reply->enquiry_id = $enq->id;
        $reply->email = $enq->email;
        $reply->message = $request->data;
        $reply->save();

        $request->session()->flash('successmsg','Reply Sent Succesfully');
        return redirect('admin/contactus/enquiries');
    }
    public function view_reply($id)
    {
        $reply = Contact_enquiries_reply::where('enquiry_id',$id)->get();
        return view('admin.enquiries.view_reply',compact('reply'));
    }
    public function reply_delete($id)
    {
        $reply = Contact_enquiries_reply::where('id',$id)->delete();
        return back();
    }
    public function seeker_enquiries(){
        $list = Contact_Enquiries::where('type','1')->get();
        $id=1;
        //print_r($list);exit();
        return view('admin.enquiries.list',compact('list','id'));
    }

    public function highestlist()
    {
        $course = HighestCourse::orderby('course_id','DESC')->get();
        return view('admin.course.listhcourse')->with('course',$course);
    }

    public function highest_create()
    {
        return view('admin.course.addhcourse');
    }

    public function highest_store(Request $request)
    {
         $rules=array('course_name'=>'required');
         $this->validate($request,$rules);

         $check = HighestCourse::where('course_name',$request->course_name)->first();
         if(!empty($check)){
                $request->session()->flash('errormsg','Record already exists');
                return redirect('admin/highest/list');
         }
         $course = new HighestCourse;
         $course->course_name = $request->course_name;
         $course->created_at = date("Y-m-d H:i:s");
         $course->updated_at = date("Y-m-d H:i:s");
         $course->save();

         if(!empty($request->specialization_name[0])){
             $insertId = $course->course_id;
             foreach($request->specialization_name as $sub){
                $specization = new HighestSpecialization;
                $specization->hc_id_fk = $insertId;
                $specization->hs_name = $sub;
                $specization->created_at = date("Y-m-d H:i:s");
                $specization->updated_at = date("Y-m-d H:i:s");
                $specization->save();    
             }
         }

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/highest/list');
    }

    public function highest_edit($id)
    {
        $course = HighestCourse::where('course_id',$id)->first();
        $specization = HighestSpecialization::where('hc_id_fk',$id)->get();
        return view('admin.course.addhcourse',compact('course','specization'));
    }

    public function highest_update(Request $request,$id)
    {
        $rules = array('course_name'=>'required');
        $this->validate($request,$rules);
        $course =HighestCourse::where('course_id',$id)->first();
        $course->course_name = $request->course_name;
         $course->updated_at = date("Y-m-d H:i:s");
        $course->save();

        HighestSpecialization::where('hc_id_fk',$id)->delete();

        if(!empty($request->specialization_name[0])){
            foreach($request->specialization_name as $sub){
                $specization = new HighestSpecialization;
                $specization->hc_id_fk = $id;
                $specization->hs_name = $sub;
                $specization->created_at = date("Y-m-d H:i:s");
                $specization->updated_at = date("Y-m-d H:i:s");
                $specization->save();    
            }
        }

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/highest/list');
    }

    public function highest_delete($id)
    {
        HighestSpecialization::where('hc_id_fk',$id)->delete();
        $course = HighestCourse::where('course_id',$id)->first();
        $course->delete();

        return redirect('admin/highest/list')->with('message','Succesfully Deleted Record');
    }
}
