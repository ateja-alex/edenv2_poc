<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20251008_suppression_bloc_achat_si_bloc_commerce implements Script
{
    public function execute(){
         $structure = [];

        $fichiers = glob(storage_path('app/eden_fiche*'));
        $noms_fichiers = array_map(
            function($fichier) {
                return pathinfo(basename($fichier), PATHINFO_FILENAME);
            },
            $fichiers
        );

        if(file_exists(storage_path('app/eden_fonctionnalites.php'))){
            $fonctionnalites = include(storage_path('app/eden_fonctionnalites.php'));
        }

        foreach ($noms_fichiers as $nom_fichier) {

            if (file_exists(storage_path('app/'.$nom_fichier.'_extranet.php')))
                $structure['extranet'] = include(storage_path('app/'.$nom_fichier.'_extranet.php'));

            $structure['std'] = include(storage_path('app/'.$nom_fichier.'.php'));

            $position = strpos($nom_fichier, 'eden_fiche_');
            if ($position !== false) {
                $type_element = substr($nom_fichier, $position + strlen('eden_fiche_'));
            }

            $fiche_management = fiche($type_element);

            foreach ($structure as $contexte => &$fiche){
                $module_commerce_present = false;

                // pour la fiche classique
                if(isset($fiche['modules'])) {

                    foreach($fiche['modules'] as $cle => &$info_structure) {

                        if(isset($info_structure['module'])) {
                            if($info_structure['module'] == 'commerce')
                                $module_commerce_present = true;
                            if(in_array($info_structure['module'], ['achats', 'achat'])){
                                $info_structure['module'] = 'commerce';
                                $module_commerce_present = true;
                            }
                            elseif($info_structure['module'] === 'commerce_achats'){
                                unset($fiche['modules'][$cle]);
                                continue;
                            }elseif(in_array($info_structure['module'], ['commerce_vente_lignes', 'commerce_achat_lignes'])){
                                $info_structure['module'] = 'commerce_lignes';
                                $module_commerce_present = true;
                            }
                                
                        }

                        foreach($info_structure as &$info_structure_tmp) {
                            if(isset($info_structure_tmp['modules']) && is_array($info_structure_tmp['modules'])){
                                foreach($info_structure_tmp['modules'] as $cle_niveau_2 => &$info_structure_niveau_2) {

                                    if(isset($info_structure_niveau_2['module'])){
                                        if($info_structure_niveau_2['module'] == 'commerce')
                                            $module_commerce_present = true;
                                        if(in_array($info_structure_niveau_2['module'], ['achats', 'achat'])){
                                            $info_structure_niveau_2['module'] = 'commerce';
                                            $module_commerce_present = true;
                                        }  
                                        elseif($info_structure_niveau_2['module'] === 'commerce_achats')
                                            unset($info_structure_tmp['modules'][$cle_niveau_2]);
                                        elseif(in_array($info_structure_niveau_2['module'], ['commerce_vente_lignes', 'commerce_achat_lignes'])){
                                            $info_structure_niveau_2['module'] = 'commerce_lignes';
                                            $module_commerce_present = true;
                                        }
                                            
                                    }
                                }
                            }    
                        }
                    }
                }

                // pour la colonne de droite
                if(isset($fiche['colonne_droite'])) {

                    foreach($fiche['colonne_droite'] as $cle => &$info_structure) {

                        if(isset($info_structure['module'])) {
                            if($info_structure['module'] == 'commerce')
                                $module_commerce_present = true;
                            if(in_array($info_structure['module'], ['achats', 'achat'])){
                                $info_structure['module'] = 'commerce';
                                $module_commerce_present = true;
                            }
                            elseif($info_structure['module'] === 'commerce_achats') 
                                unset($fiche['colonne_droite'][$cle]);
                            elseif(in_array($info_structure['module'], ['commerce_vente_lignes', 'commerce_achat_lignes'])){
                                $info_structure['module'] = 'commerce_lignes';
                                $module_commerce_present = true;
                            }   
                        }
                    }
                }

                if($module_commerce_present)
                    $fiche['options']['commerce_vente_defaut'] = $fonctionnalites['gescom_onglet_commerce_fiche_type_element_defaut'];

                $fiche_management->genere_fichier_fiche($fiche, $contexte == 'extranet');
            }
        }
        return true;
    }
}