<template>
    <b-modal id="update-task-modal" 
             :title="`Edit ${form.title}`" 
             hide-footer
             @hidden="resetModal">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>Task Title</label>
          <input v-model="form.title" type="text" class="form-control" required>
        </div>
  
        <div class="form-group">
          <label>Description</label>
          <textarea v-model="form.description" class="form-control" rows="3"></textarea>
        </div>
  
        <div class="form-group">
          <label>Priority</label>
          <select v-model="form.priority" class="form-control" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
        </div>
  
        <div class="form-group">
          <label>Start Date</label>
          <input v-model="form.start_date" type="date" class="form-control">
        </div>
  
        <div class="form-group">
          <label>Deadline</label>
          <input v-model="form.deadline" type="date" class="form-control">
        </div>
  
        <div class="d-flex justify-content-end">
          <b-button type="button" variant="secondary" @click="$bvModal.hide('update-task-modal')" class="mr-2">
            Cancel
          </b-button>
          <b-button type="submit" variant="primary" :disabled="isLoading">
            Update
          </b-button>
        </div>
      </form>
    </b-modal>
  </template>
  
  <script>
  export default {
    name: 'UpdateTaskModal',
    props: {
      isLoading: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        form: {
          id: null,
          title: '',
          description: '',
          priority: 'medium',
          start_date: '',
          deadline: ''
        }
      }
    },
    methods: {
      show(task) {
        this.form = {
          id: task.id,
          title: task.title,
          description: task.description || '',
          priority: task.priority || 'medium',
          start_date: task.start_date ? task.start_date.split('T')[0] : '',
          deadline: task.deadline ? task.deadline.split('T')[0] : ''
        };
        this.$bvModal.show('update-task-modal');
      },
      handleSubmit() {
        this.$emit('update-task', this.form);
      },
      resetModal() {
        this.form = {
          id: null,
          title: '',
          description: '',
          priority: 'medium',
          start_date: '',
          deadline: ''
        };
      }
    }
  }
  </script>