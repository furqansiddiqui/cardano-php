<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

require "../vendor/autoload.php";

$cardano = new \FurqanSiddiqui\Cardano\Cardano("localhost", 8090);

$name = "My ADA Wallet";
$mnemonic = \FurqanSiddiqui\BIP39\BIP39::Generate(24); // or use \FurqanSiddiqui\BIP39\BIP39::Words("your-15-to-24-words-here");
$passphrase = "changeme";

$wallet = $cardano->wallets()->create($name, $mnemonic, $passphrase);
var_dump($wallet);
