<template>
  <div class="layout-container">
    <!-- Mobile Header -->
    <div class="mobile-header md:hidden">
      <button @click="isMobileOpen = true" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
      </button>
      <div class="font-bold text-gray-900">NatifSaaS</div>
      <div class="w-10"></div> <!-- Placeholder for balance -->
    </div>

    <!-- Mobile Overlay -->
    <div v-if="isMobileOpen" @click="isMobileOpen = false" class="mobile-overlay md:hidden"></div>

    <Sidebar @toggle-collapse="onSidebarToggle" :class="{ 'mobile-open': isMobileOpen }" />
    
    <main class="main-content" :class="{ 'main-content-collapsed': isSidebarCollapsed, 'mobile-blur': isMobileOpen }">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Mobile Bottom Navigation -->
    <BottomNav />

    <!-- PWA Installation Banner -->
    <transition name="up">
      <div v-if="showInstallBanner" class="install-banner">
        <div class="flex items-center gap-3">
          <div class="bg-indigo-600 p-2 rounded-lg">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
          </div>
          <div>
            <p class="text-sm font-bold text-gray-900">Natif Mobile App</p>
            <p class="text-[11px] text-gray-500 leading-tight">{{ installMessage }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
            <button v-if="deferredPrompt" @click="installPWA" class="btn-install">Install</button>
            <div v-else class="text-[10px] bg-gray-100 px-2 py-1 rounded text-gray-400 font-bold uppercase">Manual</div>
            <button @click="showInstallBanner = false" class="btn-close">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import Sidebar from '../components/Sidebar.vue';
import BottomNav from '../components/BottomNav.vue';
import { ref, onMounted } from 'vue';

const isSidebarCollapsed = ref(false);
const isMobileOpen = ref(false);
const showInstallBanner = ref(false);
const deferredPrompt = ref(null);
const installMessage = ref('Add this app to your home screen');

const onSidebarToggle = (collapsed) => {
  isSidebarCollapsed.value = collapsed;
};

// Check if already in standalone mode
onMounted(() => {
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
  
  if (isStandalone) {
    showInstallBanner.value = false;
    return;
  }

  // Show banner after 2 seconds if not installed
  setTimeout(() => {
    if (!isStandalone) {
        showInstallBanner.value = true;
        if (!deferredPrompt.value) {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            installMessage.value = isIOS 
                ? 'Tap Share > Add to Home Screen' 
                : 'Tap 3-dots > Install or Add to Home Screen';
        }
    }
  }, 2000);

  window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt triggered');
    e.preventDefault();
    deferredPrompt.value = e;
    installMessage.value = 'Add to Home Screen for a full experience';
    showInstallBanner.value = true;
  });

  // Detect iOS
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  if (isIOS) {
    installMessage.value = 'Tap Share and "Add to Home Screen"';
  }
});

const installPWA = async () => {
  if (!deferredPrompt.value) return;
  deferredPrompt.value.prompt();
  const { outcome } = await deferredPrompt.value.userChoice;
  if (outcome === 'accepted') {
    deferredPrompt.value = null;
    showInstallBanner.value = false;
  }
};

// Close mobile sidebar on route change
import { watch } from 'vue';
import { useRoute } from 'vue-router';
const route = useRoute();
watch(() => route.path, () => {
  isMobileOpen.value = false;
});
</script>

<style scoped>
.layout-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}

.main-content {
  flex: 1;
  margin-left: 260px; /* Sidebar width */
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 0;
  min-height: 100vh;
  width: calc(100% - 260px);
}

.main-content-collapsed {
  margin-left: 80px;
  width: calc(100% - 80px);
}

/* Page transitions */
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@media (max-width: 1024px) {
  .main-content {
    margin-left: 0;
    width: 100%;
    padding-top: 60px; /* Space for mobile header */
    padding-bottom: 80px; /* Space for bottom nav */
  }
}

.mobile-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  z-index: 900;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.mobile-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 950;
}

.mobile-blur {
  filter: blur(2px);
  pointer-events: none;
}

.install-banner {
    position: fixed;
    bottom: 80px;
    left: 16px;
    right: 16px;
    background: white;
    padding: 12px 16px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    z-index: 1100;
    border: 1px solid #e5e7eb;
}

.btn-install {
    background: #6366f1;
    color: white;
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    border: none;
}

.btn-close {
    padding: 8px;
    color: #9ca3af;
    background: #f3f4f6;
    border-radius: 8px;
    border: none;
}

/* Animations */
.up-enter-active, .up-leave-active {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.up-enter-from, .up-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>

<style>
/* Global content styles to fix padding issues in nested views if needed */
.main-content > div {
  min-height: 100vh;
}
</style>
