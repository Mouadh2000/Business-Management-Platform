<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectUserTable extends Migration
{
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('admins')->onDelete('cascade'); // is_staff = 1
            $table->string('role', 100)->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['project_id', 'user_id'], 'unique_project_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
}

