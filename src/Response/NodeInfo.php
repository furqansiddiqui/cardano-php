<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Cardano\Response {

    use FurqanSiddiqui\Cardano\Exception\API_ResponseException;
    use FurqanSiddiqui\Cardano\Http\HttpJSONResponse;
    use FurqanSiddiqui\Cardano\Response\NodeInfo\LocalTimeInformation;

    /**
     * Class NodeInfo
     * @package FurqanSiddiqui\Cardano\Response
     */
    class NodeInfo implements ResponseModelInterface
    {
        /** @var QuantityUnitBlock */
        public QuantityUnitBlock $syncProgress;
        /** @var QuantityUnitBlock */
        public QuantityUnitBlock $blockchainHeight;
        /** @var QuantityUnitBlock */
        public QuantityUnitBlock $localBlockchainHeight;
        /** @var LocalTimeInformation */
        public LocalTimeInformation $localTimeInformation;
        /** @var array */
        public $subscriptionStatus;

        /**
         * NodeInfo constructor.
         * @param HttpJSONResponse $res
         * @throws API_ResponseException
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

namespace FurqanSiddiqui\Cardano\Response\NodeInfo {

    use FurqanSiddiqui\Cardano\Response\QuantityUnitBlock;
    use FurqanSiddiqui\Cardano\Response\ResponseModelInterface;

    /**
     * Class LocalTimeInformation
     * @package FurqanSiddiqui\Cardano\Response\NodeInfo
     */
    class LocalTimeInformation implements ResponseModelInterface
    {
        /** @var QuantityUnitBlock */
        public QuantityUnitBlock $differenceFromNtpServer;
    }
}

