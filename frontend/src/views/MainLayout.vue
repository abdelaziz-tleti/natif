<template>
  <div class="layout-container">
    <Sidebar />
    <main class="main-content" :class="{ 'main-content-expanded': isSidebarCollapsed }">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import Sidebar from '../components/Sidebar.vue';
import { ref, onMounted } from 'vue';

const isSidebarCollapsed = ref(false);

// We could listen to an event from Sidebar if we wanted to manage the main content margin dynamically
// But for now, since Sidebar is fixed, we'll just use a CSS variable or a class.
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
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 0;
  min-height: 100vh;
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
  }
}
</style>

<style>
/* Global content styles to fix padding issues in nested views if needed */
.main-content > div {
  min-height: 100vh;
}
</style>
