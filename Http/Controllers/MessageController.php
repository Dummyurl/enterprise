<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;

use App\Model\Messages;
use App\Model\Job_seeker_personal_details;
use App\User;
use Mail;
use Image;


class MessageController extends Controller{
    public function list(){
    	$data = Messages::all();
        return view('admin.messages.list',compact('data'));
    }
    public function send_get(){
    $employers = User::where('role',3)->get();
    $jobseekers = Job_seeker_personal_details::select('*')
    ->join('users', 'users.id', '=', 'job_seeker_personal_details.user_id_fk')
    ->where('users.role','2')
    ->get();
        return view('admin.messages.add',compact('jobseekers','employers'));
    }
    public function send_post(Request $request){
        //print_r($request->all());exit();
        if($request->utype == 1){
            $users = $request->jobseekers;
        }
        else{
            $users = $request->employers;
        }
        foreach ($users as $user) {
           $u = User::where('id',$user)->first();
           if(!empty($u))
           {
            Mail::raw($request->message, function ($message) use ($u){
                                $message->subject('Enterprise')
                                        ->to($u->email);
                        });

                $msg = $request->message;
                $msgs = new Messages();
                $msgs->user_type = $request->utype;
                $msgs->user_id_fks = implode(",",$users);
                $msgs->message = $msg;
                $msgs->save();
                $request->session()->flash('successmsg','Message Sent Succesfully');
                return redirect('admin/messages/list');
            }else{
                    
                   $request->session()->flash('successmsg','Message Sent Failed');
                return redirect('admin/messages/list');
            }
        }

    }
    public function delete($id)
    {
        $data = Messages::where('message_id',$id)->delete();
        return back();
    }
}