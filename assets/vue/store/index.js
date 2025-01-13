import { defineStore } from 'pinia';
import {useRoles} from '../services/roleService';
import {useUsers} from '../services/userService';
import {useResource} from '../services/resourceService';
import {useUserPrivilege} from '../services/userPrivilegeService';

export const useMainStore = defineStore('main', {
  state: () => ({
    roles: [],
    users: [],
    resources: [],
    userPrivileges: [],
    selectedUser: null,
  }),

  getters: {
    getRoleById: (state) => (roleId) =>
      state.roles.find((role) => role.id === roleId),
    getUserById: (state) => (userId) =>
      state.users.find((user) => user.id === userId),
    getResourceById: (state) => (resourceId) =>
      state.resources.find((resource) => resource.id === resourceId),
    getUserPrivilegesByUserId: (state) => (userId) =>
      state.userPrivileges.filter((privilege) => privilege.user.id === userId),
    isUserAllowedForResource: (state) => (resourceId) => {
      if (!state.selectedUser) return false;
      return state.userPrivileges.some(
        (privilege) =>
          privilege.user.id === state.selectedUser.id &&
          privilege.resource.id === resourceId
      );
    },
  },

  actions: {
    async fetchRoles() {
      try {
        const {data} = await useRoles.getAllRoles();
        this.roles = data;
      } catch (error) {
        console.error('Error fetching roles:', error);
      }
    },

    async fetchUsers() {
      try {
        const {data} = await useUsers.getAllUsers();
        this.users = data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },

    async fetchResources() {
      try {
        const {data} = await useResource.getAllResources();
        this.resources = data;
      } catch (error) {
        console.error('Error fetching resources:', error);
      }
    },

    async fetchUserPrivileges(userId) {
      try {
        const {data} = await useUserPrivilege.getUserPrivileges(userId);
        this.userPrivileges = data.user.resources || [];
        this.selectedUser = data.user;
      } catch (error) {
        console.error('Error fetching user privileges:', error);
        this.userPrivileges = [];
        this.selectedUser = null;
      }
    },

    async upsertUserPrivilege({userId, resourceId, isAllowed}) {
      try {
        const data = await useUserPrivilege.updatePrivileges({userId, resourceId, isAllowed});
        console.log('API response after update:', data);
        this.updateUserPrivilege(data);
      } catch (error) {
        console.error('Error updating user privilege:', error);
      }
    },

    updateUserPrivilege(userPrivilege) {
      if (!userPrivilege || !userPrivilege.user || !userPrivilege.resource) {
        console.error('Invalid user privilege data:', userPrivilege);
        return;
      }
      const index = this.userPrivileges.findIndex(
        (privilege) =>
          privilege.user.id === userPrivilege.user.id &&
          privilege.resource.id === userPrivilege.resource.id
      );
      if (index !== -1) {
        this.userPrivileges[index] = userPrivilege;
      } else {
        this.userPrivileges.push(userPrivilege);
      }
    }
  }
});