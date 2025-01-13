<template>
  <div class="role-management container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Gestion des Rôles</h2>
        <button @click="openRoleModal" class="btn btn-primary btn-sm">Créer un rôle</button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
            <tr>
              <th>Nom du Rôle</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="role in roles" :key="role.id">
              <td>{{ role.name }}</td>
              <td>
                <button @click="editRole(role)" class="btn btn-warning btn-sm me-1">Modifier</button>
                <button @click="deleteRole(role.id)" class="btn btn-danger btn-sm">Supprimer</button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { ref, onMounted } from 'vue';
import { useRoles } from '../services/roleService';
import Swal from 'sweetalert2';

export default {
  name: 'RolePage',

  setup() {
    const roles = ref([]);
    const newRoleName = ref('');
    const currentRoleId = ref(null);

    const fetchRoles = async () => {
      try {
        const response = await useRoles.getAllRoles();
        roles.value = response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des rôles', error);
      }
    };

    const createRole = async () => {
      try {
        const newRole = { name: newRoleName.value };
        await useRoles.createRole(newRole);
        newRoleName.value = '';
        fetchRoles();
        Swal.fire({
          icon: 'success',
          title: 'Rôle créé',
          showConfirmButton: false,
          timer: 1500
        });
      } catch (error) {
        console.error('Erreur lors de la création du rôle', error);
      }
    };

    const updateRole = async () => {
      if (currentRoleId.value && newRoleName.value) {
        try {
          const updatedRole = { id: currentRoleId.value, name: newRoleName.value };
          await useRoles.updateRole(updatedRole);
          newRoleName.value = '';
          currentRoleId.value = null;
          fetchRoles();
          Swal.fire({
            icon: 'success',
            title: 'Rôle mis à jour',
            showConfirmButton: false,
            timer: 1500
          });
        } catch (error) {
          console.error('Erreur lors de la mise à jour du rôle', error);
        }
      }
    };

    const deleteRole = async (roleId) => {
      try {
        await useRoles.deleteRole(roleId);
        fetchRoles();
        Swal.fire({
          icon: 'success',
          title: 'Rôle supprimé',
          showConfirmButton: false,
          timer: 1500
        });
      } catch (error) {
        console.error('Erreur lors de la suppression du rôle', error);
      }
    };

    const editRole = (role) => {
      newRoleName.value = role.name;
      currentRoleId.value = role.id;
      openRoleModal();
    };

    const openRoleModal = () => {
      Swal.fire({
        title: currentRoleId.value ? 'Modifier le rôle' : 'Créer un nouveau rôle',
        html: `
          <input id="roleName" class="swal2-input" placeholder="Nom du rôle" value="${newRoleName.value}">
        `,
        focusConfirm: false,
        preConfirm: () => {
          const roleName = document.getElementById('roleName').value;
          if (!roleName) {
            Swal.showValidationMessage('Le nom du rôle est requis');
            return false;
          }
          newRoleName.value = roleName;
          return true;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          if (currentRoleId.value) {
            updateRole();
          } else {
            createRole();
          }
        }
      });
    };

    onMounted(fetchRoles);

    return { roles, fetchRoles, deleteRole, editRole, openRoleModal };
  }
};
</script>
<style scoped>
.role-management {
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
</style>
