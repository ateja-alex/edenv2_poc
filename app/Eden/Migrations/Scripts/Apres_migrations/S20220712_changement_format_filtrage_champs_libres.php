<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20220712_changement_format_filtrage_champs_libres implements Script {

    public function execute() {

        DB::select('ALTER TABLE eden_champslibres MODIFY COLUMN filtrage LONGTEXT NULL');

        return true;
    }
}

