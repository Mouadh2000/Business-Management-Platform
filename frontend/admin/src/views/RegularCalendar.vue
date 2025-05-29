<template>
    <div>
      <base-header class="pb-5 pt-5 pt-md-8 bg-gray">
      </base-header>
      <b-container fluid class="mt--7">
        <b-row>
          <b-col>
            <calendar />
          </b-col>
        </b-row>
      </b-container>
    </div>
  </template>
  
  <script>
  import CardStats from './CardStats.vue';
  import Calendar from './Calendar/Calendar.vue';
  import { adminStats } from '@/data/cardStats';
  
  export default {
    components: {
      CardStats,
      Calendar
    },
    data() {
      return {
        adminStats
      };
    }
  };
  </script>