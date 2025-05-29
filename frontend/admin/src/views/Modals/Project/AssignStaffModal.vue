<template>
  <b-modal id="assign-staff-modal" title="Assign Staff to Project" @hide="resetModal">
    <div v-if="staffAssignments.length">
      <div v-for="(assignment, index) in staffAssignments" :key="index" class="mb-3 border rounded p-3 position-relative">
        <b-form-group label="Select staff members:">
          <div class="d-flex align-items-center flex-wrap mb-2">
            <b-badge 
              v-for="staffId in assignment.selectedStaff" 
              :key="staffId"
              variant="primary" 
              pill 
              class="mr-2 mb-2 d-flex align-items-center"
            >
              {{ getStaffName(staffId) }}
              <b-button 
                variant="link" 
                size="sm" 
                class="ml-1 p-0 text-white" 
                @click="removeStaff(index, staffId)"
              >
                &times;
              </b-button>
            </b-badge>
          </div>
          
          <b-form-select
            v-model="assignment.newStaffSelection"
            :options="getStaffOptions(assignment.selectedStaff)"
            @change="addStaffToAssignment(index)"
          >
            <template #first>
              <option :value="null" disabled>Select a staff member</option>
            </template>
          </b-form-select>
        </b-form-group>

        <b-form-group label="Role:" class="mt-2">
          <b-form-input
            v-model="assignment.staffRole"
            placeholder="Enter role (e.g. Developer, Manager)"
            required
          ></b-form-input>
        </b-form-group>

        <b-button
          variant="danger"
          size="sm"
          class="position-absolute"
          style="top: 10px; right: 10px;"
          @click="removeAssignment(index)"
          v-if="staffAssignments.length > 1"
        >
          -
        </b-button>
      </div>

      <b-button variant="success" size="sm" @click="addAssignment" v-if="hasAvailableStaff">+ Add Another</b-button>
      <p v-else class="text-muted">No more staff members available to assign</p>
    </div>
    <div v-else>
      <p>No staff members available for assignment</p>
    </div>

    <template #modal-footer>
      <b-button variant="secondary" @click="closeModal">Cancel</b-button>
      <b-button variant="primary" @click="assignStaff" :disabled="!isFormValid">
        Assign
      </b-button>
    </template>
  </b-modal>
</template>

<script>
export default {
  props: {
    staff: {
      type: Array,
      default: () => []
    },
    project: {
      type: Object,
      default: null
    },
    onAssign: {
      type: Function,
      required: true
    }
  },
  data() {
    return {
      staffAssignments: [
        { 
          selectedStaff: [], // Now an array to hold multiple selections
          newStaffSelection: null, // Temporary selection
          staffRole: '' 
        }
      ],
      isLoading: false
    };
  },
  computed: {
    filteredStaff() {
      const selectedIds = this.getAllSelectedStaffIds();
      return this.staff.filter(user => !selectedIds.includes(user.id));
    },
    staffOptions() {
      return this.filteredStaff.map(user => ({
        value: user.id,
        text: user.full_name || user.username || user.email
      }));
    },
    isFormValid() {
      return this.staffAssignments.every(
        a => a.selectedStaff.length > 0 && a.staffRole.trim()
      );
    },
    hasAvailableStaff() {
      return this.filteredStaff.length > 0;
    }
  },
  methods: {
    getAllSelectedStaffIds() {
      return this.staffAssignments.flatMap(a => a.selectedStaff);
    },
    getStaffName(staffId) {
      const staff = this.staff.find(u => u.id === staffId);
      return staff ? (staff.full_name || staff.username || staff.email) : '';
    },
    getStaffOptions(currentAssignmentStaff) {
      const allSelectedIds = this.getAllSelectedStaffIds();
      return this.staff
        .filter(user => !allSelectedIds.includes(user.id) || currentAssignmentStaff.includes(user.id))
        .map(user => ({
          value: user.id,
          text: user.full_name || user.username || user.email
        }));
    },
    addStaffToAssignment(index) {
      if (this.staffAssignments[index].newStaffSelection) {
        if (!this.staffAssignments[index].selectedStaff.includes(this.staffAssignments[index].newStaffSelection)) {
          this.staffAssignments[index].selectedStaff.push(this.staffAssignments[index].newStaffSelection);
        }
        this.staffAssignments[index].newStaffSelection = null;
      }
    },
    removeStaff(assignmentIndex, staffId) {
      this.staffAssignments[assignmentIndex].selectedStaff = 
        this.staffAssignments[assignmentIndex].selectedStaff.filter(id => id !== staffId);
    },
    assignStaff() {
      if (this.project && this.isFormValid) {
        const assignments = this.staffAssignments.flatMap(a => 
          a.selectedStaff.map(staffId => ({
            user_id: staffId,
            role: a.staffRole
          }))
        );
        this.onAssign(assignments);
        this.closeModal();
      }
    },
    addAssignment() {
      if (this.hasAvailableStaff) {
        this.staffAssignments.push({ 
          selectedStaff: [], 
          newStaffSelection: null,
          staffRole: '' 
        });
      }
    },
    removeAssignment(index) {
      this.staffAssignments.splice(index, 1);
    },
    resetModal() {
      this.staffAssignments = [{ 
        selectedStaff: [], 
        newStaffSelection: null,
        staffRole: '' 
      }];
    },
    closeModal() {
      this.$bvModal.hide('assign-staff-modal');
    }
  }
};
</script>

<style scoped>
.badge {
  font-size: 100%;
  padding: 0.35em 0.65em;
}
</style>