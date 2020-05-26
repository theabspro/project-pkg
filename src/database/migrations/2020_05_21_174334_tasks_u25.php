<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TasksU25 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::table('tasks', function (Blueprint $table) {

			$table->unsignedInteger('billing_status_id')->nullable()->after("remarks");

			$table->foreign("billing_status_id")->references("id")->on("billing_statuses")->onDelete("SET NULL")->onUpdate("SET NULL");

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('tasks', function (Blueprint $table) {

			$table->dropForeign("tasks_billing_status_id_foreign");

			$table->dropColumn("billing_status_id");

		});
	}
}
