<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use DB;

use App\Model\Testimonials;

use Image;

class TestimonialsController extends Controller{
    public function list(){
        $speaks = Testimonials::all();
        return view('admin.testimonials.list')->with('speaks',$speaks);
    }
    public function add_get()
    {
        return view('admin.testimonials.add');
    }
    public function add_store(Request $request)
    {
        $empspeak = new Testimonials();
        $empspeak->emp_name = $request->empname;
        $empspeak->emp_design = $request->empdesign;
        $empspeak->emp_loc = $request->emploc;
        $empspeak->title = $request->title;
        $empspeak->desc = $request->content;
        $empspeak->status = 1;
        if($request->hasFile('empprofile')){
            $destination = 'uploads/images';  
            $file = $request->file('empprofile');
            $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
            $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
            $empspeak->emp_img = $filelocation;
        }
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Inserted Record');
        return redirect('admin/empspeak/list');
    }
    public function edit($id)
    {
        $speak = Testimonials::where('id',$id)->first();
        return view('admin.testimonials.add',compact('speak'));
    }
    public function update(Request $request,$id)
    {
        $empspeak = Testimonials::where('id',$id)->first();
        $empspeak->emp_name = $request->empname;
        $empspeak->emp_design = $request->empdesign;
        $empspeak->emp_loc = $request->emploc;
        $empspeak->title = $request->title;
        $empspeak->desc = $request->content;
        if($request->hasFile('empprofile')){
            $destination = 'uploads/images';  
            $file = $request->file('empprofile');
            $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
            $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
            $empspeak->emp_img = $filelocation;
        }
        $empspeak->save();

        $request->session()->flash('successmsg','Succesfully Updated Record');
        return redirect('admin/empspeak/list');
    }
    public function delete($id)
    {
        $location = Testimonials::where('id',$id)->first();
        $location->delete();

        return redirect('admin/empspeak/list')->with('successmsg','Succesfully Deleted Record');
    }
    public function approve($id)
    {
        $location = Testimonials::where('id',$id)->first();
        $location->status = 1;
        $location->save();

        return redirect('admin/empspeak/list')->with('successmsg','Succesfully Approved');
    }
}