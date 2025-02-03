<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestAppointmentEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:test-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test appointment reminder email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Always create a new test appointment
        $appointment = Appointment::create([
            'external_id' => 'TEST-' . time(),
            'patient_email' => $email,
            'patient_name' => 'Test Patient',
            'appointment_date' => now()->addDays(3),
            'appointment_time' => '10:00:00',
            'location_id' => '1',
            'doctor_id' => 'TEST-DOC',
        ]);

        try {
            Mail::to($email)->send(new AppointmentReminder($appointment, 3));
            $this->info('Test email sent successfully!');
            
            // Clean up test data
            $appointment->delete();
        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
        }
    }
}
