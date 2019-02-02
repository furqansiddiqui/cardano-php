<?php
declare(strict_types=1);

namespace CardanoSL\Response;

use CardanoSL\Exception\API_ResponseException;
use CardanoSL\Http\HttpJSONResponse;
use CardanoSL\Validate;

/**
 * Class AccountInfo
 * @package CardanoSL\Response
 */
class AccountInfo implements ResponseModelInterface
{
    /** @var AddressesList */
    public $addresses;
    /** @var LovelaceAmount */
    public $amount;
    /** @var int */
    public $index;
    /** @var string */
    public $name;
    /** @var string */
    public $walletId;

    /**
     * AccountInfo constructor.
     * @param $data
     * @param HttpJSONResponse\Meta\Pagination|null $pagination
     * @throws API_ResponseException
     * @throws \CardanoSL\Exception\API_Exception
     * @throws \CardanoSL\Exception\AmountException
     */
    public function __construct($data, ?HttpJSONResponse\Meta\Pagination $pagination = null)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $data->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $this->index = $data["index"] ?? null;
        if (!Validate::AccountIndex($this->index)) {
            throw API_ResponseException::InvalidPropValue("accountInfo.index");
        }

        $accountInfoIndex = sprintf('accountInfo[%d].', $this->index);

        $this->amount = new LovelaceAmount($data["amount"] ?? null, $accountInfoIndex . "amount");
        $this->name = $data["name"] ?? null;
        if (!Validate::AccountName($this->name)) {
            throw API_ResponseException::InvalidPropValue($accountInfoIndex . "name");
        }

        $this->walletId = $data["walletId"] ?? null;
        if (!Validate::WalletIdentifier($this->walletId)) {
            throw API_ResponseException::InvalidPropValue($accountInfoIndex . "walletId");
        }

        // Addresses
        $addressesList = $data["addresses"] ?? null;
        $this->addresses = new AddressesList($addressesList, $pagination);
    }
}