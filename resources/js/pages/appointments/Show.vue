<template>
  <div class="appointment-show">
    <div class="mb-6">
      <router-link 
        to="/appointments"
        class="text-blue-600 hover:text-blue-800 font-medium"
      >
        ← Voltar aos agendamentos
      </router-link>
      <h1 class="text-2xl font-bold text-gray-900 mt-2">Detalhes do Agendamento</h1>
    </div>

    <!-- Loading State -->
    <LoadingSpinner 
      v-if="isLoading" 
      :title="loadingTitle"
      :message="loadingMessage"
      size="large"
    />

    <!-- Content -->
    <div v-else-if="appointment" class="bg-white shadow rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informações do Médico -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Médico</h3>
          <div class="space-y-2">
            <p><span class="font-medium">Nome:</span> {{ appointment.doctor?.user?.name }}</p>
            <p><span class="font-medium">Especialidade:</span> {{ appointment.doctor?.specialty?.name }}</p>
            <p><span class="font-medium">CRM:</span> {{ appointment.doctor?.crm }}/{{ appointment.doctor?.crm_state }}</p>
          </div>
        </div>

        <!-- Informações da Clínica -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações da Clínica</h3>
          <div class="space-y-2">
            <p><span class="font-medium">Nome:</span> {{ appointment.clinic?.name }}</p>
            <p><span class="font-medium">Endereço:</span> {{ appointment.clinic?.address }}</p>
            <p><span class="font-medium">Telefone:</span> {{ appointment.clinic?.phone }}</p>
          </div>
        </div>

        <!-- Detalhes do Agendamento -->
        <div class="md:col-span-2">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Agendamento</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <span class="font-medium">Data e Hora:</span>
              <p class="text-lg">{{ formatDate(appointment.appointment_date) }}</p>
            </div>
            <div>
              <span class="font-medium">Status:</span>
              <p>
                <span 
                  :class="getStatusClass(appointment.status)"
                  class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                >
                  {{ getStatusText(appointment.status) }}
                </span>
              </p>
            </div>
            <div>
              <span class="font-medium">Valor:</span>
              <p class="text-lg font-semibold text-green-600">R$ {{ appointment.price }}</p>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="md:col-span-2" v-if="appointment.notes">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Observações</h3>
          <p class="text-gray-700 bg-gray-50 p-4 rounded-md">{{ appointment.notes }}</p>
        </div>

        <!-- Queixa do Paciente -->
        <div class="md:col-span-2" v-if="appointment.patient_complaint">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Queixa do Paciente</h3>
          <p class="text-gray-700 bg-gray-50 p-4 rounded-md">{{ appointment.patient_complaint }}</p>
        </div>
      </div>

      <!-- Ações -->
      <div class="mt-8 flex justify-end space-x-3" v-if="canModifyAppointment">
        <button 
          v-if="appointment.status === 'scheduled' || appointment.status === 'confirmed'"
          @click="cancelAppointment"
          class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50"
        >
          Cancelar Agendamento
        </button>
        <button 
          v-if="appointment.status === 'scheduled'"
          @click="confirmAppointment"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Confirmar Agendamento
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useLoading } from '@/composables/useLoading';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { isLoading, loadingTitle, loadingMessage, withLoading } = useLoading();

const appointment = ref(null);

const canModifyAppointment = computed(() => {
  return appointment.value && 
         (appointment.value.status === 'scheduled' || appointment.value.status === 'confirmed');
});

const loadAppointment = async () => {
  await withLoading(async () => {
    console.log('Carregando agendamento ID:', route.params.id);
    console.log('Usuário autenticado:', authStore.user);
    
    const response = await axios.get(`/api/appointments/${route.params.id}`);
    appointment.value = response.data.appointment;
    console.log('Agendamento carregado:', appointment.value);
  }, 'Carregando agendamento...', 'Buscando informações do agendamento');
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

const confirmAppointment = async () => {
  try {
    await axios.patch(`/api/appointments/${appointment.value.id}`, {
      status: 'confirmed'
    });
    appointment.value.status = 'confirmed';
  } catch (error) {
    console.error('Erro ao confirmar agendamento:', error);
    alert('Erro ao confirmar agendamento. Tente novamente.');
  }
};

const cancelAppointment = async () => {
  if (!confirm('Tem certeza que deseja cancelar este agendamento?')) {
    return;
  }

  try {
    await axios.patch(`/api/appointments/${appointment.value.id}`, {
      status: 'cancelled',
      cancellation_reason: 'Cancelado pelo paciente'
    });
    appointment.value.status = 'cancelled';
  } catch (error) {
    console.error('Erro ao cancelar agendamento:', error);
    alert('Erro ao cancelar agendamento. Tente novamente.');
  }
};

onMounted(() => {
  loadAppointment();
});
</script>