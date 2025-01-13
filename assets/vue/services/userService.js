import axios from 'axios';

export const useUsers = {
    getAllUsers() {
        return axios.get('/users');
    },

    getUser(userId) {
        return axios.get(`/user/${userId}`);
    },

    createUser(user) {
        const response = axios.post('/user', user);
        console.log(response);
        return response;
    },

    deleteUser(userId) {
        return axios.delete(`/user/${userId}`);
    },

    updateUser(user) {
        return axios.put(`/user/${user.id}`, user);
    }
};
