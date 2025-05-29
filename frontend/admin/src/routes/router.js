import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';
import store from '../store';

Vue.use(VueRouter);

const router = new VueRouter({
  routes, 
  linkActiveClass: 'active',
  scrollBehavior: (to, from, savedPosition) => {
    if (savedPosition) {
      return savedPosition;
    }
    if (to.hash) {
      return { selector: to.hash };
    }
    return { x: 0, y: 0 };
  }
});

router.beforeEach(async (to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);
  const requiresStaff = to.matched.some(record => record.meta.requiresStaff);

  
  if (!store.state.auth.initialized) {
    await store.dispatch('auth/initializeAuth');
  }

  const isAuthenticated = store.getters['auth/isLoggedIn'];
  const isAdmin = store.getters['auth/isAdmin'];
  const isStaff = store.getters['auth/isStaff'];

  if (requiresAuth && !isAuthenticated) {
    next('/login');
  } else if (requiresAdmin && (!isAuthenticated || !isAdmin)) {
    next('/forbidden'); 
  } else if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
    next('/dashboard');
  } else {
    next();
  }
});

export default router;