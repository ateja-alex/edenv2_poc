<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Migrations\Scripts\Script;

class S20220712_ajout_langue_traduction implements Script {

    public function execute() {

        Maintenance_management::maj_tables_libres('traduction_langue');
        Maintenance_management::maj_champs_libres('traduction_langue');

        $langues = array(
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'it' => 'Italiano',
        );

        foreach($langues as $code => $nom){

            management('traduction_langue')->enregistre(
                array(
                    'code' => $code,
                    'nom' =>  $nom,
                )
            );
        }

        return true;
    }
}