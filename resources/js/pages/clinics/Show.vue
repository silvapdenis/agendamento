<template>
  <div class="clinic-show">
    <!-- Loading State -->
    <LoadingSpinner 
      v-if="isLoading" 
      title="Carregando informações da clínica..."
      message="Buscando dados e médicos associados"
      size="large"
    />
    
    <!-- Content -->
    <div v-else-if="clinic">
      <div class="mb-6">
        <router-link 
          to="/clinics"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          ← Voltar às clínicas
        </router-link>
      </div>

    <!-- Cabeçalho da Clínica -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ clinic.name }}</h1>
          <div class="flex items-center space-x-4 mb-4">
            <span 
              :class="getPlanClass(clinic.subscription_plan)"
              class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
            >
              {{ getPlanText(clinic.subscription_plan) }}
            </span>
            <span 
              :class="clinic.is_active ? 'text-green-600' : 'text-red-600'"
              class="text-sm font-medium"
            >
              {{ clinic.is_active ? '✓ Ativo' : '✗ Inativo' }}
            </span>
          </div>
          <p class="text-gray-600" v-if="clinic.description">{{ clinic.description }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Informações Principais -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Informações de Contato -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Informações de Contato</h2>
          <div class="space-y-3">
            <div class="flex items-start">
              <svg class="w-5 h-5 mr-3 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
              <div>
                <p class="font-medium text-gray-900">Endereço</p>
                <p class="text-gray-600">{{ clinic.address }}</p>
                <p class="text-gray-600">{{ clinic.city }} - {{ clinic.state }}</p>
                <p class="text-gray-600">CEP: {{ clinic.zip_code }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
              </svg>
              <div>
                <p class="font-medium text-gray-900">Telefone</p>
                <p class="text-gray-600">{{ clinic.phone }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
              </svg>
              <div>
                <p class="font-medium text-gray-900">E-mail</p>
                <p class="text-gray-600">{{ clinic.email }}</p>
              </div>
            </div>

            <div class="flex items-center" v-if="clinic.cnpj">
              <svg class="w-5 h-5 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 2a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1H8a1 1 0 01-1-1V6zm7 5a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-1-1a1 1 0 100-2 1 1 0 000 2zm-6 1a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-1-1a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
              </svg>
              <div>
                <p class="font-medium text-gray-900">CNPJ</p>
                <p class="text-gray-600">{{ clinic.cnpj }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Horários de Funcionamento -->
        <div class="bg-white shadow rounded-lg p-6" v-if="clinic.business_hours">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Horários de Funcionamento</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="(hours, day) in getBusinessHours(clinic.business_hours)" 
              :key="day"
              class="flex justify-between items-center p-3 border border-gray-200 rounded-lg"
            >
              <span class="font-medium text-gray-900">{{ getDayName(day) }}</span>
              <span class="text-gray-600">
                {{ hours ? (Array.isArray(hours) ? hours.join(' às ') : hours) : 'Fechado' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Médicos da Clínica -->
        <div class="bg-white shadow rounded-lg p-6" v-if="doctors.length > 0">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Médicos da Clínica</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="doctor in doctors" 
              :key="doctor.id"
              class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 font-semibold text-sm">
                    {{ getInitials(doctor.user.name) }}
                  </span>
                </div>
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">{{ doctor.user.name }}</h3>
                  <p class="text-sm text-gray-600">{{ doctor.specialty.name }}</p>
                </div>
              </div>
              <div class="mt-3 text-sm text-gray-600">
                <p><strong>CRM:</strong> {{ doctor.crm }}/{{ doctor.crm_state }}</p>
                <p><strong>Consulta:</strong> R$ {{ doctor.consultation_price }}</p>
              </div>
              <div class="mt-3 flex space-x-2">
                <router-link 
                  :to="`/doctors/${doctor.id}`"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Ver Perfil
                </router-link>
                <router-link 
                  :to="`/appointments/create?doctor_id=${doctor.id}&clinic_id=${clinic.id}`"
                  class="text-green-600 hover:text-green-800 text-sm font-medium"
                >
                  Agendar
                </router-link>
              </div>
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
              <span class="text-gray-600">Plano:</span>
              <span class="font-medium">{{ getPlanText(clinic.subscription_plan) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Status:</span>
              <span 
                :class="clinic.is_active ? 'text-green-600' : 'text-red-600'"
                class="font-medium"
              >
                {{ clinic.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
            <div class="flex justify-between" v-if="clinic.subscription_expires_at">
              <span class="text-gray-600">Assinatura até:</span>
              <span class="font-medium">{{ formatDate(clinic.subscription_expires_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
          <div class="space-y-3">
            <router-link 
              :to="`/appointments/create?clinic_id=${clinic.id}`"
              class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
            >
              Agendar Consulta
            </router-link>
            <button 
              @click="showDirections"
              class="block w-full text-center border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md font-medium"
            >
              Como Chegar
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="!isLoading && !clinic" class="text-center py-12">
      <p class="text-gray-500">Erro ao carregar informações da clínica.</p>
      <button 
        @click="loadClinic" 
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
const clinic = ref(null);
const doctors = ref([]);

const loadClinic = async () => {
  await withLoading(async () => {
    try {
      const response = await axios.get(`/api/clinics/${route.params.id}`);
      clinic.value = response.data.data;
    } catch (error) {
      console.error('Erro ao carregar clínica:', error);
      clinic.value = null;
    }
  });
};

const loadDoctors = async () => {
  try {
    const response = await axios.get(`/api/clinics/${route.params.id}/doctors`);
    doctors.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao carregar médicos:', error);
    doctors.value = [];
  }
};

const getPlanClass = (plan) => {
  const classes = {
    'basic': 'bg-gray-100 text-gray-800',
    'premium': 'bg-blue-100 text-blue-800',
    'enterprise': 'bg-purple-100 text-purple-800'
  };
  return classes[plan] || 'bg-gray-100 text-gray-800';
};

const getPlanText = (plan) => {
  const texts = {
    'basic': 'Básico',
    'premium': 'Premium',
    'enterprise': 'Empresarial'
  };
  return texts[plan] || 'Desconhecido';
};

const getBusinessHours = (hours) => {
  if (!hours) return {};
  
  if (typeof hours === 'string') {
    try {
      return JSON.parse(hours);
    } catch {
      return {};
    }
  }
  
  return hours;
};

const getDayName = (day) => {
  const days = {
    'monday': 'Segunda-feira',
    'tuesday': 'Terça-feira',
    'wednesday': 'Quarta-feira',
    'thursday': 'Quinta-feira',
    'friday': 'Sexta-feira',
    'saturday': 'Sábado',
    'sunday': 'Domingo'
  };
  return days[day] || day;
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const showDirections = () => {
  const address = `${clinic.value.address}, ${clinic.value.city} - ${clinic.value.state}`;
  const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
  window.open(mapsUrl, '_blank');
};

onMounted(async () => {
  await Promise.all([
    loadClinic(),
    loadDoctors()
  ]);
});
</script>