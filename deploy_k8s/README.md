# Déploiement Kubernetes du POC EDEN (à la main)

Manifests appliqués à la main avec Kustomize (`kubectl apply -k`), sans ArgoCD pour l'instant :
mise en place, plusieurs clients, montée en charge, scaling des pods et des nœuds.

```
deploy_k8s/
  plateforme/           # une fois par cluster
    traefik-values.yaml #   ingress (Helm), redirection HTTP -> HTTPS
    mariadb/            #   MariaDB commune (namespace eden-plateforme)
  base/                 # commun à tous les clients (ne pas appliquer seul)
  clients/<client>/     # un overlay par client (namespace eden-<client>)
  outils/               # pods temporaires (restauration des fichiers)
```

Par client (namespace `eden-<client>`) :

| Objet | Rôle |
|---|---|
| `eden-web` (Deployment, 2 à 6 pods) | nginx + PHP-FPM dans le même pod, HPA sur le CPU de PHP, PDB, 1 pod par nœud si possible |
| `eden-worker` | `queue:work` |
| `eden-migrate` (Job) | `php artisan eden:migrate`, une fois par déploiement |
| `cron-*` (CronJobs) | `php artisan eden:cron <tâche>`, horaires de la crontab de la VM |
| `redis` | sessions et cache du client (pas partagé : EDEN fait des `FLUSHDB`) |
| `eden-fichiers` (PVC RWX) | `storage_app/` et `migrations/`, partagés par tous les pods |

Commun : MariaDB (`eden-plateforme`), une base et un utilisateur par client.

Les secrets ne sont jamais dans git : `secret.env` (ignoré) à côté de chaque `secret.env.example`.

## 1. Cluster (une fois)

1. Cluster MKS, pool de nœuds en **autoscaling** (min/max dans l'espace client OVH ou Terraform).
2. Vérifier :
   ```bash
   kubectl get storageclass                 # noms RWX (File Storage) et block High Speed Gen2
   kubectl top nodes                        # metrics-server présent (requis par le HPA)
   kubectl get nodes -o wide
   ```
   Reporter les noms de classes dans `base/fichiers.pvc.yaml` et `plateforme/mariadb/mariadb.yaml`,
   et le réseau des pods dans `TRUSTED_PROXIES` (`clients/*/client.env`).
3. Traefik : voir l'en-tête de `plateforme/traefik-values.yaml`.
4. MariaDB commune :
   ```bash
   cp deploy_k8s/plateforme/mariadb/secret.env.example deploy_k8s/plateforme/mariadb/secret.env   # puis remplir
   kubectl apply -k deploy_k8s/plateforme/mariadb
   ```

## 2. Ajouter un client

1. Copier `clients/tsi` en `clients/<client>` et adapter : `namespace`, `labels`, hôte (patch Ingress),
   `client.env` (`APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `REDIS_PREFIX`), `cronjobs.yaml` (crontab de la VM).
2. `secret.env` à partir de `secret.env.example` (`APP_KEY` du client : la même que sur sa VM).
3. Accès aux images GHCR (jeton GitHub en lecture des packages, à créer soi-même) :
   ```bash
   kubectl create namespace eden-<client>
   kubectl create secret docker-registry ghcr -n eden-<client> \
     --docker-server=ghcr.io --docker-username=<compte> --docker-password=<jeton>
   ```
4. Base et utilisateur dans la MariaDB commune :
   ```bash
   kubectl exec -it -n eden-plateforme mariadb-0 -- mariadb -uroot -p
   ```
   ```sql
   CREATE DATABASE eden_<client> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'eden_<client>'@'%' IDENTIFIED BY '<DB_PASSWORD du secret.env>';
   GRANT ALL PRIVILEGES ON eden_<client>.* TO 'eden_<client>'@'%';
   ```
5. Restaurer les données (§3), puis appliquer :
   ```bash
   kubectl apply -k deploy_k8s/clients/<client>
   kubectl logs -n eden-<client> -f job/eden-migrate
   ```

## 3. Restaurer un client

La base et les fichiers doivent venir **du même instant** (le paramétrage vit des deux côtés).

```bash
# Base
kubectl exec -i -n eden-plateforme mariadb-0 -- sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" eden_<client>' < dump.sql

# Fichiers : storage/app et app/Migrations de la VM, dans le volume du client
kubectl apply -n eden-<client> -f deploy_k8s/base/fichiers.pvc.yaml        # si le client n'est pas encore appliqué
kubectl apply -n eden-<client> -f deploy_k8s/outils/copie-fichiers.pod.yaml
tar -C <vm>/storage/app -cf - . | kubectl exec -i -n eden-<client> copie-fichiers -- tar -C /v/storage_app -xf -
tar -C <vm>/app/Migrations -cf - . | kubectl exec -i -n eden-<client> copie-fichiers -- tar -C /v/migrations -xf -
# www-data, et dossiers lisibles par nginx (qui ne tourne pas sous l'utilisateur PHP)
kubectl exec -n eden-<client> copie-fichiers -- sh -c 'chown -R 33:33 /v && find /v -type d -exec chmod 755 {} +'
kubectl delete pod -n eden-<client> copie-fichiers
```

## 4. Déployer une nouvelle version

1. Tags `sha-xxxxxxx` dans `clients/<client>/kustomization.yaml` (`images:`).
2. Le Job de migration est immuable : le supprimer avant de réappliquer.
   ```bash
   kubectl delete job -n eden-<client> eden-migrate --ignore-not-found
   kubectl apply -k deploy_k8s/clients/<client>
   kubectl logs -n eden-<client> -f job/eden-migrate
   kubectl rollout status -n eden-<client> deployment/eden-web
   ```
   Une base à jour migre en ~10 s ; une montée de version avec des reprises de données peut durer des heures
   (TSI : ~7 h 40 depuis sa version de prod) → déploiements lourds de nuit.

## 5. Observer le scaling

```bash
kubectl get hpa -A -w                                    # pods : 2 à 6 par client, cible 70 % CPU de PHP
kubectl get pods -n eden-<client> -o wide -w             # répartition sur les nœuds
kubectl get nodes -w                                     # nœuds ajoutés/retirés par l'autoscaler MKS
kubectl get events -A --field-selector reason=TriggeredScaleUp
kubectl top pods -n eden-<client> --containers
```

Résilience : `kubectl drain <nœud> --ignore-daemonsets --delete-emptydir-data` (le PDB garde 1 pod par client).

## Limites connues (POC)

- Certificat TLS : celui par défaut de Traefik (auto-signé).
- Redis sans persistance : un redémarrage déconnecte les utilisateurs du client.
- CronJobs à la minute : un pod par exécution (→ à terme, planificateur Laravel, voir `09-poc.md`).
- Job `eden-migrate` et pods appliqués en même temps : les pods démarrent pendant la migration.
  Attendre la fin du Job avant d'ouvrir l'accès au client.
