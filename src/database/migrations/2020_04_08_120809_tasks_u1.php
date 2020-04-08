<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TasksU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('tasks', function (Blueprint $table) {
			$table->dropForeign('tasks_type_id_foreign');
			$table->dropForeign('tasks_status_id_foreign');

			$table->foreign('type_id')->references('id')->on('task_types')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('status_id')->references('id')->on('statuses')->onDelete('SET NULL')->onUpdate('cascade');
		});

		Schema::table('project_versions', function (Blueprint $table) {
			$table->dropForeign('project_versions_status_id_foreign');
			$table->foreign('status_id')->references('id')->on('statuses')->onDelete('SET NULL')->onUpdate('cascade');
		});

		Schema::table('reviews', function (Blueprint $table) {
			$table->dropForeign('reviews_tl_status_id_foreign');
			$table->dropForeign('reviews_tester_status_id_foreign');
			$table->dropForeign('reviews_team_meeting_status_id_foreign');

			$table->foreign('tl_status_id')->references('id')->on('statuses')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('tester_status_id')->references('id')->on('statuses')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('team_meeting_status_id')->references('id')->on('statuses')->onDelete('cascade')->onUpdate('cascade');
		});

		Schema::table('phases', function (Blueprint $table) {
			$table->dropForeign('phases_status_id_foreign');
			$table->foreign('status_id')->references('id')->on('statuses')->onDelete('SET NULL')->onUpdate('cascade');
		});

		Schema::table('modules', function (Blueprint $table) {
			$table->dropForeign('modules_status_id_foreign');
			$table->foreign('status_id')->references('id')->on('statuses')->onDelete('SET NULL')->onUpdate('cascade');
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
