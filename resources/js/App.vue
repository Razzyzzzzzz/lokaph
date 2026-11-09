<script setup>
import { computed, watchEffect } from 'vue'
import { useRoute } from 'vue-router'
import { store } from '@/store'
import AppHeader from '@/components/AppHeader.vue'
import BottomNav from '@/components/BottomNav.vue'
import Icon from '@/components/Icon.vue'
import LocationModal from '@/components/LocationModal.vue'
import ProductModal from '@/components/ProductModal.vue'
import MessageModal from '@/components/MessageModal.vue'

const route = useRoute()
const bare = computed(() => !!route.meta.bare) // full-screen pages (login/register) hide the app chrome
const home = computed(() => route.path === '/') // home lays out its own full-width sections
watchEffect(() => { document.body.style.paddingBottom = bare.value ? '0' : '' })
const subscribe = (e) => { store.flash('Thanks for subscribing!'); e.target.reset() }
const socials = ['Facebook', 'YouTube', 'TikTok', 'Discord']
const year = new Date().getFullYear()
</script>
<template>
  <AppHeader v-if="!bare" />
  <div v-if="store.toast" class="fixed inset-x-4 z-50 mx-auto max-w-md rounded-xl px-4 py-3 text-sm text-white shadow-lg" :class="[bare ? 'top-4' : 'top-16', store.toast.bad ? 'bg-red-600' : 'bg-[#7c3aed]']">{{ store.toast.m }}</div>
  <RouterView v-if="bare || home" />
  <!-- Explore re-mounts when its query changes (category/search), so it re-reads the URL filters -->
  <main v-else class="mx-auto max-w-6xl space-y-6 px-4 py-5"><RouterView :key="route.path === '/discover' ? route.fullPath : route.path" /></main>

  <footer v-if="!bare" id="footer" class="bg-[#0b1f3a] text-sm text-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr_1.4fr]">
      <div>
        <div class="flex items-center gap-3">
          <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-[#7c3aed]"><Icon name="pin" :size="26" fill /></span>
          <span><b class="block text-2xl font-extrabold leading-none">Lokal<span class="text-[#fbbf24]">PH</span></b><span class="text-xs text-white/70">Find Local. Shop Local.</span></span>
        </div>
        <p class="mt-4 max-w-xs text-white/70">Discover and support Filipino small businesses near you.</p>
      </div>
      <div>
        <h4 class="mb-3 font-bold text-[#fbbf24]">Quick Links</h4>
        <ul class="space-y-2 text-white/75">
          <li><RouterLink to="/">Home</RouterLink></li><li><RouterLink to="/discover">Explore</RouterLink></li><li><RouterLink to="/products">Products</RouterLink></li><li><RouterLink to="/about">About</RouterLink></li><li><RouterLink to="/register?role=owner">For Business</RouterLink></li>
        </ul>
      </div>
      <div>
        <h4 class="mb-3 font-bold text-[#fbbf24]">Support</h4>
        <ul class="space-y-2 text-white/75"><li>Help Center</li><li>Contact Us</li><li>Privacy Policy</li><li>Terms of Service</li></ul>
      </div>
      <div>
        <h4 class="mb-3 font-bold text-[#fbbf24]">Follow Us</h4>
        <div class="flex flex-wrap gap-2"><span v-for="s in socials" :key="s" class="rounded-full border border-white/25 px-3 py-1 text-xs text-white/80">{{ s }}</span></div>
        <h4 class="mb-2 mt-6 font-bold text-[#fbbf24]">Subscribe to our Newsletter</h4>
        <p class="mb-2 text-white/75">Get the latest updates and local deals!</p>
        <form class="flex gap-2" @submit.prevent="subscribe"><input type="email" required placeholder="Enter your email address" class="text-[#0b1f3a]"><button class="rounded-xl bg-[#7c3aed] px-4 text-sm font-semibold text-white">Subscribe</button></form>
      </div>
    </div>
    <p class="border-t border-white/10 py-4 text-center text-xs text-white/70">&copy; {{ year }} LokalPH. All rights reserved.</p>
  </footer>

  <LocationModal v-if="store.locOpen" />
  <ProductModal v-if="store.modal?.t === 'product'" />
  <MessageModal v-if="store.modal && store.modal.t !== 'product'" />
  <BottomNav v-if="!bare" />
</template>