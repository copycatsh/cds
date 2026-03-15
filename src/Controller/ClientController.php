<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ConflictException;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Repository\ClientRepository;

class ClientController
{
    public function __construct(
        private readonly ClientRepository $clientRepo,
    ) {
    }

    public function index(Request $request): Response
    {
        return Response::json($this->clientRepo->findAll());
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getRouteParam('id');
        $client = $this->clientRepo->findByIdOrFail($id);

        return Response::json($client);
    }

    public function create(Request $request): Response
    {
        $body = $request->getBody();

        $name = trim((string) ($body['name'] ?? ''));
        if ($name === '') {
            throw new ValidationException("Field 'name' is required");
        }

        $id = $this->clientRepo->create([
            'name' => $name,
            'phone' => $body['phone'] ?? null,
            'address' => $body['address'] ?? null,
        ]);

        $client = $this->clientRepo->findByIdOrFail($id);

        return Response::json($client, 201);
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->getRouteParam('id');
        $this->clientRepo->findByIdOrFail($id);

        try {
            $this->clientRepo->delete($id);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'violates foreign key constraint')) {
                throw new ConflictException('Cannot delete client with existing orders');
            }
            throw $e;
        }

        return Response::json(['deleted' => true]);
    }
}
