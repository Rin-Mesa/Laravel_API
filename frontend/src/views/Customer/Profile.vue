<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import { useRouter } from 'vue-router';
import {
  User, Mail, Phone, MapPin, Lock,
  Edit3, Save, LogOut, Camera, Shield,
  ShoppingBag, Heart, CheckCircle, Key
} from 'lucide-vue-next';

const router = useRouter();
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

const initials = computed(() => {
  const name = currentUser.value?.name || 'U';
  return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
});

const memberSince = computed(() => {
  return new Date().getFullYear();
});

onMounted(() => {
  if (currentUser.value) {
    profileData.value.name = currentUser.value.name || '';
    profileData.value.email = currentUser.value.email || '';
  }
});

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
  if (!profileData.value.name.trim()) errors.value.name = 'Name is required';
  if (!profileData.value.email.trim()) errors.value.email = 'Email is required';
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profileData.value.email)) errors.value.email = 'Enter a valid email';
  return Object.keys(errors.value).length === 0;
};

const handleSaveProfile = async () => {
  if (!validateProfile()) return;
  saving.value = true;
  try {
    const res = await api.updateProfile({ name: profileData.value.name, email: profileData.value.email });
    if (res.success) {
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
  if (!passwordData.value.current_password) errors.value.current_password = 'Required';
  if (!passwordData.value.new_password) errors.value.new_password = 'Required';
  else if (passwordData.value.new_password.length < 8) errors.value.new_password = 'Minimum 8 characters';
  if (passwordData.value.new_password !== passwordData.value.confirm_password) errors.value.confirm_password = 'Passwords do not match';
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
      passwordData.value = { current_password: '', new_password: '', confirm_password: '' };
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
  router.push('/login');
};
</script>

<template>
  <div class="w-full bg-neutral-50 min-h-screen">
    <div class="max-w-[1100px] mx-auto px-4 md:px-8 py-8 md:py-12">

      <!-- ── Page Header ── -->
      <div class="mb-8">
        <p class="font-mono text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-1">Account</p>
        <h1 class="text-2xl md:text-3xl font-bold text-neutral-900">My Profile</h1>
      </div>

      <!-- ── Hero Banner ── -->
      <div class="relative bg-primary-600 rounded-2xl p-6 md:p-8 mb-8 overflow-hidden">
        <!-- Decorative circles -->
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-primary-500/40 rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-8 w-40 h-40 bg-primary-700/50 rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-5">
          <!-- Avatar -->
          <div class="relative shrink-0">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center">
              <span class="text-white font-bold text-2xl font-mono">{{ initials }}</span>
            </div>
            <button class="absolute -bottom-1.5 -right-1.5 w-7 h-7 bg-secondary-500 hover:bg-secondary-600 rounded-lg flex items-center justify-center text-white shadow-md transition-colors">
              <Camera :size="13" />
            </button>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-white truncate">{{ currentUser?.name || 'User' }}</h2>
            <p class="text-primary-200 text-sm mt-0.5">{{ currentUser?.email || '' }}</p>
            <div class="flex items-center gap-3 mt-3 flex-wrap">
              <span class="bg-white/15 text-white text-[10px] font-mono font-bold px-3 py-1 rounded-full border border-white/20 uppercase tracking-wider">
                Member since {{ memberSince }}
              </span>
              <span class="bg-secondary-500/80 text-white text-[10px] font-mono font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                {{ currentUser?.role || 'customer' }}
              </span>
            </div>
          </div>

          <!-- Logout -->
          <button
            @click="handleLogout"
            class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg border border-white/20 transition-colors shrink-0"
          >
            <LogOut :size="15" />
            Logout
          </button>
        </div>

        <!-- Quick stats -->
        <div class="relative z-10 grid grid-cols-3 gap-3 mt-6">
          <div class="bg-white/10 rounded-xl p-3 text-center border border-white/10">
            <ShoppingBag :size="18" class="text-white/60 mx-auto mb-1" />
            <p class="text-white font-bold text-lg font-mono">0</p>
            <p class="text-primary-200 text-[10px] font-mono uppercase tracking-wider">Orders</p>
          </div>
          <div class="bg-white/10 rounded-xl p-3 text-center border border-white/10">
            <Heart :size="18" class="text-white/60 mx-auto mb-1" />
            <p class="text-white font-bold text-lg font-mono">{{ store.wishlist.value.length }}</p>
            <p class="text-primary-200 text-[10px] font-mono uppercase tracking-wider">Wishlist</p>
          </div>
          <div class="bg-white/10 rounded-xl p-3 text-center border border-white/10">
            <CheckCircle :size="18" class="text-white/60 mx-auto mb-1" />
            <p class="text-white font-bold text-lg font-mono">0</p>
            <p class="text-primary-200 text-[10px] font-mono uppercase tracking-wider">Reviews</p>
          </div>
        </div>
      </div>

      <!-- ── Main Grid ── -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

        <!-- ── Profile Information (wide) ── -->
        <div class="lg:col-span-3 bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 md:p-8">
          <!-- Header -->
          <div class="flex items-start justify-between mb-6 pb-5 border-b border-neutral-100">
            <div>
              <h2 class="font-bold text-neutral-900 text-lg">Profile Information</h2>
              <p class="text-neutral-500 text-sm mt-0.5">Update your personal details</p>
            </div>
            <button
              v-if="!editing"
              @click="editing = true"
              class="btn-secondary py-2 px-4 text-xs flex items-center gap-1.5"
            >
              <Edit3 :size="13" /> Edit
            </button>
          </div>

          <!-- Form -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">

            <div class="flex flex-col gap-1.5">
              <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Full Name</label>
              <div class="relative">
                <User :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none" />
                <input
                  type="text"
                  v-model="profileData.name"
                  :disabled="!editing"
                  class="input-field pl-9 disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
                  :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400/20': errors.name }"
                  @input="errors.name = ''"
                />
              </div>
              <span v-if="errors.name" class="text-xs text-red-500 font-mono">{{ errors.name }}</span>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Email Address</label>
              <div class="relative">
                <Mail :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none" />
                <input
                  type="email"
                  v-model="profileData.email"
                  :disabled="!editing"
                  class="input-field pl-9 disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
                  :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400/20': errors.email }"
                  @input="errors.email = ''"
                />
              </div>
              <span v-if="errors.email" class="text-xs text-red-500 font-mono">{{ errors.email }}</span>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Phone Number</label>
              <div class="relative">
                <Phone :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none" />
                <input
                  type="tel"
                  v-model="profileData.phone"
                  :disabled="!editing"
                  placeholder="+1 (555) 000-0000"
                  class="input-field pl-9 disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
                />
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Country</label>
              <select
                v-model="profileData.country"
                :disabled="!editing"
                class="input-field disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
              >
                <option>United States</option>
                <option>Canada</option>
                <option>United Kingdom</option>
                <option>Thailand</option>
                <option>Singapore</option>
              </select>
            </div>

            <div class="sm:col-span-2 flex flex-col gap-1.5">
              <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Address</label>
              <div class="relative">
                <MapPin :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none" />
                <input
                  type="text"
                  v-model="profileData.address"
                  :disabled="!editing"
                  placeholder="123 Main Street"
                  class="input-field pl-9 disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
                />
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">City</label>
              <input
                type="text"
                v-model="profileData.city"
                :disabled="!editing"
                placeholder="New York"
                class="input-field disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="flex flex-col gap-1.5">
                <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">State</label>
                <input
                  type="text"
                  v-model="profileData.state"
                  :disabled="!editing"
                  placeholder="NY"
                  class="input-field disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
                />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">ZIP</label>
                <input
                  type="text"
                  v-model="profileData.zip_code"
                  :disabled="!editing"
                  placeholder="10001"
                  class="input-field disabled:bg-neutral-50 disabled:text-neutral-600 disabled:cursor-default"
                />
              </div>
            </div>

          </div>

          <!-- Actions -->
          <div v-if="editing" class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100">
            <button @click="handleCancel" class="btn-secondary py-2 px-5 text-sm">Cancel</button>
            <button @click="handleSaveProfile" :disabled="saving" class="btn-primary py-2 px-5 text-sm flex items-center gap-2">
              <span v-if="saving" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              <Save v-else :size="14" />
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>

        <!-- ── Right Column ── -->
        <div class="lg:col-span-2 flex flex-col gap-5">

          <!-- Change Password -->
          <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-neutral-100">
              <div class="w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center">
                <Key :size="17" class="text-primary-600" />
              </div>
              <div>
                <h2 class="font-bold text-neutral-900 text-base leading-none mb-0.5">Change Password</h2>
                <p class="text-neutral-500 text-xs">Update your security credentials</p>
              </div>
            </div>

            <div class="flex flex-col gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Current Password</label>
                <input
                  type="password"
                  v-model="passwordData.current_password"
                  placeholder="Enter current password"
                  class="input-field text-sm"
                  :class="{ 'border-red-400': errors.current_password }"
                  @input="errors.current_password = ''"
                />
                <span v-if="errors.current_password" class="text-xs text-red-500 font-mono">{{ errors.current_password }}</span>
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">New Password</label>
                <input
                  type="password"
                  v-model="passwordData.new_password"
                  placeholder="Min. 8 characters"
                  class="input-field text-sm"
                  :class="{ 'border-red-400': errors.new_password }"
                  @input="errors.new_password = ''"
                />
                <span v-if="errors.new_password" class="text-xs text-red-500 font-mono">{{ errors.new_password }}</span>
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="font-mono text-[10px] font-bold text-neutral-500 uppercase tracking-wider">Confirm New Password</label>
                <input
                  type="password"
                  v-model="passwordData.confirm_password"
                  placeholder="Re-enter new password"
                  class="input-field text-sm"
                  :class="{ 'border-red-400': errors.confirm_password }"
                  @input="errors.confirm_password = ''"
                />
                <span v-if="errors.confirm_password" class="text-xs text-red-500 font-mono">{{ errors.confirm_password }}</span>
              </div>

              <button
                @click="handleChangePassword"
                :disabled="saving"
                class="btn-primary w-full py-2.5 text-sm flex items-center justify-center gap-2 mt-1"
              >
                <span v-if="saving" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                <Lock v-else :size="14" />
                {{ saving ? 'Updating...' : 'Change Password' }}
              </button>
            </div>
          </div>

          <!-- Security Info -->
          <div class="bg-primary-50 border border-primary-100 rounded-2xl p-5">
            <div class="flex items-center gap-2.5 mb-3">
              <Shield :size="16" class="text-primary-600" />
              <h3 class="font-bold text-primary-900 text-sm">Security Tips</h3>
            </div>
            <ul class="flex flex-col gap-2">
              <li class="flex items-start gap-2 text-xs text-primary-700">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-400 mt-1.5 shrink-0"></span>
                Use at least 8 characters with a mix of letters and numbers
              </li>
              <li class="flex items-start gap-2 text-xs text-primary-700">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-400 mt-1.5 shrink-0"></span>
                Don't reuse passwords across different websites
              </li>
              <li class="flex items-start gap-2 text-xs text-primary-700">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-400 mt-1.5 shrink-0"></span>
                Use a password manager to stay secure
              </li>
            </ul>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
