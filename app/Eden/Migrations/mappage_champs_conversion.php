<?php
return array (
    'table_libre' =>
        array (
            'nom_table' => 'Mappage des champs compatibles pour la conversion',
            'nom_table_sql' => 'mappage_champs_conversion',
            'element' => 'mappage_champs_conversion',
            'type_element' => 'mappage_champs_conversion',
            'element_pluriel' => 'mappage_champs_conversion',
            'fiche' => 0,
            'disponible_recherche_rapide' => 0,
            'creation_rapide' => 0,
            'affichage_recherche' => '#champ_depart# - #champ_arrivee#',
            'affichage_fiche_type' => '#champ_depart# - #champ_arrivee#',
            'affichage_dans_liste' => '#champ_depart#, #champ_arrivee#',
            'affichage_pour_select' => '#champ_depart# - #champ_arrivee#',
            'editable_client' => 0,
            'categorie' => '',
            'icone_fontawesome' => '',
        ),
    'champs_libres' =>
        array (

            'mappage_table' =>
                array (
                    'nom' => 'Mappage table',
                    'recherche' => 1,
                    'obligatoire' => 1,
                    'type' => 42,
                    'type_element_ajax' => 'mappage_table_conversion'
                ),
            'champ_depart' =>
                array (
                    'nom' => 'Champ de départ',
                    'recherche' => 1,
                    'obligatoire' => 1,
                ),
            'champ_arrivee' =>
                array (
                    'nom' => 'Champ d\'arrivée',
                    'recherche' => 1,
                    'obligatoire' => 1,
                ),
            'type_element_arrivee' =>
                array (
                    'nom' => 'Type élément d\'arrivée',
                ),
            'champ_liaison' =>
                array (
                    'nom' => 'Champ de liaison',
                ),
        ),
);