<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'specialty', 'clinics'])
                      ->active();

        // Filter by specialty
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        // Filter by clinic
        if ($request->filled('clinic_id')) {
            $query->whereHas('clinics', function($q) use ($request) {
                $q->where('clinics.id', $request->clinic_id);
            });
        }

        // Search by name
        if ($request->has('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $doctors = $query->paginate(15);

        return response()->json([
            'data' => $doctors->items(),
            'pagination' => [
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'per_page' => $doctors->perPage(),
                'total' => $doctors->total()
            ]
        ]);
    }

    /**
     * Store a newly created doctor
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|unique:users',
            'specialty_id' => 'required|exists:specialties,id',
            'crm' => 'required|string|unique:doctors',
            'crm_state' => 'required|string|size:2',
            'bio' => 'nullable|string',
            'consultation_price' => 'nullable|numeric|min:0',
            'consultation_duration_minutes' => 'nullable|integer|min:15|max:180',
            'available_days' => 'nullable|array',
            'available_days.*' => 'integer|between:1,7',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'accepts_insurance' => 'boolean',
            'insurance_plans' => 'nullable|array'
        ]);

        // Create user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'cpf' => $request->cpf,
            'user_type' => 'doctor'
        ]);

        // Create doctor profile
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialty_id' => $request->specialty_id,
            'crm' => $request->crm,
            'crm_state' => strtoupper($request->crm_state),
            'bio' => $request->bio,
            'consultation_price' => $request->consultation_price,
            'consultation_duration_minutes' => $request->consultation_duration_minutes ?? 60,
            'available_days' => $request->available_days,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'accepts_insurance' => $request->boolean('accepts_insurance'),
            'insurance_plans' => $request->insurance_plans
        ]);

        return response()->json([
            'message' => 'Médico criado com sucesso',
            'doctor' => $doctor->load(['user', 'specialty'])
        ], 201);
    }

    /**
     * Display the specified doctor
     */
    public function show(Doctor $doctor)
    {
        return response()->json([
            'data' => $doctor->load(['user', 'specialty', 'clinics'])
        ]);
    }

    /**
     * Update the specified doctor
     */
    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'phone' => 'sometimes|nullable|string|max:20',
            'specialty_id' => 'sometimes|exists:specialties,id',
            'bio' => 'sometimes|nullable|string',
            'consultation_price' => 'sometimes|nullable|numeric|min:0',
            'consultation_duration_minutes' => 'sometimes|nullable|integer|min:15|max:180',
            'available_days' => 'sometimes|nullable|array',
            'available_days.*' => 'integer|between:1,7',
            'start_time' => 'sometimes|nullable|date_format:H:i',
            'end_time' => 'sometimes|nullable|date_format:H:i|after:start_time',
            'accepts_insurance' => 'sometimes|boolean',
            'insurance_plans' => 'sometimes|nullable|array',
            'is_active' => 'sometimes|boolean'
        ]);

        // Update user data
        if ($request->hasAny(['name', 'email', 'phone'])) {
            $doctor->user->update($request->only(['name', 'email', 'phone']));
        }

        // Update doctor data
        $doctor->update($request->only([
            'specialty_id', 'bio', 'consultation_price', 'consultation_duration_minutes',
            'available_days', 'start_time', 'end_time', 'accepts_insurance',
            'insurance_plans', 'is_active'
        ]));

        return response()->json([
            'message' => 'Médico atualizado com sucesso',
            'doctor' => $doctor->fresh(['user', 'specialty', 'clinics'])
        ]);
    }

    /**
     * Remove the specified doctor
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->update(['is_active' => false]);

        return response()->json([
            'message' => 'Médico desativado com sucesso'
        ]);
    }

    /**
     * Get doctor's schedule for a specific date
     */
    public function schedule(Request $request, Doctor $doctor)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'clinic_id' => 'required|exists:clinics,id'
        ]);

        $date = $request->date;
        $clinicId = $request->clinic_id;
        
        // Convert date to day of week name (monday, tuesday, etc.)
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        // Get doctor's schedule for this clinic and day
        $schedule = $doctor->schedules()
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json([
                'available_times' => [],
                'message' => 'Médico não atende neste dia da semana nesta clínica'
            ]);
        }

        // Get existing appointments for this date and clinic
        $existingAppointments = $doctor->appointments()
            ->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('appointment_date')
            ->map(function($datetime) {
                return date('H:i', strtotime($datetime));
            });

        // Generate available time slots using the schedule
        $availableTimes = $schedule->generateTimeSlots();
        
        // Remove already booked times
        $availableTimes = array_diff($availableTimes, $existingAppointments->toArray());
        
        // Re-index array to ensure proper JSON encoding
        $availableTimes = array_values($availableTimes);

        return response()->json([
            'available_times' => $availableTimes,
            'doctor_info' => [
                'name' => $doctor->user->name,
                'specialty' => $doctor->specialty->name,
                'consultation_duration' => $schedule->slot_duration_minutes,
                'consultation_price' => $doctor->consultation_price
            ],
            'schedule_info' => [
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'lunch_start' => $schedule->lunch_start,
                'lunch_end' => $schedule->lunch_end,
                'slot_duration' => $schedule->slot_duration_minutes
            ]
        ]);
    }

    /**
     * Get doctor's appointments
     */
    public function appointments(Request $request, Doctor $doctor)
    {
        $query = $doctor->appointments()->with(['patient', 'clinic']);

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

        $appointments = $query->orderBy('appointment_date')->get();

        return response()->json([
            'appointments' => $appointments
        ]);
    }
}