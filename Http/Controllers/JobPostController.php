<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use Mail;
use App\User;
use App\Model\Job_post;
use App\Model\Saved_job;
use App\Model\Applied_job;
use App\Model\Training;
use App\Model\Microsite_resumes;
use App\Model\Features;
use App\Model\Report_Abuse;
use App\Model\Microsite_details;


class JobPostController extends Controller
{
    public function view_job()
    {
    	$job_post=Job_post::orderBy('job_id','DESC')->where('type',1)->get();
    	return view('admin/job_post',compact('job_post'));
    }


    public function saved_job($saved_id)
    {
        $applied=Applied_job::where('job_id_fk',$saved_id)->pluck('user_id_fk')->toArray();
    	$saved=Saved_job::where('job_id_fk',$saved_id)->WhereNotIn('user_id_fk',$applied)->get();
    	return view('admin/view_saved_details',compact('saved'));
    }

    public function applied_job($apply_id)
    {
        $applied=Applied_job::where('job_id_fk',$apply_id)->get();
        // dd($applied);
        return view('admin/view_applied_details',compact('applied'));
    }

    public function view_recruiter($job_id)
    {
        $view=Job_post::where('job_id',$job_id)->get();
        return view('admin/view_recruiters_details',compact('view'));
    }

    public function show_upload()
    {
        // $view=Job_post::where('job_id',$job_id)->get();
        return view('admin/training');
    }
     public function upload(Request $request)
    {
        // print_r("expression");exit;
        // ini_set('post_max_size','2000M')
        // print_r(ini_get('post_max_size'));exit();
        //$files=request()->file('uploadVideo');
        //  $this->validate($request, [
        //     'video' => 'required|video|mimes:avi,mp4|max:8M',
        // ]);

         
            // $filename=$file->getClientOriginalExtension();
            // $file->move(public_path('uploads/'), $filename);

     // $this->validate($request, [
     //        'type' => 'required',
     //        'title' => 'required',
     //        'content'=> 'required',
     //        'uploadVideo' => 'required|mimes:avi,mp4|max:2000',
     //    ]);
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'title' => 'required',
            'content'=> 'required',
            'uploadVideo' => 'mimes:avi,mp4|max:1000000',      
             ]);
            $training = new Training();
            $training->type = request()->type;
            $training->title=request()->title;
            $training->content=request()->content;
            $training->link = request()->link;

        if (($validator->fails()) && (empty(request()->link) || empty(request()->link) )) {
            return redirect('admin/training')->with('message','All field must be filled and  allowed only mp4 and avi format videos or Link');
        }else{
           
                 if($request->hasFile('uploadVideo'))
                  {
                        $destination = 'uploads/video';  
                        $file = $request->file('uploadVideo');
                        $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                        $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                        $training->video = $filelocation;
                   }
                    //$training->video='uploads/'.$filename;
                    $training->save();
                    return redirect('admin/training')->with('message','video uploaded successfully');

        }
           
    }
    public function training_list()
    {
        $list = Training::all();
        return view('admin/training_list',compact('list'));
    }
    public function training_edit($id)
    {
        $list = Training::where('training_id',$id)->first();
        return view('admin/training',compact('list'));
    }
    public function training_edit_save(Request $request)
    {
        //print_r($request->all());exit();
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'title' => 'required',
            'content'=> 'required',
            'uploadVideo' => 'mimes:avi,mp4|max:10000',      
             ]);
            $training = Training::where('training_id',$request->id)->first();
            $training->type = request()->type;
            $training->title=request()->title;
            $training->content=request()->content;
            $training->link = request()->link;

        if (($validator->fails()) && (empty(request()->link) || empty(request()->link) )) {
            return redirect('admin/training')->with('message','All field must be filled and  allowed only mp4 and avi format videos or Link');
        }else{
           
                 if($request->hasFile('uploadVideo'))
                  {
                        $destination = 'uploads/video';  
                        $file = $request->file('uploadVideo');
                        $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                        $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                        $training->video = $filelocation;
                   }
                    //$training->video='uploads/'.$filename;
                    $training->save();
                    return redirect('admin/training_list')->with('message','video uploaded successfully');

        }
    }
    public function training_delete($id)
    {
        $list = Training::where('training_id',$id)->delete();
        return back()->with('message','Item Deleted successfully');
    }

    public function micrositeResume(){
        $resumeLists=Microsite_resumes::orderBY('id','DESC')->get();
        return view('admin/resumeList',compact('resumeLists'));
    }
    public function resume_delete($id)
    {
        $resumeLists=Microsite_resumes::where('id',$id)->delete();
        return back()->with('message','Record Deleted successfully');
    }

    public function getDisplay(){
        
    }
    public function disable($id){
        Job_post::where('job_id',$id)->update(['status'=>'2']);
        return redirect('admin/jobpost');
    }
    public function enable($id){
        Job_post::where('job_id',$id)->update(['status'=>'1']);
        return redirect('admin/jobpost');
    }
    public function fjpmanage(){
        $expiry = Features::where('type','3')->first();
        return view('admin/jobpost/fjpmanage',compact('expiry'));
    }
    public function fjp_save(Request $request){
        $expdate = $request->expiry_date;
        Features::where('type','3')->update(["title"=>$expdate]);
        $expiry = Features::where('type','3')->first();
        return redirect('admin/jobpost/fjpmanage')->with('successmsg','updated successfully');
    }
    public function abuses($id){
        $abuses = Report_Abuse::where('job_id',$id)->get();
        return view('admin/jobpost/abuses',compact('abuses'));
    }
    public function top_views(){
        $job_post=Job_post::orderBy('view_count','DESC')->offset(0)->limit(10)->get();
        //print_r($job_post);exit();
        return view('admin/job_post_views',compact('job_post'));
    }
    public function microsite_list(){
        $resumeLists=Microsite_details::all();
        return view('admin/micrositeList',compact('resumeLists'));
    }
    public function abuses_list()
    {
        $job_post=Job_post::orderBy('job_id','DESC')->get();
        return view('admin/jobpost/abuses_list',compact('job_post'));
    }
}
 
