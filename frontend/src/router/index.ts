import { createRouter, createWebHistory } from 'vue-router';

import Home from '../views/Customer/Home.vue';
import Cart from '../views/Customer/Cart.vue';
import Wishlist from '../views/Customer/Wishlist.vue';
import ProductDetail from '../views/Customer/ProductDetail.vue';
import Login from '../views/Auth/Login.vue';
import Register from '../views/Auth/Register.vue';
import Dashboard from '../views/Admin/Dashboard.vue';
import AdminProducts from '../views/Admin/Products.vue';

const routes = [
  { path: '/', component: Home, name: 'Home' },
  { path: '/cart', component: Cart, name: 'Cart' },
  { path: '/wishlist', component: Wishlist, name: 'Wishlist' },
  { path: '/products/:id', component: ProductDetail, name: 'product-detail' },
  { path: '/login', component: Login, name: 'Login' },
  { path: '/register', component: Register, name: 'Register' },
  { path: '/admin/dashboard', component: Dashboard, name: 'Dashboard' },
  { path: '/admin/products', component: AdminProducts, name: 'AdminProducts' },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
