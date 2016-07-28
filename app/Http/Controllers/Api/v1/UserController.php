<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Log, DB;

use App\Http\Controllers\BaseController;

use App\Contracts\UserInterface as User;

use App\Classes\GlobeApi;

class UserController extends BaseController
{
    public function __construct(
        User $user)
    {
        $this->user = $user;

        DB::enableQueryLog();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        echo 'call';
    }

    /**
     * Subscribe/Unsubscribe user to Heads Up
     *
     * @param POST data (access token)
     * @return void
     * @author
     **/
    public function auth(Request $request)
    {
        Log::info('Subscriber Consent');
        Log::info($request->all());


        $response = $request->all();

        // Check if for opt-in or opt-out
        if (isset($response['unsubscribed'])) {
            $this->_unsubscribe($response['unsubscribed']);
            return 'ok';
        }

        // Apply for opt-in
        $this->_subscribe($response);
        return 'ok';

    }

    /**
     * Subscribe User
     *
     * @param array data
     * @return void
     * @author
     **/
    public function _subscribe($data = [])
    {
        if (empty($data)) {
            Log::info('Subscribe: empty data');
            return;
        }

        $access_token = $data['access_token'];
        $mobile_number = '+63'. $data['subscriber_number'];

        Log::info($access_token);
        Log::info($mobile_number);

        // Get the userdata if exists
        $response = $this->user->getBy([
            'msisdn' => $mobile_number
            ]);

        if (empty($response)) {
            $args = [
                'msisdn' => $mobile_number,
                'accesstoken' => $access_token,
                'email' => uniqid().'@gmail.com',
                'active' => 1
                ];

            if (! $this->user->store($args)) {
                Log::info('Subscribe: cannot store User '. json_encode($data));
            }
        } else {
            $args = [
                'accesstoken' => $access_token,
                'active' => 1
                ];

            if (! $this->user->update($response->id, $args)) {
                Log::info('Subscribe: cannot store User '. json_encode($data));
            }
        }
    }

    /**
     * Unsubscribe User
     *
     * @param array data
     * @return void
     * @author
     **/
    public function _unsubscribe($data = [])
    {
        if (empty($data)) {
            Log::info('Unsubscribe: empty data');
            return;
        }

        $access_token = $data['access_token'];
        $mobile_number = '+63'. $data['subscriber_number'];

        Log::info($access_token);
        Log::info($mobile_number);

        // Get the userdata
        $response = $this->user->getBy([
            'msisdn' => $mobile_number
            ]);

        if (empty($response)) {
            Log::info('Unsubscribe: No User Found '. json_encode($data));
            return;
        }

        $args = [
            'accesstoken' => $access_token,
            'active' => 0
            ];

        if (! $this->user->update($response->id, $args)) {
            Log::info('Unsubscribe: cannot update User '. json_encode($data));
        }

    }

    /**
     *  Validate active user
     * if active, return true
     * if inactive / not exists return false
     *
     * @param post data (msisdn)
     * @return void
     * @author
     **/
    public function postValidate(Request $request)
    {
        $mobile_number = '+'. $request->input('mobile_number');

        $user = $this->user->getBy([
            'msisdn' => $mobile_number
            ]);

        if (empty($user)) {
            return response()->json(array('user_id' => 0));
        }

        if (! $user->active) {
            return response()->json(array('user_id' => 0));
        }

        return response()->json(array('user_id' => $user->id));
    }

}
