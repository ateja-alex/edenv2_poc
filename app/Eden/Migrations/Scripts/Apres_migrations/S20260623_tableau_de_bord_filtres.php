<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20260623_tableau_de_bord_filtres implements Script{

    public function execute(){

        $tableaux_de_bord = modele('tableau_de_bord')->get();

        foreach($tableaux_de_bord as $tableau_de_bord){

            $filtres = [];

            if($tableau_de_bord->filtre_dates_mensuelles == 1){

                $index_traduction = service('traduction')->calcul_index_traduction(
                    11,
                    array(
                        'tableau_de_bord',
                        $tableau_de_bord->id,
                        'filtres',
                        1
                    ),
                    array(
                        'nom' => traduction('interface.tableau_de_bord.filtres.date'),
                    )
                );

                $filtres[] = array(
                    'id' => 1,
                    'index_traduction' => $index_traduction,
                    'type' => 'filtre-date',
                );
            }

            if($tableau_de_bord->filtre_choix_utilisateurs == 1){

                $index_traduction = service('traduction')->calcul_index_traduction(
                    11,
                    array(
                        'tableau_de_bord',
                        $tableau_de_bord->id,
                        'filtres',
                        2
                    ),
                    array(
                        'nom' => traduction('interface.tableau_de_bord.filtres.utilisateur'),
                    )
                );

                $filtres[] = array(
                    'id' => 2,
                    'index_traduction' => $index_traduction,
                    'type' => 'filtre-recherche-element',
                    'type_element_ajax' => 'utilisateur',
                );
            }

            if($tableau_de_bord->filtre_choix_entites == 1){

                $index_traduction = service('traduction')->calcul_index_traduction(
                    11,
                    array(
                        'tableau_de_bord',
                        $tableau_de_bord->id,
                        'filtres',
                        3
                    ),
                    array(
                        'nom' => traduction('interface.tableau_de_bord.filtres.entite'),
                    )
                );

                $filtres[] = array(
                    'id' => 3,
                    'index_traduction' => $index_traduction,
                    'type' => 'filtre-recherche-element',
                    'type_element_ajax' => 'entite',
                );
            }
            

            $tableau_de_bord->filtres = json_encode($filtres);
            $tableau_de_bord->save();
        }

        return true;
    }
}