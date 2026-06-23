<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import { useRouter } from 'vue-router';
import { 
  Minus, 
  Plus, 
  Trash2, 
  ArrowLeft, 
  Lock, 
  Truck, 
  Tag, 
  ShoppingCart,
  Check
} from 'lucide-vue-next';

const router = useRouter();
const loading = ref(false);
const promoCode = ref('');
const promoApplied = ref(false);
const promoDiscount = ref(0);

const fetchCart = async () => {
  loading.value = true;
  try {
    await store.fetchCart();
  } catch (err) {
    console.error('Failed to load cart items', err);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchCart();
});

const cart = computed(() => store.cart.value);
const subtotal = computed(() => store.cartSubtotal.value);
const taxRate = 0.08;
const tax = computed(() => subtotal.value * taxRate);
const total = computed(() => subtotal.value + tax.value - promoDiscount.value);

const handleQuantityChange = async (cartItem: any, change: number) => {
  const newQty = cartItem.quantity + change;
  if (newQty < 1) return;
  
  loading.value = true;
  try {
    await store.updateCartQuantity(cartItem.id, newQty);
  } finally {
    loading.value = false;
  }
};

const handleRemoveItem = async (cartId: number) => {
  loading.value = true;
  try {
    await store.removeFromCart(cartId);
  } finally {
    loading.value = false;
  }
};

const handleApplyPromo = () => {
  if (promoCode.value.toUpperCase() === 'PRECISION10') {
    promoDiscount.value = subtotal.value * 0.1;
    promoApplied.value = true;
    store.setAlert('Promo code Applied: 10% Discount!', 'success');
  } else {
    store.setAlert('Invalid promo code. Try "PRECISION10"!', 'error');
  }
};

const handleCheckout = async () => {
  if (cart.value.length === 0) return;
  loading.value = true;
  try {
    const items = cart.value.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
    }));
    const res = await api.createOrder(items);
    if (res.success) {
      // Clear cart items
      for (const item of cart.value) {
        await api.removeFromCart(item.id);
      }
      await store.fetchCart();
      store.setAlert('Order placed successfully! Thank you for shopping.', 'success');
      router.push('/');
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to checkout', 'error');
  } finally {
    loading.value = false;
  }
};

// Recommended items matching the wireframe: Rugged Case, Cable, Kit, Stand
const recommendedSkus = ['ACC-RC-V2', 'ACC-BC-06', 'ACC-SC-PRO', 'ACC-AC-01'];
const recommendedProducts = computed(() => {
  return store.products.value.filter(p => recommendedSkus.includes(p.sku));
});

const handleAddRecommended = async (productId: number) => {
  await store.addToCart(productId, 1);
};

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(val);
};
</script>

<template>
  <div class="cart-wrapper">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
      <router-link to="/">Home</router-link>
      <span>&gt;</span>
      <span class="active">Shopping Cart</span>
    </div>

    <h1 class="cart-page-title">Your Shopping Cart</h1>

    <!-- Empty state -->
    <div v-if="cart.length === 0" class="card cart-empty-card animate-fade-in">
      <ShoppingCart :size="48" class="cart-empty-icon" />
      <h3>Your shopping cart is empty</h3>
      <p>Fill it with our premium high-fidelity gadgets.</p>
      <router-link to="/" class="btn btn-primary btn-sm">
        Start Shopping
      </router-link>
    </div>

    <!-- Split column layouts -->
    <div v-else class="cart-layout animate-fade-in-up">
      <!-- 1. Cart items list -->
      <div class="cart-items-column">
        <div class="cart-items-list">
          <div v-for="item in cart" :key="item.id" class="card cart-item-card">
            <div class="item-img-box">
              <img 
                :src="item.product?.image_url" 
                :alt="item.product?.name" 
                @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=256&auto=format&fit=crop'"
              />
            </div>
            
            <div class="item-details">
              <div class="item-info">
                <h4>{{ item.product?.name }}</h4>
                <p class="item-meta">
                  {{ item.product?.sku }} • Default Spec
                </p>
              </div>

              <div class="item-pricing">
                <span class="item-price">{{ formatCurrency(item.product?.price || 0) }}</span>
              </div>

              <!-- Quantity selector -->
              <div class="quantity-selector">
                <button class="qty-btn" @click="handleQuantityChange(item, -1)" :disabled="item.quantity <= 1 || loading">
                  <Minus :size="12" />
                </button>
                <span class="qty-val">{{ item.quantity }}</span>
                <button class="qty-btn" @click="handleQuantityChange(item, 1)" :disabled="loading">
                  <Plus :size="12" />
                </button>
              </div>

              <!-- Remove button -->
              <button class="remove-btn" @click="handleRemoveItem(item.id)" :disabled="loading">
                <Trash2 :size="14" />
                Remove
              </button>
            </div>
          </div>
        </div>

        <router-link to="/" class="continue-shopping">
          <ArrowLeft :size="14" />
          Continue Shopping
        </router-link>
      </div>

      <!-- 2. Summary side card -->
      <div class="summary-column">
        <div class="card summary-card">
          <h3>Order Summary</h3>
          
          <div class="summary-row">
            <span>Subtotal</span>
            <span>{{ formatCurrency(subtotal) }}</span>
          </div>

          <div class="summary-row">
            <span>Shipping</span>
            <span class="text-success">Free</span>
          </div>

          <div class="summary-row">
            <span>Estimated Tax</span>
            <span>{{ formatCurrency(tax) }}</span>
          </div>

          <div v-if="promoApplied" class="summary-row text-success">
            <span>Promo Discount (10%)</span>
            <span>-{{ formatCurrency(promoDiscount) }}</span>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row total-row">
            <span>Total</span>
            <span>{{ formatCurrency(total) }}</span>
          </div>

          <button class="btn btn-primary checkout-btn" @click="handleCheckout" :disabled="loading">
            <Lock :size="14" />
            Proceed to Checkout
          </button>
          
          <div class="free-shipping-bar">
            <Truck :size="14" />
            <span>Free shipping on orders over $100.</span>
          </div>

          <!-- Badges -->
          <div class="trust-badges">
            <div class="trust-badge">
              <Check :size="12" class="badge-icon" />
              Secure Payment
            </div>
            <div class="trust-badge">
              <Check :size="12" class="badge-icon" />
              Tracked Delivery
            </div>
          </div>
        </div>

        <!-- Promo code box -->
        <div class="card promo-card">
          <label>Promo Code</label>
          <div class="promo-form">
            <input 
              type="text" 
              placeholder="Enter code (e.g. PRECISION10)" 
              class="form-input promo-input"
              v-model="promoCode"
              :disabled="promoApplied"
            />
            <button class="btn btn-secondary promo-btn-apply" @click="handleApplyPromo" :disabled="promoApplied">
              Apply
            </button>
          </div>
          <p v-if="promoApplied" class="promo-success-msg">
            <Tag :size="12" /> Coupon applied successfully!
          </p>
        </div>
      </div>
    </div>

    <!-- RECOMMENDED ITEMS SECTION -->
    <section class="recommended-section">
      <h3 class="recommended-title">Recommended for You</h3>
      
      <div class="recommended-grid">
        <div v-for="product in recommendedProducts" :key="product.id" class="card recommended-card">
          <img 
            :src="product.image_url" 
            :alt="product.name" 
            class="rec-thumb"
            @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=256&auto=format&fit=crop'"
          />
          
          <div class="rec-info">
            <h4>{{ product.name }}</h4>
            <span class="rec-price">{{ formatCurrency(product.price) }}</span>
          </div>

          <button class="rec-add-btn" @click="handleAddRecommended(product.id)" title="Add to Cart">
            <ShoppingCart :size="16" />
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.cart-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px;
  font-family: var(--font-body);
  background-color: #05070c;
  color: #f3f4f6;
  min-height: 80vh;
}

/* Breadcrumbs */
.breadcrumbs {
  display: flex;
  gap: 8px;
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 24px;
}

.breadcrumbs a {
  color: #6b7280;
  text-decoration: none;
  transition: color var(--transition-fast);
}

.breadcrumbs a:hover {
  color: white;
}

.breadcrumbs .active {
  color: #3b82f6;
  font-weight: 600;
}

.cart-page-title {
  font-family: var(--font-display);
  font-size: 2.2rem;
  font-weight: 800;
  color: white;
  margin-bottom: 32px;
}

/* Empty State Card */
.cart-empty-card {
  background-color: #111827;
  border-color: rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 40px;
  gap: 16px;
  text-align: center;
}

.cart-empty-icon {
  color: #1f2937;
}

.cart-empty-card h3 {
  font-family: var(--font-display);
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
}

.cart-empty-card p {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Cart Layout Grid */
.cart-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  align-items: start;
}

@media (max-width: 1024px) {
  .cart-layout {
    grid-template-columns: 1fr;
  }
}

.cart-items-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.cart-items-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item-card {
  background-color: #111827;
  border-color: rgba(255, 255, 255, 0.05);
  padding: 20px;
  display: flex;
  gap: 20px;
  align-items: center;
}

.item-img-box {
  width: 90px;
  height: 90px;
  background-color: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
}

.item-img-box img {
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
}

.item-details {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 16px;
  align-items: center;
  flex: 1;
}

@media (max-width: 640px) {
  .item-details {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}

.item-info h4 {
  font-family: var(--font-display);
  font-size: 1.05rem;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.item-meta {
  font-size: 0.75rem;
  color: #6b7280;
}

.item-price {
  font-family: var(--font-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: white;
}

/* Quantity selector */
.quantity-selector {
  display: flex;
  align-items: center;
  background-color: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-sm);
  padding: 4px;
  justify-self: start;
}

.qty-btn {
  background: none;
  border: none;
  color: #9ca3af;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.qty-btn:hover:not(:disabled) {
  color: white;
}

.qty-btn:disabled {
  opacity: 0.3;
}

.qty-val {
  font-size: 0.85rem;
  font-weight: 700;
  padding: 0 10px;
  color: white;
}

.remove-btn {
  background: none;
  border: none;
  color: #ef4444;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  justify-self: end;
}

.remove-btn:hover {
  color: #f87171;
}

.continue-shopping {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #3b82f6;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  align-self: flex-start;
  transition: color var(--transition-fast);
}

.continue-shopping:hover {
  color: #2563eb;
}

/* Summary Card */
.summary-card {
  background-color: #111827;
  border-color: rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.summary-card h3 {
  font-family: var(--font-display);
  font-size: 1.15rem;
  font-weight: 700;
  color: white;
  margin-bottom: 8px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #9ca3af;
}

.total-row {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 800;
  color: white;
}

.summary-divider {
  height: 1px;
  background-color: rgba(255, 255, 255, 0.05);
}

.checkout-btn {
  width: 100%;
  padding: 12px;
  margin-top: 8px;
}

.free-shipping-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #10b981;
  font-size: 0.75rem;
  font-weight: 600;
  justify-content: center;
  background-color: rgba(16, 185, 129, 0.08);
  padding: 8px;
  border-radius: var(--radius-sm);
  border: 1px solid rgba(16, 185, 129, 0.15);
}

.trust-badges {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 4px;
}

.trust-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.7rem;
  color: #6b7280;
}

.badge-icon {
  color: #10b981;
}

/* Promo code box */
.promo-card {
  margin-top: 24px;
  background-color: #111827;
  border-color: rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.promo-card label {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #9ca3af;
  letter-spacing: 0.05em;
}

.promo-form {
  display: flex;
  gap: 8px;
}

.promo-input {
  flex: 1;
  padding: 8px 12px;
  font-size: 0.85rem;
}

.promo-btn-apply {
  padding: 8px 16px;
  font-size: 0.85rem;
}

.promo-success-msg {
  color: #10b981;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Recommended Section */
.recommended-section {
  margin-top: 60px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 40px;
}

.recommended-title {
  font-family: var(--font-display);
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
  margin-bottom: 24px;
}

.recommended-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

@media (max-width: 1024px) {
  .recommended-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .recommended-grid {
    grid-template-columns: 1fr;
  }
}

.recommended-card {
  background-color: #111827;
  border-color: rgba(255, 255, 255, 0.05);
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  position: relative;
}

.rec-thumb {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-sm);
  background-color: #0b0f19;
  object-fit: contain;
  padding: 4px;
}

.rec-info h4 {
  font-family: var(--font-display);
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
  margin-bottom: 2px;
}

.rec-price {
  font-size: 0.8rem;
  color: #3b82f6;
  font-weight: 700;
}

.rec-add-btn {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background-color: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.rec-add-btn:hover {
  background-color: #2563eb;
  color: white;
}
</style>
