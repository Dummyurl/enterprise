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
use App\Model\Jobsby_skills;
use App\Model\Footer_Skills;
use App\Model\Banners;

use Image;

class JobsBySkillController extends Controller{
    public function list(){
        $locs = Jobsby_skills::all();
        return view('admin.jobsbyskills.list')->with('locs',$locs);
    }
    public function add_get()
    {
        return view('admin.jobsbyskills.add');
    }
    public function add_store(Request $request)
    {
         $rules=array('title'=>'required');
         $this->validate($request,$rules);
         $check = Jobsby_skills::where('skill_name',$request->title)->first();
         if(!empty($check)){
             $request->session()->flash('errormsg','Skill already exists');
         }
         else{
            $location = new Jobsby_skills();
            $location->skill_name = $request->title;
            $location->save();

            $request->session()->flash('successmsg','Succesfully Inserted Record');
         }
         return redirect('admin/jobsbyskills/list');
    }
    public function edit($id)
    {
        $location = Jobsby_skills::where('skill_id',$id)->first();
        return view('admin.jobsbyskills.add',compact('location'));
    }
    public function update(Request $request,$id)
    {
        $rules = array('title'=>'required');
        $this->validate($request,$rules);
        $location =Jobsby_skills::where('skill_id',$id)->first();
        $location->skill_name = $request->title;
        $location->save();

        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/jobsbyskills/list');
    }
    public function delete($id)
    {
        $location = Jobsby_skills::where('skill_id',$id)->first();
        $location->delete();

        return redirect('admin/jobsbyskills/list')->with('successmsg','Succesfully Deleted Record');
    }
}