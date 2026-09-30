<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Exceptions\Eden_exception;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;

class S20260319_rattrapage_filtrage_et_selection_conditionnelle implements Script{

    public function execute(){

        if(!is_dir(app_path('Migrations')))
            return true;
            
        $repertoire_champs_libres = scandir(app_path('Migrations'));

        foreach($repertoire_champs_libres as $fichier) {

            if(in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees','Listes_libres_couleurs','Listes_libres_autresvues','Scripts', 'Vue_sql')))
                continue;

            $contenu_tmp = require(app_path('Migrations/'.$fichier));

            $index_tableau = str_replace('.php', '', $fichier);

            $champs_libres[$index_tableau] = $contenu_tmp['champs_libres'];
        }

        $recherches_avancees_a_enregistrer = [];
        $regenerer_migrations_type_element = [];

        foreach($champs_libres as $type_element => $champs){

            if(!table_libre_existe($type_element))
                continue;

            foreach($champs as $champ){

                $filtres_a_enregister = [];

                if(!empty($champ['filtrage'])){

                    $champ_libre = champ_libre_modele($champ['type_element'], $champ['nom_sql']);

                    if(empty($champ_libre))
                        continue;

                    $filtrages_par_type_element = json_decode($champ['filtrage'], true);

                    if($champ_libre->type == 42 || $champ_libre->type_reference == 42)
                        $filtrages_par_type_element = [$champ_libre->type_element_ajax => $filtrages_par_type_element];

                    foreach($filtrages_par_type_element as $type_element_ajax => $filtrages){

                        $filtres_filtrage = [];

                        foreach($filtrages as $filtrage){

                            $champ_libre_filtrage = champ_libre_modele($type_element_ajax, $filtrage['champ']);

                            if($champ_libre_filtrage->type == 0){

                                $variable = 'contient';

                                if($filtrage['condition'] == 'Where'){

                                    if($filtrage['symbole'] == '=')
                                        $variable = 'egal_a';

                                    if($filtrage['symbole'] == '!=')
                                        $variable = 'ne_contient_pas';

                                    if($filtrage['symbole'] == 'LIKE' && !starts_with($filtrage['valeur'], '%') && str_ends_with($filtrage['valeur'], '%'))
                                        $variable = 'commence_par';

                                    if($filtrage['symbole'] == 'LIKE' && strpos($filtrage['valeur'], '%') === false )
                                        $variable = 'egal_a';
                                }
                                else if($filtrage['condition'] == 'WhereNull')
                                    $variable = 'vide';
                                else if($filtrage['condition'] == 'WhereNotNull')
                                    $variable = 'non_vide';
                                else
                                    throw new Eden_exception("Condition de filtrage non prise en charge dans le rattrapage : " . $filtrage['condition']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);

                                $valeurs = [
                                    'variable' => $variable,
                                    'texte' => str_replace('%','',$filtrage['valeur']),
                                ];
                            }
                            else if($champ_libre_filtrage->type == 5){
                                if($filtrage['condition'] == 'Where'){

                                    if(!in_array($filtrage['symbole'], ['>','<','>=','<=']))
                                        throw new Eden_exception("Symbole de filtrage non pris en charge dans le rattrapage : " . $filtrage['symbole']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);

                                    $valeurs = [
                                        'debut' => $filtrage['symbole'] == '>' || $filtrage['symbole'] == '>=' ? $filtrage['valeur'] : null,
                                        'fin' => $filtrage['symbole'] == '<' || $filtrage['symbole'] == '<=' ? $filtrage['valeur'] : null,
                                        'variable' => null
                                    ];
                                }
                                else
                                    throw new Eden_exception("Condition de filtrage non prise en charge dans le rattrapage : " . $filtrage['condition']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);
                            }
                            else if($champ_libre_filtrage->type == 3){
                                if($filtrage['condition'] == 'Where'){

                                    if(!in_array($filtrage['symbole'], ['=','>','<','>=','<=']))
                                        throw new Eden_exception("Symbole de filtrage non pris en charge dans le rattrapage : " . $filtrage['symbole']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);

                                    $correspondance_variable = [
                                        '=' => 'egal_a',
                                        '>' => 'superieur',
                                        '>=' => 'superieur_egal',
                                        '<' => 'inferieur',
                                        '<=' => 'inferieur_egal'
                                    ];
                                    $valeurs = [
                                        'montant' => $filtrage['valeur'],
                                        'variable' => $correspondance_variable[$filtrage['symbole']]
                                    ];
                                }
                                else
                                    throw new Eden_exception("Condition de filtrage non prise en charge dans le rattrapage : " . $filtrage['condition']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);
                            }
                            else if($champ_libre_filtrage->type == 42){
                                
                                if($filtrage['condition'] == 'Where' && (empty($filtrage['symbole']) || $filtrage['symbole'] == '=')){
                                    $valeurs = [$filtrage['valeur']];
                                }
                                else if($filtrage['condition'] == 'WhereIn'){
                                    $valeurs = explode(',',$filtrage['valeur']);
                                }
                                else if($filtrage['condition'] == 'WhereNull'){

                                    if(sizeof(array_filter($filtres_filtrage, function($filtre) use ($filtrage){
                                        return $filtre['nom_sql'] == $filtrage['champ'] && in_array(0, $filtre['valeurs']);
                                    })) > 0){
                                        continue;
                                    }
                                    $variable = 'vide';

                                }
                                else if($filtrage['condition'] == 'WhereNotNull')
                                    $variable = 'non_vide';
                                else
                                    throw new Eden_exception("Condition de filtrage non prise en charge dans le rattrapage : " . $filtrage['condition']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);
                            }
                            else if(in_array($champ_libre_filtrage->type, [1,20])){
                                if($filtrage['condition'] == 'Where' && (empty($filtrage['symbole']) || $filtrage['symbole'] == '=')){
                                    $valeurs = [$filtrage['valeur']];
                                }
                                else if($filtrage['condition'] == 'WhereIn'){
                                    $valeurs = explode(',',$filtrage['valeur']);
                                }
                                else if($filtrage['condition'] == 'WhereNull'){

                                    if(sizeof(array_filter($filtres_filtrage, function($filtre) use ($filtrage){
                                        return $filtre['nom_sql'] == $filtrage['champ'] && in_array(0, $filtre['valeurs']);
                                    })) > 0){
                                        continue;
                                    }
                                    $variable = [0];
                                }
                                else
                                    throw new Eden_exception("Condition de filtrage non prise en charge dans le rattrapage : " . $filtrage['condition']." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);
                            }
                            else
                                throw new Eden_exception("Type de champ libre non pris en charge dans le rattrapage : " . $champ_libre_filtrage->type." pour le champ libre ".$champ_libre->type_element.'.'.$champ_libre->nom_sql);

                            $filtres_filtrage[] = array (
                                'type_element' => $type_element_ajax,
                                'champ_liaison' => NULL,
                                'valeurs' => $valeurs,
                                'nom_sql' => $filtrage['champ'],
                                'operateur' => $filtrage['condition_ou'] ? 1 : 0,
                            );
                        }

                        if(empty($filtres_filtrage))
                            continue;

                        $filtres_a_enregister[$type_element_ajax] = [
                            array (
                                'operateur' => 0,
                                'exclu' => 0,
                                'blocs' => array (),
                                'filtres' => $filtres_filtrage
                            ),
			            ];
                    }
                }

                $filtres_selection_conditionnelle = [];

                if(!empty($champ['selection_conditionnelle_champ']) && !empty($champ['selection_conditionnelle_valeur'])){

                    $selections_conditionnelles = explode('|',$champ['selection_conditionnelle_champ']);

                    foreach($selections_conditionnelles as $index => $selection_conditionnelle_champ){
                        $filtres_selection_conditionnelle[] = array (
                            'type_element' => $champ['type_element_ajax'],
                            'champ_liaison' => NULL,
                            'valeurs' => 'lien_champ|'.$champ['type_element'].'.'.$champ['selection_conditionnelle_valeur'],
                            'nom_sql' => $selection_conditionnelle_champ,
                            'operateur' => sizeof($selections_conditionnelles) > 1 && $index > 0 ? 1 : 0,
                        );
                    }

                    if(isset($filtres_a_enregister[$champ['type_element_ajax']])){

                        $avec_ou = array_filter($filtres_a_enregister[$champ['type_element_ajax']][0]['filtres'], function($filtre){
                            return $filtre['operateur'] == 1;
                        });

                        if(sizeof($avec_ou) > 0)
                            $filtres_a_enregister[$champ['type_element_ajax']][] = 
                                array (
                                    'operateur' => 0,
                                    'exclu' => 0,
                                    'blocs' => array (),
                                    'filtres' => $filtres_selection_conditionnelle
                                );
                        else
                            $filtres_a_enregister[$champ['type_element_ajax']][0]['filtres'] = array_merge($filtres_selection_conditionnelle,$filtres_a_enregister[$champ['type_element_ajax']][0]['filtres']);
                    }
                    else
                        $filtres_a_enregister[$champ['type_element_ajax']] = [
                            array (
                                'operateur' => 0,
                                'exclu' => 0,
                                'blocs' => array (),
                                'filtres' => $filtres_selection_conditionnelle
                            ),
                        ];
                }

                if(empty($filtres_a_enregister))
                    continue;

                foreach($filtres_a_enregister as $type_element_ajax => $structure){

                    $recherches_avancees_a_enregistrer[] = [
                        'type_element' => $type_element_ajax,
                        'type' => 'champs_libres.'.$champ['type_element'].'.'.$champ['nom_sql'],
                        'id_cible' => $type_element_ajax,
                        'structure' => $structure
                    ];
                }

                if(!in_array($type_element, $regenerer_migrations_type_element))
                    $regenerer_migrations_type_element[] = $type_element;
            }
        }

        foreach($recherches_avancees_a_enregistrer as $recherche_avancee_a_enregistrer){
            management('recherche_avancee')->enregistre($recherche_avancee_a_enregistrer);
        }

        foreach($regenerer_migrations_type_element as $type_element){
            Table_libre_management::generer_fichier_migration($type_element, true);
        }
        
        return true;
    }
}