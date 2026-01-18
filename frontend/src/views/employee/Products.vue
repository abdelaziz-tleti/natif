<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto font-sans">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <router-link to="/" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-2 transition-colors">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
          Back to Dashboard
        </router-link>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Stock Overview</h1>
        <p class="text-gray-500 mt-1">View products and report stock issues.</p>
      </div>
    </header>

    <!-- Search Bar -->
    <div class="mb-6">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search products..." 
          class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
        >
      </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div 
        v-for="product in filteredProducts" 
        :key="product.id" 
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 shrink-0 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-sm">
                {{ product.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
                <p class="text-xs text-gray-500">{{ product.supplier || 'No Supplier' }}</p>
              </div>
            </div>
          </div>
          
          <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 mb-3">
            <div class="text-center">
              <p class="text-xs uppercase font-semibold text-gray-400 tracking-wide">Stock</p>
              <p class="text-2xl font-bold" :class="getStockColor(product)">{{ product.quantity }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs uppercase font-semibold text-gray-400 tracking-wide">Min</p>
              <p class="text-lg font-semibold text-gray-600">{{ product.minThreshold }}</p>
            </div>
            <div class="text-center">
              <span :class="getStatusBadgeClass(product)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                {{ getStatusText(product) }}
              </span>
            </div>
          </div>

          <!-- Report Button -->
          <button 
            @click="openReportModal(product)"
            :disabled="hasActiveAlert(product)"
            class="w-full py-2.5 px-4 rounded-lg font-medium text-sm transition-all flex items-center justify-center gap-2"
            :class="hasActiveAlert(product) 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            {{ hasActiveAlert(product) ? 'Alert Sent' : 'Report Stock Issue' }}
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredProducts.length === 0" class="col-span-full text-center py-16 bg-white rounded-xl border border-gray-100">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <p class="text-gray-500 font-medium">No products found</p>
        <p class="text-gray-400 text-sm mt-1">Try adjusting your search.</p>
      </div>
    </div>

    <!-- Report Modal -->
    <div v-if="showReportModal" 
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.5);">
      
      <!-- Modal Content -->
      <div style="background: white; border-radius: 16px; max-width: 400px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <form @submit.prevent="submitReport">
          <div style="padding: 24px;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
              <h3 style="font-size: 20px; font-weight: 700; color: #111; margin: 0;">
                Report Stock Issue
              </h3>
              <button type="button" @click="closeReportModal" style="background: none; border: none; cursor: pointer; padding: 4px;">
                <svg style="width: 24px; height: 24px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Product Info -->
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                  <svg style="width: 20px; height: 20px; color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                </div>
                <div>
                  <p style="font-weight: 600; color: #92400e; margin: 0;">{{ selectedProduct?.name }}</p>
                  <p style="font-size: 14px; color: #b45309; margin: 4px 0 0 0;">Current stock: {{ selectedProduct?.quantity }}</p>
                </div>
              </div>
            </div>
            
            <!-- Message Field -->
            <div>
              <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">Message (optional)</label>
              <textarea 
                v-model="reportMessage" 
                rows="3"
                placeholder="e.g. We're almost out and expecting a busy weekend..."
                style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical; box-sizing: border-box;"
              ></textarea>
            </div>
          </div>

          <!-- Footer Buttons -->
          <div style="background: #f9fafb; padding: 16px 24px; display: flex; flex-direction: column; gap: 12px;">
            <button type="submit" style="width: 100%; padding: 12px 16px; background: #f59e0b; color: white; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px;">
              <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
              </svg>
              Send Alert to Manager
            </button>
            <button type="button" @click="closeReportModal" style="width: 100%; padding: 12px 16px; background: white; color: #374151; font-weight: 500; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer; font-size: 14px;">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Toast -->
    <transition name="slide-up">
      <div v-if="showSuccessToast" class="fixed bottom-6 right-6 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Alert sent successfully!
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';

const products = ref([]);
const stockAlerts = ref([]);
const searchQuery = ref('');
const showReportModal = ref(false);
const selectedProduct = ref(null);
const reportMessage = ref('');
const showSuccessToast = ref(false);

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(p => 
    p.name.toLowerCase().includes(query) || 
    (p.supplier && p.supplier.toLowerCase().includes(query))
  );
});

function getStockColor(product) {
  if (product.quantity === 0) return 'text-red-600';
  if (product.quantity <= product.minThreshold) return 'text-amber-600';
  return 'text-gray-900';
}

function getStatusText(product) {
  if (product.quantity === 0) return 'Empty';
  if (product.quantity <= product.minThreshold) return 'Low';
  return 'OK';
}

function getStatusBadgeClass(product) {
  if (product.quantity === 0) return 'bg-red-100 text-red-800';
  if (product.quantity <= product.minThreshold) return 'bg-amber-100 text-amber-800';
  return 'bg-emerald-100 text-emerald-800';
}

function hasActiveAlert(product) {
  return stockAlerts.value.some(a => 
    a.product === product['@id'] && !a.resolved
  );
}

function openReportModal(product) {
  console.log('Opening modal for product:', product);
  selectedProduct.value = product;
  reportMessage.value = '';
  showReportModal.value = true;
}

function closeReportModal() {
  showReportModal.value = false;
  selectedProduct.value = null;
}

async function submitReport() {
  try {
    await api.post('/stock_alerts', {
      product: selectedProduct.value['@id'],
      message: reportMessage.value || null
    });
    
    closeReportModal();
    showSuccessToast.value = true;
    setTimeout(() => { showSuccessToast.value = false; }, 3000);
    
    await fetchStockAlerts();
  } catch (e) {
    console.error('Failed to submit report', e);
    alert('Failed to send alert. Please try again.');
  }
}

async function fetchProducts() {
  try {
    const response = await api.get('/products');
    products.value = response.data['hydra:member'] || response.data.member || [];
  } catch (e) {
    console.error('Failed to fetch products', e);
  }
}

async function fetchStockAlerts() {
  try {
    const response = await api.get('/stock_alerts?resolved=false');
    stockAlerts.value = response.data['hydra:member'] || response.data.member || [];
  } catch (e) {
    console.error('Failed to fetch stock alerts', e);
  }
}

onMounted(() => {
  fetchProducts();
  fetchStockAlerts();
});
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.3s ease;
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
