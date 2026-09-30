<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Liste_libre;

class S20241223_changement_desactivation_options_individuelle implements Script{

    public function execute(){

        $listes_a_gerer = Liste_libre::where('desactiver_options_individuelle','!=','')
            ->whereNotNull('desactiver_options_individuelle')
            ->get();

        foreach($listes_a_gerer as $liste){

            $desactiver_options_individuelle = $liste->desactiver_options_individuelle;

            while(!is_array($desactiver_options_individuelle)){
                $desactiver_options_individuelle = json_decode($desactiver_options_individuelle,true);
            }

            if(!isset($desactiver_options_individuelle['supprimer']))
                continue;

            $desactiver_options_individuelle = array_keys(array_filter($desactiver_options_individuelle,function($option){
                return $option === 'true' ||$option === true;
            }));

            $liste->desactiver_options_individuelle = json_encode($desactiver_options_individuelle);
            $liste->save();

            Liste_libre_management::generer_fichier_migration_liste_libre($liste->id,true);
        }

        return true;
    }

}