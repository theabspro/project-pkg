<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PhaseModuleC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('phase_module')) {
			Schema::create('phase_module', function (Blueprint $table) {
				$table->unsignedInteger('phase_id');
				$table->unsignedInteger('module_id');

				$table->foreign('phase_id')->references('id')->on('phases')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('module_id')->references('id')->on('modules')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["phase_id", "module_id"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('phase_module');
	}
}
