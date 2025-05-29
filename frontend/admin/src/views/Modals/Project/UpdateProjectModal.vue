<template>
  <b-modal id="update-project-modal" 
           :title="`Edit ${form.name}`" 
           hide-footer
           @hidden="resetModal">
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label>Project Name</label>
        <input v-model="form.name" type="text" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
      </div>

      <div class="form-group">
        <label>Client</label>
        <select v-model="form.client_id" class="form-control" required>
          <option value="" disabled>Select Client</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.company_name }}
          </option>
        </select>
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
        <select v-model="form.status" class="form-control" required @change="handleStatusChange">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="canceled">Canceled</option>
        </select>
      </div>

      <!-- Only show when status is "completed" -->
      <div v-if="form.status === 'completed'">
        <div class="form-group">
          <label>Appointment Date <span class="text-danger">*</span></label>
          <input v-model="form.appointment_date" type="date" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Start Time <span class="text-danger">*</span></label>
          <input v-model="form.start_time" type="time" class="form-control" step="900" required>
        </div>

        <div class="form-group">
          <label>End Time <span class="text-danger">*</span></label>
          <input v-model="form.end_time" type="time" class="form-control" step="900" required>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <b-button type="button" variant="secondary" @click="$bvModal.hide('update-project-modal')" class="mr-2">
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
  name: 'UpdateProjectModal',
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
        id: null,
        name: '',
        description: '',
        client_id: '',
        start_date: '',
        deadline: '',
        status: 'pending',
        appointment_date: '',
        start_time: '09:00',
        end_time: '17:00'
      }
    }
  },
  methods: {
    show(project) {
      let startTime = '09:00';
      let endTime = '17:00';
      
      // Safely get event times without optional chaining
      if (project.event && project.event.start_time) {
        startTime = project.event.start_time.substring(0, 5);
      }
      if (project.event && project.event.end_time) {
        endTime = project.event.end_time.substring(0, 5);
      }

      this.form = {
        id: project.id,
        name: project.name,
        description: project.description,
        client_id: project.client_id,
        start_date: project.start_date.split('T')[0],
        deadline: project.deadline.split('T')[0],
        status: project.status,
        appointment_date: project.appointment_date ? project.appointment_date.split('T')[0] : '',
        start_time: startTime,
        end_time: endTime
      };
      this.$bvModal.show('update-project-modal');
    },
    handleSubmit() {
      // Validation for completed status
      if (this.form.status === 'completed') {
        if (!this.form.appointment_date || !this.form.start_time || !this.form.end_time) {
          this.$bvToast.toast('All appointment fields are required when status is Completed', {
            title: 'Validation Error',
            variant: 'danger',
            solid: true
          });
          return;
        }

        // Convert to proper time format (HH:MM:SS)
        const startTime = this.formatTime(this.form.start_time);
        const endTime = this.formatTime(this.form.end_time);

        if (startTime >= endTime) {
          this.$bvToast.toast('End time must be after start time', {
            title: 'Validation Error',
            variant: 'danger',
            solid: true
          });
          return;
        }
      }

      // Prepare data
      const formData = {
        ...this.form,
        appointment_date: this.form.status === 'completed' ? this.form.appointment_date : undefined,
        start_time: this.form.status === 'completed' ? this.formatTime(this.form.start_time) : undefined,
        end_time: this.form.status === 'completed' ? this.formatTime(this.form.end_time) : undefined
      };

      this.$emit('update-project', formData);
    },
    formatTime(timeString) {
      // Ensure time is in HH:MM:SS format
      if (!timeString.includes(':')) return '00:00:00';
      
      const parts = timeString.split(':');
      if (parts.length === 2) {
        return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}:00`;
      }
      return timeString;
    },
    resetModal() {
      this.form = {
        id: null,
        name: '',
        description: '',
        client_id: '',
        start_date: '',
        deadline: '',
        status: 'pending',
        appointment_date: '',
        start_time: '09:00',
        end_time: '17:00'
      };
    },
    handleStatusChange() {
      if (this.form.status !== 'completed') {
        this.form.appointment_date = '';
        this.form.start_time = '09:00';
        this.form.end_time = '17:00';
      }
    }
  }
}
</script>