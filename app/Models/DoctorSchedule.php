<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'break_duration_minutes',
        'lunch_start',
        'lunch_end',
        'is_active',
        'exceptions'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exceptions' => 'array',
    ];

    // Relacionamentos
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    // Métodos auxiliares
    public function generateTimeSlots($date = null)
    {
        $slots = [];
        
        // Extrair apenas H:i dos campos de tempo (remover segundos se houver)
        $startTime = substr($this->start_time, 0, 5); // "08:00:00" -> "08:00"
        $endTime = substr($this->end_time, 0, 5);
        
        $current = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        
        while ($current->lt($end)) {
            // Verificar se não está no horário de almoço
            if ($this->lunch_start && $this->lunch_end) {
                $lunchStartTime = substr($this->lunch_start, 0, 5);
                $lunchEndTime = substr($this->lunch_end, 0, 5);
                
                $lunchStart = Carbon::createFromFormat('H:i', $lunchStartTime);
                $lunchEnd = Carbon::createFromFormat('H:i', $lunchEndTime);
                
                if ($current->gte($lunchStart) && $current->lt($lunchEnd)) {
                    $current = $lunchEnd->copy();
                    continue;
                }
            }
            
            $slotEnd = $current->copy()->addMinutes($this->slot_duration_minutes);
            
            if ($slotEnd->lte($end)) {
                $slots[] = $current->format('H:i');
            }
            
            $current->addMinutes($this->slot_duration_minutes + $this->break_duration_minutes);
        }
        
        return $slots;
    }

    public function getDayNameAttribute()
    {
        $days = [
            'monday' => 'Segunda-feira',
            'tuesday' => 'Terça-feira',
            'wednesday' => 'Quarta-feira',
            'thursday' => 'Quinta-feira',
            'friday' => 'Sexta-feira',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo'
        ];
        
        return $days[$this->day_of_week] ?? $this->day_of_week;
    }
}
