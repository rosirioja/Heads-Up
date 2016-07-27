<?php

namespace App\Classes;

use App\Classes\GlobeAuth;
use App\Classes\GlobeSms;

use Log;

class GlobeApi {

    /**
     * Constructor of the Wrapper Class
     *
     * @param string|null $version the version to be used
     */
    public function __construct($version = 'v1') {

        // Base URL
        $this->auth_url = 'developer.globelabs.com.ph';

        $this->short_code = ''; // Access Code
        $this->version = $version; //Api Version
    }

    /**
     * Used to instantiate an sms object to be sent
     *
     * @param  string|number $short_code
     * @param  string|null $version included for extensibility
     * @return [type]
     */
    public function sms($short_code = '', $version = '')
    {
        // check if the user passed a version parameter and use if any
        $version = empty($version) ? $this->version : $version;

        // use the set shortcode if any
        if(! empty($short_code)) {
            $this->short_code = $short_code;
        }

        $sms = new GlobeSms($this->short_code, $this->version);
        return $sms;
    }

    /**
     * Connecting to Globe Labs API
     * Requesting to perform sms send
     *
     * @param  string url
     * @param  array post data
     * @return [type]
     */
    protected function _curlPost($url, $fields = array()) {

        Log::info('Curl Post '. $url);
        Log::info($fields);

        $fields = "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"message\"\r\n\r\n". $fields['message'] ."\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"address\"\r\n\r\n". $fields['address'] ."\r\n-----011000010111000001101001--";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            "content-type: multipart/form-data; boundary=---011000010111000001101001"
        ));

        $response = curl_exec($ch);

        curl_close($ch);

        $response = json_decode($response, true);

        return $response;
    }
}
