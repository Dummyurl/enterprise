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
use App\Model\Staff_Menus;
use App\Model\Staff_Mappings;

use Image;

class StaffGroupController extends Controller{
    public function list(){
        $list = Staff_group::all();
        return view('admin.staffgroup.list')->with('list',$list);
    }
    public function add_get()
    {
        return view('admin.staffgroup.add');
    }
    public function add_store(Request $request)
    {
        $empspeak = new Staff_group();
        $empspeak->group_name = $request->name;
        $empspeak->active = $request->status;
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Inserted Record');
        return redirect('admin/staffgroup/list');
    }
    public function edit($id)
    {
        $speak = Staff_group::where('id',$id)->first();
        return view('admin.staffgroup.add',compact('speak'));
    }
    public function update(Request $request,$id)
    {
        $empspeak = Staff_group::where('id',$id)->first();
        $empspeak->group_name = $request->name;
        $empspeak->active = $request->status;
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/staffgroup/list');
    }
    public function permissions($id)
    {
        $menus = Staff_Menus::where('parent_id',0)->where('active',1)->get();
        $permission = Staff_Mappings::where('group_id_fk',$id)->pluck('menu_id')->toArray();
        return view('admin.staffgroup.permissions',compact('menus','id','permission'));
    }
    public function permission_save(Request $request){
        $staffgrpid = $request->staffgroupId;
        $menus = $request->menu;
        if(count($menus)){
            Staff_Mappings::where('group_id_fk',$staffgrpid)->delete();
            foreach($menus as $key => $n){
                $staff_maps = new Staff_Mappings();
                $staff_maps->menu_id = $menus[$key];
                $staff_maps->group_id_fk = $staffgrpid;
                $staff_maps->save();
            }
        }
        $request->session()->flash('successmsg','Permissions set successfully');
        return redirect('admin/staffgroup/list');
    }
    public function staff_list($id)
    {
        $staff = Staff_details::where('group_id_fk',$id)->get();
        return view('admin.staffgroup.staff_list',compact('staff'));
    }
}