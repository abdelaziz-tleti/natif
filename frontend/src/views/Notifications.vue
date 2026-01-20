<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Alertes et activités</p>
      </div>
      <button 
        v-if="unreadCount > 0"
        @click="markAllAsRead" 
        class="text-sm font-bold text-indigo-600 hover:text-indigo-800 px-4 py-2 rounded-xl bg-indigo-50 active:scale-95 transition-all"
      >
        Tout lire
      </button>
    </div>

    <!-- Notifications List -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <div v-else class="space-y-3">
      <div 
        v-for="notification in notifications" 
        :key="notification.id" 
        @click="markAsRead(notification)"
        class="card cursor-pointer"
        :class="notification.isRead ? 'opacity-60' : 'border-indigo-200 bg-indigo-50/20'"
      >
        <div class="flex gap-4">
          <div 
            class="h-12 w-12 shrink-0 rounded-xl flex items-center justify-center"
            :class="notification.isRead ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-600'"
          >
            <svg v-if="notification.title.includes('Stock')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
          </div>
          
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start gap-2">
              <h3 class="font-bold text-gray-900 truncate" :class="!notification.isRead && 'text-indigo-900'">{{ notification.title }}</h3>
              <span class="text-xs text-gray-400 flex-shrink-0">{{ formatTime(notification.createdAt) }}</span>
            </div>
            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ notification.message }}</p>
          </div>
          
          <div v-if="!notification.isRead" class="flex items-center">
            <div class="h-2.5 w-2.5 bg-indigo-600 rounded-full animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="notifications.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-100">
        <div class="bg-gray-50 h-20 w-20 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900">Aucune notification</h3>
        <p class="text-gray-500 max-w-xs mx-auto mt-1">Vous êtes à jour ! Les nouvelles alertes apparaîtront ici.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

const notifications = ref([]);
const loading = ref(true);

const unreadCount = computed(() => notifications.value.filter(n => !n.isRead).length);

const fetchNotifications = async () => {
  loading.value = true;
  try {
    const response = await api.get('/notifications?order[createdAt]=desc');
    notifications.value = response.data['hydra:member'] || [];
  } catch (e) {
    console.error('Failed to fetch notifications', e);
  } finally {
    loading.value = false;
  }
};

const markAsRead = async (notification) => {
  if (notification.isRead) return;
  
  try {
    await api.patch(`/notifications/${notification.id}`, {
      isRead: true
    }, {
      headers: { 'Content-Type': 'application/merge-patch+json' }
    });
    notification.isRead = true;
  } catch (e) {
    console.error('Failed to mark as read', e);
  }
};

const markAllAsRead = async () => {
  const unread = notifications.value.filter(n => !n.isRead);
  await Promise.all(unread.map(n => markAsRead(n)));
};

function formatTime(dateStr) {
  const date = new Date(dateStr);
  const now = new Date();
  const diff = (now - date) / 1000 / 60; // minutes
  
  if (diff < 1) return 'À l\'instant';
  if (diff < 60) return `Il y a ${Math.round(diff)}m`;
  if (diff < 1440) return `Il y a ${Math.round(diff / 60)}h`;
  return date.toLocaleDateString('fr-FR');
}

onMounted(() => {
  fetchNotifications();
});
</script>
