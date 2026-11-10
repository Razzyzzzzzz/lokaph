<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { store } from '@/store'
const router = useRouter()
const CONTACT_EMAIL = 'Test1@gmail.com' // palitan ng support email mo
const year = new Date().getFullYear()
const email = ref('')

const socials = [
  ['Facebook', 'https://facebook.com'],
  ['YouTube', 'https://youtube.com'],
  ['TikTok', 'https://tiktok.com'],
  ['Discord', 'https://discord.com']
]

// "For Business": guest -> register bilang owner, owner -> dashboard, admin -> admin
const bizTo = computed(() => {
  const r = store.user?.role
  return r === 'owner' ? '/owner' : r === 'admin' ? '/admin' : store.user ? '/about' : '/register?role=owner'
})

function subscribe() {
  if (!/^\S+@\S+\.\S+$/.test(email.value)) return store.flash('Enter a valid email address.', true)
  email.value = ''
  store.flash('Thanks for subscribing!')
}
const link = 'text-sm text-slate-200 hover:text-[#fbbf24]'
</script>
<template>
  <footer id="footer" class="bg-[#0b1f3a] text-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Brand -->
      <div>
        <RouterLink to="/" class="inline-flex flex-col gap-1">
          <img src="/assets/uploads/logo.png" alt="LokalPH" class="h-12 w-auto self-start object-contain">
          <span class="text-xs text-slate-300">Find Local. Shop Local.</span>
        </RouterLink>
        <p class="mt-4 max-w-xs text-sm text-slate-300">Discover and support Filipino small businesses near you.</p>
      </div>

      <!-- Quick links -->
      <div>
        <h3 class="mb-3 text-sm font-bold text-[#fbbf24]">Quick Links</h3>
        <ul class="space-y-2">
          <li><RouterLink to="/" :class="link">Home</RouterLink></li>
          <li><RouterLink to="/discover" :class="link">Explore</RouterLink></li>
          <li><RouterLink to="/products" :class="link">Products</RouterLink></li>
          <li><RouterLink to="/about" :class="link">About</RouterLink></li>
          <li><RouterLink :to="bizTo" :class="link">For Business</RouterLink></li>
        </ul>
      </div>

      <!-- Support -->
      <div>
        <h3 class="mb-3 text-sm font-bold text-[#fbbf24]">Support</h3>
        <ul class="space-y-2">
          <li><RouterLink to="/about" :class="link">Help Center</RouterLink></li>
          <li><a :href="'mailto:' + CONTACT_EMAIL" :class="link">Contact Us</a></li>
          <li><RouterLink to="/about" :class="link">Privacy Policy</RouterLink></li>
          <li><RouterLink to="/about" :class="link">Terms of Service</RouterLink></li>
        </ul>
      </div>

      <!-- Follow + newsletter -->
      <div>
        <h3 class="mb-3 text-sm font-bold text-[#fbbf24]">Follow Us</h3>
        <div class="flex flex-wrap gap-2">
          <a v-for="[n, u] in socials" :key="n" :href="u" target="_blank" rel="noopener" class="rounded-full border border-white/25 px-3 py-1 text-xs hover:border-[#fbbf24] hover:text-[#fbbf24]">{{ n }}</a>
        </div>
        <h3 class="mb-1 mt-5 text-sm font-bold text-[#fbbf24]">Subscribe to our Newsletter</h3>
        <p class="mb-2 text-sm text-slate-200">Get the latest updates and local deals!</p>
        <form class="flex gap-2" @submit.prevent="subscribe">
          <input v-model="email" type="email" placeholder="Enter your email address" aria-label="Email address" class="min-w-0 flex-1 rounded-lg !border-0 px-3 py-2 text-sm text-[#0b1f3a] focus:outline-none">
          <button class="shrink-0 rounded-lg bg-[#7c3aed] px-4 py-2 text-sm font-semibold hover:bg-[#6d28d9]">Subscribe</button>
        </form>
      </div>
    </div>

    <div class="border-t border-white/10 px-4 py-4 pb-20 text-center text-xs text-slate-300 lg:pb-4">© {{ year }} LokalPH. All rights reserved.</div>
  </footer>
</template>