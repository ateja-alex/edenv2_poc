<?php

return [

    'categorie' => 'rh',
    'icone' => 'table',
    'titre' => "Mes notes de frais",
    'description' => "Afficher mes notes de frais",
    'ordre' => 0,
    'inactif' => 0,

    // paramètres de la liste libre
    'liste_libre' => [

        'type_element' => 'note_de_frais',

        'colonnes' => [
            array("nom" => "#", "valeur" => "id", "ordre" => "0", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Date", "valeur" => "date", "ordre" => "1", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Client", "valeur" => "client_id", "ordre" => "2", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => "client_id", "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Projet", "valeur" => "projet_id", "ordre" => "3", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => "client_id", "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Statut", "valeur" => "statut", "ordre" => "4", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
            array("nom" => "Comptabilisée", "valeur" => "comptabilisee", "ordre" => "5", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
        ],
        'calculs' => [],

        // filtres disponibles pour les clients
        'filtres' => [['nom_sql' => 'date']],

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'note_de_frais',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["#utilisateur_connecte#"],
                    'nom_sql' => 'utilisateur_id',
                ],
            ]
          ],
        ],

    ],
];