<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Mon Planning</h1>
        <p class="page-subtitle">Horaires de la semaine</p>
      </div>
    </div>

    <!-- Calendar Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="col-span-2 card">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          Planning Hebdomadaire
        </h3>
        
        <div class="space-y-3">
          <div v-for="day in weekDays" :key="day.date" 
               class="flex items-center p-3 rounded-xl transition-colors"
               :class="isToday(day.date) ? 'bg-indigo-50 ring-1 ring-indigo-100' : 'hover:bg-gray-50'">
            <div class="w-16 text-center">
              <p class="text-[10px] uppercase font-bold text-gray-400">{{ day.label }}</p>
              <p class="text-lg font-black" :class="isToday(day.date) ? 'text-indigo-600' : 'text-gray-700'">{{ day.dayNum }}</p>
            </div>
            <div class="flex-1 ml-4">
              <div v-if="getShiftsForDay(day.date).length > 0" class="flex flex-wrap gap-2">
                <div v-for="shift in getShiftsForDay(day.date)" :key="shift.id" 
                     class="px-3 py-1.5 bg-white border border-indigo-100 rounded-lg shadow-sm flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                  <span class="text-sm font-bold text-gray-700">{{ formatTime(shift.startTime) }} - {{ formatTime(shift.endTime) }}</span>
                </div>
              </div>
              <p v-else class="text-sm text-gray-300 italic font-medium">Pas de service</p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
          <p class="text-indigo-100 text-sm font-medium">Total Heures</p>
          <p class="text-4xl font-black mt-2">{{ weeklyHours }}<span class="text-lg font-medium ml-1 text-indigo-200">h</span></p>
          <div class="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-xs text-indigo-100">
            <span>{{ upcomingShiftsCount }} à venir</span>
            <span class="px-2 py-0.5 bg-white/20 rounded-full font-bold">Semaine</span>
          </div>
        </div>

        <div class="card">
          <h4 class="font-bold text-gray-800 text-sm mb-4">Prochain Service</h4>
          <ul class="space-y-3 text-sm">
            <li class="flex items-center gap-3 text-gray-600">
              <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
              <span class="font-bold ml-auto text-gray-900">{{ nextShiftTime }}</span>
            </li>
          </ul>
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

const shifts = ref([]);
const authStore = useAuthStore();
const currentWeekStart = ref(getMonday(new Date()));

function getMonday(d) {
  d = new Date(d);
  const day = d.getDay();
  const diff = d.getDate() - day + (day === 0 ? -6 : 1);
  return new Date(d.setDate(diff));
}

const isToday = (date) => new Date().toDateString() === date.toDateString();

const weekDays = computed(() => {
  const days = [];
  const labels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
  for (let i = 0; i < 7; i++) {
    const d = new Date(currentWeekStart.value);
    d.setDate(d.getDate() + i);
    days.push({
      date: d,
      dayNum: d.getDate(),
      label: labels[i]
    });
  }
  return days;
});

const getShiftsForDay = (date) => {
  const dStr = date.toDateString();
  return shifts.value.filter(s => new Date(s.startTime).toDateString() === dStr);
};

const formatTime = (isoStr) => {
  return new Date(isoStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const weeklyHours = computed(() => {
  let total = 0;
  shifts.value.forEach(s => {
    const duration = (new Date(s.endTime) - new Date(s.startTime)) / (1000 * 60 * 60);
    total += duration;
  });
  return total.toFixed(1);
});

const upcomingShiftsCount = computed(() => {
  const now = new Date();
  return shifts.value.filter(s => new Date(s.startTime) > now).length;
});

const nextShiftTime = computed(() => {
  const now = new Date();
  const upcoming = shifts.value
    .filter(s => new Date(s.startTime) > now)
    .sort((a, b) => new Date(a.startTime) - new Date(b.startTime));
  
  if (upcoming.length === 0) return 'Aucun prévu';
  const start = new Date(upcoming[0].startTime);
  return `${start.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric' })} @ ${formatTime(upcoming[0].startTime)}`;
});

const fetchMyShifts = async () => {
  try {
    const response = await api.get('/shifts');
    const start = currentWeekStart.value;
    const end = new Date(start);
    end.setDate(end.getDate() + 7);
    
    const allShifts = response.data['hydra:member'] || [];
    shifts.value = allShifts.filter(s => {
      const sDate = new Date(s.startTime);
      return sDate >= start && sDate < end;
    });
  } catch (e) {
    console.error('Failed to fetch shifts', e);
  }
};

onMounted(() => {
  fetchMyShifts();
});
</script>
