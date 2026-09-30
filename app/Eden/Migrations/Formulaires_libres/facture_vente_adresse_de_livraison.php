<?php return [

    'type_element' => 'facture_vente',
    'titre_formulaire' => traduction('champs_libres.facture_vente.adresse_de_livraison.nom'),
    'champs_libres' => [
        [
            'nom_formulaire' => 'facture_vente_adresse_de_livraison',
            'type_element' => 'facture_vente',
            'nom_sql' => 'adresse_de_livraison',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '10',
            'taille_apres' => '0',
            'ordre' => '1',
            'condition_obligatoire' => "document.adresse_de_livraison_texte == '' || document.adresse_de_livraison_texte == null",
        ],
        [
            'nom_formulaire' => 'facture_vente_adresse_de_livraison',
            'type_element' => 'facture_vente',
            'nom_sql' => 'adresse_de_livraison_texte',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '10',
            'taille_apres' => '0',
            'ordre' => '2',
        ],
        [
            'nom_formulaire' => "facture_vente_adresse_de_livraison",
            'type_element' => "facture_vente",
            'nom_sql' => "",
            'ordre' => 3,
            'taille_avant' => 0,
            'taille_libelle' => 0,
            'taille_champ' => 12,
            'taille_apres' => 0,
            'type_champ' => 2,
            'nom_vue' => "watch_vuejs_adresse_de_livraison",
            'type_vue' => "document_standard",
        ],
    ]
];