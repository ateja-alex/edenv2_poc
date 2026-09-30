<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20230419_remplacement_module_fiche_client implements Script
{

    public function execute(){

        $structure = [];

        if (file_exists(storage_path('app/eden_fiche_client_extranet.php')))
            $structure['extranet'] = include(storage_path('app/eden_fiche_client_extranet.php'));

        if(file_exists(storage_path('app/eden_fiche_client.php')))
            $structure['std'] = include(storage_path('app/eden_fiche_client.php'));

        $fiche_management = fiche('client');

        foreach ($structure as $contexte => &$fiche){

            // pour la fiche classique
            if(isset($fiche['modules'])) {

                foreach($fiche['modules'] as &$info_structure) {

                    if(isset($info_structure['module'])) {
                        if($info_structure['module'] == 'paiements')
                            $info_structure['module'] = 'fiche_client_paiement';
                        else if($info_structure['module'] == 'liste_taches' || $info_structure['module'] == 'module_liste_taches')
                            $info_structure['module'] = 'fiche_client_tache';
                        continue;
                    }

                    foreach($info_structure as &$info_structure_tmp) {
                        foreach($info_structure_tmp['modules'] as &$info_structure_niveau_2) {

                            if(isset($info_structure_niveau_2['module'])){
                                if($info_structure_niveau_2['module'] == 'paiements')
                                    $info_structure_niveau_2['module'] = 'fiche_client_paiement';
                                else if($info_structure_niveau_2['module'] == 'liste_taches' || $info_structure_niveau_2['module'] == 'module_liste_taches')
                                    $info_structure_niveau_2['module'] = 'fiche_client_tache';
                            }
                        }
                    }
                }
            }

            // pour la colonne de droite
            if(isset($fiche['colonne_droite'])) {

                foreach($fiche['colonne_droite'] as &$info_structure) {

                    if(isset($info_structure['module'])) {
                        if($info_structure['module'] == 'paiements')
                            $info_structure['module'] = 'fiche_client_paiement';
                        else if($info_structure['module'] == 'liste_taches' || $info_structure['module'] == 'module_liste_taches')
                            $info_structure['module'] = 'fiche_client_tache';
                    }
                }
            }

            $fiche_management->genere_fichier_fiche($fiche, $contexte == 'extranet');

        }
        return true;
    }

}
