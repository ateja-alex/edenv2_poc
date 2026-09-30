<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20220927_changement_nom_fonctionnalite_recurrence implements Script {

    public function execute() {

        $fonctionnalites = array();

        if(file_exists(storage_path('app/eden_fonctionnalites.php')))
            $fonctionnalites = include(storage_path('app/eden_fonctionnalites.php'));

        if(isset($fonctionnalites['gestion_abonnement']) && $fonctionnalites['gestion_abonnement'] === true) {

            $fonctionnalites['gestion_recurrences_factures'] = true;
            unset($fonctionnalites['gestion_abonnement']);
        }

        Script_management::generer_fichier_fonctionnalite($fonctionnalites);

        return true;
    }
}

