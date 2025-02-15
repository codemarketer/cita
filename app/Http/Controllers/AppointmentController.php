<?php

namespace App\Http\Controllers;

use App\Services\OfimedicService;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    protected $ofimedicService;

    public function __construct(OfimedicService $ofimedicService)
    {
        $this->ofimedicService = $ofimedicService;
    }

    public function index()
    {
        $specialties = $this->ofimedicService->getSpecialties();
        return view('appointments.index', compact('specialties'));
    }

    public function getDoctors(Request $request)
    {
        $doctors = $this->ofimedicService->getDoctorsBySpecialty($request->specialty_id);
        return response()->json($doctors);
    }

    public function getVisitTypes(Request $request)
    {
        $visitTypes = $this->ofimedicService->getVisitTypes(
            $request->doctor_id,
            $request->specialty_id
        );
        return response()->json($visitTypes);
    }

    public function getAvailableSlots(Request $request)
    {
        $slots = $this->ofimedicService->getAvailableSlots(
            $request->doctor_id,
            $request->activity_id,
            now()
        );
        
        \Log::info('Slots response:', ['slots' => $slots]);
        
        return response()->json($slots);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'APP_DATE' => 'required',
            'APP_START_TIME' => 'required',
            'RESOURCE_ID' => 'required',
            'ACTIVITY_ID' => 'required',
            'LOCATION_ID' => 'required',
            'PATIENT_ID_NUMBER' => 'required',
            'PATIENT_FIRST_NAME' => 'required',
            'PATIENT_SECOND_NAME' => 'required',
            'PATIENT_EMAIL' => 'required|email',
            'PATIENT_MOBILE_PHONE' => 'required'
        ]);

        try {
            \Log::info('Creating appointment with data:', ['data' => $validated]);
            
            $appointment = $this->ofimedicService->createAppointment($validated);
            
            \Log::info('Appointment creation response:', ['response' => $appointment]);

            if ($appointment[0]['RESULT'] === 'OK') {
                $newAppointment = Appointment::create([
                    'external_id' => $appointment[0]['APP_ID'],
                    'patient_email' => $validated['PATIENT_EMAIL'],
                    'patient_name' => $validated['PATIENT_FIRST_NAME'] . ' ' . $validated['PATIENT_SECOND_NAME'],
                    'appointment_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $validated['APP_DATE']),
                    'appointment_time' => $validated['APP_START_TIME'],
                    'location_id' => $validated['LOCATION_ID'],
                    'doctor_id' => $validated['RESOURCE_ID'],
                ]);

                try {
                    Mail::to($validated['PATIENT_EMAIL'])
                        ->send(new AppointmentConfirmation($newAppointment));
                } catch (\Exception $e) {
                    \Log::error('Error sending confirmation email:', [
                        'error' => $e->getMessage(),
                        'appointment_id' => $newAppointment->id
                    ]);
                    // Continuamos con el flujo aunque falle el envío del correo
                }

                return response()->json($appointment);
            }

            return response()->json($appointment);
        } catch (\Exception $e) {
            \Log::error('Error creating appointment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'RESULT' => 'ERROR',
                'ERROR_MESSAGE' => $e->getMessage()
            ], 500);
        }
    }

    public function checkPatient(Request $request)
    {
        try {
            $patient = $this->ofimedicService->getPatients([
                'PATIENT_ID' => '',
                'PATIENT_ID_NUMBER' => $request->dni
            ]);

            if (!empty($patient)) {
                return response()->json([
                    'exists' => true,
                    'patient' => $patient[0]
                ]);
            }

            return response()->json([
                'exists' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 