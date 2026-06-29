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
  Mail,
  Shield,
  User as UserIcon,
  Calendar,
  CheckCircle,
  XCircle,
  ChevronLeft,
  ChevronRight,
  Download,
  X,
  Crown
} from 'lucide-vue-next';

// Component state
const users = ref<any[]>([]);
const search = ref('');
const roleFilter = ref('all');
const statusFilter = ref('all');
const loading = ref(true);

// Pagination
const currentPage = ref(1);
const itemsPerPage = 8;

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref<number | null>(null);

// Form state
const formName = ref('');
const formEmail = ref('');
const formRole = ref('customer');
const formIsActive = ref(true);

const fetchUsers = async () => {
  loading.value = true;
  try {
    const res = await api.getUsers();
    if (res.success) {
      users.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to fetch users', 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchUsers();
});

// Filtering and Searching
const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchesSearch =
      u.name.toLowerCase().includes(search.value.toLowerCase()) ||
      u.email.toLowerCase().includes(search.value.toLowerCase());

    const matchesRole =
      roleFilter.value === 'all' ||
      u.role === roleFilter.value;

    const matchesStatus =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && u.is_active) ||
      (statusFilter.value === 'inactive' && !u.is_active);

    return matchesSearch && matchesRole && matchesStatus;
  });
});

// Paginated users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredUsers.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage) || 1;
});

// Computed Metrics
const adminCount = computed(() => {
  return users.value.filter(u => u.role === 'admin').length;
});

const customerCount = computed(() => {
  return users.value.filter(u => u.role === 'customer').length;
});

const activeCount = computed(() => {
  return users.value.filter(u => u.is_active).length;
});

const inactiveCount = computed(() => {
  return users.value.filter(u => !u.is_active).length;
});

const getRoleBadge = (role: string) => {
  if (role === 'admin') return { label: 'Admin', class: 'badge-danger', icon: Crown };
  return { label: 'Customer', class: 'badge-info', icon: UserIcon };
};

const getStatusBadge = (isActive: boolean) => {
  if (isActive) return { label: 'Active', class: 'badge-success', icon: CheckCircle };
  return { label: 'Inactive', class: 'badge-danger', icon: XCircle };
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
const openAddModal = () => {
  isEditing.value = false;
  editingUserId.value = null;
  formName.value = '';
  formEmail.value = '';
  formRole.value = 'customer';
  formIsActive.value = true;
  isModalOpen.value = true;
};

const openEditModal = (user: any) => {
  isEditing.value = true;
  editingUserId.value = user.id;
  formName.value = user.name;
  formEmail.value = user.email;
  formRole.value = user.role || 'customer';
  formIsActive.value = user.is_active ?? true;
  isModalOpen.value = true;
};

const handleSaveUser = async () => {
  if (!formName.value || !formEmail.value) {
    store.setAlert('Please fill out all required fields', 'error');
    return;
  }

  const userData = {
    name: formName.value,
    email: formEmail.value,
    role: formRole.value,
    is_active: formIsActive.value
  };

  try {
    let res;
    if (isEditing.value && editingUserId.value) {
      res = await api.updateUser(editingUserId.value, userData);
    } else {
      res = await api.createUser(userData);
    }

    if (res.success) {
      store.setAlert(
        `User successfully ${isEditing.value ? 'updated' : 'created'}`,
        'success'
      );
      isModalOpen.value = false;
      await fetchUsers();
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to save user', 'error');
  }
};

const handleDeleteUser = async (id: number, name: string) => {
  if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
    try {
      const res = await api.deleteUser(id);
      if (res.success) {
        store.setAlert('User successfully deleted', 'success');
        await fetchUsers();
      }
    } catch (err: any) {
      store.setAlert(err.message || 'Failed to delete user', 'error');
    }
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
</script>

<template>
  <div class="users-wrapper">
    <div class="users-header">
      <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage user accounts and permissions</p>
      </div>

      <div class="header-actions">
        <button class="btn btn-outline btn-sm" @click="handleExport">
          <Download :size="16" />
          Export
        </button>
        <button class="btn btn-primary" @click="openAddModal">
          <Plus :size="16" />
          Add New User
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid animate-fade-in">
      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Total Users</span>
          <span class="stat-value">{{ users.length }}</span>
        </div>
        <div class="stat-icon-wrapper">
          <UserIcon :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Admins</span>
          <span class="stat-value">{{ adminCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #ef4444; background-color: rgba(239, 68, 68, 0.1);">
          <Crown :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Customers</span>
          <span class="stat-value">{{ customerCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #3b82f6; background-color: rgba(59, 130, 246, 0.1);">
          <Shield :size="20" />
        </div>
      </div>

      <div class="card stat-card">
        <div class="stat-info">
          <span class="stat-label">Active</span>
          <span class="stat-value">{{ activeCount }}</span>
        </div>
        <div class="stat-icon-wrapper" style="color: #10b981; background-color: rgba(16, 185, 129, 0.1);">
          <CheckCircle :size="20" />
        </div>
      </div>
    </div>

    <!-- Filters panel -->
    <div class="card filters-card animate-fade-in">
      <div class="filters-row">
        <div class="search-input-wrapper">
          <Search :size="18" class="search-icon-inside" />
          <input type="text" placeholder="Search by name or email..." class="form-input search-input"
            v-model="search" @input="currentPage = 1" />
        </div>

        <div class="filter-dropdown-wrapper">
          <label>Role:</label>
          <div class="select-wrapper">
            <select class="form-input select-input" v-model="roleFilter" @change="currentPage = 1">
              <option value="all">All Roles</option>
              <option value="admin">Admin</option>
              <option value="customer">Customer</option>
            </select>
            <ChevronDown :size="16" class="select-chevron" />
          </div>
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
      </div>
    </div>

    <!-- Table content -->
    <div class="card table-card animate-fade-in-up">
      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Loading users...</p>
      </div>

      <div v-else-if="filteredUsers.length === 0" class="empty-state">
        <UserIcon :size="48" style="color: var(--text-tertiary); margin-bottom: 16px;" />
        <p>No users found matching your filters.</p>
        <button class="btn btn-secondary btn-sm" @click="search = ''; roleFilter = 'all'; statusFilter = 'all'">
          Clear Filters
        </button>
      </div>

      <template v-else>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined Date</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in paginatedUsers" :key="user.id">
                <td>
                  <div class="user-cell">
                    <img :src="user.avatar_url || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 40 40%22%3E%3Crect width=%2240%22 height=%2240%22 fill=%22%23e2e8f0%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2212%22 fill=%22%2394a3b8%22%3EU%3C/text%3E%3C/svg%3E'" :alt="user.name"
                      class="user-avatar"
                      @error="($event.target as HTMLImageElement).src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 40 40%22%3E%3Crect width=%2240%22 height=%2240%22 fill=%22%23e2e8f0%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2212%22 fill=%22%2394a3b8%22%3EU%3C/text%3E%3C/svg%3E'" />
                    <span style="font-weight: 600;">{{ user.name }}</span>
                  </div>
                </td>
                <td>
                  <div class="email-cell">
                    <Mail :size="14" style="color: var(--text-tertiary); margin-right: 6px;" />
                    <span>{{ user.email }}</span>
                  </div>
                </td>
                <td>
                  <span :class="['badge', getRoleBadge(user.role).class]">
                    <component :is="getRoleBadge(user.role).icon" :size="12" style="margin-right: 4px;" />
                    {{ getRoleBadge(user.role).label }}
                  </span>
                </td>
                <td>
                  <span :class="['badge', getStatusBadge(user.is_active).class]">
                    <component :is="getStatusBadge(user.is_active).icon" :size="12" style="margin-right: 4px;" />
                    {{ getStatusBadge(user.is_active).label }}
                  </span>
                </td>
                <td style="color: var(--text-secondary);">
                  {{ formatDate(user.created_at) }}
                </td>
                <td style="text-align: right;">
                  <div class="actions-group">
                    <button class="action-btn edit-btn" @click="openEditModal(user)" title="Edit">
                      <Edit :size="16" />
                    </button>
                    <button class="action-btn delete-btn" @click="handleDeleteUser(user.id, user.name)"
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
            {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} of
            {{ filteredUsers.length }} users
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

    <!-- ADD/EDIT USER MODAL -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Edit User' : 'Add New User' }}</h3>
          <button class="close-btn" @click="isModalOpen = false">
            <X :size="20" />
          </button>
        </div>

        <form @submit.prevent="handleSaveUser" class="modal-form">
          <div class="modal-form-grid">
            <div class="form-group span-2">
              <label for="u-name">Full Name *</label>
              <input id="u-name" type="text" class="form-input" v-model="formName" required />
            </div>

            <div class="form-group span-2">
              <label for="u-email">Email Address *</label>
              <input id="u-email" type="email" class="form-input" v-model="formEmail" required />
            </div>

            <div class="form-group">
              <label for="u-role">Role *</label>
              <div class="select-wrapper">
                <select id="u-role" class="form-input select-input" v-model="formRole" required>
                  <option value="customer">Customer</option>
                  <option value="admin">Admin</option>
                </select>
                <ChevronDown :size="16" class="select-chevron" />
              </div>
            </div>

            <div class="form-group">
              <label for="u-active" style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                <input id="u-active" type="checkbox" v-model="formIsActive" class="toggle-checkbox" />
                <span>Active User</span>
              </label>
              <small style="color: var(--text-tertiary); font-size: 0.75rem; margin-top: 4px; display: block;">
                Enable this user to allow login
              </small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="isModalOpen = false">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary">
              Save User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.users-wrapper {
  display: flex;
  flex-direction: column;
  gap: 28px;
  font-family: var(--font-body);
}

.users-header {
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
  width: 160px;
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
.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  background-color: var(--bg-tertiary);
  border: 2px solid var(--border-color);
}

.email-cell {
  display: flex;
  align-items: center;
  font-size: 0.85rem;
  color: var(--text-secondary);
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
  background-color: rgba(30, 32, 36, 0);
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
  border: 1px solid var(--color-neutral-400);
  border-radius: var(--radius-lg);
  width: 500px;
  max-width: 100%;
  max-height: calc(100vh - 48px);
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-lg);
  animation: fadeInUp var(--transition-normal);
  overflow: hidden;
}

.modal-form-grid .form-input{
  background-color: rgba(102, 96, 96, 0.284);
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
  color: rgba(255, 255, 255, 0.673);
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

.toggle-checkbox {
  width: 44px;
  height: 24px;
  appearance: none;
  background-color: var(--border-color);
  border-radius: 12px;
  position: relative;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.toggle-checkbox::before {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  background-color: white;
  border-radius: 50%;
  top: 2px;
  left: 2px;
  transition: transform 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-checkbox:checked {
  background-color: #2563eb;
}

.toggle-checkbox:checked::before {
  transform: translateX(20px);
}

.toggle-checkbox:focus {
  outline: 2px solid rgba(37, 99, 235, 0.3);
  outline-offset: 2px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
