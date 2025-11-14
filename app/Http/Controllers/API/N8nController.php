<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\Specialty;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Appointment;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class N8nController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Get or create conversation state
     */
    public function getConversationState(Request $request)
    {
        $phoneNumber = $request->query('phone');
        
        if (!$phoneNumber) {
            return response()->json(['error' => 'Phone number is required'], 400);
        }

        $conversation = WhatsAppConversation::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'state' => 'initial',
                'context' => json_encode([])
            ]
        );

        return response()->json([
            'phone' => $conversation->phone_number,
            'state' => $conversation->state,
            'context' => json_decode($conversation->context, true) ?: []
        ]);
    }

    /**
     * Update conversation state
     */
    public function updateConversationState(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'state' => 'required|string',
            'context' => 'sometimes|array'
        ]);

        $conversation = WhatsAppConversation::updateOrCreate(
            ['phone_number' => $request->phone],
            [
                'state' => $request->state,
                'context' => json_encode($request->context ?? []),
                'last_message_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'conversation' => [
                'phone' => $conversation->phone_number,
                'state' => $conversation->state,
                'context' => json_decode($conversation->context, true)
            ]
        ]);
    }

    /**
     * Get all specialties
     */
    public function getSpecialties()
    {
        $specialties = Specialty::select('id', 'name', 'description')
                                ->orderBy('name')
                                ->get();

        return response()->json([
            'data' => $specialties
        ]);
    }

    /**
     * Get doctors by specialty
     */
    public function getDoctorsBySpecialty(Request $request, $specialtyId)
    {
        $doctors = Doctor::with(['user:id,name', 'clinics:id,name'])
                         ->where('specialty_id', $specialtyId)
                         ->where('is_active', true)
                         ->get()
                         ->map(function ($doctor) {
                             return [
                                 'id' => $doctor->id,
                                 'name' => $doctor->user->name,
                                 'crm' => $doctor->crm,
                                 'clinics' => $doctor->clinics->map(function ($clinic) {
                                     return [
                                         'id' => $clinic->id,
                                         'name' => $clinic->name
                                     ];
                                 })
                             ];
                         });

        return response()->json([
            'data' => $doctors
        ]);
    }

    /**
     * Get clinics where a doctor works
     */
    public function getDoctorClinics($doctorId)
    {
        $doctor = Doctor::with('clinics')->findOrFail($doctorId);
        
        $clinics = $doctor->clinics->map(function ($clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'address' => $clinic->address,
                'city' => $clinic->city,
                'state' => $clinic->state
            ];
        });

        return response()->json([
            'data' => $clinics
        ]);
    }

    /**
     * Get available dates for doctor at clinic
     */
    public function getAvailableDates($doctorId, $clinicId)
    {
        $dates = [];
        $currentDate = Carbon::now();
        
        for ($i = 1; $i <= 30; $i++) {
            $date = $currentDate->copy()->addDays($i);
            $dayOfWeek = strtolower($date->format('l'));
            
            // Check if doctor has schedule for this day at this clinic
            $schedule = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->first();
                
            if ($schedule) {
                $dates[] = [
                    'date' => $date->format('Y-m-d'),
                    'formatted_date' => $date->format('d/m/Y'),
                    'day_name' => $date->locale('pt_BR')->dayName
                ];
            }
            
            // Limit to 10 dates
            if (count($dates) >= 10) {
                break;
            }
        }

        return response()->json([
            'data' => $dates
        ]);
    }

    /**
     * Get available times for specific date
     */
    public function getAvailableTimes($doctorId, $clinicId, $date)
    {
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        
        $schedule = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();
            
        if (!$schedule) {
            return response()->json(['data' => []]);
        }
        
        // Generate time slots
        $timeSlots = $schedule->generateTimeSlots($date);
        
        // Filter booked slots
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('appointment_date')
            ->map(function($datetime) {
                return date('H:i', strtotime($datetime));
            });

        $availableTimes = array_diff($timeSlots, $bookedSlots->toArray());

        return response()->json([
            'data' => array_values($availableTimes)
        ]);
    }

    /**
     * Create appointment
     */
    public function createAppointment(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'patient_name' => 'required|string',
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'appointment_datetime' => 'required|date_format:Y-m-d H:i'
        ]);

        try {
            // Create or find patient
            $patient = User::firstOrCreate(
                ['phone' => $request->phone],
                [
                    'name' => $request->patient_name,
                    'email' => null,
                    'password' => bcrypt('temp_password'),
                    'is_patient' => true
                ]
            );

            // Create appointment
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $request->doctor_id,
                'clinic_id' => $request->clinic_id,
                'appointment_date' => $request->appointment_datetime,
                'status' => 'scheduled',
                'notes' => 'Agendamento via WhatsApp n8n'
            ]);

            // Get related data for response
            $doctor = Doctor::with('user', 'specialty')->find($request->doctor_id);
            $clinic = Clinic::find($request->clinic_id);

            return response()->json([
                'success' => true,
                'appointment' => [
                    'id' => $appointment->id,
                    'patient_name' => $patient->name,
                    'doctor_name' => $doctor->user->name,
                    'specialty' => $doctor->specialty->name,
                    'clinic_name' => $clinic->name,
                    'datetime' => Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i'),
                    'status' => $appointment->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating appointment via n8n', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar agendamento'
            ], 500);
        }
    }

    /**
     * Send WhatsApp message via Laravel service
     */
    public function sendWhatsAppMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        try {
            // TESTE: Simular envio sem usar WhatsApp real
            Log::info('n8n teste: mensagem simulada', [
                'phone' => $request->phone,
                'message' => $request->message
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensagem simulada com sucesso (teste n8n)',
                'phone' => $request->phone,
                'message_length' => strlen($request->message)
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp message via n8n', [
                'error' => $e->getMessage(),
                'phone' => $request->phone
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao enviar mensagem'
            ], 500);
        }
    }

    /**
     * Get conversation context for processing
     */
    public function processMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        $conversation = WhatsAppConversation::where('phone_number', $request->phone)->first();
        
        if (!$conversation) {
            return response()->json([
                'state' => 'initial',
                'context' => [],
                'needs_welcome' => true
            ]);
        }

        $context = json_decode($conversation->context, true) ?: [];

        return response()->json([
            'state' => $conversation->state,
            'context' => $context,
            'needs_welcome' => false,
            'last_updated' => $conversation->updated_at
        ]);
    }
}