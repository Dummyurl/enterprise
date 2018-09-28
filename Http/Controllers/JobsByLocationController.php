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
use App\Model\Jobsby_Locations;
use App\Model\Footer_Skills;
use App\Model\Banners;

use Image;

class JobsByLocationController extends Controller{
    public function list(){
        $locs = Jobsby_Locations::all();
        return view('admin.jobsbylocs.list')->with('locs',$locs);
    }
    public function add_get()
    {
        return view('admin.jobsbylocs.add');
    }
    public function add_store(Request $request)
    {
         $rules=array('title'=>'required');
         $this->validate($request,$rules);
         $check = Jobsby_Locations::where('name',$request->title)->first();
         if(!empty($check)){
             $request->session()->flash('errormsg','Location already exists');
         }
         else{
            $location = new Jobsby_Locations();
            $location->name = $request->title;
            $location->save();

            $request->session()->flash('successmsg','Succesfully Inserted Record');
         }
         return redirect('admin/jobsbylocs/list');
    }
    public function edit($id)
    {
        $location = Jobsby_Locations::where('location_id',$id)->first();
        return view('admin.jobsbylocs.add',compact('location'));
    }
    public function update(Request $request,$id)
    {
        $rules = array('title'=>'required');
        $this->validate($request,$rules);
        $location =Jobsby_Locations::where('location_id',$id)->first();
        $location->name = $request->title;
        $location->save();

        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/jobsbylocs/list');
    }
    public function delete($id)
    {
        $location = Jobsby_Locations::where('location_id',$id)->first();
        $location->delete();

        return redirect('admin/jobsbylocs/list')->with('successmsg','Succesfully Deleted Record');
    }
}