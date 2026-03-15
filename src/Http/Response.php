<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    /** @param array<string, mixed>|list<array<string, mixed>> $data */
    public function __construct(
        private readonly array $data,
        private readonly int $status = 200,
    ) {
    }

    /** @param array<string, mixed>|list<array<string, mixed>> $data */
    public static function json(array $data, int $status = 200): self
    {
        return new self($data, $status);
    }

    public static function error(string $code, string $message, int $status): self
    {
        return new self([
            'error' => [
                'code' => $code,
                'message' => $message,
                'status' => $status,
            ],
        ], $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
