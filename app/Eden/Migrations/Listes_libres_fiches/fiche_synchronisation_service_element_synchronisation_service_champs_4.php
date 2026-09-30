<?php
return [

    "type_element" => "synchronisation_service_champs",
    "fiche" => "synchronisation_service_element",
    "cle_etrangere" => "synchronisation_service_element_id",
    "formulaire_libre" => "formulaire_1_synchronisation_service_champs",
    "desactiver_filtres" => "",
    "desactiver_recherche" => "",
    "desactiver_actions" => "",
    "desactiver_options" => "",
    "desactiver_export" => "",
    "desactiver_creation" => "",
    "condition_desactiver_creation" => "",
    "desactiver_drag_drop_kanban" => "",
    "desactiver_kanban_sans_valeur" => "",
    'filtres_appliques' => [
      [
        'operateur' => 0,
        'blocs' => [],
        'filtres' => [[
            'type_element' => 'synchronisation_service_champs',
            'element_id' => null,
            'champ_liaison' => null,
            'valeurs' => ["0"],
            'nom_sql' => 'sens',
        ]]
      ],
    ],
    "colonnes" => [
        array("nom" => "Nom externe", "valeur" => "nom_externe", "ordre" => "1", "type" => "standard"),
        array("nom" => "Valeur", "valeur" => "#nom_sql##valeur_dur#", "ordre" => "2", "type" => "concatenation"),
    ],
    'calculs' => [],
    'filtres' => [],
];
