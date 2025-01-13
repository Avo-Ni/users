<template>
  <div class="resource-management container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Gestion des Ressources</h2>
        <button @click="openResourceModal" class="btn btn-primary btn-sm">Créer une ressource</button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
            <tr>
              <th>Nom</th>
              <th>Path</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="resource in resources" :key="resource.id">
              <td>{{ resource.name }}</td>
              <td>{{ resource.path }}</td>
              <td>
                <button @click="editResource(resource)" class="btn btn-warning btn-sm me-1">Modifier</button>
                <button @click="deleteResource(resource.id)" class="btn btn-danger btn-sm">Supprimer</button>
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
import { ref, onMounted, computed } from 'vue';
import { useMainStore } from '../store';
import Swal from 'sweetalert2';

export default {
  name: 'ResourcePage',

  setup() {
    const store = useMainStore();
    const resources = computed(() => store.resources);
    const newResourceName = ref('');
    const newResourcePath = ref('');
    const currentResourceId = ref(null);

    const fetchResources = () => {
      store.fetchResources();
    };

    const createResource = async () => {
      const newResource = {
        name: newResourceName.value,
        path: newResourcePath.value,
      };
      await store.createResource(newResource);
      resetForm();
      Swal.fire({
        icon: 'success',
        title: 'Ressource créée',
        showConfirmButton: false,
        timer: 1500
      });
    };

    const updateResource = async () => {
      const updatedResource = {
        name: newResourceName.value,
        path: newResourcePath.value,
      };
      await store.updateResource(currentResourceId.value, updatedResource);
      resetForm();
      Swal.fire({
        icon: 'success',
        title: 'Ressource mise à jour',
        showConfirmButton: false,
        timer: 1500
      });
    };

    const deleteResource = async (resourceId) => {
      const result = await Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action ne peut pas être annulée !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler'
      });

      if (result.isConfirmed) {
        await store.deleteResource(resourceId);
        Swal.fire({
          icon: 'success',
          title: 'Ressource supprimée',
          showConfirmButton: false,
          timer: 1500
        });
      }
    };

    const editResource = (resource) => {
      newResourceName.value = resource.name;
      newResourcePath.value = resource.path;
      currentResourceId.value = resource.id;
      openResourceModal();
    };

    const openResourceModal = () => {
      Swal.fire({
        title: currentResourceId.value ? 'Modifier la ressource' : 'Créer une nouvelle ressource',
        html: `
          <input 
            id="swal-input-resourceName" 
            class="swal2-input" 
            placeholder="Nom" 
            value="${newResourceName.value || ''}"
          >
          <input 
            id="swal-input-resourcePath" 
            class="swal2-input" 
            placeholder="Path" 
            value="${newResourcePath.value || ''}"
          >
        `,
        didOpen: () => {
          const nameInput = Swal.getPopup().querySelector('#swal-input-resourceName');
          const pathInput = Swal.getPopup().querySelector('#swal-input-resourcePath');
          nameInput.value = newResourceName.value || '';
          pathInput.value = newResourcePath.value || '';
        },
        preConfirm: () => {
          const nameInput = Swal.getPopup().querySelector('#swal-input-resourceName');
          const pathInput = Swal.getPopup().querySelector('#swal-input-resourcePath');
          const resourceName = nameInput.value;
          const resourcePath = pathInput.value;
          
          if (!resourceName || !resourcePath) {
            Swal.showValidationMessage('Le nom et le path sont requis');
            return false;
          }
          return {
            name: resourceName,
            path: resourcePath
          };
        }
      }).then((result) => {
        if (result.isConfirmed && result.value) {
          newResourceName.value = result.value.name;
          newResourcePath.value = result.value.path;
          if (currentResourceId.value) {
            updateResource();
          } else {
            createResource();
          }
        }
      });
    };

    const resetForm = () => {
      newResourceName.value = '';
      newResourcePath.value = '';
      currentResourceId.value = null;
    };

    onMounted(() => {
      fetchResources();
    });

    return {
      resources,
      createResource,
      updateResource,
      deleteResource,
      editResource,
      openResourceModal,
    };
  },
};
</script>

<style scoped>
.resource-management {
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
</style>