<template>
  <div class="doctor-schedule max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <!-- Loading State -->
    <LoadingSpinner 
      v-if="isLoading" 
      title="Carregando agenda..."
      message="Buscando horários configurados"
      size="large"
    />
    
    <!-- Content -->
    <div v-else>
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Gerenciar Agenda</h1>
            <p class="text-gray-600 mt-1">Configure seus horários de atendimento por clínica</p>
          </div>
          <button
            @click="showCreateModal = true"
            :disabled="doctorClinics.length === 0"
            :class="doctorClinics.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
            class="text-white px-4 py-2 rounded-md font-medium flex items-center"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nova Agenda
          </button>
        </div>
        
        <!-- Mensagem de Sucesso -->
        <div 
          v-if="showSuccess"
          class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
        >
          <span class="block sm:inline">{{ successMessage }}</span>
        </div>
      </div>

      <!-- Clínicas do Médico -->
      <div class="space-y-6">
        <div v-for="clinic in doctorClinics" :key="clinic.id" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-medium text-gray-900">{{ clinic.name }}</h3>
                <p class="text-sm text-gray-600">{{ clinic.address }}, {{ clinic.city }}</p>
              </div>
              <button
                @click="openScheduleModal(clinic.id)"
                class="text-blue-600 hover:text-blue-800 font-medium"
              >
                Configurar Horários
              </button>
            </div>
          </div>

          <!-- Agenda Atual -->
          <div class="p-6">
            <div v-if="clinicSchedules[clinic.id] && Object.keys(clinicSchedules[clinic.id]).length > 0">
              <h4 class="text-sm font-medium text-gray-900 mb-4">Horários Configurados:</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div 
                  v-for="(schedule, day) in clinicSchedules[clinic.id]" 
                  :key="day"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h5 class="font-medium text-gray-900">{{ getDayName(day) }}</h5>
                    <button
                      @click="deleteSchedule(schedule.doctor_id, clinic.id, day)"
                      class="text-red-600 hover:text-red-800 text-sm"
                    >
                      Remover
                    </button>
                  </div>
                  <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Horário:</strong> {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}</p>
                    <p><strong>Duração:</strong> {{ schedule.slot_duration_minutes }} min</p>
                    <p v-if="schedule.break_duration_minutes > 0"><strong>Intervalo:</strong> {{ schedule.break_duration_minutes }} min</p>
                    <p v-if="schedule.lunch_start && schedule.lunch_end">
                      <strong>Almoço:</strong> {{ formatTime(schedule.lunch_start) }} - {{ formatTime(schedule.lunch_end) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <p>Nenhum horário configurado para esta clínica</p>
              <button
                @click="openScheduleModal(clinic.id)"
                class="mt-2 text-blue-600 hover:text-blue-800 font-medium"
              >
                Configurar agora
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Criação de Nova Agenda -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Nova Agenda</h3>
              <button
                @click="closeCreateModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <form @submit.prevent="createNewSchedule" class="p-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Selecione a clínica para configurar a agenda:
                </label>
                
                <div v-if="availableClinicsForNew.length === 0" class="text-center py-8">
                  <div class="text-gray-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <p class="text-gray-500 text-lg font-medium">Todas as clínicas já possuem agenda!</p>
                  <p class="text-gray-400 text-sm mt-2">
                    Todas as clínicas onde você atende já têm horários configurados. 
                    Use "Configurar Horários" para editar agendas existentes.
                  </p>
                </div>
                
                <select
                  v-else
                  v-model="selectedClinicForNew"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Escolha uma clínica</option>
                  <option 
                    v-for="clinic in availableClinicsForNew" 
                    :key="clinic.id" 
                    :value="clinic.id"
                  >
                    {{ clinic.name }}
                  </option>
                </select>
              </div>

              <div v-if="availableClinicsForNew.length > 0" class="bg-blue-50 p-4 rounded-lg">
                <div class="flex">
                  <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                  <div class="ml-3 text-sm text-blue-700">
                    <p>Após selecionar a clínica, você poderá configurar os horários de atendimento para cada dia da semana.</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ availableClinicsForNew.length === 0 ? 'Fechar' : 'Cancelar' }}
              </button>
              <button
                v-if="availableClinicsForNew.length > 0"
                type="submit"
                :disabled="!selectedClinicForNew"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Continuar
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal de Configuração de Agenda -->
      <div v-if="showScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-medium text-gray-900">
                  {{ isNewSchedule ? 'Nova Agenda' : 'Editar Agenda' }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                  {{ getSelectedClinicName() }}
                </p>
              </div>
              <button
                @click="closeScheduleModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <form @submit.prevent="saveSchedule" class="p-6">
            <!-- Dica para nova agenda -->
            <div v-if="isNewSchedule" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex">
                <svg class="w-5 h-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3 text-sm text-green-700">
                  <p class="font-medium">Template padrão aplicado!</p>
                  <p class="mt-1">Configuramos automaticamente Segunda a Sexta das 8h às 18h com horário de almoço das 12h às 13h. Ajuste conforme necessário.</p>
                </div>
              </div>
            </div>
            
            <div class="space-y-6">
              <!-- Dias da Semana -->
              <div v-for="(dayData, day) in scheduleForm" :key="day" class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="dayData.active"
                      class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="font-medium text-gray-900">{{ getDayName(day) }}</span>
                  </label>
                </div>

                <div v-if="dayData.active" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Início</label>
                    <input
                      type="time"
                      v-model="dayData.start_time"
                      required
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fim</label>
                    <input
                      type="time"
                      v-model="dayData.end_time"
                      required
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duração da consulta (min)</label>
                    <select
                      v-model="dayData.slot_duration_minutes"
                      required
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="30">30 minutos</option>
                      <option value="45">45 minutos</option>
                      <option value="60">60 minutos</option>
                      <option value="90">90 minutos</option>
                      <option value="120">120 minutos</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo entre consultas (min)</label>
                    <select
                      v-model="dayData.break_duration_minutes"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="0">Sem intervalo</option>
                      <option value="5">5 minutos</option>
                      <option value="10">10 minutos</option>
                      <option value="15">15 minutos</option>
                      <option value="30">30 minutos</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Início do almoço</label>
                    <input
                      type="time"
                      v-model="dayData.lunch_start"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fim do almoço</label>
                    <input
                      type="time"
                      v-model="dayData.lunch_end"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
              <button
                type="button"
                @click="closeScheduleModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="isSaving"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center"
              >
                <svg v-if="isSaving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSaving ? 'Salvando...' : 'Salvar Agenda' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useLoading } from '@/composables/useLoading';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { isLoading, withLoading } = useLoading();

const doctorClinics = ref([]);
const clinicSchedules = ref({});
const showScheduleModal = ref(false);
const showCreateModal = ref(false);
const selectedClinicId = ref(null);
const selectedClinicForNew = ref('');
const isSaving = ref(false);
const successMessage = ref('');
const showSuccess = ref(false);

const scheduleForm = ref({
  monday: { active: false, start_time: '08:00', end_time: '18:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' },
  tuesday: { active: false, start_time: '08:00', end_time: '18:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' },
  wednesday: { active: false, start_time: '08:00', end_time: '18:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' },
  thursday: { active: false, start_time: '08:00', end_time: '18:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' },
  friday: { active: false, start_time: '08:00', end_time: '18:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' },
  saturday: { active: false, start_time: '08:00', end_time: '12:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' },
  sunday: { active: false, start_time: '08:00', end_time: '12:00', slot_duration_minutes: 60, break_duration_minutes: 0, lunch_start: '', lunch_end: '' }
});

const currentDoctor = computed(() => {
  return authStore.user?.doctor;
});

const availableClinicsForNew = computed(() => {
  return doctorClinics.value.filter(clinic => {
    // Mostrar clínicas que não têm agenda configurada ainda
    const hasSchedule = clinicSchedules.value[clinic.id] && 
                       Object.keys(clinicSchedules.value[clinic.id]).length > 0;
    return !hasSchedule;
  });
});

const isNewSchedule = computed(() => {
  if (!selectedClinicId.value) return false;
  const existingSchedule = clinicSchedules.value[selectedClinicId.value];
  return !existingSchedule || Object.keys(existingSchedule).length === 0;
});

const getSelectedClinicName = () => {
  if (!selectedClinicId.value) return '';
  const clinic = doctorClinics.value.find(c => c.id === selectedClinicId.value);
  return clinic ? clinic.name : '';
};

const loadDoctorClinics = async () => {
  if (!currentDoctor.value) return;
  
  await withLoading(async () => {
    try {
      const response = await axios.get(`/api/doctors/${currentDoctor.value.id}`);
      doctorClinics.value = response.data.data.clinics || [];
      
      // Carregar agendas para cada clínica
      for (const clinic of doctorClinics.value) {
        await loadClinicSchedule(clinic.id);
      }
    } catch (error) {
      console.error('Erro ao carregar clínicas:', error);
    }
  });
};

const loadClinicSchedule = async (clinicId) => {
  if (!currentDoctor.value) return;
  
  try {
    const response = await axios.get(`/api/doctors/${currentDoctor.value.id}/clinics/${clinicId}/schedules`);
    clinicSchedules.value[clinicId] = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar agenda da clínica:', error);
    clinicSchedules.value[clinicId] = {};
  }
};

const openScheduleModal = async (clinicId) => {
  selectedClinicId.value = clinicId;
  
  // Resetar formulário
  Object.keys(scheduleForm.value).forEach(day => {
    scheduleForm.value[day].active = false;
  });
  
  // Carregar agenda existente se houver
  const existingSchedule = clinicSchedules.value[clinicId];
  const hasExistingSchedule = existingSchedule && Object.keys(existingSchedule).length > 0;
  
  if (hasExistingSchedule) {
    // Carregar agenda existente
    Object.keys(existingSchedule).forEach(day => {
      if (scheduleForm.value[day]) {
        const schedule = existingSchedule[day];
        scheduleForm.value[day] = {
          active: true,
          start_time: formatTimeForInput(schedule.start_time),
          end_time: formatTimeForInput(schedule.end_time),
          slot_duration_minutes: schedule.slot_duration_minutes,
          break_duration_minutes: schedule.break_duration_minutes,
          lunch_start: schedule.lunch_start ? formatTimeForInput(schedule.lunch_start) : '',
          lunch_end: schedule.lunch_end ? formatTimeForInput(schedule.lunch_end) : ''
        };
      }
    });
  } else {
    // Nova agenda: aplicar template padrão
    const workDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    workDays.forEach(day => {
      scheduleForm.value[day] = {
        active: true,
        start_time: '08:00',
        end_time: '18:00',
        slot_duration_minutes: 60,
        break_duration_minutes: 0,
        lunch_start: '12:00',
        lunch_end: '13:00'
      };
    });
    
    // Sábado com horário reduzido
    scheduleForm.value.saturday = {
      active: false, // Deixar desmarcado por padrão
      start_time: '08:00',
      end_time: '12:00',
      slot_duration_minutes: 60,
      break_duration_minutes: 0,
      lunch_start: '',
      lunch_end: ''
    };
  }
  
  showScheduleModal.value = true;
};

const closeScheduleModal = () => {
  showScheduleModal.value = false;
  selectedClinicId.value = null;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  selectedClinicForNew.value = '';
};

const createNewSchedule = () => {
  if (!selectedClinicForNew.value) return;
  
  // Salvar o valor antes de fechar o modal
  const clinicId = selectedClinicForNew.value;
  
  // Fechar modal de criação e abrir modal de configuração
  closeCreateModal();
  openScheduleModal(clinicId);
};

const saveSchedule = async () => {
  if (!currentDoctor.value || !selectedClinicId.value) return;
  
  isSaving.value = true;
  
  try {
    const schedulesToSave = [];
    const daysToDelete = [];
    
    Object.keys(scheduleForm.value).forEach(day => {
      const dayData = scheduleForm.value[day];
      
      if (dayData.active) {
        schedulesToSave.push({
          day_of_week: day,
          start_time: dayData.start_time,
          end_time: dayData.end_time,
          slot_duration_minutes: parseInt(dayData.slot_duration_minutes),
          break_duration_minutes: parseInt(dayData.break_duration_minutes),
          lunch_start: dayData.lunch_start || null,
          lunch_end: dayData.lunch_end || null,
          is_active: true
        });
      } else {
        // Se o dia foi desmarcado, adicionar à lista de remoção
        // Mas só se existir agenda para este dia
        const existingSchedule = clinicSchedules.value[selectedClinicId.value];
        if (existingSchedule && existingSchedule[day]) {
          daysToDelete.push(day);
        }
      }
    });
    
    // Primeiro, remover os dias desmarcados
    for (const day of daysToDelete) {
      try {
        await axios.delete(`/api/doctors/${currentDoctor.value.id}/clinics/${selectedClinicId.value}/schedules/${day}`);
      } catch (error) {
        console.error(`Erro ao remover agenda do ${day}:`, error);
      }
    }
    
    // Depois, salvar/atualizar os dias marcados
    if (schedulesToSave.length > 0) {
      await axios.post('/api/doctor-schedules', {
        doctor_id: currentDoctor.value.id,
        clinic_id: selectedClinicId.value,
        schedules: schedulesToSave
      });
    }
    
    // Recarregar agenda da clínica
    await loadClinicSchedule(selectedClinicId.value);
    
    // Mostrar mensagem de sucesso
    successMessage.value = 'Agenda atualizada com sucesso!';
    showSuccess.value = true;
    setTimeout(() => {
      showSuccess.value = false;
    }, 3000);
    
    closeScheduleModal();
  } catch (error) {
    console.error('Erro ao salvar agenda:', error);
    alert('Erro ao salvar agenda. Tente novamente.');
  } finally {
    isSaving.value = false;
  }
};

const deleteSchedule = async (doctorId, clinicId, day) => {
  if (!confirm('Tem certeza que deseja remover este horário?')) return;
  
  try {
    await axios.delete(`/api/doctors/${doctorId}/clinics/${clinicId}/schedules/${day}`);
    await loadClinicSchedule(clinicId);
    
    // Mostrar mensagem de sucesso
    successMessage.value = `Horário de ${getDayName(day)} removido com sucesso!`;
    showSuccess.value = true;
    setTimeout(() => {
      showSuccess.value = false;
    }, 3000);
  } catch (error) {
    console.error('Erro ao remover agenda:', error);
    alert('Erro ao remover agenda. Tente novamente.');
  }
};

const getDayName = (day) => {
  const days = {
    monday: 'Segunda-feira',
    tuesday: 'Terça-feira', 
    wednesday: 'Quarta-feira',
    thursday: 'Quinta-feira',
    friday: 'Sexta-feira',
    saturday: 'Sábado',
    sunday: 'Domingo'
  };
  return days[day] || day;
};

const formatTime = (time) => {
  if (!time) return '';
  if (typeof time === 'string' && time.includes(':')) {
    return time.substring(0, 5); // Pega apenas HH:MM
  }
  return time;
};

const formatTimeForInput = (time) => {
  if (!time) return '';
  if (typeof time === 'string' && time.includes(':')) {
    return time.substring(0, 5); // Pega apenas HH:MM
  }
  return time;
};

onMounted(() => {
  if (authStore.user?.user_type !== 'doctor') {
    alert('Acesso negado. Apenas médicos podem acessar esta página.');
    return;
  }
  
  loadDoctorClinics();
});
</script>