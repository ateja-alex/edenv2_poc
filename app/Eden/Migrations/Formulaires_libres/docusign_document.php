<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "",
    ],
    'champs_libres' => [
        [
            'type_element' => "docusign_document",
            'nom_sql' => '',
            'taille_champ' => 12,
            'ordre' => 1,
            'type_champ' => 2,
            'nom_vue' => "choix_fichier",
            'type_vue' => "standard",
        ],
        [
            'type_element' => 'docusign_document',
            'nom_sql' => 'fichier',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "10",
            'taille_apres' => "0",
            'ordre' => 2,
            'condition_obligatoire' => "docusign_document.lien_fichier == 'nouveau_fichier'",
            'condition_affichage_v_if' => "docusign_document.lien_fichier == 'nouveau_fichier'"
        ],
        [
            'type_element' => 'docusign_document',
            'nom_sql' => 'remplacement_fichier',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => 3,
            'condition_lecture_seule' => "destination_choisi !== null && destination_choisi.type == 7",
            'condition_affichage_v_if' => "remplacement_possible"
        ],
    ],
];