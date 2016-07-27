<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Exception, Log, DB;

use App\Classes\GlobeApi;
use App\Classes\WebScraping;

use App\Http\Controllers\Api\v1\AlertController;
use App\Contracts\AlertInterface as Alert;

class SendSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:sms {time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an Alert via GlobeLabs SMS API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        Alert $alert,
        AlertController $alertController)
    {
        $this->alert = $alert;
        $this->alertController = $alertController;

        parent::__construct();

        DB::enableQueryLog();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // Set the new cron job again
            $this->alertController->_validateCron();

            $time = $this->argument('time');
            // Get all alerts that needs to be sent by the given time
            $alerts = $this->_getAlerts($time);

            // Process the webscraping and send sms
            $response = $this->_processAlerts($alerts);

        } catch (Exception $e) {

        }

    }

    /**
     * Get all alerts that needs to be sent
     * by the given time
     *
     * @param date time
     * @return void
     */
    public function _getAlerts($time = '')
    {
        if (empty($time)) {
            return;
        }

        $alerts = $this->alert->getList([
            'where' => [
                'and' => [
                    ['raw' => 'DATE_FORMAT(scheduled_date, "%Y-%m-%d %H:%i") = "'. date('Y-m-d H:i', strtotime($time)) .'"']
                ]
            ]
        ]);

        if (empty($alerts)) {
            Log::info('Send SMS: No Alerts');
            return;
        }

        return $alerts;
    }

    /**
     * Process Alerts
     * Get the needed Data by webscraping class
     * Send the sms by GlobeApi class
     *
     * @param array $alerts
     * @return void
     */
    public function _processAlerts($alerts = [])
    {
        if (empty($alerts)) {
            return;
        }

        foreach ($alerts as $key) {
            $category_id = $key->category_id;
            $location = $key->location;

            // Data Mining
            $webScrape = new WebScraping($category_id);
            $data = $webScrape->getData($location);

            if (empty($data)) {
                continue;
            }

            // Parse Message
            $message = $this->_parseMessage($category_id, json_decode($data, true));

            // Send SMS
            $this->_sendSms($key->user->accesstoken, $key->user->msisdn, $message);

            // Update New Scheduled Date
            $date = $this->alertController->_setScheduledDate($key->scheduled_date, $key->repetition_id);
            $response = $this->alert->update($key->id, [
                'scheduled_date' => $date
            ]);
            Log::info('Send SMS > New Scheduled Date for '. $key->id .': '. $date);
            Log::info($response);

            exit;
        }
    }

    /**
     * Parse the data corresponds to the categories
     *
     * @param int $category_id
     * @param array $data
     * @return void
     */
    public function _parseMessage($category_id = '', $response = [])
    {
        if (empty($response)) {
            return;
        }

        $data = $response['data'][0];

        switch ($category_id) {
            case '1':
                // climatex - Weather
                $message = 'WEATHER ADVISORY:';
                $message .= ' There is a '. $data['chance_of_rain'] .' chance of rain';
                $message .= ' in '. $data['location'] .'.';
                $message .= ' [From Heads Up]';
                break;

            case '2':
                // road traffic - mmda
                $message = 'TRAFFIC ADVISORY in '. $data['name'];
                $message .= ' as of '. $data['north_bound']['datetime'];
                $message .= ' North Bound: '. $data['north_bound']['status'];
                $message .= ' South Bound: '. $data['south_bound']['status'];
                break;
                $message .= ' [From Heads Up]';

            case '3':
                // mrt3 service status
                $message = 'MRT LINE 3 ADVISORY: ';
                $message .= ' As of '. $data['time'] .': ';
                $message .= ' '. $data['station'] .' Station '. $data['bound'];
                $message .= ' Status: '. $data['description'];
                $message .= ' [From Heads Up]';
                break;

            default:
                $message = '';
                break;
        }
        Log::info('SendSMS: '. $message);
        return $message;
    }

    /**
     * Send SMS via Globe Labs SMS API
     *
     * @param string $access_token
     * @param string msisdn
     * @param string messages
     * @return void
     */
    public function _sendSms($accesstoken = '', $msisdn = '', $message = '')
    {
        //$access_token = 'tMkc6GVDJN3-0KKDMDyWbbN9JpUg_ZtqLqWbRB8wDdM';

        $response = new GlobeApi();
        $sms = $response->sms(5527);
        $response = $sms->sendMessage($accesstoken, $msisdn, $message);

        Log::info('Send SMS: send SMS');
        Log::info($response);
        return $response;
    }
}
