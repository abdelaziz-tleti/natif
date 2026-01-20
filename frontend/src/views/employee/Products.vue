<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Alertes Stock</h1>
        <p class="page-subtitle">Signaler les produits manquants</p>
      </div>
    </div>

    <!-- Stock Alerts -->
    <div class="space-y-3">
      <div v-for="product in lowStockProducts" :key="product.id" 
           class="card bg-amber-50/50 border-amber-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900">{{ product.name }}</h3>
              <p class="text-sm text-gray-500">Stock: {{ product.quantity }} {{ product.unit }}</p>
            </div>
          </div>
          <button 
            @click="createAlert(product)"
            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm active:scale-95 transition-all"
          >
            Signaler
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="lowStockProducts.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-100">
        <div class="bg-emerald-50 h-20 w-20 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900">Tout est OK !</h3>
        <p class="text-gray-500 max-w-xs mx-auto mt-1">Aucune alerte de stock pour le moment.</p>
      </div>
    </div>

    <!-- Recent Alerts -->
    <div class="section mt-8">
      <h2 class="section-title">Mes Alertes</h2>
      <div class="space-y-2">
        <div v-for="alert in myAlerts" :key="alert.id" 
             class="card bg-gray-50">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-bold text-gray-900">{{ alert.product?.name || 'Produit' }}</h4>
              <p class="text-sm text-gray-500">{{ formatDate(alert.createdAt) }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold"
                  :class="alert.isResolved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
              {{ alert.isResolved ? 'Resolved' : 'En cours' }}
            </span>
          </div>
        </div>

        <div v-if="myAlerts.length === 0" class="text-center py-8 text-gray-400">
          <p class="text-sm">Aucune alerte envoyée</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '../../components/AppLayout.vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';

const products = ref([]);
const alerts = ref([]);
const authStore = useAuthStore();

const lowStockProducts = computed(() => {
  return products.value.filter(p => p.quantity <= p.minThreshold);
});

const myAlerts = computed(() => {
  return alerts.value.filter(a => a.reportedBy?.['@id'] === authStore.user?.['@id']);
});

const fetchProducts = async () => {
  try {
    const response = await api.get('/products');
    products.value = response.data['hydra:member'] || [];
  } catch (e) {
    console.error('Failed to fetch products', e);
  }
};

const fetchAlerts = async () => {
  try {
    const response = await api.get('/stock_alerts?order[createdAt]=desc');
    alerts.value = response.data['hydra:member'] || [];
  } catch (e) {
    console.error('Failed to fetch alerts', e);
  }
};

const createAlert = async (product) => {
  try {
    await api.post('/stock_alerts', {
      product: product['@id'],
      message: `Stock bas pour ${product.name}`
    });
    alert(`Alerte envoyée pour ${product.name}`);
    await fetchAlerts();
  } catch (e) {
    console.error('Failed to create alert', e);
    alert('Erreur lors de l\'envoi de l\'alerte');
  }
};

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('fr-FR', { 
    day: 'numeric', 
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  fetchProducts();
  fetchAlerts();
});
</script>
