<?php

namespace App\Services;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Specialty;
use App\Models\Appointment;
use App\Models\WhatsAppConversation;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppConversationService
{
    /**
     * Processar mensagem do usuário e retornar resposta
     */
    public function processUserMessage($phoneNumber, $message)
    {
        // Buscar ou criar conversa
        $conversation = WhatsAppConversation::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'state' => 'initial',
                'context' => json_encode([])
            ]
        );

        // Processar baseado no estado atual
        return $this->handleConversationState($conversation, $message);
    }

    /**
     * Gerenciar estado da conversa
     */
    private function handleConversationState($conversation, $message)
    {
        $state = $conversation->state;
        $context = json_decode($conversation->context, true) ?: [];

        Log::info('Processando estado da conversa', [
            'phone' => $conversation->phone_number,
            'state' => $state,
            'message' => $message
        ]);

        switch ($state) {
            case 'initial':
                return $this->handleInitialState($conversation, $message);
            
            case 'waiting_specialty':
                return $this->handleSpecialtySelection($conversation, $message, $context);
            
            case 'waiting_doctor':
                return $this->handleDoctorSelection($conversation, $message, $context);
            
            case 'waiting_clinic':
                return $this->handleClinicSelection($conversation, $message, $context);
            
            case 'waiting_date':
                return $this->handleDateSelection($conversation, $message, $context);
            
            case 'waiting_time':
                return $this->handleTimeSelection($conversation, $message, $context);
            
            case 'waiting_confirmation':
                return $this->handleConfirmation($conversation, $message, $context);
            
            case 'waiting_patient_info':
                return $this->handlePatientInfo($conversation, $message, $context);
            
            default:
                return $this->resetConversation($conversation);
        }
    }

    /**
     * Estado inicial - detectar intenção
     */
    private function handleInitialState($conversation, $message)
    {
        $message = strtolower(trim($message));
        
        // Palavras-chave para agendamento
        $appointmentKeywords = ['agendar', 'consulta', 'médico', 'doutor', 'horário', 'marcar'];
        
        $isAppointmentRequest = false;
        foreach ($appointmentKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $isAppointmentRequest = true;
                break;
            }
        }

        if ($isAppointmentRequest) {
            return $this->startAppointmentFlow($conversation);
        } else {
            return $this->showWelcomeMessage($conversation);
        }
    }

    /**
     * Iniciar fluxo de agendamento
     */
    private function startAppointmentFlow($conversation)
    {
        $specialties = Specialty::all();
        
        if ($specialties->isEmpty()) {
            return "Desculpe, não há especialidades disponíveis no momento. Tente novamente mais tarde.";
        }

        $message = "🏥 *Agendamento de Consulta*\n\n";
        $message .= "Para agendar sua consulta, preciso de algumas informações.\n\n";
        $message .= "*Especialidades disponíveis:*\n";
        
        foreach ($specialties as $index => $specialty) {
            $message .= ($index + 1) . ". " . $specialty->name . "\n";
        }
        
        $message .= "\n📝 Digite o *número* da especialidade desejada:";

        $this->updateConversationState($conversation, 'waiting_specialty', [
            'specialties' => $specialties->toArray()
        ]);

        return $message;
    }

    /**
     * Processar seleção de especialidade
     */
    private function handleSpecialtySelection($conversation, $message, $context)
    {
        $specialtyIndex = intval($message) - 1;
        $specialties = $context['specialties'];

        if (!isset($specialties[$specialtyIndex])) {
            return "❌ Opção inválida. Por favor, digite o número de uma especialidade válida.";
        }

        $selectedSpecialty = $specialties[$specialtyIndex];
        
        // Buscar médicos da especialidade
        $doctors = Doctor::with(['user', 'clinics'])
                         ->where('specialty_id', $selectedSpecialty['id'])
                         ->where('is_active', true)
                         ->get();

        if ($doctors->isEmpty()) {
            return "😔 Não há médicos disponíveis para esta especialidade no momento.";
        }

        $message = "👨‍⚕️ *Médicos disponíveis em " . $selectedSpecialty['name'] . ":*\n\n";
        
        foreach ($doctors as $index => $doctor) {
            $message .= ($index + 1) . ". Dr(a). " . $doctor->user->name . "\n";
        }
        
        $message .= "\n📝 Digite o *número* do médico desejado:";

        $this->updateConversationState($conversation, 'waiting_doctor', [
            'specialty' => $selectedSpecialty,
            'doctors' => $doctors->toArray()
        ]);

        return $message;
    }

    /**
     * Processar seleção de médico
     */
    private function handleDoctorSelection($conversation, $message, $context)
    {
        $doctorIndex = intval($message) - 1;
        $doctors = $context['doctors'];

        if (!isset($doctors[$doctorIndex])) {
            return "❌ Opção inválida. Por favor, digite o número de um médico válido.";
        }

        $selectedDoctor = $doctors[$doctorIndex];
        
        // Buscar clínicas do médico
        $doctor = Doctor::with('clinics')->find($selectedDoctor['id']);
        $clinics = $doctor->clinics;

        if ($clinics->isEmpty()) {
            return "😔 Este médico não está disponível em nenhuma clínica no momento.";
        }

        $message = "🏥 *Clínicas onde Dr(a). " . $selectedDoctor['user']['name'] . " atende:*\n\n";
        
        foreach ($clinics as $index => $clinic) {
            $message .= ($index + 1) . ". " . $clinic->name . "\n";
            $message .= "   📍 " . $clinic->address . ", " . $clinic->city . "\n\n";
        }
        
        $message .= "📝 Digite o *número* da clínica desejada:";

        $this->updateConversationState($conversation, 'waiting_clinic', [
            'specialty' => $context['specialty'],
            'doctor' => $selectedDoctor,
            'clinics' => $clinics->toArray()
        ]);

        return $message;
    }

    /**
     * Processar seleção de clínica
     */
    private function handleClinicSelection($conversation, $message, $context)
    {
        $clinicIndex = intval($message) - 1;
        $clinics = $context['clinics'];

        if (!isset($clinics[$clinicIndex])) {
            return "❌ Opção inválida. Por favor, digite o número de uma clínica válida.";
        }

        $selectedClinic = $clinics[$clinicIndex];

        // Buscar próximas datas disponíveis
        $availableDates = $this->getAvailableDates($context['doctor']['id'], $selectedClinic['id']);

        if (empty($availableDates)) {
            return "😔 Não há datas disponíveis para este médico nesta clínica nos próximos 30 dias.";
        }

        $message = "📅 *Datas disponíveis:*\n\n";
        
        foreach ($availableDates as $index => $date) {
            $formattedDate = Carbon::parse($date)->format('d/m/Y');
            $dayName = Carbon::parse($date)->locale('pt_BR')->dayName;
            $message .= ($index + 1) . ". " . $formattedDate . " (" . ucfirst($dayName) . ")\n";
        }
        
        $message .= "\n📝 Digite o *número* da data desejada:";

        $this->updateConversationState($conversation, 'waiting_date', [
            'specialty' => $context['specialty'],
            'doctor' => $context['doctor'],
            'clinic' => $selectedClinic,
            'available_dates' => $availableDates
        ]);

        return $message;
    }

    /**
     * Processar seleção de data
     */
    private function handleDateSelection($conversation, $message, $context)
    {
        $dateIndex = intval($message) - 1;
        $dates = $context['available_dates'];

        if (!isset($dates[$dateIndex])) {
            return "❌ Opção inválida. Por favor, digite o número de uma data válida.";
        }

        $selectedDate = $dates[$dateIndex];

        // Buscar horários disponíveis para a data
        $availableTimes = $this->getAvailableTimes(
            $context['doctor']['id'],
            $context['clinic']['id'],
            $selectedDate
        );

        if (empty($availableTimes)) {
            return "😔 Não há horários disponíveis para esta data.";
        }

        $message = "🕐 *Horários disponíveis para " . Carbon::parse($selectedDate)->format('d/m/Y') . ":*\n\n";
        
        foreach ($availableTimes as $index => $time) {
            $message .= ($index + 1) . ". " . $time . "\n";
        }
        
        $message .= "\n📝 Digite o *número* do horário desejado:";

        $this->updateConversationState($conversation, 'waiting_time', [
            'specialty' => $context['specialty'],
            'doctor' => $context['doctor'],
            'clinic' => $context['clinic'],
            'selected_date' => $selectedDate,
            'available_times' => $availableTimes
        ]);

        return $message;
    }

    /**
     * Processar seleção de horário
     */
    private function handleTimeSelection($conversation, $message, $context)
    {
        $timeIndex = intval($message) - 1;
        $times = $context['available_times'];

        if (!isset($times[$timeIndex])) {
            return "❌ Opção inválida. Por favor, digite o número de um horário válido.";
        }

        $selectedTime = $times[$timeIndex];
        $appointmentDateTime = $context['selected_date'] . ' ' . $selectedTime;

        // Mostrar resumo e solicitar confirmação
        $message = "📋 *Resumo do agendamento:*\n\n";
        $message .= "👨‍⚕️ Médico: Dr(a). " . $context['doctor']['user']['name'] . "\n";
        $message .= "🏥 Especialidade: " . $context['specialty']['name'] . "\n";
        $message .= "📍 Clínica: " . $context['clinic']['name'] . "\n";
        $message .= "📅 Data: " . Carbon::parse($context['selected_date'])->format('d/m/Y') . "\n";
        $message .= "🕐 Horário: " . $selectedTime . "\n\n";
        $message .= "Para confirmar o agendamento, preciso do seu *nome completo*:";

        $this->updateConversationState($conversation, 'waiting_patient_info', [
            'specialty' => $context['specialty'],
            'doctor' => $context['doctor'],
            'clinic' => $context['clinic'],
            'appointment_datetime' => $appointmentDateTime,
            'selected_time' => $selectedTime
        ]);

        return $message;
    }

    /**
     * Processar informações do paciente
     */
    private function handlePatientInfo($conversation, $message, $context)
    {
        $patientName = trim($message);

        if (strlen($patientName) < 3) {
            return "❌ Por favor, digite seu nome completo.";
        }

        // Buscar ou criar usuário paciente
        $patient = User::firstOrCreate(
            ['phone' => $conversation->phone_number],
            [
                'name' => $patientName,
                'email' => null,
                'password' => bcrypt('temp_password'),
                'is_patient' => true
            ]
        );

        // Criar agendamento
        try {
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $context['doctor']['id'],
                'clinic_id' => $context['clinic']['id'],
                'appointment_date' => $context['appointment_datetime'],
                'status' => 'scheduled',
                'notes' => 'Agendamento via WhatsApp'
            ]);

            $message = "✅ *Agendamento confirmado!*\n\n";
            $message .= "📋 Número do agendamento: #" . $appointment->id . "\n";
            $message .= "👤 Paciente: " . $patientName . "\n";
            $message .= "👨‍⚕️ Médico: Dr(a). " . $context['doctor']['user']['name'] . "\n";
            $message .= "📍 Clínica: " . $context['clinic']['name'] . "\n";
            $message .= "📅 Data: " . Carbon::parse($context['appointment_datetime'])->format('d/m/Y H:i') . "\n\n";
            $message .= "🔔 Você receberá um lembrete 24 horas antes da consulta.\n\n";
            $message .= "Para fazer um novo agendamento, digite *agendar consulta*.";

            // Resetar conversa
            $this->updateConversationState($conversation, 'initial', []);

            return $message;

        } catch (\Exception $e) {
            Log::error('Erro ao criar agendamento via WhatsApp', [
                'error' => $e->getMessage(),
                'context' => $context
            ]);

            return "❌ Erro ao confirmar o agendamento. Tente novamente mais tarde.";
        }
    }

    /**
     * Mensagem de boas-vindas
     */
    private function showWelcomeMessage($conversation)
    {
        return "👋 Olá! Bem-vindo ao sistema de agendamento!\n\n" .
               "🏥 Eu posso ajudá-lo a:\n" .
               "• Agendar consultas médicas\n" .
               "• Verificar horários disponíveis\n" .
               "• Confirmar agendamentos\n\n" .
               "Para começar, digite:\n" .
               "*agendar consulta*";
    }

    /**
     * Resetar conversa
     */
    private function resetConversation($conversation)
    {
        $this->updateConversationState($conversation, 'initial', []);
        return $this->showWelcomeMessage($conversation);
    }

    /**
     * Atualizar estado da conversa
     */
    private function updateConversationState($conversation, $state, $context)
    {
        $conversation->update([
            'state' => $state,
            'context' => json_encode($context),
            'updated_at' => now()
        ]);
    }

    /**
     * Buscar datas disponíveis
     */
    private function getAvailableDates($doctorId, $clinicId, $days = 30)
    {
        $dates = [];
        $currentDate = Carbon::now();
        
        for ($i = 1; $i <= $days; $i++) {
            $date = $currentDate->copy()->addDays($i);
            $dayOfWeek = strtolower($date->format('l'));
            
            // Verificar se o médico tem agenda para este dia na clínica
            $schedule = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->first();
                
            if ($schedule) {
                $dates[] = $date->format('Y-m-d');
            }
            
            // Limitar a 10 datas para não sobrecarregar
            if (count($dates) >= 10) {
                break;
            }
        }
        
        return $dates;
    }

    /**
     * Buscar horários disponíveis
     */
    private function getAvailableTimes($doctorId, $clinicId, $date)
    {
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        
        $schedule = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();
            
        if (!$schedule) {
            return [];
        }
        
        // Gerar slots de horário
        $timeSlots = $schedule->generateTimeSlots($date);
        
        // Filtrar horários já ocupados
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('appointment_date')
            ->map(function($datetime) {
                return date('H:i', strtotime($datetime));
            });

        return array_diff($timeSlots, $bookedSlots->toArray());
    }
}