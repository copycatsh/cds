import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useClients, useDeleteClient } from '../hooks/useClients';
import { ApiError } from '../api';
import ErrorMessage from '../components/ErrorMessage';
import LoadingSpinner from '../components/LoadingSpinner';

export default function ClientList() {
  const { data: clients, isLoading, error } = useClients();
  const deleteClient = useDeleteClient();
  const [deleteError, setDeleteError] = useState<Error | null>(null);

  function handleDelete(id: number, name: string) {
    if (!confirm(`Delete client "${name}"?`)) return;

    setDeleteError(null);
    deleteClient.mutate(id, {
      onError: (err) => setDeleteError(err instanceof ApiError ? err : new Error(String(err))),
    });
  }

  if (isLoading) return <LoadingSpinner />;
  if (error) return <ErrorMessage error={error} />;

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Clients</h1>
        <Link
          to="/clients/new"
          className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 transition-colors"
        >
          New Client
        </Link>
      </div>

      <ErrorMessage error={deleteError} />

      {clients?.length === 0 ? (
        <div className="text-center py-12 text-gray-500">
          <p>No clients yet.</p>
          <Link to="/clients/new" className="text-blue-600 hover:text-blue-500 text-sm">
            Create your first client
          </Link>
        </div>
      ) : (
        <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {clients?.map((client) => (
                <tr key={client.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{client.name}</td>
                  <td className="px-6 py-4 text-sm text-gray-500">{client.phone ?? '—'}</td>
                  <td className="px-6 py-4 text-sm text-gray-500">{client.address ?? '—'}</td>
                  <td className="px-6 py-4 text-right">
                    <button
                      onClick={() => handleDelete(client.id, client.name)}
                      disabled={deleteClient.isPending}
                      className="text-sm text-red-600 hover:text-red-500 disabled:opacity-50"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
