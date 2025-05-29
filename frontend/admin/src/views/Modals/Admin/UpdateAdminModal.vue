<template>
    <b-modal id="update-admin-modal" 
             :title="`Edit ${form.username}`" 
             hide-footer
             @hidden="resetModal">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>First Name</label>
          <input v-model="form.firstName" type="text" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label>Last Name</label>
          <input v-model="form.lastName" type="text" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label>Username</label>
          <input v-model="form.username" type="text" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label>Password (leave blank to keep current)</label>
          <input v-model="form.password" type="password" class="form-control">
        </div>
        
        <div class="form-check mb-2">
          <input v-model="form.isStaff" type="checkbox" class="form-check-input" id="editIsStaff">
          <label class="form-check-label" for="editIsStaff">Is Staff</label>
        </div>
        
        <div class="form-check mb-2">
          <input v-model="form.isAdmin" type="checkbox" class="form-check-input" id="editIsAdmin">
          <label class="form-check-label" for="editIsAdmin">Is Admin</label>
        </div>
        
        <div class="d-flex justify-content-end">
          <b-button type="button" variant="secondary" @click="$bvModal.hide('update-admin-modal')" class="mr-2">
            Cancel
          </b-button>
          <b-button type="submit" variant="primary">
            Update
          </b-button>
        </div>
      </form>
    </b-modal>
  </template>
  
  <script>
  export default {
    name: 'UpdateAdminModal',
    data() {
      return {
        form: {
          id: null,
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
      show(admin) {
        this.form = {
          id: admin.id,
          firstName: admin.first_name,
          lastName: admin.last_name,
          username: admin.username,
          email: admin.email,
          password: '',
          isStaff: admin.is_staff,
          isAdmin: admin.is_admin
        };
        this.$bvModal.show('update-admin-modal');
      },
      handleSubmit() {
        this.$emit('update-admin', this.form);
      },
      resetModal() {
        this.form = {
          id: null,
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