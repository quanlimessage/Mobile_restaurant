<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('foods', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name_jp');
			$table->string('name_en');
			$table->string('name_vi');
			$table->integer('cost_price');
			$table->integer('sale_price');
			$table->string('time_to_prepare');
			$table->integer('category_id');
			$table->string('image_url');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		//
	}
}
