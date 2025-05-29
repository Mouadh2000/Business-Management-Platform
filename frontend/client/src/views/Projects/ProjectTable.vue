<template>
  <b-card no-body>
    <b-card-header class="border-0 d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Projects</h3>
    </b-card-header>

    <el-table class="table-responsive table"
              header-row-class-name="thead-light"
              :data="projects"
              v-loading="isLoading">
      <el-table-column label="Project Name" min-width="180px" prop="name" />
      <el-table-column label="Description" min-width="250px" prop="description" />
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
      

    </el-table>

    <b-card-footer class="py-4 d-flex justify-content-end">
      <base-pagination v-model="currentPage" :per-page="10" :total="totalItems"></base-pagination>
    </b-card-footer>
    
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
import { getAllProjects, getBillByProjectId } from '../../api/projectApi';

export default {
  name: 'project-table',
  components: {
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
  },
  data() {
    return {
      projects: [],
      currentPage: 1,
      totalItems: 0,
      isLoading: false,
      currentPdfUrl: null
    };
  },
  created() {
    this.fetchProjects();
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
      if (pdfUrl.startsWith('JVBERi0xLjcKMSAw')) {
        this.currentPdfUrl = `data:application/pdf;base64,${pdfUrl}`;
      } else {
        this.currentPdfUrl = pdfUrl;
      }
      this.$bvModal.show('pdf-viewer-modal');
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
        case 'in progress': return 'primary';
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