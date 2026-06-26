<template>
  <div class="product-page">
    <div class="container">
      <router-link to="/" class="back-link">
        <span class="back-arrow">&larr;</span> Back to store
      </router-link>

      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Loading product...</p>
      </div>

      <template v-else-if="product">
        <div class="product-hero">
          <div class="image-panel">
            <div class="image-frame">
              <img
                :src="product.image_url || placeholderImage"
                :alt="product.name"
                @error="($event.target as HTMLImageElement).src = placeholderImage"
              />
            </div>
            <div class="image-thumbs">
              <div class="thumb active"></div>
              <div class="thumb"></div>
              <div class="thumb"></div>
              <div class="thumb"></div>
            </div>
          </div>

          <div class="info-panel">
            <div class="breadcrumb">
              <span>{{ product.category?.name || 'General' }}</span>
              <span class="sep">/</span>
              <span class="current">{{ product.name }}</span>
            </div>

            <h1 class="product-title">{{ product.name }}</h1>

            <div class="rating-row">
              <div class="stars">
                <span v-for="i in 5" :key="i" :class="['star', { filled: i <= avgRating }]">★</span>
              </div>
              <span class="rating-text">{{ avgRating.toFixed(1) }} ({{ product.reviews?.length || 0 }} reviews)</span>
            </div>

            <div class="price-section">
              <span class="price">{{ formatCurrency(product.price) }}</span>
              <span v-if="product.stock > 0" class="stock-badge in-stock">In Stock</span>
              <span v-else class="stock-badge out-of-stock">Out of Stock</span>
            </div>

            <p class="sku">SKU: {{ product.sku || 'N/A' }}</p>

            <p v-if="product.description" class="description">{{ product.description }}</p>

            <div class="stock-info">
              <div class="stock-bar">
                <div class="stock-fill" :style="{ width: Math.min((product.stock / 50) * 100, 100) + '%' }"></div>
              </div>
              <span class="stock-label">{{ product.stock }} units available</span>
            </div>

            <div class="action-buttons">
              <button class="btn btn-primary btn-lg" @click="addToCart" :disabled="product.stock === 0">
                <ShoppingCart :size="18" />
                Add to Cart
              </button>
              <button
                class="btn btn-icon"
                :class="{ wishlisted: isWishlisted }"
                @click="toggleWishlist"
                :title="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
              >
                <Heart :size="20" :class="{ 'heart-filled': isWishlisted }" />
              </button>
            </div>

            <div class="meta-list">
              <div class="meta-item">
                <Truck :size="15" />
                <span>Free shipping on orders over $100</span>
              </div>
              <div class="meta-item">
                <RefreshCw :size="15" />
                <span>30-day easy returns</span>
              </div>
              <div class="meta-item">
                <Shield :size="15" />
                <span>1 year warranty included</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
          <div class="reviews-header">
            <div>
              <h2>Customer Reviews</h2>
              <p class="reviews-sub">
                {{ product.reviews?.length || 0 }} review{{ product.reviews?.length !== 1 ? 's' : '' }}
              </p>
            </div>
            <button
              v-if="store.user.value && !showForm && !userReview"
              class="btn btn-primary"
              @click="openForm"
            >
              <Star :size="16" />
              Write a Review
            </button>
          </div>

          <!-- Review Form -->
          <Transition name="slide">
            <div v-if="showForm" class="form-wrapper">
              <ReviewForm
                :productId="product.id"
                :initialRating="editingReview?.rating"
                :initialComment="editingReview?.comment"
                :isEditing="!!editingReview"
                @submit="handleSubmitReview"
                @cancel="closeForm"
              />
            </div>
          </Transition>

          <!-- Reviews Grid -->
          <div v-if="product.reviews?.length" class="reviews-grid">
            <div
              v-for="r in product.reviews"
              :key="r.id"
              class="review-card"
              :class="{ 'own-review': r.user_id === store.user.value?.id }"
            >
              <div class="review-card-header">
                <div class="reviewer">
                  <div class="avatar">{{ (r.user?.name || 'A')[0].toUpperCase() }}</div>
                  <div>
                    <div class="reviewer-name">{{ r.user?.name || 'Anonymous' }}</div>
                    <div class="reviewer-stars">
                      <span v-for="i in 5" :key="i" :class="['s', { on: i <= r.rating }]">★</span>
                    </div>
                  </div>
                </div>
                <div v-if="r.user_id === store.user.value?.id" class="review-actions">
                  <button class="action-chip" @click="editReview(r)">
                    <Edit :size="14" /> Edit
                  </button>
                  <button class="action-chip danger" @click="deleteReview(r)">
                    <Trash2 :size="14" /> Delete
                  </button>
                </div>
              </div>
              <p v-if="r.comment" class="review-comment">{{ r.comment }}</p>
              <div class="review-footer">
                <span class="review-date">{{ formatDate(r.created_at) }}</span>
                <div v-if="r.user_id === store.user.value?.id" class="own-badge">Your Review</div>
              </div>
            </div>
          </div>

          <div v-else class="reviews-empty">
            <MessageSquare :size="36" class="empty-icon" />
            <h3>No reviews yet</h3>
            <p>Be the first to share your experience with this product.</p>
            <button
              v-if="store.user.value"
              class="btn btn-primary"
              @click="openForm"
            >
              Write a Review
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { store } from '../../store';
import { api } from '../../services/api';
import ReviewForm from '../../components/product/ReviewForm.vue';
import { ShoppingCart, Heart, Truck, RefreshCw, Shield, Star, Edit, Trash2, MessageSquare } from 'lucide-vue-next';

const route = useRoute();
const placeholderImage = 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&auto=format&fit=crop';

const product = ref<any>(null);
const loading = ref(true);
const showForm = ref(false);
const editingReview = ref<any>(null);

const userReview = computed(() => {
  if (!product.value?.reviews || !store.user.value) return null;
  return product.value.reviews.find((r: any) => r.user_id === store.user.value.id);
});

const avgRating = computed(() => {
  if (!product.value?.reviews?.length) return 0;
  const sum = product.value.reviews.reduce((a: number, r: any) => a + r.rating, 0);
  return sum / product.value.reviews.length;
});

onMounted(async () => {
  const id = Number(route.params.id);
  try {
    const res = await api.getProduct(id);
    if (res.success) product.value = res.data;
  } catch (err) {
    console.error('Failed to fetch product', err);
  } finally {
    loading.value = false;
  }
});

const formatCurrency = (val: number) =>
  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);

const formatDate = (dateStr: string) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric',
  });
};

const addToCart = async () => {
  if (!product.value) return;
  await store.addToCart(product.value.id, 1);
};

const isWishlisted = computed(() => {
  if (!product.value) return false;
  return store.isWishlisted(product.value.id);
});

const toggleWishlist = async () => {
  if (!product.value) return;
  await store.toggleWishlist(product.value.id);
};

const openForm = () => {
  editingReview.value = null;
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  editingReview.value = null;
};

const editReview = (r: any) => {
  editingReview.value = r;
  showForm.value = true;
};

const handleSubmitReview = async (rating: number, comment: string) => {
  if (!product.value) return;
  let result;
  if (editingReview.value) {
    result = await store.updateReview(editingReview.value.id, rating, comment);
  } else {
    result = await store.createReview(product.value.id, rating, comment);
  }
  if (result) {
    showForm.value = false;
    editingReview.value = null;
    const res = await api.getProduct(product.value.id);
    if (res.success) product.value = res.data;
  }
};

const deleteReview = async (r: any) => {
  if (!confirm('Delete your review?')) return;
  const ok = await store.deleteReview(r.id);
  if (ok) {
    const res = await api.getProduct(product.value.id);
    if (res.success) product.value = res.data;
  }
};
</script>

<style scoped>
.product-page {
  min-height: 100vh;
  background: var(--bg-primary, #0a0e17);
  color: var(--text-primary, #f9fafb);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px 64px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary, #9ca3af);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 28px;
  transition: color var(--transition-fast);
}
.back-link:hover { color: var(--accent-secondary, #60a5fa); }
.back-arrow { font-size: 1.2rem; }

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 120px 0;
  gap: 16px;
  color: var(--text-secondary, #9ca3af);
}
.loader {
  width: 44px; height: 44px;
  border: 4px solid rgba(255,255,255,0.05);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Hero Layout */
.product-hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  margin-bottom: 64px;
}
@media (max-width: 900px) {
  .product-hero { grid-template-columns: 1fr; gap: 32px; }
}

/* Image Panel */
.image-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.image-frame {
  background: var(--bg-secondary, #111827);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 20px;
  padding: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 460px;
  overflow: hidden;
}
.image-frame img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.3s ease;
}
.image-frame:hover img { transform: scale(1.06); }

.image-thumbs {
  display: flex;
  gap: 10px;
}
.thumb {
  width: 64px; height: 64px;
  border-radius: 12px;
  background: var(--bg-secondary, #111827);
  border: 2px solid rgba(255,255,255,0.05);
  cursor: pointer;
  transition: border-color var(--transition-fast);
}
.thumb:hover { border-color: rgba(59,130,246,0.3); }
.thumb.active { border-color: #3b82f6; }

/* Info Panel */
.breadcrumb {
  font-size: 0.8rem;
  color: var(--text-tertiary, #6b7280);
  margin-bottom: 12px;
}
.breadcrumb .sep { margin: 0 8px; }
.breadcrumb .current { color: var(--text-secondary, #9ca3af); }

.product-title {
  font-family: var(--font-display);
  font-size: 2.4rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 16px;
  letter-spacing: -0.02em;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}
.stars { display: flex; gap: 2px; }
.star { color: #374151; font-size: 1rem; }
.star.filled { color: #fbbf24; }
.rating-text { font-size: 0.85rem; color: var(--text-tertiary, #6b7280); }

.price-section {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
}
.price {
  font-family: var(--font-display);
  font-size: 2.2rem;
  font-weight: 800;
  color: var(--accent-secondary, #3b82f6);
  letter-spacing: -0.02em;
}
.stock-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 50px;
}
.stock-badge.in-stock { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.stock-badge.out-of-stock { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }

.sku {
  font-size: 0.8rem;
  font-family: monospace;
  color: var(--text-tertiary, #6b7280);
  margin-bottom: 20px;
}

.description {
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--text-secondary, #9ca3af);
  margin-bottom: 24px;
}

.stock-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
}
.stock-bar {
  flex: 1;
  max-width: 200px;
  height: 6px;
  background: rgba(255,255,255,0.06);
  border-radius: 3px;
  overflow: hidden;
}
.stock-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
  border-radius: 3px;
  transition: width 0.4s ease;
}
.stock-label { font-size: 0.8rem; color: var(--text-tertiary, #6b7280); }

.action-buttons {
  display: flex;
  gap: 12px;
  margin-bottom: 28px;
}
.btn-lg {
  padding: 14px 32px;
  font-size: 1rem;
  font-weight: 700;
  border-radius: 12px;
}
.btn-icon {
  width: 52px; height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  color: var(--text-secondary, #9ca3af);
  cursor: pointer;
  transition: all var(--transition-fast);
}
.btn-icon:hover { background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.2); }
.btn-icon.wishlisted { color: #ef4444; background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2); }
.heart-filled { fill: #ef4444; }

.meta-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 20px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.04);
  border-radius: 12px;
}
.meta-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.85rem;
  color: var(--text-tertiary, #6b7280);
}

/* Reviews Section */
.reviews-section {
  border-top: 1px solid rgba(255,255,255,0.06);
  padding-top: 40px;
}
.reviews-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
}
.reviews-header h2 {
  font-family: var(--font-display);
  font-size: 1.6rem;
  font-weight: 700;
}
.reviews-sub {
  font-size: 0.85rem;
  color: var(--text-tertiary, #6b7280);
  margin-top: 4px;
}

.form-wrapper {
  margin-bottom: 28px;
}

/* Transition */
.slide-enter-active, .slide-leave-active {
  transition: all 0.25s ease;
}
.slide-enter-from, .slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Review Cards */
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 16px;
}
@media (max-width: 600px) {
  .reviews-grid { grid-template-columns: 1fr; }
}

.review-card {
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 20px;
  transition: border-color var(--transition-fast);
}
.review-card:hover { border-color: rgba(255,255,255,0.1); }
.review-card.own-review {
  background: rgba(59,130,246,0.03);
  border-color: rgba(59,130,246,0.15);
}

.review-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
}
.reviewer { display: flex; align-items: center; gap: 12px; }
.avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: #fff;
  font-weight: 700; font-size: 0.9rem;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.reviewer-name {
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 2px;
}
.reviewer-stars { display: flex; gap: 1px; }
.reviewer-stars .s { color: #374151; font-size: 0.85rem; }
.reviewer-stars .s.on { color: #fbbf24; }

.review-actions { display: flex; gap: 6px; flex-shrink: 0; }
.action-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.06);
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary, #9ca3af);
  cursor: pointer;
  transition: all var(--transition-fast);
}
.action-chip:hover { background: rgba(255,255,255,0.08); color: var(--text-primary, #f9fafb); }
.action-chip.danger:hover { color: #ef4444; border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); }

.review-comment {
  font-size: 0.9rem;
  line-height: 1.6;
  color: var(--text-secondary, #9ca3af);
  margin-bottom: 12px;
}
.review-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.review-date { font-size: 0.75rem; color: var(--text-tertiary, #6b7280); }
.own-badge {
  font-size: 0.7rem;
  font-weight: 600;
  color: #3b82f6;
  background: rgba(59,130,246,0.1);
  padding: 2px 8px;
  border-radius: 4px;
}

.reviews-empty {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255,255,255,0.02);
  border: 1px dashed rgba(255,255,255,0.06);
  border-radius: 16px;
}
.reviews-empty h3 {
  font-family: var(--font-display);
  font-size: 1.15rem;
  font-weight: 600;
  margin: 12px 0 6px;
}
.reviews-empty p {
  font-size: 0.9rem;
  color: var(--text-tertiary, #6b7280);
  margin-bottom: 20px;
}
.empty-icon { color: var(--text-tertiary, #6b7280); }
</style>
