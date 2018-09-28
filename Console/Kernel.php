<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Model\Job_post;
use App\Model\Saved_job;
use App\Model\Applied_job;
use App\Model\Report_Abuse;
use App\Model\Job_post_keyskills;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        '\App\Console\Commands\DailyAlerts',
        '\App\Console\Commands\WeekelyAlerts',
        '\App\Console\Commands\CronJob',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('CronJob:cronjob')->everyMinute(); //demo
        $schedule->command('DailyAlerts:dailyalerts')->daily();
        $schedule->command('WeekelyAlerts:weekelyalerts')->weekly();
        $schedule->call(function () {
           $job = Job_post::where('status',1)->where('type',1)->get();
            foreach ($job as $j) {
                $job_expire = $j->job_expire;
                if(!empty($job_expire))
                { $job_expire = date('Y-m-d H:i:s',strtotime($job_expire));
                  $date = date('Y-m-d H:i:s');
                    if($date >= $job_expire )
                    {
                        $job = Job_post::where('job_id',$j->job_id)->first();
                        $job->status = "2";
                        $job->save();
                    }
                }
            }
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
        })->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}
