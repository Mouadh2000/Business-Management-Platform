<template>
  <b-card no-body>
    <b-card-header class="border-0 d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Projects</h3>
      <b-button variant="primary" @click="showAddModal">
        <i class="fa fa-plus mr-2"></i> Add Project
      </b-button>
    </b-card-header>

    <el-table class="table-responsive table"
              header-row-class-name="thead-light"
              :data="projects"
              v-loading="isLoading">
      <el-table-column label="Project Name" min-width="180px" prop="name" />
      <el-table-column label="Description" min-width="250px" prop="description" />
      <el-table-column label="Client" min-width="180px">
        <template v-slot="{ row }">
          {{ row.client_name || 'Loading...' }}
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
      <el-table-column label="Status" min-width="150px">
        <template v-slot="{ row }">
          <badge :type="getStatusType(row.status)">
            {{ row.status }}
          </badge>
        </template>
      </el-table-column>
      <el-table-column label="Bill" min-width="150px">
        <template v-slot="{ row }">
          <div v-if="row.bill_loading">
            <i class="fa fa-spinner fa-spin"></i> Loading...
          </div>
          <div v-else>
            <b-button 
              v-if="row.bill_pdf" 
              variant="outline-primary" 
              size="sm" 
              @click="viewBill(row.bill_pdf)"
            >
              <i class="fa fa-file-pdf-o mr-1"></i> View Bill
            </b-button>
            <span v-else class="text-muted">No bill</span>
          </div>
        </template>
      </el-table-column>
      
      <el-table-column label="Assign Staff" min-width="160px">
        <template v-slot="{ row }">
          <b-button size="sm" variant="info" @click="openAssignStaffModal(row)">
            <i class="fa fa-users mr-1"></i> Assign
          </b-button>
        </template>
      </el-table-column>

      <el-table-column label="Action" min-width="150px">
        <template v-slot="{ row }">
          <div>
            <i class="fa fa-edit mr-3 text-success" style="cursor: pointer;" @click="editProject(row)"></i>
            <i class="fa fa-trash text-danger" style="cursor: pointer;" @click="deleteProject(row)"></i>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <b-card-footer class="py-4 d-flex justify-content-end">
      <base-pagination v-model="currentPage" :per-page="10" :total="totalItems"></base-pagination>
    </b-card-footer>

    <!-- Modals -->
    <add-project-modal 
      ref="addProjectModal"
      @add-project="handleAddProject"
      :clients="clients"
      :isLoading="isAddingProject" />
    <update-project-modal 
      ref="updateProjectModal"
      @update-project="handleUpdateProject"
      :clients="clients"
      :isLoading="isUpdatingProject" />
    <delete-project-modal
      :isOpen="isDeleteModalOpen"
      :toggle="() => { isDeleteModalOpen = false; }"
      :onDelete="handleDeleteProject" />
    <assign-staff-modal
      ref="assignStaffModal"
      :staff="staffUsers"
      :project="selectedProject"
      :onAssign="handleAssignStaff"
    />
    
    <!-- PDF Viewer Modal -->
    <b-modal id="pdf-viewer-modal" size="xl" title="Project Bill" hide-footer>
      <div class="pdf-container" v-if="currentPdfUrl">
        <iframe :src="currentPdfUrl" width="100%" height="600px" frameborder="0"></iframe>
      </div>
      <div v-else class="text-center py-4">
        <p>No PDF available</p>
      </div>
    </b-modal>
  </b-card>
</template>

<script>
import { Table, TableColumn } from 'element-ui';
import AddProjectModal from '../Modals/Project/AddProjectModal.vue';
import UpdateProjectModal from '../Modals/Project/UpdateProjectModal.vue';
import DeleteProjectModal from '../Modals/Project/DeleteProjectModal.vue';
import AssignStaffModal from '../Modals/Project/AssignStaffModal.vue';
import { getAllProjects, addProject, updateProjectById, deleteProjectById, assignStaffToProject, getBillByProjectId } from '../../api/projectApi';
import { getAllClients, getClientById } from '../../api/clientApi';
import { getAllStaffUsers } from '../../api/adminApi';

export default {
  name: 'project-table',
  components: {
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    AddProjectModal,
    UpdateProjectModal,
    DeleteProjectModal,
    AssignStaffModal
  },
  data() {
    return {
      projects: [],
      clients: [],
      staffUsers: [],
      currentPage: 1,
      totalItems: 0,
      isLoading: false,
      isAddingProject: false,
      isUpdatingProject: false,
      isDeleteModalOpen: false,
      selectedProject: null,
      currentPdfUrl: null
    };
  },
  created() {
    this.fetchProjects();
    this.fetchClients();
    this.fetchStaffUsers();
  },
  methods: {
    async fetchProjects() {
      this.isLoading = true;
      try {
        const response = await getAllProjects();
        if (response === false) {
          this.projects = [];
        } else {
          this.projects = response.data.map(project => ({
            ...project,
            client_name: 'Loading...',
            bill_loading: false,
            bill_pdf: null
          }));
          this.totalItems = response.length;
          
          this.projects.forEach(async (project, index) => {
            // Fetch client name
            if (project.client_id) {
              try {
                const clientResponse = await getClientById(project.client_id);
                if (clientResponse && clientResponse.data) {
                  this.$set(this.projects[index], 'client_name', clientResponse.data.company_name);
                }
              } catch (error) {
                console.error(`Error fetching client ${project.client_id}:`, error);
                this.$set(this.projects[index], 'client_name', 'N/A');
              }
            }

            // Fetch bill
            this.$set(this.projects[index], 'bill_loading', true);
            try {
              const billResponse = await getBillByProjectId(project.id);
              if (billResponse && billResponse.success && billResponse.data && billResponse.data.bill_pdf) {
                this.$set(this.projects[index], 'bill_pdf', billResponse.data.bill_pdf);
              }
            } catch (error) {
              console.error(`Error fetching bill for project ${project.id}:`, error);
            } finally {
              this.$set(this.projects[index], 'bill_loading', false);
            }
          });
        }
      } catch (error) {
        console.error('Error fetching projects:', error);
      } finally {
        this.isLoading = false;
      }
    },

    viewBill(pdfUrl) {
      // Create a proper PDF URL from the base64 data if needed
      if (pdfUrl.startsWith('JVBERi0xLjcKMSAw')) {
        this.currentPdfUrl = `data:application/pdf;base64,${pdfUrl}`;
      } else {
        this.currentPdfUrl = pdfUrl;
      }
      this.$bvModal.show('pdf-viewer-modal');
    },

    async fetchClients() {
      try {
        const response = await getAllClients();
        if (response !== false) {
          this.clients = response.data;
        }
      } catch (error) {
        console.error('Error fetching clients:', error);
      }
    },

    async fetchStaffUsers() {
      try {
        const response = await getAllStaffUsers();
        if (response !== false) {
          this.staffUsers = response.data;
        }
      } catch (error) {
        console.error('Error fetching staff users:', error);
      }
    },

    showAddModal() {
      this.$refs.addProjectModal.$bvModal.show('add-project-modal');
    },

    async handleAddProject(newProject) {
      this.isAddingProject = true;
      try {
        const projectData = {
          name: newProject.name,
          description: newProject.description,
          start_date: newProject.start_date,
          deadline: newProject.deadline,
          client_id: newProject.client_id,
          price: newProject.price,
          status: newProject.status || 'pending'
        };

        const response = await addProject(projectData);
        if (response && response.data) {
          // Add loading states for client and bill
          const newProjectData = {
            ...response.data,
            client_name: 'Loading...',
            bill_loading: false,
            bill_pdf: null
          };

          // Fetch client name
          if (response.data.client_id) {
            try {
              const clientResponse = await getClientById(response.data.client_id);
              if (clientResponse && clientResponse.data) {
                newProjectData.client_name = clientResponse.data.company_name;
              }
            } catch (error) {
              console.error('Error fetching client:', error);
              newProjectData.client_name = 'N/A';
            }
          }

          // Fetch bill
          newProjectData.bill_loading = true;
          try {
            const billResponse = await getBillByProjectId(response.data.id);
            if (billResponse && billResponse.success && billResponse.data && billResponse.data.bill_pdf) {
              newProjectData.bill_pdf = billResponse.data.bill_pdf;
            }
          } catch (error) {
            console.error('Error fetching bill:', error);
          } finally {
            newProjectData.bill_loading = false;
          }

          this.projects.unshift(newProjectData);
          this.totalItems++;
        }
      } catch (error) {
        console.error('Error adding project:', error);
      } finally {
        this.isAddingProject = false;
      }
    },

    editProject(project) {
      this.$refs.updateProjectModal.show(project);
    },

    async handleUpdateProject(updatedProject) {
      this.isUpdatingProject = true;
      try {
        const projectData = {
          name: updatedProject.name,
          description: updatedProject.description,
          start_date: updatedProject.start_date,
          deadline: updatedProject.deadline,
          client_id: updatedProject.client_id,
          status: updatedProject.status,
          ...(updatedProject.status === 'completed' && {
            appointment_date: updatedProject.appointment_date,
            start_time: updatedProject.start_time,
            end_time: updatedProject.end_time
          })
        };

        const response = await updateProjectById(updatedProject.id, projectData);
        if (response && response.data) {
          // Update client name if client changed
          if (response.data.client_id && response.data.client_id !== updatedProject.client_id) {
            try {
              const clientResponse = await getClientById(response.data.client_id);
              if (clientResponse && clientResponse.data) {
                response.data.client_name = clientResponse.data.company_name;
              }
            } catch (error) {
              console.error('Error fetching client:', error);
              response.data.client_name = 'N/A';
            }
          } else {
            response.data.client_name = updatedProject.client_name;
          }

          // Update the local projects array
          const index = this.projects.findIndex(p => p.id === updatedProject.id);
          if (index !== -1) {
            this.$set(this.projects, index, {
              ...this.projects[index],
              ...response.data,
              // Preserve the event data structure in the local state
              event: updatedProject.status === 'completed' ? {
                appointment_date: updatedProject.appointment_date,
                start_time: updatedProject.start_time,
                end_time: updatedProject.end_time
              } : null
            });
          }
        }
        this.$refs.updateProjectModal.$bvModal.hide('update-project-modal');
      } catch (error) {
        console.error('Error updating project:', error);
        if (error.response && error.response.data) {
          this.$notify({
            title: 'Error',
            message: error.response.data.message || 'Failed to update project',
            type: 'danger'
          });
        }
      } finally {
        this.isUpdatingProject = false;
      }
    },

    deleteProject(project) {
      this.selectedProject = project;
      this.isDeleteModalOpen = true;
    },

    async handleDeleteProject() {
      if (!this.selectedProject) return;

      try {
        await deleteProjectById(this.selectedProject.id);
        this.projects = this.projects.filter(p => p.id !== this.selectedProject.id);
        this.totalItems--;
      } catch (error) {
        console.error('Error deleting project:', error);
      } finally {
        this.isDeleteModalOpen = false;
      }
    },

    async openAssignStaffModal(project) {
      this.selectedProject = project;
      try {
        const response = await getAllStaffUsers(project.id); 
        if (response !== false) {
          this.staffUsers = response.data;
          this.$refs.assignStaffModal.$bvModal.show('assign-staff-modal');
        }
      } catch (error) {
        console.error('Error fetching staff users:', error);
        this.$notify({
          title: 'Error',
          message: 'Failed to fetch staff members',
          type: 'danger'
        });
      }
    },

    async handleAssignStaff(assignmentData) {
  if (!this.selectedProject) return;

  this.isLoading = true;
  const projectId = this.selectedProject.id;
  
  try {
    // Process all assignments sequentially
    for (var i = 0; i < assignmentData.length; i++) {
      var assignment = assignmentData[i];
      // Handle both single user and array of users
      var userIds = Array.isArray(assignment.user_id) 
        ? assignment.user_id 
        : [assignment.user_id];
      
      // Assign each user individually
      for (var j = 0; j < userIds.length; j++) {
        var userId = userIds[j];
        try {
          var response = await assignStaffToProject(projectId, {
            user_id: userId,
            role: assignment.role
          });

          if (!(response && response.success)) {
            var errorMessage = (response && response.message) || 
              'Failed to assign user ' + userId + ' as ' + assignment.role;
            throw new Error(errorMessage);
          }
        } catch (error) {
          console.error('Error assigning user ' + userId + ':', error);
          this.$notify({
            title: 'Error',
            message: error.message || 'Failed to assign user ' + userId,
            type: 'danger'
          });
          // Continue with next assignment even if one fails
          continue;
        }
      }
    }

    this.$notify({
      title: 'Success',
      message: 'Staff assignments completed',
      type: 'success'
    });

    // Refresh project data
    await this.fetchProjects();

  } catch (error) {
    console.error('Error in assignment process:', error);
    this.$notify({
      title: 'Error',
      message: error.message || 'Failed to complete some staff assignments',
      type: 'danger'
    });
  } finally {
    this.isLoading = false;
  }
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

    formatTime(timeStr) {
      if (!timeStr) return 'N/A';
      return timeStr.substring(0, 5); 
    },

    getStatusType(status) {
      switch (status.toLowerCase()) {
        case 'completed': return 'success';
        case 'in_progress': return 'primary';
        case 'pending': return 'warning';
        case 'canceled': return 'danger';
        default: return 'info';
      }
    }
  }
}
</script>

<style scoped>
.pdf-container {
  width: 100%;
  height: 600px;
}
</style>