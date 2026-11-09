<script setup>
import BrandLogo from './BrandLogo.vue'
import { store } from '@/store'
import { useRouter } from 'vue-router'
const router = useRouter()
const logout = async () => { await store.logout(); router.push('/') }
</script>
<template>
  <header class="sticky top-0 z-30 border-b bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4">
      <div class="select-none"><BrandLogo size="h-12" /></div>
      <nav class="ml-6 hidden gap-5 text-sm font-medium md:flex">
        <RouterLink to="/discover">Businesses</RouterLink><RouterLink to="/products">Products</RouterLink>
        <RouterLink v-if="store.user" to="/account">Saved</RouterLink>
        <RouterLink v-if="store.user?.role === 'owner'" to="/owner">My business</RouterLink>
        <RouterLink v-if="store.user?.role === 'admin'" to="/admin">Admin</RouterLink>
      </nav>
      <div class="ml-auto flex items-center gap-2 text-sm">
        <button class="max-w-[9.5rem] truncate rounded-full bg-green-50 px-3 py-1.5 text-green-700" @click="store.locOpen = true">{{ store.loc ? store.loc.label : 'Set location' }}</button>
        <button v-if="store.user" class="text-slate-500" @click="logout">Log out</button>
        <RouterLink v-else to="/login" class="btn btn-primary !py-1.5">Log in</RouterLink>
      </div>
    </div>
  </header>
</template>