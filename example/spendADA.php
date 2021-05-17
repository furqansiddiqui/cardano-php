<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

require "../vendor/autoload.php";

$cardano = new \FurqanSiddiqui\Cardano\Cardano("localhost", 8090);

$wallet = $cardano->wallets()->get("your-wallet-id-here");
$wallet->spendingPassword("your-passphrase");

$tx = new \FurqanSiddiqui\Cardano\API\RawTransaction();
$tx->nativeTransfer("payee-address", \FurqanSiddiqui\Cardano\API\LovelaceAmount::ADA("1.5")); // Transfer 1.5 ADA to "payee-address"

var_dump($wallet->sendTx($tx));
