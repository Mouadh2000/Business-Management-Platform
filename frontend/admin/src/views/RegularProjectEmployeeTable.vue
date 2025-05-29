<template>
    <div>
      <base-header class="pb-6 pb-8 pt-5 pt-md-8 bg-gradient-primary">
        <card-stats :cards="adminStats" />
      </base-header>
      <b-container fluid class="mt--7">
        <b-row>
          <b-col>
            <project-employee-table/>
          </b-col>
        </b-row>
        <div class="mt-5"></div>
      </b-container>
    </div>
  </template>
  
  <script>
  import CardStats from './CardStats.vue';
  import ProjectEmployeeTable from './ProjectEmployee/ProjectEmployeeTable';
  import { adminStats } from '@/data/cardStats';

  
  export default {
    components: {
      CardStats,
      ProjectEmployeeTable
    },
    data() {
      return {
        adminStats
      };
    }
  };
  </script>