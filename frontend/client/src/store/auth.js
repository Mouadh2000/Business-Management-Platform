import axiosInstance from '@/api/axiosApi';
import router from '../routes/router';

const state = {
    isLoggedIn: false,
    currentUser: null,
    loading: false
};

const mutations = {
    SET_LOGGED_IN(state, value) {
        state.isLoggedIn = value;
    },
    SET_CURRENT_USER(state, user) {
        state.currentUser = user;
    },
    SET_LOADING(state, value) {
        state.loading = value;
    }
};

const actions = {
    async initializeAuth({ commit }) {
        const accessToken = localStorage.getItem('access_token');
        const refreshToken = localStorage.getItem('refresh_token');
        const isAuthenticated = !!accessToken && !!refreshToken;
        commit('SET_LOGGED_IN', isAuthenticated);

        if (isAuthenticated) {
            try {
                const userResponse = await axiosInstance.get('/details/');
                commit('SET_CURRENT_USER', userResponse.data);
            } catch (error) {
                console.error('Failed to fetch user details', error);
                commit('SET_LOGGED_IN', false);
                commit('SET_CURRENT_USER', null);
            }
        }
    },

    async login({ commit }, { email, password }) {
        commit('SET_LOADING', true);
        try {
            const response = await axiosInstance.post('/login/', {
                email,
                password,
            });

            const { access_token, refresh_token } = response.data;

            localStorage.setItem('access_token', access_token);
            localStorage.setItem('refresh_token', refresh_token);
            axiosInstance.defaults.headers['Authorization'] = "Bearer " + access_token;

            const userResponse = await axiosInstance.get('/details/');
            commit('SET_CURRENT_USER', userResponse.data);
            commit('SET_LOGGED_IN', true);
            
            router.push('/dashboard');
        } catch (error) {
            throw error;
        } finally {
            commit('SET_LOADING', false);
        }
    },

    logout({ commit }) {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        commit('SET_LOGGED_IN', false);
        commit('SET_CURRENT_USER', null);
        delete axiosInstance.defaults.headers['Authorization'];
        router.push('/login');
    }
};

const getters = {
    isLoggedIn: state => state.isLoggedIn,
    currentUser: state => state.currentUser,
    isLoading: state => state.loading,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};