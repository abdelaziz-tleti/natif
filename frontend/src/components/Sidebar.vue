<template>
  <aside 
    class="sidebar"
    :class="{ 'sidebar-collapsed': isCollapsed }"
  >
    <!-- Logo Section -->
    <div class="logo-container">
      <div class="logo-circle">
        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
      </div>
      <span v-if="!isCollapsed" class="logo-text">Natif<span>SaaS</span></span>
    </div>

    <!-- Navigation Groups -->
    <nav class="nav-groups">
      <!-- Main Group -->
      <div class="nav-group">
        <p v-if="!isCollapsed" class="group-title">Main</p>
        <router-link to="/" class="nav-link" active-class="active">
          <svg class="h-6 w-6 icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span v-if="!isCollapsed" class="link-text">Dashboard</span>
        </router-link>
      </div>

      <!-- Management Group (Managers only) -->
      <div v-if="authStore.isManager || authStore.isAdmin" class="nav-group">
        <p v-if="!isCollapsed" class="group-title">Management</p>
        <router-link to="/manager/products" class="nav-link" active-class="active">
          <svg class="h-6 w-6 icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <span v-if="!isCollapsed" class="link-text">Inventory</span>
        </router-link>
        
        <router-link to="/manager/shifts" class="nav-link" active-class="active">
          <svg class="h-6 w-6 icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span v-if="!isCollapsed" class="link-text">Shifts & Staff</span>
        </router-link>
      </div>

      <!-- Employee Group -->
      <div class="nav-group">
        <p v-if="!isCollapsed" class="group-title">Personal</p>
        <router-link to="/employee/stock" class="nav-link" active-class="active">
          <svg class="h-6 w-6 icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h8M11 10h8M11 15h8M4 5h1v1H4V5zm0 5h1v1H4v-1zm0 5h1v1H4v-1z" />
          </svg>
          <span v-if="!isCollapsed" class="link-text">Report Issue</span>
        </router-link>
      </div>
    </nav>

    <!-- Footer / User Profile -->
    <div class="sidebar-footer">
      <div class="user-info" v-if="!isCollapsed">
        <div class="user-avatar">{{ userInitial }}</div>
        <div class="user-details">
          <p class="user-email text-truncate">{{ authStore.user?.email }}</p>
          <p class="user-role">{{ userRole }}</p>
        </div>
      </div>
      
      <button @click="handleLogout" class="logout-btn" :title="isCollapsed ? 'Logout' : ''">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span v-if="!isCollapsed">Sign Out</span>
      </button>

      <button @click="toggleSidebar" class="collapse-btn">
        <svg v-if="isCollapsed" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
        <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const isCollapsed = ref(false);
const emit = defineEmits(['toggle-collapse']);

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
  emit('toggle-collapse', isCollapsed.value);
};

const userInitial = computed(() => {
  return authStore.user?.email?.charAt(0).toUpperCase() || 'U';
});

const userRole = computed(() => {
  if (authStore.isAdmin) return 'Administrator';
  if (authStore.isManager) return 'Restaurant Manager';
  return 'Employee';
});

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.sidebar {
  width: 260px;
  height: 100vh;
  background: #111827;
  color: #f3f4f6;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 10px 0 25px -5px rgba(0, 0, 0, 0.1);
}
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
    box-shadow: none;
  }
  
  .sidebar.mobile-open {
    transform: translateX(0);
    box-shadow: 20px 0 50px rgba(0, 0, 0, 0.3);
  }
  
  .sidebar-collapsed {
    width: 260px; /* Reset collapsed width on mobile */
  }
}

.sidebar-collapsed {
  width: 80px;
}

.logo-container {
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.logo-circle {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
}

.logo-text {
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.5px;
  white-space: nowrap;
}

.logo-text span {
  color: #818cf8;
}

.nav-groups {
  flex: 1;
  padding: 0 16px;
  overflow-y: auto;
}

.nav-group {
  margin-bottom: 24px;
}

.group-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #6b7280;
  letter-spacing: 1px;
  margin-bottom: 8px;
  padding-left: 12px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  color: #9ca3af;
  text-decoration: none;
  transition: all 0.2s;
  margin-bottom: 4px;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #f3f4f6;
}

.nav-link.active {
  background: rgba(99, 102, 241, 0.1);
  color: #818cf8;
}

.nav-link.active .icon {
  color: #818cf8;
}

.icon {
  flex-shrink: 0;
  transition: transform 0.2s;
}

.nav-link:hover .icon {
  transform: translateX(2px);
}

.link-text {
  font-weight: 500;
  font-size: 14px;
  white-space: nowrap;
}

.sidebar-footer {
  padding: 20px 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  margin-bottom: 8px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: #374151;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #818cf8;
  flex-shrink: 0;
}

.user-details {
  overflow: hidden;
}

.user-email {
  font-size: 13px;
  font-weight: 600;
  margin: 0;
  color: #f3f4f6;
}

.user-role {
  font-size: 11px;
  margin: 2px 0 0 0;
  color: #6b7280;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  background: none;
  border: none;
  padding: 12px;
  border-radius: 12px;
  color: #ef4444;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
}

.collapse-btn {
  background: none;
  border: none;
  color: #4b5563;
  cursor: pointer;
  padding: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: color 0.2s;
}

.collapse-btn:hover {
  color: #f3f4f6;
}

.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Scrollbar styling */
.nav-groups::-webkit-scrollbar {
  width: 4px;
}
.nav-groups::-webkit-scrollbar-track {
  background: transparent;
}
.nav-groups::-webkit-scrollbar-thumb {
  background: #374151;
  border-radius: 10px;
}
</style>
