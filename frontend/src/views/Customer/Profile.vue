<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import { 
  User, 
  Mail, 
  Phone, 
  MapPin, 
  Lock,
  Edit,
  Save,
  LogOut,
  Camera
} from 'lucide-vue-next';

const loading = ref(false);
const saving = ref(false);
const editing = ref(false);

const profileData = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  country: 'United States',
});

const passwordData = ref({
  current_password: '',
  new_password: '',
  confirm_password: '',
});

const errors = ref<Record<string, string>>({});

const currentUser = computed(() => store.user.value);

onMounted(() => {
  if (currentUser.value) {
    profileData.value.name = currentUser.value.name || '';
    profileData.value.email = currentUser.value.email || '';
  }
});

const handleEdit = () => {
  editing.value = true;
};

const handleCancel = () => {
  editing.value = false;
  if (currentUser.value) {
    profileData.value.name = currentUser.value.name || '';
    profileData.value.email = currentUser.value.email || '';
  }
  errors.value = {};
};

const validateProfile = () => {
  errors.value = {};
  
  if (!profileData.value.name.trim()) {
    errors.value.name = 'Name is required';
  }
  
  if (!profileData.value.email.trim()) {
    errors.value.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profileData.value.email)) {
    errors.value.email = 'Please enter a valid email';
  }
  
  return Object.keys(errors.value).length === 0;
};

const handleSaveProfile = async () => {
  if (!validateProfile()) return;
  
  saving.value = true;
  try {
    const res = await api.updateProfile({
      name: profileData.value.name,
      email: profileData.value.email,
    });
    
    if (res.success) {
      // Update local user data
      await store.fetchUser();
      editing.value = false;
      store.setAlert('Profile updated successfully', 'success');
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to update profile', 'error');
  } finally {
    saving.value = false;
  }
};

const validatePassword = () => {
  errors.value = {};
  
  if (!passwordData.value.current_password) {
    errors.value.current_password = 'Current password is required';
  }
  
  if (!passwordData.value.new_password) {
    errors.value.new_password = 'New password is required';
  } else if (passwordData.value.new_password.length < 8) {
    errors.value.new_password = 'Password must be at least 8 characters';
  }
  
  if (!passwordData.value.confirm_password) {
    errors.value.confirm_password = 'Please confirm your password';
  } else if (passwordData.value.new_password !== passwordData.value.confirm_password) {
    errors.value.confirm_password = 'Passwords do not match';
  }
  
  return Object.keys(errors.value).length === 0;
};

const handleChangePassword = async () => {
  if (!validatePassword()) return;
  
  saving.value = true;
  try {
    const res = await api.changePassword({
      current_password: passwordData.value.current_password,
      password: passwordData.value.new_password,
      password_confirmation: passwordData.value.confirm_password,
    });
    
    if (res.success) {
      passwordData.value = {
        current_password: '',
        new_password: '',
        confirm_password: '',
      };
      store.setAlert('Password changed successfully', 'success');
    }
  } catch (err: any) {
    store.setAlert(err.message || 'Failed to change password', 'error');
  } finally {
    saving.value = false;
  }
};

const handleLogout = async () => {
  await store.logout();
};
</script>

<template>
  <div class="profile-wrapper">
    <!-- Header -->
    <div class="profile-header">
      <div class="profile-avatar-section">
        <div class="avatar-large">
          <User :size="48" />
        </div>
        <button class="avatar-edit-btn">
          <Camera :size="16" />
        </button>
      </div>
      <div class="profile-info">
        <h1>{{ currentUser?.name || 'User' }}</h1>
        <p>{{ currentUser?.email || '' }}</p>
        <span class="member-badge">Member since {{ new Date().getFullYear() }}</span>
      </div>
      <button class="btn btn-outline logout-btn" @click="handleLogout">
        <LogOut :size="16" />
        Logout
      </button>
    </div>

    <div class="profile-layout">
      <!-- Profile Information -->
      <div class="profile-section card">
        <div class="section-header">
          <div>
            <h2>Profile Information</h2>
            <p>Update your personal details</p>
          </div>
          <button v-if="!editing" class="btn btn-secondary btn-sm" @click="handleEdit">
            <Edit :size="14" />
            Edit
          </button>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Full Name</label>
            <input 
              type="text" 
              v-model="profileData.name"
              :disabled="!editing"
              :class="{ 'input-error': errors.name }"
              @input="errors.name = ''"
            />
            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
          </div>

          <div class="form-group">
            <label>Email Address</label>
            <input 
              type="email" 
              v-model="profileData.email"
              :disabled="!editing"
              :class="{ 'input-error': errors.email }"
              @input="errors.email = ''"
            />
            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <label>Phone Number</label>
            <input 
              type="tel" 
              v-model="profileData.phone"
              :disabled="!editing"
              placeholder="+1 (555) 000-0000"
            />
          </div>

          <div class="form-group full-width">
            <label>Address</label>
            <input 
              type="text" 
              v-model="profileData.address"
              :disabled="!editing"
              placeholder="123 Main Street"
            />
          </div>

          <div class="form-group">
            <label>City</label>
            <input 
              type="text" 
              v-model="profileData.city"
              :disabled="!editing"
              placeholder="New York"
            />
          </div>

          <div class="form-group">
            <label>State</label>
            <input 
              type="text" 
              v-model="profileData.state"
              :disabled="!editing"
              placeholder="NY"
            />
          </div>

          <div class="form-group">
            <label>ZIP Code</label>
            <input 
              type="text" 
              v-model="profileData.zip_code"
              :disabled="!editing"
              placeholder="10001"
            />
          </div>

          <div class="form-group">
            <label>Country</label>
            <select v-model="profileData.country" :disabled="!editing">
              <option>United States</option>
              <option>Canada</option>
              <option>United Kingdom</option>
            </select>
          </div>
        </div>

        <div v-if="editing" class="form-actions">
          <button class="btn btn-secondary" @click="handleCancel">Cancel</button>
          <button class="btn btn-primary" @click="handleSaveProfile" :disabled="saving">
            <Save :size="14" />
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- Change Password -->
      <div class="profile-section card">
        <div class="section-header">
          <div>
            <h2>Change Password</h2>
            <p>Update your security credentials</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group full-width">
            <label>Current Password</label>
            <input 
              type="password" 
              v-model="passwordData.current_password"
              :class="{ 'input-error': errors.current_password }"
              @input="errors.current_password = ''"
              placeholder="Enter current password"
            />
            <span v-if="errors.current_password" class="error-text">{{ errors.current_password }}</span>
          </div>

          <div class="form-group">
            <label>New Password</label>
            <input 
              type="password" 
              v-model="passwordData.new_password"
              :class="{ 'input-error': errors.new_password }"
              @input="errors.new_password = ''"
              placeholder="Min. 8 characters"
            />
            <span v-if="errors.new_password" class="error-text">{{ errors.new_password }}</span>
          </div>

          <div class="form-group">
            <label>Confirm New Password</label>
            <input 
              type="password" 
              v-model="passwordData.confirm_password"
              :class="{ 'input-error': errors.confirm_password }"
              @input="errors.confirm_password = ''"
              placeholder="Re-enter new password"
            />
            <span v-if="errors.confirm_password" class="error-text">{{ errors.confirm_password }}</span>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" @click="handleChangePassword" :disabled="saving">
            <Lock :size="14" />
            {{ saving ? 'Updating...' : 'Change Password' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-wrapper {
  max-width: 1000px;
  margin: 0 auto;
  padding: 40px;
  font-family: var(--font-body);
  background-color: #05070c;
  color: #f3f4f6;
  min-height: 80vh;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 40px;
  padding: 32px;
  background: linear-gradient(135deg, #111827 0%, #1e1b4b 100%);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-xl);
}

.profile-avatar-section {
  position: relative;
}

.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  border: 3px solid rgba(255, 255, 255, 0.1);
}

.avatar-edit-btn {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #3b82f6;
  border: 2px solid #111827;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.avatar-edit-btn:hover {
  transform: scale(1.1);
}

.profile-info {
  flex: 1;
}

.profile-info h1 {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 800;
  color: white;
  margin-bottom: 4px;
}

.profile-info p {
  font-size: 0.9rem;
  color: #9ca3af;
  margin-bottom: 8px;
}

.member-badge {
  display: inline-block;
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 50px;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.logout-btn {
  padding: 8px 16px;
}

.profile-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}

@media (max-width: 1024px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }
}

.profile-section {
  padding: 32px;
  background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.section-header h2 {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  margin-bottom: 4px;
}

.section-header p {
  font-size: 0.85rem;
  color: #6b7280;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 24px;
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
  transition: all var(--transition-fast);
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-group input:disabled,
.form-group select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

@media (max-width: 768px) {
  .profile-wrapper {
    padding: 24px;
  }

  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
  }
}
</style>
