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
                            \Log::warning('No activities found for resource:', [
                                'resource_id' => $resource['RESOURCE_ID']
                            ]);
                            return false;
                        }

                        $hasSpecialty = collect($activities)
                            ->where('ACTIVITY_SPECIALTY_ID', $specialtyId)
                            ->isNotEmpty();

                        \Log::info('Activities for resource:', [
                            'resource_id' => $resource['RESOURCE_ID'],
                            'activities_count' => count($activities),
                            'specialty_id' => $specialtyId,
                            'has_specialty' => $hasSpecialty
                        ]);

                        return $hasSpecialty;

                    } catch (\Exception $e) {
                        \Log::error('Error getting activities for resource: ' . $resource['RESOURCE_ID'], [
                            'error' => $e->getMessage(),
                            'specialty_id' => $specialtyId
                        ]);
                        
                        // Si hay un error, intentamos una vez más
                        try {
                            sleep(1); // Esperamos 1 segundo antes de reintentar
                            $activities = $this->get('GetActivities', [
                                'RESOURCE_ID' => $resource['RESOURCE_ID'],
                                'INSURANCE_ID' => ''
                            ]);
                            
                            return collect($activities)
                                ->where('ACTIVITY_SPECIALTY_ID', $specialtyId)
                                ->isNotEmpty();
                        } catch (\Exception $e2) {
                            \Log::error('Second attempt failed for resource: ' . $resource['RESOURCE_ID'], [
                                'error' => $e2->getMessage()
                            ]);
                            return false;
                        }
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
                \Log::warning('No doctors found for specialty:', ['specialty_id' => $specialtyId]);
                cache()->forget('doctors_specialty_' . $specialtyId); // Eliminamos la cache si no hay resultados
            } else {
                \Log::info('Filtered doctors:', ['count' => count($filtered)]);
            }
            
            return $filtered;
        });
    }

    public function getVisitTypes($doctorId)
    {
        \Log::info('Getting visit types for doctor:', ['doctor_id' => $doctorId]);
        
        $activities = $this->get('GetActivities', [
            'RESOURCE_ID' => $doctorId,
            'INSURANCE_ID' => ''
        ]);
        
        \Log::info('Activities received:', ['activities' => $activities]);
        
        return $activities;  // Return the full response for now to see what we're getting
    }

    public function getAvailableSlots($doctorId, $activityId, $startDate)
    {
        return $this->get('SearchAvailabilities', [
            'AVA_START_DAY' => $startDate->format('d/m/Y'),
            'AVA_START_TIME' => '00:00',
            'AVA_END_DAY' => $startDate->addDays(30)->format('d/m/Y'),
            'RESOURCE_ID' => $doctorId,
            'ACTIVITY_ID' => $activityId,
            'LOCATION_ID' => '',
            'INSURANCE_ID' => ''
        ]);
    }

    public function createAppointment($data)
    {
        return $this->get('AddAppointment', $data);
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
} 