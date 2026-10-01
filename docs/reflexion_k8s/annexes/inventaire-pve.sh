#!/bin/bash
# inventaire-pve.sh — Inventaire complet d'un cluster Proxmox VE + Ceph
# À lancer en root sur UN SEUL nœud du cluster (/etc/pve est partagé).
# Lecture seule : aucune modification n'est faite sur le cluster.
#
# Usage : bash inventaire-pve.sh
# Résultat : inventaire-pve-<date>.tar.gz dans le répertoire courant

set -u
OUT="inventaire-pve-$(date +%Y%m%d)"
J="--output-format json"
mkdir -p "$OUT"/{cluster,nodes,vms,cts,ceph}

echo ">> Cluster"
pveversion -v                          > "$OUT/cluster/pveversion.txt"
pvecm status                           > "$OUT/cluster/pvecm-status.txt" 2>&1
for t in node vm storage; do
  pvesh get /cluster/resources --type $t $J > "$OUT/cluster/resources-$t.json"
done
pvesh get /storage $J                  > "$OUT/cluster/storage.json"
pvesh get /cluster/ha/resources $J     > "$OUT/cluster/ha-resources.json" 2>/dev/null
pvesh get /cluster/backup $J           > "$OUT/cluster/backup-jobs.json" 2>/dev/null
pvesh get /pools $J                    > "$OUT/cluster/pools.json" 2>/dev/null

echo ">> Nœuds"
for node in $(ls /etc/pve/nodes); do
  d="$OUT/nodes/$node"; mkdir -p "$d"
  pvesh get /nodes/$node/status $J     > "$d/status.json"
  pvesh get /nodes/$node/network $J    > "$d/network.json"
  pvesh get /nodes/$node/disks/list $J > "$d/disks.json" 2>/dev/null
  # Consommation réelle : moyenne et pics sur 1 mois, moyenne sur 1 an
  pvesh get /nodes/$node/rrddata --timeframe month --cf AVERAGE $J > "$d/rrd-month-avg.json"
  pvesh get /nodes/$node/rrddata --timeframe month --cf MAX $J     > "$d/rrd-month-max.json"
  pvesh get /nodes/$node/rrddata --timeframe year  --cf AVERAGE $J > "$d/rrd-year-avg.json"
done

# Config d'un guest, sans secrets ni notes libres (les lignes '#' = champ Notes)
clean_conf() { grep -vE '^(#|cipassword|sshkeys)' "$1"; }

echo ">> VMs (QEMU)"
for conf in /etc/pve/nodes/*/qemu-server/*.conf; do
  [ -e "$conf" ] || continue
  node=$(basename "$(dirname "$(dirname "$conf")")"); id=$(basename "$conf" .conf)
  d="$OUT/vms/$id"; mkdir -p "$d"; echo "$node" > "$d/node"
  clean_conf "$conf" > "$d/config.conf"
  pvesh get /nodes/$node/qemu/$id/rrddata --timeframe month --cf AVERAGE $J > "$d/rrd-month-avg.json" 2>/dev/null
  pvesh get /nodes/$node/qemu/$id/rrddata --timeframe month --cf MAX $J     > "$d/rrd-month-max.json" 2>/dev/null
  # Infos depuis l'intérieur de la VM (si qemu-guest-agent installé)
  for c in get-osinfo get-fsinfo get-host-name network-get-interfaces; do
    pvesh get /nodes/$node/qemu/$id/agent/$c $J > "$d/agent-$c.json" 2>/dev/null \
      || rm -f "$d/agent-$c.json"
  done
done

echo ">> Conteneurs (LXC)"
for conf in /etc/pve/nodes/*/lxc/*.conf; do
  [ -e "$conf" ] || continue
  node=$(basename "$(dirname "$(dirname "$conf")")"); id=$(basename "$conf" .conf)
  d="$OUT/cts/$id"; mkdir -p "$d"; echo "$node" > "$d/node"
  clean_conf "$conf" > "$d/config.conf"
  pvesh get /nodes/$node/lxc/$id/rrddata --timeframe month --cf AVERAGE $J > "$d/rrd-month-avg.json" 2>/dev/null
  pvesh get /nodes/$node/lxc/$id/rrddata --timeframe month --cf MAX $J     > "$d/rrd-month-max.json" 2>/dev/null
done

echo ">> Ceph"
ceph -s                     > "$OUT/ceph/status.txt"        2>&1
ceph df detail              > "$OUT/ceph/df.txt"            2>&1
ceph osd df tree            > "$OUT/ceph/osd-df-tree.txt"   2>&1
ceph osd pool ls detail     > "$OUT/ceph/pools.txt"         2>&1
for pool in $(ceph osd pool ls 2>/dev/null); do
  rbd du -p "$pool" > "$OUT/ceph/rbd-du-$pool.txt" 2>/dev/null   # taille réelle utilisée par disque
done

echo ">> Contrôle : recherche de secrets résiduels"
if grep -rilE 'password|passwd|secret|token|apikey|api_key' "$OUT" ; then
  echo "!! Fichiers ci-dessus à vérifier avant de partager l'archive."
fi

tar czf "$OUT.tar.gz" "$OUT" && echo ">> OK : $OUT.tar.gz"
