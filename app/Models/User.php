<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_type',
        'cpf',
        'birth_date',
        'address',
        'profile_photo_path',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Get the doctor profile if user is a doctor
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Get appointments as patient
     */
    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    /**
     * Get medical records as patient
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    /**
     * Get notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes

    public function scopePatients($query)
    {
        return $query->where('user_type', 'patient');
    }

    public function scopeDoctors($query)
    {
        return $query->where('user_type', 'doctor');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Mutators & Accessors

    public function getIsPatientAttribute()
    {
        return $this->user_type === 'patient';
    }

    public function getIsDoctorAttribute()
    {
        return $this->user_type === 'doctor';
    }

    public function getIsAdminAttribute()
    {
        return $this->user_type === 'admin';
    }
}