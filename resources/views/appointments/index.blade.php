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
                        <div x-show="showMoreSlots && availableSlots.length > 1" class="mt-4">
                            <!-- Calendar header -->
                            <div class="flex items-center justify-between mb-4">
                                <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <h3 class="text-lg font-semibold" x-text="formatMonthYear(currentMonth)"></h3>
                                <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Calendar grid -->
                            <div class="border border-gray-200 rounded-lg">
                                <!-- Days of week header -->
                                <div class="grid grid-cols-7 gap-px bg-gray-50 border-b border-gray-200 text-xs text-gray-500">
                                    <div class="px-2 py-2 text-center">Lun</div>
                                    <div class="px-2 py-2 text-center">Mar</div>
                                    <div class="px-2 py-2 text-center">Mié</div>
                                    <div class="px-2 py-2 text-center">Jue</div>
                                    <div class="px-2 py-2 text-center">Vie</div>
                                    <div class="px-2 py-2 text-center">Sáb</div>
                                    <div class="px-2 py-2 text-center">Dom</div>
                                </div>

                                <!-- Calendar days -->
                                <div class="bg-white">
                                    <template x-for="(week, weekIndex) in calendarWeeks" :key="weekIndex">
                                        <div class="grid grid-cols-7 border-b last:border-b-0">
                                            <template x-for="(day, dayIndex) in week" :key="dayIndex">
                                                <div 
                                                    class="min-h-[80px] border-r last:border-r-0 relative"
                                                    :class="{ 
                                                        'bg-gray-50': !day.date,
                                                        'cursor-pointer hover:bg-blue-50': day.date && day.slots.length > 0,
                                                        'bg-blue-50': selectedDay === day
                                                    }"
                                                    @click="day.date && day.slots.length > 0 && selectDay(day)"
                                                >
                                                    <template x-if="day.date">
                                                        <div class="p-2 flex flex-col items-center">
                                                            <span 
                                                                class="text-sm mb-1"
                                                                :class="{
                                                                    'text-gray-900': day.date && day.slots.length > 0,
                                                                    'text-gray-400': !day.slots.length
                                                                }"
                                                                x-text="day.date.getDate()">
                                                            </span>
                                                            <!-- Period indicators -->
                                                            <template x-if="day.slots.length > 0">
                                                                <div class="flex flex-col gap-1 items-center">
                                                                    <template x-if="getSlotPeriods(day.slots).hasMorning">
                                                                        <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full">
                                                                            Mañana
                                                                        </span>
                                                                    </template>
                                                                    <template x-if="getSlotPeriods(day.slots).hasAfternoon">
                                                                        <span class="text-[10px] px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded-full">
                                                                            Tarde
                                                                        </span>
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

                            <!-- Time slots for selected day -->
                            <div 
                                id="available-hours"
                                x-show="selectedDay && selectedDay.slots.length > 0" 
                                class="mt-4 bg-white rounded-lg border border-gray-200 p-4"
                            >
                                <h4 class="text-sm font-medium text-gray-900 mb-3" x-text="selectedDay ? formatDate(formatDayNumber(selectedDay.date)) : ''"></h4>
                                <div class="grid grid-cols-3 gap-2">
                                    <template x-for="slot in selectedDay?.slots" :key="slot.AVA_START_TIME">
                                        <button
                                            @click="selectSlot(slot)"
                                            class="px-3 py-2 text-sm rounded-md border transition-colors"
                                            :class="{
                                                'bg-blue-600 text-white hover:bg-blue-700 border-transparent': selectedSlot !== slot,
                                                'bg-white text-blue-600 border-blue-600': selectedSlot === slot
                                            }"
                                        >
                                            <span x-text="formatTime(slot.AVA_START_TIME)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
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
                calendarWeeks: [],
                currentMonth: new Date(),
                selectedDay: null,
                
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

                initializeCalendar() {
                    const firstDay = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
                    const lastDay = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 0);
                    
                    this.calendarWeeks = this.generateCalendarDays(firstDay, lastDay);
                },

                generateCalendarDays(firstDay, lastDay) {
                    console.log('Generating calendar for:', firstDay, lastDay);
                    console.log('Available slots:', this.availableSlots);
                    const weeks = [];
                    let currentWeek = [];
                    
                    // Ajustamos para que la semana empiece en lunes (1) en lugar de domingo (0)
                    const firstDayOfWeek = firstDay.getDay() || 7;
                    
                    // Añadimos días vacíos para la primera semana (ajustado para empezar en lunes)
                    for (let i = 1; i < firstDayOfWeek; i++) {
                        currentWeek.push({ date: null, slots: [] });
                    }
                    
                    // Añadimos todos los días del mes
                    for (let day = 1; day <= lastDay.getDate(); day++) {
                        const date = new Date(firstDay.getFullYear(), firstDay.getMonth(), day);
                        const dateStr = this.formatDayNumber(date);
                        
                        const daySlots = this.availableSlots.filter(slot => {
                            const matches = slot.AVA_DATE === dateStr;
                            if (matches) {
                                console.log(`Found slots for ${dateStr}:`, slot);
                            }
                            return matches;
                        });
                        
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
                }
            }
        }
    </script>
</body>
</html> 