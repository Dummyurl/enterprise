<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;

use App\Model\Emp_dashboard_contact;
use App\Model\Manage_Content;
use App\Model\Content_Careers;
use App\Model\Content_About;
use App\Model\Content_Contact;
use App\Model\Countries;
use App\Model\About_us_offers;
use Image;


class ContentController extends Controller{
    public function empdc_get(){
    	$data = Emp_dashboard_contact::where('id',1)->first();
        return view('admin.content.emp_db_contact',compact('data'));
    }
    public function empdc_update(Request $request){
        $email = $request->email;
        $landline = $request->landline;
        $tollfree = $request->tollfree;
        
        Emp_dashboard_contact::where('id',1)->update(["landline"=>$landline,"tollfree"=>$tollfree,"email"=>$email]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/content/empdc');
    }
    public function ppolicy_get(Request $request){
        $content = Manage_Content::where('content_type',1)->first();
        return view('admin.content.edit_ppolicy')->with('content',$content);
    }
    public function ppolicy_update(Request $request){
        $content = $request->data;
        Manage_Content::where('content_type',1)->update(['text'=>$content]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/content/ppolicy');
    }
    public function tnc_get(Request $request){
        $content = Manage_Content::where('content_type',2)->first();
        return view('admin.content.edit_tnc')->with('content',$content);
    }
    public function tnc_update(Request $request){
        $content = $request->data;
        Manage_Content::where('content_type',2)->update(['text'=>$content]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/content/tnc');
    }
    public function reg_get(Request $request){
        $content = Manage_Content::where('content_type',3)->first();
        return view('admin.content.mail_reg')->with('content',$content);
    }
    public function reg_update(Request $request){
        $content = $request->data;
        Manage_Content::where('content_type',3)->update(['text'=>$content]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/mailedit/reg');
    }
    public function verify_get(Request $request){
        $content = Manage_Content::where('content_type',4)->first();
        return view('admin.content.mail_verify')->with('content',$content);
    }
    public function verify_update(Request $request){
        $content = $request->data;
        Manage_Content::where('content_type',4)->update(['text'=>$content]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/mailedit/verify');
    }
    public function careers_get(Request $request){
        $content = Content_Careers::where('content_id',1)->first();
        return view('admin.content.edit_careers')->with('content',$content);
    }
    public function careers_update(Request $request){
        $content = $request->data;
        if($request->hasFile('banner_img')){
            $mime_validate = Validator::make($request->all(),['banner_img' => 'required|mimes:jpg,jpeg,png']);
            if($mime_validate->fails()){
            return redirect('admin/content/careers')->with('errormsg','Please choose a valid file');
            }
                $b_img = $request->file('banner_img');
                $destination = 'uploads/images';  
                $thumb = $b_img;
                $img = Image::make($thumb->getRealPath())->resize(1400,467);
                $path = $destination. "/" .time().'-'.$b_img->getClientOriginalName();
                $img->save($path);
                Content_Careers::where('content_id',1)->update(['img'=>$path]);
        }
        $content = Content_Careers::where('content_id',1)->update(['content_text'=>$content]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/content/careers');
    }
    public function aboutus_get(Request $request){
        $content = Content_About::where('content_id',1)->first();
        return view('admin.content.edit_about')->with('content',$content);
    }
    public function aboutus_update(Request $request){
        $content = $request->data;
        $mission = $request->mission;
        $vision = $request->vision;
        if($request->hasFile('banner_img')){
            $mime_validate = Validator::make($request->all(),['banner_img' => 'required|mimes:jpg,jpeg,png']);
            if($mime_validate->fails()){
            return redirect('admin/content/careers')->with('errormsg','Please choose a valid file');
            }
                $b_img = $request->file('banner_img');
                $destination = 'uploads/images';  
                $thumb = $b_img;
                $img = Image::make($thumb->getRealPath())->resize(1400,467);
                $path = $destination. "/" .time().'-'.$b_img->getClientOriginalName();
                $img->save($path);
                Content_About::where('content_id',1)->update(['img'=>$path]);
        }
        $content = Content_About::where('content_id',1)
                                  ->update([
                                    'content_text'=>$content,
                                    'our_mission'=>$mission,
                                    'our_vision'=>$vision
                                    ]);
        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/content/aboutus');
    }
    public function contact_list(Request $request){
        $content = Content_Contact::all();
        $country = Countries::all();
        return view('admin.content.contact_list',compact('content','country'));
    }
    public function contact_get(Request $request){
        $country = Countries::all();
        return view('admin.content.add_contact',compact('country'));
    }
    public function contact_add(Request $request){
        $country = $request->country;
        $location = $request->location;
        $address1 = $request->address1;
        $address2 = $request->address2;
        $address3 = $request->address3;
        $address4 = $request->address4;
        $fax = $request->fax;
        $landline = $request->landline;
        $mobile = $request->mobile;
        $email = $request->email;
        $cc_new = new Content_Contact();
        $cc_new->country = $country;
        $cc_new->type = $request->type;
        $cc_new->city = $location;
        $cc_new->address1 = $address1;
        $cc_new->address2 = $address2;
        $cc_new->address3 = $address3;
        $cc_new->address4 = $address4;
        $cc_new->fax_no = $fax;
        $cc_new->landline_no = $landline;
        $cc_new->mobile_no = $mobile;
        $cc_new->email = $email;
        $cc_new->from_day = $request->from_day;
        $cc_new->to_day =$request->to_day;
        $cc_new->from_time = $request->from_time;
        $cc_new->to_time = $request->to_time;
        $cc_new->holiday = $request->holiday;
        $cc_new->save();
        $request->session()->flash('message','Succesfully Added Record');
        return redirect('admin/content/contact-list');
    }
    public function contact_edit($id){
        $content = Content_Contact::where('content_id',$id)->first();
                $country = Countries::all();
        return view('admin.content.edit_contact',compact('content','country'));
    }
    public function contact_update(Request $request){
        //print_r($request->all());exit();
        $country = $request->country;
        $location = $request->location;
        $address1 = $request->address1;
        $address2 = $request->address2;
        $address3 = $request->address3;
        $address4 = $request->address4;
        $fax = $request->fax;
        $landline = $request->landline;
        $mobile = $request->mobile;
        $email = $request->email;
        $id = $request->content_id;
        $cc_exist = Content_Contact::where('content_id',$id)->first();
        $cc_exist->country = $country;
        $cc_exist->city = $location;
        $cc_exist->address1 = $address1;
        $cc_exist->address2 = $address2;
        $cc_exist->address3 = $address3;
        $cc_exist->address4 = $address4;
        $cc_exist->fax_no = $fax;
        $cc_exist->landline_no = $landline;
        $cc_exist->mobile_no = $mobile;
        $cc_exist->email = $email;
        $cc_exist->from_day = $request->from_day;
        $cc_exist->to_day =$request->to_day;
        $cc_exist->from_time = $request->from_time;
        $cc_exist->to_time = $request->to_time;
        $cc_exist->holiday = $request->holiday;
        $cc_exist->save();
        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/content/contact-list');
    }
    public function contact_remove(Request $request,$id){
        Content_Contact::where('content_id',$id)->delete();
        $request->session()->flash('message','Succesfully Removed Record');
        return redirect('admin/content/contact-list');
    }
    public function about_us_offers()
    {
        $list = About_us_offers::first();
        return view('admin/content/about_us_offers_list',compact('list'));
    }
    public function aboutus_offers_add()
    {
        return view('admin/content/aboutus_offers_add');
    }
    public function aboutus_offers_save(Request $request)
    {
        //print_r($request->all());exit();
        $list = new About_us_offers();
        $list->employer_offers = $request->e_offer_name;
        $list->e_offers = serialize($request->e_offres);
        $list->Job_seeker_offers = $request->j_offer_name;
        $list->j_offers = serialize($request->j_offres);
        $list->save();
        $request->session()->flash('message','Succesfully Added Record');
        return redirect('admin/content/about_us_offers');
    }
    public function aboutus_offers_edit($id)
    {
        $list = About_us_offers::where('id',$id)->first();
        return view('admin/content/aboutus_offers_add',compact('list'));
    }
    public function aboutus_offers_update(Request $request)
    {
        //print_r($request->file('e_image'));exit();
        $list = About_us_offers::where('id',$request->id)->first();
        $list->employer_offers = $request->e_offer_name;
        $list->e_offers = serialize($request->e_offres);
        $list->Job_seeker_offers = $request->j_offer_name;
        $list->j_offers = serialize($request->j_offres);
        if($request->file('e_image')){
           $destination = public_path('/uploads/employer');  
           $relativepath = '/uploads/employer';  
           $file = $request->file('e_image');
           $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
           $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
            $list->e_image = $filelocation;
        }
        if($request->file('j_image')){
            $destination = public_path('/uploads/employer');  
           $relativepath = '/uploads/employer';  
           $file = $request->file('j_image');
           $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
           $filelocation = $relativepath. "/" .time().'-'.$file->getClientOriginalName();
            $list->j_image = $filelocation;
        }
        //print_r($list);exit();
        $list->save();
        $request->session()->flash('message','Succesfully Updated Record');
        return redirect('admin/content/about_us_offers');
    }
}