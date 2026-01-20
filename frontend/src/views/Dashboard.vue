<template>
  <AppLayout>
    <div class="dashboard-content">
      <!-- Welcome -->
      <div class="welcome">
        <h1 class="title">Bonjour 👋</h1>
        <p class="subtitle">{{ greeting }}</p>
      </div>

      <!-- Stats -->
      <div class="stats">
        <div class="stat">
          <div class="stat-value">{{ stats.products }}</div>
          <div class="stat-label">Produits</div>
        </div>
        <div class="stat stat-warning">
          <div class="stat-value">{{ stats.lowStock }}</div>
          <div class="stat-label">Alertes</div>
        </div>
        <div class="stat">
          <div class="stat-value">{{ stats.employees }}</div>
          <div class="stat-label">Équipe</div>
        </div>
        <div class="stat">
          <div class="stat-value">{{ stats.shifts }}</div>
          <div class="stat-label">Planning</div>
        </div>
      </div>

      <!-- Actions -->
      <div class="actions">
        <router-link 
          v-for="action in quickActions" 
          :key="action.path"
          :to="action.path"
          class="action"
        >
          <span class="action-icon" v-html="action.icon"></span>
          <span class="action-title">{{ action.title }}</span>
          <svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </router-link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

const authStore = useAuthStore();

const stats = ref({
  products: 0,
  lowStock: 0,
  employees: 0,
  shifts: 0
});

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Bonne matinée';
  if (hour < 18) return 'Bon après-midi';
  return 'Bonne soirée';
});

const icons = {
  box: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
  calendar: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
  users: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
  alert: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
  bell: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>'
};

const quickActions = computed(() => {
  const actions = [];
  
  if (authStore.isManager || authStore.isAdmin) {
    actions.push(
      { path: '/manager/products', title: 'Stock', icon: icons.box },
      { path: '/manager/shifts', title: 'Planning', icon: icons.calendar },
      { path: '/manager/staff', title: 'Équipe', icon: icons.users }
    );
  } else {
    actions.push(
      { path: '/employee/shifts', title: 'Planning', icon: icons.calendar },
      { path: '/employee/stock', title: 'Alertes', icon: icons.alert },
      { path: '/notifications', title: 'Notifications', icon: icons.bell }
    );
  }
  
  return actions;
});

const fetchStats = async () => {
  try {
    const [prodRes, userRes, shiftRes] = await Promise.all([
      api.get('/products'),
      api.get('/users'),
      api.get('/shifts')
    ]);

    const prods = prodRes.data['hydra:member'] || [];
    stats.value.products = prodRes.data['hydra:totalItems'] || prods.length;
    stats.value.lowStock = prods.filter(p => p.quantity <= p.minThreshold).length;
    stats.value.employees = userRes.data['hydra:totalItems'] || (userRes.data['hydra:member'] || []).length;
    
    const today = new Date().toDateString();
    const shifts = shiftRes.data['hydra:member'] || [];
    stats.value.shifts = shifts.filter(s => new Date(s.startTime).toDateString() === today).length;
  } catch (e) {
    console.error("Failed to fetch stats", e);
  }
};

onMounted(() => {
  fetchStats();
});
</script>

<style scoped>
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.welcome {
  text-align: left;
}

.title {
  font-size: 28px;
  font-weight: 900;
  color: #111827;
  margin: 0;
}

.subtitle {
  font-size: 14px;
  color: #9ca3af;
  margin: 4px 0 0;
}

.stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.stat {
  background: white;
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  border: 1px solid #f3f4f6;
}

.stat-value {
  font-size: 32px;
  font-weight: 900;
  color: #111827;
  margin: 0;
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
  color: #9ca3af;
  margin-top: 4px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-warning .stat-value {
  color: #f59e0b;
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.action {
  background: white;
  border-radius: 16px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #f3f4f6;
  text-decoration: none;
  transition: all 0.2s ease;
}

.action:active {
  transform: scale(0.98);
  background: #f9fafb;
}

.action-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #f9fafb;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #6b7280;
}

.action-title {
  flex: 1;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}

.action-arrow {
  width: 18px;
  height: 18px;
  color: #d1d5db;
  flex-shrink: 0;
}
</style>
