<?php
/*
 * File name: AwardsTableSeeder.php
 * Last modified: 2021.03.01 at 21:40:37
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */
namespace Database\Seeders;

use DB;
use App\Models\Award;
use Illuminate\Database\Seeder;

class AwardsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('awards')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Award::factory()->count(50)->create();
    }
}
