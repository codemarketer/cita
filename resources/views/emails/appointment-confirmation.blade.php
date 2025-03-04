@component('mail::message')
# Confirmación de su cita en Clínica NYR

Hola {{ $appointment->patient_name }},

Su cita ha sido confirmada correctamente:

**Fecha:** {{ $appointment->appointment_date->format('d/m/Y') }}  

**Hora:** {{ $appointment->appointment_time->format('H:i') }}  

**Especialidad:** {{ $appointment->specialty }}  

**Profesional:** {{ $appointment->doctor_name }}  

**Dirección:** @if($appointment->location_id === '3')C/ Avenida Maestro Rodrigo 16. 46015 Valencia ([Ver en mapa](https://maps.google.com/?q=Avenida+Maestro+Rodrigo+16+Valencia))  @else C/ Finlandia 15. 46010 Valencia ([Ver en mapa](https://maps.google.com/?q=Calle+Finlandia+15+Valencia))  
@endif

Si necesita cancelar o reprogramar su cita, por favor responda a este correo.

Saludos,  
Clínica NYR
@endcomponent 