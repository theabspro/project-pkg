<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TasksC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('tasks')) {
			Schema::create('tasks', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('company_id');
				$table->string('number', 191);
				$table->unsignedInteger('assigned_to_id')->nullable();
				$table->unsignedInteger('tl_id')->nullable();
				$table->unsignedInteger('pm_id')->nullable();
				$table->date('date');
				$table->unsignedInteger('module_id')->nullable();
				$table->unsignedInteger('project_id')->nullable();
				$table->string('subject', 255);
				$table->text('description')->nullable();
				$table->unsignedInteger('type_id')->nullable();
				$table->unsignedDecimal('estimated_hours', 5, 2)->nullable()->default(0);
				$table->unsignedDecimal('actual_hours', 5, 2)->nullable()->default(0);
				$table->unsignedInteger('status_id')->nullable();
				$table->text('remarks')->nullable();
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');

				$table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('tl_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('pm_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('module_id')->references('id')->on('modules')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('project_id')->references('id')->on('projects')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('type_id')->references('id')->on('entities')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('status_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["company_id", "number"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('tasks');
	}
}
