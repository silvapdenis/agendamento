<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário admin
        User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@medisystem.com',
            'password' => Hash::make('admin123'),
            'user_type' => 'admin',
            'phone' => '(11) 99999-0000',
            'birth_date' => '1985-01-01',
            'cpf' => '000.000.000-00'
        ]);

        // Criar alguns pacientes de exemplo
        $patients = [
            [
                'name' => 'Pedro Almeida',
                'email' => 'pedro.almeida@email.com',
                'password' => Hash::make('patient123'),
                'user_type' => 'patient',
                'phone' => '(11) 99999-5555',
                'birth_date' => '1990-05-15',
                'cpf' => '567.890.123-45'
            ],
            [
                'name' => 'Lucia Fernandes',
                'email' => 'lucia.fernandes@email.com',
                'password' => Hash::make('patient123'),
                'user_type' => 'patient',
                'phone' => '(11) 99999-6666',
                'birth_date' => '1988-12-03',
                'cpf' => '678.901.234-56'
            ],
            [
                'name' => 'Roberto Carlos',
                'email' => 'roberto.carlos@email.com',
                'password' => Hash::make('patient123'),
                'user_type' => 'patient',
                'phone' => '(11) 99999-7777',
                'birth_date' => '1995-08-20',
                'cpf' => '789.012.345-67'
            ],
            [
                'name' => 'Fernanda Lima',
                'email' => 'fernanda.lima@email.com',
                'password' => Hash::make('patient123'),
                'user_type' => 'patient',
                'phone' => '(11) 99999-8888',
                'birth_date' => '1992-04-12',
                'cpf' => '890.123.456-78'
            ]
        ];

        foreach ($patients as $patient) {
            User::create($patient);
        }
    }
}