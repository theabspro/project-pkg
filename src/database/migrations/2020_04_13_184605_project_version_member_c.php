<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProjectVersionMemberC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('project_version_member')) {
			Schema::create('project_version_member', function (Blueprint $table) {
				$table->unsignedInteger('project_version_id');
				$table->unsignedInteger('member_id');
				$table->unsignedInteger('type_id');
				$table->unsignedInteger('role_id');

				$table->foreign('project_version_id')->references('id')->on('project_versions')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('member_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('role_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["project_version_id", "member_id"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('project_version_member');
	}
}
