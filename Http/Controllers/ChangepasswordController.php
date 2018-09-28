<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use Hash;
use Validator;


class ChangepasswordController extends Controller
{   
    public function update_pass()
    {
		return view('employer/change_password');
    }


    
    public function password_success(){
      return view('employer/password_success');
    }


    public function update_password(Request $request){
      $pwd_validate = Validator::make($request->all(),['new_password' => 'required|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/']);
        if ($pwd_validate->fails()){
            echo 3;die();
        }
      $user_id = Auth::user()->id;
       $password = $request->old_password;

       $check = User::where('id',$user_id)->first();
//echo $password;
       if(!empty($check)){
        	$new_pass = Hash::make($request->new_password);
        	if (Hash::check($password, $check->password)){
        		User::where('id',$user_id)->update(['password'=>$new_pass]);
          		echo 1;die();
        	}else{
        		echo 2;die();
          
       		}
       	}
       	else{
        echo 4;die();
       }
        //}

    }




}