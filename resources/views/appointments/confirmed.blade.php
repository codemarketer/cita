<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita Confirmada - Clínica NYR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-2xl mx-auto p-8">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center justify-center mb-6">
                    <div class="rounded-full bg-green-100 p-3">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl font-semibold text-center text-gray-900 mb-4">¡Cita Confirmada!</h1>
                
                <div class="text-center mb-6">
                    <p class="text-gray-600">Su cita ha sido programada para:</p>
                    <p class="text-lg font-medium mt-2">{{ request('date') }} a las {{ request('time') }}</p>
                    <p class="text-gray-600 mt-2">{{ urldecode(request('specialty')) }}</p>
                    <p class="text-gray-600 mt-2">
                        @if(request('location') === '3')
                            Clínica NYR Campanar<br>
                            C/ Avenida Maestro Rodrigo 16. 46015 Valencia
                        @else
                            Clínica NYR Mestalla<br>
                            C/ Finlandia 15. 46010 Valencia
                        @endif
                    </p>
                </div>

                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-blue-700">
                        Recibirá un correo electrónico de confirmación con los detalles de su cita.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
