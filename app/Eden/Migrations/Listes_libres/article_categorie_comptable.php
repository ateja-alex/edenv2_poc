<?php

return [
    'type_element' => 'article_categorie_comptable',
    'colonnes' => [
        array("nom" => "Catégorie comptable", "valeur" => "categorie_comptable_id", "ordre" => "4", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  "alignement_colonne" => "", "tri_par_defaut" => "0", "sens_tri_par_defaut" => "0", "arguments" => "", ),
        array("nom" => "Compte produit", "valeur" => "compte_produit", "ordre" => "5", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  "alignement_colonne" => "", "tri_par_defaut" => "0", "sens_tri_par_defaut" => "0", "arguments" => "", ),
        array("nom" => "Code de TVA ventes", "valeur" => "code_tva_id", "ordre" => "6", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  "alignement_colonne" => "", "tri_par_defaut" => "0", "sens_tri_par_defaut" => "0", "arguments" => "", ),
        array("nom" => "Compte charge", "valeur" => "compte_charge", "ordre" => "7", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  "alignement_colonne" => "", "tri_par_defaut" => "0", "sens_tri_par_defaut" => "0", "arguments" => "", ),
        array("nom" => "Code de TVA achats", "valeur" => "code_tva_achat_id", "ordre" => "8", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  "alignement_colonne" => "", "tri_par_defaut" => "0", "sens_tri_par_defaut" => "0", "arguments" => "", ),
        array("nom" => "Famille", "valeur" => "#article_id.famille_id# - #article_id.designation#", "ordre" => "2", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "concatenation", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  "alignement_colonne" => "", "tri_par_defaut" => "0", "sens_tri_par_defaut" => "", "arguments" => "", ),
    ],
    'calculs' => [
    ],
    'filtres' => [
        array("nom_sql" => "famille_id", "type_element" => "article", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "1", "champ_de_liaison" => "article_id", "afficher_categories_liste" => "0", "rapport_id" => "", ),
        array("nom_sql" => "categorie_comptable_id", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "3", "champ_de_liaison" => "", "afficher_categories_liste" => "0", "rapport_id" => "", ),
        array("nom_sql" => "compte_produit", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "4", "champ_de_liaison" => "", "afficher_categories_liste" => "0", "rapport_id" => "", ),
        array("nom_sql" => "code_tva_id", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "5", "champ_de_liaison" => "", "afficher_categories_liste" => "0", "rapport_id" => "", ),
        array("nom_sql" => "compte_charge", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "6", "champ_de_liaison" => "", "afficher_categories_liste" => "0", "rapport_id" => "", ),
        array("nom_sql" => "code_tva_achat_id", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "7", "champ_de_liaison" => "", "afficher_categories_liste" => "0", "rapport_id" => "", ),
        array("nom_sql" => "article_id", "type_element" => "", "type_filtre" => "", "methode_filtre" => "", "emplacement" => "0", "ordre" => "2", "champ_de_liaison" => "", "afficher_categories_liste" => "0", "rapport_id" => "", ),
    ],
    'filtres_appliques' => [
        [
            'operateur' => 0,
            'exclu' => 1,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'article_categorie_comptable',
                    'champ_liaison' => NULL,
                    'valeurs' => [1],
                    'nom_sql' => 'eco_contribution',
                    'operateur' => 0,
                ],
            ],
        ],
    ],
];
