<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "parametrage_destinataire_erp",
            'type_element' => 'parametrage_destinataire_erp',
            'nom_sql' => 'notification_manuelle_id',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "1",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
        ],
        [
            'nom_formulaire' => "parametrage_destinataire_erp",
            'type_element' => 'parametrage_destinataire_erp',
            'nom_sql' => '',
            'taille_avant' => "0",
            'taille_libelle' => "0",
            'taille_champ' => "12",
            'taille_apres' => "0",
            'ordre' => "2",
            'type_champ' => 2,
            'valeur_html' => "",
            'id_editeur' => "",
            'nom_vue' => "destinataire",
            'type_vue' => "standard",
        ],
        [
            'nom_formulaire' => "parametrage_destinataire_erp",
            'type_element' => 'parametrage_destinataire_erp',
            'nom_sql' => 'nom_simplifie',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'parametrage_destinataire_erp.lien_champ != null && parametrage_destinataire_erp.lien_champ != \'\''
        ],
    ]
];