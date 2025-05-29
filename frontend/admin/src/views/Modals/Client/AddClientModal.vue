<template>
  <b-modal id="add-client-modal" 
           title="Add New Client" 
           hide-footer
           @hidden="resetModal">
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label>Company Name</label>
        <input v-model="form.company_name" type="text" placeholder="Company Name" class="form-control" required>
      </div>

      <div class="form-group">
        <label>First Name</label>
        <input v-model="form.first_name" type="text" placeholder="First Name" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Last Name</label>
        <input v-model="form.last_name" type="text" placeholder="Last Name" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" placeholder="Email" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Phone</label>
        <input v-model="form.phone" type="text" placeholder="Phone" class="form-control">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input v-model="form.password" type="password" placeholder="Password" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select v-model="form.status" class="form-control">
          <option value="" disabled>Select Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="pending">Pending</option>
        </select>
      </div>

      <div class="d-flex justify-content-end">
        <b-button type="button" variant="secondary" @click="$bvModal.hide('add-client-modal')" class="mr-2">
          Cancel
        </b-button>
        <b-button type="submit" variant="primary" :disabled="isLoading">
          <span v-if="isLoading">
            <i class="fa fa-spinner fa-spin"></i> Saving...
          </span>
          <span v-else>Save</span>
        </b-button>
      </div>
    </form>
  </b-modal>
</template>

<script>
export default {
  name: 'AddClientModal',
  props: {
    isLoading: Boolean
  },
  data() {
    return {
      form: {
        company_name: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        password: '',
        status: 'active'
      }
    }
  },
  methods: {
    handleSubmit() {
      this.$emit('add-client', this.form);
    },
    resetModal() {
      this.form = {
        company_name: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        password: '',
        status: 'active'
      };
    }
  }
}
</script>

<style scoped>
.form-group {
  margin-bottom: 1.5rem;
}
label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}
</style>
