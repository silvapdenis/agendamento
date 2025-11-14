<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Clinic;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = Specialty::all();
        $clinics = Clinic::all();

        $doctors = [
            [
                'user' => [
                    'name' => 'Dr. João Silva',
                    'email' => 'joao.silva@email.com',
                    'password' => Hash::make('password123'),
                    'user_type' => 'doctor',
                    'phone' => '(11) 99999-1111',
                    'birth_date' => '1975-03-15',
                    'cpf' => '123.456.789-01'
                ],
                'doctor' => [
                    'crm' => '123456',
                    'crm_state' => 'SP',
                    'bio' => 'Especialista em Psiquiatria com mais de 15 anos de experiência no tratamento de transtornos de humor e ansiedade.',
                    'consultation_price' => 250.00,
                    'consultation_duration_minutes' => 60,
                    'start_time' => '08:00',
                    'end_time' => '18:00',
                    'available_days' => [1, 2, 3, 4, 5],
                    'accepts_insurance' => true,
                    'is_active' => true
                ],
                'specialty' => 'Psiquiatria'
            ],
            [
                'user' => [
                    'name' => 'Dra. Maria Santos',
                    'email' => 'maria.santos@email.com',
                    'password' => Hash::make('password123'),
                    'user_type' => 'doctor',
                    'phone' => '(11) 99999-2222',
                    'birth_date' => '1980-07-22',
                    'cpf' => '234.567.890-12'
                ],
                'doctor' => [
                    'crm' => '78901',
                    'crm_state' => 'SP',
                    'bio' => 'Psicóloga clínica especializada em terapia cognitivo-comportamental e atendimento de adolescentes.',
                    'consultation_price' => 180.00,
                    'consultation_duration_minutes' => 50,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'available_days' => [1, 2, 3, 4, 6],
                    'accepts_insurance' => false,
                    'is_active' => true
                ],
                'specialty' => 'Psicologia Clínica'
            ],
            [
                'user' => [
                    'name' => 'Dr. Carlos Oliveira',
                    'email' => 'carlos.oliveira@email.com',
                    'password' => Hash::make('password123'),
                    'user_type' => 'doctor',
                    'phone' => '(11) 99999-3333',
                    'birth_date' => '1970-11-08',
                    'cpf' => '345.678.901-23'
                ],
                'doctor' => [
                    'crm' => '234567',
                    'crm_state' => 'SP',
                    'bio' => 'Neurologista com foco em distúrbios do sono e cefaléias. Atendimento humanizado e baseado em evidências.',
                    'consultation_price' => 300.00,
                    'consultation_duration_minutes' => 45,
                    'start_time' => '08:00',
                    'end_time' => '16:00',
                    'available_days' => [2, 3, 4, 5],
                    'accepts_insurance' => true,
                    'is_active' => true
                ],
                'specialty' => 'Neurologia'
            ],
            [
                'user' => [
                    'name' => 'Dra. Ana Costa',
                    'email' => 'ana.costa@email.com',
                    'password' => Hash::make('password123'),
                    'user_type' => 'doctor',
                    'phone' => '(11) 99999-4444',
                    'birth_date' => '1985-01-30',
                    'cpf' => '456.789.012-34'
                ],
                'doctor' => [
                    'crm' => '345678',
                    'crm_state' => 'SP',
                    'bio' => 'Psiquiatra infantil especializada no atendimento de crianças e adolescentes com TDAH e transtornos do espectro autista.',
                    'consultation_price' => 280.00,
                    'consultation_duration_minutes' => 60,
                    'start_time' => '14:00',
                    'end_time' => '20:00',
                    'available_days' => [1, 3, 5, 6],
                    'accepts_insurance' => true,
                    'is_active' => true
                ],
                'specialty' => 'Psiquiatria Infantil'
            ]
        ];

        foreach ($doctors as $doctorData) {
            // Criar usuário
            $user = User::create($doctorData['user']);

            // Buscar especialidade
            $specialty = $specialties->firstWhere('name', $doctorData['specialty']);

            // Criar médico
            $doctor = Doctor::create(array_merge(
                $doctorData['doctor'],
                [
                    'user_id' => $user->id,
                    'specialty_id' => $specialty->id
                ]
            ));

            // Associar médico a clínicas (randomicamente)
            $randomClinics = $clinics->random(rand(1, 2));
            $doctor->clinics()->attach($randomClinics->pluck('id'));
        }
    }
}