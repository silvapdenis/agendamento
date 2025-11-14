<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    /**
     * Get doctor's schedules for all clinics
     */
    public function index(Request $request)
    {
        $doctorId = $request->route('doctor');
        
        // Verificar se o usuário pode acessar as agendas deste médico
        $this->authorizeDoctor($doctorId);
        
        $schedules = DoctorSchedule::with('clinic')
                                  ->where('doctor_id', $doctorId)
                                  ->orderBy('clinic_id')
                                  ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
                                  ->get()
                                  ->groupBy('clinic_id');
        
        return response()->json([
            'data' => $schedules
        ]);
    }

    /**
     * Get doctor's schedule for a specific clinic
     */
    public function getForClinic(Request $request, $doctorId, $clinicId)
    {
        $this->authorizeDoctor($doctorId);
        
        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
                                  ->where('clinic_id', $clinicId)
                                  ->active()
                                  ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
                                  ->get()
                                  ->keyBy('day_of_week');
        
        return response()->json([
            'data' => $schedules
        ]);
    }

    /**
     * Store or update doctor's schedule
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.slot_duration_minutes' => 'required|integer|min:15|max:180',
            'schedules.*.break_duration_minutes' => 'nullable|integer|min:0|max:60',
            'schedules.*.lunch_start' => 'nullable|date_format:H:i',
            'schedules.*.lunch_end' => 'nullable|date_format:H:i|after:schedules.*.lunch_start',
            'schedules.*.is_active' => 'boolean'
        ]);

        $this->authorizeDoctor($request->doctor_id);

        $doctor = Doctor::findOrFail($request->doctor_id);
        $clinicId = $request->clinic_id;

        // Verificar se o médico trabalha nesta clínica
        if (!$doctor->clinics()->where('clinic_id', $clinicId)->exists()) {
            return response()->json([
                'message' => 'Médico não está associado a esta clínica'
            ], 403);
        }

        $createdSchedules = [];

        foreach ($request->schedules as $scheduleData) {
            $schedule = DoctorSchedule::updateOrCreate(
                [
                    'doctor_id' => $request->doctor_id,
                    'clinic_id' => $clinicId,
                    'day_of_week' => $scheduleData['day_of_week']
                ],
                [
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'],
                    'slot_duration_minutes' => $scheduleData['slot_duration_minutes'],
                    'break_duration_minutes' => $scheduleData['break_duration_minutes'] ?? 0,
                    'lunch_start' => $scheduleData['lunch_start'] ?? null,
                    'lunch_end' => $scheduleData['lunch_end'] ?? null,
                    'is_active' => $scheduleData['is_active'] ?? true
                ]
            );

            $createdSchedules[] = $schedule;
        }

        return response()->json([
            'message' => 'Agenda atualizada com sucesso',
            'data' => $createdSchedules
        ]);
    }

    /**
     * Get available time slots for a specific date
     */
    public function getAvailableSlots(Request $request, $doctorId, $clinicId)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today'
        ]);

        $date = $request->date;
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                                 ->where('clinic_id', $clinicId)
                                 ->where('day_of_week', $dayOfWeek)
                                 ->active()
                                 ->first();

        if (!$schedule) {
            return response()->json([
                'data' => [],
                'message' => 'Médico não atende neste dia nesta clínica'
            ]);
        }

        $timeSlots = $schedule->generateTimeSlots($date);
        
        // Filtrar slots já ocupados por agendamentos existentes
        $bookedSlots = \App\Models\Appointment::where('doctor_id', $doctorId)
                                 ->where('clinic_id', $clinicId)
                                 ->whereDate('appointment_date', $date)
                                 ->whereNotIn('status', ['cancelled'])
                                 ->pluck('appointment_date')
                                 ->map(function($datetime) {
                                     return date('H:i', strtotime($datetime));
                                 });

        // Remover horários já ocupados
        $availableSlots = array_diff($timeSlots, $bookedSlots->toArray());
        $availableSlots = array_values($availableSlots); // Re-index array

        return response()->json([
            'data' => $availableSlots,
            'schedule_info' => [
                'day' => $schedule->day_name,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'slot_duration' => $schedule->slot_duration_minutes
            ]
        ]);
    }

    /**
     * Delete a schedule
     */
    public function destroy(Request $request, $doctorId, $clinicId, $dayOfWeek)
    {
        $this->authorizeDoctor($doctorId);

        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                                 ->where('clinic_id', $clinicId)
                                 ->where('day_of_week', $dayOfWeek)
                                 ->first();

        if (!$schedule) {
            return response()->json([
                'message' => 'Agenda não encontrada'
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'message' => 'Agenda removida com sucesso'
        ]);
    }

    /**
     * Authorize doctor access
     */
    private function authorizeDoctor($doctorId)
    {
        $user = Auth::user();
        
        if ($user->user_type === 'admin') {
            return; // Admin can access all
        }
        
        if ($user->user_type === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor || $doctor->id != $doctorId) {
                abort(403, 'Acesso negado');
            }
        } else {
            abort(403, 'Acesso negado');
        }
    }
}
