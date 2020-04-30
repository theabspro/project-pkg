<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('columns')) {
			Schema::create('columns', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('table_id');
				$table->string('name', 32);
				$table->string('new_name', 32)->nullable();
				$table->unsignedInteger('data_type_id');
				$table->string('size', 12)->nullable();
				$table->unsignedInteger('fk_id')->nullable();
				$table->unsignedInteger('fk_type_id')->nullable();
				$table->string('uk')->nullable();
				$table->boolean('is_nullable');
				$table->string('default')->nullable();
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('data_type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

				$table->foreign('fk_id')->references('id')->on('tables')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('fk_type_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');

				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["table_id", "name"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('columns');
	}
}
