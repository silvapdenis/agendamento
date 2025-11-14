<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'crm',
        'crm_state',
        'bio',
        'consultation_price',
        'consultation_duration_minutes',
        'available_days',
        'start_time',
        'end_time',
        'accepts_insurance',
        'insurance_plans',
        'is_active'
    ];

    protected $casts = [
        'consultation_price' => 'decimal:2',
        'available_days' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'accepts_insurance' => 'boolean',
        'insurance_plans' => 'array',
        'is_active' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Get the user associated with this doctor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the doctor's specialty
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Get clinics where this doctor works
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class)
                   ->withPivot('is_admin')
                   ->withTimestamps();
    }

    /**
     * Get doctor's appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get medical records created by this doctor
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Get doctor's schedules
     */
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    /**
     * Get active schedules for a specific clinic
     */
    public function getScheduleForClinic($clinicId)
    {
        return $this->schedules()
                   ->where('clinic_id', $clinicId)
                   ->active()
                   ->get()
                   ->keyBy('day_of_week');
    }

    /**
     * Get clinics where doctor is admin
     */
    public function adminClinics()
    {
        return $this->belongsToMany(Clinic::class)
                   ->wherePivot('is_admin', true);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableOn($query, $dayOfWeek)
    {
        return $query->whereJsonContains('available_days', $dayOfWeek);
    }

    public function scopeBySpecialty($query, $specialtyId)
    {
        return $query->where('specialty_id', $specialtyId);
    }

    // Accessors

    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getFormattedCrmAttribute()
    {
        return "CRM/{$this->crm_state} {$this->crm}";
    }

    public function getAvailableDaysNamesAttribute()
    {
        $days = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira', 
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        return collect($this->available_days ?? [])->map(fn($day) => $days[$day])->toArray();
    }

    // Methods

    /**
     * Check if doctor is available on a specific day
     */
    public function isAvailableOn($dayOfWeek)
    {
        return in_array($dayOfWeek, $this->available_days ?? []);
    }

    /**
     * Check if doctor is admin of any clinic
     */
    public function isClinicAdmin()
    {
        return $this->clinics()->wherePivot('is_admin', true)->exists();
    }
}