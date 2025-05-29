<template>
    <div class="forbidden-container">
      <b-container fluid>
        <b-row class="justify-content-center">
          <b-col md="8" class="text-center">
            <h1 class="display-1 text-danger">403</h1>
            <h2 class="display-4">Forbidden</h2>
            <p class="lead">You don't have permission to access this resource.</p>
            <b-button variant="primary" to="/">Return to Home</b-button>
          </b-col>
        </b-row>
      </b-container>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Forbidden403'
  }
  </script>
  
  <style scoped>
  .forbidden-container {
    padding: 5rem 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
  </style>