<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20230712_retrocomptabilite_fiche_article implements Script {

    public function execute() {

        if(!file_exists(storage_path('app/eden_fiche_article.php')))
            return true;

        $structure = include(storage_path('app/eden_fiche_article.php'));

        // pour la fiche classique
        if(isset($structure['modules'])) {

            foreach($structure['modules'] as &$info_structure) {

                if(isset($info_structure['module'])) {
                    if($info_structure['module'] == 'liste_fournisseurs')
                        $info_structure['module'] = 'fiche_article_article_fournisseur';
                    continue;
                }

                foreach($info_structure as &$info_structure_tmp) {
                    foreach($info_structure_tmp['modules'] as &$info_structure_niveau_2) {

                        if(isset($info_structure_niveau_2['module'])){
                            if($info_structure_niveau_2['module'] == 'liste_fournisseurs')
                                $info_structure_niveau_2['module'] = 'fiche_article_article_fournisseur';
                        }
                    }
                }
            }
        }

        fiche('article')->genere_fichier_fiche($structure, false);

        return true;
    }

}