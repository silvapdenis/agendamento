<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          Olá, {{ authStore.user?.name }}!
        </h1>
        <p class="mt-2 text-gray-600">
          Bem-vindo ao seu painel de controle
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Appointments -->
        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v-4h16v4a4 4 0 11-8 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 truncate">
                  Total de Consultas
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ stats.totalAppointments }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 truncate">
                  Próximas Consultas
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ stats.upcomingAppointments }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Completed Appointments -->
        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 truncate">
                  Consultas Realizadas
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ stats.completedAppointments }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Cancelled Appointments -->
        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 truncate">
                  Consultas Canceladas
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ stats.cancelledAppointments }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Appointments -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">
              Próximas Consultas
            </h3>
          </div>
          <div class="card-body">
            <div v-if="upcomingAppointments.length === 0" class="text-center py-6 text-gray-500">
              Nenhuma consulta agendada
            </div>
            <div v-else class="space-y-4">
              <div 
                v-for="appointment in upcomingAppointments" 
                :key="appointment.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">
                    {{ appointment.doctor?.user?.name }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ formatDate(appointment.appointment_date) }}
                  </p>
                  <span :class="`status-badge status-${appointment.status}`">
                    {{ appointment.status_label }}
                  </span>
                </div>
                <router-link 
                  :to="`/appointments/${appointment.id}`"
                  class="text-primary-600 hover:text-primary-700 font-medium"
                >
                  Ver detalhes
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900">
              Atividade Recente
            </h3>
          </div>
          <div class="card-body">
            <div class="space-y-4">
              <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="flex-1">
                  <p class="text-sm text-gray-900">
                    Bem-vindo ao MediSystem!
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ new Date().toLocaleDateString() }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8 flex justify-center space-x-4">
        <router-link 
          to="/appointments/create" 
          class="btn-primary"
        >
          Agendar Consulta
        </router-link>
        <router-link 
          to="/doctors" 
          class="btn-secondary"
        >
          Encontrar Médicos
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const authStore = useAuthStore();

const stats = ref({
  totalAppointments: 0,
  upcomingAppointments: 0,
  completedAppointments: 0,
  cancelledAppointments: 0
});

const upcomingAppointments = ref([]);
const loading = ref(true);

const formatDate = (date) => {
  return format(new Date(date), 'PPP \'às\' p', { locale: ptBR });
};

const fetchDashboardData = async () => {
  try {
    loading.value = true;
    
    // Fetch appointments
    const appointmentsResponse = await axios.get('/api/appointments', {
      params: { per_page: 5 }
    });
    
    const appointments = appointmentsResponse.data.data;
    
    // Calculate stats
    stats.value = {
      totalAppointments: appointments.length,
      upcomingAppointments: appointments.filter(apt => 
        new Date(apt.appointment_date) > new Date() && 
        !['cancelled', 'completed'].includes(apt.status)
      ).length,
      completedAppointments: appointments.filter(apt => apt.status === 'completed').length,
      cancelledAppointments: appointments.filter(apt => apt.status === 'cancelled').length
    };
    
    // Get upcoming appointments
    upcomingAppointments.value = appointments
      .filter(apt => new Date(apt.appointment_date) > new Date())
      .sort((a, b) => new Date(a.appointment_date) - new Date(b.appointment_date))
      .slice(0, 5);
      
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>