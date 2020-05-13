<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TasksU918 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::table('tasks', function (Blueprint $table) {

			$table->text('steps_to_reproduce')->nullable()->after("description");
			$table->text('expected_result')->nullable()->after("steps_to_reproduce");
			$table->unsignedInteger('severity_id')->nullable()->after("expected_result");
			$table->unsignedMediumInteger('display_order')->nullable()->after("severity_id")->default(999);

			$table->foreign("severity_id")->references("id")->on("severities")->onDelete("SET NULL")->onUpdate("SET NULL");

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('tasks', function (Blueprint $table) {

			$table->dropForeign("tasks_severity_id_foreign");

			$table->dropColumn("steps_to_reproduce");
			$table->dropColumn("expected_result");
			$table->dropColumn("severity_id");
			$table->dropColumn("display_order");

		});
	}
}
