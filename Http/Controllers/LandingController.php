<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;

use App\Model\States;
use App\Model\Landing_Locations;
use App\Model\Landing_Menus;
use App\Model\Features;
use App\Model\Footer_Locations;
use App\Model\Footer_Skills;
use App\Model\Bulk_Cvs;
use App\Model\Banners;
use App\Model\Referal_Requests;
use App\User;
use App\Model\Mail_merge;
use Mail;
use Image;

class LandingController extends Controller{
    public function locations_get(){
    	//$indianstates = States::where('country_id',101)->get();
        $indianstates = States::orderBy('name')->get();
    	$locations = Landing_Locations::all();
        return view('admin.landing.locations',compact('indianstates','locations'));
    }
    public function locations_save(Request $request){
        $locations = Landing_Locations::all();
        $cnt = count($locations);
        $cnt = $cnt + 1;
    	$states = array();
    	for($mn=1; $mn<$cnt; $mn++){
    		$name = "location".$mn;
    		$stname = $request->$name;
    		array_push($states,$stname);
    	}
    	if(count($states)==count(array_count_values($states))){
    		Landing_Locations::truncate();
    		for($mn=1; $mn<$cnt; $mn++){
    			$name = "location".$mn;
    			$stname = $request->$name;
    			$locs = new Landing_Locations();
    			$locs->created_at = date("Y-m-d H:i:s");
    			$locs->location_name = $stname;
    			$locs->save();
    		}
    	return redirect('admin/landing/locations')->with('successmsg','Your selection saved successfully');
    	}
    	else{
    	return redirect('admin/landing/locations')->with('errormsg','Repeated state selection will not be allowed');
    	}
    }
    public function menus_get(){
        $menus = Landing_Menus::where('menu_id','<>',4)->get();
        //dd($menus);exit();
        return view('admin.landing.menus',compact('menus'));
    }
    public function menus_save(Request $request){
        //print_r($request->all());exit();
        /*$cnt = Landing_Menus::where('menu_id','<>',4)->count();
        $menu = Landing_Menus::where('menu_id','<>',4)->get();
        for($mn=1; $mn<=$cnt; $mn++){
            $name = "menu_name_".$mn;
            $statval = $request->$name;
            
            Landing_Menus::where('id',$mn)->update(['status'=>$statval]);
        }*/
       // dd($menu);exit();
        foreach ($request->id as $key=>$m) {
            if(Landing_Menus::where('id',$m)->first())
            {
               $res = Landing_Menus::where('id',$m)->first();
               $res->status = $request->menu_name[$key];
                $res->save();
            }

            
        }
      return redirect('admin/landing/menus')->with('successmsg','Your selection saved successfully');
    }
    public function featurelist1(){
        $features = Features::where('type','1')->orderby('id','DESC')->get();
        return view('admin.feature.listfeature1')->with('feature',$features);
    }
    public function feature_create1()
    {
        return view('admin.feature.addfeature1');
    }
    public function feature_store1(Request $request)
    {
         $rules=array('title'=>'required');
         $this->validate($request,$rules);
         $feature = new Features();
         $feature->title = $request->title;
         $feature->type = '1';
         $feature->save();

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/jsfeature/list');
    }
    public function feature_edit1($id)
    {
        $feature = Features::where('id',$id)->first();
        return view('admin.feature.addfeature1',compact('feature'));
    }
    public function feature_update1(Request $request,$id)
    {
        $rules = array('title'=>'required');
        $this->validate($request,$rules);
        $feature =Features::where('id',$id)->first();
        $feature->title = $request->title;
        $feature->save();

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/jsfeature/list');
    }
    public function feature_delete1($id)
    {
        $feature = Features::where('id',$id)->first();
        $feature->delete();

        return redirect('admin/jsfeature/list')->with('message','Succesfully Deleted Record');
    }
    public function featurelist2(){
        $features = Features::where('type','2')->orderby('id','DESC')->get();
        return view('admin.feature.listfeature2')->with('feature',$features);
    }
    public function feature_create2()
    {
        return view('admin.feature.addfeature2');
    }
    public function feature_store2(Request $request)
    {
         $rules=array('title'=>'required');
         $this->validate($request,$rules);
         $feature = new Features();
         $feature->title = $request->title;
         $feature->type = '2';
         $feature->save();

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/empfeature/list');
    }
    public function feature_edit2($id)
    {
        $feature = Features::where('id',$id)->first();
        return view('admin.feature.addfeature2',compact('feature'));
    }
    public function feature_update2(Request $request,$id)
    {
        $rules = array('title'=>'required');
        $this->validate($request,$rules);
        $feature =Features::where('id',$id)->first();
        $feature->title = $request->title;
        $feature->save();

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/empfeature/list');
    }
    public function feature_delete2($id)
    {
        $feature = Features::where('id',$id)->first();
        $feature->delete();

        return redirect('admin/empfeature/list')->with('message','Succesfully Deleted Record');
    }
    public function floc_list()
    {
        $location = Footer_Locations::orderby('location_id','DESC')->get();
        return view('admin.flocs.list')->with('location',$location);
    }
    public function floc_add()
    {
        return view('admin.flocs.add');
    }
    public function floc_add_save(Request $request)
    {
        $rules = array('location'=>'required');
        $this->validate($request,$rules);
        $location = new Footer_Locations();
        $location->name = $request->location;
        $location->updated_at = date("Y-m-d H:i:s");
        $location->save();

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/flocs/list');
    }
    public function floc_edit($id)
    {
        $location = Footer_Locations::where('location_id',$id)->first();
        return view('admin.flocs.add',compact('location'));
    }
    public function floc_update(Request $request,$id)
    {
        $rules = array('location'=>'required');
        $this->validate($request,$rules);
        $location =Footer_Locations::where('location_id',$id)->first();
        $location->name = $request->location;
        $location->updated_at = date("Y-m-d H:i:s");
        $location->save();

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/flocs/list');
    }
    public function floc_delete($id)
    {
        $location = Footer_Locations::where('location_id',$id)->delete();
        return redirect('admin/flocs/list')->with('message','Succesfully Deleted Record');
    }
    public function fskills_list()
    {
        $skill = Footer_Skills::orderby('skill_id','DESC')->get();
        return view('admin.fskills.list')->with('skill',$skill);
    }
    public function fskills_add()
    {
        return view('admin.fskills.add');   
    }
    public function fskills_add_save(request $request)
    {
        $f = new Footer_Skills();
        $f->skill_name = $request->skill;
        $f->save(); 
        $request->session()->flash('message','Succesfully Added Record');
        return redirect('admin/fskills/list');
    }
    public function fskills_edit($id)
    {
        $skill = Footer_Skills::where('skill_id',$id)->first();
        return view('admin.fskills.add',compact('skill'));
    }
    public function fskills_update(Request $request,$id)
    {
        //print_r($request->all());exit();
        $rules = array('skill'=>'required');
        $this->validate($request,$rules);
        $location =Footer_Skills::where('skill_id',$id)->first();
        $location->skill_name = $request->skill;
        $location->status = $request->status;
        $location->updated_at = date("Y-m-d H:i:s");
        $location->save();

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/fskills/list');
    }
    public function fskills_delete($id)
    {
        Footer_Skills::where('skill_id',$id)->delete();
        return redirect('admin/fskills/list')->with('successmsg','Succesfully Deleted Record');
    }
    public function new_location()
    {
        //$indianstates = States::where('country_id',101)->get();
         $indianstates = States::orderBy('name')->get();
        return view('admin.landing.addlocation',compact('indianstates'));
    }
    public function location_save(Request $request)
    {
        $name = $request->name;
        $check = Landing_Locations::where('location_name',$name)->first();
        if(!empty($check)){
            $request->session()->flash('errormsg','Already exist');
            return redirect('admin/landing/locations');
        }
        else{
            $landing_loc = new Landing_Locations();
            $landing_loc->location_name = $name;
            $landing_loc->save();

            $request->session()->flash('successmsg','Success');
            return redirect('admin/landing/locations');
        }
    }
    public function location_edit($id)
    {
        $indianstates = States::orderBy('name')->get();
        $check = Landing_Locations::where('landing_id',$id)->first();
        return view('admin.landing.addlocation',compact('indianstates','check'));
    }
    public function update_location(Request $request)
    {
        $check = Landing_Locations::where('landing_id',$request->id)->first();
        $check->location_name = $request->name;
        $check->save();
        $request->session()->flash('successmsg','Success');
            return redirect('admin/landing/locations');
    }
    public function location_delete($id)
    {
        $check = Landing_Locations::where('landing_id',$id)->delete();
        return redirect('admin/landing/locations')->with('successmsg','Record Deleted Succesfully');
    }
    public function bottom_banner(Request $request){
		 $banner = Banners::where('id','1')->first();
         return view('admin.landing.bottom_banner',compact('banner'));
    }
	public function banner_save(Request $request){
		$mime_validate = Validator::make($request->all(),['b_banner' => 'required|mimes:jpg,jpeg,png']);
        if ($mime_validate->fails()){
           return redirect('admin/landing/bottom-banner')->with('errormsg','Please choose a valid file');
        }
		if($request->hasFile('b_banner'))
        {
                $b_img = $request->file('b_banner');
                $destination = 'uploads/images';  
                $thumb = $b_img;
                $img = Image::make($thumb->getRealPath())->resize(1400,467);
                $path = $destination. "/" .time().'-'.$b_img->getClientOriginalName();
                $img->save($path);
				Banners::where('id','1')->update(["path"=>$path]);
				return redirect('admin/landing/bottom-banner')->with('successmsg','Success');
        }
	}
    public function bulk_cvs(Request $request){
         $cvs = Bulk_Cvs::all();
         $setting = Landing_Menus::where('id',16)->first();
         return view('admin.landing.bulk_cvs',compact('cvs','setting'));
    }
    public function cv_uploads(Request $request){
         $cvs = Bulk_Cvs::all();
         return view('admin.landing.cv_bulk_upload',compact('cvs'));
    }
    public function cvs_save(Request $request){
        $mime_validate = Validator::make($request->all(),['cv_name.*' => 'required|mimes:doc,pdf,docx|max:2048']);
        if ($mime_validate->fails()){
           return redirect('admin/landing/bulk-cvs')->with('errormsg','Please choose a valid file');
        }
        if($request->hasFile('cv_name'))
        {
            $cvs = $request->file('cv_name');
            foreach($cvs as $cv){
            $destination = 'uploads/resume';  
            $timevar = time();
            $cv->move($destination, $destination. "/" .$timevar.'-'.$cv->getClientOriginalName());
            $cvslocation = $destination. "/" .$timevar.'-'.$cv->getClientOriginalName();

            $bulkcvs = new Bulk_Cvs();
            $bulkcvs->cv_path = $cvslocation;
            $bulkcvs->save();
            }
            return redirect('admin/landing/bulk-cvs')->with('sucessmsg','Success');
        }
    }
    public function bulk_delete($id){
         Bulk_Cvs::where('cv_id',$id)->delete();
         return redirect('admin/landing/bulk-cvs')->with('sucessmsg','Success');
    }
    public function setting_save(Request $request){
        $getrow = Landing_Menus::where('id',16)->first();
        $getrow->status = $request->status;
        $getrow->save();
        return redirect('admin/landing/bulk-cvs')->with('sucessmsg','Success');   
    }
    public function mm_get(Request $request){
        $employees = User::where('role','2')->where('enabled',1)->get();
        $employers = User::where('role','3')->where('enabled',1)->get();
        $referals = Referal_Requests::all();
        $mail_merge = Mail_merge::all();
        return view('admin.landing.mail_merge',compact('employees','employers','referals','mail_merge'));
    }
    public function mm_set(Request $request){
        
        $type = $request->group;
        $messge = $request->message;
        if($type == 1){
            $users = Referal_Requests::all();
        }
        if($type == 2){
            $users = User::where('role','2')->where('enabled',1)->get();
        }
        if($type == 3){
            $users = User::where('role','3')->where('enabled',1)->get();
        }else{

        }

        foreach($users as $user){
            $mail_data = array(
                'email' => $user->email,
                'name' => $user->name,
                'msg' => $messge,
            );

            Mail::send('email.mail_merge', $mail_data, function ($message) use ($mail_data) {
                         $message->subject('New message from Enterprise Admin')
                                 ->from('developer10@indglobal-consulting.com')
                                 ->bcc("dev85@indglobal-consulting.com")
                                 ->to($mail_data['email']);
            });

        }
        $mail_merge = new Mail_merge();
        $mail_merge->message = $messge;
        $mail_merge->type =$type;
        $mail_merge->save();

        $employees = User::where('role','2')->where('enabled',1)->get();
        $employers = User::where('role','3')->where('enabled',1)->get();
        $referals = Referal_Requests::all();
        $mail_merge = Mail_merge::all();
        return view('admin.landing.mail_merge',compact('employees','employers','referals','mail_merge'));
    }
    public function home_banner()
    {
        $banner = Banners::where('id','1')->first();
         return view('admin.landing.home_banner',compact('banner'));
    }
    public function home_banner_save(Request $request)
    {//print_r($request->all());exit();
        
        if($request->hasFile('b_banner'))
        {
            $mime_validate = Validator::make($request->all(),['b_banner' => 'required|mimes:jpg,jpeg,png']);
            if ($mime_validate->fails()){
               return redirect('admin/landing/home_banner')->with('errormsg','Please choose a valid file');
            }
                $b_img = $request->file('b_banner');
                $destination = 'uploads/images';  
                $thumb = $b_img;
                $img = Image::make($thumb->getRealPath())->resize(1400,467);
                $path = $destination. "/" .time().'-'.$b_img->getClientOriginalName();
                $img->save($path);
                Banners::where('id','1')->update(["home_banner"=>$path]);
                
        }
        Banners::where('id','1')->update(["main_heading"=>$request->main_heading,"sub_heading"=>$request->sub_heading]);
        return redirect('admin/landing/home_banner')->with('successmsg','Success');

    }
    public function middle_banner_save(Request $request)
    {
        $mime_validate = Validator::make($request->all(),['middle_banner' => 'required|mimes:jpg,jpeg,png']);
        if ($mime_validate->fails()){
           return redirect('admin/landing/bottom-banner')->with('errormsg','Please choose a valid file');
        }
        if($request->hasFile('middle_banner'))
        {
                $b_img = $request->file('middle_banner');
                $destination = 'uploads/images';  
                $thumb = $b_img;
                $img = Image::make($thumb->getRealPath())->resize(1400,467);
                $path = $destination. "/" .time().'-'.$b_img->getClientOriginalName();
                $img->save($path);
                Banners::where('id','1')->update(["middle_banner"=>$path]);
                return redirect('admin/landing/home_banner')->with('successmsg','Success');
        }
    }
}