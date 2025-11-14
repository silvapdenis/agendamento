<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'anamnesis',
        'physical_examination',
        'diagnosis',
        'treatment_plan',
        'prescriptions',
        'observations',
        'vital_signs',
        'attachments',
        'is_private'
    ];

    protected $casts = [
        'vital_signs' => 'array',
        'attachments' => 'array',
        'is_private' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Get the patient for this medical record
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get the doctor who created this record
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the appointment associated with this record
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Scopes

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    // Accessors

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getHasDiagnosisAttribute()
    {
        return !empty($this->diagnosis);
    }

    public function getHasPrescriptionsAttribute()
    {
        return !empty($this->prescriptions);
    }

    public function getHasAttachmentsAttribute()
    {
        return !empty($this->attachments);
    }

    // Methods

    /**
     * Add vital signs
     */
    public function addVitalSigns(array $vitalSigns)
    {
        $current = $this->vital_signs ?? [];
        $this->update(['vital_signs' => array_merge($current, $vitalSigns)]);
    }

    /**
     * Add attachment
     */
    public function addAttachment(string $filename, string $path, string $type = 'document')
    {
        $current = $this->attachments ?? [];
        $current[] = [
            'filename' => $filename,
            'path' => $path,
            'type' => $type,
            'uploaded_at' => now()->toISOString()
        ];
        $this->update(['attachments' => $current]);
    }

    /**
     * Check if user can view this record
     */
    public function canBeViewedBy(User $user)
    {
        // Patient can always view their own records
        if ($user->id === $this->patient_id) {
            return true;
        }

        // Doctor who created can always view
        if ($user->is_doctor && $user->doctor->id === $this->doctor_id) {
            return true;
        }

        // Admin can view all records
        if ($user->is_admin) {
            return true;
        }

        // Other doctors can view only if not private
        if ($user->is_doctor && !$this->is_private) {
            return true;
        }

        return false;
    }
}