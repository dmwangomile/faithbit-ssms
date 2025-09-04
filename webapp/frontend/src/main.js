import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import i18n from './locales'

// Bootstrap CSS and JS
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// FontAwesome
import '@fortawesome/fontawesome-free/css/all.min.css'

// Custom styles
import './assets/scss/main.scss'

// Plugins
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

// Create Vue app
const app = createApp(App)

// Configure toast notifications
const toastOptions = {
  position: 'top-right',
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false
}

// Register plugins
app.use(store)
app.use(router)
app.use(i18n)
app.use(Toast, toastOptions)

// Global properties
app.config.globalProperties.$appName = 'FaithBit SSMS'
app.config.globalProperties.$version = '1.0.0'

// Error handling
app.config.errorHandler = (error, instance, info) => {
  console.error('Global error:', error)
  console.error('Component:', instance)
  console.error('Info:', info)
}

// Mount the app
app.mount('#app')