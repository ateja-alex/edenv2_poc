<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20230816_modele_de_document implements Script {

    public function execute() {

        // On passe les modèles de document existants à type_de_document = 0
        $modeles = modele('modele_de_document')->get();
        foreach($modeles as $modele) {
            management('modele_de_document', $modele->id, $modele)->enregistre(['type_de_document' => 0]);
        }

        return true;
    }
}
