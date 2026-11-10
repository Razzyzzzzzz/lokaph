<script setup>
import BrandLogo from './BrandLogo.vue'
import Icon from './Icon.vue'
import { store } from '@/store'
import { useRouter } from 'vue-router'
const router = useRouter()
const CONTACT_EMAIL = 'Test1@gmail.com' // palitan ng support email mo
const logout = async () => { await store.logout(); router.push('/') }
const link = 'flex items-center gap-1.5 border-b-2 border-transparent py-1 hover:text-[#fbbf24]'
const closeMenu = (e) => e.currentTarget.blur() // closes the Categories dropdown after a pick
</script>
<template>
  <header class="sticky top-0 z-30 bg-gradient-to-r from-[#0b1f3a] to-[#12307a] text-white shadow-lg">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4">
      <RouterLink to="/" class="select-none"><BrandLogo size="h-10" tagline /></RouterLink>
      <nav class="ml-6 hidden items-center gap-5 text-sm font-medium lg:flex">
        <RouterLink to="/" :class="link" exact-active-class="!border-[#7c3aed]"><Icon name="home" /> Home</RouterLink>
        <RouterLink to="/discover" :class="link" active-class="!border-[#7c3aed]"><Icon name="search" /> Explore</RouterLink>

        <!-- Categories dropdown: each item filters Explore by that category -->
        <div class="group relative">
          <button type="button" :class="link"><Icon name="grid" /> Categories <Icon name="chevron" :size="12" /></button>
          <div class="invisible absolute left-0 top-full z-40 w-56 pt-3 opacity-0 transition group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
            <ul class="max-h-80 overflow-auto rounded-xl bg-white p-2 text-[#0b1f3a] shadow-xl">
              <li v-for="c in store.cats.business" :key="c.id"><RouterLink :to="{ path: '/discover', query: { category: c.id } }" class="block rounded-lg px-3 py-2 hover:bg-[#7c3aed]/10" @click="closeMenu">{{ c.name }}</RouterLink></li>
              <li><RouterLink to="/discover" class="block rounded-lg px-3 py-2 font-semibold text-[#7c3aed] hover:bg-[#7c3aed]/10" @click="closeMenu">View all</RouterLink></li>
            </ul>
          </div>
        </div>

        <RouterLink to="/products" :class="link" active-class="!border-[#7c3aed]"><Icon name="bag" /> Products</RouterLink>
        <RouterLink v-if="store.user?.role === 'owner'" to="/owner" :class="link" active-class="!border-[#7c3aed]"><Icon name="briefcase" /> My Business</RouterLink>
        <RouterLink to="/about" :class="link" active-class="!border-[#7c3aed]"><Icon name="info" /> About</RouterLink>
        <a :href="'mailto:' + CONTACT_EMAIL" :class="link"><Icon name="mail" /> Contact</a>
        <RouterLink v-if="store.user" to="/account" :class="link"><Icon name="heart" /> Saved</RouterLink>
        <RouterLink v-if="store.user?.role === 'admin'" to="/admin" :class="link">Admin</RouterLink>
      </nav>
      <div class="ml-auto flex items-center gap-2 text-sm">
        <button class="flex max-w-[10rem] items-center gap-1.5 truncate rounded-full bg-white/15 px-3 py-1.5" @click="store.locOpen = true"><Icon name="pin" /> <span class="truncate">{{ store.loc ? store.loc.label : 'Set location' }}</span></button>
        <template v-if="store.user"><button class="px-2 text-white/80" @click="logout">Log out</button></template>
        <template v-else>
          <RouterLink to="/login" class="hidden rounded-lg border border-[#7c3aed] px-4 py-1.5 font-semibold sm:inline-block">Login</RouterLink>
          <RouterLink to="/register" class="rounded-lg bg-[#7c3aed] px-4 py-1.5 font-semibold text-white">Sign Up</RouterLink>
        </template>
      </div>
    </div>
  </header>
</template>