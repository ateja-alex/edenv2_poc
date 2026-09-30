<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20220801_ajout_valeur_defaut_statut_contact implements Script {

    public function execute() {

        DB::select('UPDATE contact SET statut = 0 WHERE statut IS NULL');

        return true;
    }
}
