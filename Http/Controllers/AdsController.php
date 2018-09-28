<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;

use App\Model\Manage_Ads;

use Image;

class AdsController extends Controller{
    public function list(){
        $list = Manage_Ads::all();
        return view('admin.ads.list')->with('list',$list);
    }
    public function add_get()
    {
        return view('admin.ads.add');
    }
    public function add_store(Request $request)
    {
        $empspeak = new Manage_Ads(); //238*476
        if($request->hasFile('adimage')){
            $destination = 'uploads/images';  
            $file = $request->file('adimage');
            $img = Image::make($file->getRealPath())->resize(238,476);
            $path = $destination. "/" .time().'-'.$file->getClientOriginalName();
            $img->save($path);
            $empspeak->ad_img = $path;
        }
        if(!empty($request->adlink)){
            $empspeak->ad_link = $request->adlink;
        }else{
            $empspeak->ad_link = null;
        }
        
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Inserted Record');
        return redirect('admin/ads/list');
    }
    public function edit($id)
    {
        $speak = Manage_Ads::where('ad_id',$id)->first();
        return view('admin.ads.add',compact('speak'));
    }
    public function update(Request $request,$id)
    {
        $empspeak = Manage_Ads::where('ad_id',$id)->first();
        if($request->hasFile('adimage')){
            $destination = 'uploads/images';  
            $file = $request->file('adimage');
            $img = Image::make($file->getRealPath())->resize(238,476);
            $path = $destination. "/" .time().'-'.$file->getClientOriginalName();
            $img->save($path);
            $empspeak->ad_img = $path;
        }
        $empspeak->ad_link = $request->adlink;
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/ads/list');
    }
    public function delete($id)
    {
       // print_r("expression");exit();
        $location = Manage_Ads::where('ad_id',$id)->first();
        $location->delete();

        return redirect('admin/ads/list')->with('successmsg','Succesfully Deleted Record');
    }
    public function deactivte($id)
    {
        $location = Manage_Ads::where('ad_id',$id)->first();
        $location->status = 2;
        $location->save();
        return redirect('admin/ads/list')->with('successmsg','Succesfully deactivted Record');
    }
    public function activate($id)
    {
        $location = Manage_Ads::where('ad_id',$id)->first();
        $location->status = 1;
        $location->save();
        return redirect('admin/ads/list')->with('successmsg','Succesfully activated Record');
    }
}