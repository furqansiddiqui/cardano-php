<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

require "../vendor/autoload.php";

$cardano = new \FurqanSiddiqui\Cardano\Cardano("localhost", 8090);

$wallet = $cardano->wallets()->get("your-wallet-id-here");
var_dump($wallet->getAllAddresses());
