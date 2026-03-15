import { describe, it, expect } from 'vitest';
import { screen, waitFor } from '@testing-library/react';
import { http, HttpResponse } from 'msw';
import { server } from '../setup';
import { renderWithProviders } from '../test-utils';
import OrderList from '../../src/pages/OrderList';

describe('OrderList', () => {
  it('renders order list with data', async () => {
    renderWithProviders(<OrderList />);
    await waitFor(() => {
      expect(screen.getByText('AIR-abc123')).toBeInTheDocument();
    });
    expect(screen.getByText('WAT-def456')).toBeInTheDocument();
    expect(screen.getByText('Alice')).toBeInTheDocument();
    expect(screen.getByText('Bob')).toBeInTheDocument();
    expect(screen.getByText('$50.00')).toBeInTheDocument();
    expect(screen.getByText('$5.00')).toBeInTheDocument();
  });

  it('renders delivery type badges', async () => {
    renderWithProviders(<OrderList />);
    await waitFor(() => {
      expect(screen.getByText('air')).toBeInTheDocument();
    });
    expect(screen.getByText('water')).toBeInTheDocument();
  });

  it('renders empty state', async () => {
    server.use(http.get('/api/orders', () => HttpResponse.json([])));

    renderWithProviders(<OrderList />);
    await waitFor(() => {
      expect(screen.getByText('No orders yet.')).toBeInTheDocument();
    });
  });

  it('renders error state', async () => {
    server.use(
      http.get('/api/orders', () =>
        HttpResponse.json(
          { error: { code: 'internal_error', message: 'Server error', status: 500 } },
          { status: 500 },
        ),
      ),
    );

    renderWithProviders(<OrderList />);
    await waitFor(() => {
      expect(screen.getByText('Server error')).toBeInTheDocument();
    });
  });
});
