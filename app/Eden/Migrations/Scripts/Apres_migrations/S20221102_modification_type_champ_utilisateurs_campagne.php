<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20221102_modification_type_champ_utilisateurs_campagne implements Script {

    public function execute() {

        $champ_libre = Champ_libre::where('nom_sql', 'utilisateurs')->where('type_element', 'campagne_de_prospection')->first();

        if($champ_libre == null)
            return true;

        if($champ_libre->type !== 11){

            $champ_libre->type = 11;
            $champ_libre->liste_choix = 1;
            $champ_libre->table_pivot = "campagne_de_prospection_utilisateurs";

            $champ_libre->save();
        }

        return true;
    }
}

