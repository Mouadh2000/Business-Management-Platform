<template>
  <b-modal id="add-project-modal" 
           title="Add New Project" 
           hide-footer
           @hidden="resetModal">
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <input v-model="form.name" type="text" placeholder="Project Name" class="form-control" required>
      </div>

      <div class="form-group">
        <textarea v-model="form.description"  placeholder="Description" class="form-control" rows="3"></textarea>
      </div>

      <div class="form-group">
        <select v-model="form.client_id" class="form-control" required>
          <option value="" disabled>Select Client</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.company_name }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <input v-model="form.price" type="number" step="0.01" min="0" placeholder="Project Price" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Start Date</label>
        <input v-model="form.start_date" type="date" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Deadline</label>
        <input v-model="form.deadline" type="date" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select v-model="form.status" class="form-control" required>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="canceled">Canceled</option>
        </select>
      </div>

      <div class="d-flex justify-content-end">
        <b-button type="button" variant="secondary" @click="$bvModal.hide('add-project-modal')" class="mr-2">
          Cancel
        </b-button>
        <b-button type="submit" variant="primary" :disabled="isLoading">
          Save
        </b-button>
      </div>
    </form>
  </b-modal>
</template>

  
  <script>
  export default {
    name: 'AddProjectModal',
    props: {
      clients: {
        type: Array,
        default: () => []
      },
      isLoading: {
        type: Boolean,
        default: false
      }
    },
    data() {
  return {
    form: {
      name: '',
      description: '',
      client_id: '',
      start_date: '',
      deadline: '',
      status: 'pending',
      price: ''
    }
  }
},
methods: {
  handleSubmit() {
    this.$emit('add-project', this.form);
    this.$bvModal.hide('add-project-modal');
  },
  resetModal() {
    this.form = {
      name: '',
      description: '',
      client_id: '',
      start_date: '',
      deadline: '',
      status: 'pending',
      price: ''
    };
  }
}

  }
  </script>