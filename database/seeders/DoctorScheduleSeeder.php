<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use App\Models\Clinic;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();
        
        foreach ($doctors as $doctor) {
            // Pegar clínicas associadas ao médico
            $clinics = $doctor->clinics;
            
            foreach ($clinics as $clinic) {
                // Criar agenda padrão (Segunda a Sexta)
                $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                
                foreach ($weekDays as $day) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'clinic_id' => $clinic->id,
                        'day_of_week' => $day,
                        'start_time' => '08:00',
                        'end_time' => '18:00',
                        'slot_duration_minutes' => 60,
                        'break_duration_minutes' => 0,
                        'lunch_start' => '12:00',
                        'lunch_end' => '13:00',
                        'is_active' => true,
                    ]);
                }
                
                // Agenda de sábado (só manhã)
                DoctorSchedule::create([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 'saturday',
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'slot_duration_minutes' => 60,
                    'break_duration_minutes' => 0,
                    'lunch_start' => null,
                    'lunch_end' => null,
                    'is_active' => true,
                ]);
            }
        }
    }
}
