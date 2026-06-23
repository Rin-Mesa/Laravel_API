const BASE_URL = 'http://localhost:8000/api';

function getHeaders(isMultipart = false): HeadersInit {
  const token = localStorage.getItem('precision_token');
  const headers: Record<string, string> = {
    'Accept': 'application/json',
  };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  if (!isMultipart) {
    headers['Content-Type'] = 'application/json';
  }
  return headers;
}

export async function request(endpoint: string, options: RequestInit = {}) {
  const isMultipart = options.body instanceof FormData;
  const url = `${BASE_URL}${endpoint}`;
  
  options.headers = {
    ...getHeaders(isMultipart),
    ...options.headers,
  };

  const response = await fetch(url, options);
  
  if (response.status === 401) {
    // Session expired or unauthenticated
    localStorage.removeItem('precision_token');
    localStorage.removeItem('precision_user');
    if (window.location.pathname !== '/login') {
      window.location.href = '/login';
    }
  }

  const json = await response.json();
  if (!response.ok) {
    throw new Error(json.message || 'API request failed');
  }
  return json;
}

export const api = {
  // Auth
  async login(credentials: any) {
    const res = await request('/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    });
    if (res.success && res.token) {
      localStorage.setItem('precision_token', res.token);
      localStorage.setItem('precision_user', JSON.stringify(res.user));
    }
    return res;
  },

  async logout() {
    try {
      await request('/logout', { method: 'POST' });
    } finally {
      localStorage.removeItem('precision_token');
      localStorage.removeItem('precision_user');
    }
  },

  async getProfile() {
    return request('/profile');
  },

  // Categories
  async getCategories() {
    return request('/categories');
  },

  // Products
  async getProducts() {
    return request('/products');
  },

  async getProduct(id: number | string) {
    return request(`/products/${id}`);
  },

  async createProduct(formData: FormData) {
    return request('/products', {
      method: 'POST',
      body: formData,
    });
  },

  async updateProduct(id: number | string, formData: FormData) {
    formData.append('_method', 'PUT');
    return request(`/products/${id}`, {
      method: 'POST',
      body: formData,
    });
  },

  async deleteProduct(id: number | string) {
    return request(`/products/${id}`, {
      method: 'DELETE',
    });
  },

  // Wishlist
  async getWishlist() {
    return request('/wishlists');
  },

  async addToWishlist(productId: number) {
    return request('/wishlists', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId }),
    });
  },

  async removeFromWishlist(wishlistId: number) {
    return request(`/wishlists/${wishlistId}`, {
      method: 'DELETE',
    });
  },

  // Cart
  async getCart() {
    return request('/carts');
  },

  async addToCart(productId: number, quantity: number = 1) {
    return request('/carts', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, quantity }),
    });
  },

  async updateCart(cartId: number, quantity: number) {
    return request(`/carts/${cartId}`, {
      method: 'PUT',
      body: JSON.stringify({ quantity }),
    });
  },

  async removeFromCart(cartId: number) {
    return request(`/carts/${cartId}`, {
      method: 'DELETE',
    });
  },

  // Orders
  async getOrders() {
    return request('/orders');
  },

  async createOrder(items: Array<{ product_id: number; quantity: number }>) {
    return request('/orders', {
      method: 'POST',
      body: JSON.stringify({ items }),
    });
  },

  async updateOrderStatus(orderId: number, status: string) {
    return request(`/orders/${orderId}`, {
      method: 'PUT',
      body: JSON.stringify({ status }),
    });
  },

  // Admin Dashboard
  async getDashboardStats() {
    return request('/admin/dashboard-stats');
  },
};
