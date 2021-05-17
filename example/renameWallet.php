<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

require "../vendor/autoload.php";

$cardano = new \FurqanSiddiqui\Cardano\Cardano("localhost", 8090);

$renamed = $cardano->wallets()->get("your-wallet-id-here")
    ->update("new-name-for-your-wallet");
var_dump($renamed);
