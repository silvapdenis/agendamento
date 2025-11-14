<template>
  <div class="appointment-create">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Novo Agendamento</h1>
      <p class="text-gray-600 mt-1">Agende sua consulta médica</p>
    </div>

    <form @submit.prevent="submitForm" class="bg-white shadow rounded-lg p-6">
      <!-- Loading State -->
      <LoadingSpinner 
        v-if="isLoading" 
        :title="loadingTitle"
        :message="loadingMessage"
        size="medium"
      />
      
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Seleção de Clínica -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Clínica *
          </label>
          <select 
            v-model="form.clinic_id" 
            @change="loadDoctors"
            required
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Selecione uma clínica</option>
            <option 
              v-for="clinic in clinics" 
              :key="clinic.id" 
              :value="clinic.id"
            >
              {{ clinic.name }}
            </option>
          </select>
        </div>

        <!-- Seleção de Médico -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Médico *
          </label>
          <select 
            v-model="form.doctor_id"
            @change="loadAvailableSlots"
            required
            :disabled="!form.clinic_id"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
          >
            <option value="">Selecione um médico</option>
            <option 
              v-for="doctor in doctors" 
              :key="doctor.id" 
              :value="doctor.id"
            >
              {{ doctor.user?.name || 'Nome não disponível' }} - {{ doctor.specialty?.name || 'Especialidade não disponível' }}
            </option>
          </select>
        </div>

        <!-- Data do Agendamento -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Data *
          </label>
          <input 
            type="date"
            v-model="form.date"
            @change="loadAvailableSlots"
            required
            :min="minDate"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>

        <!-- Horário -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Horário *
          </label>
          <select 
            v-model="form.time"
            required
            :disabled="!availableSlots.length"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
          >
            <option value="">Selecione um horário</option>
            <option 
              v-for="slot in availableSlots" 
              :key="slot" 
              :value="slot"
            >
              {{ slot }}
            </option>
          </select>
        </div>

        <!-- Observações -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Observações
          </label>
          <textarea 
            v-model="form.notes"
            rows="3"
            placeholder="Descreva seus sintomas ou motivo da consulta..."
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
        </div>
      </div>

      <div class="mt-6 flex justify-end space-x-3">
        <router-link 
          to="/appointments"
          class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Cancelar
        </router-link>
        <button 
          type="submit"
          :disabled="isLoading"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center space-x-2"
        >
          <div v-if="isLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <span>{{ isLoading ? 'Agendando...' : 'Agendar Consulta' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useLoading } from '@/composables/useLoading';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import axios from 'axios';

const router = useRouter();
const { isLoading, loadingTitle, loadingMessage, withLoading, startLoading, stopLoading } = useLoading();

const clinics = ref([]);
const doctors = ref([]);
const availableSlots = ref([]);

const form = ref({
  clinic_id: '',
  doctor_id: '',
  date: '',
  time: '',
  notes: ''
});

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const loadClinics = async () => {
  await withLoading(async () => {
    const response = await axios.get('/api/clinics');
    clinics.value = response.data.data || [];
    console.log('Clínicas carregadas:', clinics.value);
  }, 'Carregando clínicas...', 'Aguarde enquanto buscamos as clínicas disponíveis');
};

const loadDoctors = async () => {
  if (!form.value.clinic_id) {
    doctors.value = [];
    return;
  }

  await withLoading(async () => {
    const response = await axios.get(`/api/clinics/${form.value.clinic_id}/doctors`);
    doctors.value = response.data.data || [];
    form.value.doctor_id = '';
    availableSlots.value = [];
    console.log('Médicos carregados:', doctors.value);
  }, 'Carregando médicos...', 'Buscando médicos da clínica selecionada');
};

const loadAvailableSlots = async () => {
  if (!form.value.doctor_id || !form.value.date || !form.value.clinic_id) {
    availableSlots.value = [];
    return;
  }

  await withLoading(async () => {
    const response = await axios.get(`/api/doctors/${form.value.doctor_id}/clinics/${form.value.clinic_id}/available-slots`, {
      params: {
        date: form.value.date
      }
    });
    availableSlots.value = response.data.data || [];
    form.value.time = '';
    console.log('Horários disponíveis:', availableSlots.value);
  }, 'Verificando disponibilidade...', 'Buscando horários livres para a data selecionada');
};

const submitForm = async () => {
  await withLoading(async () => {
    const appointmentData = {
      doctor_id: form.value.doctor_id,
      clinic_id: form.value.clinic_id,
      appointment_date: `${form.value.date} ${form.value.time}`,
      notes: form.value.notes
    };

    await axios.post('/api/appointments', appointmentData);
    router.push('/appointments');
  }, 'Agendando consulta...', 'Criando seu agendamento, aguarde um momento');
};

onMounted(() => {
  loadClinics();
});
</script>