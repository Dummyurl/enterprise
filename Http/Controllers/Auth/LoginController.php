<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Socialite;
use App\User;
use Auth;
use App\Model\Personal_details;
use App\Model\Last_login;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/job-seeker-dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function redirectToProvider($provider)
    {
        // print_r("expression");exit();
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that 
     * redirect them to the authenticated users homepage.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        // if($provider=='linkedin')
        // {
        //     dd(request()->all);
        // }
        $user = Socialite::driver($provider)->user();

         
        $authUser = $this->findOrCreateUser($user, $provider);
        // print_r($authUser);exit();
        Auth::login($authUser, true);
        // print_r($this->redirectTo);exit();
        return redirect('/');
    } 
    public function findOrCreateUser($user, $provider)
    {
        //print_r($user);exit();
        
         $server_ip = $_SERVER["REMOTE_ADDR"];
         $system_ip = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));
        if($provider=='google')
        {
            $authUser = User::where('google_id', $user->id)->where('email',$user->email)->first();
            if ($authUser) {
                $last = new Last_login;
                $last->user_id_fk = $authUser->id;
                $last->login_time = date("Y-m-d H:i:s");
                $last->status = '1';
                $last->created_at = date("Y-m-d H:i:s");
                $last->updated_at = date("Y-m-d H:i:s");
                $last->logout_time = date("Y-m-d H:i:s");
                $last->server_ip = $server_ip;
                $last->system_ip = $system_ip;
                $last->save();    
                return $authUser;
            }
            if($user = User::where('email',$user->email)->first())
            {
                $last = new Last_login;
                $last->user_id_fk = $user->id;
                $last->login_time = date("Y-m-d H:i:s");
                $last->status = '1';
                $last->created_at = date("Y-m-d H:i:s");
                $last->updated_at = date("Y-m-d H:i:s");
                $last->logout_time = date("Y-m-d H:i:s");
                $last->server_ip = $server_ip;
                $last->system_ip = $system_ip;
                $last->save();   
                return $user;
            }
            $users=new User();
            $users->name=$user->name;
            $users->email=$user->email;
            $users->profile_pic=$user->avatar;
            $users->role=2;
            $users->email_verify = 2;
            $users->google_id=$user->id;
            $users->save();

                $pdtls = new Personal_details();
                $pdtls->user_id_fk = $users->id;
                $pdtls->total_exp = 0;
                $pdtls->created_at = date("Y-m-d H:i:s");
                $pdtls->updated_at = date("Y-m-d H:i:s");
                $pdtls->save();
           
            $last = new Last_login;
            $last->user_id_fk = $users->id;
            $last->login_time = date("Y-m-d H:i:s");
            $last->status = '1';
            $last->created_at = date("Y-m-d H:i:s");
            $last->updated_at = date("Y-m-d H:i:s");
            $last->logout_time = date("Y-m-d H:i:s");
            $last->server_ip = $server_ip;
            $last->system_ip = $system_ip;
            $last->save();           

            return $users;
        }
        else if($provider=='linkedin')
        {
             $authUser = User::where('linkedin_id', $user->id)->first();
            if ($authUser) {
                $last = new Last_login;
                $last->user_id_fk = $authUser->id;
                $last->login_time = date("Y-m-d H:i:s");
                $last->status = '1';
                $last->created_at = date("Y-m-d H:i:s");
                $last->updated_at = date("Y-m-d H:i:s");
                $last->logout_time = date("Y-m-d H:i:s");
                $last->server_ip = $server_ip;
                $last->system_ip = $system_ip;
                $last->save();    
                return $authUser;
            }
            if($user = User::where('email',$user->email)->first())
            {
                $last = new Last_login;
                $last->user_id_fk = $user->id;
                $last->login_time = date("Y-m-d H:i:s");
                $last->status = '1';
                $last->created_at = date("Y-m-d H:i:s");
                $last->updated_at = date("Y-m-d H:i:s");
                $last->logout_time = date("Y-m-d H:i:s");
                $last->save();   
                return $user;
            }
            $users=new User();
            $users->name=$user->name;
            $users->email=$user->email;
            $users->profile_pic=$user->avatar;
            $users->role=2;
            $users->email_verify = 2;
            $users->linkedin_id=$user->id;
            $users->save();

                $pdtls = new Personal_details();
                $pdtls->user_id_fk = $users->id;
                $pdtls->total_exp = 0;
                $pdtls->created_at = date("Y-m-d H:i:s");
                $pdtls->updated_at = date("Y-m-d H:i:s");
                $pdtls->save();

            $last = new Last_login;
            $last->user_id_fk = $users->id;
            $last->login_time = date("Y-m-d H:i:s");
            $last->status = '1';
            $last->created_at = date("Y-m-d H:i:s");
            $last->updated_at = date("Y-m-d H:i:s");
            $last->logout_time = date("Y-m-d H:i:s");
            $last->server_ip = $server_ip;
            $last->system_ip = $system_ip;
            $last->save(); 

            return $users;
        }
        else if($provider=='facebook')
        {
            $authUser = User::where('facebook_id', $user->id)->first();
            if ($authUser) {
                $last = new Last_login;
                $last->user_id_fk = $authUser->id;
                $last->login_time = date("Y-m-d H:i:s");
                $last->status = '1';
                $last->created_at = date("Y-m-d H:i:s");
                $last->updated_at = date("Y-m-d H:i:s");
                $last->logout_time = date("Y-m-d H:i:s");
                $last->server_ip = $server_ip;
                $last->system_ip = $system_ip;
                $last->save();    
                return $authUser;
            }
            if($user1 = User::where('email',$user->email)->first())
            {
                $last = new Last_login;
                $last->user_id_fk = $user1->id;
                $last->login_time = date("Y-m-d H:i:s");
                $last->status = '1';
                $last->created_at = date("Y-m-d H:i:s");
                $last->updated_at = date("Y-m-d H:i:s");
                $last->logout_time = date("Y-m-d H:i:s");
                $last->server_ip = $server_ip;
                $last->system_ip = $system_ip;
                $last->save();   
                return $user;
            }
            $users=new User();
            $users->name=$user->name;
            $users->email=$user->email;
            $users->profile_pic=$user->avatar;
            $users->role=2;
            $users->email_verify = 2;
            $users->facebook_id=$user->id;
            $users->save();

                $pdtls = new Personal_details();
                $pdtls->user_id_fk = $users->id;
                $pdtls->total_exp = 0;
                $pdtls->created_at = date("Y-m-d H:i:s");
                $pdtls->updated_at = date("Y-m-d H:i:s");
                $pdtls->save();

            $last = new Last_login;
            $last->user_id_fk = $users->id;
            $last->login_time = date("Y-m-d H:i:s");
            $last->status = '1';
            $last->created_at = date("Y-m-d H:i:s");
            $last->updated_at = date("Y-m-d H:i:s");
            $last->logout_time = date("Y-m-d H:i:s");
            $last->server_ip = $server_ip;
            $last->system_ip = $system_ip;
            $last->save(); 

            return $users;
            
        }
    }
}
