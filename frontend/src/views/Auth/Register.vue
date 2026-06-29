<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '../../store';
import { api } from "../../services/api";
import { Eye, EyeOff, Check } from 'lucide-vue-next';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const agreeTerms = ref(false);
const showPassword = ref(false);
const showConfirm = ref(false);
const error = ref('');
const errors = ref<Record<string, string>>({});
const loading = ref(false);

const validateForm = () => {
  errors.value = {};
  if (!name.value.trim()) errors.value.name = 'Full name is required';
  if (!email.value.trim()) errors.value.email = 'Email is required';
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) errors.value.email = 'Enter a valid email';
  if (!password.value) errors.value.password = 'Password is required';
  else if (password.value.length < 6) errors.value.password = 'Minimum 6 characters';
  if (password.value !== confirmPassword.value) errors.value.confirmPassword = 'Passwords do not match';
  if (!agreeTerms.value) errors.value.terms = 'You must agree to the terms';
  return Object.keys(errors.value).length === 0;
};

const handleRegister = async () => {
  error.value = '';
  if (!validateForm()) return;
  loading.value = true;
  try {
    const res = await api.post('/register', {
      name: name.value, email: email.value,
      password: password.value, password_confirmation: confirmPassword.value, role: 'customer'
    });
    if (res.success) {
      if (res.token) {
        localStorage.setItem('precision_token', res.token);
        localStorage.setItem('precision_user', JSON.stringify(res.user));
        await store.init();
      }
      store.setAlert('Account created! Welcome to the shop.', 'success');
      router.push('/');
    }
  } catch (err: any) {
    const data = err?.response?.data;
    error.value = data?.message || err.message || 'Registration failed.';
    if (data?.errors) {
      for (const [field, msgs] of Object.entries(data.errors)) {
        const key = field === 'password_confirmation' ? 'confirmPassword' : field;
        const msg = (msgs as string[])[0];
        if (key && msg) errors.value[key] = msg;
      }
    }
  } finally {
    loading.value = false;
  }
};

const handleSocialLogin = (provider: string) => {
  store.setAlert(`${provider} registration coming soon`, 'info');
};

const passwordStrength = () => {
  const p = password.value;
  if (!p) return 0;
  let score = 0;
  if (p.length >= 8) score++;
  if (/[A-Z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score;
};
</script>

<template>
  <main class="flex-1 w-full flex items-center justify-center bg-primary-50 p-4 md:p-10 min-h-[calc(100vh-80px)]">
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-lg border border-neutral-100 overflow-hidden flex flex-col md:flex-row animate-fade-in-up">

      <!-- ── Left Branding Panel ── -->
      <div class="md:w-5/12 relative bg-secondary-500 p-10 flex flex-col justify-between overflow-hidden min-h-[360px]">
        <!-- Decorative circles -->
        <div class="absolute -top-16 -right-16 w-64 h-64 bg-secondary-400/40 rounded-full"></div>
        <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-secondary-600/40 rounded-full"></div>
        <!-- Photo overlay -->
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?q=80&w=900&auto=format&fit=crop')] bg-cover bg-center opacity-10"></div>

        <!-- Top badge -->
        <div class="relative z-10">
          <span class="bg-white/20 text-white text-[10px] font-mono font-bold px-3 py-1 rounded border border-white/30 uppercase tracking-widest">New Member</span>
        </div>

        <!-- Bottom copy -->
        <div class="relative z-10 mt-auto">
          <h1 class="text-3xl md:text-4xl font-bold text-white leading-snug mb-3">
            Join the<br>Community
          </h1>
          <p class="text-secondary-100 text-sm leading-relaxed">
            Unlock exclusive access to limited collections, early deals, and a personalized shopping experience.
          </p>

          <!-- Member stats -->
          <div class="mt-6 grid grid-cols-3 gap-3">
            <div class="bg-white/15 rounded-lg p-3 text-center border border-white/20">
              <div class="text-white font-bold text-lg font-mono">10k+</div>
              <div class="text-secondary-100 text-[10px] font-mono">Members</div>
            </div>
            <div class="bg-white/15 rounded-lg p-3 text-center border border-white/20">
              <div class="text-white font-bold text-lg font-mono">50k+</div>
              <div class="text-secondary-100 text-[10px] font-mono">Products</div>
            </div>
            <div class="bg-white/15 rounded-lg p-3 text-center border border-white/20">
              <div class="text-white font-bold text-lg font-mono">4.9★</div>
              <div class="text-secondary-100 text-[10px] font-mono">Rating</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Right Form Panel ── -->
      <div class="md:w-7/12 p-8 md:p-10 flex flex-col justify-center overflow-y-auto">

        <div class="mb-6">
          <p class="font-mono text-xs text-neutral-400 tracking-widest uppercase mb-1">Get started free</p>
          <h2 class="text-2xl font-bold text-neutral-900 mb-1">Create Account</h2>
          <p class="text-neutral-500 text-sm">Enter your details to start your journey with Indigo.</p>
        </div>

        <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-lg font-medium">
            {{ error }}
          </div>

          <!-- Full Name -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-neutral-600 uppercase tracking-wider font-mono">Full Name</label>
            <input type="text" v-model="name" placeholder="John Doe" class="input-field" :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/20': errors.name }" required />
            <span v-if="errors.name" class="text-xs text-red-500 font-mono">{{ errors.name }}</span>
          </div>

          <!-- Email -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-neutral-600 uppercase tracking-wider font-mono">Email Address</label>
            <input type="email" v-model="email" placeholder="john@example.com" class="input-field" :class="{ 'border-red-400': errors.email }" required />
            <span v-if="errors.email" class="text-xs text-red-500 font-mono">{{ errors.email }}</span>
          </div>

          <!-- Password -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-neutral-600 uppercase tracking-wider font-mono">Password</label>
            <div class="relative">
              <input :type="showPassword ? 'text' : 'password'" v-model="password" placeholder="••••••••" class="input-field pr-10" :class="{ 'border-red-400': errors.password }" required />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700">
                <EyeOff v-if="showPassword" :size="16" /><Eye v-else :size="16" />
              </button>
            </div>
            <!-- Strength bar -->
            <div v-if="password" class="flex gap-1 mt-1">
              <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full transition-colors duration-300" :class="[
                passwordStrength() >= i
                  ? i <= 1 ? 'bg-red-400' : i <= 2 ? 'bg-tertiary-600' : i <= 3 ? 'bg-amber-400' : 'bg-secondary-500'
                  : 'bg-neutral-200'
              ]"></div>
            </div>
            <span v-if="errors.password" class="text-xs text-red-500 font-mono">{{ errors.password }}</span>
          </div>

          <!-- Confirm Password -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-neutral-600 uppercase tracking-wider font-mono">Confirm Password</label>
            <div class="relative">
              <input :type="showConfirm ? 'text' : 'password'" v-model="confirmPassword" placeholder="••••••••" class="input-field pr-10" :class="{ 'border-red-400': errors.confirmPassword }" required />
              <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700">
                <EyeOff v-if="showConfirm" :size="16" /><Eye v-else :size="16" />
              </button>
            </div>
            <span v-if="errors.confirmPassword" class="text-xs text-red-500 font-mono">{{ errors.confirmPassword }}</span>
          </div>

          <!-- Terms -->
          <div class="flex flex-col gap-1">
            <label class="flex items-start gap-2.5 cursor-pointer">
              <input type="checkbox" v-model="agreeTerms" class="w-4 h-4 mt-0.5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500" />
              <span class="text-xs text-neutral-600 leading-relaxed">
                I agree to the <a href="#" class="font-semibold text-primary-600 hover:underline">Terms and Conditions</a> and <a href="#" class="font-semibold text-primary-600 hover:underline">Privacy Policy</a>.
              </span>
            </label>
            <span v-if="errors.terms" class="text-xs text-red-500 font-mono pl-6">{{ errors.terms }}</span>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn-success w-full py-3 mt-1 text-sm" :disabled="loading">
            <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <Check v-else :size="16" />
            {{ loading ? 'Creating Account...' : 'Create Account' }}
          </button>
        </form>

        <!-- Social -->
        <div class="flex items-center gap-3 my-5">
          <div class="flex-1 h-px bg-neutral-200"></div>
          <span class="font-mono text-[10px] text-neutral-400 uppercase tracking-widest">Or register with</span>
          <div class="flex-1 h-px bg-neutral-200"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <button type="button" @click="handleSocialLogin('Google')" class="btn-secondary text-xs py-2.5 justify-center">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-4 h-4" alt="Google" /> Google
          </button>
          <button type="button" @click="handleSocialLogin('Facebook')" class="btn-secondary text-xs py-2.5 justify-center">
            <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-4 h-4" alt="Facebook" /> Facebook
          </button>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-5 border-t border-neutral-100 text-center">
          <p class="text-sm text-neutral-500">
            Already have an account?
            <router-link to="/login" class="font-semibold text-primary-600 hover:text-primary-700 ml-1">Sign in →</router-link>
          </p>
        </div>
      </div>

    </div>
  </main>
</template>
