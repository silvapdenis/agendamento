<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', [
                'monday', 'tuesday', 'wednesday', 'thursday', 
                'friday', 'saturday', 'sunday'
            ]);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration_minutes')->default(60); // Duração de cada slot
            $table->integer('break_duration_minutes')->default(0); // Intervalo entre consultas
            $table->time('lunch_start')->nullable(); // Início do almoço
            $table->time('lunch_end')->nullable(); // Fim do almoço
            $table->boolean('is_active')->default(true);
            $table->json('exceptions')->nullable(); // Datas específicas com horários diferentes
            $table->timestamps();
            
            // Índices para performance
            $table->index(['doctor_id', 'clinic_id']);
            $table->index(['day_of_week', 'is_active']);
            
            // Evitar duplicação: um médico não pode ter dois horários no mesmo dia/clínica
            $table->unique(['doctor_id', 'clinic_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
