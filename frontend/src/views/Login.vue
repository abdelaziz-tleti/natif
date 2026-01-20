<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-4">
    <!-- Glassmorphism Card -->
    <div class="w-full max-w-md">
      <!-- Logo / Brand -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl mb-4 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white tracking-tight">Iheb Abdel</h1>
        <p class="text-white/70 mt-1">Restaurant Management Platform</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/50">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Welcome back</h2>
        
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone number</label>
            <input 
              id="phone"
              type="tel" 
              v-model="phone" 
              required
              placeholder="06 12 34 56 78"
              class="w-full px-4 py-3 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all placeholder:text-gray-400"
            >
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input 
              id="password"
              type="password" 
              v-model="password" 
              required
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full px-4 py-3 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all placeholder:text-gray-400"
            >
          </div>

          <button 
            type="submit" 
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-200 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]"
          >
            Sign in
          </button>
          
          <transition name="fade">
            <div v-if="error" class="text-center p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                {{ error }}
            </div>
          </transition>
        </form>
      </div>

      <!-- Demo credentials -->
      <div class="mt-8 text-center">
        <p class="text-white/80 font-medium mb-3">Demo Credentials</p>
        <div class="flex flex-wrap justify-center gap-2">
          <button 
            @click="fillCredentials('0101010101')" 
            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-white text-sm transition-all"
          >
            👑 Admin
          </button>
          <button 
            @click="fillCredentials('0606060606')" 
            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-white text-sm transition-all"
          >
            🍕 Manager
          </button>
          <button 
            @click="fillCredentials('0707070707')" 
            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-white text-sm transition-all"
          >
            👤 Employee
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const phone = ref('');
const password = ref('');
const error = ref('');

const fillCredentials = (userPhone) => {
  phone.value = userPhone;
  password.value = 'password';
};

const handleLogin = async () => {
  try {
    error.value = '';
    await authStore.login(phone.value, password.value);
    router.push('/');
  } catch (err) {
    console.error(err);
    if (err.response) {
        error.value = `Error: ${err.response.status} - ${err.response.data.message || err.response.statusText}`;
    } else if (err.request) {
        error.value = 'Network Error - Please check if backend is running';
    } else {
        error.value = err.message;
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
