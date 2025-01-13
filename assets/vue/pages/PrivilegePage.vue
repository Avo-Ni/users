<template>
  <div class="user-privileges-container container-fluid">
    <div class="card" v-if="!isManagingPrivileges">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Gestion des Privilèges Utilisateurs</h2>
        <div class="search-container position-relative">
          <input
              type="text"
              v-model="searchTerm"
              placeholder="Rechercher un utilisateur..."
              class="form-control form-control-sm pe-4"
          />
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>{{ user.firstname }} {{ user.lastname }}</td>
              <td>{{ user.email }}</td>
              <td class="text-center">
                <button @click="manageUserPrivileges(user)" class="btn btn-primary btn-sm">
                  Gérer les privilèges
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isManagingPrivileges" class="card mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">
          Privilèges pour
          <span class="text-primary">
            {{ selectedUser.firstname }} {{ selectedUser.lastname }}
          </span>
        </h3>
        <button @click="finishPrivilegeManagement" class="btn btn-success btn-sm">
          Terminer
        </button>
      </div>
      <div class="card-body">
        <div class="privileges-grid">
          <div v-for="resource in resources" :key="resource.id" class="privilege-card">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold">{{ resource.name }}</span>
              <div class="form-check form-switch">
                <input
                    class="form-check-input"
                    type="checkbox"
                    :id="'privilege-' + resource.id"
                    :checked="isResourceAllowed(resource.id)"
                    @change="togglePrivilege(resource)"
                />
                <label class="form-check-label" :for="'privilege-' + resource.id"></label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Swal from 'sweetalert2';

export default {
  name: 'PrivilegePage',
  data() {
    return {
      searchTerm: '',
      selectedUser: null,
      isManagingPrivileges: false
    };
  },
  computed: {
    ...mapState(['users', 'resources']),
    filteredUsers() {
      if (!this.users) return [];
      return this.users.filter(user =>
          user.firstname.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          user.lastname.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          user.email.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    }
  },
  methods: {
    ...mapActions([
      'fetchUsers',
      'fetchResources',
      'fetchUserPrivileges',
      'upsertUserPrivilege'
    ]),
    async manageUserPrivileges(user) {
      console.log('User before management:', user);

      if (user.resources) {
        this.selectedUser = {...user};
        console.log('Using user resources directly:', this.selectedUser.resources);
      } else {
        // Sinon, récupérez les privilèges
        try {
          await this.fetchUserPrivileges(user.id);
          this.selectedUser = this.$store.state.selectedUser;
        } catch (error) {
          console.error('Error fetching user privileges:', error);
          this.selectedUser = {...user, resources: []};
        }
      }

      console.log('Selected User:', this.selectedUser);
      console.log('Selected User Resources:', this.selectedUser.resources);

      this.isManagingPrivileges = true;
    },
    finishPrivilegeManagement() {
      this.isManagingPrivileges = false;
      this.selectedUser = null;
    },
    isResourceAllowed(resourceId) {
      console.log('Checking resource:', resourceId);
      console.log('Selected User:', this.selectedUser);

      if (!this.selectedUser || !this.selectedUser.resources) {
        console.log('No user or resources');
        return false;
      }

      const resource = this.selectedUser.resources.find(r => r.id === resourceId);
      console.log('Found resource:', resource);

      const isAllowed = resource &&
          resource.userPrivileges &&
          resource.userPrivileges.length > 0 &&
          resource.userPrivileges[0].allowed === true;

      console.log('Is allowed:', isAllowed);
      return isAllowed;
    },
    async togglePrivilege(resource) {
      if (!this.selectedUser) return;

      const currentPrivilege = this.selectedUser.resources
          .find(r => r.id === resource.id)?.userPrivileges[0];

      const isAllowed = !(currentPrivilege?.allowed || false);

      console.log('Toggling privilege for resource:', resource);
      console.log('Current privilege:', currentPrivilege);
      console.log('New allowed state:', isAllowed);

      try {
        await this.upsertUserPrivilege({
          userId: this.selectedUser.id,
          resourceId: resource.id,
          isAllowed: isAllowed
        });

        const resourceIndex = this.selectedUser.resources.findIndex(r => r.id === resource.id);
        if (resourceIndex !== -1) {
          this.selectedUser.resources[resourceIndex].userPrivileges = [
            {allowed: isAllowed}
          ];
        }

        Swal.fire({
          title: 'Privilège mis à jour',
          text: `Accès ${isAllowed ? 'accordé' : 'refusé'} pour ${resource.name}`,
          icon: isAllowed ? 'success' : 'warning',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      } catch (error) {
        console.error('Error updating privilege:', error);
        Swal.fire({
          title: 'Erreur',
          text: 'Impossible de mettre à jour le privilège',
          icon: 'error'
        });
      }
    }
  },
  async mounted() {
    await this.fetchUsers();
    await this.fetchResources();
  }
};
</script>

<style scoped>
.user-privileges-container {
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-header h2 {
  margin: 0;
  font-size: 24px;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.table-responsive {
  margin-bottom: 20px;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th, .table td {
  border: 1px solid #dee2e6;
  padding: 10px;
  text-align: left;
}

.table th {
  background-color: #f1f3f5;
}

.search-container {
  position: relative;
}

.privileges-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 15px;
}

.privilege-card {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
}

.privilege-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transform: translateY(-5px);
}
</style>
