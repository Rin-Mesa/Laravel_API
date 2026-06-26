# TODO - Pages Mapping With Backend

## Backend (no spec-alignment route changes; keep B)

- [ ] Add admin UI listing pages by using existing public endpoints where needed:
  - Admin Products: use GET /api/products + use existing POST/PUT/DELETE (already exists) to create/edit/delete.
  - Admin Categories: use GET /api/categories + use existing POST/PUT/DELETE (already exists).
  - Admin Orders: use GET /api/orders + PUT /api/orders/:id (controller already checks role).
- [ ] Ensure cart endpoints naming aligns between frontend and backend (/api/carts vs /api/cart).

## Frontend Routing

- [ ] Update frontend/src/router/index.ts to add:
  - /shop -> Shop.vue
  - /checkout -> Checkout.vue
  - /orders -> Orders.vue
  - /admin/dashboard (exists)
  - /admin/products (exists)
  - /admin/categories -> Categories.vue
  - /admin/orders -> AdminOrders.vue

## Frontend Customer Pages

- [ ] Create frontend/src/views/Customer/Shop.vue (search + category filter + pagination)
- [ ] Create frontend/src/views/Customer/Cart.vue (list/add/update/remove)
- [ ] Create frontend/src/views/Customer/Checkout.vue (customer fields + POST /api/orders)
- [ ] Create frontend/src/views/Customer/Orders.vue (order history)

## Frontend Admin Pages

- [ ] Create frontend/src/views/Admin/Categories.vue (table + Create/Edit/Delete)
- [ ] Create frontend/src/views/Admin/AdminOrders.vue (recent orders table + status update)

## Frontend Services

- [ ] Verify and fix frontend/src/services/api.ts endpoints to match backend route names (carts vs cart).
- [ ] Add any missing api helpers (if needed) for orders status updates / customer checkout.

## Validation / Testing

- [ ] Run frontend build/typecheck.
- [ ] Manually test:
  - Home -> Shop navigation
  - Shop filtering/pagination
  - Cart add/remove/update
  - Checkout submits order
  - Orders list shows
  - Admin pages render with admin user role
