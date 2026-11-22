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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('group', 20)->index()->comment('academic | beyond');
            $table->string('title');
            $table->string('tagline');
            $table->string('summary')->nullable()->comment('Short line used in menus');
            $table->json('body')->comment('Array of paragraphs');
            $table->string('quote')->nullable();
            $table->string('icon', 60)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
