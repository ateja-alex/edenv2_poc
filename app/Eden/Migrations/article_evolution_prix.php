<?php

return [
    'table_libre' => [
        'nom_table' => "Article évolution prix",
        'nom_table_sql' => "article_evolution_prix",
        'description' => "",
        'feminin' => "",
        'element' => "article_evolution_prix",
        'type_element' => "article_evolution_prix",
        'element_pluriel' => "articles_evolution_prix",
        'fiche' => 1,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'module' => 'Gestion commerciale',
        'editable_client' => 1,
        'categorie' => 'element_primaire',
        'icone_fontawesome' => 'fa-cart-arrow-down',
    ],
    'champs_libres' => [
        'article_id' => [
            'nom' => "Article",
            'type' => 42,
            'type_element_ajax' => 'article',
        ],
        'famille_id' => [
            'nom' => "Famille",
            'type' => 42,
            'type_element_ajax' => 'famille',
        ],
        'fournisseur_id' => [
            'nom' => "Fournisseur",
            'type' => 42,
            'type_element_ajax' => 'fournisseur',
        ],
        'date_application' => [
            'nom' => "Date d'application",
            'type' => 4,
            'valeur_defaut' => '#aujourdhui#',
        ],
        'prix_vente' => [
            'nom' => "Prix de vente",
            'type' => 3,
        ],
        'avertissement' => [
            'nom' => "Avertissement",
            'type' => "-2",
            'contenu' => "Cette mise à jour peut-être longue (rythme moyen: 300 articles par minute). Merci de patienter après le clic sur \"Enregistrer\" et de ne pas fermer la fenêtre ou mettre votre ordinateur en veille.",
        ]
    ],
];
