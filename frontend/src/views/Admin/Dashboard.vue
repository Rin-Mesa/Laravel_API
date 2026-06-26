<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { api } from '../../services/api';
import { store } from '../../store';
import { 
  TrendingUp, 
  ShoppingBag, 
  AlertTriangle, 
  Users, 
  ArrowUpRight, 
  ArrowDownRight,
  MapPin,
  MoreVertical,
  RefreshCw,
  DollarSign,
  BarChart3,
  Eye
} from 'lucide-vue-next';

interface DashboardData {
  total_revenue: number;
  new_orders: number;
  out_of_stock: number;
  total_users: number;
  active_users: number;
  recent_orders: any[];
  sales_chart: any[];
  sales_by_category?: any[];
}

const stats = ref<DashboardData | null>(null);
const loading = ref(true);
const chartTimeframe = ref<'week' | 'month'>('month');

const fetchDashboardStats = async () => {
  loading.value = true;
  try {
    const res = await api.getDashboardStats();
    if (res.success) {
      stats.value = res.data;
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to fetch dashboard statistics', 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchDashboardStats();
});

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(val);
};

const formatNumber = (val: number) => {
  return new Intl.NumberFormat('en-US').format(val);
};

// SVG Chart Path Generator
const chartPath = computed(() => {
  if (!stats.value || !stats.value.sales_chart) return { path: '', points: [] };
  const data = stats.value.sales_chart;
  const width = 500;
  const height = 150;
  const padding = 20;
  
  const minX = 0;
  const maxX = data.length - 1;
  const minY = 0;
  const maxY = Math.max(...data.map(d => d.revenue)) * 1.1;

  const points = data.map((d, index) => {
    const x = padding + (index / maxX) * (width - padding * 2);
    const y = height - padding - ((d.revenue - minY) / (maxY - minY)) * (height - padding * 2);
    return { x, y, value: d.revenue };
  });

  // Generate cubic bezier curve path
  if (points.length === 0) return { path: '', points: [] };
  let path = `M ${points[0]!.x} ${points[0]!.y}`;
  for (let i = 0; i < points.length - 1; i++) {
    const p0 = points[i]!;
    const p1 = points[i + 1]!;
    const cpX1 = p0.x + (p1.x - p0.x) / 3;
    const cpY1 = p0.y;
    const cpX2 = p0.x + 2 * (p1.x - p0.x) / 3;
    const cpY2 = p1.y;
    path += ` C ${cpX1} ${cpY1}, ${cpX2} ${cpY2}, ${p1.x} ${p1.y}`;
  }
  return { path, points };
});

// Category Bar Chart
const categoryChartData = computed(() => {
  if (!stats.value || !stats.value.sales_by_category) return [];
  const data = stats.value.sales_by_category;
  const maxValue = Math.max(...data.map(d => d.revenue || 0));
  return data.map(d => ({
    ...d,
    percentage: maxValue > 0 ? (d.revenue / maxValue) * 100 : 0
  }));
});

const getOrderStatusBadge = (status: string) => {
  const statusMap: Record<string, { label: string; class: string }> = {
    'Delivered': { label: 'Delivered', class: 'badge-success' },
    'Processing': { label: 'Processing', class: 'badge-info' },
    'Pending': { label: 'Pending', class: 'badge-warning' },
    'Cancelled': { label: 'Cancelled', class: 'badge-danger' }
  };
  return statusMap[status] || { label: status, class: 'badge-info' };
};
</script>

<template>
  <div class="dashboard-wrapper">
    <div class="dashboard-header">
      <div>
        <h1 class="page-title">Dashboard Overview</h1>
        <p class="page-subtitle">Real-time statistics and store activities</p>
      </div>
      <button class="btn btn-secondary btn-sm" @click="fetchDashboardStats" :disabled="loading">
        <RefreshCw :size="14" :class="{ 'spin-icon': loading }" />
        Refresh
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="loading && !stats" class="loading-container">
      <div class="loader"></div>
      <p>Retrieving statistics...</p>
    </div>

    <template v-else-if="stats">
      <!-- Grid Cards -->
      <div class="stats-grid animate-fade-in-up">
        <!-- Revenue Card -->
        <div class="card stat-card">
          <div class="stat-info">
            <span class="stat-label">Total Revenue</span>
            <span class="stat-value">{{ formatCurrency(stats.total_revenue) }}</span>
            <span class="stat-change up">
              <ArrowUpRight :size="14" />
              +12.5% vs last month
            </span>
          </div>
          <div class="stat-icon-wrapper">
            <DollarSign :size="20" />
          </div>
        </div>

        <!-- Orders Card -->
        <div class="card stat-card">
          <div class="stat-info">
            <span class="stat-label">New Orders</span>
            <span class="stat-value">{{ formatNumber(stats.new_orders) }}</span>
            <span class="stat-change up">
              <ArrowUpRight :size="14" />
              +8.2% vs last week
            </span>
          </div>
          <div class="stat-icon-wrapper">
            <ShoppingBag :size="20" />
          </div>
        </div>

        <!-- Out of Stock Card -->
        <div class="card stat-card">
          <div class="stat-info">
            <span class="stat-label">Out of Stock Items</span>
            <span class="stat-value">{{ stats.out_of_stock }}</span>
            <span class="badge badge-danger" style="margin-top: 6px;">
              High Priority
            </span>
          </div>
          <div class="stat-icon-wrapper" style="color: #ef4444; background-color: rgba(239, 68, 68, 0.1);">
            <AlertTriangle :size="20" />
          </div>
        </div>

        <!-- Users Card -->
        <div class="card stat-card">
          <div class="stat-info">
            <span class="stat-label">Total Users</span>
            <span class="stat-value">{{ formatNumber(stats.total_users) }}</span>
            <span class="badge badge-info" style="margin-top: 6px;">
              Active Now: {{ stats.active_users }}
            </span>
          </div>
          <div class="stat-icon-wrapper" style="color: #3b82f6; background-color: rgba(59, 130, 246, 0.1);">
            <Users :size="20" />
          </div>
        </div>
      </div>

      <!-- Charts & Visual elements -->
      <div class="visuals-grid animate-fade-in-up">
        <!-- Sales line chart -->
        <div class="card chart-card">
          <div class="card-header">
            <div>
              <h3>Sales Overview</h3>
              <p>Revenue performance across the last 7 months</p>
            </div>
            <div class="chart-filters">
              <span :class="['filter-btn', { active: chartTimeframe === 'month' }]" @click="chartTimeframe = 'month'">Month</span>
              <span :class="['filter-btn', { active: chartTimeframe === 'week' }]" @click="chartTimeframe = 'week'">Week</span>
            </div>
          </div>
          <div class="chart-content">
            <svg viewBox="0 0 500 160" class="line-chart-svg">
              <!-- Grid lines -->
              <line x1="20" y1="20" x2="480" y2="20" stroke="#f1f5f9" stroke-width="1" />
              <line x1="20" y1="56" x2="480" y2="56" stroke="#f1f5f9" stroke-width="1" />
              <line x1="20" y1="93" x2="480" y2="93" stroke="#f1f5f9" stroke-width="1" />
              <line x1="20" y1="130" x2="480" y2="130" stroke="#cbd5e1" stroke-dasharray="3" stroke-width="1" />
              
              <!-- Draw Line Path -->
              <path :d="chartPath.path" fill="none" stroke="#2563eb" stroke-width="3.5" stroke-linecap="round" />
              
              <!-- Hover highlight circle -->
              <circle cx="250" cy="56" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="2" />
              
              <!-- Month labels -->
              <text x="25" y="150" fill="#94a3b8" font-size="10">Jan</text>
              <text x="95" y="150" fill="#94a3b8" font-size="10">Feb</text>
              <text x="165" y="150" fill="#94a3b8" font-size="10">Mar</text>
              <text x="235" y="150" fill="#94a3b8" font-size="10">Apr</text>
              <text x="305" y="150" fill="#94a3b8" font-size="10">May</text>
              <text x="375" y="150" fill="#94a3b8" font-size="10">Jun</text>
              <text x="445" y="150" fill="#94a3b8" font-size="10">Jul</text>
            </svg>
          </div>
        </div>

        <!-- Sales by Category Bar Chart -->
        <div class="card chart-card">
          <div class="card-header">
            <div>
              <h3>Sales by Category</h3>
              <p>Top performing categories</p>
            </div>
            <BarChart3 :size="20" style="color: var(--text-tertiary);" />
          </div>
          <div class="category-chart-content">
            <div v-if="categoryChartData.length > 0" class="category-bars">
              <div v-for="item in categoryChartData.slice(0, 5)" :key="item.category" class="category-bar-row">
                <span class="category-bar-label">{{ item.category }}</span>
                <div class="category-bar-track">
                  <div class="category-bar-fill" :style="{ width: item.percentage + '%' }"></div>
                </div>
                <span class="category-bar-value">{{ formatCurrency(item.revenue) }}</span>
              </div>
            </div>
            <div v-else class="chart-empty-state">
              <BarChart3 :size="32" style="color: var(--text-tertiary); margin-bottom: 8px;" />
              <p style="font-size: 0.8rem; color: var(--text-secondary);">No category data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders Table -->
      <div class="card table-card animate-fade-in-up">
        <div class="card-header table-header">
          <div>
            <h3>Recent Orders</h3>
            <p>Monitoring the latest customer transactions</p>
          </div>
          <router-link to="/admin/orders" class="view-all-link">
            View All Orders &rarr;
          </router-link>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in stats.recent_orders" :key="order.id">
                <td style="font-weight: 700;">#ORD-{{ 2800 + order.id }}</td>
                <td>
                  <div class="customer-cell">
                    <span class="customer-avatar-initials">{{ order.customer_initials }}</span>
                    <span>{{ order.customer_name }}</span>
                  </div>
                </td>
                <td style="color: var(--text-secondary);">{{ order.date }}</td>
                <td style="font-weight: 600;">{{ formatCurrency(order.amount) }}</td>
                <td>
                  <span :class="['badge', getOrderStatusBadge(order.status).class]">
                    {{ getOrderStatusBadge(order.status).label }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <button class="icon-btn-more">
                    <MoreVertical :size="16" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard-wrapper {
  display: flex;
  flex-direction: column;
  gap: 32px;
  font-family: var(--font-body);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
  gap: 16px;
  color: var(--text-secondary);
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

.spin-icon {
  animation: spin 1s linear infinite;
}

.visuals-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}

@media (max-width: 1024px) {
  .visuals-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .dashboard-header button {
    width: 100%;
  }
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.card-header h3 {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
}

.card-header p {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.chart-filters {
  background-color: var(--bg-tertiary);
  padding: 4px;
  border-radius: var(--radius-sm);
  display: flex;
  gap: 4px;
}

.filter-btn {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  color: var(--text-secondary);
}

.filter-btn.active {
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  box-shadow: var(--shadow-sm);
}

.chart-content {
  height: 200px;
  display: flex;
  align-items: flex-end;
}

.line-chart-svg {
  width: 100%;
  height: 100%;
  overflow: visible;
}

/* Map Card */
.map-placeholder {
  background: radial-gradient(circle, rgba(239, 246, 255, 0.5) 0%, rgba(248, 250, 252, 1) 100%),
              url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" opacity="0.05"><path d="M 0,0 L 100,100 M 0,100 L 100,0 M 0,50 L 100,50 M 50,0 L 50,100" stroke="%23000" stroke-width="1"/></svg>');
  border: 1px dashed var(--border-color);
  border-radius: var(--radius-md);
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.map-marker-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 10;
}

.map-pin-icon {
  color: #2563eb;
  filter: drop-shadow(0 4px 8px rgba(37, 99, 235, 0.4));
  animation: bounce 2s infinite ease-in-out;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}

.map-dot-pulse {
  position: absolute;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: rgba(37, 99, 235, 0.15);
  animation: pulseGlow 3s infinite ease-in-out;
}

.pulse-2 {
  width: 120px;
  height: 120px;
  animation-duration: 4.5s;
}

.map-tooltip {
  text-align: center;
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-md);
  margin-top: 8px;
  max-width: 180px;
}

.map-tooltip h4 {
  font-family: var(--font-display);
  font-size: 0.85rem;
  font-weight: 700;
  margin-bottom: 2px;
}

.map-tooltip p {
  font-size: 0.7rem;
  color: var(--text-secondary);
  line-height: 1.2;
}

/* Category Bar Chart */
.category-chart-content {
  height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.category-bars {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 8px 0;
}

.category-bar-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-bar-label {
  width: 100px;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.category-bar-track {
  flex: 1;
  height: 8px;
  background-color: var(--bg-tertiary);
  border-radius: 4px;
  overflow: hidden;
}

.category-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #2563eb, #3b82f6);
  border-radius: 4px;
  transition: width 0.5s ease;
}

.category-bar-value {
  width: 80px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-primary);
  text-align: right;
}

.chart-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--text-tertiary);
}

/* Recent Orders Table */
.table-header {
  align-items: center;
}

.view-all-link {
  font-size: 0.85rem;
  font-weight: 700;
  color: #2563eb;
  text-decoration: none;
  transition: color var(--transition-fast);
}

.view-all-link:hover {
  color: #1d4ed8;
}

.customer-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.customer-avatar-initials {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #dbeafe, #eff6ff);
  color: #2563eb;
  font-weight: 700;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #bfdbfe;
}

.icon-btn-more {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
}

.icon-btn-more:hover {
  background-color: var(--bg-tertiary);
  color: var(--text-primary);
}
</style>
