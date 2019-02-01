#!/usr/bin/env bash
set -eo pipefail

readonly CLUSTER=mainnet

if [[ "$1" == "-c" ]]; then
  shift
  rm -Rf                \
    db-${CLUSTER}       \
    wdb-${CLUSTER}      \
    secret-$CLUSTER.key \
    logs/$CLUSTER
fi

echo "Launch a single node and connect it to '${CLUSTER}' cluster..."

readonly TMP_TOPOLOGY_YAML=/tmp/topology.yaml
printf "wallet:
    relays: [[{ host: relays.cardano-mainnet.iohk.io }]]
    valency: 1
    fallbacks: 7" > "${TMP_TOPOLOGY_YAML}"

stack exec -- cardano-node                                  \
    --tlscert ./tls/server/server.crt                       \
    --tlskey ./tls/server/server.key                        \
    --tlsca ./tls/server/ca.crt                             \
    --topology "${TMP_TOPOLOGY_YAML}"                       \
    --log-config log-configs/connect-to-cluster.yaml        \
    --logs-prefix "logs/${CLUSTER}"                         \
    --db-path db-${CLUSTER}                                 \
    --wallet-address 127.0.0.1:8090                         \
    --wallet-db-path wdb-${CLUSTER}                         \
    --keyfile secret-$CLUSTER.key                           \
    --configuration-key mainnet_full
