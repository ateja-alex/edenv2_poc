<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20250825_lien_vers_autre_element_liste implements Script {

    public function execute() {

        DB::select('UPDATE listes_libres_colonnes SET lien_vers_autre_element = NULL WHERE lien_vers_autre_element = "null" OR lien_vers_autre_element = ""');

        $cmd = "find ".app_path()."/Migrations -type f -name \"*.php\" -exec sed -i 's/\"lien_vers_autre_element\"[[:space:]]*=>[[:space:]]*\"\\(null\\|\\)\"/\"lien_vers_autre_element\" => null/g' {} +";

        exec($cmd);

        return true;
    }

}