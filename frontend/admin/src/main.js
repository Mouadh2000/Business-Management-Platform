import Vue from 'vue';
import DashboardPlugin from './plugins/dashboard-plugin';
import App from './App.vue';
import store from './store'; 
import router from './routes/router';
import Toast from 'vue-toastification';



import '@/api/axiosApi';

// plugin setup
Vue.use(DashboardPlugin);

Vue.use(Toast, {
  transition: 'Vue-Toastification__bounce',
  maxToasts: 5,
  newestOnTop: true
})

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.VUE_APP_WEBSOCKETS_KEY,
    cluster: 'mt1',
    wsHost: process.env.VUE_APP_WEBSOCKETS_SERVER,
    wsPort: 6001,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    
});
/* eslint-disable no-new */
new Vue({
  el: '#app',
  render: h => h(App),
  router,
  store, 
  created() {
    this.$store.dispatch('auth/initializeAuth');
  }
});