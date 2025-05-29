import DashboardLayout from '@/views/Layout/DashboardLayout.vue';
import AuthLayout from '@/views/Pages/AuthLayout.vue';
import NotFound from '@/views/NotFoundPage.vue';

const routes = [
  {
    path: '/',
    redirect: 'dashboard',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: { title: 'Dashboard' }
      },
      {
        path: '/profile',
        name: 'profile',
        component: () => import('../views/Pages/UserProfile.vue'),
        meta: { title: 'User Profile' }
      },
      {
        path: '/projects',
        name: 'projects',
        component: () => import('../views/RegularProjectTable.vue'),
        meta: { 
          title: 'Project Management', 
        }
      },
      {
        path: '/calendar',
        name: 'Calendar',
        component: () => import('../views/RegularCalendar.vue'),
        meta: { 
          title: 'Calendar Management',
        }
      },
      {
        path: '/forbidden',
        name: 'forbidden',
        component: () => import('../views/Errors/Forbidden403.vue'),
        meta: { title: '403 Forbidden' }
      }
    ]
  },
  {
    path: '/',
    redirect: 'login',
    component: AuthLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: '/login',
        name: 'login',
        component: () => import('../views/Pages/Login.vue'),
        meta: { title: 'Login' }
      },
      { 
        path: '*', 
        component: NotFound,
        meta: { title: '404 Not Found' }
      }
    ]
  }
];

export default routes;