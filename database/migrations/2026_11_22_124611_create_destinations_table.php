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
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->char('code', 2)->comment('ISO 3166-1 alpha-2, used for the flag');
            $table->boolean('is_primary')->default(false);
            $table->string('headline');
            $table->string('tagline', 500);
            $table->text('overview');
            $table->string('tuition_range');
            $table->string('living_cost');
            $table->string('work_rights');
            $table->json('popular_study_areas');
            $table->json('highlights');
            $table->json('top_universities');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
