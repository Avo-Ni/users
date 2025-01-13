import axios from 'axios';

export const useUserPrivilege = {
    getUserPrivileges(userId) {
        return axios.get(`/user/${userId}/privileges`);
    },

    async updatePrivileges({ userId, resourceId, isAllowed }) {
        try {
            const response = await axios.post('/user-privileges', {
                userId,
                resourceId,
                isAllowed,
            });
            return response.data;
        } catch (error) {
            throw error;
        }
    },
};
