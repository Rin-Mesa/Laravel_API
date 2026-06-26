<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
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
  AlertCircle,
  Menu
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

// Initialize application state
onMounted(async () => {
  await store.init();
});

// Determine layout type based on route path
const layoutType = computed(() => {
  if (route.path === '/login' || route.path === '/register') return 'login';
  if (route.path.startsWith('/admin')) return 'admin';
  if (route.path.startsWith('/profile') || route.path === '/wishlist' || route.path === '/orders') return 'customer-profile';
  return 'customer';
});

const currentUser = computed(() => store.user.value);
const cartCount = computed(() => store.cartCount.value);
const alert = computed(() => store.alert.value);
const sidebarOpen = ref(false);

const handleLogout = async () => {
  await store.logout();
  router.push('/login');
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const searchQuery = ref('');

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/products', query: { search: searchQuery.value } });
  }
};

const handleSearchKeyPress = (e: KeyboardEvent) => {
  if (e.key === 'Enter') {
    handleSearch();
  }
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
            <router-link to="/products" class="nav-item">Shop</router-link>
            <router-link to="/collection" class="nav-item">Collection</router-link>
            <router-link to="/deals" class="nav-item">Deals</router-link>
          </nav>
        </div>
        <div class="header-right">
          <div class="search-bar-wrapper">
            <Search :size="16" class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery"
              @keypress="handleSearchKeyPress"
              placeholder="Search products..." 
              class="header-search-input" 
            />
          </div>
          
          <!-- Auth buttons for non-logged in users -->
          <template v-if="!currentUser">
            <router-link to="/login" class="auth-btn auth-btn-secondary">
              Login
            </router-link>
            <router-link to="/register" class="auth-btn auth-btn-primary">
              Register
            </router-link>
          </template>
          
          <!-- User buttons for logged in users -->
          <template v-else>
            <router-link to="/wishlist" class="icon-btn-circle" title="Wishlist">
              <Heart :size="20" :class="{ 'filled-heart': store.wishlist.value.length > 0 }" />
            </router-link>
            <router-link to="/profile" class="icon-btn-circle" title="Profile">
              <UserIcon :size="20" />
            </router-link>
            <router-link to="/cart" class="cart-btn">
              <ShoppingCart :size="18" />
              <span>Cart</span>
              <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
            </router-link>
            <button class="logout-btn" @click="handleLogout" title="Logout">
              <LogOut :size="18" />
            </button>
          </template>
        </div>
      </div>
    </header>

    <!-- 2. SPLIT LAYOUT FOR CUSTOMER PROFILE & ADMIN -->
    <div v-if="layoutType === 'customer-profile' || layoutType === 'admin'" class="split-view">
      <!-- Mobile Menu Toggle -->
      <button v-if="layoutType === 'admin'" class="mobile-menu-toggle" @click="toggleSidebar">
        <Menu :size="24" />
      </button>

      <!-- SIDEBAR -->
      <aside :class="['sidebar', { open: sidebarOpen }]">
        <!-- Sidebar Brand header -->
        <div class="sidebar-brand">
          <div class="brand-logo">
            <Sliders :size="18" />
          </div>
          <div class="brand-info">
            <div class="brand-text">{{ layoutType === 'admin' ? 'Admin Panel' : 'My Account' }}</div>
            <div class="brand-subtitle">Precision Retail System</div>
          </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
          <!-- Admin Navigation -->
          <template v-if="layoutType === 'admin'">
            <router-link to="/admin/dashboard" class="sidebar-link" active-class="active">
              <LayoutDashboard :size="18" />
              <span>Dashboard</span>
            </router-link>
            <router-link to="/admin/products" class="sidebar-link" active-class="active">
              <Package :size="18" />
              <span>Products</span>
            </router-link>
            <router-link to="/admin/categories" class="sidebar-link" active-class="active">
              <Layers :size="18" />
              <span>Categories</span>
            </router-link>
            <router-link to="/admin/orders" class="sidebar-link" active-class="active">
              <ShoppingBag :size="18" />
              <span>Orders</span>
            </router-link>
            <router-link to="/admin/users" class="sidebar-link" active-class="active">
              <Users :size="18" />
              <span>Users</span>
            </router-link>
          </template>
          
          <!-- Customer Profile Navigation -->
          <template v-else>
            <div class="sidebar-section-title">Account</div>
            <router-link to="/profile" class="sidebar-link" active-class="active">
              <UserIcon :size="18" />
              <span>Profile</span>
            </router-link>
            <router-link to="/orders" class="sidebar-link" active-class="active">
              <ShoppingBag :size="18" />
              <span>Orders</span>
            </router-link>
            <router-link to="/wishlist" class="sidebar-link" active-class="active">
              <Heart :size="18" />
              <span>Wishlist</span>
            </router-link>
            <router-link to="/cart" class="sidebar-link" active-class="active">
              <ShoppingCart :size="18" />
              <span>Cart</span>
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
  </div>
</template>

<style>
/* CSS overrides or custom page structure inside App.vue */
.customer-header {
  height: 80px;
  background: rgba(5, 5, 5, 0.8);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border-color);
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
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  background: var(--accent-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.customer-logo .logo-bold {
  color: #ffffff;
}

.customer-logo .logo-light {
  color: #3b82f6;
}

.customer-nav {
  display: flex;
  gap: 32px;
}

.nav-item {
  color: var(--text-secondary);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
  position: relative;
}

.nav-item:hover {
  color: var(--text-primary);
}

.nav-item::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--accent-gradient);
  transition: width var(--transition-fast);
}

.nav-item:hover::after {
  width: 100%;
}

.nav-item.router-link-active {
  color: var(--text-primary);
  font-weight: 600;
}

.nav-item.router-link-active::after {
  width: 100%;
}

.search-bar-wrapper {
  position: relative;
  width: 280px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-tertiary);
  transition: color var(--transition-fast);
}

.header-search-input {
  width: 100%;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 50px;
  padding: 10px 16px 10px 40px;
  color: var(--text-primary);
  font-size: 0.85rem;
  transition: all var(--transition-fast);
}

.header-search-input:focus {
  outline: none;
  border-color: var(--accent-primary);
  background: var(--bg-secondary);
  box-shadow: var(--glow-primary);
}

.header-search-input:focus + .search-icon {
  color: var(--accent-primary);
}

.icon-btn-circle {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
}

.icon-btn-circle:hover {
  color: var(--text-primary);
  border-color: var(--border-hover);
  background: var(--bg-secondary);
  transform: translateY(-2px);
}

.filled-heart {
  color: #ef4444;
  fill: #ef4444;
}

.cart-btn {
  background: var(--accent-gradient);
  color: #ffffff;
  border: none;
  border-radius: 50px;
  padding: 10px 24px;
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
  position: relative;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.cart-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.cart-badge {
  background: #ffffff;
  color: var(--accent-primary);
  font-size: 0.7rem;
  font-weight: 800;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-btn {
  padding: 10px 24px;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  transition: all var(--transition-fast);
}

.auth-btn-secondary {
  background: transparent;
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
}

.auth-btn-secondary:hover {
  color: var(--text-primary);
  border-color: var(--border-hover);
  background: var(--bg-tertiary);
}

.auth-btn-primary {
  background: var(--accent-gradient);
  color: #ffffff;
  border: none;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.auth-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.logout-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.logout-btn:hover {
  color: #ef4444;
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
  transform: translateY(-2px);
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

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  position: fixed;
  top: 16px;
  left: 16px;
  z-index: 101;
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  padding: 8px;
  cursor: pointer;
  color: var(--text-primary);
  transition: all var(--transition-fast);
}

.mobile-menu-toggle:hover {
  background-color: var(--bg-tertiary);
}

@media (max-width: 1024px) {
  .mobile-menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
</style>
