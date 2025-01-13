<script>
import {ref, onMounted, computed} from 'vue';
import {useUsers} from '../services/userService';
import {useRoles} from '../services/roleService';
import Swal from 'sweetalert2';

export default {
  name: 'UserPage',

  setup() {
    const users = ref([]);
    const roles = ref([]);
    const searchTerm = ref('');
    const newUserFirstname = ref('');
    const newUserLastname = ref('');
    const newUserEmail = ref('');
    const newUserPassword = ref('');
    const selectedRole = ref('');
    const currentUserId = ref(null);

    const generatePassword = () => {
      const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
      let password = '';
      for (let i = 0; i < 12; i++) {
        password += characters.charAt(Math.floor(Math.random() * characters.length));
      }
      newUserPassword.value = password;
    };

    const fetchUsers = async () => {
      try {
        const response = await useUsers.getAllUsers();
        users.value = response.data.map((user) => ({
          ...user,
          roleNames: user.roles.map(role => role.name).join(', ') || 'Non défini',
        }));
      } catch (error) {
        console.error('Erreur lors de la récupération des utilisateurs', error);
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
        console.error('Erreur lors de la récupération des rôles', error);
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Erreur lors de la récupération des rôles',
        });
      }
    };

    const createUser = async () => {
      try {
        const newUser = {
          firstname: newUserFirstname.value,
          lastname: newUserLastname.value,
          email: newUserEmail.value,
          password: newUserPassword.value,
          roleId: selectedRole.value,
        };

        await useUsers.createUser(newUser);

        resetForm();
        fetchUsers();
        Swal.fire({
          icon: 'success',
          title: 'Utilisateur créé',
          showConfirmButton: false,
          timer: 1500
        });
      } catch (error) {
        console.error('Erreur lors de la création de l\'utilisateur :', error.response?.data || error.message);
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Erreur lors de la création de l\'utilisateur',
        });
      }
    };

    const updateUser = async () => {
      if (currentUserId.value && newUserFirstname.value && newUserLastname.value && newUserEmail.value) {
        try {
          const updatedUser = {
            id: currentUserId.value,
            firstname: newUserFirstname.value,
            lastname: newUserLastname.value,
            email: newUserEmail.value,
            password: newUserPassword.value,
            roleId: selectedRole.value,
          };

          await useUsers.updateUser(updatedUser);
          resetForm();
          fetchUsers();
          Swal.fire({
            icon: 'success',
            title: 'Utilisateur mis à jour',
            showConfirmButton: false,
            timer: 1500
          });
        } catch (error) {
          console.error('Erreur lors de la mise à jour de l\'utilisateur', error);
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Erreur lors de la mise à jour de l\'utilisateur',
          });
        }
      }
    };

    const deleteUser = async (userId) => {
      try {
        await useUsers.deleteUser(userId);
        fetchUsers();
        Swal.fire({
          icon: 'success',
          title: 'Utilisateur supprimé',
          showConfirmButton: true,
          timer: 1500
        });
      } catch (error) {
        console.error('Erreur lors de la suppression de l\'utilisateur', error);
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          text: 'Erreur lors de la suppression de l\'utilisateur',
        });
      }
    };

    const editUser = (user) => {
      newUserFirstname.value = user.firstname;
      newUserLastname.value = user.lastname;
      newUserEmail.value = user.email;
      selectedRole.value = user.roleId || '';
      newUserPassword.value = '';
      currentUserId.value = user.id;
      openUserModal();
    };

    const openUserModal = async () => {
      Swal.fire({
        title: currentUserId.value ? 'Modifier l\'utilisateur' : 'Créer un nouvel utilisateur',
        width: '600px',
        html: `
        <div class="form-group">
          <label for="firstName">Prénom</label>
          <input id="firstName" class="swal2-input" placeholder="Prénom" value="${newUserFirstname.value}">
        </div>
        <div class="form-group">
          <label for="lastName">Nom</label>
          <input id="lastName" class="swal2-input" placeholder="Nom" value="${newUserLastname.value}">
        </div>
        <div class="form-group">
          <label for="userEmail">Email</label>
          <input id="userEmail" class="swal2-input" placeholder="Email" value="${newUserEmail.value}">
        </div>
        <div class="form-group">
          <label for="userPassword">Mot de passe</label>
          <input id="userPassword" class="swal2-input" placeholder="Mot de passe" value="${newUserPassword.value}">
          <button type="button" class="btn btn-primary btn-sm mt-2" id="generatePasswordButton">Générer un mot de passe</button>
        </div>
        <div class="form-group">
          <label for="userRole">Rôle</label>
          <select id="userRole" class="swal2-select">
            <option value="" disabled>Choisir un rôle</option>
            ${roles.value.map(role => `<option value="${role.id}" ${String(role.id) === selectedRole.value ? 'selected' : ''}>${role.name}</option>`).join('')}
          </select>
        </div>
      `,
        focusConfirm: false,
        preConfirm: () => {
          const firstName = document.getElementById('firstName').value;
          const lastName = document.getElementById('lastName').value;
          const userEmail = document.getElementById('userEmail').value;
          const userPassword = document.getElementById('userPassword').value;
          const userRole = document.getElementById('userRole').value;

          if (!firstName || !lastName || !userEmail || !userRole) {
            Swal.showValidationMessage('Tous les champs sont requis');
            return false;
          }

          newUserFirstname.value = firstName;
          newUserLastname.value = lastName;
          newUserEmail.value = userEmail;
          newUserPassword.value = userPassword;
          selectedRole.value = userRole;

          return true;
        },
        didRender: () => {
          document.getElementById('generatePasswordButton').addEventListener('click', () => {
            generatePassword();
            document.getElementById('userPassword').value = newUserPassword.value;
          });
        },
      }).then((result) => {
        if (result.isConfirmed) {
          if (currentUserId.value) {
            updateUser();
          } else {
            createUser();
          }
        }
      });
    };

    const resetForm = () => {
      newUserFirstname.value = '';
      newUserLastname.value = '';
      newUserEmail.value = '';
      newUserPassword.value = '';
      selectedRole.value = '';
      currentUserId.value = null;
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
      filteredUsers,
      generatePassword,
      createUser,
      updateUser,
      deleteUser,
      editUser,
      openUserModal,
    };
  },
};
</script>

<template>
  <div class="user-management container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Gestion des Utilisateurs</h2>
        <button @click="openUserModal" class="btn btn-primary btn-sm">Créer un utilisateur</button>
      </div>
      <div class="card-body">
        <div class="search-container mb-3">
          <input
              type="text"
              v-model="searchTerm"
              placeholder="Rechercher un utilisateur"
              class="form-control form-control-sm"
          >
          <i class="fas fa-search search-icon"></i>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
            <tr>
              <th>Prénom</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>{{ user.firstname }}</td>
              <td>{{ user.lastname }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.roleNames }}</td>
              <td>
                <button @click="editUser(user)" class="btn btn-warning btn-sm me-1">Modifier</button>
                <button @click="deleteUser(user.id)" class="btn btn-danger btn-sm">Supprimer</button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.user-management {
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

.search-container {
  position: relative;
  max-width: 300px;
}

.search-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.table-responsive {
  margin-bottom: 20px;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th, .table td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.btn-warning {
  background-color: #ffc107;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-warning:hover {
  background-color: #e0a800;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-danger:hover {
  background-color: #c82333;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.swal2-input, .swal2-select {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  margin-bottom: 10px;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.swal2-checkbox {
  display: block;
  margin-bottom: 10px;
}
</style>
