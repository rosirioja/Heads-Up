<?php

use Carbon\Carbon;


if (! function_exists('json_to_string')) {
    /**
    * Convert json to string
    *
    * @access public
    * @param json
    * @return string
    */
    function json_to_string($json = [])
    {
        if (empty($json)) return '';

        $string = '';
        foreach ($json as $i => $row) {
            foreach ($row as $key => $value) {
                $string .= $value.'<br/>';
            }
        }

        return $string;
    }
}

if (! function_exists('parse_date')) {
    /**
    * Parse Date
    * For Cron job purposes
    *
    * @access public
    * @param json
    * @return string
    */
    function parse_date($date = '')
    {
        $date 	=  Carbon::parse($date);
        $date->timezone = 'UTC';

		$minute = $date->minute;
		$hour	= $date->hour;
		$day 	= $date->day;
		$month	= $date->month;

		return array(
			'minute' => $minute,
			'hour'   => $hour,
			'day'    => $day,
			'month'  => $month
			);
    }
}
