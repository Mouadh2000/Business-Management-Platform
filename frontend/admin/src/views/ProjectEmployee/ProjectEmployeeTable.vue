<template>
  <div>
    <b-card v-for="(project, index) in projects" :key="index" no-body class="mb-4">
      <b-card-header class="border-0 d-flex justify-content-between align-items-center">
        <h3 class="mb-0">🧰 Project: {{ project.name }}</h3>
        <small class="text-muted">Deadline: {{ formatDate(project.deadline) }}</small>
      </b-card-header>

      <el-table
        class="table-responsive table"
        header-row-class-name="thead-light"
        :data="project.employees"
        v-loading="isLoading"
      >
        <el-table-column label="Employee Name" min-width="180px" prop="username" />
        <el-table-column label="Email" min-width="200px" prop="email" />
        <el-table-column label="Role" min-width="150px" prop="role" />
        

        <el-table-column label="Action" min-width="150px">
          <template v-slot="{ row }">
            <div>
              <el-tooltip content="Assign Task" placement="top">
                <i class="fa fa-plus-circle text-primary" style="cursor: pointer;" 
                  @click="openAssignTaskModal(row, project)"></i>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <b-card-footer class="py-4 d-flex justify-content-end">
        <base-pagination v-model="currentPage" :per-page="10" :total="project.employees.length" />
      </b-card-footer>
    </b-card>

    <!-- Assign Task Modal -->
    <assign-task-modal
      ref="assignTaskModal"
      :employee="selectedEmployee"
      :project="selectedProject"
      @task-assigned="fetchProjects"
    />
  </div>
</template>

<script>
import { Table, TableColumn, Tooltip } from 'element-ui';
import { getProjectWithInfo } from '../../api/projectApi';
import AssignTaskModal from '../Modals/Task/AssignTaskModal.vue';

export default {
  name: 'ProjectEmployeeTable',
  components: {
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    [Tooltip.name]: Tooltip,
    AssignTaskModal
  },
  data() {
    return {
      projects: [],
      currentPage: 1,
      isLoading: false,
      selectedEmployee: null,
      selectedProject: null
    };
  },
  created() {
    this.fetchProjects();
  },
  methods: {
    async fetchProjects() {
      this.isLoading = true;
      try {
        const response = await getProjectWithInfo();
        if (response && response.success) {
          this.projects = response.data.map(project => ({
            ...project,
            employees: project.users.map(user => ({
              ...user,
              tasks: (user.tasks ? user.tasks.filter(task => task.project_id === project.id) : [])
            }))
          }));
        }
      } catch (error) {
        console.error('Error fetching projects:', error);
        this.$notify.error({
          title: 'Error',
          message: 'Failed to fetch projects'
        });
      } finally {
        this.isLoading = false;
      }
    },
    openAssignTaskModal(employee, project) {
      this.selectedEmployee = employee;
      this.selectedProject = project;
      this.$refs.assignTaskModal.$bvModal.show('assign-task-modal');
    },
    formatDate(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleString('fr-TN', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    },
  }
};
</script>

<style scoped>
ul {
  list-style-type: none;
  padding-left: 0;
  margin-bottom: 0;
}
ul li {
  margin-bottom: 5px;
  padding: 5px;
  border-radius: 4px;
  background-color: #f8f9fa;
}
</style>