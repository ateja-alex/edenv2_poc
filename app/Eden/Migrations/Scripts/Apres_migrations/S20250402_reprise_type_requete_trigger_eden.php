<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Controllers\Parametrage\Champs_libres_controller;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;
use Illuminate\Support\Facades\Log;

class S20250402_reprise_type_requete_trigger_eden implements Script {

    public function execute() {

        $trigger_eden = modele('trigger_eden')->get();

        foreach($trigger_eden as $trigger){

            $trigger->type_requete = strpos($trigger->requete,'insert into') !== false ? '1' : '0';
            $trigger->save();
        }

        return true;
    }
}