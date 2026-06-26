<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { api } from '../../services/api';
import { store } from '../../store';
import {
  Plus,
  Search,
  ChevronDown,
  Eye,
  Truck,
  XCircle,
  CheckCircle,
  Clock,
  ChevronLeft,
  ChevronRight,
  Download,
  Filter,
  Calendar,
  DollarSign,
  User as UserIcon,
  X,
  Package
} from 'lucide-vue-next';

// Component state
const orders = ref<any[]>([]);
const search = ref('');
const statusFilter = ref('all');
const loading = ref(true);

// Pagination
const currentPage = ref(1);
const itemsPerPage = 8;

// Modal state
const isModalOpen = ref(false);
const selectedOrder = ref<any | null>(null);

// Stats
const todayRevenue = ref(0);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const res = await api.getOrders();
    if (res.success) {
      orders.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
      // Calculate today's revenue
      const today = new Date().toISOString().split('T')[0];
      todayRevenue.value = orders.value
        .filter(o => o.created_at && o.created_at.startsWith(today))
        .reduce((sum, o) => sum + (o.total || 0), 0);
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to fetch orders', 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchOrders();
});

// Filtering and Searching
const filteredOrders = computed(() => {
  return orders.value.filter(o => {
    const matchesSearch =
      o.id.toString().includes(search.value) ||
      (o.customer_name && o.customer_name.toLowerCase().includes(search.value.toLowerCase())) ||
      (o.customer_email && o.customer_email.toLowerCase().includes(search.value.toLowerCase()));

    const matchesStatus =
      statusFilter.value === 'all' ||
      o.status === statusFilter.value;

    return matchesSearch && matchesStatus;
  });
});

// Paginated orders
const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredOrders.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / itemsPerPage) || 1;
});

// Computed Metrics
const pendingCount = computed(() => {
  return orders.value.filter(o => o.status === 'pending').length;
});

const processingCount = computed(() => {
  return orders.value.filter(o => o.status === 'processing').length;
});

const completedCount = computed(() => {
  return orders.value.filter(o => o.status === 'completed').length;
});

const cancelledCount = computed(() => {
  return orders.value.filter(o => o.status === 'cancelled').length;
});

const getStatusBadge = (status: string) => {
  const statusMap: Record<string, { label: string; class: string; icon: any }> = {
    pending: { label: 'Pending', class: 'badge-warning', icon: Clock },
    processing: { label: 'Processing', class: 'badge-info', icon: Package },
    completed: { label: 'Completed', class: 'badge-success', icon: CheckCircle },
    cancelled: { label: 'Cancelled', class: 'badge-danger', icon: XCircle }
  };
  return statusMap[status] || { label: status, class: 'badge-info', icon: Clock };
};

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(val);
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

// Modal Operations
const openViewModal = (order: any) => {
  selectedOrder.value = order;
  isModalOpen.value = true;
};

const updateOrderStatus = async (orderId: number, newStatus: string) => {
  try {
    const res = await api.updateOrderStatus(orderId, newStatus);
    if (res.success) {
      store.setAlert('Order status updated successfully', 'success');
      await fetchOrders();
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to update order status', 'error');
  }
};

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const handleExport = () => {
  store.setAlert('Export functionality coming soon', 'info');
};

const handleCreateOrder = () => {
  store.setAlert('Create order functionality coming soon', 'info');
};
</script>

<template>
  <div class="orders-wrapper">
    <div class="orders-header">
      <div>
        <h1 class="page-title">Order Management</h1>
        <p class="page-subtitle">Track and manage customer orders</p>
      </div>

      <div class="header-actions">
        <button class="btn btn-outline btn-sm" @click="handleExport">
          <Download :size="16" />
          Export
        </button>
        <button class="btn btn-primary" @click="handleCreateOrder">
          <Plus :size="16" />
          Create Order
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid animate-fade-in">
      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Today's Revenue</span>
          <span class="stat-value">{{ formatCurrency(todayRevenue) }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #2563eb; background-color: rgba(37, 99, 235, 0.1);">
          <DollarSign :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Pending</span>
          <span class="stat-value">{{ pendingCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #f59e0b; background-color: rgba(245, 158, 11, 0.1);">
          <Clock :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Processing</span>
          <span class="stat-value">{{ processingCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #3b82f6; background-color: rgba(59, 130, 246, 0.1);">
          <Package :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Completed</span>
          <span class="stat-value">{{ completedCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #10b981; background-color: rgba(16, 185, 129, 0.1);">
          <CheckCircle :size="20" />
        </div>
      </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="status-tabs animate-fade-in">
      <button 
        v-for="status in ['all', 'pending', 'processing', 'completed', 'cancelled']"
        :key="status"
        :class="['status-tab', { active: statusFilter === status }]"
        @click="statusFilter = status; currentPage = 1"
      >
        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
        <span v-if="status !== 'all'" class="tab-count">
          {{ status === 'pending' ? pendingCount : 
             status === 'processing' ? processingCount :
             status === 'completed' ? completedCount : cancelledCount }}
        </span>
      </button>
    </div>

    <!-- Filters panel -->
    <div class="card filters-card animate-fade-in">
      <div class="filters-row">
        <div class="search-input-wrapper">
          <Search :size="18" class="search-icon-inside" />
          <input type="text" placeholder="Search by Order ID, Customer Name, or Email..." class="form-input search-input"
            v-model="search" @input="currentPage = 1" />
        </div>

        <div class="filter-dropdown-wrapper">
          <label>Date Range:</label>
          <div class="select-wrapper">
            <select class="form-input select-input">
              <option value="all">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
            </select>
            <ChevronDown :size="16" class="select-chevron" />
          </div>
        </div>
      </div>
    </div>

    <!-- Table content -->
    <div class="card table-card animate-fade-in-up">
      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Loading orders...</p>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="empty-state">
        <Package :size="48" style="color: var(--text-tertiary); margin-bottom: 16px;" />
        <p>No orders found matching your filters.</p>
        <button class="btn btn-secondary btn-sm" @click="search = ''; statusFilter = 'all'">
          Clear Filters
        </button>
      </div>

      <template v-else>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items Count</th>
                <th>Total Price</th>
                <th>Order Date</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in paginatedOrders" :key="order.id">
                <td style="font-weight: 700;">#ORD-{{ order.id }}</td>
                <td>
                  <div class="customer-cell">
                    <span class="customer-avatar-initials">
                      {{ order.customer_name ? order.customer_name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2) : 'CU' }}
                    </span>
                    <div class="customer-info">
                      <span class="customer-name">{{ order.customer_name || 'Unknown' }}</span>
                      <span class="customer-email">{{ order.customer_email || '' }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="items-count-badge">{{ order.items_count || 0 }} items</span>
                </td>
                <td style="font-weight: 700; color: var(--accent-primary);">
                  {{ formatCurrency(order.total || 0) }}
                </td>
                <td style="color: var(--text-secondary);">
                  {{ formatDate(order.created_at) }}
                </td>
                <td>
                  <span :class="['badge', getStatusBadge(order.status).class]">
                    <component :is="getStatusBadge(order.status).icon" :size="12" style="margin-right: 4px;" />
                    {{ getStatusBadge(order.status).label }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="actions-group">
                    <button class="action-btn view-btn" @click="openViewModal(order)" title="View Details">
                      <Eye :size="16" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Pager -->
        <div class="pagination-row">
          <span class="pagination-info">
            Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
            {{ Math.min(currentPage * itemsPerPage, filteredOrders.length) }} of
            {{ filteredOrders.length }} orders
          </span>

          <div class="pager-buttons">
            <button class="pager-btn" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
              <ChevronLeft :size="16" />
            </button>

            <button v-for="p in totalPages" :key="p" :class="['pager-btn', { active: currentPage === p }]"
              @click="changePage(p)">
              {{ p }}
            </button>

            <button class="pager-btn" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
              <ChevronRight :size="16" />
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- ORDER DETAILS MODAL -->
    <div v-if="isModalOpen && selectedOrder" class="modal-overlay">
      <div class="modal-card modal-large">
        <div class="modal-header">
          <h3>Order Details #ORD-{{ selectedOrder.id }}</h3>
          <button class="close-btn" @click="isModalOpen = false">
            <X :size="20" />
          </button>
        </div>

        <div class="modal-body">
          <div class="order-details-grid">
            <!-- Customer Info -->
            <div class="detail-section">
              <h4>Customer Information</h4>
              <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value">{{ selectedOrder.customer_name || 'N/A' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ selectedOrder.customer_email || 'N/A' }}</span>
              </div>
            </div>

            <!-- Order Info -->
            <div class="detail-section">
              <h4>Order Information</h4>
              <div class="detail-row">
                <span class="detail-label">Order Date:</span>
                <span class="detail-value">{{ formatDate(selectedOrder.created_at) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value" style="font-weight: 700; color: var(--accent-primary);">
                  {{ formatCurrency(selectedOrder.total || 0) }}
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Items:</span>
                <span class="detail-value">{{ selectedOrder.items_count || 0 }} items</span>
              </div>
            </div>

            <!-- Status Update -->
            <div class="detail-section full-width">
              <h4>Update Status</h4>
              <div class="status-actions">
                <button 
                  v-for="status in ['pending', 'processing', 'completed', 'cancelled']"
                  :key="status"
                  :class="['status-action-btn', { active: selectedOrder.status === status }]"
                  @click="updateOrderStatus(selectedOrder.id, status)"
                >
                  <component :is="getStatusBadge(status).icon" :size="16" />
                  {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="isModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.orders-wrapper {
  display: flex;
  flex-direction: column;
  gap: 28px;
  font-family: var(--font-body);
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-title {
  font-family: var(--font-display);
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

/* Status Tabs */
.status-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.status-tab {
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  padding: 10px 20px;
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-tab:hover {
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
}

.status-tab.active {
  background-color: #2563eb;
  color: white;
  border-color: #3b82f6;
}

.tab-count {
  background-color: rgba(255, 255, 255, 0.2);
  padding: 2px 8px;
  border-radius: 50px;
  font-size: 0.75rem;
}

/* Filters Card */
.filters-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  flex-wrap: wrap;
}

.search-input-wrapper {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-icon-inside {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-tertiary);
}

.search-input {
  padding-left: 44px;
}

.filter-dropdown-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}

.filter-dropdown-wrapper label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
  white-space: nowrap;
}

.select-wrapper {
  position: relative;
  width: 180px;
}

.select-input {
  appearance: none;
  padding-right: 36px;
  cursor: pointer;
}

.select-chevron {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
  pointer-events: none;
}

/* Table Card */
.customer-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.customer-avatar-initials {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #dbeafe, #eff6ff);
  color: #2563eb;
  font-weight: 700;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #bfdbfe;
  flex-shrink: 0;
}

.customer-info {
  display: flex;
  flex-direction: column;
}

.customer-name {
  font-weight: 600;
  color: var(--text-primary);
}

.customer-email {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.items-count-badge {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-secondary);
  background-color: var(--bg-tertiary);
  padding: 4px 10px;
  border-radius: 50px;
}

.actions-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-btn {
  background: none;
  border: 1px solid var(--border-color);
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.action-btn:hover {
  background-color: var(--bg-tertiary);
}

.view-btn:hover {
  color: var(--accent-secondary);
  border-color: rgba(59, 130, 246, 0.3);
}

.pagination-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding: 0 4px;
}

.pagination-info {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.pager-buttons {
  display: flex;
  gap: 6px;
}

.pager-btn {
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all var(--transition-fast);
}

.pager-btn:hover:not(:disabled) {
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
}

.pager-btn.active {
  background-color: #2563eb;
  color: white;
  border-color: #3b82f6;
}

.pager-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.modal-card {
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  width: 580px;
  max-width: 100%;
  max-height: calc(100vh - 48px);
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-lg);
  animation: fadeInUp var(--transition-normal);
  overflow: hidden;
}

.modal-large {
  width: 700px;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-family: var(--font-display);
  font-size: 1.2rem;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.order-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.detail-section {
  background-color: var(--bg-tertiary);
  padding: 16px;
  border-radius: var(--radius-md);
}

.detail-section.full-width {
  grid-column: span 2;
}

.detail-section h4 {
  font-family: var(--font-display);
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: 12px;
  color: var(--text-primary);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 0.85rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.detail-value {
  font-size: 0.85rem;
  color: var(--text-primary);
  font-weight: 600;
}

.status-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.status-action-btn {
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  padding: 8px 16px;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-action-btn:hover {
  background-color: var(--bg-tertiary);
}

.status-action-btn.active {
  background-color: #2563eb;
  color: white;
  border-color: #3b82f6;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background-color: var(--bg-tertiary);
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  gap: 16px;
  color: var(--text-secondary);
}

.loading-state p,
.empty-state p {
  font-size: 0.9rem;
  margin: 0;
}

.loader {
  width: 48px;
  height: 48px;
  border: 4px solid var(--border-color);
  border-top-color: var(--accent-secondary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
