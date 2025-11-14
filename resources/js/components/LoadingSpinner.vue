<template>
  <div v-if="show">
    <!-- Overlay Loading (sobrepõe a tela) -->
    <div 
      v-if="overlay" 
      class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg p-8 shadow-2xl max-w-sm mx-4 border border-gray-100">
        <div class="flex flex-col items-center text-center space-y-4">
          <div class="spinner-large"></div>
          <div>
            <p class="text-gray-900 font-semibold text-lg">{{ title }}</p>
            <p v-if="message" class="text-gray-600 mt-2 text-sm leading-relaxed">{{ message }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Loading (sobrepõe apenas uma seção) -->
    <div 
      v-else-if="section"
      class="relative"
    >
      <div class="absolute inset-0 bg-white bg-opacity-80 z-10 flex items-center justify-center rounded-lg">
        <div class="bg-white rounded-lg p-6 shadow-lg border border-gray-200">
          <div class="flex items-center space-x-3">
            <div class="spinner" :class="spinnerSizeClass"></div>
            <div>
              <p class="text-gray-900 font-medium" :class="textSizeClass">{{ title }}</p>
              <p v-if="message" class="text-gray-600 mt-1" :class="messageClass">{{ message }}</p>
            </div>
          </div>
        </div>
      </div>
      <slot></slot>
    </div>

    <!-- Inline Loading (sem overlay) -->
    <div 
      v-else 
      class="flex items-center justify-center py-8"
      :class="size === 'small' ? 'py-4' : size === 'large' ? 'py-12' : 'py-8'"
    >
      <div class="flex items-center space-x-3">
        <div class="spinner" :class="spinnerSizeClass"></div>
        <div v-if="title || message">
          <p v-if="title" class="text-gray-900 font-medium" :class="textSizeClass">{{ title }}</p>
          <p v-if="message" class="text-gray-600 mt-1" :class="messageClass">{{ message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Carregando...'
  },
  message: {
    type: String,
    default: ''
  },
  overlay: {
    type: Boolean,
    default: false
  },
  section: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'medium', // small, medium, large
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  }
});

const overlayClass = computed(() => {
  return props.overlay ? 'loading-overlay' : 'loading-inline';
});

const spinnerSizeClass = computed(() => {
  const sizes = {
    small: 'w-4 h-4',
    medium: 'w-6 h-6',
    large: 'w-8 h-8'
  };
  return sizes[props.size];
});

const textSizeClass = computed(() => {
  const sizes = {
    small: 'text-sm',
    medium: 'text-base',
    large: 'text-lg'
  };
  return sizes[props.size];
});

const messageClass = computed(() => {
  const sizes = {
    small: 'text-xs',
    medium: 'text-sm',
    large: 'text-base'
  };
  return sizes[props.size];
});
</script>

<style scoped>
.spinner {
  border: 4px solid #e5e7eb;
  border-top: 4px solid #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.loading-spinner {
  width: 100%;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>