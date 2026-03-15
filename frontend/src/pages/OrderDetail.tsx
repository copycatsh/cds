import { useParams, Link } from 'react-router-dom';
import { useOrder } from '../hooks/useOrders';
import ErrorMessage from '../components/ErrorMessage';
import LoadingSpinner from '../components/LoadingSpinner';
import { DELIVERY_OPTIONS } from '../types';

export default function OrderDetail() {
  const { id } = useParams<{ id: string }>();
  const { data: order, isLoading, error } = useOrder(Number(id));

  if (isLoading) return <LoadingSpinner />;
  if (error) return <ErrorMessage error={error} />;
  if (!order) return null;

  const deliveryInfo = DELIVERY_OPTIONS[order.delivery_type];

  return (
    <div>
      <div className="mb-6">
        <Link to="/orders" className="text-sm text-gray-500 hover:text-gray-700">
          &larr; Back to Orders
        </Link>
        <div className="flex items-center gap-3 mt-2">
          <h1 className="text-2xl font-bold text-gray-900 font-mono">{order.tracking_code}</h1>
          <span className="inline-flex items-center rounded-full bg-yellow-100 text-yellow-700 px-2.5 py-0.5 text-xs font-medium capitalize">
            {order.status}
          </span>
        </div>
      </div>

      <div className="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200 max-w-lg">
        <div className="p-4 flex justify-between">
          <span className="text-sm text-gray-500">Client</span>
          <span className="text-sm font-medium text-gray-900">{order.client_name ?? `#${order.client_id}`}</span>
        </div>
        <div className="p-4 flex justify-between">
          <span className="text-sm text-gray-500">Delivery Type</span>
          <span className="text-sm font-medium text-gray-900 capitalize">{deliveryInfo.label}</span>
        </div>
        <div className="p-4 flex justify-between">
          <span className="text-sm text-gray-500">Delivery Address</span>
          <span className="text-sm font-medium text-gray-900">{order.delivery_address}</span>
        </div>
        <div className="p-4 flex justify-between">
          <span className="text-sm text-gray-500">Cost</span>
          <span className="text-sm font-medium text-gray-900 font-mono">${order.cost}</span>
        </div>
        <div className="p-4 flex justify-between">
          <span className="text-sm text-gray-500">Estimated Delivery</span>
          <span className="text-sm font-medium text-gray-900">{deliveryInfo.days} days</span>
        </div>
        <div className="p-4 flex justify-between">
          <span className="text-sm text-gray-500">Created</span>
          <span className="text-sm text-gray-900">{new Date(order.created_at).toLocaleString()}</span>
        </div>
      </div>
    </div>
  );
}
