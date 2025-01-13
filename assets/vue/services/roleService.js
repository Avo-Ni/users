import axios from 'axios';

export const useRoles = {
    getAllRoles() {
        return axios.get('/roles');
    },

    createRole(role) {
        return axios.post('/role', role);
    },

    deleteRole(roleId) {
        return axios.delete(`/role/${roleId}`);
    },

    updateRole(role) {
        return axios.put(`/role/${role.id}`, role);
    }
};
