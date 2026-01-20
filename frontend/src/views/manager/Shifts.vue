<template>
  <AppLayout>
    <!-- Compact Header - Single Line -->
    <div class="flex items-center justify-between mb-3">
      <h1 class="text-lg font-black text-gray-900">Planning</h1>
      
      <!-- Week Navigation - Compact -->
      <div class="flex items-center gap-1 bg-white px-2 py-1 rounded-lg border border-gray-200">
        <button @click="previousWeek" class="p-1 text-gray-400 active:scale-90">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <span class="text-[10px] font-bold text-gray-600 px-2">{{ weekLabel }}</span>
        <button @click="nextWeek" class="p-1 text-gray-400 active:scale-90">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        </button>
      </div>
      
      <button @click="openAddModal" class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center active:scale-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
      </button>
    </div>

    <!-- Week View - All Days Visible No Scroll -->
    <div class="space-y-1">
      <div v-for="day in weekDays" :key="day.date" class="bg-white rounded-lg p-1.5 border" :class="isToday(day.date) ? 'border-indigo-400 bg-indigo-50/30' : 'border-gray-200'">
        <!-- Day Header - Minimal -->
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold uppercase w-8" :class="isToday(day.date) ? 'text-indigo-600' : 'text-gray-400'">
              {{ day.name.substring(0, 3) }}
            </span>
            <span class="text-sm font-black" :class="isToday(day.date) ? 'text-indigo-600' : 'text-gray-900'">
              {{ new Date(day.date).getDate() }}
            </span>
          </div>
        </div>

        <!-- Shifts - Inline Compact -->
        <div class="flex gap-1">
          <!-- Morning -->
          <div class="flex-1">
            <div v-if="getShiftsForDayAndType(day.date, 'morning').length > 0">
              <div v-for="shift in getShiftsForDayAndType(day.date, 'morning')" :key="shift.id" 
                   @click="openEdit(shift)"
                   class="bg-amber-100/50 px-1.5 py-1 rounded border border-amber-200 cursor-pointer active:scale-95">
                <div class="flex gap-0.5 mb-0.5">
                  <div v-for="uIri in shift.users.slice(0, 3)" :key="uIri" 
                       :class="getEmployeeColor(uIri)"
                       class="h-0.5 flex-1 rounded">
                  </div>
                </div>
                <div class="text-[9px] font-bold text-gray-700">☀️ {{ shift.users.length }}p</div>
              </div>
            </div>
            <div v-else class="h-8 border border-dashed border-gray-200 rounded flex items-center justify-center" @click="openQuickAdd(day.date, 'morning')">
              <span class="text-[10px] text-gray-300">☀️</span>
            </div>
          </div>

          <!-- Evening -->
          <div class="flex-1">
            <div v-if="getShiftsForDayAndType(day.date, 'evening').length > 0">
              <div v-for="shift in getShiftsForDayAndType(day.date, 'evening')" :key="shift.id" 
                   @click="openEdit(shift)"
                   class="bg-indigo-100/50 px-1.5 py-1 rounded border border-indigo-200 cursor-pointer active:scale-95">
                <div class="flex gap-0.5 mb-0.5">
                  <div v-for="uIri in shift.users.slice(0, 3)" :key="uIri" 
                       :class="getEmployeeColor(uIri)"
                       class="h-0.5 flex-1 rounded">
                  </div>
                </div>
                <div class="text-[9px] font-bold text-gray-700">🌙 {{ shift.users.length }}p</div>
              </div>
            </div>
            <div v-else class="h-8 border border-dashed border-gray-200 rounded flex items-center justify-center" @click="openQuickAdd(day.date, 'evening')">
              <span class="text-[10px] text-gray-300">🌙</span>
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- Empty Global State -->
    <div v-if="shifts.length === 0 && !loading" class="mt-12 text-center py-20 bg-white rounded-[40px] border-2 border-dashed border-gray-100">
        <div class="bg-gray-50 h-24 w-24 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900">No sessions planned</h3>
        <p class="text-gray-500 max-w-xs mx-auto mt-2">Start organizing your team by adding sessions for this week.</p>
    </div>

    <!-- Session Modal (Add/Edit) -->
    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-[2000] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" @click="closeModal"></div>
        
        <div class="relative bg-white rounded-[40px] w-full max-w-xl shadow-2xl overflow-hidden transform transition-all">
          <div class="p-8 md:p-10">
            <div class="flex justify-between items-start mb-8">
              <div>
                <h2 class="text-3xl font-black text-gray-900 leading-tight">
                  {{ isEditing ? 'Edit Session' : 'New Session' }}
                </h2>
                <p class="text-gray-400 font-medium mt-1">Select time and assign staff.</p>
              </div>
              <button @click="closeModal" class="p-3 text-gray-400 hover:bg-gray-50 rounded-2xl transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            <form @submit.prevent="saveShift" class="space-y-6">
              <!-- Admin: Restaurant Select -->
              <div v-if="authStore.isAdmin && restaurants.length > 0">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-2">Restaurant</label>
                <select v-model="form.restaurant" required class="w-full px-6 py-4 bg-gray-50 border-0 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold text-gray-900">
                  <option value="">Select a restaurant...</option>
                  <option v-for="res in restaurants" :key="res.id" :value="res['@id']">
                    {{ res.name }}
                  </option>
                </select>
              </div>
              
              <!-- Session Type -->
              <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="setSessionType('morning')" 
                        :class="form.type === 'morning' ? 'bg-amber-500 text-white shadow-lg shadow-amber-200' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'"
                        class="px-4 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                  Morning Slot
                </button>
                <button type="button" @click="setSessionType('evening')" 
                        :class="form.type === 'evening' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-200' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'"
                        class="px-4 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                  Evening Slot
                </button>
              </div>

              <!-- Date Picker -->
              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-2">Date</label>
                <input v-model="form.date" type="date" required 
                       class="w-full px-6 py-4 bg-gray-50 border-0 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold text-gray-900" />
              </div>

              <!-- Time Inputs -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-2">Starts At</label>
                  <input v-model="form.startTime" type="time" required 
                         class="w-full px-6 py-4 bg-gray-50 border-0 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold text-gray-900" />
                </div>
                <div>
                  <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-2">Ends At</label>
                  <input v-model="form.endTime" type="time" required 
                         class="w-full px-6 py-4 bg-gray-50 border-0 rounded-2xl focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold text-gray-900" />
                </div>
              </div>

              <!-- Multi-Staff Selection -->
              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-2">Assign Team</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                  <div v-for="emp in employees" :key="emp.id" 
                       @click="toggleEmployee(emp['@id'])"
                       :class="form.users.includes(emp['@id']) ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-100 bg-white hover:border-gray-300'"
                       class="flex items-center gap-2 p-3 rounded-2xl border cursor-pointer transition-all group">
                    <div :class="[getEmployeeColor(emp['@id']), form.users.includes(emp['@id']) ? 'scale-125' : 'scale-100']" class="w-3 h-3 rounded-full transition-transform"></div>
                    <span class="text-xs font-bold truncate" :class="form.users.includes(emp['@id']) ? 'text-indigo-700' : 'text-gray-600'">
                        {{ emp.firstName || emp.phone.slice(-4) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="pt-4">
                <button type="submit" :disabled="form.users.length === 0 || loading" 
                        class="w-full py-5 bg-gray-900 hover:bg-black text-white rounded-3xl font-black shadow-2xl transition-all disabled:opacity-30 flex items-center justify-center gap-2">
                  <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  {{ isEditing ? 'Update Planning' : 'Confirm Session' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '../../components/AppLayout.vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const shifts = ref([]);
const restaurants = ref([]);
const employees = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const loading = ref(false);
const currentWeekStart = ref(getMonday(new Date()));

const form = ref({
  users: [],
  date: '',
  startTime: '08:00',
  endTime: '15:00',
  type: 'morning',
  restaurant: ''
});

// Helpers
function getMonday(d) {
  d = new Date(d);
  const day = d.getDay();
  const diff = d.getDate() - day + (day === 0 ? -6 : 1);
  const result = new Date(d.setDate(diff));
  result.setHours(0, 0, 0, 0);
  return result;
}

const weekDays = computed(() => {
  const days = [];
  const start = new Date(currentWeekStart.value);
  const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  
  for (let i = 0; i < 7; i++) {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    days.push({
      name: dayNames[i],
      date: d.toISOString().split('T')[0]
    });
  }
  return days;
});

const weekLabel = computed(() => {
  const start = new Date(currentWeekStart.value);
  const end = new Date(start);
  end.setDate(end.getDate() + 6);
  return `${start.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })}`;
});

const isToday = (dateStr) => {
  return new Date().toISOString().split('T')[0] === dateStr;
};

const getShiftsForDayAndType = (dateStr, type) => {
    return shifts.value.filter(s => {
        const sDate = new Date(s.startTime).toISOString().split('T')[0];
        return sDate === dateStr && s.type === type;
    });
};

const getEmployeeColor = (iri) => {
    const colors = [
        'bg-blue-500', 'bg-emerald-500', 'bg-violet-500', 
        'bg-amber-500', 'bg-rose-500', 'bg-cyan-500',
        'bg-orange-500', 'bg-indigo-500', 'bg-fuchsia-500'
    ];
    // Use last digit of ID for color
    const idStr = String(iri).split('/').pop() || '0';
    const id = parseInt(idStr.replace(/[^0-9]/g, '')) || 0;
    return colors[id % colors.length];
};

const getEmployeeNameByIri = (iri) => {
    const emp = employees.value.find(e => e['@id'] === iri);
    return emp ? (emp.firstName || emp.phone) : 'Staff';
};

const formatTime = (isoStr) => {
    return new Date(isoStr).toTimeString().slice(0, 5);
};

// Actions
const previousWeek = () => {
    const d = new Date(currentWeekStart.value);
    d.setDate(d.getDate() - 7);
    currentWeekStart.value = d;
    fetchShifts();
};

const nextWeek = () => {
    const d = new Date(currentWeekStart.value);
    d.setDate(d.getDate() + 7);
    currentWeekStart.value = d;
    fetchShifts();
};

const setSessionType = (type) => {
    form.value.type = type;
    if (type === 'morning') {
        form.value.startTime = '08:00';
        form.value.endTime = '15:00';
    } else {
        form.value.startTime = '17:00';
        form.value.endTime = '00:00';
    }
};

const toggleEmployee = (iri) => {
    const idx = form.value.users.indexOf(iri);
    if (idx > -1) form.value.users.splice(idx, 1);
    else form.value.users.push(iri);
};

const openAddModal = () => {
    isEditing.value = false;
    form.value = {
        users: [],
        date: new Date().toISOString().split('T')[0],
        startTime: '08:00',
        endTime: '15:00',
        type: 'morning',
        restaurant: ''
    };
    showModal.value = true;
};

const openQuickAdd = (date, type) => {
    openAddModal();
    form.value.date = date;
    setSessionType(type);
};

const openEdit = (shift) => {
    isEditing.value = true;
    editingId.value = shift.id;
    const start = new Date(shift.startTime);
    const end = new Date(shift.endTime);
    
    form.value = {
        users: [...shift.users],
        date: start.toISOString().split('T')[0],
        startTime: start.toTimeString().slice(0, 5),
        endTime: end.toTimeString().slice(0, 5),
        type: shift.type,
        restaurant: shift.restaurant?.['@id'] || shift.restaurant || ''
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

// API
const fetchShifts = async () => {
    loading.value = true;
    try {
        // Fetch shifts for current week range
        const start = new Date(currentWeekStart.value).toISOString();
        const end = new Date(currentWeekStart.value);
        end.setDate(end.getDate() + 7);
        const endIso = end.toISOString();
        
        console.log('Fetching shifts from', start, 'to', endIso);
        const response = await api.get(`/shifts?startTime[after]=${start}&startTime[before]=${endIso}`);
        console.log('Shifts response:', response.data);
        shifts.value = response.data.member || response.data['hydra:member'] || [];
        console.log('Shifts loaded:', shifts.value.length);
    } catch (e) {
        console.error('Failed to fetch shifts', e);
        console.error('Error details:', e.response?.data);
    } finally {
        loading.value = false;
    }
};

const fetchEmployees = async () => {
    try {
        const response = await api.get('/users');
        employees.value = response.data['hydra:member'] || [];
    } catch (e) {
        console.error('Failed to fetch employees', e);
    }
};

const saveShift = async () => {
    loading.value = true;
    try {
        const startIso = `${form.value.date}T${form.value.startTime}:00`;
        const endIso = `${form.value.date}T${form.value.endTime}:00`;
        
        const payload = {
            users: form.value.users,
            startTime: startIso,
            endTime: endIso,
            type: form.value.type,
            restaurant: form.value.restaurant || undefined
        };

        if (isEditing.value) {
            await api.put(`/shifts/${editingId.value}`, payload);
        } else {
            await api.post('/shifts', payload);
        }
        
        await fetchShifts();
        closeModal();
    } catch (e) {
        console.error('Save failed', e);
        alert(e.response?.data?.['hydra:description'] || 'Failed to save session');
    } finally {
        loading.value = false;
    }
};

const deleteShift = async (id) => {
    if (!confirm('Cancel this session?')) return;
    try {
        await api.delete(`/shifts/${id}`);
        await fetchShifts();
    } catch (e) {
        console.error('Delete failed', e);
    }
};

const goToToday = () => {
  currentWeekStart.value = getMonday(new Date());
  fetchShifts();
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

onMounted(() => {
    fetchEmployees();
    fetchShifts();
    fetchRestaurants();
});
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: translateY(40px) scale(0.95);
}

/* Custom Scrollbar for staff list if needed */
.overflow-y-auto {
  scrollbar-width: none;
}
.overflow-y-auto::-webkit-scrollbar {
  display: none;
}
</style>
