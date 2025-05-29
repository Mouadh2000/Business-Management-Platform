<template>
  <b-card no-body>
    <b-card-header class="border-0 d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Clients</h3>
      <b-button variant="primary" @click="showAddModal">
        <i class="fa fa-plus mr-2"></i> Add Client
      </b-button>
    </b-card-header>

    <el-table class="table-responsive table"
              header-row-class-name="thead-light"
              :data="clients"
              v-loading="isLoading">
      <el-table-column label="Company Name" min-width="180px" prop="company_name" />
      <el-table-column label="First Name" min-width="150px" prop="first_name" />
      <el-table-column label="Last Name" min-width="150px" prop="last_name" />
      <el-table-column label="Email" min-width="200px" prop="email" />
      <el-table-column label="Phone" min-width="150px" prop="phone" />

      <el-table-column label="Status" min-width="170px">
        <template v-slot="{ row }">
          <div class="badge-dot mr-4"
              :class="{
                'bg-success': row.status === 'active',
                'bg-warning': row.status === 'pending',
                'bg-danger': row.status === 'inactive'
              }"
              style="padding: 8px 12px; border-radius: 12px; display: inline-block;">
            <span class="status" style="color: white; font-size: 14px;">
              {{ row.status.charAt(0).toUpperCase() + row.status.slice(1) }}
            </span>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="Created At" min-width="180px">
        <template v-slot="{ row }">
          {{ formatDate(row.created_at) }}
        </template>
      </el-table-column>

      <el-table-column label="Updated At" min-width="180px">
        <template v-slot="{ row }">
          {{ formatDate(row.updated_at) }}
        </template>
      </el-table-column>

      <el-table-column label="Action" min-width="150px">
        <template v-slot="{ row }">
          <div>
            <i class="fa fa-edit mr-3 text-success" style="cursor: pointer;" @click="editClient(row)"></i>
            <i class="fa fa-trash text-danger" style="cursor: pointer;" @click="deleteClient(row)"></i>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <b-card-footer class="py-4 d-flex justify-content-end">
      <base-pagination v-model="currentPage" :per-page="10" :total="totalItems"></base-pagination>
    </b-card-footer>

    <add-client-modal 
      ref="addClientModal"
      @add-client="handleAddClient"
      :isLoading="isAddingClient" />
    <update-client-modal 
      ref="updateClientModal"
      @update-client="handleUpdateClient"
      :isLoading="isUpdatingClient" />
    <delete-client-modal
      :isOpen="isDeleteModalOpen"
      :toggle="() => { isDeleteModalOpen = false; }"
      :onDelete="handleDeleteClient" />
  </b-card>
</template>

<script>
import { Table, TableColumn } from 'element-ui';
import AddClientModal from '../Modals/Client/AddClientModal.vue';
import UpdateClientModal from '../Modals/Client/UpdateClientModal.vue';
import DeleteClientModal from '../Modals/Client/DeleteClientModal.vue';
import { getAllClients, addClient, updateClientById, deleteClientById } from '../../api/clientApi';

export default {
  name: 'client-table',
  components: {
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    AddClientModal,
    UpdateClientModal,
    DeleteClientModal
  },
  data() {
    return {
      clients: [],
      currentPage: 1,
      totalItems: 0,
      isLoading: false,
      isAddingClient: false,
      isUpdatingClient: false,
      isDeleteModalOpen: false,
      selectedClient: null,
    };
  },
  created() {
    this.fetchClients();
  },
  methods: {
    async fetchClients() {
      this.isLoading = true;
      try {
        const response = await getAllClients();
        if (response === false) {
          this.clients = [];
        } else {
          this.clients = response.data;
          this.totalItems = response.length;
        }
      } catch (error) {
        console.error('Error fetching clients:', error);
      } finally {
        this.isLoading = false;
      }
    },

    showAddModal() {
      this.$refs.addClientModal.$bvModal.show('add-client-modal');
    },

    async handleAddClient(newClient) {
      this.isAddingClient = true;
      try {
        const clientData = {
          company_name: newClient.company_name,
          first_name: newClient.first_name,
          last_name: newClient.last_name,
          email: newClient.email,
          phone: newClient.phone,
          password: newClient.password,
          password_confirmation: newClient.password_confirmation,
          status: newClient.status || 'active'
        };

        await addClient(clientData);
        this.fetchClients();
        this.$notify({
          title: 'Success',
          message: 'Client added successfully. Credentials email sent.',
          type: 'success'
        });
      } catch (error) {
        console.error('Error adding client:', error);
        this.$notify({
          title: 'Error',
          message: (error.response && error.response.data && error.response.data.message) || 'Failed to add client',
          type: 'error'
        });
      } finally {
        this.isAddingClient = false;
      }
    },

    editClient(client) {
      this.$refs.updateClientModal.show(client);
    },

    async handleUpdateClient(updatedClient) {
      this.isUpdatingClient = true;
      try {
        const clientData = {
          company_name: updatedClient.company_name,
          first_name: updatedClient.first_name,
          last_name: updatedClient.last_name,
          email: updatedClient.email,
          phone: updatedClient.phone,
          status: updatedClient.status
        };

        await updateClientById(updatedClient.id, clientData);
        this.fetchClients();
        this.$refs.updateClientModal.$bvModal.hide('update-client-modal');
      } catch (error) {
        console.error('Error updating client:', error);
      } finally {
        this.isUpdatingClient = false;
      }
    },

    deleteClient(client) {
      this.selectedClient = client;
      this.isDeleteModalOpen = true;
    },

    async handleDeleteClient() {
      if (!this.selectedClient) return;

      try {
        await deleteClientById(this.selectedClient.id);
        this.fetchClients();
      } catch (error) {
        console.error('Error deleting client:', error);
      } finally {
        this.isDeleteModalOpen = false;
      }
    },

    formatDate(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleString('fr-TN', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: false
      });
    }
  }
}
</script>