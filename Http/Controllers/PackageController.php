<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Package;
use App\Model\Saver_package;
use App\Model\Job_post_package;
use App\Model\Cv_package;
use App\Model\Branding_package;
use App\Model\Addon_package;
use App\Model\Branding_enquiries;
use App\Model\User_package;
use App\Model\Addon_price;
use Helper;

class PackageController extends Controller
{
    public function saver_list()
    {
    	$saver = Package::where('type','1')->orderby('created_at','DESC')->get();
        $addon = Addon_package::all();
    	return view('admin.package.listsaver',compact('saver','addon'));
    }

    public function saver_create()
    {
        $addon = Addon_package::all();
    	return view('admin.package.addsaver',compact('addon'));
    }

    public function saver_store(Request $request)
    {
        //dd($request->addon_amount[175][0]);exit();

    	 $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
    	 $this->validate($request,$rules);
    	 $package = new Package;
         $package->type = '1';
         $package->item_code = Helper::item_code();
         $package->currency_type = $request->currency_type;
         $package->amount = $request->amount;
    	 $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->created_at = date("Y-m-d H:i:s");
         $package->updated_at = date("Y-m-d H:i:s");
    	 $package->save();
         $pack_id = $package->package_id;

         if(!empty($pack_id)){
            $saver = new Saver_package;
            $saver->package_id_fk = $pack_id;
            if(!empty($request->enterprise_pack)){
                $saver->enterprise_pack = $request->enterprise_pack;
            }
            if(!empty($request->regular_pack)){
                 $saver->regular_pack = $request->regular_pack;
            }
            if(!empty($request->cv_access)){
                $saver->cv_access = $request->cv_access;
            }
            if(!empty($request->profile_views)){
               $saver->profile_views = $request->profile_views;
            }
            if(!empty($request->email)){
                $saver->email = $request->email;
            }
            if(!empty($request->addon)){
                $saver->addon = implode(',', $request->addon);
            }
            if(!empty($request->job_expire)){
                $saver->job_expire = $request->job_expire;
            }

            $saver->created_at = date("Y-m-d H:i:s");
            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();

            if(!empty($request->addon))
            {
                $addons = new Addon_price();
                $addons->pack_type = 1;
                $addons->pack_id = $saver->saver_pack_id;
                $addons->package_id = $pack_id;
                if(!empty($request->addon_amount[174][0]))
                $addons->addon_id_174 = $request->addon_amount[174][0];
                if(!empty($request->addon_amount[175][0]))
                $addons->addon_id_175 = $request->addon_amount[175][0];
                if(!empty($request->addon_amount[176][0]))
                $addons->addon_id_176 = $request->addon_amount[176][0];
                if(!empty($request->addon_amount[177][0]))
                $addons->addon_id_177 = $request->addon_amount[177][0];
                if(!empty($request->addon_amount[178][0]))
                $addons->addon_id_178 = $request->addon_amount[178][0];
                $addons->created_at = date("Y-m-d H:i:s");
                $addons->updated_at = date("Y-m-d H:i:s");
                $addons->save();
            }
         }

        /* if(!empty($request->addon)){
             //$insertId = $industry_type->industry_type_id;
             foreach($request->addon as $key => $sub){
                $sub_industry_type = new Addon_package;
                $sub_industry_type->package_id_fk = $pack_id;
                $sub_industry_type->addon = $sub;
                $sub_industry_type->amount = $request->price[$key];
                $sub_industry_type->currency_type = $request->currency_type;
                $sub_industry_type->created_at = date("Y-m-d H:i:s");
                $sub_industry_type->updated_at = date("Y-m-d H:i:s");
                $sub_industry_type->save();    
             }
         }*/
         

    	 $request->session()->flash('message','Succesfully Inserted Record');
    	 return redirect('admin/saver/list');
    }

    public function saver_edit($id)
    {
    	$saver = Package::where('package_id',$id)->first();
        $addon = Addon_package::all();
    	return view('admin.package.addsaver',compact('saver','addon'));
    }

    public function saver_update(Request $request,$id)
    {
    	$rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
    	$this->validate($request,$rules);
    	$package =Package::where('package_id',$id)->first();
        $package->amount = $request->amount;
        $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();



            $saver = Saver_package::where('package_id_fk',$id)->first();
            if(!empty($request->enterprise_pack)){
                $saver->enterprise_pack = $request->enterprise_pack;
            }else{
                $saver->enterprise_pack = "0";
            }
            if(!empty($request->regular_pack)){
                 $saver->regular_pack = $request->regular_pack;
            }else{
                $saver->regular_pack = "0";
            }
            if(!empty($request->cv_access)){
                $saver->cv_access = $request->cv_access;
            }else{
                 $saver->cv_access = "0";
            }
            if(!empty($request->profile_views)){
               $saver->profile_views = $request->profile_views;
            }else{
                $saver->profile_views = "0";
            }
            if(!empty($request->email)){
                $saver->email = $request->email;
            }else{
                $saver->email = "0";
            }
            if(!empty($request->addon)){
                $saver->addon = implode(',', $request->addon);
            }else{
                $saver->addon=null;
            }
            if(!empty($request->job_expire)){
                $saver->job_expire = $request->job_expire;
            }

            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();
         
         if(Addon_price::where('pack_id',$saver->saver_pack_id)->where('pack_type',1)->first())
            {
               $addons = Addon_price::where('pack_id',$saver->saver_pack_id)->where('pack_type',1)->first();
               if(!empty($request->addon_amount[174][0]))
                $addons->addon_id_174 = $request->addon_amount[174][0];
                if(!empty($request->addon_amount[175][0]))
                $addons->addon_id_175 = $request->addon_amount[175][0];
                if(!empty($request->addon_amount[176][0]))
                $addons->addon_id_176 = $request->addon_amount[176][0];
                if(!empty($request->addon_amount[177][0]))
                $addons->addon_id_177 = $request->addon_amount[177][0];
                if(!empty($request->addon_amount[178][0]))
                $addons->addon_id_178 = $request->addon_amount[178][0];
                $addons->updated_at = date("Y-m-d H:i:s");
                $addons->save();
            }

        /*Addon_package::where('package_id_fk',$id)->delete();

        if(!empty($request->addon)){
             //$insertId = $industry_type->industry_type_id;
             foreach($request->addon as $key => $sub){
                $sub_industry_type = new Addon_package;
                $sub_industry_type->package_id_fk = $id;
                $sub_industry_type->addon = $sub;
                $sub_industry_type->amount = $request->price[$key];
                $sub_industry_type->currency_type = $request->currency_type;
                $sub_industry_type->created_at = date("Y-m-d H:i:s");
                $sub_industry_type->updated_at = date("Y-m-d H:i:s");
                $sub_industry_type->save();    
             }
         }*/

    	$request->session()->flash('message','Succesfully Updated Record');
    	return redirect('admin/saver/list');
    }

    public function saver_delete($id)
    {
         //Saver_package::where('package_id_fk',$id)->delete();
          //Addon_package::where('package_id_fk',$id)->delete();
        if( Package::where('package_id',$id)->where('status',"1")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"2"]);
            return redirect('admin/saver/list')->with('message','Succesfully Deactivated Record');
        }elseif(Package::where('package_id',$id)->where('status',"2")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"1"]);
            return redirect('admin/saver/list')->with('message','Succesfully Activated Record');
        }
    	
    }


     public function cv_list()
    {
        $saver = Package::where('type','2')->orderby('created_at','DESC')->get();
        return view('admin.package.listcv')->with('cv',$saver);
    }

    public function cv_create()
    {
        return view('admin.package.addcv');
    }

    public function cv_store(Request $request)
    {
         $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
         $this->validate($request,$rules);
         $package = new Package;
         $package->type = '2';
         $package->item_code = Helper::item_code();
         $package->amount = $request->amount;
         $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->created_at = date("Y-m-d H:i:s");
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();
         $pack_id = $package->package_id;

         if(!empty($pack_id)){
            $saver = new Cv_package;
            $saver->package_id_fk = $pack_id;
            
            if(!empty($request->cv_access)){
                $saver->cv_access = $request->cv_access;
            }
            if(!empty($request->profile_views)){
               $saver->profile_views = $request->profile_views;
            }
            if(!empty($request->email)){
                $saver->email = $request->email;
            }

            $saver->created_at = date("Y-m-d H:i:s");
            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();
         }

         

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/cv/list');
    }

    public function cv_edit($id)
    {
        $cv = Package::where('package_id',$id)->first();
        return view('admin.package.addcv',compact('cv'));
    }

    public function cv_update(Request $request,$id)
    {
        $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
        $this->validate($request,$rules);
        $package =Package::where('package_id',$id)->first();
        $package->amount = $request->amount;
        $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();



            $saver = Cv_package::where('package_id_fk',$id)->first();
            
            if(!empty($request->cv_access)){
                $saver->cv_access = $request->cv_access;
            }
            if(!empty($request->profile_views)){
               $saver->profile_views = $request->profile_views;
            }
            if(!empty($request->email)){
                $saver->email = $request->email;
            }

            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();
         


        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/cv/list');
    }

    public function cv_delete($id)
    {
         //Cv_package::where('package_id_fk',$id)->delete();
        //$package = Package::where('package_id',$id)->update(['status'=>"2"]);
        //return redirect('admin/cv/list')->with('message','Succesfully Deactivated Record');
        if( Package::where('package_id',$id)->where('status',"1")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"2"]);
            return redirect('admin/cv/list')->with('message','Succesfully Deactivated Record');
        }elseif(Package::where('package_id',$id)->where('status',"2")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"1"]);
            return redirect('admin/cv/list')->with('message','Succesfully Activated Record');
        }
    }


      public function job_list()
    {
        $saver = Package::where('type','3')->orderby('created_at','DESC')->get();
        $addon = Addon_package::all();
        return view('admin.package.listjob',compact('saver','addon'));
    }

    public function job_create()
    {
        $addon = Addon_package::all();
        return view('admin.package.addjob',compact('addon'));
    }

     public function job_store(Request $request)
    {
         $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
         $this->validate($request,$rules);
         $package = new Package;
         $package->type = '3';
         $package->item_code = Helper::item_code();
         $package->amount = $request->amount;
         $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->created_at = date("Y-m-d H:i:s");
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();
         $pack_id = $package->package_id;

         if(!empty($pack_id)){
            $saver = new Job_post_package;
            $saver->package_id_fk = $pack_id;
            if(!empty($request->job_posting)){
                $saver->job_posting = $request->job_posting;
            }
            
            if(!empty($request->pack_type)){
                $saver->pack_type = $request->pack_type;
            }
            if(!empty($request->addon)){
                $saver->addon = implode(',', $request->addon);
            }
            if(!empty($request->job_expire)){
                $saver->job_expire = $request->job_expire;
            }

            $saver->created_at = date("Y-m-d H:i:s");
            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();
         }

         if(!empty($request->addon))
            {
                $addons = new Addon_price();
                $addons->pack_type = 2;
                $addons->pack_id = $saver->job_post_pack_id;
                $addons->package_id = $pack_id;
                if(!empty($request->addon_amount[174][0]))
                $addons->addon_id_174 = $request->addon_amount[174][0];
                if(!empty($request->addon_amount[175][0]))
                $addons->addon_id_175 = $request->addon_amount[175][0];
                if(!empty($request->addon_amount[176][0]))
                $addons->addon_id_176 = $request->addon_amount[176][0];
                if(!empty($request->addon_amount[177][0]))
                $addons->addon_id_177 = $request->addon_amount[177][0];
                if(!empty($request->addon_amount[178][0]))
                $addons->addon_id_178 = $request->addon_amount[178][0];
                $addons->created_at = date("Y-m-d H:i:s");
                $addons->updated_at = date("Y-m-d H:i:s");
                $addons->save();
            }

        
             //$insertId = $industry_type->industry_type_id;
             /*foreach($request->addon as $key=>$sub){
                 if(!empty($sub)){
                $sub_industry_type = new Addon_package();
                $sub_industry_type->package_id_fk = $pack_id;
                $sub_industry_type->addon = $sub;
                $sub_industry_type->amount = $request->price[$key];
                $sub_industry_type->currency_type = $request->currency_type;
                $sub_industry_type->created_at = date("Y-m-d H:i:s");
                $sub_industry_type->updated_at = date("Y-m-d H:i:s");
                $sub_industry_type->save();    
             }
         }*/

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/job_post/list');
    }

    public function job_edit($id)
    {
        $saver = Package::where('package_id',$id)->first();
        //$addon = Addon_package::where('package_id_fk',$id)->get();
         $addon = Addon_package::all();
        return view('admin.package.addjob',compact('saver','addon'));
    }

    public function job_update(Request $request,$id)
    {

        $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
        $this->validate($request,$rules);
        $package =Package::where('package_id',$id)->first();
        $package->amount = $request->amount;
        $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();
         $pack_id = $package->package_id;
          
            $saver = Job_post_package::where('package_id_fk',$pack_id)->first();

            $saver->package_id_fk = $pack_id;
            if(!empty($request->job_posting)){
                $saver->job_posting = $request->job_posting;
            }
            
            if(!empty($request->pack_type)){
                $saver->pack_type = $request->pack_type;
            }
            if(!empty($request->addon)){
                $saver->addon = implode(',', $request->addon);
            }else{
                $saver->addon=null;
            }
            if(!empty($request->job_expire)){
                $saver->job_expire = $request->job_expire;
            }else{
                $saver->job_expire = 0;
            }
            $saver->created_at = date("Y-m-d H:i:s");
            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();        

            if(Addon_price::where('pack_id',$saver->job_post_pack_id)->where('pack_type',2)->first())
            {
               $addons = Addon_price::where('pack_id',$saver->job_post_pack_id)->where('pack_type',2)->first();
               if(!empty($request->addon_amount[174][0]))
                $addons->addon_id_174 = $request->addon_amount[174][0];
                if(!empty($request->addon_amount[175][0]))
                $addons->addon_id_175 = $request->addon_amount[175][0];
                if(!empty($request->addon_amount[176][0]))
                $addons->addon_id_176 = $request->addon_amount[176][0];
                if(!empty($request->addon_amount[177][0]))
                $addons->addon_id_177 = $request->addon_amount[177][0];
                if(!empty($request->addon_amount[178][0]))
                $addons->addon_id_178 = $request->addon_amount[178][0];
                $addons->updated_at = date("Y-m-d H:i:s");
                $addons->save();
            }
        //Addon_package::where('package_id_fk',$id)->delete();
       
        
             //print_r($request->addon);exit();
             //$insertId = $industry_type->industry_type_id;
             /*foreach($request->addon as $key=>$sub){
                if(!empty($sub)){
                $sub_industry_type = new Addon_package;
                $sub_industry_type->package_id_fk = $id;
                $sub_industry_type->addon = $sub;
                $sub_industry_type->amount = $request->price[$key];
                $sub_industry_type->currency_type = $request->currency_type;
                $sub_industry_type->created_at = date("Y-m-d H:i:s");
                $sub_industry_type->updated_at = date("Y-m-d H:i:s");
                $sub_industry_type->save();    
             }
         }*/

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/job_post/list');
    }

    public function job_delete($id)
    {
         //Job_post_package::where('package_id_fk',$id)->delete();
          //Addon_package::where('package_id_fk',$id)->delete();
        //$package = Package::where('package_id',$id)->update(['status'=>"2"]);
        //return redirect('admin/job_post/list')->with('message','Succesfully Deactivated Record');
        if( Package::where('package_id',$id)->where('status',"1")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"2"]);
            return redirect('admin/job_post/list')->with('message','Succesfully Deactivated Record');
        }elseif(Package::where('package_id',$id)->where('status',"2")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"1"]);
            return redirect('admin/job_post/list')->with('message','Succesfully Activated Record');
        }
    }


    public function branding_list()
    {
        $saver = Package::where('type','4')->orderby('created_at','DESC')->get();
        $addon = Addon_package::all();
        return view('admin.package.listbranding',compact('saver','addon'));
    }
	
	public function branding_requests()
    {
        $requests = Branding_enquiries::where('status','1')->orderby('created_at','DESC')->get();
        return view('admin.package.requestsbranding')->with('requests',$requests);
    }

    public function branding_create()
    {
        $addon = Addon_package::all();
        return view('admin.package.addbranding',compact('addon'));
    }

     public function branding_store(Request $request)
    {
       // print_r(Helper::item_code());exit();
         $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
         $this->validate($request,$rules);
         if($request->package_type == 1)
         {
            $rules1=array('job_expire'=>'required');
            $this->validate($request,$rules1);
         }
         $package = new Package;
         $package->type = '4';
         $package->item_code = Helper::item_code();
         $package->amount = $request->amount;
         $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->created_at = date("Y-m-d H:i:s");
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();
         $pack_id = $package->package_id;

         if(!empty($pack_id)){
            $saver = new Branding_package;
            $saver->package_id_fk = $pack_id;
            if(!empty($request->job_posting)){
                $saver->job_posting = $request->job_posting;
            }
            if(!empty($request->top_employer)){
                 $saver->top_employer = $request->top_employer;
            }
            if(!empty($request->microsite)){
                $saver->microsite = $request->microsite;
            }
            if(!empty($request->package_type)){
                $saver->package_type = $request->package_type;
            }
            if(!empty($request->addon)){
                $saver->addon = implode(',', $request->addon);
            }
            if(!empty($request->job_expire)){
                $saver->job_expire = $request->job_expire;
            }else{
                $saver->job_expire = "0";
            }
            if(!empty($request->microsite_type))
            {
                $saver->microsite_type = implode(',', $request->microsite_type);
            }
           
            $saver->created_at = date("Y-m-d H:i:s");
            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();
         }

         if(!empty($request->addon))
            {
                $addons = new Addon_price();
                $addons->pack_type = 3;
                $addons->pack_id = $saver->branding_pack_id;
                $addons->package_id = $pack_id;
                if(!empty($request->addon_amount[174][0]))
                $addons->addon_id_174 = $request->addon_amount[174][0];
                if(!empty($request->addon_amount[175][0]))
                $addons->addon_id_175 = $request->addon_amount[175][0];
                if(!empty($request->addon_amount[176][0]))
                $addons->addon_id_176 = $request->addon_amount[176][0];
                if(!empty($request->addon_amount[177][0]))
                $addons->addon_id_177 = $request->addon_amount[177][0];
                if(!empty($request->addon_amount[178][0]))
                $addons->addon_id_178 = $request->addon_amount[178][0];
                $addons->created_at = date("Y-m-d H:i:s");
                $addons->updated_at = date("Y-m-d H:i:s");
                $addons->save();
            }

         
             //$insertId = $industry_type->industry_type_id;
             /*foreach($request->addon as $key=> $sub){
                if(!empty($sub)){
                $sub_industry_type = new Addon_package;
                $sub_industry_type->package_id_fk = $pack_id;
                $sub_industry_type->addon = $sub;
                $sub_industry_type->amount = $request->price[$key];
                $sub_industry_type->currency_type = $request->currency_type;
                $sub_industry_type->created_at = date("Y-m-d H:i:s");
                $sub_industry_type->updated_at = date("Y-m-d H:i:s");
                $sub_industry_type->save();    
             }
         }*/

         $request->session()->flash('message','Succesfully Inserted Record');
         return redirect('admin/branding/list');
    }

    public function branding_edit($id)
    {
        $saver = Package::where('package_id',$id)->first();
        //$addon = Addon_package::where('package_id_fk',$id)->get();
        $addon = Addon_package::all();
        return view('admin.package.addbranding',compact('saver','addon'));
    }

    public function branding_update(Request $request,$id)
    {
        $rules=array('amount'=>'required','validity_type'=>'required','validity'=>'required');
        $this->validate($request,$rules);
        if($request->package_type == 1)
         {
            $rules1=array('job_expire'=>'required');
            $this->validate($request,$rules1);
         }
        $package =Package::where('package_id',$id)->first();
        $package->amount = $request->amount;
        $package->currency_type = $request->currency_type;
         $package->validity_type = $request->validity_type;
         $package->validity = $request->validity;
         $package->updated_at = date("Y-m-d H:i:s");
         $package->save();



            $saver = Branding_package::where('package_id_fk',$id)->first();
             if(!empty($request->job_posting)){
                $saver->job_posting = $request->job_posting;
            }
            if(!empty($request->top_employer)){
                 $saver->top_employer = $request->top_employer;
            }
            if(!empty($request->microsite)){
                $saver->microsite = $request->microsite;
            }
            if(!empty($request->package_type)){
                $saver->package_type = $request->package_type;
            }
            if(!empty($request->addon)){
                $saver->addon = implode(',', $request->addon);
            } 
            if(!empty($request->job_expire)){
                $saver->job_expire = $request->job_expire;
            }else{
                $saver->job_expire = 0;
            }
            if(!empty($request->microsite_type))
            {
                $saver->microsite_type = implode(',', $request->microsite_type);
            }else{
                $saver->microsite_type = null;
            }          

            $saver->updated_at = date("Y-m-d H:i:s");
            $saver->save();
         
            if(Addon_price::where('pack_id',$saver->branding_pack_id)->where('pack_type',3)->first())
            {
               $addons = Addon_price::where('pack_id',$saver->branding_pack_id)->where('pack_type',3)->first();
               if(!empty($request->addon_amount[174][0]))
                $addons->addon_id_174 = $request->addon_amount[174][0];
                if(!empty($request->addon_amount[175][0]))
                $addons->addon_id_175 = $request->addon_amount[175][0];
                if(!empty($request->addon_amount[176][0]))
                $addons->addon_id_176 = $request->addon_amount[176][0];
                if(!empty($request->addon_amount[177][0]))
                $addons->addon_id_177 = $request->addon_amount[177][0];
                if(!empty($request->addon_amount[178][0]))
                $addons->addon_id_178 = $request->addon_amount[178][0];
                $addons->updated_at = date("Y-m-d H:i:s");
                $addons->save();
            }


        /*Addon_package::where('package_id_fk',$id)->delete();

        
             //$insertId = $industry_type->industry_type_id;
             foreach($request->addon as $key=> $sub){
                if(!empty($sub)){
                $sub_industry_type = new Addon_package;
                $sub_industry_type->package_id_fk = $id;
                $sub_industry_type->addon = $sub;
                $package->currency_type = $request->currency_type;
                $sub_industry_type->amount = $request->price[$key];
                $sub_industry_type->created_at = date("Y-m-d H:i:s");
                $sub_industry_type->updated_at = date("Y-m-d H:i:s");
                $sub_industry_type->save();    
             }
         }*/

        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/branding/list');
    }

    public function branding_delete($id)
    {
         //Branding_package::where('package_id_fk',$id)->delete();
          //Addon_package::where('package_id_fk',$id)->delete();
        //$package = Package::where('package_id',$id)->update(['status'=>"2"]);
        //return redirect('admin/branding/list')->with('message','Succesfully Deactivated Record');
        if( Package::where('package_id',$id)->where('status',"1")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"2"]);
            return redirect('admin/branding/list')->with('message','Succesfully Deactivated Record');
        }elseif(Package::where('package_id',$id)->where('status',"2")->first())
        {
            $package = Package::where('package_id',$id)->update(['status'=>"1"]);
            return redirect('admin/branding/list')->with('message','Succesfully Activated Record');
        }
    }
	public function branding_approve($id){
		Branding_enquiries::where('enquiry_id',$id)->update(['status'=>2]);
		return redirect('admin/branding/requests')->with('message','Succesfully Approved');
	}

    public function package_requests()
    {
        $package = User_package::all();
        $addon = Addon_package::all();
        return view('admin.package.package',compact('package','addon'));
    }
    public function package_approve($id)
    {
        $package = User_package::where('user_package_id',$id)->first();
        $package_id_fk=$package->package_id_fk;
        // dd($package_id_fk);
        $package->activated_at = date('Y-m-d H:i:s');
        $package->status = 2;
        $package->expiry_date = Helper::get_expiry_date($package_id_fk);
        $package->updated_at = date('Y-m-d H:i:s');
        $package->save();
        
        return redirect('admin/package/requests')->with('message','Succesfully Approved');
    }

    public function test()
    {
        $jsonurl = "https://api.coursera.org/api/courses.v1";
        //$json = file_get_contents($jsonurl);
        
        $data = json_decode(file_get_contents($jsonurl), true);

        return $data;
    }
}
