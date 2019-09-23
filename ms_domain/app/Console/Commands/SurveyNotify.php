<?php

namespace App\Console\Commands;

use App\Helpers\CurlHelper;
use Illuminate\Console\Command;
use DB;

class SurveyNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:survey-start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New Survey Started';

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
    	$date = date('Y-m-d H:i');

	    $surveys = \DB::select('SELECT `tutella-surveys`.surveys.id, `tutella-surveys`.surveys.name, `tutella-surveys`.assignees.user_id,
               `tutella-domain`.users.firebase_token FROM `tutella-surveys`.surveys
               JOIN `tutella-surveys`.assignees ON `tutella-surveys`.surveys.id = `tutella-surveys`.assignees.survey_id
               JOIN `tutella-domain`.users ON `tutella-surveys`.assignees.user_id = `tutella-domain`.users.user_id
                WHERE `tutella-surveys`.surveys.start_date LIKE "' . $date . '%" and `tutella-surveys`.assignees.user_id != `tutella-surveys`.surveys.created_by and `tutella-surveys`.assignees.status = 0');

	    if(!empty($surveys)) {
            foreach($surveys as $survey) {
                $surveys_count = \DB::select('SELECT COUNT(*) as count FROM `tutella-surveys`.surveys
                                    JOIN `tutella-surveys`.assignees ON `tutella-surveys`.surveys.id = `tutella-surveys`.assignees.survey_id
                                    WHERE `tutella-surveys`.assignees.user_id = '. $survey->user_id .' and `tutella-surveys`.assignees.status = 0 and `tutella-surveys`.surveys.start_date <= "'.date('Y-m-d H:i:59').'" and `tutella-surveys`.surveys.expires_at >= "'.date('Y-m-d H:i:00').'" and `tutella-surveys`.assignees.user_id != `tutella-surveys`.surveys.created_by');
                 $survey->count = $surveys_count[0]->count;
               $this->line($surveys_count[0]->count);

            }

		    $notificationData = ['surveys' => $surveys];

		    $notifications_url = env('NOTIFICATIONS_URL') . 'surveys/notifyStart';

		    CurlHelper::curlPost($notifications_url, $notificationData, true);
	    }
    }
}
