<template>
  <div class="p-4 md:p-8 max-w-7xl mx-auto font-sans">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <router-link to="/" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-2 transition-colors">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
          Back to Dashboard
        </router-link>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Shift Management</h1>
        <p class="text-gray-500 mt-1">Schedule and manage employee work shifts.</p>
      </div>
      <button @click="openAddModal" class="w-full md:w-auto btn-primary bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Shift
      </button>
    </header>

    <!-- Today's Overview -->
    <div class="mb-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <p class="text-indigo-100 text-sm font-medium">Today's Schedule</p>
          <p class="text-3xl font-bold mt-1">{{ todayShifts.length }} Shifts</p>
        </div>
        <div class="flex gap-4 text-sm">
          <div class="bg-white/20 rounded-xl px-4 py-2 backdrop-blur-sm">
            <p class="text-indigo-100">Active Now</p>
            <p class="text-xl font-bold">{{ activeShifts.length }}</p>
          </div>
          <div class="bg-white/20 rounded-xl px-4 py-2 backdrop-blur-sm">
            <p class="text-indigo-100">Upcoming</p>
            <p class="text-xl font-bold">{{ upcomingShifts.length }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Week View Selector -->
    <div class="flex items-center gap-4 mb-6">
      <button @click="previousWeek" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
      </button>
      <h2 class="text-lg font-semibold text-gray-800">{{ weekLabel }}</h2>
      <button @click="nextWeek" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
      </button>
      <button @click="goToToday" class="ml-2 px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
        Today
      </button>
    </div>

    <!-- Shifts List -->
    <div class="space-y-4">
      <div v-for="shift in sortedShifts" :key="shift.id" 
           class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex flex-col md:flex-row justify-between gap-4">
          <!-- Left: Employee & Time Info -->
          <div class="flex items-start gap-4">
            <div class="h-12 w-12 shrink-0 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-sm">
              {{ getEmployeeInitial(shift) }}
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ getEmployeeName(shift) }}</h3>
              <p class="text-sm text-gray-500">{{ formatDate(shift.startTime) }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                  {{ formatTime(shift.startTime) }} - {{ formatTime(shift.endTime) }}
                </span>
                <span class="text-xs text-gray-400">{{ getDuration(shift) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Right: Status & Actions -->
          <div class="flex items-center gap-3">
            <span :class="getStatusClass(shift)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium">
              {{ getStatus(shift) }}
            </span>
            <div class="flex gap-1">
              <button @click="openEdit(shift)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
              </button>
              <button @click="deleteShift(shift.id)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="shifts.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-100">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <p class="text-gray-500 font-medium">No shifts scheduled</p>
        <p class="text-gray-400 text-sm mt-1">Create your first shift to get started.</p>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="saveShift">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl leading-6 font-bold text-gray-900">
                  {{ isEditing ? 'Edit Shift' : 'Add New Shift' }}
                </h3>
                <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-500">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              
              <div class="space-y-5">
                <!-- Employee Select -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                  <select v-model="form.user" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                    <option value="">Select an employee...</option>
                    <option v-for="emp in employees" :key="emp.id" :value="emp['@id']">
                      {{ emp.email }}
                    </option>
                  </select>
                </div>

                <!-- Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                  <input v-model="form.date" type="date" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input v-model="form.startTime" type="time" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input v-model="form.endTime" type="time" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 bg-gray-50">
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-4 sm:px-6 flex flex-col sm:flex-row-reverse gap-3">
              <button type="submit" class="w-full sm:w-auto btn-primary justify-center">
                Save Shift
              </button>
              <button @click="closeModal" type="button" class="w-full sm:w-auto btn-secondary justify-center">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';

const shifts = ref([]);
const employees = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const currentWeekStart = ref(getMonday(new Date()));

const form = ref({
  user: '',
  date: '',
  startTime: '09:00',
  endTime: '17:00'
});

// Helpers
function getMonday(d) {
  d = new Date(d);
  const day = d.getDay();
  const diff = d.getDate() - day + (day === 0 ? -6 : 1);
  return new Date(d.setDate(diff));
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
}

function formatTime(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function getDuration(shift) {
  const start = new Date(shift.startTime);
  const end = new Date(shift.endTime);
  const hours = (end - start) / (1000 * 60 * 60);
  return `${hours.toFixed(1)}h`;
}

function getEmployeeName(shift) {
  if (!shift.user) return 'Unknown';
  // user is an IRI like /api/users/2, find matching employee
  const emp = employees.value.find(e => e['@id'] === shift.user);
  return emp ? emp.email : shift.user;
}

function getEmployeeInitial(shift) {
  const name = getEmployeeName(shift);
  return name.charAt(0).toUpperCase();
}

function getStatus(shift) {
  const now = new Date();
  const start = new Date(shift.startTime);
  const end = new Date(shift.endTime);
  
  if (now >= start && now <= end) return 'Active';
  if (now < start) return 'Upcoming';
  return 'Completed';
}

function getStatusClass(shift) {
  const status = getStatus(shift);
  switch(status) {
    case 'Active': return 'bg-green-100 text-green-800';
    case 'Upcoming': return 'bg-blue-100 text-blue-800';
    default: return 'bg-gray-100 text-gray-600';
  }
}

const weekLabel = computed(() => {
  const start = new Date(currentWeekStart.value);
  const end = new Date(start);
  end.setDate(end.getDate() + 6);
  return `${start.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })}`;
});

const sortedShifts = computed(() => {
  return [...shifts.value].sort((a, b) => new Date(a.startTime) - new Date(b.startTime));
});

const todayShifts = computed(() => {
  const today = new Date().toDateString();
  return shifts.value.filter(s => new Date(s.startTime).toDateString() === today);
});

const activeShifts = computed(() => {
  const now = new Date();
  return shifts.value.filter(s => {
    const start = new Date(s.startTime);
    const end = new Date(s.endTime);
    return now >= start && now <= end;
  });
});

const upcomingShifts = computed(() => {
  const now = new Date();
  return shifts.value.filter(s => new Date(s.startTime) > now);
});

// Navigation
function previousWeek() {
  const d = new Date(currentWeekStart.value);
  d.setDate(d.getDate() - 7);
  currentWeekStart.value = d;
}

function nextWeek() {
  const d = new Date(currentWeekStart.value);
  d.setDate(d.getDate() + 7);
  currentWeekStart.value = d;
}

function goToToday() {
  currentWeekStart.value = getMonday(new Date());
}

// API
async function fetchShifts() {
  try {
    const response = await api.get('/shifts');
    shifts.value = response.data['hydra:member'] || response.data.member || [];
  } catch (e) {
    console.error('Failed to fetch shifts', e);
  }
}

async function fetchEmployees() {
  try {
    const response = await api.get('/users');
    employees.value = response.data['hydra:member'] || response.data.member || [];
  } catch (e) {
    console.error('Failed to fetch employees', e);
  }
}

function openAddModal() {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    user: '',
    date: new Date().toISOString().split('T')[0],
    startTime: '09:00',
    endTime: '17:00'
  };
  showModal.value = true;
}

function openEdit(shift) {
  isEditing.value = true;
  editingId.value = shift.id;
  
  const startDate = new Date(shift.startTime);
  const endDate = new Date(shift.endTime);
  
  form.value = {
    user: shift.user,
    date: startDate.toISOString().split('T')[0],
    startTime: startDate.toTimeString().slice(0, 5),
    endTime: endDate.toTimeString().slice(0, 5)
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveShift() {
  try {
    // Build ISO date-time strings
    const startDateTime = `${form.value.date}T${form.value.startTime}:00`;
    const endDateTime = `${form.value.date}T${form.value.endTime}:00`;
    
    const payload = {
      user: form.value.user,
      startTime: startDateTime,
      endTime: endDateTime
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
    alert('Failed to save shift. Please try again.');
  }
}

async function deleteShift(id) {
  if (!confirm('Are you sure you want to delete this shift?')) return;
  try {
    await api.delete(`/shifts/${id}`);
    await fetchShifts();
  } catch (e) {
    console.error('Delete failed', e);
  }
}

onMounted(() => {
  fetchShifts();
  fetchEmployees();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
