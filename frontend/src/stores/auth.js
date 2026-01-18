import { defineStore } from 'pinia';
import api from '../services/api';
import { jwtDecode } from "jwt-decode";

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user: null, // Will hold decoded token info or user profile
        isAuthenticated: !!localStorage.getItem('token'),
    }),
    getters: {
        getUser: (state) => state.user,
        getRole: (state) => {
            if (!state.user) return null;
            return state.user.roles ? state.user.roles[0] : null; // Simplification
        },
        isAdmin: (state) => state.user?.roles?.includes('ROLE_ADMIN'),
        isManager: (state) => state.user?.roles?.includes('ROLE_MANAGER'),
    },
    actions: {
        async login(email, password) {
            try {
                const response = await api.post('/login_check', { email: email.trim(), password });
                const token = response.data.token;
                this.token = token;
                this.isAuthenticated = true;
                localStorage.setItem('token', token);

                // Decode token to get user info immediately
                const decoded = jwtDecode(token);
                this.user = {
                    email: decoded.username,
                    roles: decoded.roles
                };

                return true;
            } catch (error) {
                console.error('Login failed', error);
                throw error;
            }
        },
        logout() {
            this.token = null;
            this.user = null;
            this.isAuthenticated = false;
            localStorage.removeItem('token');
            // Redirect logic can be handled in component
        }
    },
});
