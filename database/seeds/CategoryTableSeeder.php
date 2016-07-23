<?php

use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            'name' => 'weather-dost',
            'display_name' => 'Weather - DOST'
        ]);

        DB::table('categories')->insert([
            'name' => 'road-traffic-mmda',
            'display_name' => 'Road Traffic - MMDA'
        ]);

        DB::table('categories')->insert([
            'name' => 'mrt3-dotc',
            'display_name' => 'MRT Line 3 - DOTC'
        ]);
    }
}
