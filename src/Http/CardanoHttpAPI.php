<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Http;

use FurqanSiddiqui\Cardano\Exception\HttpAPIException;
use HttpClient\Request;
use HttpClient\Response\JSONResponse;
use HttpClient\Response\Response;

/**
 * Class CardanoHttpAPI
 * @package FurqanSiddiqui\Cardano\Http
 */
class CardanoHttpAPI extends AbstractHttpClient
{
    /**
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @param bool $reqJSONBody
     * @return HttpJSONResponse
     * @throws HttpAPIException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_ResponseException
     * @throws \HttpClient\Exception\HttpClientException
     * @throws \HttpClient\Exception\RequestException
     * @throws \HttpClient\Exception\ResponseException
     * @throws \HttpClient\Exception\SSLException
     */
    public function call(string $method, string $endpoint, ?array $payload = null, bool $reqJSONBody = true): HttpJSONResponse
    {
        $req = new Request($method, $this->url($endpoint));
        if ($this->tls) {
            $this->tls->apply($req);
        }

        if ($payload) {
            $req->payload($payload, true);
        }

        $res = $req->send();
        if ($res instanceof Response) {
            if ($reqJSONBody) {
                $body = $res->body();
                $bodyLen = mb_strlen($body);
                if ($bodyLen > 1 && $bodyLen < 256) {
                    throw new HttpAPIException(strip_tags($body), $res->code());
                }

                throw new HttpAPIException(sprintf('Got non-JSON response with HTTP code %d', $res->code()));
            }
        }

        if ($res instanceof JSONResponse) {
            return new HttpJSONResponse(
                $res->code(),
                $res->array(),
                $res->headers()
            );
        }

        throw new HttpAPIException('No response was received');
    }
}
