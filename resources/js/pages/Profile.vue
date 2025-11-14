<template>
  <div class="profile-page">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Meu Perfil</h1>
      <p class="text-gray-600 mt-1">Gerencie suas informações pessoais</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Informações do Perfil -->
      <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-6">Informações Pessoais</h2>
          
          <form @submit.prevent="updateProfile" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nome Completo *
                </label>
                <input 
                  type="text"
                  v-model="form.name"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  E-mail *
                </label>
                <input 
                  type="email"
                  v-model="form.email"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Telefone
                </label>
                <input 
                  type="tel"
                  v-model="form.phone"
                  placeholder="(11) 99999-9999"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  CPF
                </label>
                <input 
                  type="text"
                  v-model="form.cpf"
                  placeholder="000.000.000-00"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Data de Nascimento
                </label>
                <input 
                  type="date"
                  v-model="form.birth_date"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tipo de Usuário
                </label>
                <input 
                  type="text"
                  :value="getUserTypeText(user.user_type)"
                  readonly
                  class="w-full border border-gray-300 bg-gray-50 rounded-md px-3 py-2 text-gray-600"
                >
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Endereço
              </label>
              <textarea 
                v-model="form.address"
                rows="3"
                placeholder="Rua, número, bairro, cidade - estado"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div class="flex justify-end">
              <button 
                type="submit"
                :disabled="loading"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ loading ? 'Salvando...' : 'Salvar Alterações' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Avatar/Foto -->
        <div class="bg-white shadow rounded-lg p-6 text-center">
          <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-blue-600 font-bold text-2xl">
              {{ getInitials(user.name) }}
            </span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">{{ user.name }}</h3>
          <p class="text-sm text-gray-600">{{ getUserTypeText(user.user_type) }}</p>
          <button 
            class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium"
            @click="uploadPhoto"
          >
            Alterar Foto
          </button>
        </div>

        <!-- Alterar Senha -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Alterar Senha</h3>
          
          <form @submit.prevent="changePassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Senha Atual
              </label>
              <input 
                type="password"
                v-model="passwordForm.current_password"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nova Senha
              </label>
              <input 
                type="password"
                v-model="passwordForm.new_password"
                required
                minlength="6"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Confirmar Nova Senha
              </label>
              <input 
                type="password"
                v-model="passwordForm.new_password_confirmation"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <button 
              type="submit"
              :disabled="passwordLoading"
              class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 disabled:opacity-50"
            >
              {{ passwordLoading ? 'Alterando...' : 'Alterar Senha' }}
            </button>
          </form>
        </div>

        <!-- Informações da Conta -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações da Conta</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Membro desde:</span>
              <span class="font-medium">{{ formatDate(user.created_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Último acesso:</span>
              <span class="font-medium">{{ formatDate(user.updated_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Status:</span>
              <span 
                :class="user.is_active ? 'text-green-600' : 'text-red-600'"
                class="font-medium"
              >
                {{ user.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const user = authStore.user;

const loading = ref(false);
const passwordLoading = ref(false);

const form = ref({
  name: user.name || '',
  email: user.email || '',
  phone: user.phone || '',
  cpf: user.cpf || '',
  birth_date: user.birth_date || '',
  address: user.address || ''
});

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const updateProfile = async () => {
  try {
    loading.value = true;
    await axios.put('/api/auth/profile', form.value);
    
    // Atualizar dados no store
    authStore.updateUser(form.value);
    
    alert('Perfil atualizado com sucesso!');
  } catch (error) {
    console.error('Erro ao atualizar perfil:', error);
    alert('Erro ao atualizar perfil. Tente novamente.');
  } finally {
    loading.value = false;
  }
};

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    alert('As senhas não coincidem.');
    return;
  }

  try {
    passwordLoading.value = true;
    await axios.put('/api/auth/change-password', passwordForm.value);
    
    // Limpar formulário
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    };
    
    alert('Senha alterada com sucesso!');
  } catch (error) {
    console.error('Erro ao alterar senha:', error);
    alert('Erro ao alterar senha. Verifique sua senha atual.');
  } finally {
    passwordLoading.value = false;
  }
};

const uploadPhoto = () => {
  alert('Funcionalidade de upload de foto em desenvolvimento.');
};

const getUserTypeText = (type) => {
  const types = {
    'admin': 'Administrador',
    'doctor': 'Médico',
    'patient': 'Paciente'
  };
  return types[type] || 'Usuário';
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};
</script>