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
        Schema::create('partner_applications', function (Blueprint $table) {
            $table->id();
            $table->string('org_name', 160);
            $table->string('org_type', 80);
            $table->string('city', 80);
            $table->string('state', 80);
            $table->string('contact_person', 120);
            $table->string('designation', 120);
            $table->string('email', 190)->index();
            $table->string('phone', 30);
            $table->string('students_per_year', 30)->nullable();
            $table->json('interests')->nullable();
            $table->text('message')->nullable();
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
        Schema::dropIfExists('partner_applications');
    }
};
