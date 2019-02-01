<?php
declare(strict_types=1);

namespace CardanoSL;

use CardanoSL\Http\AbstractHttpClient;
use CardanoSL\Http\CardanoHttpAPI;
use CardanoSL\Response\NodeInfo;

/**
 * Class CardanoSL
 * @package CardanoSL
 */
class CardanoSL
{
    public const MAX_LOVELACE = 45000000000000000;

    /** @var AbstractHttpClient */
    private $httpClient;
    /** @var string */
    private $host;
    /** @var int */
    private $port;

    /**
     * CardanoSL constructor.
     * @param string $host
     * @param int $port
     * @param AbstractHttpClient|null $httpClient
     */
    public function __construct(string $host, int $port, ?AbstractHttpClient $httpClient = null)
    {
        if (!$httpClient) {
            $httpClient = new CardanoHttpAPI($host, $port);
        }

        $this->host = $host;
        $this->port = $port;
        $this->httpClient = $httpClient;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [sprintf('Cardano SL node "%s"', $this->host)];
    }

    /**
     * @return AbstractHttpClient
     */
    public function http(): AbstractHttpClient
    {
        return $this->httpClient;
    }

    /**
     * @return string
     */
    public function host(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function port(): int
    {
        return $this->port;
    }

    /**
     * @return NodeInfo
     * @throws Exception\API_ResponseException
     */
    public function nodeInfo(): NodeInfo
    {
        $res = $this->httpClient->get("/api/v1/node-info");
        return new NodeInfo($res);
    }
}