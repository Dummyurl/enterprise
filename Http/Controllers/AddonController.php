<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;
use App\Model\Notice;

use App\Model\Addon_package;

use Image;
use Helper;

class AddonController extends Controller{

	public function list(){
		$addon = Addon_package::all();
    	return view('admin.package.addon_list',compact('addon'));
	}
	public function add(){
		return view('admin.package.addon_add');
	}
	public function save_addon(Request $request){
		//print_r($request->all());exit();
		$addon = new Addon_package();
		$addon->addon = $request->addon_name;
		$addon->amount = $request->amount;
		$addon->currency_type = $request->currency_type;
		$addon->save();
		return redirect('admin/addon/list');
	}
	public function edit($id){
		$addon = Addon_package::where('addon_id',$id)->first();
		return view('admin.package.addon_add',compact('addon'));
	}
	public function update_addon(Request $request)
	{
		//print_r($request->all());exit();
		$addon = Addon_package::where('addon_id',$request->id)->first();
		$addon->amount = $request->amount;
		$addon->currency_type = $request->currency_type;
		$addon->save();
		return redirect('admin/addon/list');
	}
	public function notice()
	{
		$notice = Notice::orderBy('id','DSC')->first();
		return view('admin.landing.notice',compact('notice'));
	}
	public function savenotice(Request $request)
	{
		$notice = Notice::where('id',1)->first();
		$notice->notice_text = $request->notice_text;
		$notice->save();
		return redirect('admin/notice');
	}

}