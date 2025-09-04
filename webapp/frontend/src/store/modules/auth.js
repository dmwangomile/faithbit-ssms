import api from '@/services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()

const state = {
  user: null,
  token: localStorage.getItem('access_token'),
  refreshToken: localStorage.getItem('refresh_token'),
  isAuthenticated: false,
  loginLoading: false
}

const getters = {
  isAuthenticated: state => !!state.token && !!state.user,
  user: state => state.user,
  token: state => state.token,
  userRole: state => state.user?.role,
  userBranch: state => state.user?.branch,
  userPermissions: state => state.user?.permissions || [],
  
  hasRole: state => role => {
    return state.user?.role === role
  },
  
  hasPermission: state => permission => {
    if (!state.user?.permissions) return false
    
    const permissions = state.user.permissions
    
    // Admin has all permissions
    if (permissions.includes('*')) return true
    
    // Check exact permission
    if (permissions.includes(permission)) return true
    
    // Check wildcard permissions
    return permissions.some(perm => {
      if (perm.endsWith('.*')) {
        const prefix = perm.slice(0, -2)
        return permission.startsWith(prefix + '.')
      }
      return false
    })
  },
  
  canAccessBranch: state => branchId => {
    if (!state.user) return false
    if (state.user.role === 'admin') return true
    return state.user.branch_id === branchId
  }
}

const mutations = {
  SET_AUTH_DATA(state, { user, tokens }) {
    state.user = user
    state.token = tokens.access_token
    state.refreshToken = tokens.refresh_token
    state.isAuthenticated = true
    
    // Store in localStorage
    localStorage.setItem('access_token', tokens.access_token)
    localStorage.setItem('refresh_token', tokens.refresh_token)
    localStorage.setItem('user', JSON.stringify(user))
    
    // Set API authorization header
    api.defaults.headers.common['Authorization'] = `Bearer ${tokens.access_token}`
  },
  
  SET_USER(state, user) {
    state.user = user
    state.isAuthenticated = !!user
    
    if (user) {
      localStorage.setItem('user', JSON.stringify(user))
    }
  },
  
  SET_TOKEN(state, token) {
    state.token = token
    
    if (token) {
      localStorage.setItem('access_token', token)
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    } else {
      localStorage.removeItem('access_token')
      delete api.defaults.headers.common['Authorization']
    }
  },
  
  SET_LOGIN_LOADING(state, loading) {
    state.loginLoading = loading
  },
  
  CLEAR_AUTH_DATA(state) {
    state.user = null
    state.token = null
    state.refreshToken = null
    state.isAuthenticated = false
    
    // Clear localStorage
    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
    localStorage.removeItem('user')
    
    // Clear API authorization header
    delete api.defaults.headers.common['Authorization']
  }
}

const actions = {
  async login({ commit }, credentials) {
    try {
      commit('SET_LOGIN_LOADING', true)
      
      const response = await api.post('/auth/login', credentials)
      
      if (response.data.success) {
        const { user, tokens } = response.data.data
        commit('SET_AUTH_DATA', { user, tokens })
        
        toast.success(response.data.message || 'Login successful')
        return { success: true }
      } else {
        throw new Error(response.data.message || 'Login failed')
      }
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Login failed'
      toast.error(message)
      return { success: false, message }
    } finally {
      commit('SET_LOGIN_LOADING', false)
    }
  },
  
  async logout({ commit }) {
    try {
      // Call logout API to invalidate token on server
      await api.post('/auth/logout')
    } catch (error) {
      console.error('Logout API call failed:', error)
    } finally {
      commit('CLEAR_AUTH_DATA')
      toast.info('Logged out successfully')
    }
  },
  
  async refreshToken({ commit, state }) {
    try {
      if (!state.refreshToken) {
        throw new Error('No refresh token available')
      }
      
      const response = await api.post('/auth/refresh', {
        refresh_token: state.refreshToken
      })
      
      if (response.data.success) {
        const { tokens } = response.data.data
        commit('SET_TOKEN', tokens.access_token)
        
        // Update refresh token
        state.refreshToken = tokens.refresh_token
        localStorage.setItem('refresh_token', tokens.refresh_token)
        
        return { success: true }
      } else {
        throw new Error(response.data.message || 'Token refresh failed')
      }
    } catch (error) {
      console.error('Token refresh failed:', error)
      commit('CLEAR_AUTH_DATA')
      return { success: false }
    }
  },
  
  async checkAuth({ commit, dispatch, state }) {
    // Check if we have tokens in localStorage
    const token = localStorage.getItem('access_token')
    const refreshToken = localStorage.getItem('refresh_token')
    const userData = localStorage.getItem('user')
    
    if (!token || !userData) {
      commit('CLEAR_AUTH_DATA')
      return false
    }
    
    try {
      // Parse user data
      const user = JSON.parse(userData)
      
      // Set initial auth state
      commit('SET_USER', user)
      commit('SET_TOKEN', token)
      
      // Verify token is still valid by making a test API call
      const response = await api.get('/auth/me')
      
      if (response.data.success) {
        // Update user data from server
        commit('SET_USER', response.data.data)
        return true
      } else {
        throw new Error('Token invalid')
      }
    } catch (error) {
      // If main token fails, try refresh token
      if (refreshToken) {
        const refreshResult = await dispatch('refreshToken')
        if (refreshResult.success) {
          return true
        }
      }
      
      // If refresh also fails, clear auth data
      commit('CLEAR_AUTH_DATA')
      return false
    }
  },
  
  updateUser({ commit }, userData) {
    commit('SET_USER', userData)
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}