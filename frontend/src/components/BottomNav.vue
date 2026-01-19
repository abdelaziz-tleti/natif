<template>
  <nav class="bottom-nav md:hidden">
    <router-link to="/" class="nav-item" active-class="active">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>
      <span class="nav-label">Home</span>
    </router-link>

    <router-link v-if="authStore.isManager || authStore.isAdmin" to="/manager/products" class="nav-item" active-class="active">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      <span class="nav-label">Inventory</span>
    </router-link>

    <router-link v-if="authStore.isManager || authStore.isAdmin" to="/manager/staff" class="nav-item" active-class="active">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <span class="nav-label">Staff</span>
    </router-link>

    <router-link to="/employee/stock" class="nav-item" active-class="active">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h8M11 10h8M11 15h8M4 5h1v1H4V5zm0 5h1v1H4v-1zm0 5h1v1H4v-1z" />
      </svg>
      <span class="nav-label">Report</span>
    </router-link>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
const authStore = useAuthStore();
</script>

<style scoped>
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding-bottom: env(safe-area-inset-bottom);
  z-index: 1000;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.03);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-decoration: none;
  color: #94a3b8;
  gap: 4px;
  flex: 1;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-item.active {
  color: #6366f1;
}

.nav-item svg {
  width: 24px;
  height: 24px;
}

.nav-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.2px;
}

.nav-item.active .nav-label {
  transform: scale(1.05);
}
</style>
