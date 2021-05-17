# Cardano PHP

> This lib has been re-written for **Shelley** phase of Cardano roadmap.

## Prerequisites

* PHP 7.4+
* You need to be running a cardano node+wallet.  
  *(Inspect the docker-compose file in `etc` directory)*

## Installation

`composer require furqansiddiqui/cardano-php`

## Examples

Following functionalities have been completed and test:  
*(example file may not be available for few; but shouldn't be hard to figure out)*

* [Create/restore cardano wallets using BIP39](example/createOrRestoreWallet.php)
    * [Rename loaded wallets on node](example/renameWallet.php)
    * Delete loaded wallets on node
* [Get all wallet addresses](example/getWalletAddresses.php)
* [Get information on a specific address](example/getAddressInfo.php)
* [Get all wallet transactions](example/getWalletTransactions.php)
    * [Get information on a specific wallet transaction](example/getWalletTransaction.php)
* [Spend ADA to receiver's address](example/spendADA.php)
* Spend Cardano assets/tokens to receiver's address


