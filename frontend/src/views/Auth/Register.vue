<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '../../stores';
import * as authService from "../../services/auth";
import { ShoppingBag } from 'lucide-vue-next';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirm = ref('');
const loading = ref(false);
const error = ref('');

const handleRegister = async () => {
  error.value = '';
  if (password.value !== passwordConfirm.value) {
    error.value = 'Passwords do not match.';
    return;
  }
  loading.value = true;
  try {
    const res = await authService.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
    });
    const payload = res?.data;
    if (payload?.success) {
      store.setAlert('Account created! Please sign in.', 'success');
      router.push('/login');
    } else {
      error.value = payload?.message || 'Registration failed.';
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
          <input v-model="name" type="text" class="form-input" placeholder="John Doe" required />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="email" type="email" class="form-input" placeholder="you@example.com" required />
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input v-model="password" type="password" class="form-input" placeholder="Min. 8 characters" required
            minlength="8" />
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input v-model="passwordConfirm" type="password" class="form-input" placeholder="Re-enter password"
            required />
        </div>
        <button type="submit" class="btn btn-primary auth-submit-btn" :disabled="loading">
          <span v-if="loading" class="spinner-sm"></span>
          <span>{{ loading ? 'Creating account...' : 'Create Account' }}</span>
        </button>
      </form>

      <p class="auth-switch">
        Already have an account?
        <router-link to="/login" class="auth-link">Sign in</router-link>
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
</style>
