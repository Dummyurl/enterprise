<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\IndustryType;
use App\Model\SubIndustryType;
use App\Model\Course;
use App\Model\Specialization;
use App\Model\PGCourse;
use App\Model\PGSpecialization;

class IndustryTypeController extends Controller
{
    public function industry_typelist()
    {
    	$industry_type = IndustryType::orderBy('industry_type_name')->get();
    	return view('admin.industry.listindustry')->with('industry_type',$industry_type);
    }
    public function footer_industry_typelist()
    {
        $industry_type = IndustryType::orderby('industry_type_id','DESC')->get();
        return view('admin.footer_listindustry')->with('industry_type',$industry_type);
    }

    public function create()
    {
    	return view('admin.industry.addindustry');
    }

    public function store(Request $request)
    {
    	 $rules=array('industry_type_name'=>'required');
    	 $this->validate($request,$rules);
    	 $industry_type = new IndustryType;
    	 $industry_type->industry_type_name = $request->industry_type_name;
    	 $industry_type->save();

         if(!empty($request->sub_industry_type_name[0])){
             $insertId = $industry_type->industry_type_id;
             foreach($request->sub_industry_type_name as $sub){
                $sub_industry_type = new SubIndustryType;
                $sub_industry_type->industry_type_id_fk = $insertId;
                $sub_industry_type->sub_industry_type_name = $sub;
                $sub_industry_type->save();    
             }
         }

    	 $request->session()->flash('message','Succesfully Inserted Record');
    	 return redirect('admin/industry/list');
    }

    public function edit($id)
    {
    	$industry_type = IndustryType::where('industry_type_id',$id)->first();
        $sub_industry_type = SubIndustryType::where('industry_type_id_fk',$id)->get();
    	return view('admin.industry.addindustry',compact('industry_type','sub_industry_type'));
    }

    public function update(Request $request,$id)
    {
    	$rules = array('industry_type_name'=>'required');
    	$this->validate($request,$rules);
    	$industry_type =IndustryType::where('industry_type_id',$id)->first();
    	$industry_type->industry_type_name = $request->industry_type_name;
    	$industry_type->save();

        SubIndustryType::where('industry_type_id_fk',$id)->delete();

        if(!empty($request->sub_industry_type_name[0])){
            foreach($request->sub_industry_type_name as $sub){
                $sub_industry_type = new SubIndustryType;
                $sub_industry_type->industry_type_id_fk = $id;
                $sub_industry_type->sub_industry_type_name = $sub;
                $sub_industry_type->save();    
            }
        }

    	$request->session()->flash('message','Succesfully Updated Record');
    	return redirect('admin/industry/list');
    }

    public function delete($id)
    {
    	$industry_type = IndustryType::where('industry_type_id',$id)->first();
    	$industry_type->delete();
    	return redirect('admin/industry/list')->with('message','Succesfully Deleted Record');
    }


public function manage_footer_industry($flag,$id)
    {
        $industry_type =IndustryType::where('industry_type_id',$id)->first();
        $industry_type->footer_status = $flag;
        $industry_type->save();
        return redirect('admin/job_industry/list');
    }

}
