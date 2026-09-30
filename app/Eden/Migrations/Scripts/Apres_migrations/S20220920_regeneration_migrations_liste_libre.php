<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20220920_regeneration_migrations_liste_libre implements Script {

    public function execute() {

        $controller = new S20220729_regenerer_migrations_listes_libres_specifique();

        return $controller->execute();
    }
}

