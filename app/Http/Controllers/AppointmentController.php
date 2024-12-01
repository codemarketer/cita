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
            'date' => 'required|date',
            'time' => 'required',
            'doctor_id' => 'required',
            'activity_id' => 'required',
            'patient_name' => 'required',
            'patient_email' => 'required|email',
            'patient_phone' => 'required'
        ]);

        $appointment = $this->ofimedicService->createAppointment([
            'APP_DATE' => $validated['date'],
            'APP_START_TIME' => $validated['time'],
            'RESOURCE_ID' => $validated['doctor_id'],
            'ACTIVITY_ID' => $validated['activity_id'],
            'PATIENT_FIRST_NAME' => $validated['patient_name'],
            'PATIENT_EMAIL' => $validated['patient_email'],
            'PATIENT_MOBILE_PHONE' => $validated['patient_phone'],
            'APPOINTMENT_TYPE' => '1' // Presencial por defecto
        ]);

        return response()->json($appointment);
    }
} 