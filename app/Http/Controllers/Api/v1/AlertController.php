<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Validator, Log, Exception, DB, DateTimeZone, DateTime, Carbon\Carbon;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface as User;
use App\Contracts\CategoryInterface as Category;
use App\Contracts\RepetitionInterface as Repetition;
use App\Contracts\AlertInterface as Alert;

use App\Classes\GlobeApi;
use App\Classes\Cron;

class AlertController extends BaseController
{
    public function __construct(
        User $user,
        Category $category,
        Repetition $repetition,
        Alert $alert)
    {
        $this->user = $user;
        $this->category = $category;
        $this->repetition = $repetition;
        $this->alert = $alert;

        DB::enableQueryLog();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|numeric',
                'category_id'       => 'required|numeric',
                // 'name'              => 'required', //optional
                'location'          => 'required',
                'set_date'          => 'required|date',
                'repetition_id'     => 'required|numeric'
            ]);
            if ($validator->fails()) {
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $user_id = $request->input('user_id');
            $category_id = $request->input('category_id');
            $repetition_id = $request->input('repetition_id');

            // VALIDATION - START

            // check if user id exists and active
            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid User");
            }

            // check if valid category
            if (! $this->category->exists(['id' => $category_id])) {
                throw new Exception("Error Processing Request: Invalid Category");
            }

            // check if valid repetition
            if (! $this->repetition->exists(['id' => $repetition_id])) {
                throw new Exception("Error Processing Request: Invalid Repetition");
            }
            // VALIDATION - END

            // Convert Asia/Manila Timezone

            $tz = new DateTimeZone('Asia/Manila');
            $date = new DateTime($request->input('set_date'));
            $date->setTimeZone($tz);
            $set_date = date_format($date, 'Y-m-d H:i:s');

            $data = [
                'category_id' => $category_id,
                'user_id' => $user_id,
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'set_date' => $set_date,
                'scheduled_date' =>  $this->_setScheduledDate($set_date, $repetition_id),
                'repetition_id' => $repetition_id
            ];

            if (! $alert = $this->alert->store($data)) {
                throw new Exception("Error Processing Request: Cannot store alert");
            }

            // Validate New Cron
            $this->_validateCron($alert);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Compute for the date
     * Date will be added to CRON
     *
     * @param date set date by user
     * @param int repetition id
     * @return void
     */
    public function _setScheduledDate($date = '', $repetition_id = '')
    {
        try {
            if (empty($date) || empty($repetition_id)) {
                throw new Exception();
            }

            $repetition = $this->repetition->get($repetition_id)->name;

            $scheduled_date = $date;
            $date_now = date('Y-m-d H:i');

            Log::info('Set Scheduled Date (today '. $date_now .')'. $scheduled_date);

            switch ($repetition) {
                case 'daily':
                    /* check if the input date is ahead of todays time
                     * if yes, the input date will be saved in cron
                     * else, increment date by +1
                     */
                    if (date('Y-m-d H:i', strtotime($date)) > $date_now) {
                        $scheduled_date = $date;
                    } else {
                        $scheduled_date = date('Y-m-d H:i', strtotime('+1 day', strtotime($date)));
                    }
                    break;

                case 'every-weekday':
                    break;

                case 'weekly':
                    break;

                case 'one-time-schedule':
                default:
                    break;
            }
        } catch (Exception $e) {
            return false;
        }

        return $scheduled_date;
    }

    /**
     * Validate whether it needs to recreate cron
     *
     * @param array $alert data
     * @return void
     */
    public function _validateCron($data = [])
    {
        try {
            $alert_id = '';
            $scheduled_date = '';

            if (! empty($data)) {
                $alert_id = $data->id;
                $scheduled_date = $data->scheduled_date;
            }

            //  get the latest scheduled
            $args = [
                'where' => [
                    'and' => [
                        ['field' => 'scheduled_date', 'operator' => '>', 'value' => date('Y-m-d H:i')],
                    ]
                ],
                'order_by' => ['scheduled_date' => 'asc'],
                'limit' => 1
            ];

            if (! empty($alert_id)) {
                $args['where']['and'][] = ['field' => 'id', 'operator' => '!=', 'value' => $alert_id];
            }

            $latest = $this->alert->getList($args);
            Log::info($latest);
            if (! empty($scheduled_date)) {
                /* If empty latest date,
                 *  set cron using the scheduled date
                 */
                if ($latest->isEmpty()) {
                    //echo 'scheduled date';
                    $cron = new Cron();
                    $cron->setNewCron($scheduled_date);
                } else {
                    /* Check if the latest is ahead/greater than the scheduled date
                    * if yes, set new cron
                    * else do nothing
                    */
                    if ($latest[0]->scheduled_date >= $scheduled_date) {
                        //echo 'scheduled date';
                        $cron = new Cron();
                        $cron->setNewCron($scheduled_date);
                    }
                }
            } else {
                /* If empty scheduled date
                 *  set cron using the latest date
                 */
                if (! $latest->isEmpty()){
                    //echo 'latest date';
                    $cron = new Cron();
                    $cron->setNewCron($latest[0]->scheduled_date);
                }
            }

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id user id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // START VALIDATION
            if (empty($id)) {
                throw new Exception("Error Processing Request: User ID is required.");
            }

            // Check if user exists and active
            if (! $this->user->exists(['id' => $id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid User");
            }
            //  END VALIDATION

            $alerts = $this->alert->getList([
                'where' => [
                    'and' => [
                        ['field' => 'user_id', 'value' => $id]
                    ]
                ],
                'order_by' => ['id' => 'desc']
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|numeric',
                'category_id'       => 'required|numeric',
                // 'name'              => 'required', //optional
                'location'          => 'required',
                'set_date'          => 'required|date',
                'repetition_id'     => 'required|numeric'
            ]);
            if ($validator->fails()) {
                throw new Exception(json_to_string($validator->messages()->toArray()));
            }

            $user_id = $request->input('user_id');
            $category_id = $request->input('category_id');
            $repetition_id = $request->input('repetition_id');

            // VALIDATION - START

            // check if user id exists and active
            if (! $this->user->exists(['id' => $user_id, 'active' => 1])) {
                throw new Exception("Error Processing Request: Invalid User");
            }

            // check if alert id exists and active
            if (! $this->alert->exists(['id' => $id])) {
                throw new Exception("Error Processing Request: Invalid Alert");
            }

            // check if valid category
            if (! $this->category->exists(['id' => $category_id])) {
                throw new Exception("Error Processing Request: Invalid Category");
            }

            // check if valid repetition
            if (! $this->repetition->exists(['id' => $repetition_id])) {
                throw new Exception("Error Processing Request: Invalid Repetition");
            }
            // VALIDATION - END

            // Convert Asia/Manila Timezone

            $tz = new DateTimeZone('Asia/Manila');
            $date = new DateTime($request->input('set_date'));
            $date->setTimeZone($tz);
            $set_date = date_format($date, 'Y-m-d H:i:s');

            $data = [
                'category_id' => $category_id,
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'set_date' => $set_date,
                'scheduled_date' =>  $this->_setScheduledDate($set_date, $repetition_id),
                'repetition_id' => $repetition_id
            ];

            if (! $alert = $this->alert->update($id, $data)) {
                throw new Exception("Error Processing Request: Cannot update alert");
            }
            
            // Validate New Cron
            $this->_validateCron($this->alert->get($id));

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**** START - FUNCTIONS FOR TESTING AND DEBUGGING ****/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = new GlobeApi();
        $sms = $response->sms(5527);
        $response = $sms->sendMessage('tMkc6GVDJN3-0KKDMDyWbbN9JpUg_ZtqLqWbRB8wDdM', '+639156809880', 'sample sample');

        print_r($response);
    }

    /**
     * Rewrite the cron job
     * For testing and debugging purposes
     *
     * @return \Illuminate\Http\Response
     */
    public function writeCron()
    {
        $this->_validateCron();
    }

    /**
     * Get the latest date to be run by cron
     * For testing and debugging purposes
     *
     * @return \Illuminate\Http\Response
     */
    public function latestDate()
    {
        //  get the latest scheduled
        $args = [
            'where' => [
                'and' => [
                    ['field' => 'scheduled_date', 'operator' => '>', 'value' => date('Y-m-d H:i')],
                ]
            ],
            'order_by' => ['scheduled_date' => 'asc'],
            'limit' => 1
        ];

        if (! empty($alert_id)) {
            $args['where']['and'] = ['field' => 'id', 'operator' => '!=', 'value' => $alert_id];
        }

        $latest = $this->alert->getList($args);

        if ($latest->isEmpty()) {
            echo 'no latest date to cron';
            return;
        }

        echo $latest[0]->scheduled_date;
    }

    /**** END - FUNCTIONS FOR TESTING AND DEBUGGING ****/
}
