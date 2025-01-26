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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('external_id');  // Ofimedic's appointment ID
            $table->string('patient_email');
            $table->string('patient_name');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('location_id');
            $table->string('doctor_id');
            $table->timestamp('reminder_3days_sent_at')->nullable();
            $table->timestamp('reminder_24h_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
