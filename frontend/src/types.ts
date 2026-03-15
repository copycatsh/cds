export interface Client {
  id: number;
  name: string;
  phone: string | null;
  address: string | null;
  created_at: string;
  updated_at: string;
}

export interface Order {
  id: number;
  client_id: number;
  client_name?: string;
  delivery_type: DeliveryType;
  status: OrderStatus;
  tracking_code: string;
  delivery_address: string;
  cost: string;
  created_at: string;
  updated_at: string;
}

export type DeliveryType = 'water' | 'land' | 'air';
export type OrderStatus = 'pending' | 'processing' | 'delivered' | 'cancelled';

export interface CreateClientData {
  name: string;
  phone?: string;
  address?: string;
}

export interface CreateOrderData {
  client_id: number;
  delivery_type: DeliveryType;
  delivery_address: string;
}

export const DELIVERY_OPTIONS: Record<DeliveryType, { label: string; cost: number; days: number }> = {
  water: { label: 'Water', cost: 5, days: 14 },
  land: { label: 'Land', cost: 15, days: 7 },
  air: { label: 'Air', cost: 50, days: 2 },
};
