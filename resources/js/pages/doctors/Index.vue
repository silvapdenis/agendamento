<template>
  <div class="doctors-index">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Médicos</h1>
      <p class="text-gray-600 mt-1">Encontre o profissional ideal para sua consulta</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Especialidade
          </label>
          <select 
            v-model="filters.specialty_id"
            @change="loadDoctors"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todas as especialidades</option>
            <option 
              v-for="specialty in specialties" 
              :key="specialty.id" 
              :value="specialty.id"
            >
              {{ specialty.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Clínica
          </label>
          <select 
            v-model="filters.clinic_id"
            @change="loadDoctors"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todas as clínicas</option>
            <option 
              v-for="clinic in clinics" 
              :key="clinic.id" 
              :value="clinic.id"
            >
              {{ clinic.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Buscar por nome
          </label>
          <input 
            type="text"
            v-model="filters.search"
            @input="debounceSearch"
            placeholder="Digite o nome do médico..."
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
      </div>
    </div>

    <!-- Lista de Médicos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" v-if="doctors.length > 0">
      <div 
        v-for="doctor in doctors" 
        :key="doctor.id"
        class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <div class="flex items-center space-x-3 mb-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 font-semibold text-lg">
                  {{ getInitials(doctor.user.name) }}
                </span>
              </div>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">
                {{ doctor.user.name }}
              </h3>
              <p class="text-sm text-gray-600">
                {{ doctor.specialty.name }}
              </p>
            </div>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <p><strong>CRM:</strong> {{ doctor.crm }}/{{ doctor.crm_state }}</p>
            <p><strong>Valor da Consulta:</strong> R$ {{ doctor.consultation_price }}</p>
            <p><strong>Duração:</strong> {{ doctor.consultation_duration_minutes }} min</p>
          </div>

          <p class="text-sm text-gray-700 mb-4" v-if="doctor.bio">
            {{ truncateText(doctor.bio, 100) }}
          </p>

          <div class="flex justify-between items-center">
            <div class="flex flex-wrap gap-1">
              <span 
                v-for="clinic in doctor.clinics" 
                :key="clinic.id"
                class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded"
              >
                {{ clinic.name }}
              </span>
            </div>
            <router-link 
              :to="`/doctors/${doctor.id}`"
              class="text-blue-600 hover:text-blue-800 font-medium text-sm"
            >
              Ver Perfil
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!loading" class="text-center py-12">
      <p class="text-gray-500">Nenhum médico encontrado com os filtros selecionados.</p>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Carregando médicos...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const doctors = ref([]);
const specialties = ref([]);
const clinics = ref([]);

const filters = ref({
  specialty_id: '',
  clinic_id: '',
  search: ''
});

let searchTimeout;

const loadDoctors = async () => {
  try {
    loading.value = true;
    
    // Filtrar parâmetros vazios antes de enviar
    const cleanFilters = {};
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key] && filters.value[key] !== '') {
        cleanFilters[key] = filters.value[key];
      }
    });
    
    console.log('Enviando filtros:', cleanFilters);
    const response = await axios.get('/api/doctors', {
      params: cleanFilters
    });
    console.log('Resposta da API:', response.data);
    doctors.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao carregar médicos:', error);
  } finally {
    loading.value = false;
  }
};

const loadSpecialties = async () => {
  try {
    const response = await axios.get('/api/specialties');
    specialties.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao carregar especialidades:', error);
  }
};

const loadClinics = async () => {
  try {
    const response = await axios.get('/api/clinics');
    clinics.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao carregar clínicas:', error);
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadDoctors();
  }, 500);
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const truncateText = (text, maxLength) => {
  if (text.length <= maxLength) return text;
  return text.substr(0, maxLength) + '...';
};

onMounted(async () => {
  await Promise.all([
    loadSpecialties(),
    loadClinics(),
    loadDoctors()
  ]);
});
</script>