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
        $excludedIds = [30, 53, 67, 72, 9, 10, 12, 50, 5, 6, 49, 52, 81, 87, 51, 86, 54, 51];
        
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