import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';
import Products from '../views/manager/Products.vue';
import Shifts from '../views/manager/Shifts.vue';
import EmployeeProducts from '../views/employee/Products.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/manager/products',
        name: 'Products',
        component: Products,
        meta: { requiresAuth: true, roles: ['ROLE_MANAGER', 'ROLE_ADMIN'] }
    },
    {
        path: '/manager/shifts',
        name: 'Shifts',
        component: Shifts,
        meta: { requiresAuth: true, roles: ['ROLE_MANAGER', 'ROLE_ADMIN'] }
    },
    {
        path: '/employee/stock',
        name: 'EmployeeStock',
        component: EmployeeProducts,
        meta: { requiresAuth: true }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && isAuthenticated) {
        next('/');
    } else {
        next();
    }
});

export default router;
