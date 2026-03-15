export class ApiError extends Error {
  constructor(
    public readonly code: string,
    message: string,
    public readonly status: number,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

async function request<T>(url: string, options?: RequestInit): Promise<T> {
  const res = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options?.headers,
    },
  });

  if (!res.ok) {
    let code = 'unknown_error';
    let message = 'An unexpected error occurred';

    try {
      const body = await res.json() as { error?: { code?: string; message?: string } };
      if (body.error) {
        code = body.error.code ?? code;
        message = body.error.message ?? message;
      }
    } catch {
      // Response wasn't JSON — use defaults
    }

    throw new ApiError(code, message, res.status);
  }

  return res.json() as Promise<T>;
}

// Clients
import type { Client, CreateClientData, Order, CreateOrderData } from './types';

export function getClients(): Promise<Client[]> {
  return request<Client[]>('/api/clients');
}

export function getClient(id: number): Promise<Client> {
  return request<Client>(`/api/clients/${id}`);
}

export function createClient(data: CreateClientData): Promise<Client> {
  return request<Client>('/api/clients', {
    method: 'POST',
    body: JSON.stringify(data),
  });
}

export function deleteClient(id: number): Promise<{ deleted: boolean }> {
  return request<{ deleted: boolean }>(`/api/clients/${id}`, {
    method: 'DELETE',
  });
}

// Orders
export function getOrders(): Promise<Order[]> {
  return request<Order[]>('/api/orders');
}

export function getOrder(id: number): Promise<Order> {
  return request<Order>(`/api/orders/${id}`);
}

export function createOrder(data: CreateOrderData): Promise<Order> {
  return request<Order>('/api/orders', {
    method: 'POST',
    body: JSON.stringify(data),
  });
}
