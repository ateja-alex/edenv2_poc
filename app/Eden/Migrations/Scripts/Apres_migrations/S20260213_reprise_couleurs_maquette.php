<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Cache_management;
use App\Eden\Migrations\Scripts\Script;

class S20260213_reprise_couleurs_maquette implements Script {

    public function execute() {

        $maquettes = modele('maquette')->get();
        $champs_a_reprendre = [
            'couleur_police_nom_application' => 1,
            'background_navbar' => 2,
            'background_menus' => 3,
            'background_menus_extranet' => 4,
            'background_menus_hover' => 5,
            'background_sous_menus' => 6,
            'couleur_texte_menus' => 7,
            'couleur_liens' => 8,
            'background_tache' => 9,
            'couleur_police_tache' => 10
        ];

        foreach($maquettes as $maquette){
            
            foreach($champs_a_reprendre as $nom_champ => $valeur){

                if(!empty($maquette->$nom_champ)){

                    management('maquette_couleurs')->enregistre([
                        'maquette' => $maquette->id,
                        'nom_couleur' => $valeur,
                        'valeur' => $maquette->$nom_champ
                    ]);
                }
            }
        }

        return true;
    }
}