import { createRouter, createWebHistory } from 'vue-router'
import store from '@/store'

// Layout components
const DefaultLayout = () => import('@/components/layout/DefaultLayout.vue')
const AuthLayout = () => import('@/components/layout/AuthLayout.vue')

// Auth views
const Login = () => import('@/views/auth/Login.vue')

// Main views
const Dashboard = () => import('@/views/Dashboard.vue')

// Product management
const ProductList = () => import('@/views/products/ProductList.vue')
const ProductForm = () => import('@/views/products/ProductForm.vue')
const ProductView = () => import('@/views/products/ProductView.vue')

// Customer management
const CustomerList = () => import('@/views/customers/CustomerList.vue')
const CustomerForm = () => import('@/views/customers/CustomerForm.vue')
const CustomerView = () => import('@/views/customers/CustomerView.vue')

// Sales
const POSSystem = () => import('@/views/pos/POSSystem.vue')
const SalesOrders = () => import('@/views/sales/SalesOrders.vue')
const QuotesList = () => import('@/views/sales/QuotesList.vue')
const InvoicesList = () => import('@/views/sales/InvoicesList.vue')

// Inventory
const InventoryOverview = () => import('@/views/inventory/InventoryOverview.vue')
const StockMovements = () => import('@/views/inventory/StockMovements.vue')
const StockAdjustments = () => import('@/views/inventory/StockAdjustments.vue')

// Service Management
const ServiceOrders = () => import('@/views/service/ServiceOrders.vue')
const TechnicianSchedule = () => import('@/views/service/TechnicianSchedule.vue')
const WorkOrderForm = () => import('@/views/service/WorkOrderForm.vue')

// Reports
const ReportsDashboard = () => import('@/views/reports/ReportsDashboard.vue')
const SalesReport = () => import('@/views/reports/SalesReport.vue')
const InventoryReport = () => import('@/views/reports/InventoryReport.vue')

// Settings
const Settings = () => import('@/views/settings/Settings.vue')
const UserManagement = () => import('@/views/settings/UserManagement.vue')
const BranchManagement = () => import('@/views/settings/BranchManagement.vue')

// Error pages
const NotFound = () => import('@/views/errors/NotFound.vue')
const Forbidden = () => import('@/views/errors/Forbidden.vue')

const routes = [
  // Authentication routes
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'Login',
        component: Login,
        meta: { 
          requiresGuest: true,
          title: 'Login'
        }
      }
    ]
  },
  
  // Main application routes
  {
    path: '/',
    component: DefaultLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard,
        meta: { 
          title: 'Dashboard',
          permission: 'dashboard.view'
        }
      },
      
      // Product Management
      {
        path: '/products',
        name: 'Products',
        component: ProductList,
        meta: { 
          title: 'Products',
          permission: 'product.view'
        }
      },
      {
        path: '/products/create',
        name: 'CreateProduct',
        component: ProductForm,
        meta: { 
          title: 'Create Product',
          permission: 'product.create'
        }
      },
      {
        path: '/products/:id/edit',
        name: 'EditProduct',
        component: ProductForm,
        meta: { 
          title: 'Edit Product',
          permission: 'product.update'
        }
      },
      {
        path: '/products/:id',
        name: 'ViewProduct',
        component: ProductView,
        meta: { 
          title: 'Product Details',
          permission: 'product.view'
        }
      },
      
      // Customer Management
      {
        path: '/customers',
        name: 'Customers',
        component: CustomerList,
        meta: { 
          title: 'Customers',
          permission: 'customer.view'
        }
      },
      {
        path: '/customers/create',
        name: 'CreateCustomer',
        component: CustomerForm,
        meta: { 
          title: 'Create Customer',
          permission: 'customer.create'
        }
      },
      {
        path: '/customers/:id/edit',
        name: 'EditCustomer',
        component: CustomerForm,
        meta: { 
          title: 'Edit Customer',
          permission: 'customer.update'
        }
      },
      {
        path: '/customers/:id',
        name: 'ViewCustomer',
        component: CustomerView,
        meta: { 
          title: 'Customer Details',
          permission: 'customer.view'
        }
      },
      
      // Point of Sale
      {
        path: '/pos',
        name: 'POS',
        component: POSSystem,
        meta: { 
          title: 'Point of Sale',
          permission: 'pos.access'
        }
      },
      
      // Sales Management
      {
        path: '/sales/orders',
        name: 'SalesOrders',
        component: SalesOrders,
        meta: { 
          title: 'Sales Orders',
          permission: 'sales.view'
        }
      },
      {
        path: '/sales/quotes',
        name: 'Quotes',
        component: QuotesList,
        meta: { 
          title: 'Quotes',
          permission: 'quote.view'
        }
      },
      {
        path: '/sales/invoices',
        name: 'Invoices',
        component: InvoicesList,
        meta: { 
          title: 'Invoices',
          permission: 'invoice.view'
        }
      },
      
      // Inventory Management
      {
        path: '/inventory',
        name: 'Inventory',
        component: InventoryOverview,
        meta: { 
          title: 'Inventory Overview',
          permission: 'inventory.view'
        }
      },
      {
        path: '/inventory/movements',
        name: 'StockMovements',
        component: StockMovements,
        meta: { 
          title: 'Stock Movements',
          permission: 'inventory.view'
        }
      },
      {
        path: '/inventory/adjustments',
        name: 'StockAdjustments',
        component: StockAdjustments,
        meta: { 
          title: 'Stock Adjustments',
          permission: 'inventory.adjust'
        }
      },
      
      // Service Management
      {
        path: '/service/orders',
        name: 'ServiceOrders',
        component: ServiceOrders,
        meta: { 
          title: 'Service Orders',
          permission: 'service.view'
        }
      },
      {
        path: '/service/schedule',
        name: 'TechnicianSchedule',
        component: TechnicianSchedule,
        meta: { 
          title: 'Technician Schedule',
          permission: 'service.schedule'
        }
      },
      {
        path: '/service/work-order/create',
        name: 'CreateWorkOrder',
        component: WorkOrderForm,
        meta: { 
          title: 'Create Work Order',
          permission: 'service.create'
        }
      },
      
      // Reports
      {
        path: '/reports',
        name: 'Reports',
        component: ReportsDashboard,
        meta: { 
          title: 'Reports Dashboard',
          permission: 'report.view'
        }
      },
      {
        path: '/reports/sales',
        name: 'SalesReport',
        component: SalesReport,
        meta: { 
          title: 'Sales Reports',
          permission: 'report.sales'
        }
      },
      {
        path: '/reports/inventory',
        name: 'InventoryReport',
        component: InventoryReport,
        meta: { 
          title: 'Inventory Reports',
          permission: 'report.inventory'
        }
      },
      
      // Settings
      {
        path: '/settings',
        name: 'Settings',
        component: Settings,
        meta: { 
          title: 'System Settings',
          permission: 'admin.settings'
        }
      },
      {
        path: '/settings/users',
        name: 'UserManagement',
        component: UserManagement,
        meta: { 
          title: 'User Management',
          permission: 'admin.users'
        }
      },
      {
        path: '/settings/branches',
        name: 'BranchManagement',
        component: BranchManagement,
        meta: { 
          title: 'Branch Management',
          permission: 'admin.branches'
        }
      }
    ]
  },
  
  // Error routes
  {
    path: '/403',
    name: 'Forbidden',
    component: Forbidden,
    meta: { title: 'Access Forbidden' }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: { title: 'Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  // Set page title
  if (to.meta.title) {
    document.title = `${to.meta.title} - FaithBit SSMS`
  }
  
  // Check authentication
  const isAuthenticated = store.getters['auth/isAuthenticated']
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  
  // If route requires authentication but user is not authenticated
  if (requiresAuth && !isAuthenticated) {
    return next('/auth/login')
  }
  
  // If route requires guest (login page) but user is authenticated
  if (requiresGuest && isAuthenticated) {
    return next('/')
  }
  
  // Check permissions
  if (to.meta.permission && isAuthenticated) {
    const user = store.getters['auth/user']
    const hasPermission = store.getters['auth/hasPermission'](to.meta.permission)
    
    if (!hasPermission) {
      return next('/403')
    }
  }
  
  next()
})

export default router