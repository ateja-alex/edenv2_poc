<?php
return [

    "type_element" => "synchronisation_service_element",
    "fiche" => "synchronisation_service",
    "cle_etrangere" => "synchronisation_service_id",
    "formulaire_libre" => "",
    "desactiver_filtres" => "",
    "desactiver_recherche" => "",
    "desactiver_actions" => "",
    "desactiver_options" => "",
    "desactiver_export" => "",
    "desactiver_creation" => "",
    "condition_desactiver_creation" => "",
    "desactiver_drag_drop_kanban" => "",
    "desactiver_kanban_sans_valeur" => "",

    "colonnes" => [
        array('nom' => 'Type de synchronisation', 'valeur' => 'type_synchronisation', 'ordre' => 1),
        array('nom' => 'Type élément', 'valeur' => 'type_element', 'ordre' => 2),
        array('nom' => 'Type externe', 'valeur' => 'type_externe', 'ordre' => 3),
        array('nom' => 'Désactivé', 'valeur' => '', 'ordre' => 4, 'type' => 'champ', 'champ' => 'desactive'),
    ],
    'calculs' => [
    ],
    'filtres' => [
    ],
];