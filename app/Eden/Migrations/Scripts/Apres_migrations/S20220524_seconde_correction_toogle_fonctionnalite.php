<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;
use App\Eden\Migrations\Scripts\Apres_migrations\S20220512_correction_toogle_fonctionnalite;

class S20220524_seconde_correction_toogle_fonctionnalite implements Script {

    public function execute() {

        $classe_instanciee = new S20220512_correction_toogle_fonctionnalite();

        return $classe_instanciee->execute();
    }
}
