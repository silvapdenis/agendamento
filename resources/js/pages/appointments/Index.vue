<template>
  <div class="appointments-index">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Meus Agendamentos</h1>
      <router-link 
        to="/appointments/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
      >
        Novo Agendamento
      </router-link>
    </div>

    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Lista de Agendamentos</h2>
      </div>
      
      <!-- Loading State -->
      <LoadingSpinner 
        v-if="isLoading" 
        title="Carregando agendamentos..."
        message="Buscando seus agendamentos..."
        size="medium"
      />
      
      <div v-else-if="appointments.length > 0" class="divide-y divide-gray-200">
        <div 
          v-for="appointment in appointments" 
          :key="appointment.id"
          class="p-6 hover:bg-gray-50"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-2">
                <h3 class="text-lg font-medium text-gray-900">
                  Dr(a). {{ appointment.doctor?.user?.name }}
                </h3>
                <span 
                  :class="getStatusClass(appointment.status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ getStatusText(appointment.status) }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mt-1">
                {{ appointment.clinic?.name }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                {{ formatDate(appointment.appointment_date) }}
              </p>
              <p class="text-sm text-gray-700 mt-2" v-if="appointment.notes">
                {{ appointment.notes }}
              </p>
            </div>
            <div class="flex items-center space-x-2">
              <span class="text-lg font-semibold text-green-600">
                R$ {{ appointment.price }}
              </span>
              <router-link 
                :to="`/appointments/${appointment.id}`"
                class="text-blue-600 hover:text-blue-800 font-medium"
              >
                Ver Detalhes
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!isLoading" class="p-6 text-center">
        <p class="text-gray-500">Nenhum agendamento encontrado.</p>
        <router-link 
          to="/appointments/create"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          Criar meu primeiro agendamento
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useLoading } from '@/composables/useLoading';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { isLoading, withLoading } = useLoading();
const appointments = ref([]);

const loadAppointments = async () => {
  await withLoading(async () => {
    const response = await axios.get('/api/appointments');
    appointments.value = response.data.data || [];
  }, 'Carregando agendamentos...', 'Buscando seus agendamentos...');
};

const getStatusClass = (status) => {
  const classes = {
    'scheduled': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800', 
    'in_progress': 'bg-orange-100 text-orange-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'no_show': 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
  const texts = {
    'scheduled': 'Agendado',
    'confirmed': 'Confirmado',
    'in_progress': 'Em Andamento', 
    'completed': 'Concluído',
    'cancelled': 'Cancelado',
    'no_show': 'Faltou'
  };
  return texts[status] || 'Desconhecido';
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  loadAppointments();
});
</script>