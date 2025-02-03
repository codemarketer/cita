<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Mail\AppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send appointment reminders to patients';

    public function handle()
    {
        // Get appointments for tomorrow that haven't received 24h reminder
        $tomorrowAppointments = Appointment::whereDate('appointment_date', now()->addDay())
            ->whereNull('reminder_24h_sent_at')
            ->get();

        // Get appointments in 3 days that haven't received 3-day reminder
        $threeDaysAppointments = Appointment::whereDate('appointment_date', now()->addDays(3))
            ->whereNull('reminder_3days_sent_at')
            ->get();

        foreach ($tomorrowAppointments as $appointment) {
            Mail::to($appointment->patient_email)
                ->send(new AppointmentReminder($appointment, 1));
            
            $appointment->update(['reminder_24h_sent_at' => now()]);
        }

        foreach ($threeDaysAppointments as $appointment) {
            Mail::to($appointment->patient_email)
                ->send(new AppointmentReminder($appointment, 3));
            
            $appointment->update(['reminder_3days_sent_at' => now()]);
        }

        $this->info("Sent {$tomorrowAppointments->count()} 24h reminders and {$threeDaysAppointments->count()} 3-day reminders");
    }
}