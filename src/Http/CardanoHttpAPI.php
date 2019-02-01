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
            $req->payload($payload);
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
            $payload = $res->array();
            $status = $payload["status"] ?? null;
            $httpCodeStatus = $res->code() >= 200 && $res->code() < 300 ? true : false;

            if ($status !== "success" || !$httpCodeStatus) {
                $msg = $payload["message"] ?? null;
                $detailMsg = $payload["diagnostic"]["msg"] ?? $payload["diagnostic"]["details"]["msg"] ?? null;

                if ($msg && $detailMsg) {
                    throw new API_Exception(sprintf('[%s]: %s', $msg, $detailMsg), $res->code());
                } elseif ($msg) {
                    throw new API_Exception(sprintf('Cardano SL API error: %s', $msg), $res->code());
                } else {
                    throw new API_Exception('Cardano SL API call not successful, unknown error', $res->code());
                }
            }

            $jsonResponse = new HttpJSONResponse();
            $jsonResponse->httpCode = $res->code();
            $jsonResponse->payload = $payload;
            $jsonResponse->headers = $res->headers();

            return $jsonResponse;
        }

        throw new HttpAPIException('No response was received');
    }
}