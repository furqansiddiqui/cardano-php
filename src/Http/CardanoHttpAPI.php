<?php
declare(strict_types=1);

namespace CardanoSL\Http;

use HttpClient\Request;

/**
 * Class CardanoHttpAPI
 * @package CardanoSL\Http
 */
class CardanoHttpAPI extends AbstractHttpClient
{
    public function call(string $method, string $endpoint, ?array $payload = null): array
    {
        $req = new Request($method);
        $req->url($this->url($endpoint));
        $req->json();

        if ($this->tls) {
            $this->tls->apply($req);
        }

        if ($payload) {
            $req->payload($payload);
        }

        $res = $req->send();
        var_dump($res);
    }
}