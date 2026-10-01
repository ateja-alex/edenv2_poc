# 04 — Contraintes, équipe, priorités

## Contraintes
| Sujet | Réponse |
|---|---|
| Localisation | France ou UE, **France privilégiée** |
| Certifications | Aucune exigée aujourd'hui |
| Fournisseurs | Exclus : AWS, Azure, Google (donc sortie des S3 AWS actuels) |
| Budget | ≤ coût actuel (~2 321 € HT/mois tout compris), **objectif : baisser** |
| Engagement OVH | Aucun (résiliation au mois) |
| Architecture | **Tout en instances cloud** : pas de serveur dédié, pas d'hybride. MariaDB obligatoire (pas de MySQL) |
| Windows | Hors périmètre |
| SLA | 99,9 % visé, **aucun SLA contractuel** avec les clients |

## Équipe
- **Infra : 1 personne**, qui assure aussi l'astreinte. D'autres ont accès pour les problèmes simples.
- Développeurs : 3,5 ETP, prêts à passer à Docker + CI.
- Temps d'admin : ~50 h/mois, tout en interne.

→ **Bus factor = 1** : la cible doit réduire ce qu'une seule personne doit porter (managé plutôt qu'auto-géré, GitOps plutôt que gestes manuels).

## Priorités
1. Coût
2. SLA
3. Modernisation et standardisation
4. Temps consacré
