import { createRouter, createWebHistory } from 'vue-router'
import { store } from './store'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () => import('./views/HomeView.vue') },
    { path: '/discover', component: () => import('./views/DiscoverView.vue') },
    { path: '/about', component: () => import('./views/AboutView.vue') },
    { path: '/products', component: () => import('./views/ProductsView.vue') },
    { path: '/business/:id', component: () => import('./views/BusinessView.vue'), props: true },
    { path: '/login', component: () => import('./views/AuthView.vue'), props: { mode: 'login' }, meta: { bare: true } },
    { path: '/register', component: () => import('./views/AuthView.vue'), props: { mode: 'register' }, meta: { bare: true } },
    { path: '/account', component: () => import('./views/AccountView.vue'), meta: { auth: true } },
    { path: '/owner', component: () => import('./views/owner/OwnerDashboard.vue'), meta: { role: 'owner' } },
    { path: '/admin', component: () => import('./views/admin/AdminDashboard.vue'), meta: { role: 'admin' } },
    { path: '/:pathMatch(.*)*', redirect: '/' }
  ]
})
router.beforeEach((to) => {
  const m = to.meta
  if (m.auth || m.role) { if (!store.user) return { path: '/login', query: { redirect: to.fullPath } }; if (m.role && store.user.role !== m.role) return '/' }
})
router.afterEach(() => window.scrollTo(0, 0))
export default router