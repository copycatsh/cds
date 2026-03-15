# Phase 2: React Frontend Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a React SPA that consumes the CDS backend API — order and client CRUD with polished UI.

**Architecture:** Vite + React 18 SPA served by nginx. API calls proxied to php-fpm at `/api/*`. Thin `api.ts` fetch wrapper with `ApiError` class, React Query hooks per resource, MSW-based tests. Frontend runs as a Docker service (Vite dev server for dev, built assets for prod).

**Tech Stack:** React 18, TypeScript, Vite, Tailwind CSS, Framer Motion, React Query (TanStack Query), React Router, Vitest, React Testing Library, MSW

**Completed:** Phase 1 backend (all endpoints working, 55 tests passing)

**Dependency graph:**
```
M1 (Infra)  ──→ M2 (API + Types) ──→ M3 (Layout + Routing)
                      │                       │
                      ▼                       ▼
                 M4 (Client pages) ──→ M5 (Order pages)
                                              │
                                              ▼
                                       M6 (Tests)
                                              │
                                              ▼
                                       M7 (Polish + Framer Motion)
```

---

## Milestone 1: Infrastructure — /api prefix + Docker + nginx

### Task 1.1: Add /api prefix to backend routes

**Files:**
- Modify: `public/index.php:23-30`

**Step 1: Update all 7 route registrations to add /api prefix**

Change:
```php
$r->addRoute('GET', '/clients', [$clients, 'index']);
```
To:
```php
$r->addRoute('GET', '/api/clients', [$clients, 'index']);
```

Apply to all 7 routes.

**Step 2: Run backend tests**

Run: `docker compose exec php vendor/bin/phpunit`
Expected: 55 tests pass (tests call controllers directly, not through HTTP routes)

**Step 3: Verify via curl**

```bash
curl -s http://localhost:8080/api/clients
curl -s http://localhost:8080/clients  # should return 404
```

**Step 4: Commit**

```bash
git add public/index.php
git commit -m "feat: add /api prefix to all backend routes"
```

---

### Task 1.2: Update nginx config for SPA + API split

**Files:**
- Modify: `docker/nginx/default.conf`

**Step 1: Rewrite nginx config**

```nginx
server {
    listen 80;
    server_name localhost;

    # API routes → PHP backend
    location /api/ {
        fastcgi_pass php:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/public/index.php;
        fastcgi_param DOCUMENT_ROOT /var/www/public;
        fastcgi_param REQUEST_URI $request_uri;
    }

    # Frontend static files (prod build)
    root /var/www/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

**Step 2: Commit**

```bash
git add docker/nginx/default.conf
git commit -m "feat: nginx SPA + /api proxy split"
```

---

### Task 1.3: Add frontend Docker service

**Files:**
- Modify: `compose.yaml` — add frontend service
- Modify: `Makefile` — add frontend targets

**Step 1: Add frontend service to compose.yaml**

Add a Node 20 service that runs Vite dev server. Mount frontend/ directory. Proxy /api to nginx.

**Step 2: Add Makefile targets**

```makefile
install-frontend:
	docker compose exec frontend npm install

dev-frontend:
	docker compose exec frontend npm run dev

build-frontend:
	docker compose exec frontend npm run build

test-frontend:
	docker compose exec frontend npm run test
```

**Step 3: Commit**

```bash
git add compose.yaml Makefile
git commit -m "feat: add frontend Docker service and Makefile targets"
```

---

### Task 1.4: Scaffold Vite + React + TypeScript project

**Files:**
- Create: `frontend/` directory with Vite scaffold
- Create: `frontend/package.json`
- Create: `frontend/vite.config.ts` — with /api proxy to nginx
- Create: `frontend/tsconfig.json`
- Create: `frontend/tailwind.config.js`
- Create: `frontend/postcss.config.js`
- Create: `frontend/index.html`
- Create: `frontend/src/main.tsx` — minimal React mount
- Create: `frontend/src/App.tsx` — "Hello CDS" placeholder

**Step 1: Create all scaffold files**

vite.config.ts must include proxy:
```typescript
export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://nginx:80',
        changeOrigin: true,
      },
    },
  },
});
```

**Step 2: Install dependencies**

```bash
docker compose up -d
docker compose exec frontend npm install
```

**Step 3: Verify Vite boots**

Open http://localhost:5173 — should show "Hello CDS"

**Step 4: Verify API proxy**

```bash
curl -s http://localhost:5173/api/clients
```
Expected: `[]` (empty array from backend)

**Step 5: Commit**

```bash
git add frontend/
git commit -m "feat: scaffold Vite + React + TypeScript + Tailwind frontend"
```

---

## Milestone 2: API Client Layer + Types

### Task 2.1: TypeScript types

**Files:**
- Create: `frontend/src/types.ts`

Types: `Client`, `Order`, `ApiErrorResponse`, `DeliveryType`

---

### Task 2.2: API client + ApiError

**Files:**
- Create: `frontend/src/api.ts`

Fetch wrapper with base URL, JSON headers, error envelope parsing. `ApiError` class with code, message, status fields. Functions: `getClients`, `getClient`, `createClient`, `deleteClient`, `getOrders`, `getOrder`, `createOrder`.

---

### Task 2.3: React Query hooks

**Files:**
- Create: `frontend/src/hooks/useClients.ts`
- Create: `frontend/src/hooks/useOrders.ts`

Custom hooks wrapping api.ts functions with useQuery/useMutation. Query invalidation on mutations.

---

## Milestone 3: Layout + Routing

### Task 3.1: Layout component

**Files:**
- Create: `frontend/src/components/Layout.tsx`

Navigation bar with links to Orders, Clients. `<Outlet />` for page content. Tailwind styled.

---

### Task 3.2: Shared components

**Files:**
- Create: `frontend/src/components/ErrorMessage.tsx`
- Create: `frontend/src/components/LoadingSpinner.tsx`

---

### Task 3.3: React Router setup

**Files:**
- Modify: `frontend/src/App.tsx`

Routes: `/` → redirect to `/orders`, `/orders` → OrderList, `/orders/new` → OrderForm, `/orders/:id` → OrderDetail, `/clients` → ClientList, `/clients/new` → ClientForm, `*` → 404 page.

---

## Milestone 4: Client Pages

### Task 4.1: ClientList page

**Files:**
- Create: `frontend/src/pages/ClientList.tsx`

Displays list of clients. Delete button with confirmation. Empty state. Loading/error states. Link to "New Client".

---

### Task 4.2: ClientForm page

**Files:**
- Create: `frontend/src/pages/ClientForm.tsx`

Form fields: name (required), phone, address. Submits via useCreateClient(). Shows validation errors from API. Redirects to /clients on success.

---

## Milestone 5: Order Pages

### Task 5.1: OrderList page

**Files:**
- Create: `frontend/src/pages/OrderList.tsx`

Displays orders with client name, delivery type, status, tracking code, cost. Color-coded delivery type badges. Link to "New Order".

---

### Task 5.2: OrderForm page

**Files:**
- Create: `frontend/src/pages/OrderForm.tsx`

Client dropdown (loads from useClients). Delivery type selector (water/land/air) with cost/time preview. Delivery address field. Submits via useCreateOrder(). Shows validation errors.

---

### Task 5.3: OrderDetail page

**Files:**
- Create: `frontend/src/pages/OrderDetail.tsx`

Shows full order data: tracking code, status, delivery type, address, cost, timestamps, client info.

---

## Milestone 6: Tests

### Task 6.1: Test setup + MSW handlers

**Files:**
- Create: `frontend/tests/setup.ts`
- Create: `frontend/tests/mocks/handlers.ts`

MSW handlers for all 7 API endpoints. Setup file configures MSW server for Vitest.

---

### Task 6.2: API client tests

**Files:**
- Create: `frontend/tests/api.test.ts`

Test: successful response parsing, 4xx error envelope → ApiError, 5xx → ApiError, network failure.

---

### Task 6.3: Hook tests

**Files:**
- Create: `frontend/tests/hooks/useClients.test.ts`
- Create: `frontend/tests/hooks/useOrders.test.ts`

Test: data fetching, mutation + cache invalidation, error states.

---

### Task 6.4: Page tests

**Files:**
- Create: `frontend/tests/pages/OrderList.test.tsx`
- Create: `frontend/tests/pages/OrderForm.test.tsx`
- Create: `frontend/tests/pages/OrderDetail.test.tsx`
- Create: `frontend/tests/pages/ClientList.test.tsx`
- Create: `frontend/tests/pages/ClientForm.test.tsx`

Test: renders data, loading states, empty states, error states, form submission, validation errors, navigation.

---

## Milestone 7: Polish + Framer Motion

### Task 7.1: Add Framer Motion animations

**Files:**
- Modify: all page components

Add: page transitions (fade+slide on route change), list item stagger animation, form field entrance animation, button hover/tap effects, toast-style success/error notifications.

---

### Task 7.2: Final lint + type check + test run

```bash
docker compose exec frontend npm run lint
docker compose exec frontend npm run typecheck
docker compose exec frontend npm run test
```

All must pass.

---

## Test count summary

| Suite | Test file | Cases |
|-------|-----------|-------|
| Unit | api.test.ts | 4 |
| Unit | useClients.test.ts | 4 |
| Unit | useOrders.test.ts | 3 |
| Component | OrderList.test.tsx | 4 |
| Component | OrderForm.test.tsx | 5 |
| Component | OrderDetail.test.tsx | 3 |
| Component | ClientList.test.tsx | 4 |
| Component | ClientForm.test.tsx | 3 |
| **Total** | | **~30** |
