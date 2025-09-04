import { createStore } from 'vuex'
import auth from './modules/auth'
import app from './modules/app'
import products from './modules/products'
import customers from './modules/customers'
import inventory from './modules/inventory'
import pos from './modules/pos'
import sales from './modules/sales'
import service from './modules/service'
import reports from './modules/reports'

export default createStore({
  modules: {
    auth,
    app,
    products,
    customers,
    inventory,
    pos,
    sales,
    service,
    reports
  },
  
  strict: process.env.NODE_ENV !== 'production'
})