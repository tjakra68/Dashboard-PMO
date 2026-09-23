<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('account');
            $table->string('project');
            $table->string('pid')->nullable();
            $table->string('status_so')->nullable();
            $table->string('department')->nullable();
            $table->string('customer')->nullable();
            $table->string('name')->nullable();
            $table->decimal('so_value', 20, 2)->default(0);
            $table->decimal('collection', 20, 2)->default(0);
            $table->decimal('outstanding', 20, 2)->default(0);
            $table->decimal('target', 20, 2)->default(0);
            $table->decimal('remaining', 20, 2)->default(0);
            $table->timestamps();

            $table->index(['year', 'account']);
            $table->index(['year', 'project']);
        });

        Schema::create('master_project_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->decimal('forecast', 20, 2)->default(0);
            $table->decimal('target', 20, 2)->default(0);
            $table->decimal('actual', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['master_project_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_project_months');
        Schema::dropIfExists('master_projects');
    }
};
