import { describe, it, expect } from 'vitest';
import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { server } from '../setup';
import { renderWithProviders } from '../test-utils';
import ClientList from '../../src/pages/ClientList';

describe('ClientList', () => {
  it('renders loading then client list', async () => {
    renderWithProviders(<ClientList />);
    await waitFor(() => {
      expect(screen.getByText('Alice')).toBeInTheDocument();
    });
    expect(screen.getByText('Bob')).toBeInTheDocument();
    expect(screen.getByText('+111')).toBeInTheDocument();
  });

  it('renders empty state when no clients', async () => {
    server.use(http.get('/api/clients', () => HttpResponse.json([])));

    renderWithProviders(<ClientList />);
    await waitFor(() => {
      expect(screen.getByText('No clients yet.')).toBeInTheDocument();
    });
  });

  it('renders error state', async () => {
    server.use(
      http.get('/api/clients', () =>
        HttpResponse.json(
          { error: { code: 'internal_error', message: 'DB down', status: 500 } },
          { status: 500 },
        ),
      ),
    );

    renderWithProviders(<ClientList />);
    await waitFor(() => {
      expect(screen.getByText('DB down')).toBeInTheDocument();
    });
  });

  it('shows conflict error when deleting client with orders', async () => {
    renderWithProviders(<ClientList />);

    await waitFor(() => {
      expect(screen.getByText('Alice')).toBeInTheDocument();
    });

    const deleteButtons = screen.getAllByText('Delete');
    // First delete button is for Alice (id=1), which has orders → conflict
    window.confirm = () => true;
    await userEvent.click(deleteButtons[0]!);

    await waitFor(() => {
      expect(screen.getByText('Cannot delete client with existing orders')).toBeInTheDocument();
    });
  });
});
