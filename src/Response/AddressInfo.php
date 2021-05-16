<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_Exception;
use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class AddressInfo
 * @package FurqanSiddiqui\Cardano\Response
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

        $this->id = $data["id"] ?? null;
        if (!Validate::Address($this->id)) {
            throw API_ResponseException::InvalidPropValue("addressInfo.id");
        }

        $this->changeAddress = $data["changeAddress"] ?? null;
        if (!is_bool($this->changeAddress)) {
            throw API_Exception::InvalidPropValue("addressInfo.changeAddress", "bool", gettype($this->changeAddress));
        }

        $this->ownership = $data["ownership"] ?? null;
        if (!Validate::AddressOwnership($this->ownership)) {
            throw API_Exception::InvalidPropValue("addressInfo.ownership");
        }

        $this->used = $data["used"] ?? null;
        if (!is_bool($this->used)) {
            throw API_Exception::InvalidPropValue("addressInfo.used", "bool", gettype($this->used));
        }
    }
}
