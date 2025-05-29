<template>
    <b-modal id="assign-task-modal" 
             title="Assign Task" 
             hide-footer
             @hidden="handleClose">
      <b-form @submit.prevent="submitAssignTask">
        
        <b-form-group>
          <b-form-input
            id="title-input"
            v-model="form.title"
            placeholder="Task title"
            required
          ></b-form-input>
        </b-form-group>
  
        <b-form-group>
          <b-form-textarea
            id="description-input"
            v-model="form.description"
            placeholder="Task description"
            rows="3"
          ></b-form-textarea>
        </b-form-group>
    
        <b-form-group label="Priority" label-for="priority-input">
          <b-form-select
            id="priority-input"
            v-model="form.priority"
            :options="priorityOptions"
          ></b-form-select>
        </b-form-group>
    
        <b-form-group label="Status" label-for="status-input">
          <b-form-select
            id="status-input"
            v-model="form.status"
            :options="statusOptions"
          ></b-form-select>
        </b-form-group>
  
        <div class="form-group">
          <label>Start Date</label>
          <input 
            v-model="form.start_date" 
            type="date" 
            class="form-control" 
            :min="projectStartDate" 
            required
            @change="updateDeadlineMin"
          >
        </div>

        <div class="form-group">
          <label>Deadline</label>
          <input 
            v-model="form.deadline" 
            type="date" 
            class="form-control" 
            :min="form.start_date || projectStartDate" 
            required
          >
        </div>
  
        <div class="d-flex justify-content-end">
          <b-button type="button" variant="secondary" @click="handleClose" class="mr-2">
            Cancel
          </b-button>
          <b-button type="submit" variant="primary" :disabled="isSubmitting">
            Assign
          </b-button>
        </div>
      </b-form>
    </b-modal>
  </template>
  
<script>
import { assignTaskToUser } from '@/api/taskApi';
  
export default {
  name: 'AssignTaskModal',
  props: {
    employee: {
      type: Object,
      default: null
    },
    project: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      isSubmitting: false,
      form: {
        title: '',
        description: '',
        priority: 'medium',
        status: 'todo',
        start_date: this.project ? this.project.start_date : null,
        deadline: null
      },
      priorityOptions: [
        { value: 'high', text: 'High' },
        { value: 'medium', text: 'Medium' },
        { value: 'low', text: 'Low' }
      ],
      statusOptions: [
        { value: 'todo', text: 'To Do' },
        { value: 'in_progress', text: 'In Progress' },
        { value: 'done', text: 'Done' }
      ],
      projectStartDate: this.project ? this.project.start_date : new Date().toISOString().split('T')[0]
    };
  },
  methods: {
    resetForm() {
      this.form = {
        title: '',
        description: '',
        priority: 'medium',
        status: 'todo',
        start_date: this.project ? this.project.start_date : null,
        deadline: null
      };
    },
    async submitAssignTask() {
      if (!this.form.title || !this.form.deadline) {
        this.$notify.warning({
          title: 'Warning',
          message: 'Please fill required fields'
        });
        return;
      }

      this.isSubmitting = true;

      const payload = {
        project_id: this.project.id,
        title: this.form.title,
        description: this.form.description,
        priority: this.form.priority,
        status: this.form.status,
        start_date: this.form.start_date ? this.formatDateForBackend(this.form.start_date) : null,
        deadline: this.formatDateForBackend(this.form.deadline),
        assignees: [this.employee.id]
      };

      try {
        await assignTaskToUser(payload);
        this.$notify.success({
          title: 'Success',
          message: 'Task assigned successfully'
        });
        this.$bvModal.hide('assign-task-modal');
        this.$emit('task-assigned');
      } catch (error) {
        console.error('Assignment failed:', error);
        this.$notify.error({
          title: 'Error',
          message: (error.response && error.response.data && error.response.data.message) || 'Failed to assign task'
        });
      } finally {
        this.isSubmitting = false;
      }
    },
    handleClose() {
      this.resetForm();
      this.$bvModal.hide('assign-task-modal');
    },
    formatDateForBackend(date) {
      return date;
    },
    updateDeadlineMin() {
      // If deadline is before the new start date, reset it
      if (this.form.deadline && this.form.start_date && this.form.deadline < this.form.start_date) {
        this.form.deadline = this.form.start_date;
      }
    }
  }
};
</script>
  