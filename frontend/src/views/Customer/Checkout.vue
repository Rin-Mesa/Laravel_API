<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '../../store';
import { api } from '../../services/api';
import {
  Lock,
  Truck,
  Shield,
  CreditCard,
  MapPin,
  Phone,
  Mail,
  User,
  Check
} from 'lucide-vue-next';

const router = useRouter();
const loading = ref(false);
const processing = ref(false);

const formData = ref({
  // Shipping Info
  full_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  country: 'United States',

  // Payment Info
  card_number: '',
  card_name: '',
  card_expiry: '',
  card_cvc: '',

  // Additional
  save_address: false,
  same_as_shipping: true,
});

const errors = ref<Record<string, string>>({});

const cart = computed(() => store.cart.value);
const subtotal = computed(() => store.cartSubtotal.value);
const taxRate = 0.08;
const tax = computed(() => subtotal.value * taxRate);
const shipping = computed(() => subtotal.value >= 100 ? 0 : 9.99);
const total = computed(() => subtotal.value + tax.value + shipping.value);

onMounted(() => {
  // Pre-fill with user data if available
  if (store.user.value) {
    formData.value.full_name = store.user.value.name || '';
    formData.value.email = store.user.value.email || '';
  }
});

const validateForm = () => {
  errors.value = {};

  if (!formData.value.full_name.trim()) {
    errors.value.full_name = 'Full name is required';
  }

  if (!formData.value.email.trim()) {
    errors.value.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    errors.value.email = 'Please enter a valid email';
  }

  if (!formData.value.phone.trim()) {
    errors.value.phone = 'Phone number is required';
  }

  if (!formData.value.address.trim()) {
    errors.value.address = 'Address is required';
  }

  if (!formData.value.city.trim()) {
    errors.value.city = 'City is required';
  }

  if (!formData.value.zip_code.trim()) {
    errors.value.zip_code = 'ZIP code is required';
  }

  if (!formData.value.card_number.trim()) {
    errors.value.card_number = 'Card number is required';
  } else if (formData.value.card_number.length < 16) {
    errors.value.card_number = 'Invalid card number';
  }

  if (!formData.value.card_name.trim()) {
    errors.value.card_name = 'Name on card is required';
  }

  if (!formData.value.card_expiry.trim()) {
    errors.value.card_expiry = 'Expiry date is required';
  }

  if (!formData.value.card_cvc.trim()) {
    errors.value.card_cvc = 'CVC is required';
  } else if (formData.value.card_cvc.length < 3) {
    errors.value.card_cvc = 'Invalid CVC';
  }

  return Object.keys(errors.value).length === 0;
};

const handleCheckout = async () => {
  if (cart.value.length === 0) {
    store.setAlert('Your cart is empty', 'error');
    router.push('/');
    return;
  }

  if (!validateForm()) {
    return;
  }

  processing.value = true;
  try {
    const items = cart.value.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
    }));

    // Backend expects: customer_name, phone, email, address, items
    const res = await api.post('/orders', {
      customer_name: formData.value.full_name,
      phone: formData.value.phone,
      email: formData.value.email || null,
      address: formData.value.address,
      items,
    });
    if (res.success) {
      // Clear cart (use store actions so state + error handling stays consistent)
      for (const item of cart.value) {
        if (!item?.id) {
          throw new Error('Cart item id missing; cannot remove item from cart.');
        }
        await store.removeFromCart(item.id);
      }
      await store.fetchCart();


      store.setAlert('Order placed successfully! Thank you for shopping.', 'success');
      router.push('/');
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to place order', 'error');
  } finally {
    processing.value = false;
  }
};

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(val);
};

const formatCardNumber = (value: string) => {
  const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
  const matches = v.match(/\d{4,16}/g);
  const match = (matches && matches[0]) || '';
  const parts = [];
  for (let i = 0, len = match.length; i < len; i += 4) {
    parts.push(match.substring(i, i + 4));
  }
  if (parts.length) {
    return parts.join(' ');
  } else {
    return v;
  }
};

const formatExpiry = (value: string) => {
  const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
  if (v.length >= 2) {
    return v.substring(0, 2) + '/' + v.substring(2, 4);
  }
  return v;
};
</script>

<template>
  <div class="checkout-wrapper">
    <!-- Header -->
    <div class="checkout-header">
      <h1>Checkout</h1>
      <p>Complete your order securely</p>
    </div>

    <div v-if="cart.length === 0" class="empty-cart">
      <ShoppingCart :size="48" class="empty-icon" />
      <h3>Your cart is empty</h3>
      <router-link to="/" class="btn btn-primary">Start Shopping</router-link>
    </div>

    <div v-else class="checkout-layout">
      <!-- Left Column: Forms -->
      <div class="checkout-forms">
        <!-- Shipping Information -->
        <div class="form-section card">
          <div class="section-header">
            <MapPin :size="20" />
            <h2>Shipping Information</h2>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" v-model="formData.full_name" :class="{ 'input-error': errors.full_name }"
                @input="errors.full_name = ''" placeholder="John Doe" />
              <span v-if="errors.full_name" class="error-text">{{ errors.full_name }}</span>
            </div>

            <div class="form-group">
              <label>Email</label>
              <input type="email" v-model="formData.email" :class="{ 'input-error': errors.email }"
                @input="errors.email = ''" placeholder="john@example.com" />
              <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label>Phone</label>
              <input type="tel" v-model="formData.phone" :class="{ 'input-error': errors.phone }"
                @input="errors.phone = ''" placeholder="+1 (555) 000-0000" />
              <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
            </div>

            <div class="form-group full-width">
              <label>Address</label>
              <input type="text" v-model="formData.address" :class="{ 'input-error': errors.address }"
                @input="errors.address = ''" placeholder="123 Main Street" />
              <span v-if="errors.address" class="error-text">{{ errors.address }}</span>
            </div>

            <div class="form-group">
              <label>City</label>
              <input type="text" v-model="formData.city" :class="{ 'input-error': errors.city }"
                @input="errors.city = ''" placeholder="New York" />
              <span v-if="errors.city" class="error-text">{{ errors.city }}</span>
            </div>

            <div class="form-group">
              <label>State</label>
              <input type="text" v-model="formData.state" placeholder="NY" />
            </div>

            <div class="form-group">
              <label>ZIP Code</label>
              <input type="text" v-model="formData.zip_code" :class="{ 'input-error': errors.zip_code }"
                @input="errors.zip_code = ''" placeholder="10001" />
              <span v-if="errors.zip_code" class="error-text">{{ errors.zip_code }}</span>
            </div>

            <div class="form-group">
              <label>Country</label>
              <select v-model="formData.country">
                <option>United States</option>
                <option>Canada</option>
                <option>United Kingdom</option>
              </select>
            </div>
          </div>

          <label class="checkbox-label">
            <input type="checkbox" v-model="formData.save_address" />
            <span>Save this address for future orders</span>
          </label>
        </div>

        <!-- Payment Information -->
        <div class="form-section card">
          <div class="section-header">
            <CreditCard :size="20" />
            <h2>Payment Information</h2>
          </div>

          <div class="form-grid">
            <div class="form-group full-width">
              <label>Card Number</label>
              <input type="text" v-model="formData.card_number" :class="{ 'input-error': errors.card_number }"
                @input="formData.card_number = formatCardNumber(formData.card_number); errors.card_number = ''"
                placeholder="1234 5678 9012 3456" maxlength="19" />
              <span v-if="errors.card_number" class="error-text">{{ errors.card_number }}</span>
            </div>

            <div class="form-group full-width">
              <label>Name on Card</label>
              <input type="text" v-model="formData.card_name" :class="{ 'input-error': errors.card_name }"
                @input="errors.card_name = ''" placeholder="JOHN DOE" />
              <span v-if="errors.card_name" class="error-text">{{ errors.card_name }}</span>
            </div>

            <div class="form-group">
              <label>Expiry Date</label>
              <input type="text" v-model="formData.card_expiry" :class="{ 'input-error': errors.card_expiry }"
                @input="formData.card_expiry = formatExpiry(formData.card_expiry); errors.card_expiry = ''"
                placeholder="MM/YY" maxlength="5" />
              <span v-if="errors.card_expiry" class="error-text">{{ errors.card_expiry }}</span>
            </div>

            <div class="form-group">
              <label>CVC</label>
              <input type="text" v-model="formData.card_cvc" :class="{ 'input-error': errors.card_cvc }"
                @input="errors.card_cvc = ''" placeholder="123" maxlength="4" />
              <span v-if="errors.card_cvc" class="error-text">{{ errors.card_cvc }}</span>
            </div>
          </div>

          <div class="secure-notice">
            <Lock :size="14" />
            <span>Your payment information is encrypted and secure</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Summary -->
      <div class="checkout-summary">
        <div class="summary-card card">
          <h2>Order Summary</h2>

          <div class="order-items">
            <div v-for="item in cart" :key="item.id" class="order-item">
              <img :src="item.product?.image_url" :alt="item.product?.name" class="order-item-img"
                @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=100&auto=format&fit=crop'" />
              <div class="order-item-info">
                <h4>{{ item.product?.name }}</h4>
                <span class="order-item-qty">Qty: {{ item.quantity }}</span>
              </div>
              <span class="order-item-price">{{ formatCurrency((item.product?.price || 0) * item.quantity) }}</span>
            </div>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row">
            <span>Subtotal</span>
            <span>{{ formatCurrency(subtotal) }}</span>
          </div>

          <div class="summary-row">
            <span>Shipping</span>
            <span>{{ shipping === 0 ? 'Free' : formatCurrency(shipping) }}</span>
          </div>

          <div class="summary-row">
            <span>Tax (8%)</span>
            <span>{{ formatCurrency(tax) }}</span>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row total">
            <span>Total</span>
            <span>{{ formatCurrency(total) }}</span>
          </div>

          <button class="btn btn-primary checkout-btn" @click="handleCheckout" :disabled="processing">
            <Lock :size="16" v-if="!processing" />
            <span v-if="processing">Processing...</span>
            <span v-else>Place Order</span>
          </button>

          <div class="trust-badges">
            <div class="trust-badge">
              <Shield :size="14" />
              <span>Secure Checkout</span>
            </div>
            <div class="trust-badge">
              <Truck :size="14" />
              <span>Free Shipping</span>
            </div>
            <div class="trust-badge">
              <Check :size="14" />
              <span>30-Day Returns</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.checkout-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px;
  font-family: var(--font-body);
  background-color: #05070c;
  color: #f3f4f6;
  min-height: 80vh;
}

.checkout-header {
  margin-bottom: 40px;
}

.checkout-header h1 {
  font-family: var(--font-display);
  font-size: 2.2rem;
  font-weight: 800;
  color: white;
  margin-bottom: 8px;
}

.checkout-header p {
  font-size: 0.9rem;
  color: #9ca3af;
}

.empty-cart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  gap: 16px;
  color: #6b7280;
}

.empty-icon {
  color: #1f2937;
}

.checkout-layout {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 32px;
}

@media (max-width: 1024px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
}

/* Form Sections */
.checkout-forms {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-section {
  padding: 32px;
  background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.section-header h2 {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: span 2;
}

@media (max-width: 640px) {
  .form-group.full-width {
    grid-column: span 1;
  }
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.form-group input,
.form-group select {
  background: #0b0f19;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  padding: 12px 16px;
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
  transition: border-color var(--transition-fast);
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
}

.input-error {
  border-color: #ef4444 !important;
}

.input-error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
}

.error-text {
  color: #f87171;
  font-size: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #9ca3af;
  cursor: pointer;
  margin-top: 16px;
}

.checkbox-label input {
  width: 16px;
  height: 16px;
  accent-color: #3b82f6;
}

.secure-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  padding: 12px 16px;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: var(--radius-sm);
  font-size: 0.8rem;
  color: #10b981;
}

/* Summary Card */
.checkout-summary {
  position: sticky;
  top: 40px;
  height: fit-content;
}

.summary-card {
  padding: 32px;
  background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.summary-card h2 {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  margin-bottom: 24px;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.order-item-img {
  width: 60px;
  height: 60px;
  border-radius: var(--radius-sm);
  object-fit: contain;
  background: #0b0f19;
  padding: 8px;
}

.order-item-info {
  flex: 1;
}

.order-item-info h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.order-item-qty {
  font-size: 0.75rem;
  color: #6b7280;
}

.order-item-price {
  font-family: var(--font-display);
  font-weight: 700;
  color: white;
}

.summary-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
  margin: 16px 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #9ca3af;
  margin-bottom: 12px;
}

.summary-row.total {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 800;
  color: white;
  margin-top: 16px;
}

.checkout-btn {
  width: 100%;
  padding: 14px;
  margin-top: 24px;
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.trust-badges {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.trust-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #6b7280;
}

.trust-badge svg {
  color: #10b981;
}

@media (max-width: 768px) {
  .checkout-wrapper {
    padding: 24px;
  }

  .form-section,
  .summary-card {
    padding: 24px;
  }
}
</style>
