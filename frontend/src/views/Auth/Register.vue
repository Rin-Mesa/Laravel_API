<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '../../store';
import { api } from "../../services/api";
import { ShoppingBag, Check, Facebook, Chrome } from 'lucide-vue-next';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirm = ref('');
const agreeTerms = ref(false);
const loading = ref(false);
const error = ref('');
const errors = ref<Record<string, string>>({});

const validateForm = () => {
  errors.value = {};
  
  if (!name.value.trim()) {
    errors.value.name = 'Full name is required';
  } else if (name.value.trim().length < 2) {
    errors.value.name = 'Name must be at least 2 characters';
  }
  
  if (!email.value.trim()) {
    errors.value.email = 'Email address is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    errors.value.email = 'Please enter a valid email address';
  }
  
  if (!password.value) {
    errors.value.password = 'Password is required';
  } else if (password.value.length < 8) {
    errors.value.password = 'Password must be at least 8 characters';
  }
  
  if (!passwordConfirm.value) {
    errors.value.passwordConfirm = 'Please confirm your password';
  } else if (password.value !== passwordConfirm.value) {
    errors.value.passwordConfirm = 'Passwords do not match';
  }
  
  if (!agreeTerms.value) {
    errors.value.agreeTerms = 'You must agree to the Terms and Conditions';
  }
  
  return Object.keys(errors.value).length === 0;
};

const handleRegister = async () => {
  error.value = '';
  
  if (!validateForm()) {
    return;
  }
  
  loading.value = true;
  try {
    const res = await api.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
    });
    if (res?.success) {
      store.setAlert('Account created successfully! Please sign in.', 'success');
      router.push('/login');
    } else {
      error.value = res?.message || 'Registration failed.';
    }
  } catch (e: any) {
    const data = e?.response?.data;
    const firstField = data?.errors ? Object.keys(data.errors)[0] : undefined;
    const firstError = firstField ? data?.errors?.[firstField]?.[0] : undefined;

    error.value = data?.message || firstError || 'Registration failed. Please try again.';
  } finally {
    loading.value = false;
  }
};

const handleSocialLogin = (provider: 'google' | 'facebook') => {
  store.setAlert(`${provider.charAt(0).toUpperCase() + provider.slice(1)} login coming soon`, 'info');
};
</script>

<template>
  <div class="auth-page">
    <div class="auth-bg"></div>
    <div class="auth-card">
      <div class="auth-logo">
        <ShoppingBag :size="28" />
      </div>
      <h1 class="auth-title">Create Account</h1>
      <p class="auth-subtitle">Join Precision Retail today</p>

      <div v-if="error" class="auth-error">{{ error }}</div>

      <form @submit.prevent="handleRegister" class="auth-form">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input 
            v-model="name" 
            type="text" 
            class="form-input" 
            :class="{ 'input-error': errors.name }"
            placeholder="John Doe" 
            @input="errors.name = ''"
          />
          <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input 
            v-model="email" 
            type="email" 
            class="form-input" 
            :class="{ 'input-error': errors.email }"
            placeholder="you@example.com" 
            @input="errors.email = ''"
          />
          <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input 
            v-model="password" 
            type="password" 
            class="form-input" 
            :class="{ 'input-error': errors.password }"
            placeholder="Min. 8 characters"
            @input="errors.password = ''"
          />
          <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input 
            v-model="passwordConfirm" 
            type="password" 
            class="form-input" 
            :class="{ 'input-error': errors.passwordConfirm }"
            placeholder="Re-enter password"
            @input="errors.passwordConfirm = ''"
          />
          <span v-if="errors.passwordConfirm" class="field-error">{{ errors.passwordConfirm }}</span>
        </div>

        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input 
              type="checkbox" 
              v-model="agreeTerms" 
              class="checkbox-input"
              @change="errors.agreeTerms = ''"
            />
            <span class="checkbox-custom">
              <Check :size="14" v-if="agreeTerms" />
            </span>
            <span class="checkbox-text">
              I agree to the <a href="#" class="link">Terms and Conditions</a> and <a href="#" class="link">Privacy Policy</a>
            </span>
          </label>
          <span v-if="errors.agreeTerms" class="field-error">{{ errors.agreeTerms }}</span>
        </div>

        <button type="submit" class="btn btn-primary auth-submit-btn" :disabled="loading">
          <span v-if="loading" class="spinner-sm"></span>
          <span>{{ loading ? 'Creating account...' : 'Create Account' }}</span>
        </button>
      </form>

      <div class="social-divider">
        <span>Or continue with</span>
      </div>

      <div class="social-buttons">
        <button type="button" class="social-btn google-btn" @click="handleSocialLogin('google')">
          <Chrome :size="20" />
          <span>Google</span>
        </button>
        <button type="button" class="social-btn facebook-btn" @click="handleSocialLogin('facebook')">
          <Facebook :size="20" />
          <span>Facebook</span>
        </button>
      </div>

      <p class="auth-switch">
        Already have an account?
        <router-link to="/login" class="auth-link">Login here</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0b0f19 0%, #111827 50%, #0b0f19 100%);
  padding: 24px;
  position: relative;
  overflow: hidden;
}

.auth-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.15) 0%, transparent 60%),
    radial-gradient(circle at 80% 70%, rgba(99, 102, 241, 0.12) 0%, transparent 55%);
  pointer-events: none;
}

.auth-card {
  position: relative;
  width: 100%;
  max-width: 440px;
  background: rgba(17, 24, 39, 0.8);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.4);
  animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-logo {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  margin-bottom: 24px;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
}

.auth-title {
  font-family: var(--font-display);
  font-size: 1.75rem;
  font-weight: 800;
  color: #f9fafb;
  margin-bottom: 6px;
}

.auth-subtitle {
  font-size: 0.875rem;
  color: #9ca3af;
  margin-bottom: 28px;
}

.auth-error {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.25);
  color: #fca5a5;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-bottom: 20px;
}

.field-error {
  color: #fca5a5;
  font-size: 0.75rem;
  margin-top: 4px;
}

.input-error {
  border-color: #ef4444 !important;
}

.input-error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #9ca3af;
}

.form-input {
  background: rgba(255, 255, 255, 0.05);
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  color: #f9fafb;
  padding: 11px 14px;
  font-size: 0.9rem;
  width: 100%;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.form-input::placeholder {
  color: #4b5563;
}

.auth-submit-btn {
  width: 100%;
  justify-content: center;
  padding: 12px;
  font-size: 0.95rem;
  margin-top: 4px;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
}

.auth-submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
}

.spinner-sm {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.auth-switch {
  text-align: center;
  margin-top: 20px;
  font-size: 0.85rem;
  color: #9ca3af;
}

.auth-link {
  color: #3b82f6;
  font-weight: 600;
  text-decoration: none;
}

.auth-link:hover {
  color: #60a5fa;
  text-decoration: underline;
}

.checkbox-group {
  gap: 8px;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.checkbox-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.checkbox-custom {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.05);
  transition: all 0.15s ease;
  flex-shrink: 0;
  color: white;
}

.checkbox-label:hover .checkbox-custom {
  border-color: #3b82f6;
}

.checkbox-input:checked + .checkbox-custom {
  background: #3b82f6;
  border-color: #3b82f6;
}

.checkbox-text {
  font-size: 0.85rem;
  color: #9ca3af;
  line-height: 1.4;
}

.checkbox-text .link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.checkbox-text .link:hover {
  text-decoration: underline;
}

.social-divider {
  text-align: center;
  margin: 24px 0 20px 0;
  position: relative;
}

.social-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.08);
  z-index: 1;
}

.social-divider span {
  background-color: #111827;
  padding: 0 16px;
  color: #4b5563;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  position: relative;
  z-index: 2;
}

.social-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 24px;
}

.social-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #f9fafb;
}

.social-btn:hover {
  transform: translateY(-1px);
}

.google-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
}

.facebook-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
}
</style>
