<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import { 
  ShoppingCart, 
  Heart, 
  Star, 
  Search, 
  Grid,
  List,
  Tag,
  Clock,
  Percent
} from 'lucide-vue-next';

const loading = ref(true);
const products = ref<any[]>([]);
const categories = ref<any[]>([]);
const searchQuery = ref('');
const selectedCategory = ref('all');
const sortBy = ref('discount');
const viewMode = ref<'grid' | 'list'>('grid');

const fetchProducts = async () => {
  loading.value = true;
  try {
    const res = await api.getProducts();
    if (res.success) {
      products.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }
  } catch (e) {
    console.error('Failed to load products', e);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const res = await api.getCategories();
    if (res.success) {
      categories.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }
  } catch (e) {
    console.error('Failed to load categories', e);
  }
};

onMounted(async () => {
  await Promise.all([fetchProducts(), fetchCategories()]);
});

const calculateDiscount = (price: number) => {
  // Simulate discount calculation (in real app, this would come from API)
  const discountPercent = Math.floor(Math.random() * 30) + 10;
  return {
    originalPrice: price * (1 + discountPercent / 100),
    discountPercent
  };
};

const filteredProducts = computed(() => {
  let filtered = [...products.value];

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(p => 
      p.name?.toLowerCase().includes(query) ||
      p.description?.toLowerCase().includes(query) ||
      p.sku?.toLowerCase().includes(query)
    );
  }

  // Category filter
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(p => p.category_id === Number(selectedCategory.value));
  }

  // Sort
  if (sortBy.value === 'discount') {
    filtered.sort((a, b) => calculateDiscount(b.price).discountPercent - calculateDiscount(a.price).discountPercent);
  } else if (sortBy.value === 'price-low') {
    filtered.sort((a, b) => a.price - b.price);
  } else if (sortBy.value === 'price-high') {
    filtered.sort((a, b) => b.price - a.price);
  } else if (sortBy.value === 'name') {
    filtered.sort((a, b) => a.name.localeCompare(b.name));
  }

  return filtered;
});

const handleAddToCart = async (productId: number) => {
  await store.addToCart(productId, 1);
};

const handleToggleWishlist = async (productId: number) => {
  await store.toggleWishlist(productId);
};

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(val);
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = 'all';
  sortBy.value = 'discount';
};

const timeLeft = ref({
  days: 2,
  hours: 14,
  minutes: 32,
  seconds: 45
});

// Countdown timer
setInterval(() => {
  if (timeLeft.value.seconds > 0) {
    timeLeft.value.seconds--;
  } else if (timeLeft.value.minutes > 0) {
    timeLeft.value.minutes--;
    timeLeft.value.seconds = 59;
  } else if (timeLeft.value.hours > 0) {
    timeLeft.value.hours--;
    timeLeft.value.minutes = 59;
    timeLeft.value.seconds = 59;
  } else if (timeLeft.value.days > 0) {
    timeLeft.value.days--;
    timeLeft.value.hours = 23;
    timeLeft.value.minutes = 59;
    timeLeft.value.seconds = 59;
  }
}, 1000);
</script>

<template>
  <div class="deals-wrapper">
    <!-- Hero Section -->
    <div class="deals-hero">
      <div class="hero-content">
        <div class="hero-badge">
          <Tag :size="16" />
          <span>Limited Time Offers</span>
        </div>
        <h1>Hot Deals</h1>
        <p>Save up to 40% on selected premium electronics</p>
        
        <!-- Countdown Timer -->
        <div class="countdown">
          <div class="countdown-item">
            <span class="countdown-value">{{ String(timeLeft.days).padStart(2, '0') }}</span>
            <span class="countdown-label">Days</span>
          </div>
          <span class="countdown-separator">:</span>
          <div class="countdown-item">
            <span class="countdown-value">{{ String(timeLeft.hours).padStart(2, '0') }}</span>
            <span class="countdown-label">Hours</span>
          </div>
          <span class="countdown-separator">:</span>
          <div class="countdown-item">
            <span class="countdown-value">{{ String(timeLeft.minutes).padStart(2, '0') }}</span>
            <span class="countdown-label">Mins</span>
          </div>
          <span class="countdown-separator">:</span>
          <div class="countdown-item">
            <span class="countdown-value">{{ String(timeLeft.seconds).padStart(2, '0') }}</span>
            <span class="countdown-label">Secs</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="deals-header">
      <div>
        <h2>Flash Sale</h2>
        <p>{{ filteredProducts.length }} products on sale</p>
      </div>
      <div class="header-actions">
        <button 
          :class="['view-toggle', { active: viewMode === 'grid' }]" 
          @click="viewMode = 'grid'"
        >
          <Grid :size="18" />
        </button>
        <button 
          :class="['view-toggle', { active: viewMode === 'list' }]" 
          @click="viewMode = 'list'"
        >
          <List :size="18" />
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
      <div class="search-box">
        <Search :size="18" class="search-icon" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search deals..." 
          class="search-input"
        />
      </div>

      <div class="filter-group">
        <label class="filter-label">Category</label>
        <select v-model="selectedCategory" class="filter-select">
          <option value="all">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>

      <div class="filter-group">
        <label class="filter-label">Sort By</label>
        <select v-model="sortBy" class="filter-select">
          <option value="discount">Best Discount</option>
          <option value="price-low">Price: Low to High</option>
          <option value="price-high">Price: High to Low</option>
          <option value="name">Name: A-Z</option>
        </select>
      </div>

      <button class="clear-filters-btn" @click="clearFilters">
        Clear Filters
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="loading-state">
      <div class="loader"></div>
      <p>Loading deals...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="filteredProducts.length === 0" class="empty-state">
      <Tag :size="48" class="empty-icon" />
      <h3>No deals found</h3>
      <p>Check back later for new offers</p>
      <button class="btn btn-secondary" @click="clearFilters">
        Clear All Filters
      </button>
    </div>

    <!-- Products Grid -->
    <div v-else :class="['products-container', viewMode]">
      <div 
        v-for="product in filteredProducts" 
        :key="product.id" 
        :class="['product-card', viewMode]"
      >
        <!-- Discount Badge -->
        <div class="discount-badge">
          <Percent :size="12" />
          <span>{{ calculateDiscount(product.price).discountPercent }}% OFF</span>
        </div>

        <!-- Card Header -->
        <div class="product-card-top">
          <button 
            class="wishlist-toggle-btn" 
            @click="handleToggleWishlist(product.id)"
            :title="store.isWishlisted(product.id) ? 'Remove from Wishlist' : 'Add to Wishlist'"
          >
            <Heart 
              :size="18" 
              :class="{ 'wishlist-active': store.isWishlisted(product.id) }" 
            />
          </button>
          <img 
            :src="product.image_url" 
            :alt="product.name" 
            class="product-image"
            @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop'"
          />
        </div>

        <!-- Card Body -->
        <div class="product-card-body">
          <span class="product-category">{{ product.category?.name }}</span>
          <h3 class="product-name">{{ product.name }}</h3>
          
          <div class="product-rating">
            <div class="stars">
              <Star v-for="i in 5" :key="i" :size="12" class="filled-star" />
            </div>
            <span class="rating-count">({{ 50 + product.id * 5 }})</span>
          </div>

          <p v-if="viewMode === 'list'" class="product-description">
            {{ product.description || 'High-quality product with premium features.' }}
          </p>

          <div class="product-footer">
            <div class="price-section">
              <span class="original-price">{{ formatCurrency(calculateDiscount(product.price).originalPrice) }}</span>
              <span class="product-price">{{ formatCurrency(product.price) }}</span>
            </div>
            <button class="add-to-cart-btn" @click="handleAddToCart(product.id)">
              <ShoppingCart :size="14" />
              {{ viewMode === 'list' ? 'Add to Cart' : '' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.deals-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px 40px;
  font-family: var(--font-body);
  background-color: var(--bg-primary);
  color: var(--text-primary);
  min-height: 80vh;
}

/* Hero Section */
.deals-hero {
  background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 50%, #3b82f6 100%);
  background-size: 200% 200%;
  animation: gradientShift 8s ease infinite;
  padding: 80px 40px;
  margin-bottom: 40px;
  border-radius: 0 0 var(--radius-xl) var(--radius-xl);
  border: 1px solid var(--border-color);
  border-top: none;
  position: relative;
  overflow: hidden;
}

.deals-hero::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 500px;
  height: 500px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

.hero-content {
  max-width: 800px;
  position: relative;
  z-index: 1;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  color: white;
  padding: 10px 20px;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 20px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.deals-hero h1 {
  font-family: var(--font-display);
  font-size: 3.5rem;
  font-weight: 800;
  color: white;
  margin-bottom: 12px;
  line-height: 1.1;
  text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
}

.deals-hero p {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 32px;
}

/* Countdown Timer */
.countdown {
  display: flex;
  align-items: center;
  gap: 12px;
}

.countdown-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  padding: 16px 20px;
  border-radius: var(--radius-md);
  min-width: 80px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.countdown-value {
  font-family: var(--font-display);
  font-size: 2rem;
  font-weight: 800;
  color: white;
  line-height: 1;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.countdown-label {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-top: 6px;
}

.countdown-separator {
  font-size: 2rem;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.5);
}

/* Header */
.deals-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
}

.deals-header h2 {
  font-family: var(--font-display);
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 4px;
  background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.deals-header p {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.header-actions {
  display: flex;
  gap: 8px;
}

.view-toggle {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-color);
  background: var(--bg-tertiary);
  color: var(--text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-fast);
}

.view-toggle:hover {
  color: var(--text-primary);
  border-color: var(--border-hover);
  transform: translateY(-2px);
}

.view-toggle.active {
  background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
  color: white;
  border-color: transparent;
  box-shadow: 0 0 20px rgba(124, 58, 237, 0.4);
}

/* Filters Bar */
.filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 24px;
  margin-bottom: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 200px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-tertiary);
  transition: color var(--transition-fast);
}

.search-input {
  width: 100%;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  padding: 12px 12px 12px 40px;
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
  transition: all var(--transition-fast);
}

.search-input:focus {
  outline: none;
  border-color: #7c3aed;
  background: var(--bg-secondary);
  box-shadow: 0 0 20px rgba(124, 58, 237, 0.3);
}

.search-input:focus + .search-icon {
  color: #7c3aed;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.filter-select {
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.filter-select:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 20px rgba(124, 58, 237, 0.3);
}

.clear-filters-btn {
  background: transparent;
  border: none;
  color: #7c3aed;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  padding: 10px 18px;
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.clear-filters-btn:hover {
  background: rgba(124, 58, 237, 0.1);
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  gap: 16px;
  color: var(--text-tertiary);
}

.loader {
  width: 48px;
  height: 48px;
  border: 4px solid var(--bg-tertiary);
  border-top-color: #7c3aed;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  color: var(--border-color);
}

.empty-state h3 {
  font-family: var(--font-display);
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--text-primary);
}

/* Products Container */
.products-container {
  display: grid;
  gap: 24px;
}

.products-container.grid {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}

.products-container.list {
  grid-template-columns: 1fr;
}

/* Product Card */
.product-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: all var(--transition-normal);
  position: relative;
}

.product-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
  opacity: 0;
  transition: opacity var(--transition-normal);
  z-index: 0;
}

.product-card:hover {
  transform: translateY(-8px);
  border-color: #7c3aed;
  box-shadow: 0 20px 40px rgba(124, 58, 237, 0.3);
}

.product-card.list {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 0;
}

@media (max-width: 768px) {
  .product-card.list {
    grid-template-columns: 1fr;
  }
}

.discount-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 800;
  z-index: 10;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.product-card-top {
  position: relative;
  background: var(--bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  overflow: hidden;
  z-index: 1;
}

.product-card.list .product-card-top {
  height: 100%;
}

.product-image {
  max-width: 85%;
  max-height: 85%;
  object-fit: contain;
  transition: transform var(--transition-normal);
  filter: brightness(0.9);
}

.product-card:hover .product-image {
  transform: scale(1.08);
  filter: brightness(1);
}

.wishlist-toggle-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--transition-fast);
  z-index: 10;
}

.wishlist-toggle-btn:hover {
  background: rgba(0, 0, 0, 0.8);
  color: #ef4444;
  transform: scale(1.1);
}

.wishlist-active {
  color: #ef4444;
  fill: #ef4444;
}

.product-card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  z-index: 1;
  position: relative;
  background: var(--bg-card);
}

.product-category {
  font-size: 0.75rem;
  font-weight: 600;
  color: #7c3aed;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.product-name {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.3;
  transition: color var(--transition-fast);
}

.product-card:hover .product-name {
  color: #7c3aed;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 6px;
}

.stars {
  display: flex;
  color: #fbbf24;
}

.filled-star {
  fill: #fbbf24;
}

.rating-count {
  font-size: 0.75rem;
  color: var(--text-tertiary);
}

.product-description {
  font-size: 0.85rem;
  color: var(--text-secondary);
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
}

.price-section {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.original-price {
  font-size: 0.8rem;
  color: var(--text-tertiary);
  text-decoration: line-through;
}

.product-price {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 800;
  color: #ef4444;
}

.add-to-cart-btn {
  background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  padding: 10px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all var(--transition-fast);
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
}

.add-to-cart-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
}

@media (max-width: 768px) {
  .deals-wrapper {
    padding: 0 24px 24px;
  }

  .deals-hero {
    padding: 40px 24px;
  }

  .deals-hero h1 {
    font-size: 2rem;
  }

  .deals-header {
    flex-direction: column;
    gap: 16px;
  }

  .filters-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-select {
    width: 100%;
  }
}
</style>
