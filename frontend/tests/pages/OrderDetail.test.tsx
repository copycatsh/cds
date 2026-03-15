import { describe, it, expect } from 'vitest';
import { screen, waitFor } from '@testing-library/react';
import { renderWithProviders } from '../test-utils';
import { Routes, Route } from 'react-router-dom';
import OrderDetail from '../../src/pages/OrderDetail';

function renderOrderDetail(id: number) {
  return renderWithProviders(
    <Routes>
      <Route path="/orders/:id" element={<OrderDetail />} />
    </Routes>,
    { route: `/orders/${id}` },
  );
}

describe('OrderDetail', () => {
  it('renders order data', async () => {
    renderOrderDetail(1);
    await waitFor(() => {
      expect(screen.getByText('AIR-abc123')).toBeInTheDocument();
    });
    expect(screen.getByText('Alice')).toBeInTheDocument();
    expect(screen.getByText('Air')).toBeInTheDocument();
    expect(screen.getByText('456 Oak Ave')).toBeInTheDocument();
    expect(screen.getByText('$50.00')).toBeInTheDocument();
    expect(screen.getByText('pending')).toBeInTheDocument();
  });

  it('renders not found error', async () => {
    renderOrderDetail(999);
    await waitFor(() => {
      expect(screen.getByText(/not found/i)).toBeInTheDocument();
    });
  });

  it('renders loading state', () => {
    renderOrderDetail(1);
    // LoadingSpinner renders while query is in flight
    expect(document.querySelector('.animate-spin')).toBeInTheDocument();
  });
});
