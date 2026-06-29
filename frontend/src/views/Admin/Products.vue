<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { api } from '../../services/api';
import { store } from '../../store';
import {
  Plus,
  Search,
  ChevronDown,
  Edit,
  Trash2,
  Sparkles,
  TrendingUp,
  AlertCircle,
  History,
  ChevronLeft,
  ChevronRight,
  Upload,
  X,
  Check,
  MoreHorizontal
} from 'lucide-vue-next';

// Component state
const products = ref<any[]>([]);
const categories = ref<any[]>([]);
const search = ref('');
const selectedCategory = ref('all');
const loading = ref(true);
const selectedProducts = ref<Set<number>>(new Set());
const selectAll = ref(false);

// Pagination
const currentPage = ref(1);
const itemsPerPage = 5;

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingProductId = ref<number | null>(null);

// Form state
const formName = ref('');
const formSku = ref('');
const formCategoryId = ref('');
const formPrice = ref(0);
const formStock = ref(0);
const formDescription = ref('');
const formImageFile = ref<File | null>(null);
const formImagePreview = ref<string | null>(null);

const fetchProducts = async () => {
  loading.value = true;
  try {
    const res = await api.getProducts();
    if (res.success) {
      // API now returns paginator shape: { data: { items, ... } }
      products.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to fetch products', 'error');
  } finally {
    loading.value = false;
  }
};


const fetchCategories = async () => {
  try {
    const res = await api.getCategories();
    if (res.success) {
      categories.value = res.data;
    }
  } catch (err) {
    console.error('Failed to fetch categories', err);
  }
};

onMounted(async () => {
  await Promise.all([fetchProducts(), fetchCategories()]);
});

// Filtering and Searching
const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchesSearch =
      p.name.toLowerCase().includes(search.value.toLowerCase()) ||
      (p.sku && p.sku.toLowerCase().includes(search.value.toLowerCase())) ||
      p.id.toString().includes(search.value);

    const matchesCategory =
      selectedCategory.value === 'all' ||
      p.category_id.toString() === selectedCategory.value;

    return matchesSearch && matchesCategory;
  });
});

// Paginated products
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredProducts.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredProducts.value.length / itemsPerPage) || 1;
});

// Computed Metrics
const lowStockCount = computed(() => {
  return products.value.filter(p => p.stock > 0 && p.stock <= 15).length;
});

const outOfStockCount = computed(() => {
  return products.value.filter(p => p.stock === 0).length;
});

const getStockBadge = (stock: number) => {
  if (stock === 0) return { label: 'Out of Stock', class: 'badge-danger', dot: 'red-dot' };
  if (stock <= 15) return { label: `${stock} Low Stock`, class: 'badge-warning', dot: 'orange-dot' };
  return { label: `${stock} In Stock`, class: 'badge-success', dot: 'green-dot' };
};

// Modal Operations
const openAddModal = () => {
  isEditing.value = false;
  editingProductId.value = null;
  formName.value = '';
  formSku.value = '';
  formCategoryId.value = categories.value[0]?.id || '';
  formPrice.value = 0;
  formStock.value = 0;
  formDescription.value = '';
  formImageFile.value = null;
  formImagePreview.value = null;
  isModalOpen.value = true;
};

const openEditModal = (product: any) => {
  isEditing.value = true;
  editingProductId.value = product.id;
  formName.value = product.name;
  formSku.value = product.sku || '';
  formCategoryId.value = product.category_id;
  formPrice.value = product.price;
  formStock.value = product.stock;
  formDescription.value = product.description || '';
  formImageFile.value = null;
  formImagePreview.value = product.image_url || null;
  isModalOpen.value = true;
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0] as File;
    formImageFile.value = file;
    formImagePreview.value = URL.createObjectURL(file);
  }
};

const handleSaveProduct = async () => {
  if (!formName.value || !formPrice.value === undefined || !formStock.value === undefined) {
    store.setAlert('Please fill out all required fields', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('name', formName.value);
  formData.append('sku', formSku.value);
  formData.append('category_id', formCategoryId.value);
  formData.append('price', formPrice.value.toString());
  formData.append('stock', formStock.value.toString());
  formData.append('description', formDescription.value);

  if (formImageFile.value) {
    formData.append('image', formImageFile.value);
  }

  try {
    let res;
    if (isEditing.value && editingProductId.value) {
      res = await api.updateProduct(editingProductId.value, formData);
    } else {
      res = await api.createProduct(formData);
    }

    if (res.success) {
      store.setAlert(
        `Product successfully ${isEditing.value ? 'updated' : 'created'}`,
        'success'
      );
      isModalOpen.value = false;
      await fetchProducts();
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to save product', 'error');
  }
};

const handleDeleteProduct = async (id: number, name: string) => {
  if (confirm(`Are you sure you want to delete "${name}"?`)) {
    try {
      const res = await api.deleteProduct(id);
      if (res.success) {
        store.setAlert('Product successfully deleted', 'success');
        await fetchProducts();
      }
    } catch (err: any) {
      store.setAlert(err.message || 'Failed to delete product', 'error');
    }
  }
};

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Bulk selection
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedProducts.value.clear();
  } else {
    paginatedProducts.value.forEach(p => selectedProducts.value.add(p.id));
  }
  selectAll.value = !selectAll.value;
};

const toggleSelectProduct = (id: number) => {
  if (selectedProducts.value.has(id)) {
    selectedProducts.value.delete(id);
  } else {
    selectedProducts.value.add(id);
  }
  selectAll.value = selectedProducts.value.size === paginatedProducts.value.length;
};

const handleBulkDelete = async () => {
  if (selectedProducts.value.size === 0) {
    store.setAlert('Please select at least one product', 'error');
    return;
  }
  if (confirm(`Are you sure you want to delete ${selectedProducts.value.size} selected products?`)) {
    try {
      for (const id of selectedProducts.value) {
        await api.deleteProduct(id);
      }
      store.setAlert(`${selectedProducts.value.size} products successfully deleted`, 'success');
      selectedProducts.value.clear();
      selectAll.value = false;
      await fetchProducts();
    } catch (err: any) {
      store.setAlert(err.message || 'Failed to delete products', 'error');
    }
  }
};
</script>

<template>
  <div class="products-wrapper">
    <div class="products-header">
      <div>
        <h1 class="page-title">Product Inventory</h1>
        <p class="page-subtitle">Manage store catalog, pricing, and stock levels</p>
      </div>

      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddModal">
          <Plus :size="16" />
          New Product
        </button>
      </div>
    </div>

    <!-- Filters panel -->
    <div class="card filters-card animate-fade-in">
      <div class="filters-row">
        <div class="search-input-wrapper">
          <Search :size="18" class="search-icon-inside" />
          <input type="text" placeholder="Search by ID, SKU, or Product Name..." class="form-input search-input"
            v-model="search" @input="currentPage = 1" />
        </div>

        <div class="filter-dropdown-wrapper">
          <label>Filter by Category:</label>
          <div class="select-wrapper">
            <select class="form-input select-input" v-model="selectedCategory" @change="currentPage = 1">
              <option value="all">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                {{ cat.name }}
              </option>
            </select>
            <ChevronDown :size="16" class="select-chevron" />
          </div>
        </div>

        <div v-if="selectedProducts.size > 0" class="bulk-actions-wrapper">
          <span class="selected-count">{{ selectedProducts.size }} selected</span>
          <button class="btn btn-danger btn-sm" @click="handleBulkDelete">
            <Trash2 :size="14" />
            Delete Selected
          </button>
        </div>
      </div>
    </div>

    <!-- Table content -->
    <div class="card table-card animate-fade-in-up">
      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Loading products catalog...</p>
      </div>

      <div v-else-if="filteredProducts.length === 0" class="empty-state">
        <p>No products found matching your filters.</p>
        <button class="btn btn-secondary btn-sm" @click="search = ''; selectedCategory = 'all'">
          Clear Filters
        </button>
      </div>

      <template v-else>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 40px;">
                  <input type="checkbox" :checked="selectAll" @change="toggleSelectAll" class="checkbox-input" />
                </th>
                <th>Image</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock Level</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in paginatedProducts" :key="product.id">
                <td>
                  <input type="checkbox" :checked="selectedProducts.has(product.id)"
                    @change="toggleSelectProduct(product.id)" class="checkbox-input" />
                </td>
                <td>
                  <img :src="product.image_url" :alt="product.name" class="product-thumb"
                    @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=256&auto=format&fit=crop'" />
                </td>
                <td style="font-weight: 600;">{{ product.name }}</td>
                <td style="font-family: monospace; color: var(--text-secondary);">
                  {{ product.sku || 'N/A' }}
                </td>
                <td>
                  <span class="category-pill">{{ product.category?.name }}</span>
                </td>
                <td style="font-weight: 700; color: var(--accent-primary);">
                  {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(product.price) }}
                </td>
                <td>
                  <div class="stock-cell">
                    <span :class="['stock-dot', getStockBadge(product.stock).dot]"></span>
                    <span :class="['badge', getStockBadge(product.stock).class]">
                      {{ getStockBadge(product.stock).label }}
                    </span>
                  </div>
                </td>
                <td style="text-align: right;">
                  <div class="actions-group">
                    <button class="action-btn edit-btn" @click="openEditModal(product)" title="Edit">
                      <Edit :size="16" />
                    </button>
                    <button class="action-btn delete-btn" @click="handleDeleteProduct(product.id, product.name)"
                      title="Delete">
                      <Trash2 :size="16" />
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
            {{ Math.min(currentPage * itemsPerPage, filteredProducts.length) }} of
            {{ filteredProducts.length }} products
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

    <!-- Metrics panel below table -->
    <div class="metrics-cards-grid animate-fade-in-up">
      <div class="card metric-mini-card">
        <div class="metric-icon-box" style="color: #2563eb; background-color: rgba(37, 99, 235, 0.1);">
          <TrendingUp :size="18" />
        </div>
        <div class="metric-mini-info">
          <h4>Stock Trend</h4>
          <p>Inventory turnover increased by 12% this week. Consider restocking high-performing items.</p>
        </div>
      </div>

      <div class="card metric-mini-card">
        <div class="metric-icon-box" style="color: #f59e0b; background-color: rgba(245, 158, 11, 0.1);">
          <AlertCircle :size="18" />
        </div>
        <div class="metric-mini-info">
          <h4>Low Stock Alert</h4>
          <p>{{ lowStockCount + outOfStockCount }} items are currently below safety threshold. Re-order recommended
            immediately.</p>
        </div>
      </div>

      <div class="card metric-mini-card">
        <div class="metric-icon-box" style="color: #6b7280; background-color: rgba(107, 114, 128, 0.1);">
          <History :size="18" />
        </div>
        <div class="metric-mini-info">
          <h4>Recent Activity</h4>
          <p>Admin user updated the 'Sonic Aura Elite' pricing details 14 minutes ago.</p>
        </div>
      </div>
    </div>

    <!-- ADD/EDIT PRODUCT MODAL -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h3>
          <button class="close-btn" @click="isModalOpen = false">
            <X :size="20" />
          </button>
        </div>

        <form @submit.prevent="handleSaveProduct" class="modal-form">
          <div class="modal-form-grid">
            <div class="form-group span-2">
              <label for="p-name">Product Name *</label>
              <input id="p-name" type="text" class="form-input" v-model="formName" required />
            </div>

            <div class="form-group">
              <label for="p-sku">SKU (Stock Keeping Unit)</label>
              <input id="p-sku" type="text" class="form-input" v-model="formSku" placeholder="ST-APX-01" />
            </div>

            <div class="form-group">
              <label for="p-cat">Category *</label>
              <div class="select-wrapper">
                <select id="p-cat" class="form-input select-input" v-model="formCategoryId" required>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
                <ChevronDown :size="16" class="select-chevron" />
              </div>
            </div>

            <div class="form-group">
              <label for="p-price">Price ($) *</label>
              <input id="p-price" type="number" step="0.01" min="0" class="form-input" v-model="formPrice" required />
            </div>

            <div class="form-group">
              <label for="p-stock">Stock Level *</label>
              <input id="p-stock" type="number" min="0" class="form-input" v-model="formStock" required />
            </div>

            <div class="form-group span-2">
              <label for="p-desc">Description</label>
              <textarea id="p-desc" class="form-input" style="height: 100px; resize: vertical;"
                v-model="formDescription"></textarea>
            </div>

            <!-- Image upload -->
            <div class="form-group span-2">
              <label>Product Image</label>
              <div class="image-upload-zone">
                <input type="file" id="p-image-file" accept="image/*" class="hidden-file-input"
                  @change="handleFileChange" />

                <div v-if="formImagePreview" class="image-preview-box">
                  <img :src="formImagePreview" class="preview-img" />
                  <button type="button" class="remove-preview-btn"
                    @click="formImageFile = null; formImagePreview = null">
                    Remove
                  </button>
                </div>
                <label v-else for="p-image-file" class="upload-placeholder">
                  <Upload :size="24" />
                  <span>Click to upload product image</span>
                  <span class="sub text-secondary">PNG, JPG or GIF up to 2MB</span>
                </label>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="isModalOpen = false">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary">
              Save Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.products-wrapper {
  display: flex;
  flex-direction: column;
  gap: 28px;
  font-family: var(--font-body);
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

@media (max-width: 640px) {
  .products-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: space-between;
  }
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.spark-icon {
  animation: pulseGlow 2s infinite ease-in-out;
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
  width: 200px;
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

.bulk-actions-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-left: 24px;
  border-left: 1px solid var(--border-color);
}

.selected-count {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.checkbox-input {
  width: 16px;
  height: 16px;
  cursor: pointer;
  accent-color: #2563eb;
}

/* Table Card */
.product-thumb {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  background-color: var(--bg-tertiary);
  border: 1px solid var(--border-color);
}

.category-pill {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-secondary);
  background-color: var(--bg-tertiary);
  padding: 4px 10px;
  border-radius: 50px;
}

.stock-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stock-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.green-dot {
  background-color: var(--success);
}

.orange-dot {
  background-color: var(--warning);
}

.red-dot {
  background-color: var(--danger);
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

.edit-btn:hover {
  color: var(--accent-secondary);
  border-color: rgba(59, 130, 246, 0.3);
}

.delete-btn:hover {
  color: var(--danger);
  border-color: rgba(239, 68, 68, 0.3);
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

/* Metrics Mini Cards */
.metrics-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.metric-mini-card {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.metric-icon-box {
  width: 38px;
  height: 38px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.metric-mini-info h4 {
  font-family: var(--font-display);
  font-size: 0.9rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.metric-mini-info p {
  font-size: 0.75rem;
  color: var(--text-secondary);
  line-height: 1.4;
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
  background: #3b82f6;
  color: white;
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

.modal-form {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.modal-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.span-2 {
  grid-column: span 2;
}

.hidden-file-input {
  display: none;
}

.modal-form-grid .form-input,
.modal-form-grid .image-upload-zone {
  background-color: rgba(102, 96, 96, 0.419);
}

.image-upload-zone {
  border: 2px dashed var(--border-color);
  border-radius: var(--radius-md);
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background-color: var(--bg-tertiary);
  transition: border-color var(--transition-fast);
  overflow: hidden;
}

.image-upload-zone:hover {
  border-color: var(--accent-secondary);
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}

.upload-placeholder span {
  font-size: 0.85rem;
  font-weight: 600;
}

.upload-placeholder .sub {
  font-size: 0.7rem;
}

.image-preview-box {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  width: 100%;
  height: 100%;
}

.preview-img {
  height: 100%;
  width: 90px;
  object-fit: cover;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-color);
}

.remove-preview-btn {
  background-color: var(--danger-light);
  color: var(--danger);
  border: none;
  padding: 6px 12px;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: var(--radius-sm);
  cursor: pointer;
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
</style>
