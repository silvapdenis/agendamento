<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for authorization
        
        // Doctor management
        Gate::define('manage-doctors', function (User $user) {
            return $user->user_type === 'admin';
        });

        // Clinic management
        Gate::define('manage-clinics', function (User $user) {
            return $user->user_type === 'admin' || 
                   ($user->user_type === 'doctor' && $user->doctor && $user->doctor->isClinicAdmin());
        });

        // Check if user is a doctor
        Gate::define('is-doctor', function (User $user) {
            return $user->user_type === 'doctor';
        });

        // Check if user is a patient
        Gate::define('is-patient', function (User $user) {
            return $user->user_type === 'patient';
        });

        // Access clinic data
        Gate::define('access-clinic-data', function (User $user, Clinic $clinic) {
            if ($user->user_type === 'admin') {
                return true;
            }

            if ($user->user_type === 'doctor' && $user->doctor) {
                return $user->doctor->clinics()->where('clinic_id', $clinic->id)->exists();
            }

            return false;
        });

        // View appointment
        Gate::define('view-appointment', function (User $user, Appointment $appointment) {
            // Admin can view all
            if ($user->user_type === 'admin') {
                return true;
            }

            // Patient can view their own appointments
            if ($user->user_type === 'patient' && $appointment->patient_id === $user->id) {
                return true;
            }

            // Doctor can view their appointments
            if ($user->user_type === 'doctor' && $user->doctor && $appointment->doctor_id === $user->doctor->id) {
                return true;
            }

            return false;
        });

        // Manage appointment
        Gate::define('manage-appointment', function (User $user, Appointment $appointment) {
            // Admin can manage all
            if ($user->user_type === 'admin') {
                return true;
            }

            // Patient can manage their own appointments (limited actions)
            if ($user->user_type === 'patient' && $appointment->patient_id === $user->id) {
                return true;
            }

            // Doctor can manage their appointments
            if ($user->user_type === 'doctor' && $user->doctor && $appointment->doctor_id === $user->doctor->id) {
                return true;
            }

            return false;
        });

        // Create appointment for patient
        Gate::define('create-appointment-for-patient', function (User $user, User $patient) {
            // Admin can create for any patient
            if ($user->user_type === 'admin') {
                return true;
            }

            // Patient can create for themselves
            if ($user->id === $patient->id) {
                return true;
            }

            return false;
        });

        // Manage doctor profile
        Gate::define('manage-doctor-profile', function (User $user, Doctor $doctor) {
            // Admin can manage all doctors
            if ($user->user_type === 'admin') {
                return true;
            }

            // Doctor can manage their own profile
            if ($user->user_type === 'doctor' && $user->doctor && $user->doctor->id === $doctor->id) {
                return true;
            }

            return false;
        });

        // Access medical records
        Gate::define('access-medical-records', function (User $user, User $patient) {
            // Admin can access all records
            if ($user->user_type === 'admin') {
                return true;
            }

            // Patient can access their own records
            if ($user->id === $patient->id) {
                return true;
            }

            // Doctor can access records of their patients (if they have appointments)
            if ($user->user_type === 'doctor' && $user->doctor) {
                return $user->doctor->appointments()
                    ->where('patient_id', $patient->id)
                    ->exists();
            }

            return false;
        });
    }
}