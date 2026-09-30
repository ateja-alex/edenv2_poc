# std-eden

ERP Laravel 12 construit sur le framework maison **Eden** (submodule
`app/Eden`, assets `public/eden`) : framework **piloté par les métadonnées** —
les écrans CRUD se déclarent en config, on ne code presque jamais de
contrôleur. L'auth et les droits reposent sur la session
(`moi()` = `session('utilisateur_eden')`) : sans contexte utilisateur, aucun
scoping ne s'applique.

**Avant toute modification, lire le document pertinent dans
[docs/tech/](docs/tech/README.md)** :

Toute modification structurante doit mettre à jour ces docs.

Les migrations Eden ne passent pas par `artisan migrate` : voir
« Exécuter les migrations » dans elements-et-migrations.md.
