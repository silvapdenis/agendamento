<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = [
            [
                'name' => 'Centro de Saúde Mental São Paulo',
                'slug' => 'centro-saude-mental-sp',
                'address' => 'Rua Augusta, 1234 - Consolação',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01305-100',
                'phone' => '(11) 3456-7890',
                'email' => 'contato@csmsp.com.br',
                'cnpj' => '12.345.678/0001-90',
                'description' => 'Centro especializado em saúde mental com equipe multidisciplinar.',
                'business_hours' => [
                    'monday' => ['08:00', '18:00'],
                    'tuesday' => ['08:00', '18:00'],
                    'wednesday' => ['08:00', '18:00'],
                    'thursday' => ['08:00', '18:00'],
                    'friday' => ['08:00', '17:00'],
                    'saturday' => ['08:00', '12:00'],
                    'sunday' => null
                ],
                'subscription_plan' => 'premium',
                'is_active' => true
            ],
            [
                'name' => 'Instituto de Psiquiatria Paulista',
                'slug' => 'instituto-psiquiatria-paulista',
                'address' => 'Av. Paulista, 2000 - Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01310-100',
                'phone' => '(11) 2345-6789',
                'email' => 'contato@ipp.com.br',
                'cnpj' => '23.456.789/0001-01',
                'description' => 'Instituto de referência em psiquiatria e neurologia.',
                'business_hours' => [
                    'monday' => ['07:00', '19:00'],
                    'tuesday' => ['07:00', '19:00'],
                    'wednesday' => ['07:00', '19:00'],
                    'thursday' => ['07:00', '19:00'],
                    'friday' => ['07:00', '18:00'],
                    'saturday' => ['08:00', '14:00'],
                    'sunday' => null
                ],
                'subscription_plan' => 'enterprise',
                'is_active' => true
            ],
            [
                'name' => 'Clínica Mente Sã',
                'slug' => 'clinica-mente-sa',
                'address' => 'Rua Oscar Freire, 567 - Jardins',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01426-001',
                'phone' => '(11) 3333-4444',
                'email' => 'contato@mentesa.com.br',
                'cnpj' => '34.567.890/0001-12',
                'description' => 'Clínica boutique especializada em saúde mental.',
                'business_hours' => [
                    'monday' => ['08:00', '18:00'],
                    'tuesday' => ['08:00', '18:00'],
                    'wednesday' => ['08:00', '18:00'],
                    'thursday' => ['08:00', '18:00'],
                    'friday' => ['08:00', '16:00'],
                    'saturday' => null,
                    'sunday' => null
                ],
                'subscription_plan' => 'basic',
                'is_active' => true
            ]
        ];

        foreach ($clinics as $clinic) {
            Clinic::create($clinic);
        }
    }
}