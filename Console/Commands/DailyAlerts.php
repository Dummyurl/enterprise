<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Cv_search_details;
use App\Model\Cvsearch_save;
use App\Model\Job_seeker_technical_skills;
use App\Model\Job_seeker_personal_details;
use App\Model\Job_preference;
use App\User;
use App\Model\Job_post;
use Mail;

class DailyAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyAlerts:dailyalerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alerts sent successfully';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
      $savedsearches = Cvsearch_save::all();
      if(count($savedsearches)>0){
        foreach($savedsearches as $savedsearch){
            if(($savedsearch->alert_opt == 1) && ($savedsearch->cv_frequency == 3)){
                $searchID = $savedsearch->search_id_fk;
                $search_data = Cv_search_details::where('search_id_fk', $searchID)->get();

                $jsids = array();
                //$job_title[0]['jp_type'] == 1
                $title = $search_data[0]['keyword'];
                $farea = $search_data[0]['farea'];
                $itype = $search_data[0]['industry'];
                $visaval = $search_data[0]['visa_status'];
                $locations = $search_data[0]['cur_loc'];
                $genderval = $search_data[0]['gender'];
                if(!empty($title)){
                    $jobseekers = Job_seeker_technical_skills::where('skill','like','%'.$title.'%')->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($farea)){
                    $jobseekers = Job_preference::where('preferred_job_function',$farea)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($itype)){
                    $jobseekers = Job_preference::where('preferred_industry_type',$itype)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($visaval)){
                    $jobseekers = Job_seeker_personal_details::where('current_visa_status',$visaval)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($locations)){
                    $jobseekers = Job_seeker_personal_details::where('current_location',$locations)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                if(!empty($genderval)){
                    $jobseekers = Job_seeker_personal_details::where('gender',$genderval)->get();
                    if(count($jobseekers)>0){
                        foreach($jobseekers as $jobseeker){
                            array_push($jsids, $jobseeker->user_id_fk);              
                        }
                    }
                }
                //print_r($jsids);
                /*$data = User::whereIn('id',$jsids)->get();
                return view('email/js_details',compact('data'));*/
                $profilematches = User::whereIn('id',$jsids)->get();
                $mail_data = array(
                         'email' => $savedsearch->email,
                         'data' => $profilematches,
                     );
                Mail::send('email.js_details', $mail_data, function ($message) use ($mail_data) {
                             $message->subject('Matching Profiles for your search criteria')
                                     ->from('admin@enterprise.com')
                                     ->bcc("dev85@indglobal-consulting.com")
                                     ->to($mail_data['email']);
                });
            }
        }
      }
    }
}
