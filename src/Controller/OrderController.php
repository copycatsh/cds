<?php

declare(strict_types=1);

namespace App\Controller;

use App\Delivery\DeliveryFactory;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Repository\ClientRepository;
use App\Repository\OrderRepository;

class OrderController
{
    public function __construct(
        private readonly OrderRepository $orderRepo,
        private readonly ClientRepository $clientRepo,
        private readonly DeliveryFactory $deliveryFactory,
    ) {
    }

    public function index(Request $request): Response
    {
        return Response::json($this->orderRepo->findAll());
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getRouteParam('id');
        $order = $this->orderRepo->findByIdOrFail($id);

        return Response::json($order);
    }

    public function create(Request $request): Response
    {
        $body = $request->getBody();

        $clientId = $body['client_id'] ?? null;
        if ($clientId === null) {
            throw new ValidationException("Field 'client_id' is required");
        }
        $this->clientRepo->findByIdOrFail((int) $clientId);

        $deliveryType = trim((string) ($body['delivery_type'] ?? ''));
        if ($deliveryType === '') {
            throw new ValidationException("Field 'delivery_type' is required");
        }

        $deliveryAddress = trim((string) ($body['delivery_address'] ?? ''));
        if ($deliveryAddress === '') {
            throw new ValidationException("Field 'delivery_address' is required");
        }

        $delivery = $this->deliveryFactory->create($deliveryType);

        if (!$delivery->isAvailableForAddress($deliveryAddress)) {
            throw new ValidationException(
                sprintf("Delivery type '%s' is not available for this address", $deliveryType)
            );
        }

        $orderData = [
            'client_id' => (int) $clientId,
            'delivery_type' => $deliveryType,
            'delivery_address' => $deliveryAddress,
        ];

        $cost = $delivery->calculateCost($orderData);
        $trackingCode = OrderRepository::generateTrackingCode($deliveryType);

        $id = $this->orderRepo->create([
            ...$orderData,
            'tracking_code' => $trackingCode,
            'cost' => $cost,
        ]);

        $order = $this->orderRepo->findByIdOrFail($id);

        return Response::json($order, 201);
    }
}
