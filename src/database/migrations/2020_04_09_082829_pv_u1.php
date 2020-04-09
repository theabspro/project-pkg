<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PvU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('project_versions', function (Blueprint $table) {
			$table->dropForeign('project_versions_company_id_foreign');
			$table->dropUnique('project_versions_company_id_number_unique');
			$table->unique(["company_id", "project_id", "number"]);
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('project_versions', function (Blueprint $table) {
			$table->dropUnique('project_versions_company_id_project_id_number_unique');
			$table->unique(["company_id", "number"]);
		});
		//
	}
}
