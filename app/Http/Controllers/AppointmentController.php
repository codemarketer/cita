<?php

namespace App\Http\Controllers;

use App\Services\OfimedicService;
use Illuminate\Http\Request;

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
            'PATIENT_FIRST_NAME' => 'required',
            'PATIENT_SECOND_NAME' => 'required',
            'PATIENT_EMAIL' => 'required|email',
            'PATIENT_MOBILE_PHONE' => 'required'
        ]);

        try {
            $appointment = $this->ofimedicService->createAppointment([
                'APP_DATE' => $validated['APP_DATE'],
                'APP_START_TIME' => $validated['APP_START_TIME'],
                'RESOURCE_ID' => $validated['RESOURCE_ID'],
                'ACTIVITY_ID' => $validated['ACTIVITY_ID'],
                'LOCATION_ID' => $validated['LOCATION_ID'],
                'PATIENT_FIRST_NAME' => $validated['PATIENT_FIRST_NAME'],
                'PATIENT_SECOND_NAME' => $validated['PATIENT_SECOND_NAME'],
                'PATIENT_EMAIL' => $validated['PATIENT_EMAIL'],
                'PATIENT_MOBILE_PHONE' => $validated['PATIENT_MOBILE_PHONE'],
                'APPOINTMENT_TYPE' => '1'
            ]);

            \Log::info('Appointment creation response:', ['response' => $appointment]);
            return response()->json($appointment);
        } catch (\Exception $e) {
            \Log::error('Error creating appointment:', ['error' => $e->getMessage()]);
            return response()->json([
                'RESULT' => 'ERROR',
                'ERROR_MESSAGE' => $e->getMessage()
            ], 500);
        }
    }
} 