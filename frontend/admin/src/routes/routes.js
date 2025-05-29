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
      // Public authenticated routes (no admin required)
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
        path: '/tasks',
        name: 'Tasks',
        component: () => import('../views/RegularTaskTable.vue'),
        meta: { title: 'Tasks',
          requiresStaff: true
         }
      },

      // Admin-only routes
      {
        path: '/admins',
        name: 'admins',
        component: () => import('../views/RegularAdminTable.vue'),
        meta: { 
          title: 'Admin Management',
          requiresAdmin: true
        }
      },
      {
        path: '/clients',
        name: 'clients',
        component: () => import('../views/RegularClientTable.vue'),
        meta: { 
          title: 'Client Management',
          requiresAdmin: true
        }
      },
      {
        path: '/projects',
        name: 'projects',
        component: () => import('../views/RegularProjectTable.vue'),
        meta: { 
          title: 'Project Management', 
          requiresAdmin: true
        }
      },
      {
        path: '/project-employees',
        name: 'Projects and Tasks',
        component: () => import('../views/ProjectWithTasks.vue'),
        meta: { 
          title: 'Project Employees',
          requiresAdmin: true
        }
      },
      {
        path: '/calendar',
        name: 'Calendar',
        component: () => import('../views/RegularCalendar.vue'),
        meta: { 
          title: 'Calendar Management',
          requiresAdmin: true
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