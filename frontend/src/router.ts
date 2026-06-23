import { createRouter, createWebHistory } from 'vue-router';
import Home from './views/Customer/Home.vue';
import Cart from './views/Customer/Cart.vue';
import Wishlist from './views/Customer/Wishlist.vue';
import Dashboard from './views/Admin/Dashboard.vue';
import Products from './views/Admin/Products.vue';
import Login from './views/Auth/Login.vue';

const routes = [
  { path: '/', component: Home, name: 'Home' },
  { path: '/cart', component: Cart, name: 'Cart' },
  { path: '/profile/wishlist', component: Wishlist, name: 'Wishlist' },
  { path: '/admin/dashboard', component: Dashboard, name: 'Dashboard' },
  { path: '/admin/products', component: Products, name: 'Products' },
  { path: '/login', component: Login, name: 'Login' }
];

export const router = createRouter({
  history: createWebHistory(),
  routes
});
