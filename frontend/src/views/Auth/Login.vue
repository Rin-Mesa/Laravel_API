<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '../../store';
import { Sliders, Shield, User as UserIcon } from 'lucide-vue-next';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

onMounted(() => {
  // If already logged in, redirect
  if (store.user.value) {
    redirectBasedOnRole(store.user.value.role);
  }
});

const redirectBasedOnRole = (role: string) => {
  if (role === 'admin') {
    router.push('/admin/dashboard');
  } else {
    router.push('/');
  }
};

const handleLogin = async () => {
  error.value = '';
  loading.value = true;
  try {
    const res = await store.login({ email: email.value, password: password.value });
    if (res.success) {
      redirectBasedOnRole(res.user.role);
    }
  } catch (err: any) {
    error.value = err.message || 'Invalid email or password.';
  } finally {
    loading.value = false;
  }
};

const quickConnect = async (role: 'admin' | 'customer') => {
  error.value = '';
  loading.value = true;
  const targetEmail = role === 'admin' ? 'admin@precision.io' : 'alex@precision.io';
  try {
    const res = await store.login({ email: targetEmail, password: 'password' });
    if (res.success) {
      redirectBasedOnRole(res.user.role);
    }
  } catch (err: any) {
    error.value = 'Failed to connect. Make sure your database is seeded.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="login-wrapper">
    <div class="login-background"></div>
    <div class="login-card">
      <div class="login-header">
        <div class="logo">
          <Sliders :size="24" />
        </div>
        <h1>Precision Retail</h1>
        <p>Enterprise Commerce Management Suite</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="error" class="login-error">
          {{ error }}
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input 
            id="email"
            type="email" 
            v-model="email" 
            placeholder="admin@precision.io" 
            class="form-input" 
            required 
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input 
            id="password"
            type="password" 
            v-model="password" 
            placeholder="••••••••" 
            class="form-input" 
            required 
          />
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          {{ loading ? 'Authenticating...' : 'Sign In' }}
        </button>
      </form>

      <div class="login-divider">
        <span>Quick Connect Demo Accounts</span>
      </div>

      <div class="demo-buttons">
        <button class="demo-btn demo-admin" @click="quickConnect('admin')" :disabled="loading">
          <Shield :size="16" />
          <div class="demo-btn-text">
            <span class="title">Admin Dashboard</span>
            <span class="sub">admin@precision.io</span>
          </div>
        </button>

        <button class="demo-btn demo-customer" @click="quickConnect('customer')" :disabled="loading">
          <UserIcon :size="16" />
          <div class="demo-btn-text">
            <span class="title">Customer Storefront</span>
            <span class="sub">alex@precision.io</span>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background-color: #05070c;
  font-family: var(--font-body);
}

.login-background {
  position: absolute;
  top: -20%;
  left: -20%;
  width: 140%;
  height: 140%;
  background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(0, 0, 0, 0) 60%),
              radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.08) 0%, rgba(0, 0, 0, 0) 50%);
  filter: blur(80px);
  z-index: 1;
}

.login-card {
  width: 440px;
  background: rgba(17, 24, 39, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius-xl);
  padding: 48px;
  backdrop-filter: blur(20px);
  box-shadow: 0 20px 50px -12px rgba(0, 0, 0, 0.5);
  z-index: 2;
  animation: fadeInUp var(--transition-slow);
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.logo {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  border-radius: var(--radius-md);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: white;
  margin-bottom: 16px;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.login-header h1 {
  font-family: var(--font-display);
  font-size: 1.8rem;
  font-weight: 800;
  color: white;
  margin-bottom: 6px;
}

.login-header p {
  font-size: 0.85rem;
  color: #9ca3af;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.login-error {
  background-color: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  padding: 12px 16px;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  text-align: center;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.form-input {
  background-color: #0b0f19;
  border: 1px solid #1f2937;
  color: white;
  padding: 12px 16px;
  border-radius: var(--radius-sm);
  font-size: 0.95rem;
  transition: all var(--transition-fast);
}

.form-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.btn-block {
  width: 100%;
  padding: 12px;
  font-size: 0.95rem;
}

.login-divider {
  text-align: center;
  margin: 32px 0 20px 0;
  position: relative;
}

.login-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.08);
  z-index: 1;
}

.login-divider span {
  background-color: #111827;
  padding: 0 16px;
  color: #4b5563;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  position: relative;
  z-index: 2;
}

.demo-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.demo-btn {
  display: flex;
  align-items: center;
  gap: 16px;
  background-color: #111827;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-md);
  padding: 12px 20px;
  cursor: pointer;
  text-align: left;
  transition: all var(--transition-fast);
}

.demo-btn:hover {
  transform: translateY(-1px);
}

.demo-admin:hover {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

.demo-customer:hover {
  border-color: #10b981;
  background-color: rgba(16, 185, 129, 0.05);
}

.demo-btn .shield {
  color: #3b82f6;
}

.demo-btn .user-icon {
  color: #10b981;
}

.demo-btn-text {
  display: flex;
  flex-direction: column;
}

.demo-btn-text .title {
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
}

.demo-btn-text .sub {
  color: #4b5563;
  font-size: 0.75rem;
}
</style>
