#!/usr/bin/env bash
# Cree la base et l'utilisateur d'un client dans la MariaDB commune (eden-plateforme).
# Les mots de passe ne sont jamais affiches ni passes en argument :
#   - DB_PASSWORD est lu dans deploy_k8s/clients/<client>/secret.env et envoye par stdin ;
#   - le mot de passe root reste dans le pod (variable MARIADB_ROOT_PASSWORD).
# Usage : deploy_k8s/outils/creer-base-client.sh <client>
set -euo pipefail

client=${1:?usage : $0 <client>}
dossier="$(cd "$(dirname "$0")/.." && pwd)/clients/$client"

valeur() { grep -E "^$1=" "$2" | head -n 1 | cut -d= -f2-; }

base=$(valeur DB_DATABASE "$dossier/client.env")
utilisateur=$(valeur DB_USERNAME "$dossier/client.env")
mot_de_passe=$(valeur DB_PASSWORD "$dossier/secret.env")

[[ "$base" =~ ^[a-z0-9_]+$ && "$utilisateur" =~ ^[a-z0-9_]+$ ]] || { echo "DB_DATABASE / DB_USERNAME invalides" >&2; exit 1; }
[ -n "$mot_de_passe" ] || { echo "DB_PASSWORD vide dans $dossier/secret.env" >&2; exit 1; }

# apostrophes doublees pour la chaine SQL
mot_de_passe_sql=${mot_de_passe//\'/\'\'}

kubectl exec -i -n eden-plateforme mariadb-0 -- sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD"' <<SQL
CREATE DATABASE IF NOT EXISTS \`$base\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$utilisateur'@'%' IDENTIFIED BY '$mot_de_passe_sql';
ALTER USER '$utilisateur'@'%' IDENTIFIED BY '$mot_de_passe_sql';
GRANT ALL PRIVILEGES ON \`$base\`.* TO '$utilisateur'@'%';
SQL

echo "Base $base et utilisateur $utilisateur prets."
