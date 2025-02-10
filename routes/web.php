<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/appointments');

Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
Route::get('/appointments/doctors', [AppointmentController::class, 'getDoctors'])->name('appointments.doctors');
Route::get('/appointments/visit-types', [AppointmentController::class, 'getVisitTypes'])->name('appointments.visitTypes');
Route::get('/appointments/slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.slots');
Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
Route::get('/appointments/check-patient', [AppointmentController::class, 'checkPatient'])->name('appointments.checkPatient');
Route::get('/appointment-confirmed', function () {
    return view('appointments.confirmed');
})->name('appointments.confirmed');
