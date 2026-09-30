<?php
return array (
    'table_libre' =>
        array (
            'nom_table' => 'Mappage des tables compatibles pour la conversion',
            'nom_table_sql' => 'mappage_table_conversion',
            'element' => 'mappage_table_conversion',
            'type_element' => 'mappage_table_conversion',
            'element_pluriel' => 'mappage_table_conversion',
            'fiche' => 1,
            'disponible_recherche_rapide' => 0,
            'creation_rapide' => 0,
            'affichage_recherche' => '#type_element_depart# - #type_element_arrivee#',
            'affichage_fiche_type' => '#type_element_depart# - #type_element_arrivee#',
            'affichage_dans_liste' => '#type_element_depart#, #type_element_arrivee#',
            'affichage_pour_select' => '#type_element_depart# - #type_element_arrivee#',
            'editable_client' => 0,
            'categorie' => '',
            'icone_fontawesome' => '',
        ),
    'champs_libres' =>
        array (
            'type_element_depart' =>
                array (
                    'nom' => 'Type élément de départ',
                    'recherche' => 1,
                    'obligatoire' => 1,
                ),
            'type_element_arrivee' =>
                array (
                    'nom' => 'Type élément d\'arrivée',
                    'recherche' => 1,
                    'obligatoire' => 1,
                ),
            'creation_auto' =>
                array (
                    'nom' => 'Création automatique',
                    'type' => 20,
                    'obligatoire' => 1,
                    'liste_choix' => 14
                ),
            'nom_formulaire' =>
                array (
                    'nom' => 'Nom du formulaire utilisé pour la transformation',
                ),
            'conversion_unique' =>
                array (
                    'nom' => 'Conversion unique',
                    'type' => 20,
                    'liste_choix' => 14
                ),
        ),
);