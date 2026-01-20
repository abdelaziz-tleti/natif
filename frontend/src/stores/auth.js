import { defineStore } from 'pinia';
import api from '../services/api';
import { jwtDecode } from "jwt-decode";

export const useAuthStore = defineStore('auth', {
    state: () => {
        const token = localStorage.getItem('token');
        let user = null;

        if (token) {
            try {
                const decoded = jwtDecode(token);
                user = {
                    phone: decoded.username,
                    roles: decoded.roles
                };
            } catch (e) {
                console.error("Invalid token found", e);
                localStorage.removeItem('token');
            }
        }

        return {
            token: token || null,
            user: user,
            isAuthenticated: !!token,
        };
    },
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
        async login(phone, password) {
            try {
                const response = await api.post('/login_check', { phone: phone.trim(), password });
                const token = response.data.token;
                this.token = token;
                this.isAuthenticated = true;
                localStorage.setItem('token', token);

                // Decode token to get user info immediately
                const decoded = jwtDecode(token);
                this.user = {
                    phone: decoded.username,
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
