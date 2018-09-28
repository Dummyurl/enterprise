<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use Socialite;
use Validator;
use Session;
use Mail;
use DB;
use App\User;
use App\Model\Job_post;
use App\Model\Top_Employer;
use App\Model\Landing_Locations;
use App\Model\IndustryType;
use App\Model\Employer;
use App\Model\Feedback;
use App\Model\Features;
use App\Model\Banners;
use App\Model\Report_Abuse;
use App\Model\Footer_Locations;
use App\Model\Jobsby_Locations;
use App\Model\Jobsby_skills;
use App\Model\Countries;
use App\Model\Job_post_package;
use App\Model\Last_login;
use App\Model\Contact_Enquiries;
use App\Model\Testimonials;
use App\Model\Faqs;
use App\Model\Job_post_keyskills;
use Carbon\Carbon;
use Softon\Indipay\Facades\Indipay;

class HomeController extends Controller{
	private $search_employer_email;
    private $mail_search_subject;
	private $jids = array();
	public function home(){
		if(Session::get('otp') != "")
		{
			Session::forget('otp');
		}
		
		// dd( Session::get('otp'));
		//$user = Auth::user()->id;
		//print_r($user);exit();
		$topemployers = Top_Employer::all();
		$bylocations = Landing_Locations::all();
		$bysector = IndustryType::orderBy('industry_type_name')->get();
		$companies = Employer::where('type',1)->where('parent_id',0)->get(['employer_id','company_name']);
		$agencies = Employer::where('type',2)->where('parent_id',0)->get(['employer_id','company_name']);
		$alljobs = Job_post::where('status','1')->where('type',1)->orderBy('created_at','DESC')->get();
		$featured = Job_post::where('featured_job','2')->where('type',1)->where('status','1')->where('job_expire','>=',date('Y-m-d'))->orderBy('created_at','DESC')->distinct()->get();
		$js_fs = Features::where('type',1)->get();
		$emp_fs = Features::where('type',2)->get();
		$banner = Banners::where('id','1')->first();
		$countries = Countries::all();
		//print_r($companies);exit();
		return view('index',compact('featured','topemployers','bylocations','bysector','companies','agencies','alljobs','js_fs','emp_fs','banner','countries'));
	}
	public function search_job(Request $request){
		// dd($request->all());
		$keywords = $request->keywords;
		$location = $request->location;
		if(!empty($location)){
			$heading = $keywords." Jobs in ".$location;
		}
		else{
			$heading = $keywords." Jobs found ";
		}
        //print_r($loc);exit();
        $title='';
        $company='';
        if(!empty($keywords)){
             if(!empty($location)){
	            $locationarr = explode(",", $location);
	            $locationlen = count($locationarr);
	            
	            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
	                                    ->where('job_title','like','%'.$keywords.'%')->get();
	            $this->addtoarr($matchedJobs);
        	}else{
        		 $matchedJobs = Job_post::where('job_title','like','%'.$keywords.'%')->get();
	            $this->addtoarr($matchedJobs);
	            $title=$keywords;
        	}
		}
        if(!empty($keywords)){
             if(!empty($location)){
	            $locationarr = explode(",", $location);
	            $locationlen = count($locationarr);
	            
	            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
	                                    ->where('job_title','like','%'.$keywords.'%')->get();
	            $this->addtoarr($matchedJobs);
        	}else{
        		$matchedJobs = Job_post::where('employer_company_name','like','%'.$keywords.'%')->get();
            	$this->addtoarr($matchedJobs);
            	//$company=$keywords;
        	}
		}
		// dd($matchedJobs);
		$country='';
		$city='';
        if(!empty($location)){
            $locationarr = explode(",", $location);
            $locationlen = count($locationarr);
            if(!empty($keywords)){
            $matchedJobs = Job_post::where('job_title','like','%'.$keywords.'%')
            						->where('location','like','%'.$locationarr[0].'%')
            						->get();
            $this->addtoarr($matchedJobs);
            $title=$keywords;
			}else{
				 $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                   ->get();
            $this->addtoarr($matchedJobs);
			}
	        if(!empty($keywords)){
	            $matchedJobs = Job_post::where('employer_company_name','like','%'.$keywords.'%')
	            						->where('location','like','%'.$locationarr[0].'%')
	            						->get();
	            $this->addtoarr($matchedJobs);
	            $company=$keywords;
			}else{
				 $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                   ->get();
            $this->addtoarr($matchedJobs);
			}
            
		}
//dd($matchedJobs);
		$refinearr['0'] = $country;
		$refinearr['1'] = $city;
		$refinearr['2'] = $title;
		$refinearr['3'] = '';
		$refinearr['4'] = '';
		$refinearr['5'] = $company;
		$refinearr['6'] = "";
		$refinearr['7'] = '';
		$refinearr['8'] = "";
		$refinearr['9'] = "";
		$refinearr['10'] = '';
		$refinearr['11'] = '';
		$refinearr['12'] = "";
		$refinearr['13'] = "";
		$refinearr['14'] = "";
		$topemployers = Top_Employer::all();
		$date=date('Y-m-d',strtotime('-1 month',strtotime(date('Y-m-d'))));
		if(empty($keywords) && empty($location))
		{

		$search = Job_post::where('type',1)->where('job_expire','>=',date('Y-m-d'))->orderBy('created_at','DESC')->where('status',1)->get();
		}
		else
		{
		$search = Job_post::whereIn('job_id',$this->jids)->where('type',1)->where('job_expire','>=',date('Y-m-d'))->orderBy('created_at','DESC')->where('status',1)->get();
		}
		$consultant = Employer::where('type','2')->get();
		//dd($refinearr);
   		return view('job_listing',compact('search','keywords','location','heading','topemployers','company','title','city','country','consultant'))->with('refines',$refinearr);
	}
	public function browseby_loc(Request $request){
		$location = $request->location;
		$heading = " Jobs in ".$location;
		$keywords = "";

        if(!empty($location)){
            $locationarr = explode(",", $location);
            $locationlen = count($locationarr);
            if($locationlen == 4){
            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->get();
            $this->addtoarr($matchedJobs);
            }
            else if($locationlen == 3){
            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->get();
            $this->addtoarr($matchedJobs);
            }
            else if($locationlen == 2){
            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->get();
            $this->addtoarr($matchedJobs);
            }
            else{
            $matchedJobs = Job_post::where('location','like','%'.$locationarr[0].'%')
                                    ->get();
            $this->addtoarr($matchedJobs);
            }
		}

		$topemployers = Top_Employer::all();
		$search = Job_post::whereIn('job_id',$this->jids)->where('type',1)
						->where('job_expire','>=',date('Y-m-d'))
						->where('status',1)
						->orderBy('created_at','DESC')->get();
		$consultant = Employer::where('type','2')->get();
   		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function sort_job(Request $request){
		$sort_param = $request->filter_ddl;
		$keywords = $request->keywords;
		$location = $request->location;
		if(!empty($location)){
			$heading = $keywords." Jobs in ".$location;
		}
		else{
			$heading = $keywords." Jobs found ";
		}
        //print_r($loc);exit();
        if(!empty($keywords)){
            $matchedJobs = Job_post::where('job_title','like','%'.$keywords.'%')->get();
            $this->addtoarr($matchedJobs);
		}
        if(!empty($location)){
            $locationarr = explode(",", $location);
            $locationlen = count($locationarr);
            if($locationlen == 4){
            $matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
                                    ->where('job_title','like','%'.$keywords.'%')->get();
            $this->addtoarr($matchedJobs);
            }
            else if($locationlen == 3){
            $matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
                                    ->where('job_title','like','%'.$keywords.'%')->get();
            $this->addtoarr($matchedJobs);
            }
            else if($locationlen == 2){
            $matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
                                    ->where('job_title','like','%'.$keywords.'%')->get();
            $this->addtoarr($matchedJobs);
            }
            else{
            $matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
                                    ->where('job_title','like','%'.$keywords.'%')->get();
            $this->addtoarr($matchedJobs);
            }
		}
		$topemployers = Top_Employer::all();
		if($sort_param == '1'){
		$search = Job_post::whereIn('job_id',$this->jids)->where('type',1)
													->where('job_expire','>=',date('Y-m-d'))
													->where('status',1)
													->orderBy('created_at','DESC')->get();
		}
		else{
		$search = Job_post::whereIn('job_id',$this->jids)->where('type',1)
						->where('job_expire','>=',date('Y-m-d'))
						->where('status',1)
						->orderBy('created_at','DESC')->get();
		}
		$consultant = Employer::where('type','2')->get();
   		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function filter_job(Request $request){
		//dd($request->all());
		$state = $request->filter_loc;
		$itype = $request->filter_sector;
		$company = $request->filter_company;
		$skill = $request->filter_skill;
		$keywords = "";
		$location = "";
		// print_r($request->all());exit();
		if(!empty($state)){
			$matchedJobs = Job_post::where('location','like','%'.$state.'%')->get();
			$this->addtoarr($matchedJobs);
			$keywords = "location";
			$location = $state;
		}
		if(!empty($itype)){
			$matchedJobs = Job_post::where('industry_type',$itype)->get();
			$this->addtoarr($matchedJobs);
			$keywords = "industry_type";
			$location = $itype;
		}
		if(!empty($company)){
			$matchedJobs = Job_post::where('employer_company_name',$company)->get();
			$this->addtoarr($matchedJobs);
			$keywords = "employer_company_name";
			$location = $company;
		}
		if(!empty($skill)){
			//$matchedJobs = Job_post::where('job_title','like','%'.$skill.'%')->get();

			$matchedJobs = Job_post::where(function($sq) use ($skill)
                                {
                                   $sq->whereIn('job_id',function($sq) use ($skill)
                                        {
                                           $sq->select('job_id_fk')
                                                ->from('job_post_key_skills')
                                                ->where('skill','like','%'.$skill.'%')
                                                ->pluck('job_id_fk')->toarray();
                                        });
                                })->get();


			$this->addtoarr($matchedJobs);
			$keywords = "";
			//$location = $skill;
		}
		$topemployers = Top_Employer::all();
		$date=date('Y-m-d',strtotime('-1 month',strtotime(date('Y-m-d'))));
		$search = Job_post::whereIn('job_id',$this->jids)->where('type',1)->where('job_expire','>=',date('Y-m-d'))->orderBy('created_at','DESC')->where('status',1)->get();
		$heading = " Jobs in ".$location;
		$consultant = Employer::where('type','2')->get();
		//print_r($search);exit();
  		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function refine_job(Request $request){

		 //dd(request()->all());
		$refinearr['0'] = $request->countryarr;
		$refinearr['1'] = $request->cityarr;
		$refinearr['2'] = $request->titlearr;
		$refinearr['3'] = $request->functionarr;
		$refinearr['4'] = $request->industryarr;
		$refinearr['5'] = $request->companyarr;
		$refinearr['6'] = $request->postedarr;
		$refinearr['7'] = $request->emptypearr;
		$refinearr['8'] = $request->exparr;
		$refinearr['9'] = $request->genderarr;
		$refinearr['10'] = $request->msalarr;
		$refinearr['11'] = $request->nationarr;
		$refinearr['12'] = $request->designarr;
		$refinearr['13'] = $request->skillarr;
		$refinearr['14'] = $request->sort;


		$heading = $request->heading;
		$keywords = $request->keywords;
		$location = $request->location;

		$date=date('Y-m-d',strtotime('-1 month',strtotime(date('Y-m-d'))));
		$matchedJobs = Job_post::where('type',1)->where('job_expire','>=',date('Y-m-d'))->where('status',1)->pluck('job_id')->toArray();
		
		$keywords = $request->keywords;
        if(!empty($keywords)){
            // dd(explode(',', $location));
			$keywords=explode(',', $keywords);
			$result=[];
			foreach ($keywords as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_title','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}
		//dd($matchedJobs);
		$location=$request->countryarr;
		if(!empty($location))
		{
			// dd(explode(',', $location));
			$location=explode(',', $location);
			$result=[];
			foreach ($location as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('loc_country','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$city=$request->cityarr;
		if(!empty($city))
		{
			// dd(explode(',', $city));
			$city=explode(',', $city);
			$result=[];
			foreach ($city as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('location','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$title=$request->titlearr;
		if(!empty($title))
		{
			// dd(explode(',', $title));
			$title=explode(',', $title);
			$result=[];
			foreach ($title as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_title','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$function=$request->functionarr;
		if(!empty($function))
		{
			// dd(explode(',', $title));
			$function=explode(',', $function);
			$result=[];
			foreach ($function as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('functional_area','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$industry=$request->industryarr;
			// dd(explode(',', $industry));
		if(!empty($industry))
		{
			$industry=explode(',', $industry);
			$result=[];
			foreach ($industry as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('industry_type','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}
//dd($matchedJobs);
		$company=$request->companyarr;
		if(!empty($company))
		{
			// dd(explode(',', $company));
			$company=explode(',', $company);
			$result=[];
			foreach ($company as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('employer_company_name','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}
 
		$emp_type=$request->emptypearr;
		if(!empty($emp_type))
		{
			$emp_type=explode(',', $emp_type);
			$result=[];
			foreach ($emp_type as $key) {
				$emp= Employer::where('type',$key)->pluck('user_id_fk')->toArray();
				$user=User::whereIn('id',$emp)->pluck('id')->toArray();
				$data=Job_post::whereIn('job_id',$matchedJobs)->whereIn('user_id_fk',$user)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$experience=$request->exparr;
		if(!empty($experience))
		{
			// dd(explode(',', $experience));
			$experience=explode(',', $experience);
			$result=[];
			foreach ($experience as $key) {
			$experiences=explode('-', $key);
			// dd($experience);
			if($experiences[1]==0)
			{
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('min_experience','>=',$experiences[0])->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			}
			else{
				$data=Job_post::whereIn('job_id',$matchedJobs)->whereBetween('min_experience',[$experiences[0],$experiences[1]])->whereBetween('max_experience',[$experiences[0],$experiences[1]])->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			}
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$gender=$request->genderarr;
		if(!empty($gender))
		{
			// dd(explode(',', $gender));
			$gender=explode(',', $gender);
			$result=[];
			if(count($gender)>1)
			{
				array_push($gender, '1');
				$key=$gender;
			}
			else{
				$key=$gender;
			}
			// dd($key);
				$data=Job_post::whereIn('job_id',$matchedJobs)->whereIn('gender',$key)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			$matchedJobs=$result;
		}

		$salary=$request->msalarr;
		if(!empty($salary))
		{
			// dd(explode(',', $salary));
			$salary=explode(',', $salary);
			$result=[];
			foreach ($salary as $key) {
			$salarys=explode('-', $key);
			if($salarys[1]==0)
			{
        		// DB::connection()->enableQueryLog();
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_min','>=',$salarys[0])->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id','salary_min')->toArray();
       			 // $data['query']=DB::getQueryLog();
			}
			else
			{
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('status',1)->whereBetween('salary_min',[$salarys[0],$salarys[1]])->whereBetween('salary_max',[$salarys[0],$salarys[1]])->WhereNotIn('job_id',$result)->pluck('job_id')->toArray();
			}
				$result=array_merge($data,$result);
			}
			$matchedJobs=$result;
		}

		$nationality=$request->nationarr;
		if(!empty($nationality))
		{
			// dd(explode(',', $nationality));
			$nationality=explode(',', $nationality);
			$result=[];
			foreach ($nationality as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('status',1)->where('nationality','like','%'.$key.'%')->WhereNotIn('job_id',$result)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		/*$title=$request->designarr;
		if(!empty($title))
		{
			// dd(explode(',', $title));
			$title=explode(',', $title);
			$result=[];
			foreach ($title as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_title','like','%'.$key.'%')->WhereNotIn('job_id',$result)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}*/

		$skils=$request->skillarr;
			 //dd($skils);
		if(!empty($skils))
		{
			$skils=explode(',', $skils);
			$result=[];
			foreach ($skils as $key) {
				$data=Job_post_keyskills::whereIn('job_id_fk',$matchedJobs)->where('skill','like','%'.$key.'%')->WhereNotIn('job_id_fk',$result)->pluck('job_id_fk')->toArray();
				$result=array_merge($data,$result);
				//dd(Job_post_keyskills::where('skill','like','%'.$key.'%')->get());
			}
			// dd($cubrid_result(result, row)lt);

			$matchedJobs=$result;
		}
		// dd($matchedJobs);
		$topemployers = Top_Employer::all();
		// $search = $matchedJobs;
		$keywords = $request->keywords;
		$location = $request->location;
		$heading = $request->heading;
		//print_r($search);exit();
		if($request->sort==2)
		{
			$search=Job_post::whereIn('job_id',$matchedJobs)->orderBy('updated_at','DESC')->where('status',1)->get();
		}
		else
		{
			$search=Job_post::whereIn('job_id',$matchedJobs)->orderBy('updated_at')->where('status',1)->get();
		}
		// dd($search);
		$consultant = Employer::where('type','2')->get();
		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines',$refinearr);
	}
	public function refine_job2(Request $request){
		$refinearr['0'] = $request->countryarr;
		$refinearr['1'] = $request->cityarr;
		$refinearr['2'] = $request->titlearr;
		$refinearr['3'] = $request->functionarr;
		$refinearr['4'] = $request->industryarr;
		$refinearr['5'] = $request->companyarr;
		$refinearr['6'] = $request->postedarr;
		$refinearr['7'] = $request->emptypearr;
		$refinearr['8'] = $request->exparr;
		$refinearr['9'] = $request->genderarr;
		$refinearr['10'] = $request->msalarr;
		$refinearr['11'] = $request->nationarr;
		$refinearr['12'] = $request->designarr;
		$refinearr['13'] = $request->skillarr;
		$refinearr['14'] = $request->sort;

		$keywords = $request->keywords;
		$location = $request->location;
		if(!empty($location)){
            $locationarr = explode(",", $location);
            $locationlen = count($locationarr);
            $alljobs = Job_post::all();
            if($locationlen == 4){
				if($keywords == 'industry_type'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('industry_type',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'employer_company_name'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('employer_company_name',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'job_title'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'featured'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('featured_job','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'topemployers'){
					$top_emps = Top_Employer::all();
					foreach($top_emps as $emp){
						$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('user_id_fk',$emp->user_id_fk)->get();
						$this->addtoarr($matchedJobs);
					}
				}
				else if($keywords == 'walkins'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('walk_in_interview','1')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'freshers'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('min_experience','0')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'part-time'){
					$matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
											->where('job_type','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else{
					 $matchedJobs = Job_post::where('loc_area','like','%'.$locationarr[0].'%')
										->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
            }
            else if($locationlen == 3){
					if($keywords == 'industry_type'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('industry_type',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'employer_company_name'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('employer_company_name',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'job_title'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'featured'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('featured_job','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'topemployers'){
					$top_emps = Top_Employer::all();
					foreach($top_emps as $emp){
						$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
												->where('user_id_fk',$emp->user_id_fk)->get();
						$this->addtoarr($matchedJobs);
					}
				}
				else if($keywords == 'walkins'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('walk_in_interview','1')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'freshers'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('min_experience','0')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'part-time'){
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('job_type','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else{
					$matchedJobs = Job_post::where('loc_city','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
            }
            else if($locationlen == 2){
					if($keywords == 'industry_type'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('industry_type',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'employer_company_name'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('employer_company_name',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'job_title'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'featured'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('featured_job','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'topemployers'){
					$top_emps = Top_Employer::all();
					foreach($top_emps as $emp){
						$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
												->where('user_id_fk',$emp->user_id_fk)->get();
						$this->addtoarr($matchedJobs);
					}
				}
				else if($keywords == 'walkins'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('walk_in_interview','1')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'freshers'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('min_experience','0')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'part-time'){
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('job_type','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else{
					$matchedJobs = Job_post::where('loc_state','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
            }
            else{
				if($keywords == 'industry_type'){
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('industry_type',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'employer_company_name'){
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('employer_company_name',$keywords)->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'job_title'){
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'featured'){
					$this->addtoarr($matchedJobs);
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('featured_job','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'topemployers'){
					$top_emps = Top_Employer::all();
					foreach($top_emps as $emp){
						$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
												->where('user_id_fk',$emp->user_id_fk)->get();
						$this->addtoarr($matchedJobs);
					}
				}
				else if($keywords == 'walkins'){
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('walk_in_interview','1')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'freshers'){
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('min_experience','0')->get();
					$this->addtoarr($matchedJobs);
				}
				else if($keywords == 'part-time'){
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('job_type','2')->get();
					$this->addtoarr($matchedJobs);
				}
				else{
					$matchedJobs = Job_post::where('loc_country','like','%'.$locationarr[0].'%')
											->where('job_title','like','%'.$keywords.'%')->get();
					$this->addtoarr($matchedJobs);
				}
            }
		}

		//print_r($refinearr);exit();
		if(!empty($refinearr['0'])){
			$arr = explode(",", $refinearr['0']);
			if(sizeof($arr)>0){
                foreach ($arr as $c) {
                    $matchedJobs = Job_post::whereIn('job_id',$this->jids)->where('location','like','%'.$c.'%')->where('status',1)->get();
                    $this->addtoarr($matchedJobs);
                }
            }
		}
		if(!empty($refinearr['1'])){
			$arr = explode(",", $refinearr['1']);
			if(sizeof($arr)>0){
                foreach ($arr as $c) {
                    $matchedJobs = Job_post::whereIn('job_id',$this->jids)->where('loc_city','like','%'.$c.'%')->where('status',1)->get();
                    $this->addtoarr($matchedJobs);
                }
            }
		}
		if(!empty($refinearr['4'])){
			$arr = explode(",", $refinearr['4']);
			if(sizeof($arr)>0){
			  $matchedJobs = Job_post::whereIn('job_id',$this->jids)->whereIn('industry_type',$arr)->where('status',1)->get();
			  $this->addtoarr($matchedJobs);
			}
		}
		if(!empty($refinearr['5'])){
			$arr = explode(",", $refinearr['5']);
			if(sizeof($arr)>0){
			  $matchedJobs = Job_post::whereIn('job_id',$this->jids)->whereIn('employer_company_name',$arr)->where('status',1)->get();
			  $this->addtoarr($matchedJobs);
			}
		}
		if(!empty($refinearr['2'])){
			$arr = explode(",", $refinearr['2']);
			if(sizeof($arr)>0){
                foreach ($arr as $t) {
                    $matchedJobs = Job_post::whereIn('job_id',$this->jids)->where('job_title','like','%'.$t.'%')->where('status',1)->get();
					$this->addtoarr($matchedJobs);
                }
            }
		}
		$search = $matchedJobs;
		$heading = $request->heading;
		$topemployers = Top_Employer::all();
		$consultant = Employer::where('type','2')->get();
		//print_r($search);exit();
		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines',$refinearr);
	}
	public function featured_all(){
		$search = Job_post::where('featured_job','2')->where('type',1)
						   ->where('job_expire','>=',date('Y-m-d'))
						   ->where('status',1)
						   ->orderBy('created_at','DESC')->get();
		$keywords = "featured";
		$location = "";
		$heading = "Jobs found";
		$topemployers = Top_Employer::all();
		$consultant = Employer::where('type','2')->get();
  		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function topemployers_all(){
		$top_emps = Top_Employer::all();
		$search = "";
		$search = Job_post::where('top_search',2)
							->where('type',1)
							->where('status',1)
							->where('job_expire','>=',date('Y-m-d'))
							->orderBy('created_at','DESC')->get();
		$keywords = "topemployers";
		$location = "";
		$heading = " Jobs found";
		$topemployers = Top_Employer::all();
		$consultant = Employer::where('type','2')->get();
   		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function walkins_all(){
		$search = Job_post::where('walk_in_interview','1')->where('type',1)
						->where('job_expire','>=',date('Y-m-d'))
						->where('status',1)
						->orderBy('created_at','DESC')->get();
		$keywords = "walkins";
		$location = "";
		$heading = " Jobs found";
		$topemployers = Top_Employer::all();
		$consultant = Employer::where('type','2')->get();
   		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function freshers_all(){
		$search = Job_post::where('min_experience','0')->where('type',1)
							->where('job_expire','>=',date('Y-m-d'))
							->where('status',1)
							->orderBy('created_at','DESC')->get();
		$keywords = "freshers";
		$location = "";
		$heading = " Jobs found";
		$topemployers = Top_Employer::all();
		$consultant = Employer::where('type','2')->get();
  		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function partime_all(){
		$search = Job_post::where('job_type',2)->where('type',1)
							->where('job_expire','>=',date('Y-m-d'))
							->where('status',1)
							->orderBy('created_at','DESC')->get();
		$keywords = "part-time";
		$location = "";
		$heading = " Jobs found";
		$topemployers = Top_Employer::all();
		$consultant = Employer::where('type','2')->get();
  		return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function jd_sendmail(Request $request){
		$jobId =  $request->jobid;
		$url = url('/job-detail/'.$jobId);
		$this->search_employer_email = "admin@enterprisejobs.com";
        $this->mail_search_subject = 'New Message from EnterpriseJobs';
		$mail_data = array(
                         'email' => $request->email,
                         'data' => $url,
                         'name' => $request->name,
                     );
        Mail::send('email.job_friend', $mail_data, function ($message) use ($mail_data) {
                             $message->subject($this->mail_search_subject)
                                     ->from($this->search_employer_email)
                                     ->to($mail_data['email']);
        });
        echo 1;
        die();
	}
	public function addtoarr($arr){
        if(count($arr)>0){
            foreach($arr as $a){
                array_push($this->jids, $a->job_id);              
            }
        }
    }
    public function send_empfeedback(Request $request){
        $feedback = new Feedback();
        $feedback->name = $request->name;
        $feedback->email = $request->email;
        $feedback->contact_no = $request->mobnumber;
        $feedback->country_code = $request->country_code;
        $feedback->message = $request->message;
        $feedback->type = $request->type;
        $feedback->company_name = $request->fmodal_company;
        $feedback->created_at = date("Y-m-d H:i:s");
        $feedback->updated_at = date("Y-m-d H:i:s");
        $feedback->save();
        $redirect_to = url('/employer-profile');
        return redirect($redirect_to);
    }
    public function send_feedback(Request $request){
        $feedback = new Feedback();
        $feedback->name = $request->name;
        $feedback->email = $request->email;
        $feedback->contact_no = $request->mobnumber;
        $feedback->country_code = $request->country_code;
        $feedback->message = $request->message;
        $feedback->type = $request->type;
        $feedback->created_at = date("Y-m-d H:i:s");
        $feedback->updated_at = date("Y-m-d H:i:s");
        $feedback->save();
        echo 1; die();
    }
    // ============= About us 15-3-2018 =============
    public function about_us(Request $request){
   	 	return view('about_us');
	}

	public function employer_testimonial(Request $request){
		$speaks = Testimonials::where('status',1)->get();
		return view('employer_testimonial',compact('speaks'));
	}

	public function jd_report(Request $request){
		$job_id = $request->jobid;
		$email = $request->email;
		$name = $request->name;
		$desc = $request->desc;

		$ra = new Report_Abuse();
		$ra->job_id = $job_id;
		$ra->name = $name;
		$ra->email = $email;
		$ra->desc = $desc;
		$ra->save();
		echo 1; die();
	}
	public function jobsby_locs(Request $request){
		$jobsbylocs = Jobsby_Locations::all();
		return view('jobsby_locs',compact('jobsbylocs'));
	}
	public function jobsby_skills(Request $request){
		$jobsbyskills = Jobsby_skills::all();
		return view('jobsby_skills',compact('jobsbyskills'));
	}
	public function jobsby_sector(Request $request){
		$bysector = IndustryType::orderBy('industry_type_name')->get();
		return view('jobsby_sector',compact('bysector'));
	}
	public function jobsby_company(Request $request){
		$companies = Employer::where('type',1)->get(['employer_id','company_name']);
		$type = "1";	
		return view('jobsby_company',compact('companies','type'));
	}
	public function jobsby_agency(Request $request){
		$companies = Employer::where('type',2)->get(['employer_id','company_name']);
		$type = "2";	
		return view('jobsby_company',compact('companies','type'));
	}
	   // ============= Career-FAQ-Contacct 29-3-2018 =============
    public function career_enterprise(Request $request){
   	 	return view('career_enterprise');
	}
	public function contact_employer(Request $request){
   	 	return view('contact-employer');
	}
	public function job_seeker_faq(Request $request){
		$faqs = Faqs::where('used_for',1)->get();
		return view('employee.job_seeker_faq',compact('faqs'));
	}
	public function employer_faq(Request $request){
		$faqs = Faqs::where('used_for',2)->get();
		return view('employer.employer_faq',compact('faqs'));
	}
	public function contact_enterprise(Request $request){
		return view('contact-enterprise');
	}
	public function adv_search(Request $request){
		// dd($request->all());
		$keywords = $request->adv_keywords;
		$location = $request->loc;
		$radioval = $request->role_radio;
		$itype = $request->adv_sector;
		$farea = $request->adv_farea;
		$jobtype = $request->adv_jobtype;
		$minexp = $request->min_exp;
		$maxexp = $request->max_exp;
		$design = $request->design;
		$minsal = $request->min_sal;
		$maxsal = $request->max_sal;
		$excludings = $request->adv_exkeywords;
		$currency=$request->adv_currency;

		$refinearr['0'] = '';
		$refinearr['1'] = '';
		$refinearr['2'] = $request->design;
		$refinearr['3'] = $request->adv_farea;
		$refinearr['4'] = $request->adv_sector;
		$refinearr['5'] = '';
		$refinearr['6'] = "";
		$refinearr['7'] = $request->role_radio;
		if($request->min_exp >=5)
		{
			$refinearr['8'] =$request->min_exp.'-0';
		}
		else{

		$refinearr['8'] = $request->min_exp.'-'.$request->max_exp;
		}
		$refinearr['9'] = '';
		$refinearr['10'] = $request->min_sal.'-'.$request->max_sal;
		$refinearr['11'] = '';
		$refinearr['12'] = $request->design;
		$refinearr['13'] = '';
		$refinearr['14'] = '';

		

		$date=date('Y-m-d',strtotime('-1 month',strtotime(date('Y-m-d'))));
		$matchedJobs = Job_post::where('type',1)->where('status',1)->where('job_expire','>=',date('Y-m-d'))->pluck('job_id')->toArray();
		
		//dd($matchedJobs);
		$title=$request->adv_keywords;
		if(!empty($title))
		{
			// dd(explode(',', $title));
			$title=explode(',', $title);
			$result=[];
			foreach ($title as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_title','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->Orwhere(function($sq) use ($key)
                                {
                                   $sq->whereIn('job_id',function($sq) use ($key)
                                        {
                                           $sq->select('job_id_fk')
                                                ->from('job_post_key_skills')
                                                ->where('skill','like','%'.$key.'%')
                                                ->pluck('job_id_fk')->toarray();
                                        });
                                })->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			 //dd($result);
			$matchedJobs=$result;
		}
		$title=$request->adv_exkeywords;
		if(!empty($title))
		{
			// dd(explode(',', $title));
			$title=explode(',', $title);
			$result=[];
			foreach ($title as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_title','not like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		// $experience1=$request->min_exp;
		if(!empty($request->min_exp))
		{
			$experience1=$request->min_exp;
		}
		else
		{
			$experience1='';
		}
		// $experience1=$request->min_exp;
		if(!empty($request->max_exp))
		{
			$experience2=$request->max_exp;
		}
		else
		{
			$experience2='';
		}
		if($experience1!='' || $experience2!='')
		{
			// dd($experience2);
			$result=[];
			if($experience1=='')
			{
				$experience1=0;
			}
			if($experience2=='')
			{
				$experience2=0;
			}
			if($experience2==0 || $experience2==41)
			{
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('min_experience','>=',$experience1)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			}
			else{
				$data=Job_post::whereIn('job_id',$matchedJobs)->whereBetween('min_experience',[$experience1,$experience2])->whereBetween('max_experience',[$experience1,$experience2])->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			}
				$result=array_merge($data,$result);
			// dd($result);
			$matchedJobs=$result;
		}
		// dd("aaa");


		$location=$request->loc;
		if(!empty($location))
		{
			// dd(explode(',', $location));
			$location=explode(',', $location);
			$result=[];
			// foreach ($location as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('location','like','%'.$location[0].'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			// }
			// dd($result);
			$matchedJobs=$result;
		}

		$industry=$request->adv_sector;
			// dd(explode(',', $industry));
		if(!empty($industry))
		{
			$industry=explode(',', $industry);
			$result=[];
			foreach ($industry as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('industry_type','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$function=$request->adv_farea;
		if(!empty($function))
		{
			// dd(explode(',', $title));
			$function=explode(',', $function);
			$result=[];
			foreach ($function as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('functional_area','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		

		$job_type=$request->adv_jobtype;
		if(!empty($job_type))
		{
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_type',$job_type)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}

		$currency=$request->adv_currency;
		if(!empty($currency))
		{
			// print_r($currency);die;
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('currency_type',$currency)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}
		$salary1=$request->min_sal;
		$salary2=$request->max_sal;
		if($salary1 == 999 && empty($salary2))
		{
			// print_r($currency);die;
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_min','<=',$salary1)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}elseif( $salary2 == 99001 && empty($salary1)){
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_max','>=',$salary2)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}elseif($salary1 == 999 && $salary2 == 99001)
		{
			// print_r($currency);die;
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_min','<=',$salary1)->where('salary_max','>=',$salary2)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}
		elseif(!empty($salary1) && !empty($salary2))
		{
			// print_r($currency);die;
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->whereBetween('salary_min',[$salary1,$salary2])->whereBetween('salary_max',[$salary1,$salary2])->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}elseif(!empty($salary1) && empty($salary2))
		{
			// print_r($currency);die;
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_min','<=',$salary1)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}elseif(empty($salary1) && !empty($salary2))
		{
			// print_r($currency);die;
			$result=[];
			$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_max','<=',$salary2)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			$result=array_merge($data,$result);
			$matchedJobs=$result;
		}

		/*if(!empty($request->min_sal))
		{
			$salary1=$request->min_sal;
		}
		else
		{
			$salary1='';
		}
		// $experience1=$request->min_sal;
		if(!empty($request->max_sal))
		{
			$salary2=$request->max_sal;
		}
		else
		{
			$salary2='';
		}
		if($salary1!='' & $salary2!='')
		{
			$result=[];
			// dd($experience);
			if($salary1=='')
			{
				$salary1=0;
			}
			if($salary2=='')
			{
				$salary2=0;
			}
			if($salary2==0 || $salary2==41)
			{
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('salary_min','>=',$salary1)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			}
			else{
				$data=Job_post::whereIn('job_id',$matchedJobs)->whereBetween('salary_min',[$salary1,$salary2])->whereBetween('salary_max',[$salary1,$salary2])->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
			}
				$result=array_merge($data,$result);
			// dd($result);
			$matchedJobs=$result;
		}*/

		$designation=$request->design;
		if(!empty($designation))
		{
			// dd(explode(',', $title));
			$designation=explode(',', $designation);
			$result=[];
			foreach ($designation as $key) {
				$data=Job_post::whereIn('job_id',$matchedJobs)->where('job_title','like','%'.$key.'%')->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			}
			// dd($result);
			$matchedJobs=$result;
		}

		$emp_type=$request->role_radio;
		if(!empty($emp_type))
		{
			$result=[];
				$emp= Employer::where('type',$emp_type)->pluck('user_id_fk')->toArray();
				$user=User::whereIn('id',$emp)->pluck('id')->toArray();
				$data=Job_post::whereIn('job_id',$matchedJobs)->whereIn('user_id_fk',$user)->WhereNotIn('job_id',$result)->where('status',1)->pluck('job_id')->toArray();
				$result=array_merge($data,$result);
			// dd($result);
			$matchedJobs=$result;
		}
		$keywords = $request->adv_keywords;
		$location = $request->loc;
		if(!empty($location)){
			$heading = $keywords." Jobs in ".$location;
		}
		else{
			$heading = $keywords." Jobs found ";
		}
		
		// dd($matchedJobs);
        if(!empty($location)){
            $locationarr = explode(",", $location);
            $locationlen = count($locationarr);
            // dd($locationarr);
            if($locationlen == 4){
            	$place=$locationarr[0];
            }
            else if($locationlen == 3){
            	$city=$locationarr[0];
            	$country=$locationarr[2];
            }
            else if($locationlen == 2){
            	$place=$locationarr[0];
            }
            else{
            	$city=$locationarr[0];
            	$country=$locationarr[0];

            }
		}

		$topemployers = Top_Employer::all();
			$search=Job_post::whereIn('job_id',$matchedJobs)->orderBy('created_at')->where('job_expire','>=',date('Y-m-d'))->where('status',1)->get();
			$consultant = Employer::where('type','2')->get();
	   return view('job_listing',compact('search','keywords','location','heading','topemployers','city','country','consultant'))->with('refines',$refinearr);
	}
	public function adv_search2(Request $request){
		$keywords = $request->adv_keywords;
		$location = $request->loc;
		$radioval = $request->role_radio;
		$itype = $request->adv_sector;
		$farea = $request->adv_farea;
		$jobtype = $request->adv_jobtype;
		$minexp = $request->min_exp;
		$maxexp = $request->max_exp;
		$design = $request->design;
		$minsal = $request->min_sal;
		$maxsal = $request->max_sal;
		$excludings = $request->adv_exkeywords;
		$users_arr = array();
		if($radioval == 1){
			$company_users = Employer::where('type',1)->get();
			if(count($company_users)>0){
				foreach($company_users as $company_user)
				array_push($users_arr, $company_user->user_id_fk);
			}
		}
		else{
			$recruiter_users = Employer::where('type',2)->get();
			if(count($recruiter_users)>0){
				foreach($recruiter_users as $recruiter_user)
				array_push($users_arr, $recruiter_user->user_id_fk);
			}
		}
		$jobs = Job_post::whereIn('user_id_fk',$users_arr)->where('type',1)->where('status',1)->where('job_expire','>=',date('Y-m-d'))->get();
		$jobids_arr = array();
		$rawQry = "";
		if(count($jobs)>0){
			foreach($jobs as $job){
				array_push($jobids_arr,$job->job_id);
			}
			$jobid_csl = implode(",", $jobids_arr);
			$rawQry = $rawQry." job_id in (".$jobid_csl.")";
		}
		if(!empty($location)){
			$heading = $keywords." Jobs in ".$location;
		}
		else{
			$heading = $keywords." Jobs found ";
		}
		if(!empty($keywords)){
			if(!empty($rawQry)){
				$rawQry = $rawQry." and job_title like '%".$keywords."%'";
			}
			else{
				$rawQry = $rawQry." job_title like '%".$keywords."%'";
			}
		}
		if(!empty($design)){
			if(!empty($rawQry)){
				$rawQry = $rawQry." and job_title like '%".$design."%'";
			}
			else{
				$rawQry = $rawQry." job_title like '%".$design."%'";
			}
		}
        //print_r($loc);exit();
        if(!empty($location)){
            $locationarr = explode(",", $location);
            $locationlen = count($locationarr);
            if($locationlen == 4){
	            if(!empty($rawQry)){
					$rawQry = $rawQry." and loc_area like '%".$locationarr[0]."%'";
				}
				else{
					$rawQry = $rawQry." loc_area like '%".$locationarr[0]."%'";
				}
            }
            else if($locationlen == 3){
            	if(!empty($rawQry)){
					$rawQry = $rawQry." and loc_city like '%".$locationarr[0]."%'";
				}
				else{
					$rawQry = $rawQry." loc_city like '%".$locationarr[0]."%'";
				}
            }
            else if($locationlen == 2){
            	if(!empty($rawQry)){
					$rawQry = $rawQry." and loc_state like '%".$locationarr[0]."%'";
				}
				else{
					$rawQry = $rawQry." loc_state like '%".$locationarr[0]."%'";
				}
            }
            else{
            	if(!empty($rawQry)){
					$rawQry = $rawQry." and loc_country like '%".$locationarr[0]."%'";
				}
				else{
					$rawQry = $rawQry." loc_country like '%".$locationarr[0]."%'";
				}
            }
		}
		if(!empty($itype)){
			if(!empty($rawQry)){
				$rawQry = $rawQry." and industry_type like '%".$itype."%'";
			}
			else{
				$rawQry = $rawQry." industry_type like '%".$itype."%'";
			}
		}
		if(!empty($farea)){
			if(!empty($rawQry)){
				$rawQry = $rawQry." and functional_area like '%".$farea."%'";
			}
			else{
				$rawQry = $rawQry." functional_area like '%".$farea."%'";
			}
		}
		if(!empty($jobtype)){
			if(!empty($rawQry)){
				$rawQry = $rawQry." and job_type=".$jobtype;
			}
			else{
				$rawQry = $rawQry." job_type=".$jobtype;
			}
		}
		if($minexp != ''){
        	if(!empty($rawQry)){
				$rawQry = $rawQry." and min_experience like '%".$minexp."%'";
			}
			else{
				$rawQry = $rawQry." min_experience like '%".$minexp."%'";
			}
        }
        if(!empty($maxexp)){
        	if(!empty($rawQry)){
				$rawQry = $rawQry." and max_experience like '%".$maxexp."%'";
			}
			else{
				$rawQry = $rawQry." max_experience like '%".$maxexp."%'";
			}
        }
        if($minsal != ''){
        	if(!empty($rawQry)){
				$rawQry = $rawQry." and salary_min like '%".$minsal."%'";
			}
			else{
				$rawQry = $rawQry." salary_min like '%".$minsal."%'";
			}
        }
        if($maxsal != ''){
        	if(!empty($rawQry)){
				$rawQry = $rawQry." and salary_max like '%".$maxsal."%'";
			}
			else{
				$rawQry = $rawQry." salary_max like '%".$maxsal."%'";
			}
        }
        if(!empty($excludings)){
        	if(!empty($rawQry)){
				$rawQry = $rawQry." and job_title not like '%".$excludings."%'";
			}
			else{
				$rawQry = $rawQry." job_title not like '%".$excludings."%'";
			}
        }
		//echo $rawQry; exit();
		if(!empty($rawQry)){
            $matchedJobs = Job_post::whereRaw($rawQry)->get();
            $this->addtoarr($matchedJobs);
		}
		$topemployers = Top_Employer::all();
		$search = Job_post::whereIn('job_id',$this->jids)->orderBy('created_at','DESC')->where('status',1)->get();
		$consultant = Employer::where('type','2')->get();
	   return view('job_listing',compact('search','keywords','location','heading','topemployers','consultant'))->with('refines','');
	}
	public function chartdata(){
		try{
          $serviceName = "get_chartdata";

          	$tot_js_reg = User::where('role',2)->get();
            $return_arr[] = array(
                'team' =>'TeamA',
                'matchname' =>'Total Job seekers Registered',
                'score' =>count($tot_js_reg),
            );

            $tot_emp_reg = User::where('role',3)->get();
            $return_arr[] = array(
                'team' =>'TeamA',
                'matchname' =>'Total Employers Registered',
                'score' =>count($tot_emp_reg),
            );

            $tot_email_confirmed = User::where('email_verify',2)->get();
            $return_arr[] = array(
                'team' =>'TeamA',
                'matchname' =>'Total Users Email Confirmed',
                'score' =>count($tot_email_confirmed),
            );

            $tot_jobs_active = Job_post::where('type',1)
            							->where('job_expire','>=',date('Y-m-d'))
            							->where('status',1)
            							->get();
            $return_arr[] = array(
                'team' =>'TeamA',
                'matchname' =>'Total Active jobs',
                'score' =>count($tot_jobs_active),
            );

            $tot_job_views = Job_post::where('type',1)
            							->where('job_expire','>=',date('Y-m-d'))
            							->where('status',1)
            							->sum('view_count');
            $return_arr[] = array(
                'team' =>'TeamA',
                'matchname' =>'Total Job Views',
                'score' =>$tot_job_views,
            );

            $active_emp = User::select('*')
            						 ->join('last_login', 'users.id', '=', 'last_login.user_id_fk')
            						 ->where('users.role','3')
            						 ->groupBy('users.id')
            						 ->get();
            $active_emp_arr = array();
         	foreach($active_emp as $aemp){
         		array_push($active_emp_arr,$aemp->id);
         	}
            $tot_emp_notactive = User::WhereNotIn('id',$active_emp_arr)->get();
            $return_arr[] = array(
                'team' =>'TeamA',
                'matchname' =>'Total Employers not active',
                'score' =>count($tot_emp_notactive),
            );


          	return response()->json($return_arr);
      }
      catch(Exception $e){
            $data["message"] = "Something went wrong.<br />Please try again";
            Seekahoo_lib::return_status('error', $serviceName,$data,'');
            return response()->json(array('success' => false,'status_code' => 100,'error' => 'server_error', 'message' => "Something went wrong.<br />Please try again"),500);
      }
	}
	public function contact_enquiry(Request $request){
		//print_r($request->all());
		$fname = $request->fname;
		$lname = $request->lname;
		$mobile = $request->mobile;
		$email = $request->email;
		$company = $request->company;
		$message = $request->message;
		$type = $request->type;
		$ce_new = new Contact_Enquiries();
		$ce_new->fname = $fname;
		$ce_new->lname = $lname;
		if(strlen((string)$mobile)>=10 )
		{
			$ce_new->mobile = $mobile;
		}else{
			//return back()->with('succeesmsg','1');
			echo 2; die();
		}		
		
		$ce_new->email = $email;
		$ce_new->company = $company;
		$ce_new->message = $message;
		$ce_new->type = $type;
		$ce_new->save();
		if($ce_new->id){
			echo 1; die();
		}
		echo 4; die();
		/*if(!empty($company)){
			$redirect_to = url('/contact-employer');
		}
		else{
			$redirect_to = url('/contact-enterprise');
		}*/
		//return redirect($redirect_to)->with('succeesmsg','1');
	}
	public function ccavenue(){
		$parameters = [
      
        'tid' => '1233221223322',
        
        'order_id' => '1232212',
        
        'amount' => '1200.00',
        
      ];
      
      // gateway = CCAvenue / PayUMoney / EBS / Citrus / InstaMojo / ZapakPay / Mocker
      
      $order = Indipay::gateway('CCavenue')->prepare($parameters);
      return Indipay::process($order);
	}
	public function response(Request $request)
    {
        // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCavenue')->response($request);

        dd($response);
    
    } 
    public function cv_send(Request $request)
    {
    	//print_r($request->all());exit();
    	$data = User::where('id',$request->id)->first();
    	//print_r($data);exit();
    	echo json_encode($data); 
    }
    public function sendcv_form(Request $request)
    {
    	//print_r($request->all());exit();
    	$email = $request->cunsultent_email;
    	$description = $request->description;
    	$filelocation = "";
    	if($request->hasFile('resume'))
            {
                $destination = 'uploads/resume';  
                $file = $request->file('resume');
                $file->move($destination, $destination. "/" .time().'-'.$file->getClientOriginalName());
                $filelocation = $destination. "/" .time().'-'.$file->getClientOriginalName();
                
            }
    	Mail::raw($description, function ($message) use ($email,$filelocation,$description) {
                             $message->subject('Enhance Our CV')
                                     ->from('developer10@indglobal-consulting.com')
                                     ->to($email)
                                      ->attach($filelocation);
                });
    	if (Mail::failures()) {
        echo 2;die();
    	}else{
    		echo 1;die();
    	}
    	
    }
}