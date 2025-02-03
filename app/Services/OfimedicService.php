<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OfimedicService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = config('services.ofimedic.url');
        $this->username = config('services.ofimedic.username');
        $this->password = config('services.ofimedic.password');
    }

    protected function get(string $endpoint, array $params = [])
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->get($this->baseUrl . '/services.asmx/' . $endpoint, $params);
        
        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['Error'])) {
                throw new \Exception($data['Error']);
            }
            return $data;
        }

        throw new \Exception('Error connecting to Ofimedic API: ' . $response->body());
    }

    public function getSpecialties()
    {
        // Obtenemos las especialidades a partir de las actividades
        $activities = $this->get('GetActivities', [
            'RESOURCE_ID' => '',
            'INSURANCE_ID' => ''
        ]);
        
        return collect($activities)->unique('ACTIVITY_SPECIALTY_ID')
            ->map(function ($activity) {
                return [
                    'id' => $activity['ACTIVITY_SPECIALTY_ID'],
                    'name' => $activity['ACTIVITY_SPECIALTY_NAME']
                ];
            })->values();
    }

    public function getDoctorsBySpecialty($specialtyId)
    {
        return cache()->remember('doctors_specialty_' . $specialtyId, now()->addHours(48), function () use ($specialtyId) {
            $resources = $this->get('GetResources', [
                'LOCATION_ID' => ''
            ]);
            
            \Log::info('Resources received:', ['count' => count($resources)]);

            $filtered = collect($resources)
                ->filter(function ($resource) use ($specialtyId) {
                    try {
                        $activities = $this->get('GetActivities', [
                            'RESOURCE_ID' => $resource['RESOURCE_ID'],
                            'INSURANCE_ID' => ''
                        ]);
                        
                        if (empty($activities)) {
                            return false;
                        }

                        return collect($activities)
                            ->where('ACTIVITY_SPECIALTY_ID', $specialtyId)
                            ->isNotEmpty();

                    } catch (\Exception $e) {
                        \Log::error('Error getting activities for resource:', [
                            'resource_id' => $resource['RESOURCE_ID'],
                            'error' => $e->getMessage()
                        ]);
                        return false;
                    }
                })
                ->map(function ($resource) {
                    return [
                        'id' => $resource['RESOURCE_ID'],
                        'name' => $resource['RESOURCE_FIRST_NAME'] . ' ' . $resource['RESOURCE_SECOND_NAME']
                    ];
                })
                ->values();

            if ($filtered->isEmpty()) {
                cache()->forget('doctors_specialty_' . $specialtyId);
            }
            
            return $filtered;
        });
    }

    public function getVisitTypes($doctorId, $specialtyId)
    {
        \Log::info('Getting visit types for doctor:', ['doctor_id' => $doctorId, 'specialty_id' => $specialtyId]);
        
        $activities = $this->get('GetActivities', [
            'RESOURCE_ID' => $doctorId,
            'INSURANCE_ID' => ''
        ]);
        
        \Log::info('Activities received:', ['activities' => $activities]);
        
        // Lista de IDs a excluir
        $excludedIds = [
            30, 53, 67, 72, 9, 10, 12, 50, 54, 55, 24,
            5, 6, 49, 52, 71, 81, 85, 36, 87, 31, 51,
            82, 84, 86
        ];
        
        // Filtrar las actividades por especialidad y excluyendo los IDs especificados
        return collect($activities)
            ->filter(function ($activity) use ($specialtyId) {
                return $activity['ACTIVITY_SPECIALTY_ID'] == $specialtyId;
            })
            ->reject(function ($activity) use ($excludedIds) {
                return in_array($activity['ACTIVITY_ID'], $excludedIds);
            })
            ->values()
            ->all();
    }

    public function getAvailableSlots($doctorId, $activityId, $startDate)
    {
        $locations = ['3', '4']; // Campanar and Mestalla locations
        $allSlots = [];
        
        foreach ($locations as $locationId) {
            try {
                // Clonamos la fecha para cada iteración
                $currentDate = clone $startDate;
                $now = now();
                
                \Log::info('Requesting slots for location:', [
                    'location_id' => $locationId,
                    'doctor_id' => $doctorId,
                    'activity_id' => $activityId,
                    'start_date' => $currentDate->format('d/m/Y')
                ]);
                
                $slots = $this->get('SearchAvailabilities', [
                    'AVA_START_DAY' => $currentDate->format('d/m/Y'),
                    'AVA_START_TIME' => '00:00', // Keep it at 00:00 to get all slots
                    'AVA_END_DAY' => $currentDate->addDays(30)->format('d/m/Y'),
                    'RESOURCE_ID' => $doctorId,
                    'ACTIVITY_ID' => $activityId,
                    'LOCATION_ID' => $locationId,
                    'INSURANCE_ID' => '',
                    'AVA_RESULTS_NUMBER' => ''
                ]);
                
                // Filter out past dates (but allow any time for future dates)
                if (is_array($slots)) {
                    $slots = array_filter($slots, function($slot) use ($now) {
                        $slotDate = \Carbon\Carbon::createFromFormat('d/m/Y', $slot['AVA_DATE'])->startOfDay();
                        $today = $now->copy()->startOfDay();
                        
                        if ($slotDate->isAfter($today)) {
                            return true; // Future date, keep all times
                        } elseif ($slotDate->equalTo($today)) {
                            // For today, only filter times that have already passed
                            $slotTime = \Carbon\Carbon::createFromFormat('H:i', $slot['AVA_START_TIME']);
                            return $slotTime->isAfter($now);
                        }
                        return false; // Past date, filter out
                    });
                    
                    foreach ($slots as &$slot) {
                        $slot['LOCATION_ID'] = $locationId;
                    }
                    $allSlots = array_merge($allSlots, $slots);
                }
                
            } catch (\Exception $e) {
                \Log::error('Error getting slots for location:', [
                    'location_id' => $locationId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return array_values($allSlots); // Reset array keys after filtering
    }

    public function createAppointment($data)
    {
        // Aseguramos que todos los parámetros opcionales estén presentes
        $defaultParams = [
            'PATIENT_ID' => '',
            'APPOINTMENT_REASON' => '',
            'INSURANCE_ID' => '',
            'PATIENT_ID_NUMBER' => '',
            'OTHER_FIRST_NAME' => '',
            'OTHER_SECOND_NAME' => '',
            'OTHER_DATE_OF_BIRTH' => '',
            'OTHER_GENDER' => '',
            'APPOINTMENT_TYPE' => '1' //Presencial
        ];

        // Combinamos los parámetros proporcionados con los valores por defecto
        $appointmentData = array_merge($defaultParams, $data);

        return $this->get('AddAppointment', $appointmentData);
    }

    public function clearDoctorsCache($specialtyId = null)
    {
        if ($specialtyId) {
            cache()->forget('doctors_specialty_' . $specialtyId);
        } else {
            $specialties = $this->getSpecialties();
            foreach ($specialties as $specialty) {
                cache()->forget('doctors_specialty_' . $specialty['id']);
            }
        }
    }

    public function getPatients($params)
    {
        return $this->get('GetPatients', $params);
    }
} 