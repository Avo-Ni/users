import axios from 'axios';
export const useResource = {
    getAllResources() {
        return axios.get(`/resources`);
    },

    createResource(resource) {
        return axios.post(`/resource`, resource);
    },

    deleteResource(resourceId) {
        return axios.delete(`/resource/${resourceId}`);
    },

    updateResource(resourceId, resource) {
        return axios.put(`/resource/${resourceId}`, resource);
    }
};
