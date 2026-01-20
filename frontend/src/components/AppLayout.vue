<template>
  <div class="app-layout">
    <!-- Mobile Navigation Component -->
    <MobileNav :show-fab="showFab" @fab-click="handleFabClick" />

    <!-- Main Content Area -->
    <main class="app-main">
      <slot></slot>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import MobileNav from './MobileNav.vue';

const route = useRoute();

const showFab = computed(() => {
  // Montrer le FAB sur certaines pages spécifiques
  const fabPages = ['/manager/products', '/manager/shifts', '/manager/staff'];
  return fabPages.includes(route.path);
});

const handleFabClick = () => {
  // Émettre un événement global pour ouvrir le modal d'ajout
  window.dispatchEvent(new CustomEvent('fab-clicked'));
};
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
}

.app-main {
  padding: 80px 20px 100px;
  max-width: 100%;
}

@media (min-width: 768px) {
  .app-main {
    max-width: 1600px;
    margin: 0 auto;
  }
}
</style>
