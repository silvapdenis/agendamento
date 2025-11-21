<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Este seeder importará os dados do MySQL para PostgreSQL
        
        // Primeiro, vamos executar as migrações para garantir que as tabelas existam
        
        // Dados das especialidades
        $specialties = [
            ['id' => 1, 'name' => 'Cardiologia', 'description' => 'Especialidade médica que se dedica ao diagnóstico e tratamento das doenças cardiovasculares'],
            ['id' => 2, 'name' => 'Dermatologia', 'description' => 'Especialidade médica que se dedica ao diagnóstico e tratamento das doenças de pele'],
            ['id' => 3, 'name' => 'Neurologia', 'description' => 'Especialidade médica que se dedica ao diagnóstico e tratamento das doenças do sistema nervoso'],
            ['id' => 4, 'name' => 'Pediatria', 'description' => 'Especialidade médica que se dedica ao cuidado da saúde de crianças e adolescentes'],
            ['id' => 5, 'name' => 'Ortopedia', 'description' => 'Especialidade médica que se dedica ao diagnóstico e tratamento das doenças do aparelho locomotor']
        ];

        foreach ($specialties as $specialty) {
            DB::table('specialties')->updateOrInsert(
                ['id' => $specialty['id']],
                $specialty
            );
        }

        // Dados dos usuários
        $users = [
            [
                'id' => 1,
                'name' => 'Administrador Sistema',
                'email' => 'admin@medisystem.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'user_type' => 'admin',
                'phone' => '(11) 99999-0000',
                'birth_date' => '1985-01-01',
                'cpf' => '000.000.000-00'
            ],
            [
                'id' => 2,
                'name' => 'Dr. Carlos Silva',
                'email' => 'carlos@medisystem.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'user_type' => 'doctor',
                'phone' => '(11) 98765-4321',
                'birth_date' => '1980-05-15',
                'cpf' => '123.456.789-01'
            ],
            [
                'id' => 3,
                'name' => 'Dra. Ana Santos',
                'email' => 'ana@medisystem.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'user_type' => 'doctor',
                'phone' => '(11) 98765-4322',
                'birth_date' => '1982-03-20',
                'cpf' => '123.456.789-02'
            ],
            [
                'id' => 4,
                'name' => 'João da Silva',
                'email' => 'joao@exemplo.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'user_type' => 'patient',
                'phone' => '(11) 99999-1234',
                'birth_date' => '1990-01-15',
                'cpf' => '111.222.333-44'
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                array_merge($user, [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'email_verified_at' => now()
                ])
            );
        }

        // Dados das clínicas
        $clinics = [
            [
                'id' => 1,
                'name' => 'Clínica Central',
                'slug' => 'clinica-central',
                'phone' => '(11) 3456-7890',
                'email' => 'contato@clinicacentral.com',
                'address' => 'Rua das Flores, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
                'is_active' => true
            ],
            [
                'id' => 2,
                'name' => 'Clínica Norte',
                'slug' => 'clinica-norte',
                'phone' => '(11) 3456-7891',
                'email' => 'contato@clinicanorte.com',
                'address' => 'Av. Paulista, 456',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-568',
                'is_active' => true
            ]
        ];

        foreach ($clinics as $clinic) {
            DB::table('clinics')->updateOrInsert(
                ['id' => $clinic['id']],
                array_merge($clinic, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Dados dos médicos
        $doctors = [
            [
                'id' => 1,
                'user_id' => 2,
                'specialty_id' => 1,
                'crm' => '123456',
                'crm_state' => 'SP',
                'bio' => 'Cardiologista com 15 anos de experiência',
                'consultation_price' => 300.00,
                'consultation_duration_minutes' => 30,
                'is_active' => true
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'specialty_id' => 2,
                'crm' => '654321',
                'crm_state' => 'SP',
                'bio' => 'Dermatologista especializada em pele sensível',
                'consultation_price' => 250.00,
                'consultation_duration_minutes' => 45,
                'is_active' => true
            ]
        ];

        foreach ($doctors as $doctor) {
            DB::table('doctors')->updateOrInsert(
                ['id' => $doctor['id']],
                array_merge($doctor, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Relacionamento clínica-médico
        $clinicDoctors = [
            ['clinic_id' => 1, 'doctor_id' => 1, 'is_admin' => true],
            ['clinic_id' => 1, 'doctor_id' => 2, 'is_admin' => false],
            ['clinic_id' => 2, 'doctor_id' => 1, 'is_admin' => false],
        ];

        foreach ($clinicDoctors as $relation) {
            DB::table('clinic_doctor')->updateOrInsert(
                ['clinic_id' => $relation['clinic_id'], 'doctor_id' => $relation['doctor_id']],
                array_merge($relation, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        echo "Dados importados com sucesso!\n";
    }

    public function down()
    {
        // Remove os dados importados
        DB::table('clinic_doctor')->truncate();
        DB::table('doctors')->truncate();
        DB::table('clinics')->truncate();
        DB::table('users')->truncate();
        DB::table('specialties')->truncate();
    }
};