<?php
return [

    "type_element" => "synchronisation_service_element_erreur",
    "fiche" => "synchronisation_service_element",
    "cle_etrangere" => "synchronisation_service_element_id",
    "formulaire_libre" => "",
    "desactiver_filtres" => "",
    "desactiver_recherche" => "",
    "desactiver_actions" => "1",
    "desactiver_options" => "1",
    "desactiver_export" => "",
    "desactiver_creation" => "1",
    "condition_desactiver_creation" => "",
    "desactiver_drag_drop_kanban" => "",
    "desactiver_kanban_sans_valeur" => "",
    "colonnes" => [
        array("nom" => "Date", "valeur" => "cree_le", "ordre" => "1",  "type" => "standard"),
        array("nom" => "Type élément", "valeur" => "type_element", "ordre" => "2",  "type" => "standard"),
        array("nom" => "Element id", "valeur" => "element_id", "ordre" => "3",  "type" => "standard"),
        array("nom" => "Type d'évenement", "valeur" => "type_evenement", "ordre" => "4",  "type" => "standard"),
        array("nom" => "Valeurs transmises", "valeur" => "valeurs_transmises", "ordre" => "5",  "type" => "standard"),
        array("nom" => "Code erreur", "valeur" => "code_erreur", "ordre" => "6",  "type" => "standard"),
        array("nom" => "Message erreur", "valeur" => "message_erreur", "ordre" => "7",  "type" => "standard"),
    ],
    'calculs' => [
    ],
    'filtres' => [
    ],
];