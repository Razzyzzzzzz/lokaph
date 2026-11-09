<script setup>
import { computed } from 'vue'
import { Home, Store, ShoppingBag, Heart, User } from 'lucide-vue-next'
import { store } from '@/store'
const account = computed(() => !store.user ? '/login' : store.user.role === 'admin' ? '/admin' : store.user.role === 'owner' ? '/owner' : '/account')
const items = computed(() => [
  { to: '/', icon: Home, t: 'Home' }, { to: '/discover', icon: Store, t: 'Stores' }, { to: '/products', icon: ShoppingBag, t: 'Products' },
  { to: '/account', icon: Heart, t: 'Saved' }, { to: account.value, icon: User, t: store.user ? 'Account' : 'Log in' }])
</script>
<template>
  <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t bg-white text-center text-[11px] md:hidden">
    <RouterLink v-for="n in items" :key="n.t" :to="n.to" class="flex flex-col items-center gap-0.5 py-2" active-class="text-brand"><component :is="n.icon" :size="22" />{{ n.t }}</RouterLink>
  </nav>
</template>
