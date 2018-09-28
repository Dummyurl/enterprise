<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use Mail;
use App\Model\Job_post;
use App\Model\Package;
use App\Model\Job_post_package;
use App\Model\Branding_package;
use App\Model\User_package;
use App\Model\ShoppingCart;
use App\Model\Countries;
use App\Model\IndustryType;
use App\Model\Branding_enquiries;
use App\Model\Benefits;
use App\Model\Addon_package;
use App\Model\Microsite_details;
use Helper;

class ProductController extends Controller{
	public function product_home(){
		//$user = Auth::user()->id;
		//print_r($user);exit();
		$featured = Job_post::where('featured_job','2')->where('status','1')->distinct()->get();
		$saver = Package::where('type','1')->where('status','1')->get();
		$cv = Package::where('type','2')->where('status','1')->get();
		$job = Package::where('type','3')->where('status','1')->get();
		$enterprise = Job_post_package::where('pack_type','1')->get();
		$regular = Job_post_package::where('pack_type','2')->get();
		$r_benifit = Benefits::where('type',1)->get();
		$e_benifit = Benefits::where('type',2)->get();
		$addon = Addon_package::all();
		//echo json_encode($regular);
		//$saver = Package::where('type','1')->get();
		if(Auth::check()){
		   return view('our-product',compact('featured','saver','cv','job','enterprise','regular','r_benifit','e_benifit','addon'));
		}
		else{
		   $country = Countries::all();
		   $industry = IndustryType::orderBy('industry_type_name')->get();
		   return view('employer/register',compact('country','industry'));
		}
	}
	public function branding_package(){
		//$user = Auth::user()->id;
		//print_r($user);exit();
		$branding = Package::where('type','4')->where('status','1')->get();
		$job = Branding_package::where('package_type','1')->orderby('created_at','desc')->limit(4)->get();
		$microsite = Branding_package::where('package_type','2')->orderby('created_at','desc')->limit(4)->get();
		$featured = Job_post::where('featured_job','2')->where('status','1')->distinct()->get();
		$addon = Addon_package::all();
		$cv = Package::where('type','4')->where('status','1')->get();
		$enquery=Branding_enquiries::where('employer_user_id',Auth::user()->id)->where('status',2)->first();

		return view('branding-package',compact('featured','branding','job','microsite','cv','enquery','addon'));
	}
	public function branding_package_full(){
		//$user = Auth::user()->id;
		//print_r($user);exit();
		$branding = Package::where('type','4')->where('status','1')->get();
		$job = Branding_package::where('package_type','1')->get();
		$microsite = Branding_package::where('package_type','2')->get();
		$featured = Job_post::where('featured_job','2')->where('status','1')->distinct()->get();

		return view('branding-package',compact('featured','branding','job','microsite'));
	}
	public function branding_package_enquiry(){
		$cv = Package::where('type','4')->where('status','1')->get();
		$enquery=Branding_enquiries::where('employer_user_id',Auth::user()->id)->where('status',2)->first();
		$addon = Addon_package::all();
		return view('branding-package-enquiry',compact('featured','branding','job','microsite','cv','enquery','addon'));
	}
	public function buy_now(Request $request){
		//print_r($request->all());exit();
		if(isset(Auth::user()->id)){
			$user_id = Auth::user()->id;
			$id = $request->id;
			if(User_package::where('user_id_fk',$user_id)->where('package_id_fk',$id)->where('status',2)->first())
			{
				echo 4;die();
			}else{
				$user_package=User_package::where('user_id_fk',$user_id)->pluck('package_id_fk')->toArray();
				$package=array();
				if(count($user_package)>0)
				{
					$current_package=Package::where('package_id',$id)->first();
					
					//print_r($current_package);exit();
					if($current_package->type==1)
					{
						if(Helper::job_post_access()>0 && count(Helper::total_job_posted())!=Helper::job_post_access())
						{
							echo 11;die();
						}
						if(Helper::cv_search_access()>0 &&count(Helper::total_cv_searched())!= Helper::cv_search_access() )
						{
							echo 12;die();
						}
					}
					if($current_package->type==2)
					{
						if(Helper::cv_search_access()>0 &&count(Helper::total_cv_searched())!= Helper::cv_search_access() )
						{
							echo 12;die();
						}
					}
					if($current_package->type==3)
					{
						if(Helper::job_post_access()>0 && count(Helper::total_job_posted())!=Helper::job_post_access())
						{
							echo 11;die();
						}
					}
					if($current_package->type==4  && $current_package->branding_pack->package_type==1)
					{
						if(Helper::job_post_access()>0 && count(Helper::total_job_posted())!=Helper::job_post_access())
						{
							echo 11;die();
						}
					}
					if($current_package->type==4  && $current_package->branding_pack->package_type==2)
					{
						if(!empty(Helper::current_microsite_pack())){
							echo 5; die();
						}
					}
					
				}
				$addon_amount=0;
				$package_data = Package::where('package_id',$id)->first();
				if(!empty($request->addon)){ 
					foreach ($request->addon as $a) {
						
						$adons =  Addon_package::where('addon_id',$a)->first();
						$addon_amount=$addon_amount+$adons->amount;
					}
				}
				$pack = new User_package;
				$pack->user_id_fk = $user_id;
				$pack->package_id_fk = $id;
				$pack->price = $addon_amount+$package_data->amount;
				$pack->status = '1';
				if(!empty($request->microsite_type)){
					$pack->microsite_type = $request->microsite_type;
				}
				
				if(!empty($request->addon))
				{$pack->addon = implode(',', $request->addon);}

				// $pack->expiry_date = Helper::get_expiry_date($id);
				//print_r($pack->expiry_date);exit();
				$pack->created_at = date("Y-m-d H:i:s");
				$pack->updated_at = date("Y-m-d H:i:s");
				$pack->save();
				$pack_id = $pack->user_package_id;

					
        		if( !empty($pack->packa->branding_pack) && $pack->packa->branding_pack->package_type == 2)
                {
                    Microsite_details::where('user_id_fk',Auth::user()->id)->update(['user_package_id'=>$pack_id]);
                }

				if(!empty($pack_id)){
					echo 2;die();
				}else{
					echo 3;die();
				}
			}

		}else{
			echo 1;die();
		}
	}
	public function addto_cart(Request $request){
		//print_r($request->all());
		$package_id = $request->id;
		$package_data = Package::where('package_id',$package_id)->first();
		if($package_data->type == 1){
			$p_name = "Ultimate Savers";
		}
		else if($package_data->type == 2){
			$p_name = "CV Access";
		}
		else if($package_data->type == 3){
			$p_name = "Job posting";
		}
		else{
			$p_name = "Branding";
		}
		$userid = Auth::user()->id;
		$cartitems = ShoppingCart::where('user_id_fk',$userid)->where('package_id_fk',$package_id)->first();
		if(!empty($cartitems)){
			echo 1; die();
		}
		else{
			$addon_amount=0;
			if(!empty($request->addon)){ 
			foreach ($request->addon as $a) {
				
				$adons =  Addon_package::where('addon_id',$a)->first();
				$addon_amount=$addon_amount+$adons->amount;
			}
			}
			
			$cart = new ShoppingCart();
			$cart->user_id_fk = $userid;
			$cart->package_id_fk = $package_id;
			$cart->name = $p_name;
			if(!empty($request->addon)){
				$cart->addon = implode(',', $request->addon);
			}
			$cart->currency_type = $package_data->currency_type;
			$cart->qty = 1;
			$cart->price = $package_data->amount+$addon_amount;
			$cart->microsite_type = $request->microsite_type;
			$cart->save();
//print_r($cart);exit();
			echo 2; die();
		}
	}
	public function employer_cart(){
		$userid = Auth::user()->id;
		$content = ShoppingCart::where('user_id_fk',$userid)->get();
		$adon = array();
		$addon = array();
		
		foreach ($content as $key => $ad) {
			if(!empty($ad->addon)){
			$adon[] = explode(',', $ad->addon);
			}
		}
		
		
			$addon = Addon_package::all();
		
		//dd($adon);exit();
        return view('employer/show_cart',compact('content','addon'));
    }
	public function bp_enquiry(Request $request){
		//print_r($request->all());exit();
		$userid = Auth::user()->id;
		$bp_data = Branding_enquiries::where('employer_user_id',$userid)->first();
		if(!empty($bp_data)){
			if($bp_data->status = 1){
				echo 2; die();
			}
			else{
				Branding_enquiries::where('employer_user_id',$userid)->update(['status'=>1]);
				echo 1; die();
			}
		}
		else{
			$bp_enquiry = New Branding_enquiries();
			$bp_enquiry->employer_user_id = $userid;
			$bp_enquiry->message = $request->postdata;
			$bp_enquiry->status = 1;
			$bp_enquiry->save();
			echo 1; die();
		}
	}
}