<?php

return [

    'categorie' => 'gestion_commerciale',
    'icone' => 'table',
    'titre' => "Tickets blacklistés",
    'description' => "Liste des tickets blacklistés",
    'ordre' => 0,
    'inactif' => 0,
    // paramètres de la liste libre
    'liste_libre' => [

        'type_element' => 'ticket_client',
        'colonnes' => [

            array("nom" => "#", "valeur" => "id", "ordre" => "1", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Créé le", "valeur" => "cree_le", "ordre" => "2", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Titre", "valeur" => "titre", "ordre" => "3", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Statut", "valeur" => "statut", "ordre" => "4", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Priorité", "valeur" => "priorite", "ordre" => "5", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", ),
            array("nom" => "Affectation", "valeur" => "", "ordre" => "6", "lien_vers_element" => "0", "methode" => "liste_affectation", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
        ],
        'calculs' => [

        ],
        'filtres' => [
            array("nom_sql" => "utilisateur_id", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", ),
            array("nom_sql" => "statut", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", ),
            array("nom_sql" => "priorite", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", ),
        ],
    ],
];


