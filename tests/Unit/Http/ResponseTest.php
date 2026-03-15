<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testJsonCreatesResponse(): void
    {
        $response = Response::json(['key' => 'value']);
        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame('{"key":"value"}', $output);
    }

    public function testErrorCreatesEnvelope(): void
    {
        $response = Response::error('test_error', 'Something went wrong', 422);
        ob_start();
        $response->send();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertSame('test_error', $decoded['error']['code']);
        $this->assertSame('Something went wrong', $decoded['error']['message']);
        $this->assertSame(422, $decoded['error']['status']);
    }
}
