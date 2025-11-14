<template>
  <div class="bg-white">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-primary-600 to-primary-700">
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-primary-600 mix-blend-multiply" aria-hidden="true" />
      </div>
      <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Sistema de Agendamento Médico
          </h1>
          <p class="mt-6 max-w-3xl mx-auto text-xl text-primary-100">
            Conecte-se com os melhores médicos e clínicas. Agende consultas de forma rápida e fácil.
          </p>
          <div class="mt-10 flex justify-center space-x-4">
            <router-link 
              to="/doctors" 
              class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-primary-700 bg-white hover:bg-gray-50"
            >
              Encontrar Médicos
            </router-link>
            <router-link 
              to="/register" 
              class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-500 hover:bg-primary-400"
            >
              Cadastrar-se
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h2 class="text-3xl font-extrabold text-gray-900">
            Como funciona
          </h2>
          <p class="mt-4 text-lg text-gray-600">
            Simples, rápido e seguro
          </p>
        </div>

        <div class="mt-16">
          <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="text-center">
              <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary-500 text-white mx-auto">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <h3 class="mt-6 text-lg font-medium text-gray-900">Encontre médicos</h3>
              <p class="mt-2 text-base text-gray-500">
                Busque por especialidade, localização ou nome do médico
              </p>
            </div>

            <div class="text-center">
              <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary-500 text-white mx-auto">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v-4h16v4a4 4 0 11-8 0z" />
                </svg>
              </div>
              <h3 class="mt-6 text-lg font-medium text-gray-900">Agende consultas</h3>
              <p class="mt-2 text-base text-gray-500">
                Escolha data e horário disponíveis na agenda do médico
              </p>
            </div>

            <div class="text-center">
              <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary-500 text-white mx-auto">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="mt-6 text-lg font-medium text-gray-900">Receba confirmação</h3>
              <p class="mt-2 text-base text-gray-500">
                Confirmação automática via WhatsApp e email
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-primary-600">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
          <span class="block">Pronto para começar?</span>
          <span class="block text-primary-200">Cadastre-se gratuitamente.</span>
        </h2>
        <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
          <div class="inline-flex rounded-md shadow">
            <router-link 
              to="/register" 
              class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-primary-50"
            >
              Começar agora
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Home page component
</script>