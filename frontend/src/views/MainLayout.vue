<template>
  <div class="layout-container">
    <!-- Main Content - Full Width -->
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Mobile Bottom Navigation -->
    <BottomNav />
  </div>
</template>

<script setup>
import BottomNav from '../components/BottomNav.vue';
import { watch } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
</script>

<style scoped>
.layout-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}

.main-content {
  flex: 1;
  width: 100%;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    padding-bottom: 80px; /* Space for bottom nav */
  }
}
</style>

<style>
/* Global content styles */
.main-content > div {
  min-height: 100vh;
}
</style>
