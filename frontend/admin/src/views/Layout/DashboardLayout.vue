<template>
  <div class="wrapper">
    <notifications></notifications>
    <side-bar>
      <template slot="links">
        <sidebar-item
          :link="{
            name: 'Dashboard',
            path: '/dashboard',
            icon: 'ni ni-tv-2 text-primary',
          }"
        >
        </sidebar-item>
        <template v-if="isAdmin">
          <sidebar-item
            :link="{
              name: 'Admins',
              path: '/admins',
              icon: 'ni ni-single-02 text-green'
            }">
          </sidebar-item>
          <sidebar-item
            :link="{
              name: 'Clients',
              path: '/clients',
              icon: 'ni ni-circle-08 text-orange'
            }">
          </sidebar-item>
          <sidebar-item
            :link="{
              name: 'projects',
              path: '/projects',
              icon: 'ni ni-bullet-list-67 text-blue'
            }">
          </sidebar-item>
          <sidebar-item
            :link="{
              name: 'Projects and Tasks',
              path: '/project-employees',
              icon: 'ni ni-check-bold text-red'
            }">
          </sidebar-item>
          
        </template>
        <sidebar-item v-if="isStaff"
          :link="{
            name: 'Tasks',
            path: '/tasks',
            icon: 'ni ni-check-bold text-blue'
          }">
        </sidebar-item>
        <sidebar-item v-if="isAdmin"
            :link="{
              name: 'Calendar',
              path: '/calendar',
              icon: 'ni ni-calendar-grid-58 text-orange'
            }">
          </sidebar-item>
      </template>
    </side-bar>
    <div class="main-content">
      <dashboard-navbar :type="$route.meta.navbarType"></dashboard-navbar>

      <div @click="$sidebar.displaySidebar(false)">
        <fade-transition :duration="200" origin="center top" mode="out-in">
          <router-view></router-view>
        </fade-transition>
      </div>
      <content-footer v-if="!$route.meta.hideFooter"></content-footer>
    </div>
  </div>
</template>

<script>
import PerfectScrollbar from 'perfect-scrollbar';
import 'perfect-scrollbar/css/perfect-scrollbar.css';
import { mapGetters } from 'vuex';
import DashboardNavbar from './DashboardNavbar.vue';
import ContentFooter from './ContentFooter.vue';
import DashboardContent from './Content.vue';
import { FadeTransition } from 'vue2-transitions';

function hasElement(className) {
  return document.getElementsByClassName(className).length > 0;
}

function initScrollbar(className) {
  if (hasElement(className)) {
    new PerfectScrollbar(`.${className}`);
  } else {
    setTimeout(() => {
      initScrollbar(className);
    }, 100);
  }
}

export default {
  components: {
    DashboardNavbar,
    ContentFooter,
    DashboardContent,
    FadeTransition
  },
  computed: {
    ...mapGetters('auth', ['isAdmin', 'isStaff'])
  },
  methods: {
    initScrollbar() {
      let isWindows = navigator.platform.startsWith('Win');
      if (isWindows) {
        initScrollbar('sidenav');
      }
    }
  },
  mounted() {
    this.initScrollbar()
  }
};
</script>

<style lang="scss">
</style>