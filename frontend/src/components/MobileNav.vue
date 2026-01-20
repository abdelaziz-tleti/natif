<template>
  <div class="mobile-nav-wrapper">
    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
      <router-link 
        v-for="item in navItems" 
        :key="item.path" 
        :to="item.path"
        class="nav-item"
        :class="{ 'active': isActive(item.path) }"
      >
        <div class="nav-icon">
          <span v-html="item.icon"></span>
          <span v-if="item.badge" class="badge">{{ item.badge }}</span>
        </div>
        <span class="nav-label">{{ item.label }}</span>
      </router-link>
    </nav>

    <!-- Floating Action Button (FAB) -->
    <button 
      v-if="showFab"
      @click="$emit('fab-click')"
      class="fab"
    >
      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
      </svg>
    </button>

    <!-- Slide-out Menu -->
    <transition name="slide">
      <div v-if="menuOpen" class="menu-overlay" @click="closeMenu">
        <div class="menu-panel" @click.stop>
          <div class="menu-header">
            <div class="user-info">
              <div class="avatar">
                <span>{{ userInitial }}</span>
              </div>
              <div class="user-details">
                <h3>{{ userName }}</h3>
                <p>{{ userRole }}</p>
              </div>
            </div>
            <button @click="closeMenu" class="close-btn">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="menu-content">
            <div class="menu-section">
              <h4>Navigation</h4>
              <router-link 
                v-for="item in allMenuItems" 
                :key="item.path"
                :to="item.path"
                @click="closeMenu"
                class="menu-link"
              >
                <span class="menu-icon" v-html="item.icon"></span>
                <span>{{ item.label }}</span>
                <svg class="w-5 h-5 ml-auto opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </router-link>
            </div>

            <div class="menu-section">
              <h4>Paramètres</h4>
              <button class="menu-link">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Mon Profil</span>
              </button>
              <button class="menu-link">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Réglages</span>
              </button>
              <button @click="logout" class="menu-link text-red-600">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Déconnexion</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Menu Toggle Button (Hamburger) -->
    <button @click="toggleMenu" class="menu-toggle">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>
</div>
</template>

<script setup>
// v2.0 - Navigation en Français
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
  showFab: {
    type: Boolean,
    default: false
  }
});

defineEmits(['fab-click']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const menuOpen = ref(false);

const userName = computed(() => {
  return authStore.user?.firstName || authStore.user?.phone || 'Utilisateur';
});

const userInitial = computed(() => {
  return userName.value.charAt(0).toUpperCase();
});

const userRole = computed(() => {
  if (authStore.isAdmin) return 'Administrateur';
  if (authStore.isManager) return 'Manager';
  return 'Employé';
});

const icons = {
  home: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
  box: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
  calendar: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
  users: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
  alert: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
  bell: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>'
};

const navItems = computed(() => {
  const items = [
    {
      path: '/',
      label: 'Accueil',
      icon: icons.home
    }
  ];

  if (authStore.isManager || authStore.isAdmin) {
    items.push(
      {
        path: '/manager/products',
        label: 'Stock',
        icon: icons.box
      },
      {
        path: '/manager/shifts',
        label: 'Planning',
        icon: icons.calendar
      },
      {
        path: '/notifications',
        label: 'Notifs',
        icon: icons.bell,
        badge: 0
      }
    );
  } else {
    items.push(
      {
        path: '/employee/shifts',
        label: 'Planning',
        icon: icons.calendar
      },
      {
        path: '/employee/stock',
        label: 'Alertes',
        icon: icons.alert
      },
      {
        path: '/notifications',
        label: 'Notifs',
        icon: icons.bell,
        badge: 0
      }
    );
  }

  return items;
});

const allMenuItems = computed(() => {
  const items = [...navItems.value];
  
  if (authStore.isManager || authStore.isAdmin) {
    items.push({
      path: '/manager/staff',
      label: 'Gestion Équipe',
      icon: icons.users
    });
  }
  
  return items;
});

const isActive = (path) => {
  if (path === '/') return route.path === '/';
  return route.path.startsWith(path);
};

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value;
};

const closeMenu = () => {
  menuOpen.value = false;
};

const logout = () => {
  authStore.logout();
  router.push('/login');
  closeMenu();
};
</script>

<style scoped>
.mobile-nav-wrapper {
  position: relative;
}

/* Bottom Navigation */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 72px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 0 8px 8px;
  z-index: 1000;
  box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.08);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 8px 16px;
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  color: #6b7280;
  text-decoration: none;
  position: relative;
  min-width: 64px;
}

.nav-item:active {
  transform: scale(0.92);
}

.nav-item.active {
  color: #4f46e5;
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(99, 102, 241, 0.05));
}

.nav-icon {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
}

.nav-item.active .nav-icon {
  animation: bounce 0.6s ease;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  font-size: 10px;
  font-weight: 800;
  padding: 2px 5px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.nav-label {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.02em;
}

/* Floating Action Button */
.fab {
  position: fixed;
  bottom: 88px;
  right: 20px;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1f2937, #111827);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  border: none;
  cursor: pointer;
  z-index: 999;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: fab-appear 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes fab-appear {
  from {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  to {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

.fab:active {
  transform: scale(0.9);
}

/* Menu Toggle Button */
.menu-toggle {
  position: fixed;
  top: 20px;
  left: 20px;
  width: 48px;
  height: 48px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  z-index: 1001;
  transition: all 0.3s ease;
  color: #1f2937;
}

.menu-toggle:active {
  transform: scale(0.92);
}

/* Slide-out Menu */
.menu-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
}

.menu-panel {
  width: 85%;
  max-width: 360px;
  height: 100%;
  background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.menu-header {
  padding: 24px 20px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.user-info {
  display: flex;
  gap: 12px;
  align-items: center;
}

.avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 800;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.user-details h3 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.user-details p {
  font-size: 13px;
  opacity: 0.9;
  margin: 4px 0 0;
}

.close-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:active {
  transform: scale(0.9);
}

.menu-content {
  flex: 1;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.menu-section h4 {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #9ca3af;
  margin: 0 0 12px 12px;
}

.menu-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 16px;
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  color: #1f2937;
  text-decoration: none;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.2s ease;
  margin-bottom: 8px;
  cursor: pointer;
  width: 100%;
  text-align: left;
}

.menu-link:active {
  transform: scale(0.97);
  background: #f3f4f6;
}

.menu-icon {
  width: 22px;
  height: 22px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Slide Transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from .menu-panel,
.slide-leave-to .menu-panel {
  transform: translateX(-100%);
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
}

/* Safe Area for Notch Devices */
@supports (padding: max(0px)) {
  .bottom-nav {
    padding-bottom: max(8px, env(safe-area-inset-bottom));
  }
}
</style>
