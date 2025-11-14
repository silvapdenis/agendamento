<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            [
                'name' => 'Psiquiatria',
                'slug' => 'psiquiatria',
                'description' => 'Especialidade médica focada no diagnóstico, tratamento e prevenção de transtornos mentais, emocionais e comportamentais.'
            ],
            [
                'name' => 'Psicologia Clínica',
                'slug' => 'psicologia-clinica',
                'description' => 'Área da psicologia que se dedica ao estudo, avaliação e tratamento de problemas psicológicos e transtornos mentais.'
            ],
            [
                'name' => 'Neurologia',
                'slug' => 'neurologia',
                'description' => 'Especialidade médica que trata distúrbios estruturais do sistema nervoso.'
            ],
            [
                'name' => 'Psiquiatria Infantil',
                'slug' => 'psiquiatria-infantil',
                'description' => 'Subespecialidade da psiquiatria focada no diagnóstico e tratamento de transtornos mentais em crianças e adolescentes.'
            ],
            [
                'name' => 'Terapia Familiar',
                'slug' => 'terapia-familiar',
                'description' => 'Abordagem terapêutica que trabalha com famílias para melhorar a comunicação e resolver conflitos.'
            ]
        ];

        foreach ($specialties as $specialty) {
            Specialty::create($specialty);
        }
    }
}
