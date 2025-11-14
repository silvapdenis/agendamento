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
    public function up()
    {
        Schema::create('whats_app_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique(); // Número de telefone do usuário
            $table->enum('state', [
                'initial',
                'waiting_specialty',
                'waiting_doctor', 
                'waiting_clinic',
                'waiting_date',
                'waiting_time',
                'waiting_confirmation',
                'waiting_patient_info'
            ])->default('initial'); // Estado atual da conversa
            $table->json('context')->nullable(); // Contexto da conversa (dados temporários)
            $table->timestamp('last_message_at')->nullable(); // Última mensagem
            $table->timestamps();
            
            // Índices para performance
            $table->index(['phone_number', 'state']);
            $table->index('updated_at'); // Para limpeza de conversas antigas
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whats_app_conversations');
    }
};
