<template>
  <div id="app">
    <!-- Loading screen -->
    <div v-if="isLoading" class="loading-screen">
      <div class="loading-content">
        <img src="@/assets/images/logo.png" alt="FaithBit" class="loading-logo" />
        <h4>{{ $t('common.loading') }}</h4>
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>

    <!-- Main app content -->
    <div v-else>
      <!-- Navigation -->
      <AppNavbar v-if="isAuthenticated" />
      
      <!-- Main content -->
      <main :class="{ 'with-navbar': isAuthenticated }">
        <router-view />
      </main>
      
      <!-- Footer -->
      <AppFooter v-if="isAuthenticated" />
    </div>

    <!-- Offline indicator -->
    <div v-if="!isOnline" class="offline-indicator">
      <i class="fas fa-wifi-slash"></i>
      {{ $t('common.offline_mode') }}
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'

export default {
  name: 'App',
  components: {
    AppNavbar,
    AppFooter
  },
  data() {
    return {
      isOnline: navigator.onLine,
      isLoading: true
    }
  },
  computed: {
    ...mapState('auth', {
      isAuthenticated: 'isAuthenticated'
    })
  },
  async created() {
    // Check authentication status
    await this.checkAuth()
    
    // Setup network status listeners
    window.addEventListener('online', this.updateOnlineStatus)
    window.addEventListener('offline', this.updateOnlineStatus)
    
    // Initialize app
    await this.initializeApp()
    
    this.isLoading = false
  },
  beforeUnmount() {
    window.removeEventListener('online', this.updateOnlineStatus)
    window.removeEventListener('offline', this.updateOnlineStatus)
  },
  methods: {
    ...mapActions('auth', ['checkAuth']),
    ...mapActions('app', ['initializeApp']),
    
    updateOnlineStatus() {
      this.isOnline = navigator.onLine
      
      if (this.isOnline) {
        this.$toast.success(this.$t('common.back_online'))
        // Sync offline data if any
        this.syncOfflineData()
      } else {
        this.$toast.warning(this.$t('common.went_offline'))
      }
    },
    
    async syncOfflineData() {
      // Sync any offline POS transactions or other data
      if (this.isAuthenticated) {
        // Implementation for syncing offline data
        console.log('Syncing offline data...')
      }
    }
  }
}
</script>

<style lang="scss">
#app {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  min-height: 100vh;
  background-color: var(--bs-body-bg);
}

.loading-screen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  text-align: center;
  color: white;
  
  .loading-logo {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
    border-radius: 50%;
    background: white;
    padding: 10px;
  }
  
  h4 {
    margin-bottom: 20px;
    font-weight: 300;
  }
}

main {
  min-height: calc(100vh - 60px);
  padding-top: 20px;
  
  &.with-navbar {
    margin-top: 80px;
    min-height: calc(100vh - 140px);
  }
}

.offline-indicator {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #dc3545;
  color: white;
  padding: 10px 15px;
  border-radius: 5px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  z-index: 1000;
  font-size: 0.9rem;
  
  i {
    margin-right: 8px;
  }
}

// Responsive design
@media (max-width: 768px) {
  main {
    padding: 10px;
    
    &.with-navbar {
      margin-top: 70px;
    }
  }
}
</style>