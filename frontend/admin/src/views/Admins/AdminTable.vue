<template>
  <b-card no-body>
    <b-card-header class="border-0 d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Admins and Employees</h3>
      <b-button variant="primary" @click="showAddModal">
        <i class="fa fa-plus mr-2"></i> Add Admin
      </b-button>
    </b-card-header>

    <el-table class="table-responsive table"
              header-row-class-name="thead-light"
              :data="admins"
              v-loading="isLoading">
      <!-- Your table columns remain the same -->
      <el-table-column label="First Name" min-width="150px" prop="first_name"></el-table-column>
      <el-table-column label="Last Name" min-width="150px" prop="last_name"></el-table-column>
      <el-table-column label="Username" min-width="150px" prop="username"></el-table-column>
      <el-table-column label="Email" min-width="200px" prop="email"></el-table-column>
      
      <el-table-column label="Role" min-width="230px">
        <template v-slot="{ row }">
          <div class="badge-dot mr-4"
              :class="(row.is_staff && row.is_admin) ? 'bg-success' : 'bg-info'"
              style="padding: 8px 12px; border-radius: 12px; display: inline-block;">
            <span class="status" style="color: white; font-size: 14px;">
              {{ getRole(row) }}
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
            <i class="fa fa-edit mr-3 text-success" style="cursor: pointer;" @click="editAdmin(row)"></i> 
            <i class="fa fa-trash text-danger" style="cursor: pointer;"  @click="deleteAdmin(row)"></i> 
          </div>
        </template>
      </el-table-column>
    </el-table>

    <b-card-footer class="py-4 d-flex justify-content-end">
      <base-pagination v-model="currentPage" :per-page="10" :total="totalItems"></base-pagination>
    </b-card-footer>
    
    <add-admin-modal 
      ref="addAdminModal" 
      @add-admin="handleAddAdmin"
      :isLoading="isAddingAdmin" />
    <update-admin-modal 
      ref="updateAdminModal" 
      @update-admin="handleUpdateAdmin"
      :isLoading="isUpdatingAdmin" />
      <delete-admin-modal
        :isOpen="isDeleteModalOpen"
        :toggle="() => { isDeleteModalOpen = false; }"
        :onDelete="handleDeleteAdmin" />

  </b-card>
</template>

<script>
import { Table, TableColumn } from 'element-ui';
import AddAdminModal from '../Modals/Admin/AddAdminModal.vue';
import UpdateAdminModal from '../Modals/Admin/UpdateAdminModal.vue';
import DeleteAdminModal from '../Modals/Admin/DeleteAdminModal.vue';
import { getAllUsers, addUser, updateUserById, deleteUserById } from '../../api/adminApi';

export default {
  name: 'admin-table',
  components: {
    [Table.name]: Table,
    [TableColumn.name]: TableColumn,
    AddAdminModal,
    UpdateAdminModal,
    DeleteAdminModal
  },
  data() {
    return {
      admins: [],
      currentPage: 1,
      totalItems: 0,
      isLoading: false,
      isAddingAdmin: false,
      isUpdatingAdmin: false,
      isDeleteModalOpen: false,
      selectedAdmin: null,

    };
  },
  created() {
    this.fetchAdmins();
  },
  methods: {
    async fetchAdmins() {
      this.isLoading = true;
      try {
        const response = await getAllUsers();
        if (response === false) {
          this.admins = [];
        } else {
          this.admins = response.data;
          this.totalItems = response.length;
        }
      } catch (error) {
        console.error('Error fetching admins:', error);
      } finally {
        this.isLoading = false;
      }
    },
    showAddModal() {
      this.$refs.addAdminModal.$bvModal.show('add-admin-modal');
    },
    async handleAddAdmin(newAdmin) {
      this.isAddingAdmin = true;
      try {
        const userData = {
          first_name: newAdmin.firstName,
          last_name: newAdmin.lastName,
          username: newAdmin.username,
          email: newAdmin.email,
          password: newAdmin.password,
          is_staff: newAdmin.isStaff,
          is_admin: newAdmin.isAdmin
        };
        
        await addUser(userData);
        this.fetchAdmins(); 
      } catch (error) {
        console.error('Error adding admin:', error);
      } finally {
        this.isAddingAdmin = false;
      }
    },
    editAdmin(admin) {
      this.$refs.updateAdminModal.show(admin);
    },

    async handleUpdateAdmin(updatedAdmin) {
      this.isUpdatingAdmin = true;
      try {
        const userData = {
          first_name: updatedAdmin.firstName,
          last_name: updatedAdmin.lastName,
          username: updatedAdmin.username,
          email: updatedAdmin.email,
          is_staff: updatedAdmin.isStaff,
          is_admin: updatedAdmin.isAdmin
        };
        
        if (updatedAdmin.password) {
          userData.password = updatedAdmin.password;
        }
        
        await updateUserById(updatedAdmin.id, userData);
        this.fetchAdmins();
        this.$refs.updateAdminModal.$bvModal.hide('update-admin-modal');
      } catch (error) {
        console.error('Error updating admin:', error);
      } finally {
        this.isUpdatingAdmin = false;
      }
    },

    deleteAdmin(admin) {
      this.selectedAdmin = admin; 
      this.isDeleteModalOpen = true;
    },

    async handleDeleteAdmin() {
      if (!this.selectedAdmin) return;

      try {

        await deleteUserById(this.selectedAdmin.id);  
        this.fetchAdmins(); 
      } catch (error) {
        console.error('Error deleting admin:', error);
      } finally {
        this.isDeleteModalOpen = false;
      }
    },
    getRole(row) {
      if (row.is_staff && row.is_admin) return 'Administrator';
      if (row.is_staff && !row.is_admin) return 'Employee';
      return 'User';
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