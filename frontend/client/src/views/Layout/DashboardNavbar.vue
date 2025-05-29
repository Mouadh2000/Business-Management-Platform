<template>
  <base-nav
    container-classes="container-fluid"
    class="navbar-top navbar-expand"
    :class="{'navbar-dark': type === 'default'}"
  >
    <a href="#" aria-current="page" class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block active router-link-active"> {{$route.name}} </a>
    
    <!-- Navbar links -->
    <b-navbar-nav class="align-items-center ml-md-auto">
      <li class="nav-item d-sm-none">
        <a class="nav-link" href="#" data-action="search-show" data-target="#navbar-search-main">
          <i class="ni ni-zoom-split-in"></i>
        </a>
      </li>
    </b-navbar-nav>
    
    <b-navbar-nav class="align-items-center ml-auto ml-md-0">
      <!-- Notification Dropdown -->
      <base-dropdown menu-on-right
                     class="nav-item"
                     tag="li"
                     title-tag="a"
                     title-classes="nav-link pr-0">
        <a href="#" class="nav-link pr-0" @click.prevent slot="title-container">
          <i class="ni ni-bell-55"></i>
          <span v-if="unreadCount > 0" class="badge badge-danger badge-pill notification-badge">{{ unreadCount }}</span>
        </a>

        <template>
          <b-dropdown-header class="noti-title">
            <h6 class="text-overflow m-0">Notifications ({{ unreadCount }})</h6>
          </b-dropdown-header>
          
          <b-dropdown-item v-for="notification in notifications" 
                          :key="notification.id"
                          @click="handleNotificationClick(notification)">
            <div class="d-flex align-items-center">
              <div class="mr-2">
                <i class="ni ni-calendar-grid-58 text-danger"></i>
              </div>
              <div>
                <span class="font-weight-bold">{{ notification.data.project_name }}</span>
                <p class="text-sm text-muted mb-0">
                  Deadline: {{ formatDate(notification.data.deadline) }}
                </p>
                <small class="text-sm">{{ notification.data.message }}</small>
              </div>
            </div>
          </b-dropdown-item>
          
          <b-dropdown-item v-if="notifications.length === 0">
            <span>No new notifications</span>
          </b-dropdown-item>
          
          <div class="dropdown-divider"></div>
          
          <b-dropdown-item href="#!" @click="markAllAsRead" v-if="notifications.length > 0">
            <i class="ni ni-check-bold"></i>
            <span>Mark all as read</span>
          </b-dropdown-item>
        </template>
      </base-dropdown>

      <!-- User Dropdown -->
      <base-dropdown menu-on-right
                     class="nav-item"
                     tag="li"
                     title-tag="a"
                     title-classes="nav-link pr-0">
        <a href="#" class="nav-link pr-0" @click.prevent slot="title-container">
          <b-media no-body class="align-items-center">
            <span class="avatar avatar-sm rounded-circle">
              <img alt="Image placeholder" src="img/theme/team-4.jpg">
            </span>
            <b-media-body class="ml-2 d-none d-lg-block">
              <span class="mb-0 text-sm font-weight-bold">{{userName}}</span>
            </b-media-body>
          </b-media>
        </a>

        <template>
          <b-dropdown-header class="noti-title">
            <h6 class="text-overflow m-0">Welcome!</h6>
          </b-dropdown-header>
          <b-dropdown-item href="/#/profile">
            <i class="ni ni-single-02"></i>
            <span>My profile</span>
          </b-dropdown-item>
          <b-dropdown-item href="#!">
            <i class="ni ni-settings-gear-65"></i>
            <span>Settings</span>
          </b-dropdown-item>
          <div class="dropdown-divider"></div>
          <b-dropdown-item href="#!" @click.prevent="logout">
            <i class="ni ni-user-run"></i>
            <span>Logout</span>
          </b-dropdown-item>
        </template>
      </base-dropdown>
    </b-navbar-nav>
  </base-nav>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { CollapseTransition } from 'vue2-transitions';
import { BaseNav, Modal } from '@/components';

export default {
  components: {
    CollapseTransition,
    BaseNav,
    Modal
  },
  props: {
    type: {
      type: String,
      default: 'default',
      description: 'Look of the dashboard navbar. Default (Green) or light (gray)'}
  },
  data() {
    return {
      allNotifications: [],
      unreadNotifications: [],
      activeNotifications: false,
      showMenu: false,
      searchModalVisible: false,
      searchQuery: ''
    };
  },
  computed: {
    ...mapGetters('auth', ['currentUser']),
    routeName() {
      const { name } = this.$route;
      return this.capitalizeFirstLetter(name);
    },
    userName() {
      return (this.currentUser && this.currentUser.first_name) || 'User';
    },
    notifications() {
      return this.unreadNotifications;
    },
    unreadCount() {
      return this.unreadNotifications.length;
    }
  },
  mounted() {
    this.listenForNotifications();
  },
  methods: {
    ...mapActions('auth', ['logout']),
    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    toggleNotificationDropDown() {
      this.activeNotifications = !this.activeNotifications;
    },
    closeDropDown() {
      this.activeNotifications = false;
    },
    listenForNotifications() {
      console.log('Attempting to listen to channel...');
      if (window.Echo && window.Echo.connector) {
        console.log('Echo connection state:', window.Echo.connector.pusher.connection.state);
      } else {
        console.error('Laravel Echo is not initialized.');
        return;
      }

      window.Echo.channel('project.deadline')
        .listen('.ProjectDeadlineSoon', (notification) => {
          console.log('Received notification:', notification);
          this.handleNewNotification(notification);
        });
    },
    handleNewNotification(notification) {
  try {
    const notificationData = notification.data || notification
    
    const newNotification = {
      id: notificationData.id || Date.now(),
      data: {
        project_id: notificationData.project_id || notificationData.id,
        project_name: notificationData.name || notificationData.project_name,
        deadline: notificationData.deadline,
        message: notificationData.message || `Project deadline soon: ${notificationData.name || notificationData.project_name}`
      },
      created_at: notificationData.created_at || new Date().toISOString()
    }

    const exists = this.allNotifications.some(n => 
      n.data.project_id === newNotification.data.project_id && 
      n.data.deadline === newNotification.data.deadline
    )

    if (!exists) {
      console.log('Adding new notification:', newNotification)
      this.allNotifications.unshift(newNotification)
      this.unreadNotifications.unshift(newNotification)
      this.showToast(newNotification)
    }
  } catch (error) {
    console.error('Error processing notification:', error, notification)
  }
},

showToast(notification) {
  if (this.$toast) {
    this.$toast(notification.data.message, {
      type: 'info',
      position: 'top-right',
      timeout: 5000,
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      icon: 'ni ni-calendar-grid-58'
    })
  } 
  else {
    console.info('Notification:', notification.data.message)
  }
},
    handleNotificationClick(notification) {
      this.markAsRead(notification);
      if (notification.data.project_id) {
        this.$router.push(`/projects/${notification.data.project_id}`);
      }
    },
    markAsRead(notification) {
      this.unreadNotifications = this.unreadNotifications.filter(
        n => n.id !== notification.id
      );
    },
    markAllAsRead() {
      this.unreadNotifications = [];
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    showToast(notification) {
      this.$toast.info(notification.data.message, {
        position: 'top-right',
        timeout: 5000,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: true,
        closeButton: 'button',
        icon: 'ni ni-calendar-grid-58'
      });
    }
  }
};
</script>

<style scoped>
.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  font-size: 0.6rem;
  transform: translate(25%, -25%);
}

.noti-title {
  padding: 0.5rem 1.5rem;
}

.dropdown-item {
  white-space: normal;
  padding: 0.75rem 1.5rem;
}
</style>
