<template>
    <b-modal id="add-admin-modal" 
             title="Add New Admin or Employee" 
             hide-footer
             @hidden="resetModal">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <input v-model="form.firstName" type="text" placeholder="First Name" class="form-control" required>
        </div>
        
        <div class="form-group">
          <input v-model="form.lastName" placeholder="Last Name" type="text" class="form-control" required>
        </div>
        
        <div class="form-group">
          <input v-model="form.username" placeholder="Username" type="text" class="form-control" required>
        </div>
        
        <div class="form-group">
          <input v-model="form.email" type="email" placeholder="Email" class="form-control" required>
        </div>
        
        <div class="form-group">
          <input v-model="form.password" type="password" placeholder="password" class="form-control" required>
        </div>
        
        <div class="form-check mb-2">
          <input v-model="form.isStaff" type="checkbox" class="form-check-input" id="isStaff">
          <label class="form-check-label" for="isStaff">Is Staff</label>
        </div>
        
        <div class="form-check mb-2">
          <input v-model="form.isAdmin" type="checkbox" class="form-check-input" id="isAdmin">
          <label class="form-check-label" for="isAdmin">Is Admin</label>
        </div>
        
        <div class="d-flex justify-content-end">
          <b-button type="button" variant="secondary" @click="$bvModal.hide('add-admin-modal')" class="mr-2">
            Cancel
          </b-button>
          <b-button type="submit" variant="primary">
            Save
          </b-button>
        </div>
      </form>
    </b-modal>
  </template>
  
  <script>
  export default {
    name: 'AddAdminModal',
    data() {
      return {
        form: {
          firstName: '',
          lastName: '',
          username: '',
          email: '',
          password: '',
          isStaff: false,
          isAdmin: false
        }
      }
    },
    methods: {
      handleSubmit() {
        this.$emit('add-admin', this.form);
        this.$bvModal.hide('add-admin-modal');
      },
      resetModal() {
        this.form = {
          firstName: '',
          lastName: '',
          username: '',
          email: '',
          password: '',
          isStaff: false,
          isAdmin: false
        };
      }
    }
  }
  </script>