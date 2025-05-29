<template>
  <div>
    <base-header class="pb-6 pb-8 pt-5 pt-md-8 bg-gradient-success">
      <card-stats :cards="adminStats" />
    </base-header>
    <b-container fluid class="mt--7">
      <b-row>
        <b-col>
          <admin-table />
        </b-col>
      </b-row>
      <div class="mt-5"></div>
    </b-container>
  </div>
</template>

<script>
import CardStats from './CardStats.vue';
import AdminTable from './Admins/AdminTable';
import { adminStats } from '@/data/cardStats';

export default {
  components: {
    CardStats,
    AdminTable
  },
  data() {
    return {
      adminStats
    };
  }
};
</script>

