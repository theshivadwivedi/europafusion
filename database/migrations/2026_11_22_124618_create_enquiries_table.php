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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index()->comment('counselling | evaluation | contact');
            $table->string('full_name', 120);
            $table->string('email', 190)->index();
            $table->string('phone', 30);
            $table->string('qualification', 60)->nullable();
            $table->string('gpa', 40)->nullable();
            $table->string('preferred_course', 120)->nullable();
            $table->string('preferred_country', 60)->nullable();
            $table->string('preferred_intake', 40)->nullable();
            $table->text('message')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('status', 20)->default('new')->index()->comment('new | contacted | in_progress | closed');
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
