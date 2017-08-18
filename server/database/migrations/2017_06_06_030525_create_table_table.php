<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mac_address');
            $table->string('name_jp');
            $table->string('name_en');
            $table->string('name_vi');
            $table->string('description_jp');
            $table->string('description_en');
            $table->string('description_vi');
            $table->char('table_status');
            $table->integer('num_of_seat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
