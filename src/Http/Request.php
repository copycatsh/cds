<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    /** @var array<string, string> */
    private array $routeParams = [];

    /** @param array<string, mixed> $body */
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        private readonly array $body,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $body = [];

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $raw = file_get_contents('php://input');
            if ($raw !== false && $raw !== '') {
                $body = json_decode($raw, true) ?? [];
            }
        }

        return new self($method, $uri, $body);
    }

    /** @return array<string, mixed> */
    public function getBody(): array
    {
        return $this->body;
    }

    public function getBodyParam(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /** @param array<string, string> $params */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function getRouteParam(string $key): ?string
    {
        return $this->routeParams[$key] ?? null;
    }
}
