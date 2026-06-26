import axios, { type AxiosInstance, type AxiosRequestConfig } from "axios";

const BASE_URL = "http://127.0.0.1:8000/api";

const http: AxiosInstance = axios.create({
  baseURL: BASE_URL,
  headers: { Accept: "application/json" },
});

http.interceptors.request.use((config) => {
  const token = localStorage.getItem("precision_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (!(config.data instanceof FormData)) {
    config.headers["Content-Type"] = "application/json";
  }
  return config;
});

http.interceptors.response.use(
  (res) => res,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("precision_token");
      localStorage.removeItem("precision_user");
      if (window.location.pathname !== "/login") {
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  },
);

async function request<T = any>(
  endpoint: string,
  options: AxiosRequestConfig = {},
): Promise<T> {
  const response = await http({ url: endpoint, ...options });
  return response.data;
}

export const api = {
  http,

  get<T = any>(endpoint: string, config?: AxiosRequestConfig) {
    return request<T>(endpoint, { method: "GET", ...config });
  },

  post<T = any>(endpoint: string, data?: any, config?: AxiosRequestConfig) {
    return request<T>(endpoint, { method: "POST", data, ...config });
  },

  put<T = any>(endpoint: string, data?: any, config?: AxiosRequestConfig) {
    return request<T>(endpoint, { method: "PUT", data, ...config });
  },

  delete<T = any>(endpoint: string, config?: AxiosRequestConfig) {
    return request<T>(endpoint, { method: "DELETE", ...config });
  },

  // Auth
  async login(credentials: any) {
    const res = await this.post("/login", credentials);
    if (res.success && res.token) {
      localStorage.setItem("precision_token", res.token);
      localStorage.setItem("precision_user", JSON.stringify(res.user));
    }
    return res;
  },

  async register(data: any) {
    return this.post("/register", data);
  },

  async logout() {
    try {
      await this.post("/logout");
    } finally {
      localStorage.removeItem("precision_token");
      localStorage.removeItem("precision_user");
    }
  },

  async getProfile() {
    return this.get("/profile");
  },

  async updateProfile(data: any) {
    return this.put("/profile", data);
  },

  async changePassword(data: any) {
    return this.put("/change-password", data);
  },

  // Categories
  async getCategories() {
    return this.get("/categories");
  },

  async createCategory(data: any) {
    return this.post("/categories", data);
  },

  async updateCategory(id: number, data: any) {
    return this.put(`/categories/${id}`, data);
  },

  async deleteCategory(id: number) {
    return this.delete(`/categories/${id}`);
  },

  // Products
  async getProducts() {
    return this.get("/products");
  },

  async getProduct(id: number | string) {
    return this.get(`/products/${id}`);
  },

  async createProduct(formData: FormData) {
    return this.post("/products", formData);
  },

  async updateProduct(id: number | string, formData: FormData) {
    formData.append("_method", "PUT");
    return this.post(`/products/${id}`, formData);
  },

  async deleteProduct(id: number | string) {
    return this.delete(`/products/${id}`);
  },

  // Wishlist
  async getWishlist() {
    return this.get("/wishlists");
  },

  async addToWishlist(productId: number) {
    return this.post("/wishlists", { product_id: productId });
  },

  async removeFromWishlist(wishlistId: number) {
    return this.delete(`/wishlists/${wishlistId}`);
  },

  // Cart
  async getCart() {
    return this.get("/carts");
  },

  // Backward-compatible alias for spec text: GET /api/cart
  async getCartItems() {
    return this.getCart();
  },

  async addToCart(productId: number, quantity: number = 1) {
    return this.post("/carts", { product_id: productId, quantity });
  },

  async updateCart(cartId: number, quantity: number) {
    return this.put(`/carts/${cartId}`, { quantity });
  },

  async removeFromCart(cartId: number) {
    return this.delete(`/carts/${cartId}`);
  },

  // Backward-compatible alias for spec text: DELETE /api/cart/:id
  async deleteCartItem(cartId: number) {
    return this.removeFromCart(cartId);
  },

  // Orders
  async getOrders() {
    return this.get("/orders");
  },

  async createOrder(items: Array<{ product_id: number; quantity: number }>) {
    return this.post("/orders", { items });
  },

  async updateOrderStatus(orderId: number, status: string) {
    return this.put(`/orders/${orderId}`, { status });
  },

  // Reviews
  async createReview(data: {
    product_id: number;
    rating: number;
    comment?: string;
  }) {
    return this.post("/reviews", data);
  },

  async updateReview(id: number, data: { rating: number; comment?: string }) {
    return this.put(`/reviews/${id}`, data);
  },

  async deleteReview(id: number) {
    return this.delete(`/reviews/${id}`);
  },

  // Admin
  async getDashboardStats() {
    return this.get("/admin/dashboard-stats");
  },

  async getUsers() {
    return this.get("/admin/users");
  },

  async createUser(data: any) {
    return this.post("/admin/users", data);
  },

  async updateUser(id: number, data: any) {
    return this.put(`/admin/users/${id}`, data);
  },

  async deleteUser(id: number) {
    return this.delete(`/admin/users/${id}`);
  },
};

export default api;
