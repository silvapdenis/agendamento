<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = User::where('user_type', 'patient')->get();
        $doctors = Doctor::with('clinics')->get();

        $appointments = [
            // Agendamentos confirmados
            [
                'doctor_id' => 1,
                'patient_id' => $patients->first()->id,
                'clinic_id' => 1,
                'appointment_date' => Carbon::now()->addDays(3)->setTime(9, 0),
                'status' => 'confirmed',
                'notes' => 'Primeira consulta - avaliação inicial',
                'price' => 250.00,
                'payment_status' => 'pending'
            ],
            [
                'doctor_id' => 2,
                'patient_id' => $patients->skip(1)->first()->id,
                'clinic_id' => 2,
                'appointment_date' => Carbon::now()->addDays(5)->setTime(14, 0),
                'status' => 'confirmed',
                'notes' => 'Sessão de terapia - seguimento',
                'price' => 180.00,
                'payment_status' => 'pending'
            ],
            [
                'doctor_id' => 3,
                'patient_id' => $patients->skip(2)->first()->id,
                'clinic_id' => 1,
                'appointment_date' => Carbon::now()->addDays(7)->setTime(10, 30),
                'status' => 'confirmed',
                'notes' => 'Consulta neurológica - cefaléia',
                'price' => 300.00,
                'payment_status' => 'pending'
            ],
            
            // Agendamentos pendentes
            [
                'doctor_id' => 1,
                'patient_id' => $patients->skip(3)->first()->id,
                'clinic_id' => 1,
                'appointment_date' => Carbon::now()->addDays(10)->setTime(15, 0),
                'status' => 'scheduled',
                'notes' => 'Retorno - ajuste medicação',
                'price' => 250.00,
                'payment_status' => 'pending'
            ],
            [
                'doctor_id' => 4,
                'patient_id' => $patients->first()->id,
                'clinic_id' => 3,
                'appointment_date' => Carbon::now()->addDays(12)->setTime(8, 30),
                'status' => 'scheduled',
                'notes' => 'Consulta psiquiatria infantil',
                'price' => 280.00,
                'payment_status' => 'pending'
            ],

            // Agendamentos realizados (histórico)
            [
                'doctor_id' => 2,
                'patient_id' => $patients->skip(1)->first()->id,
                'clinic_id' => 2,
                'appointment_date' => Carbon::now()->subDays(7)->setTime(16, 0),
                'status' => 'completed',
                'notes' => 'Sessão inicial - anamnese',
                'price' => 180.00,
                'payment_status' => 'paid',
                'payment_method' => 'pix'
            ],
            [
                'doctor_id' => 1,
                'patient_id' => $patients->skip(2)->first()->id,
                'clinic_id' => 1,
                'appointment_date' => Carbon::now()->subDays(14)->setTime(11, 0),
                'status' => 'completed',
                'notes' => 'Consulta psiquiátrica - avaliação depressão',
                'price' => 250.00,
                'payment_status' => 'paid',
                'payment_method' => 'card'
            ],

            // Agendamento cancelado
            [
                'doctor_id' => 3,
                'patient_id' => $patients->skip(3)->first()->id,
                'clinic_id' => 2,
                'appointment_date' => Carbon::now()->addDays(1)->setTime(13, 30),
                'status' => 'cancelled',
                'notes' => 'Cancelado pelo paciente - reagendamento necessário',
                'price' => 300.00,
                'payment_status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'cancellation_reason' => 'Conflito de agenda do paciente'
            ]
        ];

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }
    }
}