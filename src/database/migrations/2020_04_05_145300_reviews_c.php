<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviewsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('reviews')) {
			Schema::create('reviews', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('phase_id');
				$table->string('number', 191);
				$table->unsignedInteger('tl_status_id')->nullable();
				$table->unsignedInteger('tester_status_id')->nullable();
				$table->unsignedInteger('team_meeting_status_id')->nullable();
				$table->datetime('estimated_date_of_completion')->nullable();
				$table->datetime('requested_date')->nullable();
				$table->datetime('started_date')->nullable();
				$table->datetime('completed_date')->nullable();
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('phase_id')->references('id')->on('phases')->onDelete('CASCADE')->onUpdate('cascade');

				$table->foreign('tl_status_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('tester_status_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('team_meeting_status_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');

				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

				$table->unique(["phase_id", "number"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('reviews');
	}
}
