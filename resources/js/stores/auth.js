import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        loading: false
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isDoctor: (state) => state.user?.user_type === 'doctor',
        isPatient: (state) => state.user?.user_type === 'patient',
        isAdmin: (state) => state.user?.user_type === 'admin'
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            try {
                const response = await axios.post('/api/auth/login', credentials);
                
                this.token = response.data.token;
                this.user = response.data.user;
                
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                
                // Set axios default authorization header
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                
                return response.data;
            } catch (error) {
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            try {
                const response = await axios.post('/api/auth/register', userData);
                
                this.token = response.data.token;
                this.user = response.data.user;
                
                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                
                return response.data;
            } catch (error) {
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            this.loading = true;
            try {
                if (this.token) {
                    await axios.post('/api/auth/logout');
                }
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.user = null;
                this.token = null;
                
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                
                delete axios.defaults.headers.common['Authorization'];
                
                this.loading = false;
            }
        },

        async fetchUser() {
            try {
                const response = await axios.get('/api/auth/me');
                this.user = response.data.user;
                localStorage.setItem('user', JSON.stringify(this.user));
                return response.data.user;
            } catch (error) {
                this.logout();
                throw error;
            }
        },

        async updateProfile(profileData) {
            try {
                const response = await axios.put('/api/auth/profile', profileData);
                this.user = response.data.user;
                localStorage.setItem('user', JSON.stringify(this.user));
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async changePassword(passwordData) {
            try {
                const response = await axios.put('/api/auth/change-password', passwordData);
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        checkAuth() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            }
        },

        async fetchUser() {
            if (!this.token) return;
            
            try {
                const response = await axios.get('/api/auth/me');
                this.user = response.data.user;
                localStorage.setItem('user', JSON.stringify(this.user));
            } catch (error) {
                console.error('Error fetching user:', error);
                this.logout();
            }
        }
    }
});