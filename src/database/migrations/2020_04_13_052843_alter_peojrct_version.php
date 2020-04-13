<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPeojrctVersion extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('project_versions', function (Blueprint $table) {
			$table->unsignedMediumInteger('display_order')->default(999)->nullable()->after('estimated_end_date');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('project_versions', function (Blueprint $table) {
			$table->dropColumn('display_order');
		});
	}
}
