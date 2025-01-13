<script>
import { ref, onMounted, computed } from 'vue';
import { useUsers } from '../services/userService';
import { useRoles } from '../services/roleService';
import Swal from 'sweetalert2';

export default {
  name: 'UserPage',

  setup() {
    const users = ref([]);
    const roles = ref([]);
    const searchTerm = ref('');
    const formData = ref({
      firstname: '',
      lastname: '',
      email: '',
      password: '',
      roleId: ''
    });
    const currentUserId = ref(null);
    const isModalOpen = ref(false);

    const generatePassword = () => {
      const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
      let password = '';
      for (let i = 0; i < 12; i++) {
        password += characters.charAt(Math.floor(Math.random() * characters.length));
      }
      formData.value.password = password;
      if (document.getElementById('userPassword')) {
        document.getElementById('userPassword').value = password;
      }
    };

    const resetForm = () => {
      formData.value = {
        firstname: '',
        lastname: '',
        email: '',
        password: '',
        roleId: ''
      };
      currentUserId.value = null;
    };

    const fetchUsers = async () => {
      try {
        const response = await useUsers.getAllUsers();
        users.value = response.data.map((user) => ({
          ...user,
          roleNames: user.roles.join(', ') || 'Non défini',
        }));
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Erreur lors de la récupération des utilisateurs',
        });
      }
    };

    const fetchRoles = async () => {
      try {
        const response = await useRoles.getAllRoles();
        roles.value = response.data;
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Erreur lors de la récupération des rôles',
        });
      }
    };

    const handleSubmit = async () => {
      try {
        if (currentUserId.value) {
          await useUsers.updateUser({
            id: currentUserId.value,
            ...formData.value
          });
        } else {
          await useUsers.createUser(formData.value);
        }

        await fetchUsers();
        Swal.fire({
          icon: 'success',
          title: currentUserId.value ? 'Utilisateur mis à jour' : 'Utilisateur créé',
          showConfirmButton: false,
          timer: 1500
        });

        resetForm();
        closeModal();
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: `Erreur lors de ${currentUserId.value ? 'la mise à jour' : 'la création'} de l'utilisateur`,
        });
      }
    };

    const deleteUser = async (userId) => {
      const result = await Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      });

      if (result.isConfirmed) {
        try {
          await useUsers.deleteUser(userId);
          await fetchUsers();
          Swal.fire({
            icon: 'success',
            title: 'Utilisateur supprimé',
            showConfirmButton: false,
            timer: 1500
          });
        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Erreur lors de la suppression de l\'utilisateur',
          });
        }
      }
    };

    const editUser = (user) => {
      formData.value = {
        firstname: user.firstname,
        lastname: user.lastname,
        email: user.email,
        password: '',
        roleId: user.roleId || ''
      };
      currentUserId.value = user.id;
      openModal();
    };

    const openModal = () => {
      isModalOpen.value = true;
    };

    const closeModal = () => {
      isModalOpen.value = false;
      resetForm();
    };

    const filteredUsers = computed(() =>
        users.value.filter(user =>
            `${user.firstname} ${user.lastname} ${user.email} ${user.roleNames}`
                .toLowerCase()
                .includes(searchTerm.value.toLowerCase())
        )
    );

    onMounted(() => {
      fetchRoles();
      fetchUsers();
    });

    return {
      users,
      roles,
      searchTerm,
      formData,
      filteredUsers,
      currentUserId,
      isModalOpen,
      generatePassword,
      handleSubmit,
      deleteUser,
      editUser,
      openModal,
      closeModal,
    };
  },
};
</script>

<template>
  <div class="user-management container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Gestion des Utilisateurs</h2>
        <button @click="openModal" class="btn btn-primary">
          <i class="fas fa-plus me-2"></i>Créer un utilisateur
        </button>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="search-container">
              <input
                  type="text"
                  v-model="searchTerm"
                  placeholder="Rechercher un utilisateur"
                  class="form-control"
              >
              <i class="fas fa-search search-icon"></i>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
            <tr>
              <th>Prénom</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
              <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>{{ user.firstname }}</td>
              <td>{{ user.lastname }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.roleNames }}</td>
              <td class="text-end">
                <button @click="editUser(user)" class="btn btn-warning btn-sm me-2">
                  <i class="fas fa-edit me-1"></i>Modifier
                </button>
                <button @click="deleteUser(user.id)" class="btn btn-danger btn-sm">
                  <i class="fas fa-trash me-1"></i>Supprimer
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal utilisateur -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ currentUserId ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}</h3>
          <button @click="closeModal" class="btn-close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleSubmit">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Prénom</label>
                <input
                    v-model="formData.firstname"
                    type="text"
                    class="form-control"
                    required
                >
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nom</label>
                <input
                    v-model="formData.lastname"
                    type="text"
                    class="form-control"
                    required
                >
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input
                  v-model="formData.email"
                  type="email"
                  class="form-control"
                  required
              >
            </div>

            <div class="mb-3">
              <label class="form-label">Mot de passe</label>
              <div class="input-group">
                <input
                    v-model="formData.password"
                    type="text"
                    class="form-control"
                    :required="!currentUserId"
                >
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="generatePassword"
                >
                  Générer
                </button>
              </div>
            </div>

            <div class="mb-4" v-if="!currentUserId">
              <label class="form-label">Rôle</label>
              <select
                  v-model="formData.roleId"
                  class="form-select"
                  required
              >
                <option value="" disabled>Choisir un rôle</option>
                <option
                    v-for="role in roles"
                    :key="role.id"
                    :value="role.id"
                >
                  {{ role.name }}
                </option>
              </select>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">
                Annuler
              </button>
              <button type="submit" class="btn btn-primary">
                {{ currentUserId ? 'Mettre à jour' : 'Créer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.user-management {
  padding: 20px;
}

.search-container {
  position: relative;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1rem;
}

.modal-footer {
  padding: 1rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.table {
  margin-bottom: 0;
}

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
}

.form-control:focus,
.form-select:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
