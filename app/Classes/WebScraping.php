<?php

namespace App\Classes;

use Exception, Log;

class WebScraping {

    public function __construct($category_id = '')
    {
        $this->category_id = $category_id;
        //$this->live_url = 'http://webscraper:5000/api/0/%s'; // for local
        $this->live_url = 'http://headsup-app.cloudapp.net:5000/api/0/%s'; // for prod
    }

    /**
     * Perform Scraping
     * Determine which data to scrape
     *
     * @param string location
     * @return void
     */
    public function getData($location = '')
    {
        switch ($this->category_id) {
            case '1':
                // Weather - DOST
                $category = 'climatex';
                break;

            case '2':
                // Road Traffic MMDA
                $category = 'mmda';
                break;

            case '3':
            default:
                // MRT3 Service Status
                $category = 'mrt';
                $location = '';
                break;
        }

        $url = sprintf($this->live_url, $category);

        $response = $this->_curlJson($url, $location);

        return $response;
    }

    /**
     * Perform Curl
     * json type data
     *
     * @param string $url
     * @param string $location
     * @return void
     */
    public function _curlJson($url = '', $location = '')
    {
        if (empty($url)) {
            return false;
        }

        $data = json_encode([
            'location' => $location
        ]);

        $ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (! empty($location)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
            );
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);

        curl_close($ch);
        Log::info($response);
        return $response;
    }

}
