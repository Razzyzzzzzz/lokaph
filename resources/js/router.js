import { createRouter, createWebHistory } from 'vue-router'
import { store } from './store'

const homeFor = (u) => (u.role === 'admin' ? '/admin' : u.role === 'owner' ? '/owner' : '/')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () => import('./views/HomeView.vue') },
    { path: '/discover', component: () => import('./views/DiscoverView.vue') },
    { path: '/products', component: () => import('./views/ProductsView.vue') },
    { path: '/about', component: () => import('./views/AboutView.vue') },
    { path: '/business/:id', component: () => import('./views/BusinessView.vue'), props: true },
    { path: '/login', component: () => import('./views/AuthView.vue'), props: { mode: 'login' }, meta: { bare: true, guest: true } },
    { path: '/register', component: () => import('./views/AuthView.vue'), props: { mode: 'register' }, meta: { bare: true, guest: true } },
    { path: '/account', component: () => import('./views/AccountView.vue'), meta: { auth: true } },
    { path: '/messages/:id?', component: () => import('./views/MessagesView.vue'), props: true, meta: { auth: true } },
    { path: '/owner', component: () => import('./views/owner/OwnerDashboard.vue'), meta: { role: 'owner' } },
    { path: '/admin', component: () => import('./views/admin/AdminDashboard.vue'), meta: { role: 'admin' } },
    { path: '/:pathMatch(.*)*', redirect: '/' }
  ]
})
router.beforeEach((to) => {
  const m = to.meta
  if (m.guest && store.user) return homeFor(store.user)
  if (m.auth || m.role) { if (!store.user) return { path: '/login', query: { redirect: to.fullPath } }; if (m.role && store.user.role !== m.role) return '/' }
})
router.afterEach(() => window.scrollTo(0, 0))
export default router