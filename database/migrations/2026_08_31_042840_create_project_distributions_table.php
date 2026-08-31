<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('total_so', 20, 2)->default(0);
            $table->decimal('taxation', 20, 2)->default(0);
            $table->decimal('collection', 20, 2)->default(0);
            $table->decimal('july_target', 20, 2)->default(0);
            $table->decimal('july_actual', 20, 2)->default(0);
            $table->decimal('forecast_aug', 20, 2)->default(0);
            $table->decimal('forecast_sep', 20, 2)->default(0);
            $table->decimal('forecast_oct', 20, 2)->default(0);
            $table->decimal('forecast_nov', 20, 2)->default(0);
            $table->decimal('forecast_dec', 20, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_distributions');
    }
};
