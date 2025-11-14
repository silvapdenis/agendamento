<template>
  <div class="clinics-index">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Clínicas</h1>
      <p class="text-gray-600 mt-1">Encontre a clínica mais próxima de você</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Buscar por nome ou localização
          </label>
          <input 
            type="text"
            v-model="filters.search"
            @input="debounceSearch"
            placeholder="Digite o nome da clínica ou cidade..."
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Plano de assinatura
          </label>
          <select 
            v-model="filters.subscription_plan"
            @change="loadClinics"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os planos</option>
            <option value="basic">Básico</option>
            <option value="premium">Premium</option>
            <option value="enterprise">Empresarial</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Lista de Clínicas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="clinics.length > 0">
      <div 
        v-for="clinic in clinics" 
        :key="clinic.id"
        class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                {{ clinic.name }}
              </h3>
              <span 
                :class="getPlanClass(clinic.subscription_plan)"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ getPlanText(clinic.subscription_plan) }}
              </span>
            </div>
            <div class="text-right">
              <span 
                :class="clinic.is_active ? 'text-green-600' : 'text-red-600'"
                class="text-sm font-medium"
              >
                {{ clinic.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <p class="flex items-start">
              <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
              {{ clinic.address }}, {{ clinic.city }} - {{ clinic.state }}
            </p>
            <p class="flex items-center">
              <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
              </svg>
              {{ clinic.phone }}
            </p>
            <p class="flex items-center">
              <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
              </svg>
              {{ clinic.email }}
            </p>
          </div>

          <div class="mb-4" v-if="clinic.description">
            <p class="text-sm text-gray-700">
              {{ truncateText(clinic.description, 120) }}
            </p>
          </div>

          <!-- Horários de Funcionamento -->
          <div class="mb-4" v-if="clinic.business_hours">
            <p class="text-xs font-medium text-gray-700 mb-2">Horários de Funcionamento:</p>
            <div class="grid grid-cols-2 gap-1 text-xs text-gray-600">
              <div v-for="(hours, day) in getBusinessHours(clinic.business_hours)" :key="day">
                <span class="font-medium">{{ getDayName(day) }}:</span>
                <span class="ml-1">{{ hours || 'Fechado' }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-between items-center pt-4 border-t">
            <div class="text-sm text-gray-600">
              <span class="font-medium">CNPJ:</span> {{ clinic.cnpj || 'N/A' }}
            </div>
            <router-link 
              :to="`/clinics/${clinic.id}`"
              class="text-blue-600 hover:text-blue-800 font-medium text-sm"
            >
              Ver Detalhes
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!loading" class="text-center py-12">
      <p class="text-gray-500">Nenhuma clínica encontrada com os filtros selecionados.</p>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Carregando clínicas...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const clinics = ref([]);

const filters = ref({
  search: '',
  subscription_plan: ''
});

let searchTimeout;

const loadClinics = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/clinics', {
      params: filters.value
    });
    clinics.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao carregar clínicas:', error);
  } finally {
    loading.value = false;
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadClinics();
  }, 500);
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

const truncateText = (text, maxLength) => {
  if (!text || text.length <= maxLength) return text;
  return text.substr(0, maxLength) + '...';
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
    'monday': 'Seg',
    'tuesday': 'Ter',
    'wednesday': 'Qua',
    'thursday': 'Qui',
    'friday': 'Sex',
    'saturday': 'Sáb',
    'sunday': 'Dom'
  };
  return days[day] || day;
};

onMounted(() => {
  loadClinics();
});
</script>