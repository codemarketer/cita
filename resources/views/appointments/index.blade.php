<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cita Online - Clínica NYR</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen" x-data="appointmentForm()">
        <div class="max-w-3xl mx-auto py-12 px-4">
            <h1 class="text-2xl font-normal text-gray-900 mb-8 text-center">Solicitar Cita</h1>
            
            <div class="space-y-8">
                <!-- Paso 1: Selección de especialidad -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">1. Seleccione la especialidad</h2>
                    <select 
                        x-model="specialty"
                        @change="loadDoctors()"
                        class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seleccione una especialidad</option>
                        @foreach($specialties as $specialty)
                            @if($specialty['id'])
                                <option value="{{ $specialty['id'] }}">{{ $specialty['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Paso 2: Selección de doctor -->
                <div x-show="specialty" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">2. Seleccione el profesional</h2>
                    <div x-show="loadingDoctors" class="flex items-center justify-center py-4">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2">Cargando profesionales...</span>
                    </div>
                    <select 
                        x-show="!loadingDoctors"
                        x-model="doctor"
                        @change="loadVisitTypes()"
                        class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seleccione un profesional</option>
                        <template x-for="doc in doctors">
                            <option :value="doc.id" x-text="doc.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Paso 3: Tipo de visita -->
                <div x-show="doctor" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">3. Seleccione el tipo de visita</h2>
                    <select 
                        x-model="visitType"
                        @change="loadSlots()"
                        class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seleccione el tipo de visita</option>
                        <template x-for="type in visitTypes">
                            <option :value="type.ACTIVITY_ID" x-text="type.ACTIVITY_NAME"></option>
                        </template>
                    </select>
                </div>

                <!-- Paso 4: Selección de centro y horario -->
                <div x-show="visitType" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">4. Seleccione centro y horario</h2>
                    
                    <!-- Loading state -->
                    <div x-show="loadingSlots" class="flex items-center justify-center py-4">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2">Buscando citas disponibles...</span>
                    </div>

                    <!-- Slots display -->
                    <div x-show="!loadingSlots">
                        <template x-if="availableSlots && availableSlots.length > 0">
                            <div class="space-y-6">
                                <!-- Location selection -->
                                <div class="space-y-4">
                                    <!-- Clínica NYR Campanar -->
                                    <div class="p-4 bg-white rounded-lg border transition-all hover:border-blue-200"
                                         :class="{'border-blue-500 ring-2 ring-blue-200': selectedLocation === '3', 'border-gray-200': selectedLocation !== '3'}">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-medium">Clínica NYR Campanar</h3>
                                        </div>
                                        
                                        <template x-if="getFirstSlotForLocation('3')">
                                            <div>
                                                <p class="text-sm text-gray-600 mb-1">Primera cita disponible:</p>
                                                <div class="flex items-center justify-between">
                                                    <div class="text-base">
                                                        <span x-text="formatDate(getFirstSlotForLocation('3').AVA_DATE)" class="font-medium"></span>
                                                        <span x-text="formatTime(getFirstSlotForLocation('3').AVA_START_TIME)" class="ml-1 text-gray-600"></span>
                                                    </div>
                                                    <button @click="selectedLocation = '3'" 
                                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                        Seleccionar este centro
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="!getFirstSlotForLocation('3')">
                                            <p class="text-gray-500 text-sm">No hay citas disponibles</p>
                                        </template>
                                    </div>

                                    <!-- Clínica NYR Mestalla -->
                                    <div class="p-4 bg-white rounded-lg border transition-all hover:border-blue-200"
                                         :class="{'border-blue-500 ring-2 ring-blue-200': selectedLocation === '4', 'border-gray-200': selectedLocation !== '4'}">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-medium">Clínica NYR Mestalla</h3>
                                        </div>
                                        
                                        <template x-if="getFirstSlotForLocation('4')">
                                            <div>
                                                <p class="text-sm text-gray-600 mb-1">Primera cita disponible:</p>
                                                <div class="flex items-center justify-between">
                                                    <div class="text-base">
                                                        <span x-text="formatDate(getFirstSlotForLocation('4').AVA_DATE)" class="font-medium"></span>
                                                        <span x-text="formatTime(getFirstSlotForLocation('4').AVA_START_TIME)" class="ml-1 text-gray-600"></span>
                                                    </div>
                                                    <button @click="selectedLocation = '4'" 
                                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                        Seleccionar este centro
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="!getFirstSlotForLocation('4')">
                                            <p class="text-gray-500 text-sm">No hay citas disponibles</p>
                                        </template>
                                    </div>
                                </div>

                                <!-- Calendar view (only shown after location selection) -->
                                <template x-if="selectedLocation">
                                    <div class="mt-6">
                                        <!-- Calendar navigation -->
                                        <div class="flex items-center justify-between mb-4">
                                            <button @click="previousMonth" class="text-gray-600 hover:text-gray-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                </svg>
                                            </button>
                                            <h3 class="text-lg font-medium" x-text="formatMonthYear(currentMonth)"></h3>
                                            <button @click="nextMonth" class="text-gray-600 hover:text-gray-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Calendar grid -->
                                        <div class="grid grid-cols-7 gap-1">
                                            <!-- Days of week headers -->
                                            <template x-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']">
                                                <div class="text-center text-sm font-medium text-gray-600 py-2" x-text="day"></div>
                                            </template>

                                            <div class="col-span-7 flex flex-col">
                                                <!-- Calendar days -->
                                                <template x-for="week in calendarWeeks">
                                                    <div class="grid grid-cols-7 gap-1">
                                                        <template x-for="day in week">
                                                            <div 
                                                                class="aspect-square p-1 relative"
                                                                :class="{
                                                                    'opacity-50': !day.date || !day.slots.length,
                                                                    'cursor-pointer hover:bg-gray-50': day.slots.length
                                                                }"
                                                            >
                                                                <template x-if="day.date">
                                                                    <div
                                                                        @click="day.slots.length && selectDay(day)"
                                                                        class="w-full h-full flex flex-col items-center justify-center rounded-lg"
                                                                        :class="{
                                                                            'bg-blue-50 ring-2 ring-blue-200': selectedDay && day.date.getTime() === selectedDay.date.getTime(),
                                                                            'hover:border hover:border-blue-200': day.slots.length
                                                                        }"
                                                                    >
                                                                        <span class="text-sm" x-text="day.date.getDate()"></span>
                                                                        <template x-if="day.slots.length">
                                                                            <div class="flex gap-1 mt-1">
                                                                                <template x-if="getSlotPeriods(day.slots).hasMorning">
                                                                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                                                                                </template>
                                                                                <template x-if="getSlotPeriods(day.slots).hasAfternoon">
                                                                                    <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                                                                                </template>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Legend -->
                                        <div class="flex items-center justify-center gap-6 mt-4 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-2"></div>
                                                <span>Mañanas</span>
                                            </div>
                                            <div class="flex items-center">
                                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 mr-2"></div>
                                                <span>Tardes</span>
                                            </div>
                                        </div>

                                        <!-- Available hours for selected day -->
                                        <template x-if="selectedDay">
                                            <div id="available-hours" class="mt-6">
                                                <h4 class="text-lg font-medium mb-4">Horarios disponibles para <span x-text="formatDate(selectedDay.date)"></span></h4>
                                                
                                                <div class="grid grid-cols-3 gap-2">
                                                    <template x-for="slot in selectedDay.slots">
                                                        <button
                                                            @click="selectSlot(slot)"
                                                            class="p-2 text-center rounded-lg border transition-colors"
                                                            :class="{
                                                                'bg-blue-50 border-blue-500 text-blue-700': selectedSlot === slot,
                                                                'border-gray-200 hover:border-blue-200': selectedSlot !== slot
                                                            }"
                                                        >
                                                            <span x-text="formatTime(slot.AVA_START_TIME)"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Paso 5: Verificación DNI -->
                <div id="dni-verification" x-show="selectedSlot !== null && !dniVerified" class="bg-white p-6 rounded-lg shadow mb-4">
                    <h2 class="text-xl font-semibold mb-4">5. Verificación de paciente</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">DNI/NIE</label>
                            <input 
                                type="text" 
                                x-model="form.dni" 
                                @keyup.enter="checkPatient()"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                                required>
                        </div>
                        <button 
                            @click="checkPatient()" 
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                            Verificar DNI
                        </button>
                        <div x-show="patientChecking" class="text-sm text-gray-500">
                            Verificando DNI...
                        </div>
                        <div x-show="patientError" class="text-sm text-red-500" x-text="patientError">
                        </div>
                    </div>
                </div>

                <!-- Paso 6: Datos del paciente -->
                <div id="patient-data" x-show="dniVerified === true && existingPatient === null" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">6. Datos del paciente</h2>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" x-model="form.patient_first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input type="text" x-model="form.patient_second_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" x-model="form.patient_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input type="tel" x-model="form.patient_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                            Confirmar cita
                        </button>
                    </form>
                </div>

                <!-- Paso 6: Confirmación para paciente existente -->
                <div id="patient-confirmation" x-show="dniVerified === true && existingPatient !== null" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">6. Confirmar cita</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="font-medium">Paciente: <span x-text="form.patient_first_name + ' ' + form.patient_second_name"></span></p>
                            <p class="text-gray-600">Email: <span x-text="form.patient_email"></span></p>
                            <p class="text-gray-600">Teléfono: <span x-text="form.patient_phone"></span></p>
                        </div>
                        <button @click="submitForm" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                            Confirmar cita
                        </button>
                    </div>
                </div>
            </div>

            <!-- Move the logo to the bottom -->
            <div class="flex justify-center mt-12">
                <img src="{{ asset('images/nuevologo.svg') }}" alt="Clínica NYR Logo" class="w-full max-w-xs opacity-40">
            </div>
        </div>
    </div>

    <script>
        function appointmentForm() {
            return {
                init() {
                    this.$watch('selectedLocation', (value) => {
                        if (value) {
                            this.selectedDay = null;
                            this.selectedSlot = null;

                            // Encuentra el primer slot disponible para la ubicación seleccionada
                            const locationSlots = this.availableSlots.filter(slot => 
                                slot.LOCATION_ID === value
                            );
                            
                            if (locationSlots.length > 0) {
                                const firstSlotDate = locationSlots[0].AVA_DATE;
                                const [day, month, year] = firstSlotDate.split('/');
                                this.currentMonth = new Date(year, parseInt(month) - 1, day);
                            }
                            
                            this.initializeCalendar();
                        }
                    });
                },
                specialty: '',
                doctor: '',
                visitType: '',
                doctors: [],
                visitTypes: [],
                availableSlots: [],
                selectedSlot: null,
                loadingDoctors: false,
                loadingVisitTypes: false,
                form: {
                    patient_first_name: '',
                    patient_second_name: '',
                    patient_email: '',
                    patient_phone: ''
                },
                calendarWeeks: [],
                currentMonth: new Date(),
                selectedDay: null,
                selectedLocation: null,
                showMoreSlots: false,
                patientChecking: false,
                patientError: null,
                existingPatient: null,
                dniVerified: false,
                
                async loadDoctors() {
                    this.loadingDoctors = true;
                    this.doctors = [];
                    this.doctor = '';
                    this.visitType = '';
                    this.selectedSlot = null;
                    
                    try {
                        const response = await fetch(`/appointments/doctors?specialty_id=${this.specialty}`);
                        this.doctors = await response.json();
                    } catch (error) {
                        console.error('Error loading doctors:', error);
                    } finally {
                        this.loadingDoctors = false;
                    }
                },

                async loadVisitTypes() {
                    this.loadingVisitTypes = true;
                    this.visitTypes = [];
                    this.visitType = '';
                    this.selectedSlot = null;
                    
                    try {
                        const response = await fetch(`/appointments/visit-types?doctor_id=${this.doctor}&specialty_id=${this.specialty}`);
                        const data = await response.json();
                        this.visitTypes = data;
                    } catch (error) {
                        console.error('Error loading visit types:', error);
                    } finally {
                        this.loadingVisitTypes = false;
                    }
                },

                async loadSlots() {
                    this.loadingSlots = true;
                    this.availableSlots = [];
                    this.selectedSlot = null;
                    this.selectedDay = null;
                    this.showMoreSlots = false;
                    
                    try {
                        const response = await fetch(`/appointments/slots?doctor_id=${this.doctor}&activity_id=${this.visitType}`);
                        const data = await response.json();
                        this.availableSlots = data;
                        
                        // Inicializar el calendario en el mes del primer slot disponible
                        if (this.availableSlots && this.availableSlots.length > 0) {
                            // Encuentra el primer slot disponible
                            const firstSlotDate = this.availableSlots[0].AVA_DATE;
                            const [day, month, year] = firstSlotDate.split('/');
                            this.currentMonth = new Date(year, parseInt(month) - 1, day);
                        } else {
                            // Si no hay slots, mostrar el mes actual
                            this.currentMonth = new Date();
                        }
                        
                        this.initializeCalendar();
                        console.log('Current month set to:', this.currentMonth);
                    } catch (error) {
                        console.error('Error loading slots:', error);
                    } finally {
                        this.loadingSlots = false;
                    }
                },

                selectSlot(slot) {
                    if (this.selectedSlot === slot) {
                        this.selectedSlot = null;
                    } else {
                        this.selectedSlot = slot;
                        this.$nextTick(() => document.querySelector('#dni-verification').scrollIntoView({behavior: 'smooth'}));
                    }
                },

                async checkPatient() {
                    if (!this.form.dni) {
                        this.patientError = 'Por favor, introduce un DNI/NIE';
                        return;
                    }
                    
                    console.log('Starting checkPatient with DNI:', this.form.dni);
                    this.patientChecking = true;
                    this.patientError = null;
                    this.existingPatient = null;
                    this.dniVerified = false;

                    try {
                        const response = await fetch(`/appointments/check-patient?dni=${this.form.dni}`);
                        const data = await response.json();
                        
                        console.log('Server response:', data);

                        if (data.exists) {
                            console.log('Patient found in system');
                            this.existingPatient = data.patient;
                            console.log('existingPatient after assignment:', this.existingPatient);
                            
                            this.form.patient_first_name = data.patient.PATIENT_FIRST_NAME;
                            this.form.patient_second_name = data.patient.PATIENT_SECOND_NAME;
                            this.form.patient_email = data.patient.PATIENT_EMAIL;
                            this.form.patient_phone = data.patient.PATIENT_MOBILE_PHONE;
                            this.form.patient_id = data.patient.PATIENT_ID;
                        } else {
                            console.log('Patient not found in system');
                            this.existingPatient = null;
                            this.form.patient_first_name = '';
                            this.form.patient_second_name = '';
                            this.form.patient_email = '';
                            this.form.patient_phone = '';
                            this.form.patient_id = '';
                        }
                        
                        this.dniVerified = true;
                        console.log('Final state:', {
                            existingPatient: this.existingPatient,
                            dniVerified: this.dniVerified
                        });
                    } catch (error) {
                        console.error('Error in checkPatient:', error);
                        this.patientError = 'Error al verificar el DNI';
                    } finally {
                        this.patientChecking = false;
                    }
                },

                async submitForm() {
                    const formData = {
                        APP_DATE: this.selectedSlot.AVA_DATE,
                        APP_START_TIME: this.selectedSlot.AVA_START_TIME,
                        RESOURCE_ID: this.doctor,
                        ACTIVITY_ID: this.visitType,
                        LOCATION_ID: this.selectedSlot.LOCATION_ID,
                        PATIENT_ID: this.form.patient_id || '',
                        PATIENT_ID_NUMBER: this.form.dni,
                        PATIENT_FIRST_NAME: this.form.patient_first_name,
                        PATIENT_SECOND_NAME: this.form.patient_second_name,
                        PATIENT_EMAIL: this.form.patient_email,
                        PATIENT_MOBILE_PHONE: this.form.patient_phone,
                        APPOINTMENT_TYPE: '1'
                    };

                    console.log('Sending appointment data:', formData);

                    const response = await fetch('/appointments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    console.log('API Response:', result);

                    if (result[0] && result[0].RESULT === 'OK') {
                        alert('Cita confirmada correctamente');
                        window.location.reload();
                    } else {
                        alert('No se ha podido confirmar la cita. Por favor, contacte con el centro para más información.');
                    }
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const [day, month, year] = dateStr.split('/');
                    const date = new Date(year, month - 1, day);
                    return new Intl.DateTimeFormat('es-ES', { 
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long'
                    }).format(date);
                },

                formatTime(timeStr) {
                    if (!timeStr) return '';
                    return timeStr.substring(0, 5);
                },

                initializeCalendar() {
                    const firstDay = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
                    const lastDay = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 0);
                    
                    this.calendarWeeks = this.generateCalendarDays(firstDay, lastDay);
                },

                generateCalendarDays(firstDay, lastDay) {
                    if (!this.selectedLocation) return [];
                    
                    console.log('Generating calendar for:', firstDay, lastDay);
                    console.log('Selected location:', this.selectedLocation);
                    
                    const weeks = [];
                    let currentWeek = [];
                    
                    // Ajustamos para que la semana empiece en lunes (1) en lugar de domingo (0)
                    const firstDayOfWeek = firstDay.getDay() || 7;
                    
                    // Filtramos los slots por ubicación antes de generar el calendario
                    const locationSlots = this.availableSlots.filter(slot => 
                        slot.LOCATION_ID === this.selectedLocation
                    );
                    
                    // Añadimos días vacíos para la primera semana
                    for (let i = 1; i < firstDayOfWeek; i++) {
                        currentWeek.push({ date: null, slots: [] });
                    }
                    
                    // Añadimos todos los días del mes
                    for (let day = 1; day <= lastDay.getDate(); day++) {
                        const date = new Date(firstDay.getFullYear(), firstDay.getMonth(), day);
                        const dateStr = this.formatDayNumber(date);
                        
                        const daySlots = locationSlots.filter(slot => slot.AVA_DATE === dateStr);
                        
                        currentWeek.push({ 
                            date: date,
                            slots: daySlots
                        });
                        
                        if (currentWeek.length === 7) {
                            weeks.push(currentWeek);
                            currentWeek = [];
                        }
                    }
                    
                    // Rellenamos la última semana con días vacíos si es necesario
                    while (currentWeek.length < 7) {
                        currentWeek.push({ date: null, slots: [] });
                    }
                    if (currentWeek.length > 0) {
                        weeks.push(currentWeek);
                    }
                    
                    return weeks;
                },

                previousMonth() {
                    this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() - 1);
                    this.initializeCalendar();
                },

                nextMonth() {
                    this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1);
                    this.initializeCalendar();
                },

                formatMonthYear(date) {
                    return new Intl.DateTimeFormat('es-ES', { 
                        month: 'long',
                        year: 'numeric'
                    }).format(date);
                },

                formatDayNumber(date) {
                    if (!date) return '';
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                },

                isMorningSlot(time) {
                    const hour = parseInt(time.split(':')[0]);
                    return hour < 15; // Consideramos mañana hasta las 15:00
                },

                getSlotPeriods(slots) {
                    const hasMorning = slots.some(slot => this.isMorningSlot(slot.AVA_START_TIME));
                    const hasAfternoon = slots.some(slot => !this.isMorningSlot(slot.AVA_START_TIME));
                    return { hasMorning, hasAfternoon };
                },

                selectDay(day) {
                    console.log('Selecting day:', day);
                    if (day.slots.length > 0) {
                        this.selectedDay = day;
                        this.selectedSlot = null;
                        
                        // Esperar al siguiente ciclo para asegurar que el elemento existe
                        this.$nextTick(() => {
                            const hoursSection = document.getElementById('available-hours');
                            if (hoursSection) {
                                hoursSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        });
                    }
                },

                getFirstSlotForLocation(locationId) {
                    return this.availableSlots.find(slot => slot.LOCATION_ID === locationId);
                }
            }
        }
    </script>
</body>
</html> 