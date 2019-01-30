<?php
declare(strict_types=1);

namespace CardanoSL\Http;

/**
 * Class CardanoHttpAPI
 * @package CardanoSL\Http
 */
class CardanoHttpAPI extends AbstractHttpClient
{
    public function call(string $method, string $endpoint, ?array $payload = null): array
    {
    }
}