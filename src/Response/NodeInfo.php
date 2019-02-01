<?php
declare(strict_types=1);

namespace CardanoSL\Response {

    use CardanoSL\Exception\API_ResponseException;
    use CardanoSL\Http\HttpJSONResponse;
    use CardanoSL\Response\NodeInfo\localTimeInformation;

    /**
     * Class NodeInfo
     * @package CardanoSL\Response
     */
    class NodeInfo implements ResponseModelInterface
    {
        /** @var QuantityUnitBlock */
        public $syncProgress;
        /** @var QuantityUnitBlock */
        public $blockchainHeight;
        /** @var QuantityUnitBlock */
        public $localBlockchainHeight;
        /** @var LocalTimeInformation */
        public $localTimeInformation;
        /** @var array */
        public $subscriptionStatus;

        /**
         * NodeInfo constructor.
         * @param HttpJSONResponse $res
         * @throws \CardanoSL\Exception\API_ResponseException
         */
        public function __construct(HttpJSONResponse $res)
        {
            $data = $res->payload["data"] ?? null;
            $this->syncProgress = QuantityUnitBlock::Construct("nodeInfo.syncProgress", $data["syncProgress"] ?? null);
            $this->blockchainHeight = QuantityUnitBlock::Construct("nodeInfo.blockchainHeight", $data["blockchainHeight"] ?? null);
            $this->localBlockchainHeight = QuantityUnitBlock::Construct("nodeInfo.localBlockchainHeight", $data["localBlockchainHeight"] ?? null);
            $this->localTimeInformation = new LocalTimeInformation();
            $this->localTimeInformation->differenceFromNtpServer = QuantityUnitBlock::Construct(
                "nodeInfo.localTimeInformation.differenceFromNtpServer",
                $data["localTimeInformation"]["differenceFromNtpServer"] ?? null
            );

            $this->subscriptionStatus = $data["subscriptionStatus"] ?? null;
            if (!is_array($this->subscriptionStatus) || !$this->subscriptionStatus) {
                throw API_ResponseException::RequirePropMissing("nodeInfo.subscriptionStatus");
            }
        }
    }
}

namespace CardanoSL\Response\NodeInfo {

    use CardanoSL\Response\QuantityUnitBlock;
    use CardanoSL\Response\ResponseModelInterface;

    /**
     * Class localTimeInformation
     * @package CardanoSL\Response\NodeInfo
     */
    class LocalTimeInformation implements ResponseModelInterface
    {
        /** @var QuantityUnitBlock */
        public $differenceFromNtpServer;
    }
}

