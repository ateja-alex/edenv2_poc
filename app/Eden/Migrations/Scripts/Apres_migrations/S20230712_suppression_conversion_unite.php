<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20230712_suppression_conversion_unite implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'conditionnement')->where('type_element', 'composition_article')->get();

        $nouvelles_informations = array(
            'type_element_ajax' => 'conditionnement',
        );

		Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        if(!file_exists(storage_path('app/eden_fiche_article.php')))
            return true;

        $structure = include(storage_path('app/eden_fiche_article.php'));

        // pour la fiche classique
        if(isset($structure['modules'])) {

            foreach($structure['modules'] as $index_module => $info_structure) {

                if(isset($info_structure['module'])) {
                    if($info_structure['module'] == 'liste_conditionnements')
                        unset($structure['modules'][$index_module]);

                    continue;
                }

                foreach($info_structure as &$info_structure_tmp) {
                    foreach($info_structure_tmp['modules'] as $sous_index_module => &$info_structure_niveau_2) {

                        if(isset($info_structure_niveau_2['module'])){
                            if($info_structure_niveau_2['module'] == 'liste_fournisseurs')
                                unset($info_structure_tmp['modules'][$sous_index_module]);
                        }
                    }
                }
            }
        }

        fiche('article')->genere_fichier_fiche($structure, false);

        return true;
    }
}

