<template>
    <div>
      <base-header class="pb-6 pb-8 pt-5 pt-md-8 bg-gradient-primary">
      </base-header>
      <b-container fluid class="mt--7">
        <b-row>
          <b-col>
            <project-table/>
          </b-col>
        </b-row>
        <div class="mt-5"></div>
      </b-container>
    </div>
  </template>
  
  <script>
  import ProjectTable from './Projects/ProjectTable';
  
  export default {
    components: {
      ProjectTable
    },
    data() {
      
    }
  };
  </script>