<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20240304_changement_format_valeur_defaut implements Script {

    public function execute() {

        DB::select('ALTER TABLE eden_champslibres MODIFY COLUMN valeur_defaut TEXT NULL');

        return true;
    }
}

