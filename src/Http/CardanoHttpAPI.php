<?php
declare(strict_types=1);

namespace CardanoSL\Http;

use CardanoSL\Exception\API_Exception;
use CardanoSL\Exception\HttpAPIException;
use HttpClient\Request;
use HttpClient\Response\JSONResponse;
use HttpClient\Response\Response;

/**
 * Class CardanoHttpAPI
 * @package CardanoSL\Http
 */
class CardanoHttpAPI extends AbstractHttpClient
{
    /**
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @return HttpJSONResponse
     * @throws API_Exception
     * @throws HttpAPIException
     * @throws \HttpClient\Exception\HttpClientException
     * @throws \HttpClient\Exception\RequestException
     * @throws \HttpClient\Exception\ResponseException
     * @throws \HttpClient\Exception\SSLException
     */
    public function call(string $method, string $endpoint, ?array $payload = null): HttpJSONResponse
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
            $body = $res->body();
            $bodyLen = mb_strlen($body);
            if ($bodyLen > 1 && $bodyLen < 256) {
                throw new HttpAPIException(strip_tags($body), $res->code());
            }

            throw new HttpAPIException(sprintf('Got non-JSON response with HTTP code %d', $res->code()));
        }

        if ($res instanceof JSONResponse) {
            $jsonResponse = new HttpJSONResponse(
                $res->code(),
                $res->array(),
                $res->headers()
            );

            return $jsonResponse;
        }

        throw new HttpAPIException('No response was received');
    }
}