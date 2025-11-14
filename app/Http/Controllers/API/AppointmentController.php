<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Appointment::with(['patient', 'doctor.user', 'doctor.specialty', 'clinic']);

        // Filter based on user type
        if ($user->is_patient) {
            $query->where('patient_id', $user->id);
        } elseif ($user->is_doctor) {
            $query->where('doctor_id', $user->doctor->id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('appointment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('appointment_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by clinic (for admin/doctor)
        if ($request->has('clinic_id') && !$user->is_patient) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $appointments = $query->orderBy('appointment_date')->paginate(15);

        return response()->json($appointments);
    }

    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'appointment_date' => 'required|date|after:now',
            'patient_complaint' => 'nullable|string',
            'is_follow_up' => 'boolean',
            'previous_appointment_id' => 'nullable|exists:appointments,id'
        ]);

        $user = $request->user();
        $doctor = Doctor::findOrFail($request->doctor_id);

        // Check if doctor works at this clinic
        if (!$doctor->clinics()->where('clinic_id', $request->clinic_id)->exists()) {
            return response()->json([
                'message' => 'Médico não atende nesta clínica'
            ], 422);
        }

        // Check if time slot is available
        $appointmentDateTime = $request->appointment_date;
        $dayOfWeek = date('N', strtotime($appointmentDateTime));

        if (!$doctor->isAvailableOn($dayOfWeek)) {
            return response()->json([
                'message' => 'Médico não atende neste dia da semana'
            ], 422);
        }

        // Check for conflicts
        $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $appointmentDateTime)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'message' => 'Horário já ocupado'
            ], 422);
        }

        // Calculate appointment end time
        $appointmentEnd = date('Y-m-d H:i:s', strtotime($appointmentDateTime . ' + ' . $doctor->consultation_duration_minutes . ' minutes'));

        $appointment = Appointment::create([
            'patient_id' => $user->id,
            'doctor_id' => $request->doctor_id,
            'clinic_id' => $request->clinic_id,
            'appointment_date' => $appointmentDateTime,
            'appointment_end' => $appointmentEnd,
            'patient_complaint' => $request->patient_complaint,
            'price' => $doctor->consultation_price,
            'is_follow_up' => $request->boolean('is_follow_up'),
            'previous_appointment_id' => $request->previous_appointment_id
        ]);

        // Create notification for patient
        Notification::createAppointmentConfirmation($user->id, $appointment);

        // Create notification for doctor
        Notification::create([
            'user_id' => $doctor->user_id,
            'type' => 'new_appointment',
            'title' => 'Nova Consulta Agendada',
            'message' => "Nova consulta agendada com {$user->name} para {$appointment->formatted_date}",
            'data' => ['appointment_id' => $appointment->id],
            'channel' => 'system'
        ]);

        return response()->json([
            'message' => 'Consulta agendada com sucesso',
            'appointment' => $appointment->load(['patient', 'doctor.user', 'doctor.specialty', 'clinic'])
        ], 201);
    }

    /**
     * Display the specified appointment
     */
    public function show(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        // Check if user can view this appointment
        if ($user->is_patient && $appointment->patient_id !== $user->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        if ($user->is_doctor && $appointment->doctor_id !== $user->doctor->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        return response()->json([
            'appointment' => $appointment->load(['patient', 'doctor.user', 'doctor.specialty', 'clinic', 'medicalRecord'])
        ]);
    }

    /**
     * Update the specified appointment
     */
    public function update(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        // Check permissions
        if ($user->is_patient && $appointment->patient_id !== $user->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        if ($user->is_doctor && $appointment->doctor_id !== $user->doctor->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $request->validate([
            'appointment_date' => 'sometimes|date|after:now',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes' => 'sometimes|nullable|string',
            'patient_complaint' => 'sometimes|nullable|string',
            'payment_status' => 'sometimes|in:pending,paid,cancelled',
            'payment_method' => 'sometimes|nullable|in:cash,card,pix,insurance'
        ]);

        // If rescheduling, check availability
        if ($request->has('appointment_date') && $request->appointment_date !== $appointment->appointment_date) {
            if (!$appointment->canBeRescheduled()) {
                return response()->json([
                    'message' => 'Esta consulta não pode ser reagendada'
                ], 422);
            }

            $newDateTime = $request->appointment_date;
            $conflictingAppointment = Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('appointment_date', $newDateTime)
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled'])
                ->first();

            if ($conflictingAppointment) {
                return response()->json([
                    'message' => 'Novo horário já ocupado'
                ], 422);
            }

            // Update end time as well
            $doctor = $appointment->doctor;
            $appointmentEnd = date('Y-m-d H:i:s', strtotime($newDateTime . ' + ' . $doctor->consultation_duration_minutes . ' minutes'));
            $appointment->appointment_end = $appointmentEnd;
        }

        $appointment->update($request->only([
            'appointment_date', 'status', 'notes', 'patient_complaint', 
            'payment_status', 'payment_method'
        ]));

        // Send notifications based on status change
        if ($request->has('status')) {
            switch ($request->status) {
                case 'confirmed':
                    Notification::createAppointmentConfirmation($appointment->patient_id, $appointment);
                    break;
                case 'cancelled':
                    Notification::create([
                        'user_id' => $appointment->patient_id,
                        'type' => 'appointment_cancelled',
                        'title' => 'Consulta Cancelada',
                        'message' => "Sua consulta de {$appointment->formatted_date} foi cancelada",
                        'data' => ['appointment_id' => $appointment->id],
                        'channel' => 'system'
                    ]);
                    break;
            }
        }

        return response()->json([
            'message' => 'Consulta atualizada com sucesso',
            'appointment' => $appointment->fresh(['patient', 'doctor.user', 'doctor.specialty', 'clinic'])
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $request->validate([
            'cancellation_reason' => 'nullable|string'
        ]);

        if (!$appointment->canBeCancelled()) {
            return response()->json([
                'message' => 'Esta consulta não pode ser cancelada'
            ], 422);
        }

        $appointment->cancel($request->cancellation_reason);

        // Notify both parties
        Notification::create([
            'user_id' => $appointment->patient_id,
            'type' => 'appointment_cancelled',
            'title' => 'Consulta Cancelada',
            'message' => "Sua consulta de {$appointment->formatted_date} foi cancelada",
            'data' => ['appointment_id' => $appointment->id],
            'channel' => 'system'
        ]);

        Notification::create([
            'user_id' => $appointment->doctor->user_id,
            'type' => 'appointment_cancelled',
            'title' => 'Consulta Cancelada',
            'message' => "A consulta de {$appointment->formatted_date} com {$appointment->patient->name} foi cancelada",
            'data' => ['appointment_id' => $appointment->id],
            'channel' => 'system'
        ]);

        return response()->json([
            'message' => 'Consulta cancelada com sucesso',
            'appointment' => $appointment->fresh()
        ]);
    }

    /**
     * Get available time slots for a doctor on a specific date
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'date' => 'required|date|after_or_equal:today'
        ]);

        $doctor = Doctor::findOrFail($request->doctor_id);
        
        // Use the schedule method from DoctorController
        $doctorController = new DoctorController();
        return $doctorController->schedule($request, $doctor);
    }
}