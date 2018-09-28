<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;

use App\Model\Staff_details;
use App\Model\Staff_group;
use App\User;

use Image;

class StaffController extends Controller{
    public function list(){
        $list = Staff_details::all();
        return view('admin.staff.list')->with('list',$list);
    }
    public function add_get()
    {
        $groups = Staff_group::where('active',1)->get();
        return view('admin.staff.add',compact('groups'));
    }
    public function add_store(Request $request)
    {
         $pwd_validate = Validator::make($request->all(),['password' => 'required|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/']);
          $email_validate = Validator::make($request->all(),['email' => 'required|string|regex:/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/']);
        if ($email_validate->fails()){
             $request->session()->flash('errormsg','Please Enter A Valid Email');
             return back();
        }
        if ($pwd_validate->fails()){
             $request->session()->flash('errormsg','Password must be more than 6 characters long, should contain at-least 1 Uppercase, 1 Lowercase, 1 Numeric and 1 special character.');
             return back();
        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = '4';
        $user->mobile = $request->mobile;
        $user->password = Hash::make($request->password);
        $user->created_at = date("Y-m-d H:i:s");
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        $userid = $user->id;

        $empspeak = new Staff_details();
        $empspeak->user_id_fk = $userid;
        $empspeak->group_id_fk = $request->group;
        $empspeak->name = $request->name;
        $empspeak->email = $request->email;
        $empspeak->mobile = $request->mobile;
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Inserted Record');
        return redirect('admin/staff/list');
    }
    public function edit($id)
    {
        $groups = Staff_group::where('active',1)->get();
        $speak = Staff_details::where('id',$id)->first();
        return view('admin.staff.add',compact('speak','groups'));
    }
    public function update(Request $request,$id)
    {
        $empspeak = Staff_details::where('id',$id)->first();
        $empspeak->name = $request->name;
        $empspeak->email = $request->email;
        $empspeak->mobile = $request->mobile;
        $empspeak->group_id_fk = $request->group;
        $empspeak->save();

        $user = User::where('id',$empspeak->user_id_fk)->first();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->save();

        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/staff/list');
    }
    public function delete($id)
    {
        $location = Staff_details::where('id',$id)->first();
        $location->delete();

        $user = User::where('id',$id)->delete();
       /* $user->enable = 2;
        $user->save();
*/
        return redirect('admin/staff/list')->with('successmsg','Succesfully Deleted Record');
    }
}