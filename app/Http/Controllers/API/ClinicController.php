<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Display a listing of clinics
     */
    public function index(Request $request)
    {
        $query = Clinic::with(['doctors.user', 'doctors.specialty'])
                      ->where('is_active', true);

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Filter by state
        if ($request->has('state')) {
            $query->where('state', $request->state);
        }

        $clinics = $query->paginate(15);

        return response()->json([
            'data' => $clinics->items(),
            'pagination' => [
                'current_page' => $clinics->currentPage(),
                'last_page' => $clinics->lastPage(),
                'per_page' => $clinics->perPage(),
                'total' => $clinics->total()
            ]
        ]);
    }

    /**
     * Store a newly created clinic
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|unique:clinics',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'zip_code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'business_hours' => 'nullable|array',
            'subscription_plan' => 'sometimes|in:basic,premium,enterprise'
        ]);

        $clinic = Clinic::create($request->all());

        return response()->json([
            'message' => 'Clínica criada com sucesso',
            'clinic' => $clinic
        ], 201);
    }

    /**
     * Display the specified clinic
     */
    public function show(Clinic $clinic)
    {
        return response()->json([
            'data' => $clinic->load(['doctors.user', 'doctors.specialty'])
        ]);
    }

    /**
     * Update the specified clinic
     */
    public function update(Request $request, Clinic $clinic)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'cnpj' => 'sometimes|nullable|string|unique:clinics,cnpj,' . $clinic->id,
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|size:2',
            'zip_code' => 'sometimes|string|max:10',
            'description' => 'sometimes|nullable|string',
            'business_hours' => 'sometimes|nullable|array',
            'subscription_plan' => 'sometimes|in:basic,premium,enterprise',
            'is_active' => 'sometimes|boolean'
        ]);

        $clinic->update($request->all());

        return response()->json([
            'message' => 'Clínica atualizada com sucesso',
            'clinic' => $clinic->fresh(['doctors.user', 'doctors.specialty'])
        ]);
    }

    /**
     * Remove the specified clinic
     */
    public function destroy(Clinic $clinic)
    {
        $clinic->update(['is_active' => false]);

        return response()->json([
            'message' => 'Clínica desativada com sucesso'
        ]);
    }

    /**
     * Get clinic's doctors
     */
    public function doctors(Clinic $clinic)
    {
        $doctors = $clinic->doctors()
                          ->with(['user', 'specialty'])
                          ->where('is_active', true)
                          ->get();

        return response()->json([
            'data' => $doctors
        ]);
    }

    /**
     * Add doctor to clinic
     */
    public function addDoctor(Request $request, Clinic $clinic)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'is_admin' => 'boolean'
        ]);

        // Check if doctor is already associated with clinic
        if ($clinic->doctors()->where('doctor_id', $request->doctor_id)->exists()) {
            return response()->json([
                'message' => 'Médico já está associado a esta clínica'
            ], 422);
        }

        $clinic->doctors()->attach($request->doctor_id, [
            'is_admin' => $request->boolean('is_admin')
        ]);

        return response()->json([
            'message' => 'Médico adicionado à clínica com sucesso'
        ]);
    }

    /**
     * Remove doctor from clinic
     */
    public function removeDoctor(Request $request, Clinic $clinic)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        $clinic->doctors()->detach($request->doctor_id);

        return response()->json([
            'message' => 'Médico removido da clínica com sucesso'
        ]);
    }

    /**
     * Get clinic's appointments
     */
    public function appointments(Request $request, Clinic $clinic)
    {
        $query = $clinic->appointments()->with(['patient', 'doctor.user']);

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

        // Filter by doctor
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('appointment_date')->paginate(20);

        return response()->json($appointments);
    }

    /**
     * Get clinic statistics
     */
    public function statistics(Clinic $clinic)
    {
        $totalDoctors = $clinic->doctors()->count();
        $activeDoctors = $clinic->doctors()->where('is_active', true)->count();
        
        $appointmentsToday = $clinic->appointments()
            ->whereDate('appointment_date', today())
            ->count();
            
        $appointmentsThisMonth = $clinic->appointments()
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->count();

        $completedAppointments = $clinic->appointments()
            ->where('status', 'completed')
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->count();

        $revenue = $clinic->appointments()
            ->where('payment_status', 'paid')
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->sum('price');

        return response()->json([
            'statistics' => [
                'total_doctors' => $totalDoctors,
                'active_doctors' => $activeDoctors,
                'appointments_today' => $appointmentsToday,
                'appointments_this_month' => $appointmentsThisMonth,
                'completed_appointments' => $completedAppointments,
                'monthly_revenue' => $revenue,
                'subscription_plan' => $clinic->subscription_plan,
                'subscription_active' => $clinic->is_subscription_active
            ]
        ]);
    }
}