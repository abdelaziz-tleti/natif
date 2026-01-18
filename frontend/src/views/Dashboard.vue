<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
            <span class="font-bold text-xl text-gray-900 hidden sm:block">RestoPOS</span>
          </div>
          
          <!-- User Menu -->
          <div class="flex items-center gap-3">
            <div class="hidden sm:block text-right">
              <p class="text-sm font-medium text-gray-900">{{ user?.email }}</p>
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="roleBadgeClass">
                {{ roleLabel }}
              </span>
            </div>
            <button 
              @click="logout" 
              class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
              title="Logout"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Banner -->
      <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Welcome back! 👋</h1>
        <p class="text-gray-500 mt-1">Here's what's happening with your restaurant today.</p>
      </div>

      <!-- Quick Stats (Mobile Horizontal Scroll) -->
      <div class="flex gap-4 overflow-x-auto pb-4 mb-8 snap-x snap-mandatory scrollbar-hide">
        <div class="min-w-[160px] snap-start bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-200">
          <p class="text-indigo-100 text-sm font-medium">Total Products</p>
          <p class="text-3xl font-bold mt-1">24</p>
        </div>
        <div class="min-w-[160px] snap-start bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-5 text-white shadow-lg shadow-amber-200">
          <p class="text-amber-100 text-sm font-medium">Low Stock</p>
          <p class="text-3xl font-bold mt-1">3</p>
        </div>
        <div class="min-w-[160px] snap-start bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl p-5 text-white shadow-lg shadow-emerald-200">
          <p class="text-emerald-100 text-sm font-medium">Employees</p>
          <p class="text-3xl font-bold mt-1">8</p>
        </div>
        <div class="min-w-[160px] snap-start bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl p-5 text-white shadow-lg shadow-purple-200">
          <p class="text-purple-100 text-sm font-medium">Shifts Today</p>
          <p class="text-3xl font-bold mt-1">5</p>
        </div>
      </div>

      <!-- Feature Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Admin Section -->
        <div v-if="isAdmin" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="h-2 bg-gradient-to-r from-blue-500 to-cyan-500"></div>
          <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-2 bg-blue-100 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              </div>
              <h2 class="text-xl font-bold text-gray-900">Admin Controls</h2>
            </div>
            <ul class="space-y-3">
              <li class="flex items-center gap-3 text-gray-600 hover:text-blue-600 cursor-pointer transition-colors">
                <span class="text-lg">🏢</span> Manage Restaurants
              </li>
              <li class="flex items-center gap-3 text-gray-600 hover:text-blue-600 cursor-pointer transition-colors">
                <span class="text-lg">👥</span> Manage Global Users
              </li>
              <li class="flex items-center gap-3 text-gray-600 hover:text-blue-600 cursor-pointer transition-colors">
                <span class="text-lg">📊</span> View System Reports
              </li>
            </ul>
          </div>
        </div>

        <!-- Manager Section -->
        <div v-if="isAdmin || isManager" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="h-2 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
          <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-2 bg-emerald-100 rounded-xl">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
              </div>
              <h2 class="text-xl font-bold text-gray-900">Manager Area</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <router-link 
                  to="/manager/products" 
                  class="flex items-center gap-3 text-gray-600 hover:text-emerald-600 transition-colors group"
                >
                  <span class="text-lg">📦</span> 
                  <span class="group-hover:translate-x-1 transition-transform">Manage Inventory</span>
                  <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </router-link>
              </li>
              <li>
                <router-link 
                  to="/manager/shifts" 
                  class="flex items-center gap-3 text-gray-600 hover:text-emerald-600 transition-colors group"
                >
                  <span class="text-lg">📅</span> 
                  <span class="group-hover:translate-x-1 transition-transform">Schedule Shifts</span>
                  <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </router-link>
              </li>
              <li class="flex items-center gap-3 text-gray-400 cursor-not-allowed">
                <span class="text-lg">👥</span> Manage Employees <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">Soon</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Employee Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="h-2 bg-gradient-to-r from-purple-500 to-pink-500"></div>
          <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-2 bg-purple-100 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              </div>
              <h2 class="text-xl font-bold text-gray-900">My Workspace</h2>
            </div>
            <ul class="space-y-3">
              <li class="flex items-center gap-3 text-gray-400 cursor-not-allowed">
                <span class="text-lg">📅</span> View My Planning <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">Soon</span>
              </li>
              <li>
                <router-link 
                  to="/employee/stock" 
                  class="flex items-center gap-3 text-gray-600 hover:text-purple-600 transition-colors group"
                >
                  <span class="text-lg">⚠️</span> 
                  <span class="group-hover:translate-x-1 transition-transform">Report Stock Issue</span>
                  <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </router-link>
              </li>
              <li class="flex items-center gap-3 text-gray-400 cursor-not-allowed">
                <span class="text-lg">🔔</span> View Notifications <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">Soon</span>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const user = computed(() => authStore.user);
const isAdmin = computed(() => authStore.isAdmin);
const isManager = computed(() => authStore.isManager);

const roleLabel = computed(() => {
  if (authStore.isAdmin) return 'Administrator';
  if (authStore.isManager) return 'Manager';
  return 'Employee';
});

const roleBadgeClass = computed(() => {
  if (authStore.isAdmin) return 'bg-blue-100 text-blue-800';
  if (authStore.isManager) return 'bg-emerald-100 text-emerald-800';
  return 'bg-purple-100 text-purple-800';
});

const logout = () => {
  authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
</style>
