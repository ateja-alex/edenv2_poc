<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20240305_recuperer_valeur_timeline_creation_tache implements Script {

    public function execute() {

        if(fonctionnalite('alimentation_timeline_creation_tache'))
            Script_management::modifier_fonctionnalites(array('alimentation_timeline_creation_tache' => 'les_2_taches'));
        else
            Script_management::modifier_fonctionnalites(array('alimentation_timeline_creation_tache' => 'desactive'));

        return true;
    }
}