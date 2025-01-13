import { createRouter, createWebHistory } from 'vue-router';
import RolePage from '../pages/RolePage.vue';
import UserPage from '../pages/UserPage.vue';
import ResourcePage from "../pages/ResourcePage.vue";
import PrivilegePage from "../pages/PrivilegePage.vue";
import PortfolioPage from "../pages/PortfolioPage.vue";

const routes = [
    {path: '/' , component: PortfolioPage},
    { path: '/roles', component: RolePage },
    { path: '/users', component: UserPage },
    { path: '/resources', component: ResourcePage },
    { path: '/privileges', component: PrivilegePage },

];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
