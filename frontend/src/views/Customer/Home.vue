<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { store } from '../../store';
import { api } from '../../services/api';
import {
  ShoppingCart, Heart, Star, ArrowRight, Send, Grid, Share2, Settings, MapPin, Cpu
} from 'lucide-vue-next';

const loading = ref(true);
const products = ref<any[]>([]);

const fetchProducts = async () => {
  loading.value = true;
  try {
    const res = await api.getProducts();
    if (res.success) {
      products.value = Array.isArray(res.data) ? res.data : (res.data?.items ?? []);
    }
  } catch (e) {
    console.error('Failed to load storefront products', e);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchProducts();
});

const trendingSkus = ['HP-EP-02', 'KB-PT-K1', 'WA-VF-03', 'CPU-PC-09'];
const trendingProducts = computed(() => {
  return products.value.filter(p => trendingSkus.includes(p.sku));
});

const getBadge = (sku: string) => {
  if (sku === 'HP-EP-02') return { text: 'SALE', class: 'bg-gradient-to-br from-rose-500 to-red-600 shadow-rose-500/40 text-white' };
  if (sku === 'WA-VF-03') return { text: 'NEW', class: 'bg-gradient-to-br from-cyan-500 to-violet-600 shadow-cyan-500/40 text-white' };
  return null;
};

const getOriginalPrice = (sku: string) => {
  if (sku === 'HP-EP-02') return 150.00;
  return null;
};

const handleAddToCart = async (productId: number) => {
  await store.addToCart(productId, 1);
};

const handleToggleWishlist = async (productId: number) => {
  await store.toggleWishlist(productId);
};

const emailInput = ref('');
const handleSubscribe = () => {
  if (emailInput.value) {
    store.setAlert(`Successfully subscribed email: ${emailInput.value}`, 'success');
    emailInput.value = '';
  }
};
</script>

<template>
  <div class="flex flex-col gap-24 pb-20">

    <!-- HERO SECTION -->
    <section
      class="relative pt-24 pb-20 lg:pt-32 lg:pb-28 overflow-hidden rounded-[2.5rem] mt-6 bg-gradient-to-br from-cyan-900 via-zinc-950 to-violet-950 shadow-2xl shadow-cyan-900/20 border border-white/10 animate-fade-in mx-4 sm:mx-6 md:mx-0">

      <!-- Decorative background elements -->
      <div
        class="absolute -top-1/2 -right-1/4 w-[800px] h-[800px] bg-cyan-500/10 rounded-full blur-3xl animate-float pointer-events-none">
      </div>
      <div
        class="absolute -bottom-1/3 -left-1/4 w-[600px] h-[600px] bg-violet-500/10 rounded-full blur-3xl animate-pulse-glow pointer-events-none">
      </div>

      <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10 grid lg:grid-cols-2 gap-16 items-center">

        <div class="flex flex-col gap-8 max-w-2xl text-center lg:text-left mx-auto lg:mx-0">
          <div class="inline-block">
            <span
              class="font-bold text-xs md:text-sm tracking-[0.2em] text-white/90 uppercase bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-full border border-white/20">
              Next-Gen Interface Pack
            </span>
          </div>

          <h1
            class="font-display text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-[1.1] tracking-tight drop-shadow-lg">
            Precision<br /><span class="text-gradient">Performance</span><br />Defined.
          </h1>

          <p class="text-lg md:text-xl text-zinc-300 leading-relaxed max-w-xl mx-auto lg:mx-0">
            Experience the apex of retail technology. Our curated collection of high-fidelity gadgets and
            high-performance hardware is designed for those who demand excellence in every pixel.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 mt-4 justify-center lg:justify-start">
            <button class="btn-primary text-lg py-3.5 px-8 flex items-center justify-center gap-2 group"
              @click="$el.querySelector('#trending-products')?.scrollIntoView({ behavior: 'smooth' })">
              Shop Now
              <ArrowRight :size="18" class="group-hover:translate-x-1 transition-transform" />
            </button>
            <button class="btn-secondary bg-white/5 border-white/10 hover:bg-white/10 text-white text-lg py-3.5 px-8">
              View Showcase
            </button>
          </div>
        </div>

        <div class="hidden lg:block relative animate-float delay-300">
          <!-- Abstract representation of tech device -->
          <div class="relative w-[400px] h-[500px] mx-auto perspective-1000">
            <div
              class="absolute inset-0 bg-gradient-to-tr from-cyan-600/30 to-violet-600/30 rounded-[2.5rem] blur-2xl transform -rotate-6">
            </div>
            <div
              class="absolute inset-0 bg-zinc-900 border border-white/20 rounded-[2.5rem] shadow-2xl overflow-hidden backdrop-blur-sm transform rotate-3 transition-transform hover:rotate-0 duration-500">
              <!-- Screen content -->
              <div class="absolute inset-2 bg-zinc-950 rounded-[2rem] border border-white/5 overflow-hidden">
                <!-- UI Mockup inside -->
                <div class="h-16 border-b border-white/5 flex items-center px-6 gap-3">
                  <div class="w-8 h-8 rounded-full bg-cyan-500/20"></div>
                  <div class="h-3 w-24 bg-white/10 rounded-full"></div>
                </div>
                <div class="p-6 flex flex-col gap-4">
                  <div class="h-32 bg-gradient-to-br from-cyan-500/20 to-purple-500/20 rounded-xl"></div>
                  <div class="flex gap-4">
                    <div class="h-24 flex-1 bg-white/5 rounded-xl"></div>
                    <div class="h-24 flex-1 bg-white/5 rounded-xl"></div>
                  </div>
                  <div class="h-4 w-1/2 bg-white/10 rounded-full mt-4"></div>
                  <div class="h-3 w-1/3 bg-white/5 rounded-full"></div>
                </div>
              </div>
            </div>
            <!-- Floating Elements -->
            <div
              class="absolute -right-6 top-20 w-16 h-16 bg-cyan-500/20 border border-cyan-500/30 rounded-2xl backdrop-blur-md animate-pulse-glow flex items-center justify-center">
              <Cpu class="text-cyan-400" :size="24" />
            </div>
            <div
              class="absolute -left-8 bottom-32 w-20 h-20 bg-emerald-500/20 border border-emerald-500/30 rounded-2xl backdrop-blur-md animate-float flex items-center justify-center delay-700"
              style="animation-delay: 1s;">
              <Star class="text-emerald-400" :size="30" />
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- FEATURED CATEGORIES SECTION -->
    <section class="max-w-7xl mx-auto w-full">
      <h2
        class="font-display text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-10 text-gradient inline-block">
        Featured Categories</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 auto-rows-[280px]">

        <!-- 1. Computing (Large) -->
        <router-link to="/products"
          class="group relative md:col-span-2 md:row-span-2 rounded-[2rem] overflow-hidden glass-card p-8 flex flex-col justify-end border-0 bg-slate-900">
          <div
            class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=800&auto=format&fit=crop')] bg-cover bg-center opacity-40 group-hover:opacity-50 group-hover:scale-105 transition-all duration-700">
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
          <div class="relative z-10">
            <h3
              class="font-display text-3xl font-bold text-white mb-2 drop-shadow-md group-hover:-translate-y-1 transition-transform">
              High-Performance Computing</h3>
            <p class="text-zinc-300 font-medium mb-6">Laptops, Workstations & Gaming Rigs</p>
            <button
              class="bg-white/20 hover:bg-white/30 backdrop-blur-md text-white px-6 py-2.5 rounded-full font-semibold transition-all border border-white/30 shadow-lg">Explore
              Category</button>
          </div>
        </router-link>

        <!-- 2. Audio -->
        <router-link to="/products"
          class="group relative rounded-[2rem] overflow-hidden glass-card p-6 flex flex-col justify-end border-0 bg-slate-900">
          <div
            class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=600&auto=format&fit=crop')] bg-cover bg-center opacity-50 group-hover:opacity-60 group-hover:scale-105 transition-all duration-700">
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
          <div class="relative z-10 group-hover:-translate-y-1 transition-transform">
            <h3 class="font-display text-xl font-bold text-white mb-1">Immersive Audio</h3>
            <p class="text-zinc-300 text-sm">Noise Canceling & Hi-Fi</p>
          </div>
        </router-link>

        <!-- 3. Smart Living -->
        <router-link to="/products"
          class="group relative rounded-[2rem] overflow-hidden glass-card p-6 flex flex-col justify-end border-0 bg-slate-900">
          <div
            class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=600&auto=format&fit=crop')] bg-cover bg-center opacity-50 group-hover:opacity-60 group-hover:scale-105 transition-all duration-700">
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
          <div class="relative z-10 group-hover:-translate-y-1 transition-transform">
            <h3 class="font-display text-xl font-bold text-white mb-1">Smart Living</h3>
            <p class="text-zinc-300 text-sm">Automated controllers</p>
          </div>
        </router-link>

        <!-- 4. Photography -->
        <router-link to="/products"
          class="group relative rounded-[2rem] overflow-hidden glass-card p-6 flex flex-col justify-end border-0 bg-slate-900 md:col-span-2 lg:col-span-2">
          <div
            class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=800&auto=format&fit=crop')] bg-cover bg-center opacity-40 group-hover:opacity-50 group-hover:scale-105 transition-all duration-700">
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
          <div class="relative z-10 group-hover:-translate-y-1 transition-transform">
            <h3 class="font-display text-2xl font-bold text-white mb-1">Photography</h3>
            <p class="text-zinc-300">Cameras, lenses & mounts</p>
          </div>
        </router-link>

      </div>
    </section>

    <!-- TRENDING PRODUCTS SECTION -->
    <section id="trending-products" class="max-w-7xl mx-auto w-full">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
        <div>
          <h2 class="font-display text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2">Trending
            Products</h2>
          <p class="text-slate-500 dark:text-zinc-400">Most wanted electronics this week</p>
        </div>
        <router-link to="/products" class="btn-secondary bg-white text-sm dark:bg-zinc-900 flex items-center gap-2">
          View All Collection
          <ArrowRight :size="16" />
        </router-link>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4 text-zinc-500">
        <div class="w-10 h-10 border-4 border-cyan-500/30 border-t-cyan-500 rounded-full animate-spin"></div>
        <p class="font-medium animate-pulse">Curating catalog...</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="product in trendingProducts" :key="product.id"
          class="group flex flex-col glass-card border border-slate-200 dark:border-white/5 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 transition-all duration-300 hover:-translate-y-2 overflow-hidden bg-white dark:bg-zinc-900/50 relative">

          <!-- Image & Badges -->
          <div
            class="relative h-64 bg-slate-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden rounded-t-2xl">
            <span v-if="getBadge(product.sku)"
              :class="['absolute top-4 left-4 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-wider backdrop-blur-md z-10', getBadge(product.sku)?.class]">
              {{ getBadge(product.sku)?.text }}
            </span>

            <button @click="handleToggleWishlist(product.id)"
              class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/80 dark:bg-black/40 backdrop-blur-md border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-400 dark:text-zinc-400 hover:text-rose-500 dark:hover:text-rose-500 hover:scale-110 transition-all z-10 shadow-sm">
              <Heart :size="18" :class="{ 'fill-rose-500 text-rose-500': store.isWishlisted(product.id) }" />
            </button>

            <img :src="product.image_url" :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
              @error="($event.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop'" />
          </div>

          <!-- Product Details -->
          <div class="p-6 flex flex-col flex-1">
            <span class="text-[10px] font-bold text-cyan-500 uppercase tracking-wider mb-2">{{ product.category?.name ||
              'Electronics' }}</span>
            <h3
              class="font-display font-bold text-slate-900 dark:text-white text-lg leading-snug mb-3 group-hover:text-cyan-500 transition-colors">
              {{ product.name }}</h3>

            <div class="flex items-center gap-1.5 mb-4">
              <div class="flex text-amber-400">
                <Star v-for="i in 5" :key="i" :size="14" class="fill-current" />
              </div>
              <span class="text-xs text-slate-500 dark:text-zinc-400 font-medium">({{ 80 + product.id * 3 }})</span>
            </div>

            <div class="mt-auto flex items-center justify-between pt-4 border-t border-slate-100 dark:border-zinc-800">
              <div class="flex flex-col">
                <span class="font-display font-extrabold text-xl text-slate-900 dark:text-white">
                  {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(product.price) }}
                </span>
                <span v-if="getOriginalPrice(product.sku)"
                  class="text-sm text-slate-400 dark:text-zinc-500 line-through font-medium">
                  {{ new Intl.NumberFormat('en-US', {
                    style: 'currency', currency: 'USD'
                  }).format(getOriginalPrice(product.sku)!) }}
                </span>
              </div>

              <button @click="handleAddToCart(product.id)"
                class="w-10 h-10 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center hover:bg-cyan-600 dark:hover:bg-cyan-500 hover:text-white transition-colors shadow-md">
                <ShoppingCart :size="18" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- STAY AHEAD OF THE CURVE CTA -->
    <section class="max-w-5xl mx-auto w-full animate-fade-in-up">
      <div
        class="glass-card bg-gradient-to-br from-violet-900 to-cyan-900 border-violet-500/30 p-10 md:p-16 rounded-[2.5rem] text-center relative overflow-hidden shadow-2xl shadow-violet-900/20 mx-4 sm:mx-6 md:mx-0">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10">
        </div>
        <div class="relative z-10 flex flex-col items-center">
          <div
            class="w-16 h-16 bg-white/10 rounded-2xl backdrop-blur-lg border border-white/20 flex items-center justify-center mb-6 shadow-xl">
            <Send class="text-cyan-300" :size="32" />
          </div>
          <h2 class="font-display text-3xl md:text-5xl font-extrabold text-white mb-6 tracking-tight">Stay ahead of the
            curve.</h2>
          <p class="text-violet-100/80 text-lg md:text-xl max-w-2xl mb-10 leading-relaxed">
            Join our elite member list for early access to product launches, exclusive technical guides, and member-only
            pricing events.
          </p>
          <form @submit.prevent="handleSubscribe" class="flex flex-col sm:flex-row w-full max-w-md gap-3 mx-auto">
            <input type="email" placeholder="Enter your email address" v-model="emailInput" required
              class="flex-1 bg-white/10 border border-white/20 text-white placeholder:text-white/50 rounded-full px-6 py-3.5 focus:outline-none focus:ring-2 focus:ring-cyan-400/50 backdrop-blur-md font-medium shadow-inner" />
            <button type="submit"
              class="bg-white text-violet-900 hover:bg-cyan-50 font-bold py-3.5 px-8 rounded-full shadow-lg transition-transform hover:-translate-y-1">
              Subscribe
            </button>
          </form>
        </div>
      </div>
    </section>
  </div>
</template>
