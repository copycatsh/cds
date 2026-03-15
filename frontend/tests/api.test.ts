import { describe, it, expect } from 'vitest';
import { http, HttpResponse } from 'msw';
import { server } from './setup';
import { getClients, ApiError } from '../src/api';

describe('api', () => {
  it('parses successful JSON response', async () => {
    const clients = await getClients();
    expect(clients).toHaveLength(2);
    expect(clients[0]?.name).toBe('Alice');
  });

  it('throws ApiError on 4xx with error envelope', async () => {
    server.use(
      http.get('/api/clients', () => {
        return HttpResponse.json(
          { error: { code: 'test_error', message: 'Test failure', status: 422 } },
          { status: 422 },
        );
      }),
    );

    await expect(getClients()).rejects.toThrow(ApiError);

    try {
      await getClients();
    } catch (err) {
      expect(err).toBeInstanceOf(ApiError);
      const apiErr = err as ApiError;
      expect(apiErr.code).toBe('test_error');
      expect(apiErr.message).toBe('Test failure');
      expect(apiErr.status).toBe(422);
    }
  });

  it('throws ApiError on 5xx with generic message', async () => {
    server.use(
      http.get('/api/clients', () => {
        return new HttpResponse('Internal Server Error', { status: 500 });
      }),
    );

    try {
      await getClients();
    } catch (err) {
      expect(err).toBeInstanceOf(ApiError);
      expect((err as ApiError).status).toBe(500);
    }
  });

  it('throws on network failure', async () => {
    server.use(
      http.get('/api/clients', () => {
        return HttpResponse.error();
      }),
    );

    await expect(getClients()).rejects.toThrow();
  });
});
