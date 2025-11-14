<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'appointment_date',
        'appointment_end',
        'status',
        'notes',
        'patient_complaint',
        'price',
        'payment_status',
        'payment_method',
        'is_follow_up',
        'previous_appointment_id',
        'cancelled_at',
        'cancellation_reason'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'appointment_end' => 'datetime',
        'price' => 'decimal:2',
        'is_follow_up' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    // Relacionamentos

    /**
     * Get the patient for this appointment
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get the doctor for this appointment
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the clinic for this appointment
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the previous appointment if this is a follow-up
     */
    public function previousAppointment()
    {
        return $this->belongsTo(Appointment::class, 'previous_appointment_id');
    }

    /**
     * Get follow-up appointments
     */
    public function followUpAppointments()
    {
        return $this->hasMany(Appointment::class, 'previous_appointment_id');
    }

    /**
     * Get medical record associated with this appointment
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // Scopes

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    // Accessors

    public function getFormattedDateAttribute()
    {
        return $this->appointment_date->format('d/m/Y H:i');
    }

    public function getFormattedTimeAttribute()
    {
        return $this->appointment_date->format('H:i');
    }

    public function getDurationAttribute()
    {
        if ($this->appointment_end) {
            return $this->appointment_date->diffInMinutes($this->appointment_end);
        }
        return $this->doctor->consultation_duration_minutes ?? 60;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'confirmed' => 'green',
            'in_progress' => 'yellow',
            'completed' => 'gray',
            'cancelled' => 'red',
            'no_show' => 'orange',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            'no_show' => 'Faltou',
            default => 'Desconhecido'
        };
    }

    // Methods

    /**
     * Mark appointment as confirmed
     */
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    /**
     * Cancel the appointment
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason
        ]);
    }

    /**
     * Mark as completed
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'appointment_end' => now()
        ]);
    }

    /**
     * Check if appointment can be cancelled
     */
    public function canBeCancelled()
    {
        return !in_array($this->status, ['completed', 'cancelled', 'no_show']) &&
               $this->appointment_date->isFuture();
    }

    /**
     * Check if appointment can be rescheduled
     */
    public function canBeRescheduled()
    {
        return $this->canBeCancelled();
    }
}