<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;
use Image;
use App\Model\Benefits;
use App\Model\Package_rules;
use App\Model\Package_content;


class BenifitController extends Controller{
	public function regular(){
		$benefit = Benefits::where('type',1)->get();
		return view('admin.pack_benifit.regular',compact('benefit'));
	}
	public function enterprise(){
		$benefit = Benefits::where('type',2)->get();
		return view('admin.pack_benifit.enterprise',compact('benefit'));
	}
	public function add_benifit(){
		return view('admin.pack_benifit.add_benifit');
	}
	public function save_benifit(request $request){
		//print_r($request->all());exit();
		$benefit = new Benefits();
		$benefit->benefit = $request->benefit;
		$benefit->type = $request->benefit_type;
		$benefit->save();
		if($request->benefit_type == 1)
		{
			return redirect('admin/benifit/regular');
		}elseif($request->benefit_type == 2){
			return redirect('admin/benifit/enterprise');
		}
	}
	public function delete($id)
	{
		Benefits::where('id',$id)->delete();
		return back();
	}
	public function edit($id)
	{
		$benefit = Benefits::where('id',$id)->first();
		return view('admin.pack_benifit.add_benifit',compact('benefit'));
	}
	public function update_benifit(Request $request)
	{
		//print_r($request->all());exit();
		$benefit = Benefits::where('id',$request->id)->first();
		$benefit->benefit = $request->benefit;
		$benefit->save();
		if($request->benefit_type == 1)
		{
			return redirect('admin/benifit/regular');
		}elseif($request->benefit_type == 2){
			return redirect('admin/benifit/enterprise');
		}
	}
	public function add_rules()
	{
		$content = Package_content::first();
		return view('admin.pack_benifit.add_rules',compact('content'));
	}
	public function save_rules(Request $request)
	{
		$rules = new Package_rules();
		$rules->rules = $request->rules;
		$rules->type = $request->rules_type;
		$rules->save();
		if($request->rules_type == 1)
		{
			return redirect('admin/rules/saver');
		}elseif($request->rules_type == 2){
			return redirect('admin/rules/cv_access');
		}
		elseif($request->rules_type == 3){
			return redirect('admin/rules/job_post');
		}
		elseif($request->rules_type == 4){
			return redirect('admin/rules/branding');
		}
	}
	public function edit_rules($id)
	{
		$data = Package_rules::where('id',$id)->first();
		return view('admin.pack_benifit.add_rules',compact('data'));
	}
	public function update_rules(Request $request)
	{
		$rules = Package_rules::where('id',$request->id)->first();
		$rules->rules = $request->rules;
		$rules->type = $request->rules_type;
		$rules->save();
		if($request->rules_type == 1)
		{
			return redirect('admin/rules/saver');
		}elseif($request->rules_type == 2){
			return redirect('admin/rules/cv_access');
		}
		elseif($request->rules_type == 3){
			return redirect('admin/rules/job_post');
		}
		elseif($request->rules_type == 4){
			return redirect('admin/rules/branding');
		}
	}
	public function delete_rules($id)
	{
		Package_rules::where('id',$id)->delete();
		return back();
	}
	public function saver()
	{
		$data = Package_rules::where('type',1)->get();
		return view('admin.pack_benifit.saver',compact('data'));
	}
	public function cv_access()
	{
		$data = Package_rules::where('type',2)->get();
		return view('admin.pack_benifit.cv_access',compact('data'));
	}
	public function job_post()
	{
		$data = Package_rules::where('type',3)->get();
		return view('admin.pack_benifit.job_post',compact('data'));
	}
	public function branding()
	{
		$data = Package_rules::where('type',4)->get();
		return view('admin.pack_benifit.branding',compact('data'));
	}

	public function package_content_add(Request $request)
	{
		//print_r($request->file('image'));exit();
		$content = Package_content::where('id',1)->first();
		if($request->hasFile('image')){
			//print_r("expression");exit();
            $destination = 'uploads/images';  
            $file = $request->file('image');
            $img = Image::make($file->getRealPath())->resize(1256,233);
            $path = $destination. "/" .time().'-'.$file->getClientOriginalName();
            $img->save($path);
            $content->image = $path;
        }
		$content->text = $request->text;
		$content->save();
		return view('admin.pack_benifit.add_rules',compact('content'));
	}

}