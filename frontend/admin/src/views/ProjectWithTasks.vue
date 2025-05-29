<template>
    <div>
      <base-header class="pb-6 pb-8 pt-5 pt-md-8 bg-gradient-primary">
        <card-stats :cards="adminStats" />
      </base-header>
      
      <b-container fluid class="mt--7 workspace-container">
        <b-row>
          <!-- Projects Panel (Left) -->
          <b-col lg="4" class="projects-panel">
            <b-card no-body class="projects-card">
              <b-card-header class="border-0 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Projects</h3>
                
              </b-card-header>
              
              <div class="project-list">
                <b-card 
                  v-for="project in projects" 
                  :key="project.id"
                  :class="['project-card', { 'active': selectedProject && selectedProject.id === project.id }]"
                  @click="selectProject(project)">
                  
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-1">{{ project.name }}</h5>
                    <badge :type="getProjectStatusType(project)">
                      {{ formatDate(project.deadline) }}
                    </badge>
                  </div>
                  
                  <div class="project-meta mt-2">
                    <small class="text-muted">
                      <i class="fas fa-users mr-1"></i>
                      {{ project.employees ? project.employees.length : 0 }} members
                    </small>
                    <small class="text-muted ml-3">
                      <i class="fas fa-tasks mr-1"></i>
                      {{ countTasksByStatus(project, 'completed') }}/{{ countTotalTasks(project) }} done
                    </small>
                  </div>
                  
                  <b-progress 
                    class="mt-2" 
                    height="4px" 
                    :value="calculateCompletion(project)" 
                    :variant="getCompletionVariant(project)">
                  </b-progress>
                  <!-- Team Members Section -->
                    <div class="team-members mt-3" v-if="project.users && project.users.length">
                    <h6 class="mb-2">Team Members:</h6>
                    <div class="member-item" v-for="user in project.users" :key="user.id">
                        <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                            {{ user.email }} ({{ user.role }})
                            </small>
                        </div>
                        <b-button 
                        v-if="isAdmin"
                        size="sm" 
                        variant="link" 
                        class="mt-2 p-0"
                        @click.stop="openAssignTaskModal(user, project)">
                        <i class="fas fa-plus-circle text-primary"></i> Add Task
                        </b-button>
                        </div>
                    </div>
                    </div>
                </b-card>
              </div>
            </b-card>
          </b-col>
          
          <!-- Tasks Panel (Right) -->
          <b-col lg="8" class="tasks-panel">
            <template v-if="selectedProject">
              <b-card no-body class="tasks-card">
                <b-card-header class="border-0 d-flex justify-content-between align-items-center">
                  <div>
                    <h3 class="mb-0">{{ selectedProject.name }}</h3>
                    <small class="text-muted">
                      Deadline: {{ formatDate(selectedProject.deadline) }}
                    </small>
                  </div>
                  <div>
                    <b-button-group>
                      <b-button 
                        size="sm" 
                        :variant="viewMode === 'table' ? 'primary' : 'outline-primary'"
                        @click="viewMode = 'table'">
                        <i class="fas fa-table"></i>
                      </b-button>
                      <b-button 
                        size="sm" 
                        :variant="viewMode === 'kanban' ? 'primary' : 'outline-primary'"
                        @click="viewMode = 'kanban'">
                        <i class="fas fa-th-large"></i>
                      </b-button>
                    </b-button-group>
                  </div>
                </b-card-header>
                
                <!-- Table View -->
                <template v-if="viewMode === 'table'">
                  <b-form-group label="Filter by status" class="mb-3 ml-3">
                      <b-form-checkbox-group v-model="statusFilter" :options="taskStatusOptions" buttons button-variant="outline-primary" size="sm"></b-form-checkbox-group>
                    </b-form-group>
                  <el-table 
                    class="table-responsive table"
                    header-row-class-name="thead-light"
                    :data="filteredTasks"
                    v-loading="isLoading">
                    
                    
                    <el-table-column label="Title" min-width="180px" prop="title" sortable />
                    <el-table-column label="Description" min-width="280px">
                      <template v-slot="{ row }">
                        <div>
                          <template v-if="row.description && row.description.length > 40">
                            {{ row.description.substring(0, 40) }}...
                            <b-button 
                              variant="link" 
                              size="sm" 
                              @click="showFullDescription(row.description)">
                              View More
                            </b-button>
                          </template>
                          <template v-else>
                            {{ row.description || 'N/A' }}
                          </template>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column label="Priority" min-width="130px" sortable>
                      <template v-slot="{ row }">
                        <badge :type="getPriorityType(row.priority)">
                          {{ row.priority }}
                        </badge>
                      </template>
                    </el-table-column>
                    <el-table-column label="Start Date" min-width="150px" sortable>
                      <template v-slot="{ row }">
                        {{ formatDate(row.start_date) }}
                      </template>
                    </el-table-column>
                    <el-table-column label="Deadline" min-width="150px" sortable>
                      <template v-slot="{ row }">
                        {{ formatDate(row.deadline) }}
                      </template>
                    </el-table-column>
                    <el-table-column label="Assigned To" min-width="200px">
                      <template v-slot="{ row }">
                        <div>
                          {{ row.email }}
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column label="Status" min-width="130px" sortable>
                      <template v-slot="{ row }">
                        <badge :type="getStatusType(row.status)">
                          {{ row.status }}
                        </badge>
                        
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
                  
                  <b-card-footer class="py-4 d-flex justify-content-between align-items-center">
                    
                    <base-pagination v-model="currentPage" :per-page="10" :total="filteredTasks.length"></base-pagination>
                  </b-card-footer>
                </template>
                
                <!-- Kanban View -->
                <template v-else>
                  <div class="kanban-board">
                    <div class="kanban-column" v-for="status in kanbanStatuses" :key="status.value">
                      <div class="kanban-header">
                        <h6>{{ status.text }}</h6>
                        <small>{{ countTasksByStatus(selectedProject, status.value) }} tasks</small>
                      </div>
                      <draggable 
                        class="kanban-list"
                        :list="getTasksByStatus(selectedProject, status.value)"
                        group="tasks"
                        @end="onTaskDragEnd">
                        <div 
                          class="kanban-item"
                          v-for="task in getTasksByStatus(selectedProject, status.value)"
                          :key="task.id"
                          @click="editTask(task)">
                          <div class="kanban-item-header">
                            <h6>{{ task.title }}</h6>
                            <badge :type="getPriorityType(task.priority)">
                              {{ task.priority }}
                            </badge>
                          </div>
                          <small class="text-muted">
                            Due: {{ formatDate(task.deadline) }}
                          </small>
                          <div class="kanban-item-footer mt-2">
                            <small v-if="task.assignees && task.assignees.length">
                              <i class="fas fa-user"></i> {{ task.assignees[0].email }}
                            </small>
                          </div>
                        </div>
                      </draggable>
                    </div>
                  </div>
                </template>
              </b-card>
            </template>
            
            <template v-else>
              <b-card no-body class="empty-state">
                <b-card-body class="text-center py-5">
                  <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                  <h4>Select a project to view tasks</h4>
                  <p class="text-muted">Click on a project from the left panel to see its tasks</p>
                </b-card-body>
              </b-card>
            </template>
          </b-col>
        </b-row>
      </b-container>
      
      <assign-task-modal
        id="assignTaskModal"
        :employee="selectedEmployee"
        :project="selectedProject"
        @task-assigned="fetchProjects" />
      
      <update-task-modal 
        ref="updateTaskModal"
        @update-task="handleUpdateTask"
        :isLoading="isUpdatingTask" />
      
      <delete-task-modal
        :isOpen="isDeleteModalOpen"
        :toggle="() => { isDeleteModalOpen = false; }"
        :onDelete="handleDeleteTask" />

      <b-modal id="descriptionModal" title="Task Description" ok-only>
        <p>{{ fullDescription }}</p>
      </b-modal>
    </div>
  </template>
  
  <script>
  import { Table, TableColumn } from 'element-ui';
  import draggable from 'vuedraggable';
  import { 
    getProjectWithInfo, 
  } from '@/api/projectApi';
  import {updateTaskById, deleteTaskById} from '@/api/taskApi'
  import AssignTaskModal from './Modals/Task/AssignTaskModal.vue';
  import UpdateTaskModal from './Modals/Task/UpdateTaskModal.vue';
  import DeleteTaskModal from './Modals/Task/DeleteTaskModal.vue';
  import { mapGetters } from 'vuex';
  import CardStats from './CardStats.vue';
  import { adminStats } from '@/data/cardStats';

  
  export default {
    name: 'ProjectTaskWorkspace',
    components: {
      [Table.name]: Table,
      [TableColumn.name]: TableColumn,
      draggable,
      AssignTaskModal,
      UpdateTaskModal,
      DeleteTaskModal,
      CardStats,

    },
    data() {
      return {
        projects: [],
        adminStats,
        selectedProject: null,
        selectedEmployee: null,
        currentPage: 1,
        isLoading: false,
        isUpdatingTask: false,
        isDeleteModalOpen: false,
        fullDescription: '',
        viewMode: 'table', // 'table' or 'kanban'
        statusFilter: ['todo', 'in_progress', 'completed', 'blocked'],
        selectedTask: {
          id: null,
          status: null
        },
        taskToDelete: null,
        taskStatusOptions: [
          { value: 'todo', text: 'To Do' },
          { value: 'in_progress', text: 'In Progress' },
          { value: 'completed', text: 'Completed' },
          { value: 'blocked', text: 'Blocked' }
        ],
        kanbanStatuses: [
          { value: 'todo', text: 'To Do' },
          { value: 'in_progress', text: 'In Progress' },
          { value: 'completed', text: 'Done' },
          { value: 'blocked', text: 'Blocked' }
        ]
      };
    },
    computed: {
      ...mapGetters('auth', ['isAdmin', 'currentUser']),
      filteredTasks() {
        if (!this.selectedProject) return [];
        
        return this.selectedProject.tasks.filter(task => 
          this.statusFilter.includes(task.status)
        );
      }
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
              tasks: project.tasks || [],
              employees: project.users || []
            }));
            
            // If there's a selected project, refresh its data
            if (this.selectedProject) {
              const updatedProject = this.projects.find(p => p.id === this.selectedProject.id);
              if (updatedProject) {
                this.selectedProject = updatedProject;
              }
            }
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
      showFullDescription(description) {
        this.fullDescription = description;
        this.$bvModal.show('descriptionModal');
      },
      
      selectProject(project) {
        this.selectedProject = project;
        this.currentPage = 1;
      },
      
      openAssignTaskModal(employee, project) {
        this.selectedEmployee = employee || null;
        this.selectedProject = project || this.selectedProject;
        this.$bvModal.show('assign-task-modal');
    },
      
      
      editTask(task) {
        this.$refs.updateTaskModal.show(task);
      },
      
      async handleUpdateTask(updatedTask) {
        this.isUpdatingTask = true;
        try {
          const response = await updateTaskById(updatedTask.id, updatedTask);
          if (response && response.success) {
            this.fetchProjects();
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
          this.$notify({
            type: 'success',
            message: 'Task deleted successfully'
          });
          this.fetchProjects();
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
      },
      
      async onTaskDragEnd(evt) {
        if (evt.to !== evt.from) {
          const taskId = parseInt(evt.item.dataset.id);
          const newStatus = evt.to.dataset.status;
          
          try {
            await updateTaskStatus(taskId, { status: newStatus });
            this.$notify({
              type: 'success',
              message: 'Task status updated'
            });
          } catch (error) {
            console.error('Error updating task status:', error);
            this.fetchProjects(); // Revert UI if API call fails
            this.$notify({
              type: 'danger',
              message: 'Failed to update task status'
            });
          }
        }
      },
      
      // Helper methods
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
          case 'in_progress': return 'primary';
          case 'todo': return 'warning';
          case 'blocked': return 'danger';
          default: return 'info';
        }
      },
      
      getPriorityType(priority) {
        if (!priority) return 'info';
        switch (priority.toLowerCase()) {
          case 'high': return 'danger';
          case 'medium': return 'warning';
          case 'low': return 'success';
          default: return 'info';
        }
      },
      
      getProjectStatusType(project) {
        if (!project.deadline) return 'info';
        const now = new Date();
        const deadline = new Date(project.deadline);
        const diffDays = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24));
        
        if (diffDays < 0) return 'danger'; // Overdue
        if (diffDays <= 3) return 'warning'; // Due soon
        return 'success'; // On track
      },
      
      countTotalTasks(project) {
        return project.tasks ? project.tasks.length : 0;
      },
      
      countTasksByStatus(project, status) {
        if (!project.tasks) return 0;
        return project.tasks.filter(task => task.status === status).length;
      },
      
      getTasksByStatus(project, status) {
        if (!project.tasks) return [];
        return project.tasks.filter(task => task.status === status);
      },
      
      calculateCompletion(project) {
        if (!project.tasks || project.tasks.length === 0) return 0;
        const completed = project.tasks.filter(t => t.status === 'completed').length;
        return Math.round((completed / project.tasks.length) * 100);
      },
      
      getCompletionVariant(project) {
        const percent = this.calculateCompletion(project);
        if (percent >= 90) return 'success';
        if (percent >= 50) return 'primary';
        return 'warning';
      }
    }
  };
  </script>
  
  <style scoped>
  .workspace-container {
    min-height: calc(100vh - 200px);
  }
  
  .projects-panel {
    border-right: 1px solid #eee;
    height: 100%;
    overflow-y: auto;
  }
  
  .tasks-panel {
    height: 100%;
    overflow-y: auto;
  }
  
  .project-card {
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .project-card.active {
    border-left: 4px solid #5e72e4;
    background-color: #f8f9fe;
  }
  
  .kanban-board {
    display: flex;
    padding: 1rem;
    overflow-x: auto;
    min-height: 500px;
  }
  
  .kanban-column {
    flex: 1;
    min-width: 250px;
    margin: 0 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    padding: 0.5rem;
  }
  
  .kanban-header {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
  }
  
  .kanban-list {
    min-height: 100px;
  }
  
  .kanban-item {
    background: white;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .kanban-item:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .kanban-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
  }
  
  .empty-state {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .team-members {
  border-top: 1px solid #eee;
  padding-top: 0.5rem;
}

.member-item {
  padding: 0.25rem 0;
  border-bottom: 1px solid #f5f5f5;
}

.member-item:last-child {
  border-bottom: none;
}
#descriptionModal .modal-body {
  white-space: pre-wrap;
  word-break: break-word;
}
  </style>