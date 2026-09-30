<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20230922_reprise_fonctionnalite_garder_nom_originel_fichier implements Script {

    public function execute() {

        if(config('eden.pieces_jointes_garder_nom_originel'))
            Script_management::modifier_fonctionnalites(array('pieces_jointes_garder_nom_originel' => true));

        return true;
    }
}
