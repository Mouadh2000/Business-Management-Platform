<template>
  <b-modal id="update-client-modal" 
           :title="`Edit ${form.companyName}`" 
           hide-footer
           @hidden="resetModal">
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label>Company Name</label>
        <input v-model="form.company_name" type="text" class="form-control" required>
      </div>

      <div class="form-group">
        <label>First Name</label>
        <input v-model="form.first_name" type="text" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Last Name</label>
        <input v-model="form.last_name" type="text" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input v-model="form.phone" type="text" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select v-model="form.status" class="form-control" required>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <div class="d-flex justify-content-end">
        <b-button type="button" variant="secondary" @click="$bvModal.hide('update-client-modal')" class="mr-2">
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
  name: 'UpdateClientModal',
  data() {
  return {
    form: {
      id: null,
      company_name: '',
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      status: 'active'
    }
  }
},
methods: {
  show(client) {
    this.form = {
      id: client.id,
      company_name: client.company_name,
      first_name: client.first_name,
      last_name: client.last_name,
      email: client.email,
      phone: client.phone,
      status: client.status
    };
    this.$bvModal.show('update-client-modal');
  },
  handleSubmit() {
    this.$emit('update-client', this.form);
  },
  resetModal() {
    this.form = {
      id: null,
      company_name: '',
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      status: 'active'
    };
  }
}
}
</script>
