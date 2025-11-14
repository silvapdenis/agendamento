<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\ClinicController;
use App\Http\Controllers\API\SpecialtyController;
use App\Http\Controllers\API\DoctorScheduleController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
        'services' => [
            'laravel' => 'running',
            'database' => \DB::connection()->getPdo() ? 'connected' : 'disconnected'
        ]
    ]);
});

// WhatsApp Business API Webhook
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'webhook']);

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public clinic and doctor search routes
Route::get('/clinics', [ClinicController::class, 'index']);
Route::get('/clinics/{clinic}', [ClinicController::class, 'show']);
Route::get('/clinics/{clinic}/doctors', [ClinicController::class, 'doctors']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
Route::get('/doctors/{doctor}/schedule', [DoctorController::class, 'schedule']);

Route::get('/specialties', [SpecialtyController::class, 'index']);
Route::get('/specialties/{specialty}', [SpecialtyController::class, 'show']);

// Get available appointment slots (public for booking)
Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);

// Doctor schedules (public access for viewing available slots)
Route::get('/doctors/{doctor}/clinics/{clinic}/available-slots', [DoctorScheduleController::class, 'getAvailableSlots']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // Appointment routes
    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

    // Doctor management (for admin/doctors only)
    Route::middleware('can:manage-doctors')->group(function () {
        Route::post('/doctors', [DoctorController::class, 'store']);
        Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);
        Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);
    });

    // Doctor specific routes (for doctors only)
    Route::middleware('can:is-doctor')->group(function () {
        Route::get('/doctors/{doctor}/appointments', [DoctorController::class, 'appointments']);
        
        // Doctor schedule management
        Route::get('/doctors/{doctor}/schedules', [DoctorScheduleController::class, 'index']);
        Route::get('/doctors/{doctor}/clinics/{clinic}/schedules', [DoctorScheduleController::class, 'getForClinic']);
        Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);
        Route::delete('/doctors/{doctor}/clinics/{clinic}/schedules/{day}', [DoctorScheduleController::class, 'destroy']);
    });

    // Clinic management (for admin/clinic owners only)
    Route::middleware('can:manage-clinics')->group(function () {
        Route::post('/clinics', [ClinicController::class, 'store']);
        Route::put('/clinics/{clinic}', [ClinicController::class, 'update']);
        Route::delete('/clinics/{clinic}', [ClinicController::class, 'destroy']);
        Route::post('/clinics/{clinic}/doctors', [ClinicController::class, 'addDoctor']);
        Route::delete('/clinics/{clinic}/doctors', [ClinicController::class, 'removeDoctor']);
    });

    // Clinic specific routes (for clinic admins/doctors)
    Route::middleware('can:access-clinic-data')->group(function () {
        Route::get('/clinics/{clinic}/appointments', [ClinicController::class, 'appointments']);
        Route::get('/clinics/{clinic}/statistics', [ClinicController::class, 'statistics']);
    });

});

// n8n Integration Routes (sem autenticação para permitir chamadas do n8n)
Route::prefix('n8n')->group(function () {
    // Conversation management
    Route::get('/conversation-state', [App\Http\Controllers\API\N8nController::class, 'getConversationState']);
    Route::post('/conversation-state', [App\Http\Controllers\API\N8nController::class, 'updateConversationState']);
    Route::post('/process-message', [App\Http\Controllers\API\N8nController::class, 'processMessage']);
    
    // Data endpoints
    Route::get('/specialties', [App\Http\Controllers\API\N8nController::class, 'getSpecialties']);
    Route::get('/specialties/{specialty}/doctors', [App\Http\Controllers\API\N8nController::class, 'getDoctorsBySpecialty']);
    Route::get('/doctors/{doctor}/clinics', [App\Http\Controllers\API\N8nController::class, 'getDoctorClinics']);
    Route::get('/doctors/{doctor}/clinics/{clinic}/dates', [App\Http\Controllers\API\N8nController::class, 'getAvailableDates']);
    Route::get('/doctors/{doctor}/clinics/{clinic}/dates/{date}/times', [App\Http\Controllers\API\N8nController::class, 'getAvailableTimes']);
    
    // Actions
    Route::post('/appointments', [App\Http\Controllers\API\N8nController::class, 'createAppointment']);
    Route::post('/send-whatsapp', [App\Http\Controllers\API\N8nController::class, 'sendWhatsAppMessage']);
});

// WhatsApp Webhook Routes (sem autenticação para receber webhooks)
Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [App\Http\Controllers\API\WhatsAppController::class, 'verify']);
    Route::post('/webhook', [App\Http\Controllers\API\WhatsAppController::class, 'webhook']);
    
    // Rota protegida para enviar mensagens (apenas para testes/admin)
    Route::middleware('auth:sanctum')->post('/send-message', [App\Http\Controllers\API\WhatsAppController::class, 'sendMessage']);
});

// WhatsApp Webhook routes
Route::get('/whatsapp/webhook', [App\Http\Controllers\WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [App\Http\Controllers\WhatsAppWebhookController::class, 'webhook']);

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint não encontrado'
    ], 404);
});