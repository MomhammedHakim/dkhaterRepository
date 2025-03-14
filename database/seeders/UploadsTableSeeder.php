<?php
/*
 * File name: UploadsTableSeeder.php
 * Last modified: 2021.03.01 at 21:37:55
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */
namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class UploadsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('uploads')->delete();
    }
}
