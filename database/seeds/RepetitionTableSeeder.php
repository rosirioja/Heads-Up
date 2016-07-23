<?php

use Illuminate\Database\Seeder;

class RepetitionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('repetitions')->insert([
            'name' => 'one-time-schedule',
            'display_name' => 'One Time Schedule'
        ]);

        DB::table('repetitions')->insert([
            'name' => 'daily',
            'display_name' => 'Daily'
        ]);

        DB::table('repetitions')->insert([
            'name' => 'every-weekday',
            'display_name' => 'Every Weekday (Mon-Fri)'
        ]);

        DB::table('repetitions')->insert([
            'name' => 'weekly',
            'display_name' => 'Weekly (every -)'
        ]);
    }
}
