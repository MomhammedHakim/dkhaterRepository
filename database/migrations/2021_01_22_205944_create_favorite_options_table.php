<?php
/*
 * File name: 2021_01_22_205944_create_favorite_options_table.php
 * Last modified: 2021.01.22 at 22:00:01
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoriteOptionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::dropIfExists('favorite_options');
        Schema::create('favorite_options', function (Blueprint $table) {
            $table->integer('option_id')->unsigned();
            $table->integer('favorite_id')->unsigned();
            $table->primary(['option_id', 'favorite_id']);
            $table->foreign('option_id')->references('id')->on('options')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('favorite_id')->references('id')->on('favorites')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('favorite_options');
    }
}
