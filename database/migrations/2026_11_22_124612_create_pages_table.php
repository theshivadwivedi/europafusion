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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('URL path without leading slash');
            $table->string('kind', 20)->index()->comment('country | pathway | legal');
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('nav_label')->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->json('sections')->nullable()->comment('Content blocks, see docs/CONTENT_SCHEMA.md');
            $table->longText('body')->nullable()->comment('HTML body for legal pages');
            $table->date('last_updated')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
