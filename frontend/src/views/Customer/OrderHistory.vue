<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import { 
  ShoppingBag, 
  Calendar, 
  Package, 
  Truck, 
  CheckCircle,
  XCircle,
  Clock,
  Eye,
  Download
} from 'lucide-vue-next';

const loading = ref(true);
const orders = ref<any[]>([]);
const selectedOrder = ref<any>(null);
const showDetailModal = ref(false);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const res = await api.getOrders();
    if (res.success) {
      orders.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }
  } catch (e) {
    console.error('Failed to load orders', e);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchOrders();
});

const getStatusInfo = (status: string) => {
  const statusMap: Record<string, { label: string; icon: any; class: string }> = {
    'Pending': { label: 'Pending', icon: Clock, class: 'status-pending' },
    'Processing': { label: 'Processing', icon: Package, class: 'status-processing' },
    'Shipped': { label: 'Shipped', icon: Truck, class: 'status-shipped' },
    'Delivered': { label: 'Delivered', icon: CheckCircle, class: 'status-delivered' },
    'Cancelled': { label: 'Cancelled', icon: XCircle, class: 'status-cancelled' },
    'Completed': { label: 'Completed', icon: CheckCircle, class: 'status-completed' }
  };
  return statusMap[status] || { label: status, icon: Package, class: 'status-pending' };
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(val);
};

const viewOrderDetail = (order: any) => {
  selectedOrder.value = order;
  showDetailModal.value = true;
};

const closeModal = () => {
  selectedOrder.value = null;
  showDetailModal.value = false;
};

const filteredOrders = computed(() => {
  return orders.value.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
});
</script>

<template>
  <div class="orders-wrapper">
    <!-- Header -->
    <div class="orders-header">
      <div>
        <h1>Order History</h1>
        <p>Track and manage your orders</p>
      </div>
      <button class="btn btn-secondary" @click="fetchOrders" :disabled="loading">
        <Download :size="16" />
        Refresh
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="loading-state">
      <div class="loader"></div>
      <p>Loading your orders...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="orders.length === 0" class="empty-state">
      <ShoppingBag :size="48" class="empty-icon" />
      <h3>No orders yet</h3>
      <p>Start shopping to see your order history here</p>
      <router-link to="/" class="btn btn-primary">Start Shopping</router-link>
    </div>

    <!-- Orders list -->
    <div v-else class="orders-list">
      <div v-for="order in filteredOrders" :key="order.id" class="order-card card">
        <div class="order-card-header">
          <div class="order-info">
            <span class="order-number">Order #{{ order.id }}</span>
            <span class="order-date">{{ formatDate(order.created_at) }}</span>
          </div>
          <div :class="['order-status', getStatusInfo(order.status).class]">
            <component :is="getStatusInfo(order.status).icon" :size="16" />
            <span>{{ getStatusInfo(order.status).label }}</span>
          </div>
        </div>

        <div class="order-items-preview">
          <div v-for="item in (order.items || []).slice(0, 3)" :key="item.id" class="order-item-preview">
            <img 
              :src="item.product?.image_url" 
              :alt="item.product?.name"
              class="item-preview-img"
              @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=80&auto=format&fit=crop'"
            />
          </div>
          <div v-if="order.items?.length > 3" class="more-items">
            +{{ order.items.length - 3 }}
          </div>
        </div>

        <div class="order-card-footer">
          <span class="order-total">{{ formatCurrency(order.total || 0) }}</span>
          <button class="btn btn-outline btn-sm" @click="viewOrderDetail(order)">
            <Eye :size="14" />
            View Details
          </button>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <Transition name="modal">
      <div v-if="showDetailModal && selectedOrder" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Order #{{ selectedOrder.id }}</h2>
            <button class="modal-close" @click="closeModal">&times;</button>
          </div>

          <div class="modal-body">
            <div class="detail-section">
              <h3>Order Information</h3>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="label">Order Date</span>
                  <span class="value">{{ formatDate(selectedOrder.created_at) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Status</span>
                  <div :class="['order-status', getStatusInfo(selectedOrder.status).class]">
                    <component :is="getStatusInfo(selectedOrder.status).icon" :size="14" />
                    <span>{{ getStatusInfo(selectedOrder.status).label }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h3>Order Items</h3>
              <div class="order-items-list">
                <div v-for="item in (selectedOrder.items || [])" :key="item.id" class="order-item-detail">
                  <img 
                    :src="item.product?.image_url" 
                    :alt="item.product?.name"
                    class="order-item-img"
                    @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=80&auto=format&fit=crop'"
                  />
                  <div class="order-item-details">
                    <h4>{{ item.product?.name }}</h4>
                    <span class="item-qty">Qty: {{ item.quantity }}</span>
                  </div>
                  <span class="item-price">{{ formatCurrency((item.product?.price || 0) * item.quantity) }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h3>Order Summary</h3>
              <div class="summary-row">
                <span>Subtotal</span>
                <span>{{ formatCurrency(selectedOrder.total || 0) }}</span>
              </div>
              <div class="summary-row">
                <span>Shipping</span>
                <span>Free</span>
              </div>
              <div class="summary-row">
                <span>Tax</span>
                <span>{{ formatCurrency((selectedOrder.total || 0) * 0.08) }}</span>
              </div>
              <div class="summary-divider"></div>
              <div class="summary-row total">
                <span>Total</span>
                <span>{{ formatCurrency((selectedOrder.total || 0) * 1.08) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.orders-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px;
  font-family: var(--font-body);
  background-color: #05070c;
  color: #f3f4f6;
  min-height: 80vh;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
}

.orders-header h1 {
  font-family: var(--font-display);
  font-size: 2.2rem;
  font-weight: 800;
  color: white;
  margin-bottom: 8px;
}

.orders-header p {
  font-size: 0.9rem;
  color: #9ca3af;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  gap: 16px;
  color: #6b7280;
}

.loader {
  width: 44px;
  height: 44px;
  border: 4px solid rgba(255, 255, 255, 0.05);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  color: #1f2937;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 24px;
}

.order-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.order-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.order-number {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 700;
  color: white;
}

.order-date {
  font-size: 0.8rem;
  color: #6b7280;
}

.order-status {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-pending {
  background: rgba(251, 191, 36, 0.1);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.2);
}

.status-processing {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.status-shipped {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
  border: 1px solid rgba(99, 102, 241, 0.2);
}

.status-delivered,
.status-completed {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.status-cancelled {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.order-items-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.order-item-preview {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  background: #0b0f19;
  padding: 8px;
}

.item-preview-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.more-items {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  color: #6b7280;
}

.order-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-total {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 24px;
}

.modal-content {
  background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-xl);
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.modal-header h2 {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

.modal-close {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.modal-close:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.modal-body {
  padding: 24px;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-section h3 {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 700;
  color: white;
  margin-bottom: 16px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item .label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-item .value {
  font-size: 0.9rem;
  color: white;
  font-weight: 600;
}

.order-items-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-item-detail {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.02);
  border-radius: var(--radius-sm);
}

.order-item-img {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  object-fit: contain;
  background: #0b0f19;
  padding: 8px;
}

.order-item-details {
  flex: 1;
}

.order-item-details h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.item-qty {
  font-size: 0.75rem;
  color: #6b7280;
}

.item-price {
  font-family: var(--font-display);
  font-weight: 700;
  color: white;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #9ca3af;
  margin-bottom: 8px;
}

.summary-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
  margin: 12px 0;
}

.summary-row.total {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 800;
  color: white;
  margin-top: 8px;
}

/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .orders-wrapper {
    padding: 24px;
  }

  .orders-header {
    flex-direction: column;
    gap: 16px;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
