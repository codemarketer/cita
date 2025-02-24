<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'external_id',
        'patient_email',
        'patient_name',
        'appointment_date',
        'appointment_time',
        'location_id',
        'doctor_id',
        'reminder_3days_sent_at',
        'reminder_24h_sent_at',
        'doctor_name',
        'specialty',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
        'reminder_3days_sent_at' => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
    ];
}
