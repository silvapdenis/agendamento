<template>
  <div class="doctor-show">
    <!-- Loading State -->
    <LoadingSpinner 
      v-if="isLoading" 
      title="Carregando informações do médico..."
      message="Buscando dados do profissional"
      size="large"
    />
    
    <!-- Content -->
    <div v-else-if="doctor">
      <div class="mb-6">
        <router-link 
          to="/doctors"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          ← Voltar aos médicos
        </router-link>
      </div>

    <!-- Cabeçalho do Perfil -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex items-start space-x-6">
        <div class="flex-shrink-0">
          <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
            <span class="text-blue-600 font-bold text-2xl">
              {{ getInitials(doctor.user.name) }}
            </span>
          </div>
        </div>
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-gray-900">{{ doctor.user.name }}</h1>
          <p class="text-xl text-gray-600 mt-1">{{ doctor.specialty.name }}</p>
          <div class="flex items-center space-x-4 mt-3 text-sm text-gray-600">
            <span><strong>CRM:</strong> {{ doctor.crm }}/{{ doctor.crm_state }}</span>
            <span><strong>Consulta:</strong> R$ {{ doctor.consultation_price }}</span>
            <span><strong>Duração:</strong> {{ doctor.consultation_duration_minutes }} min</span>
          </div>
        </div>
        <div>
          <router-link 
            :to="`/appointments/create?doctor_id=${doctor.id}`"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium"
          >
            Agendar Consulta
          </router-link>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Informações Principais -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Biografia -->
        <div class="bg-white shadow rounded-lg p-6" v-if="doctor.bio">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Sobre o Profissional</h2>
          <p class="text-gray-700 leading-relaxed">{{ doctor.bio }}</p>
        </div>

        <!-- Horários de Atendimento -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Horários de Atendimento</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-700 mb-2">Dias da Semana</p>
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="day in getAvailableDays(doctor.available_days)"
                  :key="day"
                  class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded"
                >
                  {{ day }}
                </span>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700 mb-2">Horários</p>
              <p class="text-sm text-gray-600">
                {{ doctor.start_time }} às {{ doctor.end_time }}
              </p>
            </div>
          </div>
        </div>

        <!-- Clínicas -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Clínicas de Atendimento</h2>
          <div class="space-y-4">
            <div 
              v-for="clinic in doctor.clinics" 
              :key="clinic.id"
              class="border border-gray-200 rounded-lg p-4"
            >
              <h3 class="font-semibold text-gray-900">{{ clinic.name }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ clinic.address }}, {{ clinic.city }} - {{ clinic.state }}</p>
              <p class="text-sm text-gray-600">{{ clinic.phone }}</p>
              <router-link 
                :to="`/clinics/${clinic.id}`"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block"
              >
                Ver detalhes da clínica
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Informações Rápidas -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Rápidas</h3>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Aceita Convênio:</span>
              <span class="font-medium">
                {{ doctor.accepts_insurance ? 'Sim' : 'Não' }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Especialidade:</span>
              <span class="font-medium">{{ doctor.specialty.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Contato:</span>
              <span class="font-medium">{{ doctor.user.phone || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Convênios Aceitos -->
        <div class="bg-white shadow rounded-lg p-6" v-if="doctor.accepts_insurance && doctor.insurance_plans">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Convênios Aceitos</h3>
          <div class="space-y-2">
            <span 
              v-for="plan in doctor.insurance_plans"
              :key="plan"
              class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1"
            >
              {{ plan }}
            </span>
          </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
          <div class="space-y-3">
            <router-link 
              :to="`/appointments/create?doctor_id=${doctor.id}`"
              class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
            >
              Agendar Consulta
            </router-link>
            <button 
              @click="checkSchedule"
              class="block w-full text-center border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md font-medium"
            >
              Ver Agenda
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="!isLoading && !doctor" class="text-center py-12">
      <p class="text-gray-500">Erro ao carregar informações do médico.</p>
      <button 
        @click="loadDoctor" 
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Tentar Novamente
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useLoading } from '@/composables/useLoading';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import axios from 'axios';

const route = useRoute();
const { isLoading, withLoading } = useLoading();
const doctor = ref(null);

const loadDoctor = async () => {
  await withLoading(async () => {
    try {
      const response = await axios.get(`/api/doctors/${route.params.id}`);
      doctor.value = response.data.data;
    } catch (error) {
      console.error('Erro ao carregar médico:', error);
      doctor.value = null;
    }
  });
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getAvailableDays = (days) => {
  if (!days) return [];
  
  const dayNames = {
    1: 'Segunda',
    2: 'Terça', 
    3: 'Quarta',
    4: 'Quinta',
    5: 'Sexta',
    6: 'Sábado',
    7: 'Domingo'
  };

  return days.map(day => dayNames[day] || day);
};

const checkSchedule = () => {
  // Implementar visualização da agenda
  alert('Funcionalidade de visualização da agenda em desenvolvimento.');
};

onMounted(() => {
  loadDoctor();
});
</script>