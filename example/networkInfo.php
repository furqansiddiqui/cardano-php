<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

require "../vendor/autoload.php";

$cardano = new \FurqanSiddiqui\Cardano\Cardano("localhost", 8090);
var_dump($cardano->nodeInfo());

