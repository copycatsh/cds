import { http, HttpResponse } from 'msw';
import type { Client, Order } from '../../src/types';

export const mockClients: Client[] = [
  { id: 1, name: 'Alice', phone: '+111', address: '1 Oak St', created_at: '2026-01-01T00:00:00Z', updated_at: '2026-01-01T00:00:00Z' },
  { id: 2, name: 'Bob', phone: null, address: null, created_at: '2026-01-02T00:00:00Z', updated_at: '2026-01-02T00:00:00Z' },
];

export const mockOrders: Order[] = [
  { id: 1, client_id: 1, client_name: 'Alice', delivery_type: 'air', status: 'pending', tracking_code: 'AIR-abc123', delivery_address: '456 Oak Ave', cost: '50.00', created_at: '2026-01-01T00:00:00Z', updated_at: '2026-01-01T00:00:00Z' },
  { id: 2, client_id: 2, client_name: 'Bob', delivery_type: 'water', status: 'delivered', tracking_code: 'WAT-def456', delivery_address: 'Port 42', cost: '5.00', created_at: '2026-01-02T00:00:00Z', updated_at: '2026-01-02T00:00:00Z' },
];

export const handlers = [
  http.get('/api/clients', () => {
    return HttpResponse.json(mockClients);
  }),

  http.get('/api/clients/:id', ({ params }) => {
    const client = mockClients.find((c) => c.id === Number(params.id));
    if (!client) {
      return HttpResponse.json(
        { error: { code: 'not_found', message: `Client with id ${params.id as string} not found`, status: 404 } },
        { status: 404 },
      );
    }
    return HttpResponse.json(client);
  }),

  http.post('/api/clients', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;
    if (!body.name || String(body.name).trim() === '') {
      return HttpResponse.json(
        { error: { code: 'validation_error', message: "Field 'name' is required", status: 422 } },
        { status: 422 },
      );
    }
    const created: Client = {
      id: 3,
      name: String(body.name),
      phone: body.phone ? String(body.phone) : null,
      address: body.address ? String(body.address) : null,
      created_at: '2026-01-03T00:00:00Z',
      updated_at: '2026-01-03T00:00:00Z',
    };
    return HttpResponse.json(created, { status: 201 });
  }),

  http.delete('/api/clients/:id', ({ params }) => {
    if (Number(params.id) === 1) {
      return HttpResponse.json(
        { error: { code: 'conflict', message: 'Cannot delete client with existing orders', status: 409 } },
        { status: 409 },
      );
    }
    return HttpResponse.json({ deleted: true });
  }),

  http.get('/api/orders', () => {
    return HttpResponse.json(mockOrders);
  }),

  http.get('/api/orders/:id', ({ params }) => {
    const order = mockOrders.find((o) => o.id === Number(params.id));
    if (!order) {
      return HttpResponse.json(
        { error: { code: 'not_found', message: `Order with id ${params.id as string} not found`, status: 404 } },
        { status: 404 },
      );
    }
    return HttpResponse.json(order);
  }),

  http.post('/api/orders', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;
    if (!body.client_id) {
      return HttpResponse.json(
        { error: { code: 'validation_error', message: "Field 'client_id' is required", status: 422 } },
        { status: 422 },
      );
    }
    if (!body.delivery_type) {
      return HttpResponse.json(
        { error: { code: 'validation_error', message: "Field 'delivery_type' is required", status: 422 } },
        { status: 422 },
      );
    }
    if (!body.delivery_address) {
      return HttpResponse.json(
        { error: { code: 'validation_error', message: "Field 'delivery_address' is required", status: 422 } },
        { status: 422 },
      );
    }
    const created: Order = {
      id: 3,
      client_id: Number(body.client_id),
      client_name: 'Alice',
      delivery_type: body.delivery_type as Order['delivery_type'],
      status: 'pending',
      tracking_code: 'LND-aaa111',
      delivery_address: String(body.delivery_address),
      cost: '15.00',
      created_at: '2026-01-03T00:00:00Z',
      updated_at: '2026-01-03T00:00:00Z',
    };
    return HttpResponse.json(created, { status: 201 });
  }),
];
