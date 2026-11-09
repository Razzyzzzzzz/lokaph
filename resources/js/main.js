import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { store } from './store'
import '../css/app.css'

store.init().finally(() => createApp(App).use(router).mount('#app'))
