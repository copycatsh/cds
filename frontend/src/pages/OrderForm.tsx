import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useCreateOrder } from '../hooks/useOrders';
import { useClients } from '../hooks/useClients';
import ErrorMessage from '../components/ErrorMessage';
import LoadingSpinner from '../components/LoadingSpinner';
import { DELIVERY_OPTIONS } from '../types';
import type { DeliveryType } from '../types';

export default function OrderForm() {
  const navigate = useNavigate();
  const createOrder = useCreateOrder();
  const { data: clients, isLoading: clientsLoading } = useClients();

  const [clientId, setClientId] = useState('');
  const [deliveryType, setDeliveryType] = useState<DeliveryType>('land');
  const [deliveryAddress, setDeliveryAddress] = useState('');

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    createOrder.mutate(
      {
        client_id: Number(clientId),
        delivery_type: deliveryType,
        delivery_address: deliveryAddress.trim(),
      },
      {
        onSuccess: () => navigate('/orders'),
      },
    );
  }

  if (clientsLoading) return <LoadingSpinner />;

  const selected = DELIVERY_OPTIONS[deliveryType];

  return (
    <div>
      <div className="mb-6">
        <Link to="/orders" className="text-sm text-gray-500 hover:text-gray-700">
          &larr; Back to Orders
        </Link>
        <h1 className="text-2xl font-bold text-gray-900 mt-2">New Order</h1>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-lg border border-gray-200 p-6 max-w-lg space-y-4">
        <ErrorMessage error={createOrder.error} />

        <div>
          <label htmlFor="client" className="block text-sm font-medium text-gray-700 mb-1">
            Client <span className="text-red-500">*</span>
          </label>
          {clients?.length === 0 ? (
            <p className="text-sm text-gray-500">
              No clients yet.{' '}
              <Link to="/clients/new" className="text-blue-600 hover:text-blue-500">Create one first</Link>
            </p>
          ) : (
            <select
              id="client"
              required
              value={clientId}
              onChange={(e) => setClientId(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"
            >
              <option value="">Select a client</option>
              {clients?.map((c) => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Delivery Type <span className="text-red-500">*</span>
          </label>
          <div className="grid grid-cols-3 gap-3">
            {(Object.entries(DELIVERY_OPTIONS) as [DeliveryType, typeof selected][]).map(([type, opt]) => (
              <button
                key={type}
                type="button"
                onClick={() => setDeliveryType(type)}
                className={`rounded-lg border-2 p-3 text-center transition-colors ${
                  deliveryType === type
                    ? 'border-gray-900 bg-gray-50'
                    : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <div className="text-sm font-medium capitalize">{opt.label}</div>
                <div className="text-xs text-gray-500 mt-1">${opt.cost} &middot; {opt.days}d</div>
              </button>
            ))}
          </div>
        </div>

        <div>
          <label htmlFor="address" className="block text-sm font-medium text-gray-700 mb-1">
            Delivery Address <span className="text-red-500">*</span>
          </label>
          <textarea
            id="address"
            required
            rows={3}
            value={deliveryAddress}
            onChange={(e) => setDeliveryAddress(e.target.value)}
            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"
          />
        </div>

        <div className="rounded-md bg-gray-50 border border-gray-200 p-3">
          <div className="flex justify-between text-sm">
            <span className="text-gray-500">Estimated cost</span>
            <span className="font-medium text-gray-900">${selected.cost}.00</span>
          </div>
          <div className="flex justify-between text-sm mt-1">
            <span className="text-gray-500">Estimated delivery</span>
            <span className="font-medium text-gray-900">{selected.days} days</span>
          </div>
        </div>

        <div className="flex gap-3 pt-2">
          <button
            type="submit"
            disabled={createOrder.isPending || !clientId}
            className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 transition-colors"
          >
            {createOrder.isPending ? 'Creating...' : 'Create Order'}
          </button>
          <Link
            to="/orders"
            className="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancel
          </Link>
        </div>
      </form>
    </div>
  );
}
