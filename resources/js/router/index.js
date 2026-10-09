import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Login from '../views/Auth/Login.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import Dashboard from '../views/Dashboard.vue';
import Departments from '../views/Departments/Index.vue';
import Users from '../views/Users/Index.vue';

const routes = [
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },
    {
        path: '/',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'dashboard', component: Dashboard },
            { path: 'phong-ban', name: 'departments', component: Departments },
            { path: 'nguoi-dung', name: 'users', component: Users },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Middleware điều hướng Auth
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({ name: 'login' });
    }
    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'dashboard' });
    }
    next();
});

export default router;