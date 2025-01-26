@component('mail::message')
# Recordatorio de Cita Médica

Hola {{ $appointment->patient_name }},

Le recordamos que tiene una cita programada en {{ $appointment->location_id === '3' ? 'Clínica NYR Campanar' : 'Clínica NYR Mestalla' }}:

**Fecha:** {{ $appointment->appointment_date->format('d/m/Y') }}  
**Hora:** {{ $appointment->appointment_time->format('H:i') }}

Si necesita cancelar o reprogramar su cita, por favor contáctenos.

Saludos,  
{{ config('app.name') }}
@endcomponent