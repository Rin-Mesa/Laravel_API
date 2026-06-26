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
  Image as ImageIcon,
  Package,
  CheckCircle,
  XCircle,
  ChevronLeft,
  ChevronRight,
  Filter,
  Download,
  X,
  Check
} from 'lucide-vue-next';

// Component state
const categories = ref<any[]>([]);
const search = ref('');
const statusFilter = ref('all');
const loading = ref(true);
const selectedCategories = ref<Set<number>>(new Set());
const selectAll = ref(false);

// Pagination
const currentPage = ref(1);
const itemsPerPage = 8;

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingCategoryId = ref<number | null>(null);

// Form state
const formName = ref('');
const formSlug = ref('');
const formDescription = ref('');
const formStatus = ref('active');
const formImageFile = ref<File | null>(null);
const formImagePreview = ref<string | null>(null);

const fetchCategories = async () => {
  loading.value = true;
  try {
    const res = await api.getCategories();
    if (res.success) {
      categories.value = res.data;
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to fetch categories', 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchCategories();
});

// Filtering and Searching
const filteredCategories = computed(() => {
  return categories.value.filter(c => {
    const matchesSearch =
      c.name.toLowerCase().includes(search.value.toLowerCase()) ||
      c.slug.toLowerCase().includes(search.value.toLowerCase());

    const matchesStatus =
      statusFilter.value === 'all' ||
      c.status === statusFilter.value;

    return matchesSearch && matchesStatus;
  });
});

// Paginated categories
const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredCategories.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredCategories.value.length / itemsPerPage) || 1;
});

// Computed Metrics
const activeCount = computed(() => {
  return categories.value.filter(c => c.status === 'active').length;
});

const inactiveCount = computed(() => {
  return categories.value.filter(c => c.status === 'inactive').length;
});

const avgProductsPerCategory = computed(() => {
  if (categories.value.length === 0) return 0;
  const totalProducts = categories.value.reduce((sum, c) => sum + (c.products_count || 0), 0);
  return (totalProducts / categories.value.length).toFixed(1);
});

const getStatusBadge = (status: string) => {
  if (status === 'active') return { label: 'Active', class: 'badge-success', icon: CheckCircle };
  return { label: 'Inactive', class: 'badge-danger', icon: XCircle };
};

const generateSlug = (name: string) => {
  return name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
};

// Modal Operations
const openAddModal = () => {
  isEditing.value = false;
  editingCategoryId.value = null;
  formName.value = '';
  formSlug.value = '';
  formDescription.value = '';
  formStatus.value = 'active';
  formImageFile.value = null;
  formImagePreview.value = null;
  isModalOpen.value = true;
};

const openEditModal = (category: any) => {
  isEditing.value = true;
  editingCategoryId.value = category.id;
  formName.value = category.name;
  formSlug.value = category.slug;
  formDescription.value = category.description || '';
  formStatus.value = category.status || 'active';
  formImageFile.value = null;
  formImagePreview.value = category.image_url || null;
  isModalOpen.value = true;
};

const handleNameChange = () => {
  if (!isEditing.value) {
    formSlug.value = generateSlug(formName.value);
  }
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0] as File;
    formImageFile.value = file;
    formImagePreview.value = URL.createObjectURL(file);
  }
};

const handleSaveCategory = async () => {
  if (!formName.value || !formSlug.value) {
    store.setAlert('Please fill out all required fields', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('name', formName.value);
  formData.append('slug', formSlug.value);
  formData.append('description', formDescription.value);
  formData.append('status', formStatus.value);

  if (formImageFile.value) {
    formData.append('image', formImageFile.value);
  }

  try {
    let res;
    if (isEditing.value && editingCategoryId.value) {
      res = await api.updateCategory(editingCategoryId.value, formData);
    } else {
      res = await api.createCategory(formData);
    }

    if (res.success) {
      store.setAlert(
        `Category successfully ${isEditing.value ? 'updated' : 'created'}`,
        'success'
      );
      isModalOpen.value = false;
      await fetchCategories();
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to save category', 'error');
  }
};

const handleDeleteCategory = async (id: number, name: string) => {
  if (confirm(`Are you sure you want to delete "${name}"? This will affect all products in this category.`)) {
    try {
      const res = await api.deleteCategory(id);
      if (res.success) {
        store.setAlert('Category successfully deleted', 'success');
        await fetchCategories();
      }
    } catch (err: any) {
      store.setAlert(err.message || 'Failed to delete category', 'error');
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
    selectedCategories.value.clear();
  } else {
    paginatedCategories.value.forEach(c => selectedCategories.value.add(c.id));
  }
  selectAll.value = !selectAll.value;
};

const toggleSelectCategory = (id: number) => {
  if (selectedCategories.value.has(id)) {
    selectedCategories.value.delete(id);
  } else {
    selectedCategories.value.add(id);
  }
  selectAll.value = selectedCategories.value.size === paginatedCategories.value.length;
};

const handleBulkDelete = async () => {
  if (selectedCategories.value.size === 0) {
    store.setAlert('Please select at least one category', 'error');
    return;
  }
  if (confirm(`Are you sure you want to delete ${selectedCategories.value.size} selected categories? This will affect all products in these categories.`)) {
    try {
      for (const id of selectedCategories.value) {
        await api.deleteCategory(id);
      }
      store.setAlert(`${selectedCategories.value.size} categories successfully deleted`, 'success');
      selectedCategories.value.clear();
      selectAll.value = false;
      await fetchCategories();
    } catch (err: any) {
      store.setAlert(err.message || 'Failed to delete categories', 'error');
    }
  }
};

const handleExport = () => {
  store.setAlert('Export functionality coming soon', 'info');
};
</script>

<template>
  <div class="categories-wrapper">
    <div class="categories-header">
      <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Organize your product catalog with categories</p>
      </div>

      <div class="header-actions">
        <button class="btn btn-outline btn-sm" @click="handleExport">
          <Download :size="16" />
          Export
        </button>
        <button class="btn btn-primary" @click="openAddModal">
          <Plus :size="16" />
          Add New Category
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid animate-fade-in">
      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Total Categories</span>
          <span class="stat-value">{{ categories.length }}</span>
        </div>
        <div class="stat-icon-wrapper">
          <Package :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Active</span>
          <span class="stat-value">{{ activeCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: var(--success); background-color: var(--success-light);">
          <CheckCircle :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Inactive</span>
          <span class="stat-value">{{ inactiveCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: var(--danger); background-color: var(--danger-light);">
          <XCircle :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Avg. Products/Cat</span>
          <span class="stat-value">{{ avgProductsPerCategory }}</span>
        </div>
        <div class="stat-icon-wrapper">
          <ImageIcon :size="20" />
        </div>
      </div>
    </div>

    <!-- Filters panel -->
    <div class="card filters-card animate-fade-in">
      <div class="filters-row">
        <div class="search-input-wrapper">
          <Search :size="18" class="search-icon-inside" />
          <input type="text" placeholder="Search categories..." class="form-input search-input"
            v-model="search" @input="currentPage = 1" />
        </div>

        <div class="filter-dropdown-wrapper">
          <label>Status:</label>
          <div class="select-wrapper">
            <select class="form-input select-input" v-model="statusFilter" @change="currentPage = 1">
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <ChevronDown :size="16" class="select-chevron" />
          </div>
        </div>

        <div v-if="selectedCategories.size > 0" class="bulk-actions-wrapper">
          <span class="selected-count">{{ selectedCategories.size }} selected</span>
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
        <p>Loading categories...</p>
      </div>

      <div v-else-if="filteredCategories.length === 0" class="empty-state">
        <ImageIcon :size="48" style="color: var(--text-tertiary); margin-bottom: 16px;" />
        <p>No categories found matching your filters.</p>
        <button class="btn btn-secondary btn-sm" @click="search = ''; statusFilter = 'all'">
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
                <th>Category Name</th>
                <th>Slug</th>
                <th>Product Count</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in paginatedCategories" :key="category.id">
                <td>
                  <input type="checkbox" :checked="selectedCategories.has(category.id)" @change="toggleSelectCategory(category.id)" class="checkbox-input" />
                </td>
                <td>
                  <img :src="category.image_url || 'https://via.placeholder.com/44'" :alt="category.name" 
                    class="category-thumb"
                    @error="($event.target as HTMLImageElement).src = 'https://via.placeholder.com/44?text=CAT'" />
                </td>
                <td style="font-weight: 600;">{{ category.name }}</td>
                <td style="font-family: monospace; color: var(--text-secondary); font-size: 0.85rem;">
                  {{ category.slug }}
                </td>
                <td>
                  <span class="product-count-badge">{{ category.products_count || 0 }} products</span>
                </td>
                <td>
                  <span :class="['badge', getStatusBadge(category.status).class]">
                    <component :is="getStatusBadge(category.status).icon" :size="12" style="margin-right: 4px;" />
                    {{ getStatusBadge(category.status).label }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="actions-group">
                    <button class="action-btn edit-btn" @click="openEditModal(category)" title="Edit">
                      <Edit :size="16" />
                    </button>
                    <button class="action-btn delete-btn" @click="handleDeleteCategory(category.id, category.name)"
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
            {{ Math.min(currentPage * itemsPerPage, filteredCategories.length) }} of
            {{ filteredCategories.length }} categories
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

    <!-- ADD/EDIT CATEGORY MODAL -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Edit Category' : 'Add New Category' }}</h3>
          <button class="close-btn" @click="isModalOpen = false">
            <X :size="20" />
          </button>
        </div>

        <form @submit.prevent="handleSaveCategory" class="modal-form">
          <div class="modal-form-grid">
            <div class="form-group span-2">
              <label for="c-name">Category Name *</label>
              <input id="c-name" type="text" class="form-input" v-model="formName" @input="handleNameChange" required />
            </div>

            <div class="form-group span-2">
              <label for="c-slug">Slug *</label>
              <input id="c-slug" type="text" class="form-input" v-model="formSlug" required />
              <small style="color: var(--text-tertiary); font-size: 0.75rem;">URL-friendly identifier</small>
            </div>

            <div class="form-group span-2">
              <label for="c-status">Status</label>
              <div class="select-wrapper">
                <select id="c-status" class="form-input select-input" v-model="formStatus">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <ChevronDown :size="16" class="select-chevron" />
              </div>
            </div>

            <div class="form-group span-2">
              <label for="c-desc">Description</label>
              <textarea id="c-desc" class="form-input" style="height: 100px; resize: vertical;"
                v-model="formDescription"></textarea>
            </div>

            <!-- Image upload -->
            <div class="form-group span-2">
              <label>Category Image</label>
              <div class="image-upload-zone">
                <input type="file" id="c-image-file" accept="image/*" class="hidden-file-input"
                  @change="handleFileChange" />

                <div v-if="formImagePreview" class="image-preview-box">
                  <img :src="formImagePreview" class="preview-img" />
                  <button type="button" class="remove-preview-btn"
                    @click="formImageFile = null; formImagePreview = null">
                    Remove
                  </button>
                </div>
                <label v-else for="c-image-file" class="upload-placeholder">
                  <ImageIcon :size="24" />
                  <span>Click to upload category image</span>
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
              Save Category
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.categories-wrapper {
  display: flex;
  flex-direction: column;
  gap: 28px;
  font-family: var(--font-body);
}

.categories-header {
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
.category-thumb {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  background-color: var(--bg-tertiary);
  border: 1px solid var(--border-color);
}

.product-count-badge {
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
