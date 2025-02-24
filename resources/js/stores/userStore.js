import { defineStore } from "pinia";
import axios from "axios";


export const useUserStore = defineStore("userStore", {
    state: () => ({
        user: [],
        roles: [],
        permissions: [],
        loading: true,
    }),
    actions: {
        setUserRolesAndPermissions(roles, permissions, user) {
            this.user = user,
            this.roles = roles;
            this.permissions = permissions;
            this.loading = false; // Set loading to false once data is loaded
        },
        async fetchRolesAndPermissions() {
            try {
                const response = await axios.get("/roles/permissions");
                this.setUserRolesAndPermissions(response.data.roles, response.data.permissions, response.data.user);
                this.loading = false;
            } catch (error) {
                console.error(error);
                this.loading = false;
            }
            
        },
        hasRole(role) {
            return this.roles.includes(role);
        },
        hasPermission(permission) {
            return this.permissions.includes(permission);
        },
    },
});
