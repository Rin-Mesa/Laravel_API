<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { store } from './store';
import { 
  LayoutDashboard, 
  Package, 
  Layers, 
  ShoppingBag, 
  Users, 
  Heart, 
  Settings, 
  LogOut,
  Search,
  User as UserIcon,
  ShoppingCart,
  Bell,
  Sliders,
  CheckCircle,
  AlertCircle
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

// Initialize application state
onMounted(async () => {
  await store.init();
  // Auto login customer as default if not logged in
  if (!store.user.value) {
    await store.switchUser('customer');
  }
});

// Determine layout type based on route path
const layoutType = computed(() => {
  if (route.path === '/login') return 'login';
  if (route.path.startsWith('/admin')) return 'admin';
  if (route.path.startsWith('/profile')) return 'customer-profile';
  return 'customer';
});

const currentUser = computed(() => store.user.value);
const cartCount = computed(() => store.cartCount.value);
const alert = computed(() => store.alert.value);

const handleLogout = async () => {
  await store.logout();
  router.push('/login');
};

const handleSwitchRole = async (role: 'admin' | 'customer') => {
  const success = await store.switchUser(role);
  if (success) {
    if (role === 'admin') {
      router.push('/admin/dashboard');
    } else {
      router.push('/');
    }
  }
};
</script>

<template>
  <div :class="['app-container', layoutType === 'customer' || layoutType === 'customer-profile' ? 'theme-dark' : 'theme-light']">
    <!-- Toast Alert Notifications -->
    <div v-if="alert" :class="['alert-toast', `alert-toast-${alert.type}`]">
      <CheckCircle v-if="alert.type === 'success'" :size="20" />
      <AlertCircle v-else-if="alert.type === 'error'" :size="20" />
      <Bell v-else :size="20" />
      <span>{{ alert.message }}</span>
    </div>

    <!-- 1. CUSTOMER LAYOUT TOP NAVBAR -->
    <header v-if="layoutType === 'customer' || layoutType === 'customer-profile'" class="customer-header">
      <div class="header-container">
        <div class="header-left">
          <router-link to="/" class="customer-logo">
            <span class="logo-bold">Precision</span>
            <span class="logo-light">Retail</span>
          </router-link>
          <nav class="customer-nav">
            <span class="nav-item">Categories</span>
            <span class="nav-item">Deals</span>
            <span class="nav-item">New Arrivals</span>
            <span class="nav-item">Support</span>
          </nav>
        </div>
        <div class="header-right">
          <div class="search-bar-wrapper">
            <Search :size="16" class="search-icon" />
            <input type="text" placeholder="Search products..." class="header-search-input" />
          </div>
          <router-link to="/profile/wishlist" class="icon-btn-circle" title="Wishlist">
            <Heart :size="20" :class="{ 'filled-heart': store.wishlist.value.length > 0 }" />
          </router-link>
          <router-link to="/profile/wishlist" class="icon-btn-circle" title="Profile">
            <UserIcon :size="20" />
          </router-link>
          <router-link to="/cart" class="cart-btn">
            <ShoppingCart :size="18" />
            <span>Cart</span>
            <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
          </router-link>
        </div>
      </div>
    </header>

    <!-- 2. SPLIT LAYOUT FOR CUSTOMER PROFILE & ADMIN -->
    <div v-if="layoutType === 'customer-profile' || layoutType === 'admin'" class="split-view">
      <!-- SIDEBAR -->
      <aside class="sidebar">
        <!-- Sidebar Brand header (Only in Admin mode) -->
        <div class="sidebar-brand">
          <div class="brand-logo">
            <Sliders :size="18" />
          </div>
          <div class="brand-info">
            <div class="brand-text">Admin Panel</div>
            <div class="brand-subtitle">Precision Retail System</div>
          </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
          <router-link to="/admin/dashboard" class="sidebar-link" active-class="active">
            <LayoutDashboard :size="18" />
            <span>Dashboard</span>
          </router-link>
          <router-link to="/admin/products" class="sidebar-link" active-class="active">
            <Package :size="18" />
            <span>Products</span>
          </router-link>
          <div class="sidebar-link">
            <Layers :size="18" />
            <span>Categories</span>
          </div>
          <div class="sidebar-link">
            <ShoppingBag :size="18" />
            <span>Orders</span>
          </div>
          <div class="sidebar-link">
            <Users :size="18" />
            <span>Users</span>
          </div>
          
          <!-- Show wishlist if in profile dashboard mode -->
          <template v-if="layoutType === 'customer-profile'">
            <div class="sidebar-section-title">My Account</div>
            <router-link to="/profile/wishlist" class="sidebar-link" active-class="active">
              <Heart :size="18" />
              <span>Wishlist</span>
            </router-link>
          </template>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
          <div v-if="currentUser" class="user-profile-summary">
            <img 
              :src="currentUser.role === 'admin' ? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=256&auto=format&fit=crop' : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=256&auto=format&fit=crop'" 
              :alt="currentUser.name" 
              class="avatar"
            />
            <div class="user-info">
              <div class="user-name">{{ currentUser.name }}</div>
              <div class="user-email">{{ currentUser.email }}</div>
            </div>
          </div>
          <div class="sidebar-link">
            <Settings :size="18" />
            <span>Settings</span>
          </div>
          <div class="sidebar-link text-danger" @click="handleLogout">
            <LogOut :size="18" />
            <span>Logout</span>
          </div>
        </div>
      </aside>

      <!-- MAIN CONTENT PANEL -->
      <main :class="['main-content', layoutType === 'admin' ? 'admin-main-offset' : 'profile-main-offset']">
        <router-view />
      </main>
    </div>

    <!-- 3. STANDARD FULL-WIDTH VIEWS (Customer Home/Cart & Login) -->
    <div v-else class="full-view">
      <router-view />
    </div>

    <!-- FLOATING ROLE SWITCHER FOR DEMONSTRATION -->
    <div class="demo-switcher">
      <span class="demo-switcher-label">View Mode:</span>
      <button 
        :class="['demo-switcher-btn', { active: currentUser?.role === 'customer' }]" 
        @click="handleSwitchRole('customer')"
      >
        Customer
      </button>
      <button 
        :class="['demo-switcher-btn', { active: currentUser?.role === 'admin' }]" 
        @click="handleSwitchRole('admin')"
      >
        Admin
      </button>
    </div>
  </div>
</template>

<style>
/* CSS overrides or custom page structure inside App.vue */
.customer-header {
  height: 80px;
  background-color: #0b0f19;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-container {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left, .header-right {
  display: flex;
  align-items: center;
  gap: 32px;
}

.customer-logo {
  text-decoration: none;
  font-family: var(--font-display);
  font-size: 1.4rem;
  font-weight: 800;
  letter-spacing: -0.03em;
}

.customer-logo .logo-bold {
  color: #ffffff;
}

.customer-logo .logo-light {
  color: #3b82f6;
}

.customer-nav {
  display: flex;
  gap: 24px;
}

.nav-item {
  color: #9ca3af;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: color var(--transition-fast);
}

.nav-item:hover {
  color: #ffffff;
}

.search-bar-wrapper {
  position: relative;
  width: 240px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #4b5563;
}

.header-search-input {
  width: 100%;
  background-color: #111827;
  border: 1px solid #1f2937;
  border-radius: 50px;
  padding: 8px 16px 8px 36px;
  color: #f9fafb;
  font-size: 0.85rem;
  transition: all var(--transition-fast);
}

.header-search-input:focus {
  outline: none;
  border-color: #3b82f6;
  width: 280px;
}

.icon-btn-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  background-color: #111827;
  border: 1px solid #1f2937;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
}

.icon-btn-circle:hover {
  color: #ffffff;
  border-color: #3b82f6;
}

.filled-heart {
  color: #ef4444;
  fill: #ef4444;
}

.cart-btn {
  background-color: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 50px;
  padding: 8px 20px;
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
  position: relative;
}

.cart-btn:hover {
  background-color: #1d4ed8;
}

.cart-badge {
  background-color: #ffffff;
  color: #2563eb;
  font-size: 0.75rem;
  font-weight: 800;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Split View Structure */
.split-view {
  display: flex;
  flex: 1;
}

.admin-main-offset {
  margin-left: 260px;
  flex: 1;
  padding: 32px 40px;
  background-color: #f8fafc;
  min-height: calc(100vh);
}

.profile-main-offset {
  margin-left: 260px;
  flex: 1;
  padding: 32px 40px;
  background-color: #0a0e17;
  min-height: calc(100vh - 80px);
}

.full-view {
  flex: 1;
  display: flex;
  flex-direction: column;
}
</style>
