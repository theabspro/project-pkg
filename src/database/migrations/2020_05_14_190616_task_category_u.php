<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskCategoryU extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->nullable()->after("severity_id");
            $table->foreign("category_id")->references("id")->on("configs")->onDelete("SET NULL")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign("tasks_category_id_foreign");
            $table->dropColumn("category_id");
        });
    }
}
