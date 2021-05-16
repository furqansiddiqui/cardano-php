<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response;

use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
use FurqanSiddiqui\Cardano\Validate;

/**
 * Class AccountInfo
 * @package FurqanSiddiqui\Cardano\Response
 */
class AccountInfo implements ResponseModelInterface
{
    /** @var AddressesList */
    public AddressesList $addresses;
    /** @var LovelaceAmount */
    public LovelaceAmount $amount;
    /** @var int */
    public int $index;
    /** @var string */
    public string $name;
    /** @var string */
    public string $walletId;

    /**
     * AccountInfo constructor.
     * @param $data
     * @param HttpJSONResponse\Meta\Pagination|null $pagination
     * @throws API_ResponseException
     * @throws \FurqanSiddiqui\Cardano\Exception\API_Exception
     * @throws \FurqanSiddiqui\Cardano\Exception\AmountException
     */
    public function __construct($data, ?HttpJSONResponse\Meta\Pagination $pagination = null)
    {
        if ($data instanceof HttpJSONResponse) {
            $data = $data->payload["data"] ?? null;
        }

        if (!is_array($data) || !$data) {
            throw API_ResponseException::RequirePropMissing("data");
        }

        $index = $data["index"];
        if (!Validate::AccountIndex($index)) {
            throw API_ResponseException::InvalidPropValue("accountInfo.index");
        }

        $this->index = $index;

        $accountInfoIndex = sprintf('accountInfo[%d].', $this->index);
        $this->amount = new LovelaceAmount($data["amount"] ?? null, $accountInfoIndex . "amount");

        // Account name
        $name = $data["name"];
        if (!Validate::AccountName($name)) {
            throw API_ResponseException::InvalidPropValue($accountInfoIndex . "name");
        }

        $this->name = $name;

        // Wallet identifier
        $walletId = $data["walletId"];
        if (!Validate::WalletIdentifier($walletId)) {
            throw API_ResponseException::InvalidPropValue($accountInfoIndex . "walletId");
        }

        $this->walletId = $walletId;

        // Addresses
        $addressesList = $data["addresses"] ?? null;
        $this->addresses = new AddressesList($addressesList, $pagination);
    }
}
