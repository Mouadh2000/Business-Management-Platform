<template>
  <card>
    <b-row align-v="center" slot="header">
      <b-col cols="8">
        <h3 class="mb-0">Edit profile</h3>
      </b-col>
      <b-col cols="4" class="text-right">
        <a href="#!" class="btn btn-sm btn-primary">Settings</a>
      </b-col>
    </b-row>

    <b-form @submit.prevent="updateProfile">
      <h6 class="heading-small text-muted mb-4">User information</h6>

      <div class="pl-lg-4">
        <b-row>
          <b-col lg="6">
            <base-input
              type="email"
              label="Email address"
              placeholder="mike@email.com"
              v-model="user.email"
            >
            </base-input>
          </b-col>
        </b-row>
        <b-row>
          <b-col lg="6">
            <base-input
              type="text"
              label="First Name"
              placeholder="First Name"
              v-model="user.firstName"
            >
            </base-input>
          </b-col>
          <b-col lg="6">
            <base-input
              type="text"
              label="Last Name"
              placeholder="Last Name"
              v-model="user.lastName"
            >
            </base-input>
          </b-col>
        </b-row>
      </div>
    </b-form>
  </card>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  data() {
    return {
      user: {
        email: '',
        firstName: '',
        lastName: '',
      }
    };
  },
  computed: {
    ...mapGetters('auth', ['currentUser'])
  },
  created() {
    if (this.currentUser) {
      this.user = {
        email: this.currentUser.email || '',
        firstName: this.currentUser.first_name || '',
        lastName: this.currentUser.last_name || '',
      };
    }
  },
  methods: {
    updateProfile() {
      // Call the API to update the user profile
    }
  }
};
</script>

<style></style>