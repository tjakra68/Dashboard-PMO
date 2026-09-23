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
        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('account');
            $table->string('project');
            $table->decimal('so_value', 20, 2)->default(0);
            $table->decimal('collection', 20, 2)->default(0);
            $table->decimal('forecast', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['year', 'month', 'account', 'project']);
            $table->index(['year', 'account']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_entries');
    }
};
