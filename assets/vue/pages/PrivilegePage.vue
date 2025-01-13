<template>
  <div class="privilege-management container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Gestion des Privilèges</h2>
        <div class="search-container">
          <input
              type="text"
              v-model="searchTerm"
              placeholder="Rechercher un utilisateur"
              class="form-control form-control-sm"
          />
          <i class="fas fa-search search-icon"></i>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
            <tr>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>{{ user.lastname }}</td>
              <td>{{ user.firstname }}</td>
              <td>{{ user.email }}</td>
              <td>
                <button
                    class="btn btn-primary btn-sm"
                    @click="openPrivilegeModal(user)"
                >
                  <i class="fas fa-user-shield me-1"></i> Gérer les privilèges
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal de Gestion des Privilèges -->
    <div class="modal fade" id="privilegeModal" tabindex="-1" ref="privilegeModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" v-if="store.selectedUser">
              Privilèges de {{ store.selectedUser.firstname }} {{ store.selectedUser.lastname }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div v-for="resource in store.resources" :key="resource.id" class="col-md-4 mb-3">
                <div class="card">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <span>
                      {{ resource.name }}
                    </span>
                    <div class="form-check form-switch">
                      <input
                          class="form-check-input"
                          type="checkbox"
                          :checked="store.isUserAllowedForResource(resource.id)"
                          @change="confirmPrivilegeChange(resource.id)"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useMainStore } from '../store';
import Swal from 'sweetalert2';

export default {
  name: 'PrivilegePage',

  setup() {
    const store = useMainStore();
    const searchTerm = ref('');

    // Filtrer les utilisateurs
    const filteredUsers = computed(() =>
        store.users.filter((user) =>
            `${user.firstname} ${user.lastname} ${user.email}`
                .toLowerCase()
                .includes(searchTerm.value.toLowerCase())
        )
    );

    // Charger les données initiales
    const fetchInitialData = async () => {
      await store.fetchUsers();
      await store.fetchResources();
    };

    // Ouvrir le modal et charger les privilèges
    const openPrivilegeModal = async (user) => {
      console.log('Utilisateur sélectionné:', user);
      store.selectedUser = user; // Assurez-vous que l'utilisateur est correctement assigné au store

      // Vérifiez si l'utilisateur a bien été sélectionné dans le store
      console.log('selectedUser dans store:', store.selectedUser);
      await store.fetchUserPrivileges(user.id);

      const modal = new bootstrap.Modal(document.getElementById('privilegeModal'));
      modal.show();
    };

    // Confirmer le changement de privilège
    const confirmPrivilegeChange = (resourceId) => {
      const isCurrentlyAllowed = store.isUserAllowedForResource(resourceId);
      const action = isCurrentlyAllowed ? 'retirer' : 'ajouter';

      Swal.fire({
        title: 'Confirmation',
        text: `Voulez-vous vraiment ${action} ce privilège ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, confirmer',
        cancelButtonText: 'Annuler',
      }).then((result) => {
        if (result.isConfirmed) {
          togglePrivilege(resourceId);
        }
      });
    };

    // Effectuer le changement du privilège
    const togglePrivilege = async (resourceId) => {
      if (!store.selectedUser) return;

      try {
        // Vérifiez si le privilège doit être ajouté ou retiré
        const isCurrentlyAllowed = store.isUserAllowedForResource(resourceId);
        const isAllowed = !isCurrentlyAllowed;

        // Appel API pour mettre à jour le privilège
        await store.upsertUserPrivilege({
          userId: store.selectedUser.id,
          resourceId: resourceId,
          isAllowed: isAllowed,
        });

        Swal.fire({
          icon: 'success',
          title: 'Privilège mis à jour',
          showConfirmButton: false,
          timer: 1500,
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'La mise à jour a échoué',
        });
      }
    };

    // Appel initial pour charger les données
    fetchInitialData();

    return {
      store,
      searchTerm,
      filteredUsers,
      openPrivilegeModal,
      confirmPrivilegeChange,
      togglePrivilege,
    };
  },
};
</script>

<style scoped>
/* Style personnalisé */
.search-container {
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  right: 10px;
}

.card-body .form-switch {
  display: flex;
  align-items: center;
}

.card-body .form-switch input {
  width: 60px;
}
</style>
