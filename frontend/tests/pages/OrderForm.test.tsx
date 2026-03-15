import { describe, it, expect, vi } from 'vitest';
import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { renderWithProviders } from '../test-utils';
import OrderForm from '../../src/pages/OrderForm';

const mockNavigate = vi.fn();
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return { ...actual, useNavigate: () => mockNavigate };
});

describe('OrderForm', () => {
  it('renders form with client dropdown after loading', async () => {
    renderWithProviders(<OrderForm />);
    await waitFor(() => {
      expect(screen.getByText('Alice')).toBeInTheDocument();
    });
    expect(screen.getByText('Bob')).toBeInTheDocument();
  });

  it('renders delivery type selector with cost and days', async () => {
    renderWithProviders(<OrderForm />);
    await waitFor(() => {
      expect(screen.getByText('Water')).toBeInTheDocument();
    });
    expect(screen.getByText('Land')).toBeInTheDocument();
    expect(screen.getByText('Air')).toBeInTheDocument();
    expect(screen.getByText('$5 · 14d')).toBeInTheDocument();
    expect(screen.getByText('$15 · 7d')).toBeInTheDocument();
    expect(screen.getByText('$50 · 2d')).toBeInTheDocument();
  });

  it('shows cost summary that updates with delivery type', async () => {
    renderWithProviders(<OrderForm />);
    const user = userEvent.setup();

    await waitFor(() => {
      expect(screen.getByText('$15.00')).toBeInTheDocument();
    });
    expect(screen.getByText('7 days')).toBeInTheDocument();

    await user.click(screen.getByText('Air'));
    expect(screen.getByText('$50.00')).toBeInTheDocument();
    expect(screen.getByText('2 days')).toBeInTheDocument();
  });

  it('submits valid form and navigates to orders', async () => {
    renderWithProviders(<OrderForm />);
    const user = userEvent.setup();

    await waitFor(() => {
      expect(screen.getByText('Alice')).toBeInTheDocument();
    });

    await user.selectOptions(screen.getByRole('combobox'), '1');
    await user.type(screen.getByLabelText(/delivery address/i), '789 Pine Rd');
    await user.click(screen.getByRole('button', { name: /create order/i }));

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith('/orders');
    });
  });

  it('disables submit when no client selected', async () => {
    renderWithProviders(<OrderForm />);

    await waitFor(() => {
      expect(screen.getByText('Alice')).toBeInTheDocument();
    });

    expect(screen.getByRole('button', { name: /create order/i })).toBeDisabled();
  });
});
