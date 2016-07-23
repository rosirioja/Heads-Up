<?php

namespace App\Classes;

class Cron {

    /**
     * Recreate New Cron
     *
     * @param date new scheduled date
     * @return void
     */
    public function setNewCron($scheduled_date = '')
    {
        try {
            if (empty($scheduled_date)) {
                throw new Exception();
            }

            // Setup the script
            $date = parse_date($scheduled_date);

            $dir 	= '/www_home/headsup/artisan sms:send --time="'. $scheduled_date .'"';
            $cron 	= $date['minute'].' '.$date['hour'].' '.$date['day'].' '.$date['month'].' * '.$dir;

            $output = shell_exec('crontab -r');
			$output = shell_exec('crontab -l');
			file_put_contents(public_path('tmp/crontab.txt'), $output.$cron.PHP_EOL);

            exec('crontab '.public_path('tmp/crontab.txt'));

        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
