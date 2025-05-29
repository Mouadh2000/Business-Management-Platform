<template>
  <b-card no-body class="card-profile" alt="Image placeholder" img-top>
    <b-row class="justify-content-center">
      <b-col lg="3" class="order-lg-2">
        <div class="card-profile-image">
          <a href="#">
            <b-img src="img/theme/team-4.jpg" rounded="circle" />
          </a>
        </div>
      </b-col>
    </b-row>
    <b-card-body class="pt-0">
      <b-row>
        <b-col>
          <div class="card-profile-stats d-flex justify-content-center mt-md-5">
          </div>
        </b-col>
      </b-row>
      <div class="text-center mt-6">
        <h5 class="h3">
          {{ fullName }}
        </h5>
        <div class="h5 font-weight-300">
          <i class="ni location_pin mr-2"></i>Tunis, Tunisia
        </div>
        <div class="h5 mt-4">
          <i class="ni business_briefcase-24 mr-2"></i>Chief Executive Officer
        </div>
        
        <hr class="my-4">
      </div>
    </b-card-body>
  </b-card>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters('auth', ['currentUser']),
    fullName() {
      if (this.currentUser) {
        const firstName = this.currentUser.firstName || this.currentUser.first_name || '';
        const lastName = this.currentUser.lastName || this.currentUser.last_name || '';
        return `${firstName} ${lastName}`.trim() || 'User';
      }
      return 'User';
    }
  }
};
</script>

<style></style>