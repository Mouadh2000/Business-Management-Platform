<template>
  <b-card no-body>
    <b-card-header class="border-0 d-flex justify-content-between align-items-center">
      <h3 class="mb-0">{{ isAdmin ? 'All Tasks' : 'My Tasks' }}</h3>
    </b-card-header>

    <el-table class="table-responsive table"
              header-row-class-name="thead-light"
              :data="tasks"
              v-loading="isLoading">
      <el-table-column label="Title" min-width="180px" prop="title" />
      <el-table-column label="Description" min-width="280px" prop="description" />
      <el-table-column label="Project" min-width="180px">
        <template v-slot="{ row }">
          {{ row.project && row.project.name ? row.project.name : 'N/A' }}
        </template>
      </el-table-column>
      

      <el-table-column label="Priority" min-width="130px">
        <template v-slot="{ row }">
          <badge :type="getPriorityType(row.priority)">
            {{ row.priority }}
          </badge>
        </template>
      </el-table-column>
      <el-table-column label="Start Date" min-width="150px">
        <template v-slot="{ row }">
          {{ formatDate(row.start_date) }}
        </template>
      </el-table-column>
      <el-table-column label="Deadline" min-width="150px">
        <template v-slot="{ row }">
          {{ formatDate(row.deadline) }}
        </template>
      </el-table-column>

      <el-table-column v-if="isAdmin" label="Assigned To" min-width="200px">
        <template v-slot="{ row }">
          <div v-if="row.assignees && row.assignees.length">
            {{ row.assignees[0].email }}
          </div>
          <span v-else>Not assigned</span>
        </template>
      </el-table-column>

      <el-table-column label="Status" min-width="130px">
        <template v-slot="{ row }">
          <badge :type="getStatusType(row.status)">
            {{ row.status }}
          </badge>
          <b-button v-if="!isAdmin" size="sm" variant="link" @click="showTaskStatusModal(row)">
            <i class="fas fa-edit"></i>
          </b-button>
        </template>
      </el-table-column>

      <el-table-column v-if="isAdmin" label="Actions" min-width="150px" align="center">
        <template v-slot="{ row }">
          <div>
            <i class="fas fa-edit mr-3 text-info" style="cursor: pointer;" @click="editTask(row)"></i>
            <i class="fas fa-trash text-danger" style="cursor: pointer;" @click="deleteTask(row)"></i>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <b-card-footer class="py-4 d-flex justify-content-end">
      <base-pagination v-model="currentPage" :per-page="10" :total="totalItems"></base-pagination>
    </b-card-footer>

    <!-- Task Status Modal -->
    <b-modal id="task-status-modal" 
             title="Update Task Status" 
             @ok="updateTaskStatus"
             @hidden="resetTaskStatusModal">
      <b-form-group label="Task Status">
        <b-form-select v-model="selectedTask.status" :options="taskStatusOptions"></b-form-select>
      </b-form-group>
    </b-modal>

    <!-- Assignment Status Modal -->
    <b-modal id="assignment-status-modal" 
             title="Update Assignment Status" 
             @ok="updateAssignmentStatus"
             @hidden="resetAssignmentStatusModal">
      <b-form-group label="Assignment Status">
        <b-form-select v-model="selectedAssignment.status" :options="assignmentStatusOptions"></b-form-select>
      </b-form-group>
      <b-form-group v-if="selectedAssignment.status === 'declined'" label="Decline Reason (Optional)">
        <b-form-textarea v-model="selectedAssignment.decline_reason" rows="3"></b-form-textarea>
      </b-form-group>
    </b-modal>

    <!-- Update Task Modal -->
    <update-task-modal 
      ref="updateTaskModal"
      @update-task="handleUpdateTask"
      :isLoading="isUpdatingTask" />
    
    <!-- Delete Task Modal -->
    <delete-task-modal
      :isOpen="isDeleteModalOpen"
      :toggle="() => { isDeleteModalOpen = false; }"
      :onDelete="handleDeleteTask" />
  </b-card>
</template>

<script>
import { Table, TableColumn } from 'element-ui';
import { getAllTasks, getMyTasks, updateTaskStatus, updateAssignmentStatus, updateTaskById, deleteTaskById } from '../../api/taskApi';
import UpdateTaskModal from '../Modals/Task/UpdateTaskModal.vue';
import DeleteTaskModal from '../Modals/Task/DeleteTaskModal.vue';
import { mapGetters } from 'vuex';

export default {
  name: 'task-table',
  components: {
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    UpdateTaskModal,
    DeleteTaskModal
  },
  data() {
    return {
      tasks: [],
      currentPage: 1,
      totalItems: 0,
      isLoading: false,
      isUpdatingTask: false,
      isDeleteModalOpen: false,
      selectedTask: {
        id: null,
        status: null
      },
      selectedAssignment: {
        taskId: null,
        assignmentId: null,
        status: null,
        decline_reason: null
      },
      taskToDelete: null,
      taskStatusOptions: [
        { value: 'todo', text: 'To Do' },
        { value: 'in_progress', text: 'In Progress' },
        { value: 'completed', text: 'Completed' },
        { value: 'blocked', text: 'Blocked' }
      ],
      assignmentStatusOptions: [
        { value: 'pending', text: 'Pending' },
        { value: 'accepted', text: 'Accepted' },
        { value: 'declined', text: 'Declined' }
      ]
    };
  },
  computed: {
    ...mapGetters('auth', ['isAdmin', 'currentUser'])
  },
  created() {
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      this.isLoading = true;
      try {
        let response;
        
        if (this.isAdmin) {
          response = await getAllTasks();
        } else {
          response = await getMyTasks(this.currentUser.id);
        }

        if (response === false) {
          this.tasks = [];
        } else {
          this.tasks = response.data;
          this.totalItems = response.data.length;
        }
      } catch (error) {
        console.error('Error fetching tasks:', error);
        this.$notify({
          type: 'danger',
          message: 'Failed to load tasks'
        });
      } finally {
        this.isLoading = false;
      }
    },

    showTaskStatusModal(task) {
      this.selectedTask = {
        id: task.id,
        status: task.status
      };
      this.$bvModal.show('task-status-modal');
    },

    showAssignmentStatusModal(task) {
      if (task.assignees && task.assignees.length) {
        this.selectedAssignment = {
          taskId: task.id,
          assignmentId: task.assignees[0].id,
          status: task.assignees[0].assignment_status,
          decline_reason: task.assignees[0].decline_reason || null
        };
        this.$bvModal.show('assignment-status-modal');
      }
    },

    async updateTaskStatus() {
      try {
        await updateTaskStatus(this.selectedTask.id, { status: this.selectedTask.status });
        this.$notify({
          type: 'success',
          message: 'Task status updated successfully'
        });
        this.fetchTasks();
      } catch (error) {
        console.error('Error updating task status:', error);
        this.$notify({
          type: 'danger',
          message: 'Failed to update task status'
        });
      }
    },

    async updateAssignmentStatus() {
      try {
        const payload = {
          status: this.selectedAssignment.status,
          decline_reason: this.selectedAssignment.decline_reason
        };
        
        await updateAssignmentStatus(this.selectedAssignment.assignmentId, payload);
        
        this.$notify({
          type: 'success',
          message: 'Assignment status updated successfully'
        });
        this.fetchTasks();
      } catch (error) {
        console.error('Error updating assignment status:', error);
        this.$notify({
          type: 'danger',
          message: 'Failed to update assignment status'
        });
      }
    },

    resetTaskStatusModal() {
      this.selectedTask = {
        id: null,
        status: null
      };
    },

    resetAssignmentStatusModal() {
      this.selectedAssignment = {
        taskId: null,
        assignmentId: null,
        status: null,
        decline_reason: null
      };
    },

    formatDate(dateStr) {
      if (!dateStr) return 'N/A';
      const date = new Date(dateStr);
      return date.toLocaleString('fr-TN', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    },

    getStatusType(status) {
      if (!status) return 'info';
      switch (status.toLowerCase()) {
        case 'completed': return 'success';
        case 'in_progress':
        case 'in progress': return 'primary';
        case 'todo': return 'warning';
        case 'blocked':
        case 'canceled': return 'danger';
        default: return 'info';
      }
    },

    getPriorityType(priority) {
      if (!priority) return 'info';
      switch (priority.toLowerCase()) {
        case 'high':
        case 'critical': return 'danger';
        case 'medium': return 'warning';
        case 'low': return 'success';
        default: return 'info';
      }
    },

    getAssignmentStatusType(status) {
      if (!status) return 'info';
      switch (status.toLowerCase()) {
        case 'accepted': return 'success';
        case 'pending': return 'warning';
        case 'declined': return 'danger';
        default: return 'info';
      }
    },

    showCreateModal() {
      // Implementation for create modal
    },

    editTask(task) {
      this.$refs.updateTaskModal.show(task);
    },

    async handleUpdateTask(updatedTask) {
      this.isUpdatingTask = true;
      try {
        const response = await updateTaskById(updatedTask.id, updatedTask);
        if (response && response.success) {
          const index = this.tasks.findIndex(t => t.id === updatedTask.id);
          if (index !== -1) {
            this.$set(this.tasks, index, response.data);
          }
          this.$notify({
            type: 'success',
            message: 'Task updated successfully'
          });
          this.$refs.updateTaskModal.$bvModal.hide('update-task-modal');
        }
      } catch (error) {
        console.error('Error updating task:', error);
        this.$notify({
          type: 'danger',
          message: 'Failed to update task'
        });
      } finally {
        this.isUpdatingTask = false;
      }
    },

    deleteTask(task) {
      this.taskToDelete = task;
      this.isDeleteModalOpen = true;
    },

    async handleDeleteTask() {
      if (!this.taskToDelete) return;

      try {
        await deleteTaskById(this.taskToDelete.id);
        this.tasks = this.tasks.filter(t => t.id !== this.taskToDelete.id);
        this.totalItems--;
        this.$notify({
          type: 'success',
          message: 'Task deleted successfully'
        });
      } catch (error) {
        console.error('Error deleting task:', error);
        this.$notify({
          type: 'danger',
          message: 'Failed to delete task'
        });
      } finally {
        this.isDeleteModalOpen = false;
        this.taskToDelete = null;
      }
    }
  }
}
</script>