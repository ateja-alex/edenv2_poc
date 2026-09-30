<?php


return [
    'table_libre' => [
        'nom_table' => "Licence",
        'nom_table_sql' => "licence",
        'description' => "",
        'feminin' => "",
        'element' => "licence",
        'type_element' => "licence",
        'element_pluriel' => "licences",
        'fiche' => 1,
        
        'creation_rapide' => 0,
        'disponible_recherche_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'recherche' => 1,
            'obligatoire' => 1,
        ],
        'nombre' => [
            'nom' => "Nombre de licences",
            'type' => 2
        ],
        'specifique' => [
            'nom' => 'Spécifique',
            'liste_choix' => 14
        ],
        'nombre_utilises' => [
            'nom' => "Nombre utilisés",
            'type' => 2,
            'donnee_calculee_depuis' => "utilisateur",
            'donnee_calculee_requete' => "SELECT count(*) as resultat FROM utilisateur WHERE licence_id = #id_cible# AND type_utilisateur != 2 and coalesce(inactif,0) = 0",
            'donnee_calculee_champ_maj' => "licence_id",
        ],
    ],
];