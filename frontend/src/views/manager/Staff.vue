<template>
  <AppLayout>
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Équipe</h1>
        <p class="page-subtitle">Gestion du personnel</p>
      </div>
      <button @click="openAddModal" class="btn-fab">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
      </button>
    </div>

    <!-- Staff Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-gray-500 text-sm font-medium">Total Staff</p>
        <p class="text-3xl font-black text-gray-900 mt-1">{{ staffList.length }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-gray-500 text-sm font-medium">Managers</p>
        <p class="text-3xl font-black text-indigo-600 mt-1">{{ countByRole('ROLE_MANAGER') }}</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-gray-500 text-sm font-medium">Kitchen/Floor</p>
        <p class="text-3xl font-black text-emerald-600 mt-1">{{ countByRole('ROLE_USER') }}</p>
      </div>
    </div>

    <!-- Staff List -->
    <div class="grid gap-4">
      <div v-for="member in staffList" :key="member.id" class="group bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-indigo-200 transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 bg-gray-50 rounded-2xl flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
              <span class="text-2xl font-black text-gray-400 group-hover:text-indigo-600">
                {{ member.firstName?.charAt(0) || member.phone?.charAt(0) || 'U' }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-gray-900 truncate">
                  {{ member.firstName }} {{ member.lastName }}
                </h3>
                <span :class="roleBadgeClass(member.roles)" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider">
                  {{ displayRole(member.roles) }}
                </span>
              </div>
              <p class="text-sm font-medium text-gray-900 truncate">{{ member.phone }}</p>
              <p v-if="member.email" class="text-xs text-gray-500 truncate">{{ member.email }}</p>
              <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Joined {{ formatDate(member.joiningDate) }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:self-center">
            <button @click="openEdit(member)" class="p-2.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
            </button>
            <button @click="deleteMember(member)" class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="staffList.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-100">
        <div class="bg-gray-50 h-20 w-20 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900">No staff members yet</h3>
        <p class="text-gray-500 max-w-xs mx-auto mt-1">Start building your team by adding your first employee.</p>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0">
      <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
      
      <div class="relative bg-white rounded-3xl max-w-lg mx-auto shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 md:p-8">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-black text-gray-900">{{ isEditing ? 'Edit Staff Member' : 'Add Staff Member' }}</h2>
            <button @click="closeModal" class="p-2 text-gray-400 hover:bg-gray-100 rounded-xl">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="saveMember" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">First Name</label>
                <input v-model="form.firstName" type="text" placeholder="John" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:border-indigo-500 outline-none transition-all font-medium" />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Last Name</label>
                <input v-model="form.lastName" type="text" placeholder="Doe" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:border-indigo-500 outline-none transition-all font-medium" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Phone Number (Required)</label>
              <input v-model="form.phone" type="tel" required placeholder="06 12 34 56 78" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:border-indigo-500 outline-none transition-all font-medium" />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Email Address (Optional)</label>
              <input v-model="form.email" type="email" placeholder="john@example.com" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:border-indigo-500 outline-none transition-all font-medium" />
            </div>

            <div v-if="!isEditing">
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Password</label>
              <input v-model="form.plainPassword" type="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:border-indigo-500 outline-none transition-all font-medium" />
            </div>



            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Role</label>
              <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="form.roles = ['ROLE_USER']" :class="form.roles.includes('ROLE_USER') ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-50 text-gray-600'" class="py-3 rounded-xl font-bold transition-all text-sm">Employee</button>
                <button type="button" @click="form.roles = ['ROLE_MANAGER']" :class="form.roles.includes('ROLE_MANAGER') ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-50 text-gray-600'" class="py-3 rounded-xl font-bold transition-all text-sm">Manager</button>
              </div>
            </div>

            <div class="pt-4">
              <button type="submit" :disabled="loading" class="w-full py-4 bg-gray-900 hover:bg-black text-white rounded-2xl font-black shadow-xl transition-all disabled:opacity-50">
                {{ loading ? 'Updating...' : (isEditing ? 'Save Changes' : 'Create Account') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '../../components/AppLayout.vue';
import api from '../../services/api';

const staffList = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const editingMember = ref(null);
const loading = ref(false);

const form = ref({
  email: '',
  firstName: '',
  lastName: '',
  phone: '',
  roles: ['ROLE_USER'],
  plainPassword: ''
});

const fetchStaff = async () => {
  try {
    const response = await api.get('/users');
    staffList.value = response.data['hydra:member'] || [];
  } catch (e) {
    console.error('Failed to fetch staff', e);
  }
};

const countByRole = (role) => {
  return staffList.value.filter(u => u.roles.includes(role)).length;
};

const displayRole = (roles) => {
  if (roles.includes('ROLE_ADMIN')) return 'Admin';
  if (roles.includes('ROLE_MANAGER')) return 'Manager';
  return 'Employee';
};

const roleBadgeClass = (roles) => {
  if (roles.includes('ROLE_ADMIN')) return 'bg-amber-100 text-amber-600';
  if (roles.includes('ROLE_MANAGER')) return 'bg-indigo-100 text-indigo-600';
  return 'bg-emerald-100 text-emerald-600';
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    email: '',
    firstName: '',
    lastName: '',
    phone: '',
    roles: ['ROLE_USER'],
    plainPassword: ''
  };
  showModal.value = true;
};

const openEdit = (member) => {
  isEditing.value = true;
  editingMember.value = member;
  form.value = {
    email: member.email,
    firstName: member.firstName || '',
    lastName: member.lastName || '',
    phone: member.phone || '',
    roles: [...member.roles],
    plainPassword: '' // Don't show password of course
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveMember = async () => {
  loading.value = true;
  try {
    if (isEditing.value) {
      const payload = { ...form.value };
      if (!payload.plainPassword) delete payload.plainPassword;
      await api.patch(`/users/${editingMember.value.id}`, payload, {
        headers: { 'Content-Type': 'application/merge-patch+json' }
      });
    } else {
      await api.post('/users', form.value);
    }
    await fetchStaff();
    closeModal();
  } catch (e) {
    console.error('Save failed', e);
    alert(e.response?.data?.['hydra:description'] || 'Failed to save member');
  } finally {
    loading.value = false;
  }
};

const deleteMember = async (member) => {
  if (!confirm(`Are you sure you want to remove ${member.phone}?`)) return;
  try {
    await api.delete(`/users/${member.id}`);
    await fetchStaff();
  } catch (e) {
    console.error('Delete failed', e);
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Recently';
  return new Date(dateStr).toLocaleDateString();
};

onMounted(() => {
  fetchStaff();
});
</script>
