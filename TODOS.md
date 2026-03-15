# TODOS

## Phase 2: React Frontend + Dashboard
**Status:** IN PROGRESS — review complete, ready for implementation
**Priority:** Next after Phase 1
**What:** Build React 18 + Vite frontend with order dashboard, create-order form, client list.
**Why:** The original plan included a full frontend — this is the deferred half. Makes the app usable by non-technical users; validates API design end-to-end.
**Pros:** Completes the product; exercises every API endpoint; catches API design issues early.
**Cons:** Significant scope (~10+ components, API client, routing, state management).
**Context:** API will be fully functional and tested after Phase 1. Frontend should consume the JSON API as-is. Original plan specified Tailwind CSS + Framer Motion + React Query. Nginx already configured as reverse proxy — frontend dev server or built assets served from a separate container.
**Depends on:** Phase 1 complete and running.

## Courier Entity + Order Assignment
**Priority:** Can be done during or after Phase 2
**What:** Add `couriers` table (id, name, transport_type, status, created_at, updated_at), `CourierRepository`, `CourierController`, and `courier_id` FK on `orders`. Add `GET/POST /couriers` and `PATCH /orders/{id}/assign` endpoints.
**Why:** Delivery system is incomplete without someone performing the delivery. Courier assignment links order creation to fulfillment.
**Pros:** Completes domain model; enables order lifecycle tracking; `transport_type` can validate against `delivery_type`.
**Cons:** Requires migration (add table + alter orders), new controller, new repository.
**Context:** `AbstractRepository` makes new repo trivial — extend with `findByTransportType()`, `findAvailable()`. Migration: `002_add_couriers.sql`. Was in the original plan.
**Depends on:** Phase 1 complete (migration runner working, AbstractRepository proven).

## Dashboard Page + Stats API
**Priority:** After Phase 2 frontend ships
**What:** Add `GET /api/stats` endpoint (order counts by status, total revenue, orders by delivery type) and a Dashboard page with counters/charts.
**Why:** Original plan included a Dashboard — deferred from Phase 2 because backend has no aggregation queries.
**Pros:** Makes the app feel complete; gives a landing page with useful data at a glance.
**Cons:** Requires new backend endpoint + frontend page + possibly a chart library (recharts or styled counters).
**Context:** Phase 2 frontend ships with `/` redirecting to `/orders`. Dashboard can replace this redirect once stats API exists. Keep it simple — styled counters may be enough without a chart library.
**Depends on:** Phase 2 frontend complete, new backend stats endpoint.

## Backend CRUD Completeness
**Priority:** During or after Phase 2
**What:** Add `PUT /api/clients/{id}` (update client), `PATCH /api/orders/{id}` (update order status) endpoints so the frontend can offer edit functionality.
**Why:** Phase 1 backend only has create + read + delete for clients, create + read for orders. No update endpoints exist. The frontend ships without edit capability.
**Pros:** Completes CRUD cycle; enables order status workflow (pending → processing → delivered); enables client profile editing.
**Cons:** Requires new controller methods, new routes, new tests. ~2-3 hours of backend work.
**Context:** `AbstractRepository::update()` already exists and works. The backend plumbing is ready — just needs controller methods and route registration. Order status updates should validate transitions (e.g., can't go from `delivered` back to `pending`).
**Depends on:** Phase 2 frontend complete (or can be done in parallel).
