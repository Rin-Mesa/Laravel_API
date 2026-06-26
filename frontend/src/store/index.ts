import { reactive, computed } from 'vue';
import { api } from '../services/api';

interface State {
  user: any | null;
  token: string | null;
  cart: any[];
  wishlist: any[];
  categories: any[];
  products: any[];
  alert: { message: string; type: 'success' | 'error' | 'info' } | null;
}

const state = reactive<State>({
  user: null,
  token: null,
  cart: [],
  wishlist: [],
  categories: [],
  products: [],
  alert: null,
});

let alertTimeout: number | null = null;

export const store = {
  // Getters
  user: computed(() => state.user),
  token: computed(() => state.token),
  cart: computed(() => state.cart),
  wishlist: computed(() => state.wishlist),
  categories: computed(() => state.categories),
  products: computed(() => state.products),
  alert: computed(() => state.alert),
  
  cartCount: computed(() => state.cart.reduce((sum, item) => sum + item.quantity, 0)),
  cartSubtotal: computed(() => state.cart.reduce((sum, item) => sum + (item.product?.price || 0) * item.quantity, 0)),
  
  // Setters/Actions
  setAlert(message: string, type: 'success' | 'error' | 'info' = 'success') {
    state.alert = { message, type };
    if (alertTimeout) clearTimeout(alertTimeout);
    alertTimeout = window.setTimeout(() => {
      state.alert = null;
    }, 4000);
  },

  clearAlert() {
    state.alert = null;
  },

  async init() {
    const token = localStorage.getItem('precision_token');
    const userStr = localStorage.getItem('precision_user');
    if (token && userStr) {
      state.token = token;
      state.user = JSON.parse(userStr);
      
      try {
        await Promise.all([
          this.fetchCart(),
          this.fetchWishlist()
        ]);
      } catch (err) {
        console.error('Failed to fetch user cart/wishlist', err);
      }
    }
    
    // Always fetch categories & products
    await Promise.all([
      this.fetchCategories(),
      this.fetchProducts()
    ]);
  },

  async login(credentials: any) {
    const res = await api.login(credentials);
    if (res.success) {
      state.token = res.token;
      state.user = res.user;
      this.setAlert('Logged in successfully', 'success');
      await Promise.all([
        this.fetchCart(),
        this.fetchWishlist()
      ]);
    }
    return res;
  },

  async logout() {
    await api.logout();
    state.token = null;
    state.user = null;
    state.cart = [];
    state.wishlist = [];
    this.setAlert('Logged out successfully', 'info');
  },

  async fetchUser() {
    if (!state.token) return;
    try {
      const res = await api.getProfile();
      if (res.success) {
        state.user = res.data;
        localStorage.setItem('precision_user', JSON.stringify(res.data));
      }
    } catch (err) {
      console.error('Failed to fetch user profile', err);
    }
  },

  async switchUser(role: 'admin' | 'customer') {
    // Quick helper to switch users for demonstration
    const email = role === 'admin' ? 'admin@precision.io' : 'alex@precision.io';
    try {
      const res = await api.login({ email, password: 'password' });
      if (res.success) {
        state.token = res.token;
        state.user = res.user;
        this.setAlert(`Switched view to ${role === 'admin' ? 'Admin' : 'Customer'} Mode`, 'info');
        await Promise.all([
          this.fetchCart(),
          this.fetchWishlist(),
          this.fetchProducts()
        ]);
        return true;
      }
    } catch (e) {
      this.setAlert('Failed to switch user mode', 'error');
    }
    return false;
  },

  // Cart actions
  async fetchCart() {
    if (!state.token) return;
    const res = await api.getCart();
    if (res.success) {
      state.cart = res.data;
    }
  },

  async addToCart(productId: number, quantity: number = 1) {
    if (!state.token) {
      this.setAlert('Please login to add items to cart', 'error');
      return false;
    }
    try {
      const res = await api.addToCart(productId, quantity);
      if (res.success) {
        await this.fetchCart();
        this.setAlert('Item added to cart', 'success');
        return true;
      }
    } catch (err: any) {
      this.setAlert(err.message || 'Failed to add item to cart', 'error');
    }
    return false;
  },

  async updateCartQuantity(cartId: number, quantity: number) {
    try {
      const res = await api.updateCart(cartId, quantity);
      if (res.success) {
        await this.fetchCart();
        return true;
      }
    } catch (err: any) {
      this.setAlert(err.message || 'Failed to update quantity', 'error');
    }
    return false;
  },

  async removeFromCart(cartId: number) {
    try {
      const res = await api.removeFromCart(cartId);
      if (res.success) {
        await this.fetchCart();
        this.setAlert('Item removed from cart', 'success');
        return true;
      }
    } catch (err: any) {
      this.setAlert(err.message || 'Failed to remove item', 'error');
    }
    return false;
  },

  // Wishlist actions
  async fetchWishlist() {
    if (!state.token) return;
    const res = await api.getWishlist();
    if (res.success) {
      state.wishlist = res.data;
    }
  },

  async toggleWishlist(productId: number) {
    if (!state.token) {
      this.setAlert('Please login to save items to wishlist', 'error');
      return false;
    }
    
    const existing = state.wishlist.find(w => w.product_id === productId);
    try {
      if (existing) {
        const res = await api.removeFromWishlist(existing.id);
        if (res.success) {
          await this.fetchWishlist();
          this.setAlert('Removed from wishlist', 'success');
          return true;
        }
      } else {
        const res = await api.addToWishlist(productId);
        if (res.success) {
          await this.fetchWishlist();
          this.setAlert('Saved to wishlist', 'success');
          return true;
        }
      }
    } catch (err: any) {
      this.setAlert(err.message || 'Failed to toggle wishlist', 'error');
    }
    return false;
  },

  isWishlisted(productId: number): boolean {
    return state.wishlist.some(w => w.product_id === productId);
  },

  // Reviews
  async createReview(productId: number, rating: number, comment: string) {
    if (!state.token) {
      this.setAlert("Please login to submit a review", "error");
      return false;
    }
    try {
      const res = await api.createReview({ product_id: productId, rating, comment });
      if (res.success) {
        this.setAlert("Review submitted!", "success");
        return res.data;
      }
    } catch (err: any) {
      this.setAlert(err.response?.data?.message || "Failed to submit review", "error");
    }
    return false;
  },

  async updateReview(reviewId: number, rating: number, comment: string) {
    try {
      const res = await api.updateReview(reviewId, { rating, comment });
      if (res.success) {
        this.setAlert("Review updated!", "success");
        return res.data;
      }
    } catch (err: any) {
      this.setAlert(err.response?.data?.message || "Failed to update review", "error");
    }
    return false;
  },

  async deleteReview(reviewId: number) {
    try {
      const res = await api.deleteReview(reviewId);
      if (res.success) {
        this.setAlert("Review deleted!", "success");
        return true;
      }
    } catch (err: any) {
      this.setAlert(err.response?.data?.message || "Failed to delete review", "error");
    }
    return false;
  },

  // Categories & Products
  async fetchCategories() {
    const res = await api.getCategories();
    if (res.success) {
      state.categories = res.data;
    }
  },

  async fetchProducts() {
    const res = await api.getProducts();
    if (res.success) {
      state.products = res.data;
    }
  },
};
