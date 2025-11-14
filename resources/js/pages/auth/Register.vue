<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Criar conta
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Ou
          <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
            entre em sua conta existente
          </router-link>
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nome completo</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              required
              class="mt-1 input-field"
              placeholder="Seu nome completo"
            />
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              required
              class="mt-1 input-field"
              placeholder="seu@email.com"
            />
          </div>

          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
            <input
              id="phone"
              v-model="form.phone"
              name="phone"
              type="tel"
              class="mt-1 input-field"
              placeholder="(00) 00000-0000"
            />
          </div>

          <div>
            <label for="user_type" class="block text-sm font-medium text-gray-700">Tipo de usuário</label>
            <select
              id="user_type"
              v-model="form.user_type"
              name="user_type"
              required
              class="mt-1 input-field"
            >
              <option value="">Selecione</option>
              <option value="patient">Paciente</option>
              <option value="doctor">Médico</option>
            </select>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              required
              class="mt-1 input-field"
              placeholder="Mínimo 8 caracteres"
            />
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar senha</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              required
              class="mt-1 input-field"
              placeholder="Confirme sua senha"
            />
          </div>
        </div>

        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <div class="text-sm text-red-700">
            {{ error }}
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
          >
            <span v-if="authStore.loading">Criando conta...</span>
            <span v-else>Criar conta</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
  name: '',
  email: '',
  phone: '',
  user_type: '',
  password: '',
  password_confirmation: ''
});

const error = ref('');

const handleRegister = async () => {
  error.value = '';
  
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'As senhas não conferem';
    return;
  }
  
  try {
    await authStore.register(form.value);
    router.push('/dashboard');
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao criar conta';
  }
};
</script>