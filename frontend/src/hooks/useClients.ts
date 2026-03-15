import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { getClients, getClient, createClient, deleteClient } from '../api';
import type { CreateClientData } from '../types';

export function useClients() {
  return useQuery({
    queryKey: ['clients'],
    queryFn: getClients,
  });
}

export function useClient(id: number) {
  return useQuery({
    queryKey: ['clients', id],
    queryFn: () => getClient(id),
  });
}

export function useCreateClient() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateClientData) => createClient(data),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['clients'] });
    },
  });
}

export function useDeleteClient() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => deleteClient(id),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['clients'] });
    },
  });
}
