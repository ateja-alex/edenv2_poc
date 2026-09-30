<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20220816_monnaie_par_defaut_maquette implements Script {

    public function execute() {

        $maquettes = modele('maquette')->get();

        foreach ($maquettes as $maquette){

            $maquette_modifie = false;

            if(empty($maquette->devise_application_symbole)){
                $maquette->devise_application_symbole = "€";
                $maquette_modifie = true;
            }
            if(empty($maquette->devise_application_nom)){
                $maquette->devise_application_nom = "euros";
                $maquette_modifie = true;
            }
            if(empty($maquette->devise_application_iso)){
                $maquette->devise_application_iso = "EUR";
                $maquette_modifie = true;
            }

            if($maquette_modifie === true)
                management('maquette', $maquette->id)->enregistre($maquette->toArray());

        }
        return true;
    }
}
