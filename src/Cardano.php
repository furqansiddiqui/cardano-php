<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano;

use FurqanSiddiqui\Cardano\API\Wallets;
use FurqanSiddiqui\Cardano\Exception\AddressException;
use FurqanSiddiqui\Cardano\Http\AbstractHttpClient;
use FurqanSiddiqui\Cardano\Http\CardanoHttpAPI;
use FurqanSiddiqui\Cardano\Response\AddressesList;
use FurqanSiddiqui\Cardano\Response\AddressInfo;
use FurqanSiddiqui\Cardano\Response\NodeInfo;

/**
 * Class Cardano
 * @package FurqanSiddiqui\Cardano
 */
class Cardano
{
    public const SCALE = 6;
    public const MAX_LOVELACE = 45000000000000000;
    public const MIN_ACCOUNTS_INDEX = 2147483648;
    public const MAX_ACCOUNTS_INDEX = 4294967295;

    /** @var AbstractHttpClient */
    private AbstractHttpClient $httpClient;
    /** @var string */
    private string $host;
    /** @var int */
    private int $port;
    /** @var null|Wallets */
    private ?Wallets $_api_Wallets = null;

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
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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
     * @return Wallets
     */
    public function wallets(): Wallets
    {
        if ($this->_api_Wallets) {
            return $this->_api_Wallets;
        }

        $this->_api_Wallets = new Wallets($this);
        return $this->_api_Wallets;
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return AddressesList
     * @throws Exception\API_Exception
     * @throws Exception\API_ResponseException
     */
    public function addresses(int $page = 1, int $perPage = 10): AddressesList
    {
        $payload = [
            "page" => $page,
            "per_page" => $perPage
        ];

        $res = $this->httpClient->get('/api/v1/addresses', $payload);
        return new AddressesList($res->payload["data"] ?? null, $res->meta->pagination);
    }

    /**
     * @param string $address
     * @return AddressInfo
     * @throws AddressException
     * @throws Exception\API_Exception
     * @throws Exception\API_ResponseException
     */
    public function addressInfo(string $address): AddressInfo
    {
        if (!Validate::Address($address)) {
            throw new AddressException('Invalid Cardano SL address');
        }

        $res = $this->httpClient->get(sprintf('/api/v1/addresses/%s', $address));
        return new AddressInfo($res);
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
