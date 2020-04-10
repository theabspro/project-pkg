<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PhasesU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('phases', function (Blueprint $table) {
			$table->foreign('branch_id')->references('id')->on('git_branches')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('phases', function (Blueprint $table) {
			$table->dropForeign('phases_branch_id_foreign');
		});
	}
}
