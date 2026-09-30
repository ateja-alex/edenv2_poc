<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class S20231206_colonne_inactif_lignes_document implements Script {

    public function execute() {

        $types_elements = Variables::$documents_gescom_lignes;

        $colonnes_inactif = DB::select('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = "'.env('DB_DATABASE').'" AND table_name IN (\''.implode('\',\'',$types_elements).'\') AND column_name = "inactif"');

        foreach($colonnes_inactif as $colonne){

            DB::select('ALTER TABLE `'.$colonne->TABLE_NAME.'` DROP COLUMN `inactif`;');
        }

        return true;
    }
}