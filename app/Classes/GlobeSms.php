<?php

namespace App\Classes;

use App\Classes\GlobeApi;

use Exception;

class GlobeSms extends GlobeApi {

    /**
     * Creates an sms
     *
     * @param string|null   $version        the api version to be used
     * @param string|null   $short_code        the shortcode
     */
    public function __construct(
        $short_code = '',
        $version = '') {

        $this->live_url = 'devapi.globelabs.com.ph';
        $this->send_url = 'https://%s/smsmessaging/%s/outbound/%s/requests?access_token=%s';

        $this->short_code = $short_code; // Access Code
        $this->version = $version; // Api Version
        $this->access_token = ''; // User Access Token
        $this->recepient = ''; // User Msisdn
        $this->message = ''; // Text Message
    }

    /**
     * Triggers the send or the message
     *
     * @param  string|null  $accesstoken        the access token of the user to be charged
     * @param  string|null  $msisdn             the number of the user to be charged
     * @param  string|null  $message            the message to be sent
     * @param  boolean $bodyOnly                returns the header if set to false
     * @return array
     */
    public function sendMessage($accesstoken = '', $msisdn = '', $message = '', $body_only = true) {

        try {
            if(! empty($accesstoken)) {
                $this->access_token = $accesstoken;
            }

            if(! empty($msisdn)) {
                $this->recepient = $msisdn;
            }

            if(! empty($message)) {
                $this->message = $message;
            }

            if(!$this->recepient) {
                throw new Exception('recepient is required');
            }

            if(!$this->message) {
                throw new Exception('message is required');
            }

            if(!$this->short_code) {
                throw new Exception('shortcode is required');
            }

            $url = sprintf($this->send_url,
                $this->live_url,
                $this->version,
                $this->short_code,
                $this->access_token
            );

            $postFields = array(
                'message' => urlencode($this->message),
                'address' => $this->recepient
            );

            $response = $this->_curlPost($url, $postFields);

            if (isset($response['error'])) {
                throw new Exception("Error Processing Request: ". $response['error']);
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $response;
    }
}
