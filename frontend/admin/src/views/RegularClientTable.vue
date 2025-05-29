<template>
    <div>
      <base-header class="pb-6 pb-8 pt-5 pt-md-8 bg-gradient-primary">
      <card-stats :cards="adminStats" />
      </base-header>
      <b-container fluid class="mt--7">
        <b-row>
          <b-col>
            <client-table/>
          </b-col>
        </b-row>
        <div class="mt-5"></div>
      </b-container>
    </div>
  </template>
  
  <script>
  import CardStats from './CardStats.vue';
  import ClientTable from './Clients/ClientTable';
  import { adminStats } from '@/data/cardStats';
  
  export default {
    components: {
      CardStats,
      ClientTable
    },
    data() {
      return {
        adminStats
      };
    }
  };
  </script>
  
  <style>
  .el-table.table-dark {
    background-color: #172b4d;
    color: #f8f9fe;
  }
  
  .el-table.table-dark th,
  .el-table.table-dark tr {
    background-color: #172b4d;
  }
  
  .el-table.table-dark td,
  .el-table.table-dark th.is-leaf {
    border-bottom: none;
  }
  </style>
  