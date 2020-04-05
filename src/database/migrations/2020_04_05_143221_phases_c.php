<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PhasesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('credentials')) {
			Schema::create('credentials', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('project_id');
				$table->string('name', 191);
				$table->string('description', 255)->nullable();
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["project_id", "name"]);
			});
		}

		if (!Schema::hasTable('credential_details')) {
			Schema::create('credential_details', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('credential_id');
				$table->string('key', 191);
				$table->string('value', 255);

				$table->foreign('credential_id')->references('id')->on('credentials')->onDelete('cascade')->onUpdate('cascade');

				$table->unique(["credential_id", "key"]);
			});
		}

		if (!Schema::hasTable('git_branches')) {
			Schema::create('git_branches', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('project_id');
				$table->string('name', 191);
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["project_id", "name"]);
			});
		}

		if (!Schema::hasTable('phases')) {
			Schema::create('phases', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('project_id');
				$table->string('number', 191);
				$table->unsignedInteger('branch_id')->nullable();
				$table->unsignedInteger('credential_id')->nullable();
				$table->unsignedInteger('status_id')->nullable();
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE')->onUpdate('cascade');
				$table->foreign('credential_id')->references('id')->on('credentials')->onDelete('SET NULL')->onUpdate('cascade');
				// $table->foreign('branch_id')->references('id')->on('git_branches')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('status_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["project_id", "number"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('phases');
		Schema::dropIfExists('git_branches');
		Schema::dropIfExists('credential_details');
		Schema::dropIfExists('credentials');

	}
}
