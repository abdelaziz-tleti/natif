<template>
  <AppLayout>
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Inventaire</h1>
        <p class="page-subtitle">Gestion du stock</p>
      </div>
      <button @click="showAddModal = true" class="btn-fab">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
      </button>
    </div>

    <!-- Employee Stock Alerts (Modern Checklist) -->
    <transition name="fade">
        <div v-if="pendingAlerts.length > 0" class="mb-8 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-gray-900 font-bold text-lg flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600 text-xs font-black">
                            {{ pendingAlerts.length }}
                        </span>
                        Action Required
                    </h3>
                    <p class="text-gray-500 text-xs">Verify and resolve employee reports</p>
                </div>
            </div>

            <div class="grid gap-3">
                <div v-for="alert in pendingAlerts" :key="alert.id || alert['@id']" 
                     class="group relative bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-indigo-200 transition-all duration-300">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="h-12 w-12 shrink-0 bg-gray-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                <span class="text-xl font-black text-gray-400 group-hover:text-indigo-600">
                                    {{ getProductName(alert).charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-gray-900 truncate leading-tight">{{ getProductName(alert) }}</h4>
                                    <span class="px-1.5 py-0.5 rounded-md bg-red-50 text-[10px] font-bold text-red-600 uppercase tracking-wider">Reported</span>
                                </div>
                                <p class="text-sm text-gray-500 truncate mt-0.5 italic">"{{ alert.message || 'No specific comment provided' }}"</p>
                                <p class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ formatAlertDate(alert.createdAt) }}
                                </p>
                            </div>
                        </div>

                        <!-- Modern Action Toggle -->
                        <button 
                            @click.stop.prevent="resolveAlert(alert)"
                            :disabled="resolvingAlerts[alert.id || alert['@id']]"
                            class="relative h-12 w-12 shrink-0 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 active:scale-90 transition-all duration-300 disabled:opacity-50"
                            title="Mark as Resolved"
                        >
                            <svg v-if="!resolvingAlerts[alert.id || alert['@id']]" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            
                            <!-- Success indicator ring on hover -->
                            <div class="absolute inset-0 rounded-2xl border-2 border-transparent scale-110 group-hover:border-emerald-200 group-hover:scale-100 transition-all duration-300"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Stock Alerts (Low Stock Warning) -->
    <transition name="fade">
        <div v-if="lowStockProducts.length > 0" class="mb-8 bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row items-start gap-4 shadow-sm animate-pulse-slow">
            <div class="bg-amber-100 p-2 rounded-full shrink-0">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-amber-800 font-semibold text-lg">Attention Needed</h3>
                <p class="text-amber-700 mt-1 text-sm">
                    You have <strong>{{ lowStockProducts.length }} products</strong> running low on stock. Please restock soon to avoid shortages.
                </p>
                <!-- Quick Filter (Optional future feature) -->
            </div>
        </div>
    </transition>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50/50">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product Info</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50/50 transition-colors">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 shrink-0 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-lg select-none">
                        {{ product.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900">{{ product.name }}</div>
                        <div class="text-xs text-gray-500">{{ product.supplier || 'No Supplier' }}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-medium">{{ product.quantity }} <span class="text-gray-400 text-xs font-normal">units</span></div>
                <div class="text-xs text-gray-400 mt-0.5">Min: {{ product.minThreshold }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="product.quantity == 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Out of Stock
              </span>
              <span v-else-if="product.quantity <= product.minThreshold" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                Low Stock
              </span>
              <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                In Stock
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <div class="flex items-center justify-end gap-2">
                    <button @click="openEdit(product)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button @click="deleteProduct(product.id)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </td>
          </tr>
          <tr v-if="products.length === 0">
             <td colspan="4" class="px-6 py-12 text-center">
                 <div class="flex flex-col items-center justify-center text-gray-400">
                     <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                     <p class="text-base font-medium text-gray-500">No products found</p>
                     <p class="text-sm">Get started by adding your first product.</p>
                 </div>
             </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4">
        <div v-for="product in products" :key="product.id" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 active:scale-[0.99] transition-transform">
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 shrink-0 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-lg">
                        {{ product.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
                        <p class="text-xs text-gray-500">{{ product.supplier || 'No Supplier' }}</p>
                    </div>
                </div>
                <div class="flex gap-1">
                    <button @click="openEdit(product)" class="p-2 text-gray-400 hover:text-indigo-600 active:bg-indigo-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button @click="deleteProduct(product.id)" class="p-2 text-gray-400 hover:text-red-600 active:bg-red-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                <div class="flex flex-col">
                    <span class="text-xs uppercase font-semibold text-gray-400 tracking-wide">Quantity</span>
                    <span class="text-xl font-bold text-gray-800">{{ product.quantity }}</span>
                </div>
                 <div class="h-8 w-px bg-gray-200"></div>
                <div class="flex flex-col items-center">
                    <span class="text-xs uppercase font-semibold text-gray-400 tracking-wide">Threshold</span>
                    <span class="text-sm font-semibold text-gray-600">{{ product.minThreshold }}</span>
                </div>
                 <div class="h-8 w-px bg-gray-200"></div>
                <div class="flex flex-col items-end">
                    <span class="text-xs uppercase font-semibold text-gray-400 tracking-wide mb-1">Status</span>
                    <span v-if="product.quantity == 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        Empty
                    </span>
                    <span v-else-if="product.quantity <= product.minThreshold" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                        Low
                    </span>
                    <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                        Good
                    </span>
                </div>
            </div>
        </div>
        
        <div v-if="products.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100 border-dashed">
            <p class="text-gray-500">No products found.</p>
        </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="saveProduct">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="flex justify-between items-center mb-6">
                   <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                    {{ isEditing ? 'Edit Product' : 'Add New Product' }}
                  </h3>
                  <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-500">
                      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
              </div>
              
              <div class="space-y-5">
                <div v-if="authStore.isAdmin && restaurants.length > 0">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Restaurant</label>
                  <select v-model="form.restaurant" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                    <option value="">Select a restaurant...</option>
                    <option v-for="res in restaurants" :key="res.id" :value="res['@id']">
                      {{ res.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                  <input v-model="form.name" type="text" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50" placeholder="e.g. Tomatoes">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                  <input v-model="form.supplier" type="text" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50" placeholder="e.g. Metro Inc.">
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Quantity</label>
                    <input v-model.number="form.quantity" type="number" min="0" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alert Threshold</label>
                    <input v-model.number="form.minThreshold" type="number" min="0" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                  </div>
                </div>
              </div>

            </div>
            <div class="bg-gray-50 px-4 py-4 sm:px-6 flex flex-col sm:flex-row-reverse gap-3">
              <button type="submit" class="w-full sm:w-auto btn-primary justify-center">
                Save Product
              </button>
              <button @click="closeModal" type="button" class="w-full sm:w-auto btn-secondary justify-center">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '../../components/AppLayout.vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';

const products = ref([]);
const restaurants = ref([]);
const pendingAlerts = ref([]);
const resolvingAlerts = ref({});
const showAddModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const authStore = useAuthStore();

const form = ref({
  name: '',
  quantity: 0,
  minThreshold: 5,
  supplier: '',
  restaurant: ''
});

const lowStockProducts = computed(() => {
  return products.value.filter(p => p.quantity <= p.minThreshold);
});

const fetchProducts = async () => {
    try {
        // In a real app, this should confirm we only fetch for the user's restaurant
        // For MVP, we'll rely on backend filtering (Work In Progress) or filter client side if needed
        const response = await api.get('/products');
        console.log('API Response:', response.data);
        // Handle different API Platform formats (hydra:member, member, or plain array)
        products.value = response.data['hydra:member'] || response.data.member || response.data;
    } catch (e) {
        console.error("Failed to fetch products", e);
    }
};

const fetchRestaurants = async () => {
    if (!authStore.isAdmin) return;
    try {
        const response = await api.get('/restaurants');
        restaurants.value = response.data['hydra:member'] || [];
    } catch (e) {
        console.error("Failed to fetch restaurants", e);
    }
};

const closeModal = () => {
  showAddModal.value = false;
  isEditing.value = false;
  editingId.value = null;
  form.value = { name: '', quantity: 0, minThreshold: 5, supplier: '', restaurant: '' };
};

const openEdit = (product) => {
  editingId.value = product.id;
  isEditing.value = true;
  form.value = {
    name: product.name,
    quantity: product.quantity,
    minThreshold: product.minThreshold,
    supplier: product.supplier || '',
    restaurant: product.restaurant?.['@id'] || product.restaurant || ''
  };
  showAddModal.value = true;
};

const saveProduct = async () => {
  try {
    const payload = { ...form.value };
    
    // Clear restaurant if empty string (for non-admins or if not selected)
    if (!payload.restaurant) delete payload.restaurant;

    if (isEditing.value) {
      await api.put(`/products/${editingId.value}`, payload);
    } else {
      await api.post('/products', payload);
    }
    
    await fetchProducts();
    closeModal();
  } catch (e) {
    console.error("Save failed", e);
    const errorMsg = e.response?.data?.['hydra:description'] || e.response?.data?.message || "Failed to save product.";
    alert("Error: " + errorMsg);
  }
};

const deleteProduct = async (id) => {
    if(!confirm('Are you sure?')) return;
    try {
        await api.delete(`/products/${id}`);
        await fetchProducts();
    } catch (e) {
        console.error(e);
    }
}

// Stock Alerts functions
const fetchStockAlerts = async () => {
    try {
        const response = await api.get('/stock_alerts?resolved=false');
        pendingAlerts.value = response.data['hydra:member'] || response.data.member || [];
    } catch (e) {
        console.error('Failed to fetch stock alerts', e);
    }
};

const getProductName = (alert) => {
    if (!alert.product) return 'Unknown';
    // product is an IRI like /api/products/2, find matching product
    const prod = products.value.find(p => p['@id'] === alert.product);
    return prod ? prod.name : 'Product';
};

const formatAlertDate = (dateStr) => {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = (now - date) / 1000 / 60; // in minutes
    
    if (diff < 60) return `${Math.round(diff)}m ago`;
    if (diff < 1440) return `${Math.round(diff / 60)}h ago`;
    return date.toLocaleDateString('fr-FR');
};

const resolveAlert = async (stockAlert) => {
    if (!stockAlert) return;
    
    // Extract ID safely
    const irid = stockAlert['@id'] || '';
    const alertId = stockAlert.id || irid.split('/').pop();
    
    if (!alertId || alertId === irid) {
        console.error('Could not determine alert ID', stockAlert);
        return;
    }

    const idKey = stockAlert.id || irid;
    resolvingAlerts.value[idKey] = true;
    
    try {
        await api.patch(`/stock_alerts/${alertId}`, {
            resolved: true,
            status: 'resolved'
        }, {
            headers: {
                'Content-Type': 'application/merge-patch+json'
            }
        });
        
        // Success! Refresh the list
        await fetchStockAlerts();
    } catch (e) {
        console.error('Failed to resolve alert', e);
        window.alert('Error: ' + (e.response?.data?.['hydra:description'] || e.message));
    } finally {
        delete resolvingAlerts.value[idKey];
    }
};

onMounted(() => {
    fetchProducts();
    fetchStockAlerts();
    fetchRestaurants();
});
</script>

<style scoped>
/* Custom animations */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

