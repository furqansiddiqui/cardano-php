<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_Exception;
use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;
use CardanoSL\Validate;

/**
 * Class AddressInfo
 * @package CardanoSL\Response
 */
class AddressInfo
{
    /** @var bool */
    public $changeAddress;
    /** @var string */
    public $id;
    /** @var string */
    public $ownership;
    /** @var bool */
    public $used;

    /**
     * AddressInfo constructor.
     * @param $data
     * @throws API_Exception
     * @throws API_ResponseException
     */
    public function __construct($data)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $data->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("addressInfo.data");
        }

        $this->changeAddress = $data["changeAddress"] ?? null;
        $this->id = $data["id"] ?? null;
        $this->ownership = $data["ownership"] ?? null;
        $this->used = $data["used"] ?? null;

        $this->validate();
    }

    /**
     * @throws API_Exception
     */
    public function validate(): void
    {
        if (!Validate::Address($this->id)) {
            throw API_Exception::InvalidPropValue("addressInfo.id");
        }

        if (!is_bool($this->changeAddress)) {
            throw API_Exception::InvalidPropValue("addressInfo.changeAddress", "bool", gettype($this->changeAddress));
        }

        if (!Validate::AddressOwnership($this->ownership)) {
            throw API_Exception::InvalidPropValue("addressInfo.ownership");
        }

        if (!is_bool($this->used)) {
            throw API_Exception::InvalidPropValue("addressInfo.used", "bool", gettype($this->used));
        }
    }
}