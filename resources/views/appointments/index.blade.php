<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita Online - Clínica NYR</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen" x-data="appointmentForm()">
        <div class="max-w-3xl mx-auto py-12 px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Solicitar Cita Online</h1>
            
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
                            <option value="{{ $specialty['id'] }}">{{ $specialty['name'] }}</option>
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

                <!-- Paso 4: Selección de fecha y hora -->
                <div x-show="visitType" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">4. Seleccione fecha y hora</h2>
                    
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
                            <div class="mb-4">
                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-blue-600 mb-2">Primera cita disponible:</p>
                                    <div class="w-full text-left p-3 bg-white rounded-md shadow-sm">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span x-text="formatDate(availableSlots[0].AVA_DATE)" class="font-medium"></span>
                                                <span x-text="formatTime(availableSlots[0].AVA_START_TIME)" class="ml-2 text-gray-600"></span>
                                            </div>
                                            <button 
                                                @click="selectSlot(availableSlots[0])"
                                                :class="{
                                                    'bg-blue-600 text-white hover:bg-blue-700': selectedSlot !== availableSlots[0],
                                                    'bg-white text-blue-600 border-blue-600': selectedSlot === availableSlots[0]
                                                }"
                                                class="px-6 py-2 rounded-md border transition-colors font-medium">
                                                <span x-text="selectedSlot === availableSlots[0] ? 'Cita seleccionada ✓' : 'Seleccionar esta cita'"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <button 
                                        x-show="availableSlots.length > 1"
                                        @click="showMoreSlots = !showMoreSlots"
                                        class="mt-4 text-blue-600 hover:text-blue-800 text-sm w-full flex items-center justify-center gap-2">
                                        <span x-text="showMoreSlots ? 'Ver menos horarios' : 'Ver más horarios disponibles'"></span>
                                        <svg 
                                            xmlns="http://www.w3.org/2000/svg" 
                                            class="h-4 w-4 transition-transform"
                                            :class="showMoreSlots ? 'rotate-180' : ''"
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- More available slots -->
                        <div x-show="showMoreSlots && availableSlots.length > 1" class="mt-4 grid grid-cols-2 gap-2">
                            <template x-for="slot in availableSlots.slice(1)" :key="`${slot.AVA_DATE}-${slot.AVA_START_TIME}`">
                                <button 
                                    @click="selectSlot(slot)"
                                    :class="{'ring-2 ring-blue-500': selectedSlot === slot}"
                                    class="p-3 text-left border rounded-md hover:bg-gray-50">
                                    <span x-text="formatDate(slot.AVA_DATE)" class="block font-medium"></span>
                                    <span x-text="formatTime(slot.AVA_START_TIME)" class="text-gray-600"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="!availableSlots || availableSlots.length === 0" class="text-center py-4 text-gray-500">
                            No hay citas disponibles
                        </div>
                    </div>
                </div>

                <!-- Paso 5: Datos del paciente -->
                <div id="patient-data" x-show="selectedSlot !== null" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">5. Datos del paciente</h2>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
                            <input type="text" x-model="form.patient_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
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
            </div>
        </div>
    </div>

    <script>
        function appointmentForm() {
            return {
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
                    patient_name: '',
                    patient_email: '',
                    patient_phone: ''
                },

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
                        const response = await fetch(`/appointments/visit-types?doctor_id=${this.doctor}`);
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
                    this.showMoreSlots = false;
                    
                    try {
                        const response = await fetch(`/appointments/slots?doctor_id=${this.doctor}&activity_id=${this.visitType}`);
                        const data = await response.json();
                        this.availableSlots = data;
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
                        this.$nextTick(() => document.querySelector('#patient-data').scrollIntoView({behavior: 'smooth'}));
                    }
                },

                async submitForm() {
                    const response = await fetch('/appointments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            date: this.selectedSlot.AVA_DATE,
                            time: this.selectedSlot.AVA_START_TIME,
                            doctor_id: this.doctor,
                            activity_id: this.visitType,
                            ...this.form
                        })
                    });

                    const result = await response.json();
                    if (result.RESULT === 'OK') {
                        alert('Cita confirmada correctamente');
                        window.location.reload();
                    } else {
                        alert('Error al confirmar la cita: ' + result.ERROR_MESSAGE);
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

                groupSlotsByWeek(slots) {
                    const groupedSlots = [];
                    let currentWeek = [];
                    let currentDate = null;

                    slots.forEach(slot => {
                        const date = new Date(slot.AVA_DATE);
                        if (!currentDate || date.getDate() !== currentDate.getDate()) {
                            currentDate = date;
                            currentWeek = [];
                            groupedSlots.push(currentWeek);
                        }
                        currentWeek.push(slot);
                    });

                    return groupedSlots;
                },

                formatDayNumber(date) {
                    const day = date.getDate();
                    const month = date.getMonth() + 1;
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                }
            }
        }
    </script>
</body>
</html> 