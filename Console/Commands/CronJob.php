<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Cv_search_details;
use App\Model\Job_post;
use App\Model\Saved_job;
use App\Model\Applied_job;
use App\Model\Report_Abuse;
use App\Model\Job_post_keyskills;
use App\Model\User_package;
use App\User;
use App\Model\Employer;
use App\Model\Job_seeker_personal_details;

class CronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronJob:cronjob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User Name Change Successfully';

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
        \Log::info('CronJob Started');
        $search_data = Cv_search_details::where('search_id_fk', 3)->update(["keyword"=>"bskr"]);

            //job post expiry
            $job = Job_post::where('status',1)->where('type',1)->get();
            foreach ($job as $j) {
                $job_expire = $j->job_expire;
                if(!empty($job_expire))
                { $job_expire = date('d-m-Y H:i:s',strtotime($job_expire));
                  $date = date('d-m-Y H:i:s');
                    if($date >= $job_expire )
                    {
                        $job = Job_post::where('job_id',$j->job_id)->first();
                        $job->status = "2";
                        $job->save();
                    }
                }
            }
            //expired job delete after 15 days
            $jobsposted =  Job_post::where('status',2)->where('type',1)->get();
            foreach ($jobsposted as $j) 
            {
              if(date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', strtotime($j->job_expire. ' + 15 day')) )
              {
                Saved_job::where('job_id_fk',$j->job_id)->delete();
                Applied_job::where('job_id_fk',$j->job_id)->delete();
                Report_Abuse::where('job_id',$j->job_id)->delete();
                Job_post_keyskills::where('job_id_fk',$j->job_id)->delete();
                Job_post::where('job_id',$j->job_id)->delete();
              }                                                                
                
            }

            //package expire
            $package = User_package::where('status',2)->get();
            foreach ($package as $pack) {
                if(date('Y-m-d H:i:s') >= date('Y-m-d H:i:s',strtotime($pack->expiry_date)))
                {
                User_package::where('user_package_id',$pack->user_package_id)->update(['status'=>'3']);
                   
                }
            }

            //Varification Link Expiry
            $user = User::where('email_verify','1')->get();
            foreach ($user as $key) {
                if(!empty($key->link_expiry))
                {
                    if(date('Y-m-d H:i:s') >= date('Y-m-d H:i:s',strtotime($key->link_expiry.' +1 day')))
                    {
                        if($key->role == "2")
                        {
                            Job_seeker_personal_details::where('user_id_fk',$key->id)->delete();
                            User::where('id',$key->id)->delete();
                        }elseif($key->role == "3")
                        {
                            Employer::where('user_id_fk',$key->id)->delete();
                            User::where('id',$key->id)->delete();
                        }
                        
                    }
                }    
               
            }
            \Log::info('CronJob Ended');

    }
}
