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
  import Calendar from './Calendar/Calendar.vue';
  
  export default {
    components: {
      Calendar
    },
    data() {
      return {
        
      };
    }
  };
  </script>