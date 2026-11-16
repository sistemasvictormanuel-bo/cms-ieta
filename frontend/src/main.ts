import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'

import App from './App.vue'
import router from './router'
import es from './locales/es.json'
import en from './locales/en.json'
import apiClient from './services/api'

const savedLocale = (typeof localStorage !== 'undefined' && localStorage.getItem('ieta-locale')) || 'es'

const i18n = createI18n({
  legacy: false,
  locale: savedLocale === 'en' ? 'en' : 'es',
  fallbackLocale: 'es',
  messages: { es, en },
})

apiClient.defaults.headers.common['Accept-Language'] = i18n.global.locale.value

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)

app.mount('#app')
