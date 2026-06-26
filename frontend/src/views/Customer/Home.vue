<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import {
  ShoppingCart,
  Heart,
  Star,
  Cpu,
  Laptop,
  Headphones,
  Tv,
  ArrowRight,
  Send,
  Grid,
  Share2,
  Settings,
  MapPin
} from 'lucide-vue-next';

const loading = ref(true);
const products = ref<any[]>([]);

const fetchProducts = async () => {
  loading.value = true;
  try {
    const res = await api.getProducts();
    if (res.success) {
      products.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }

  } catch (e) {
    console.error('Failed to load storefront products', e);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchProducts();
});

// Seed data trending items matching the wireframe
const trendingSkus = ['HP-EP-02', 'KB-PT-K1', 'WA-VF-03', 'CPU-PC-09'];
const trendingProducts = computed(() => {
  return products.value.filter(p => trendingSkus.includes(p.sku));
});

const getBadge = (sku: string) => {
  if (sku === 'HP-EP-02') return { text: 'SALE', class: 'sale-badge' };
  if (sku === 'WA-VF-03') return { text: 'NEW', class: 'new-badge' };
  return null;
};

const getOriginalPrice = (sku: string) => {
  if (sku === 'HP-EP-02') return 150.00;
  return null;
};

const handleAddToCart = async (productId: number) => {
  await store.addToCart(productId, 1);
};

const handleToggleWishlist = async (productId: number) => {
  await store.toggleWishlist(productId);
};

const emailInput = ref('');
const handleSubscribe = () => {
  if (emailInput.value) {
    store.setAlert(`Successfully subscribed email: ${emailInput.value}`, 'success');
    emailInput.value = '';
  }
};
</script>

<template>
  <div class="home-wrapper">
    <!-- HERO SECTION -->
    <section class="hero-section animate-fade-in">
      <div class="hero-container">
        <div class="hero-text">
          <span class="hero-tagline">NEXT-GEN INTERFACE PACK</span>
          <h1 class="hero-title">Precision Performance Defined.</h1>
          <p class="hero-description">
            Experience the apex of retail technology. Our curated collection of high-fidelity gadgets and
            high-performance hardware is designed for those who demand excellence in every pixel.
          </p>
          <div class="hero-actions">
            <button class="btn btn-primary"
              @click="$el.querySelector('#trending-products').scrollIntoView({ behavior: 'smooth' })">
              Shop Now
            </button>
            <button class="btn btn-outline">
              View Showcase
            </button>
          </div>
        </div>

        <div class="hero-visual">
          <!-- Animated phone blueprint SVG -->
          <svg viewBox="0 0 400 400" class="blueprint-svg">
            <defs>
              <linearGradient id="glowGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.6" />
                <stop offset="100%" stop-color="#10b981" stop-opacity="0.1" />
              </linearGradient>
            </defs>
            <!-- Background mesh -->
            <rect width="400" height="400" fill="none" />
            <circle cx="200" cy="200" r="160" fill="none" stroke="rgba(59, 130, 246, 0.08)" stroke-width="1.5"
              stroke-dasharray="8 4" />
            <circle cx="200" cy="200" r="120" fill="none" stroke="rgba(16, 185, 129, 0.1)" stroke-width="1" />

            <!-- Phone outline -->
            <rect x="110" y="50" width="180" height="300" rx="24" fill="url(#glowGrad)" stroke="#3b82f6"
              stroke-width="2" />
            <rect x="120" y="60" width="160" height="280" rx="16" fill="none" stroke="rgba(59, 130, 246, 0.2)"
              stroke-width="1" />

            <!-- Camera Module -->
            <rect x="135" y="75" width="40" height="40" rx="8" fill="none" stroke="#3b82f6" stroke-width="1.5" />
            <circle cx="155" cy="95" r="10" fill="none" stroke="#10b981" stroke-width="1.5" />

            <!-- Circuit paths -->
            <path d="M 175 95 L 230 95 L 230 180" fill="none" stroke="rgba(16, 185, 129, 0.4)" stroke-width="1.5"
              stroke-dasharray="3" />
            <path d="M 230 180 L 160 180 L 160 250" fill="none" stroke="rgba(59, 130, 246, 0.4)" stroke-width="1.5" />

            <!-- Chip representation -->
            <rect x="180" y="140" width="40" height="40" rx="6" fill="#0b0f19" stroke="#10b981" stroke-width="2"
              class="pulse-chip" />
            <text x="189" y="164" fill="#10b981" font-size="9" font-weight="bold" font-family="monospace">CPU</text>

            <!-- Battery cell -->
            <rect x="135" y="210" width="130" height="70" rx="8" fill="none" stroke="rgba(59, 130, 246, 0.3)"
              stroke-width="1.5" />
            <line x1="145" y1="225" x2="255" y2="225" stroke="rgba(59, 130, 246, 0.2)" stroke-width="2" />
            <line x1="145" y1="245" x2="255" y2="245" stroke="rgba(59, 130, 246, 0.2)" stroke-width="2" />
            <line x1="145" y1="265" x2="255" y2="265" stroke="rgba(59, 130, 246, 0.2)" stroke-width="2" />
          </svg>
        </div>
      </div>
    </section>

    <!-- FEATURED CATEGORIES SECTION -->
    <section class="categories-section">
      <h2 class="section-title">Featured Categories</h2>
      <div class="categories-grid">
        <!-- 1. Computing -->
        <router-link to="/products" class="category-card large-card animate-fade-in">
          <div class="card-bg-img img-comp"></div>
          <div class="category-content">
            <h3>High-Performance Computing</h3>
            <p>Laptops, Workstations & Gaming Rigs</p>
            <button class="category-btn">Explore Category</button>
          </div>
        </router-link>

        <!-- 2. Audio -->
        <router-link to="/products" class="category-card animate-fade-in">
          <div class="card-bg-img img-audio"></div>
          <div class="category-content">
            <h3>Immersive Audio</h3>
            <p>Noise Canceling & Hi-Fi Systems</p>
          </div>
        </router-link>

        <!-- 3. Smart Living -->
        <router-link to="/products" class="category-card animate-fade-in">
          <div class="card-bg-img img-smart"></div>
          <div class="category-content">
            <h3>Smart Living</h3>
            <p>Automated sensors & controllers</p>
          </div>
        </router-link>

        <!-- 4. Photography -->
        <router-link to="/products" class="category-card animate-fade-in">
          <div class="card-bg-img img-photo"></div>
          <div class="category-content">
            <h3>Photography</h3>
            <p>Cameras, lenses, & lighting mounts</p>
          </div>
        </router-link>
      </div>
    </section>

    <!-- TRENDING PRODUCTS SECTION -->
    <section id="trending-products" class="trending-section">
      <div class="trending-header">
        <div>
          <h2 class="section-title">Trending Products</h2>
          <p class="section-subtitle">Most wanted electronics this week</p>
        </div>
        <router-link to="/products" class="btn btn-outline btn-sm">
          View All Products
          <ArrowRight :size="14" />
        </router-link>
      </div>

      <div v-if="loading" class="trending-loading">
        <div class="loader"></div>
        <p>Fetching storefront catalog...</p>
      </div>

      <div v-else class="products-grid">
        <div v-for="product in trendingProducts" :key="product.id" class="product-item-card">
          <!-- Card Header & Badge -->
          <div class="product-card-top">
            <span v-if="getBadge(product.sku)" :class="['card-badge', getBadge(product.sku)?.class]">
              {{ getBadge(product.sku)?.text }}
            </span>
            <button class="wishlist-toggle-btn" @click="handleToggleWishlist(product.id)"
              :title="store.isWishlisted(product.id) ? 'Remove from Wishlist' : 'Add to Wishlist'">
              <Heart :size="18" :class="{ 'wishlist-active': store.isWishlisted(product.id) }" />
            </button>
            <img :src="product.image_url" :alt="product.name" class="product-card-img"
              @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop'" />
          </div>

          <!-- Card Details -->
          <div class="product-card-body">
            <span class="product-card-category">{{ product.category?.name }}</span>
            <h3 class="product-card-name">{{ product.name }}</h3>

            <div class="product-rating">
              <div class="stars">
                <Star v-for="i in 5" :key="i" :size="12" class="filled-star" />
              </div>
              <span class="rating-count">({{ 80 + product.id * 3 }})</span>
            </div>

            <div class="product-card-footer">
              <div class="price-container">
                <span class="current-price">
                  {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(product.price) }}
                </span>
                <span v-if="getOriginalPrice(product.sku)" class="original-price">
                  {{ new Intl.NumberFormat('en-US', {
                    style: 'currency', currency: 'USD'
                  }).format(getOriginalPrice(product.sku)!) }}
                </span>
              </div>

              <button class="add-to-cart-card-btn" @click="handleAddToCart(product.id)">
                <ShoppingCart :size="14" />
                Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- STAY AHEAD OF THE CURVE CTA -->
    <section class="cta-section animate-fade-in">
      <div class="cta-content">
        <h2>Stay ahead of the curve.</h2>
        <p>Join our elite member list for early access to product launches, exclusive technical guides, and member-only
          pricing events.</p>
        <form @submit.prevent="handleSubscribe" class="cta-form">
          <input type="email" placeholder="Enter your email" class="cta-input" v-model="emailInput" required />
          <button type="submit" class="cta-submit-btn">
            Subscribe
          </button>
        </form>
      </div>
    </section>

    <!-- CUSTOMER FOOTER -->
    <footer class="customer-footer">
      <div class="footer-grid">
        <div class="footer-info">
          <h3>Precision Retail</h3>
          <p>Defining the future of technical retail through mathematical harmony and structural clarity.</p>
          <div class="footer-icons">
            <Grid :size="18" />
            <Share2 :size="18" />
            <Settings :size="18" />
          </div>
        </div>

        <div class="footer-links-col">
          <h4>Shop</h4>
          <ul>
            <li>Computers</li>
            <li>Smart Home</li>
            <li>Audio Gear</li>
            <li>Components</li>
          </ul>
        </div>

        <div class="footer-links-col">
          <h4>Support</h4>
          <ul>
            <li>Privacy Policy</li>
            <li>Terms of Service</li>
            <li>Shipping Info</li>
            <li>Returns</li>
          </ul>
        </div>

        <div class="footer-map-col">
          <h4>Store Location</h4>
          <div class="footer-map-mock">
            <MapPin :size="16" class="footer-map-pin" />
            <div class="footer-map-overlay">5th Ave, New York, NY 10001, USA</div>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 Precision Retail. All rights reserved. Precision System v2.4.1</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.home-wrapper {
  background-color: var(--bg-primary);
  color: var(--text-primary);
  font-family: var(--font-body);
  display: flex;
  flex-direction: column;
  gap: 80px;
}

/* Hero Section */
.hero-section {
  padding: 100px 0 60px 0;
  background: var(--accent-gradient);
  background-size: 200% 200%;
  animation: gradientShift 10s ease infinite;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 600px;
  height: 600px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  animation: float 8s ease-in-out infinite;
}

.hero-section::after {
  content: '';
  position: absolute;
  bottom: -30%;
  left: -10%;
  width: 400px;
  height: 400px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 50%;
  animation: float 10s ease-in-out infinite reverse;
}

.hero-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  align-items: center;
  gap: 60px;
  position: relative;
  z-index: 1;
}

@media (max-width: 768px) {
  .hero-container {
    grid-template-columns: 1fr;
  }

  .hero-visual {
    display: none;
  }
}

.hero-text {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.hero-tagline {
  font-family: var(--font-body);
  font-size: 0.85rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.9);
  letter-spacing: 0.1em;
  text-transform: uppercase;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  padding: 8px 16px;
  border-radius: 50px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: fit-content;
}

.hero-title {
  font-family: var(--font-display);
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.1;
  color: white;
  letter-spacing: -0.03em;
  text-shadow: 0 2px 30px rgba(0, 0, 0, 0.2);
}

.hero-description {
  font-size: 1.15rem;
  color: rgba(255, 255, 255, 0.85);
  line-height: 1.7;
}

.hero-actions {
  display: flex;
  gap: 16px;
  margin-top: 8px;
}

.blueprint-svg {
  width: 100%;
  max-width: 450px;
  margin: 0 auto;
  overflow: visible;
  filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
}

.pulse-chip {
  animation: pulseGlow 3s infinite ease-in-out;
}

/* Featured Categories */
.categories-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  width: 100%;
}

.section-title {
  font-family: var(--font-display);
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 32px;
  position: relative;
  display: inline-block;
  background: var(--accent-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  grid-auto-rows: 260px;
}

.category-card {
  position: relative;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  overflow: hidden;
  display: flex;
  align-items: flex-end;
  padding: 28px;
  transition: all var(--transition-normal);
  background: var(--bg-card);
}

.category-card:hover {
  transform: translateY(-8px);
  border-color: var(--accent-primary);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.large-card {
  grid-column: span 2;
  grid-row: span 2;
  height: auto;
}

@media (max-width: 1024px) {
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .large-card {
    grid-column: span 2;
  }
}

@media (max-width: 640px) {
  .categories-grid {
    grid-template-columns: 1fr;
  }

  .large-card {
    grid-column: span 1;
  }
}

.card-bg-img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  filter: brightness(0.4) contrast(1.1);
  transition: transform var(--transition-slow);
  z-index: 1;
}

.category-card:hover .card-bg-img {
  transform: scale(1.05);
}

.img-comp {
  background-image: url('https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=800&auto=format&fit=crop');
}

.img-audio {
  background-image: url('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=600&auto=format&fit=crop');
}

.img-smart {
  background-image: url('https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=600&auto=format&fit=crop');
}

.img-photo {
  background-image: url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=600&auto=format&fit=crop');
}

.category-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.category-content h3 {
  font-family: var(--font-display);
  font-size: 1.4rem;
  font-weight: 700;
  color: white;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.category-content p {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
}

.category-btn {
  background-color: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.25);
  color: white;
  padding: 10px 20px;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 50px;
  cursor: pointer;
  align-self: flex-start;
  margin-top: 12px;
  transition: all var(--transition-fast);
}

.category-btn:hover {
  background-color: white;
  color: var(--accent-primary);
  transform: translateY(-2px);
}

/* Trending Products */
.trending-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  width: 100%;
}

.trending-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 32px;
}

.section-subtitle {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin-top: 8px;
}

.trending-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px 0;
  gap: 16px;
  color: var(--text-tertiary);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.product-item-card {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all var(--transition-normal);
  position: relative;
}

.product-item-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: var(--accent-gradient);
  opacity: 0;
  transition: opacity var(--transition-normal);
  z-index: 0;
}

.product-item-card:hover {
  transform: translateY(-8px);
  border-color: var(--accent-primary);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.product-card-top {
  position: relative;
  padding: 24px;
  background-color: var(--bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 220px;
  overflow: hidden;
  z-index: 1;
}

.product-card-img {
  max-height: 90%;
  max-width: 90%;
  object-fit: contain;
  transition: transform var(--transition-normal);
  filter: brightness(0.9);
}

.product-item-card:hover .product-card-img {
  transform: scale(1.08);
  filter: brightness(1);
}

.card-badge {
  position: absolute;
  top: 16px;
  left: 16px;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  z-index: 10;
  backdrop-filter: blur(10px);
}

.sale-badge {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.new-badge {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

.wishlist-toggle-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-color);
  width: 36px;
  height: 36px;
  border-radius: 50%;
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
  flex: 1;
  z-index: 1;
  position: relative;
  background: var(--bg-card);
}

.product-card-category {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--accent-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.product-card-name {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.3;
  transition: color var(--transition-fast);
}

.product-item-card:hover .product-card-name {
  color: var(--accent-primary);
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

.product-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
}

.price-container {
  display: flex;
  flex-direction: column;
}

.current-price {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--text-primary);
  background: var(--accent-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.original-price {
  font-size: 0.8rem;
  color: var(--text-tertiary);
  text-decoration: line-through;
}

.add-to-cart-card-btn {
  background: var(--accent-gradient);
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
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.add-to-cart-card-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

/* CTA Section */
.cta-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  width: 100%;
}

.cta-content {
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-xl);
  padding: 60px 40px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.cta-content h2 {
  font-family: var(--font-display);
  font-size: 2.2rem;
  font-weight: 800;
  color: white;
}

.cta-content p {
  color: #9ca3af;
  max-width: 580px;
  font-size: 0.95rem;
  line-height: 1.5;
}

.cta-form {
  display: flex;
  gap: 12px;
  width: 100%;
  max-width: 480px;
  margin-top: 10px;
}

.cta-input {
  flex: 1;
  background-color: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
}

.cta-submit-btn {
  background-color: #2563eb;
  color: white;
  border: none;
  font-weight: 600;
  padding: 0 24px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.cta-submit-btn:hover {
  background-color: #1d4ed8;
}

/* Customer Footer */
.customer-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding: 60px 40px 30px 40px;
  background-color: #07090e;
}

.footer-grid {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 2fr;
  gap: 40px;
}

@media (max-width: 1024px) {
  .footer-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .footer-grid {
    grid-template-columns: 1fr;
  }
}

.footer-info h3 {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 16px;
}

.footer-info p {
  color: #6b7280;
  font-size: 0.85rem;
  line-height: 1.5;
  margin-bottom: 20px;
}

.footer-icons {
  display: flex;
  gap: 16px;
  color: #4b5563;
}

.footer-links-col h4,
.footer-map-col h4 {
  font-family: var(--font-display);
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: 20px;
}

.footer-links-col ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-links-col li {
  color: #6b7280;
  font-size: 0.85rem;
  cursor: pointer;
  transition: color var(--transition-fast);
}

.footer-links-col li:hover {
  color: white;
}

.footer-map-mock {
  background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.15) 0%, rgba(17, 24, 39, 0.8) 100%),
    url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" opacity="0.03"><rect width="50" height="50" fill="none" stroke="%23fff" stroke-width="0.5"/></svg>');
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-md);
  height: 120px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  position: relative;
}

.footer-map-pin {
  color: #3b82f6;
  animation: bounce 2s infinite ease-in-out;
}

.footer-map-overlay {
  font-size: 0.75rem;
  color: #9ca3af;
  background-color: #0b0f19;
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.footer-bottom {
  max-width: 1400px;
  margin: 40px auto 0 auto;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.03);
  text-align: center;
  color: #4b5563;
  font-size: 0.8rem;
}
</style>
