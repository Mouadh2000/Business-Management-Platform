<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStatusFromTaskAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down()
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
        });
    }
}
