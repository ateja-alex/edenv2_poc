<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Models\Liste_libre;
use App\Eden\Migrations\Scripts\Script;
use DB;

class S20251023_desactiver_recherche_avancee_par_defaut implements Script {

    public function execute(){

        $listes_spe = [];

        $charger_migrations_spe = function(string $chemin){
            $resultats = [];

            if(is_dir(app_path('Migrations/'.$chemin))) {
                $repertoire = scandir(app_path('Migrations/'.$chemin));

                foreach ($repertoire as $fichier) {

                    if ($fichier == '.' || $fichier == '..')
                        continue;

                    $contenu_tmp = require(app_path('Migrations/'.$chemin.'/' . $fichier));

                    if($chemin == 'Rapports'){
                        if(isset($contenu_tmp['liste_libre']) && isset($contenu_tmp['liste_libre']['id'])){
                            $resultats[] = $contenu_tmp['liste_libre']['id'];
                        }
                    } else {
                        if(isset($contenu_tmp['id'])){
                            $resultats[] = $contenu_tmp['id'];
                        }
                    }
                }
            }

            return $resultats;
        };

        $listes_spe = array_merge(
            $listes_spe,
            $charger_migrations_spe('Listes_libres'),
            $charger_migrations_spe('Listes_libres_fiches'),
            $charger_migrations_spe('Rapports')
        );

        $listes_libres_a_modifier = Liste_libre::where('desactiver_filtres', '!=', null)->get();

        foreach ($listes_libres_a_modifier as $liste) {
            $liste->desactiver_recherche_avancee = $liste->desactiver_filtres;
            $liste->save();

            if(in_array($liste->id, $listes_spe)){
                Liste_libre_management::generer_fichier_migration_liste_libre($liste->id, true);
            }
        }

        return true;
    }
}