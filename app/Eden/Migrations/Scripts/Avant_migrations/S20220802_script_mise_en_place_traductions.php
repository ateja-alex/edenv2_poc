<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use Illuminate\Support\Facades\DB;

class S20220802_script_mise_en_place_traductions implements Script {

    public function execute() {

        Maintenance_management::maj_tables_libres('traduction_index');
        Maintenance_management::maj_champs_libres('traduction_index');

        Maintenance_management::maj_tables_libres('traduction_valeur');
        Maintenance_management::maj_champs_libres('traduction_valeur');

        DB::select('UPDATE listes_libres_colonnes SET index_traduction = NULL');
        DB::select('UPDATE eden_champslibres SET index_traduction = NULL');
        DB::select('UPDATE eden_listes_libres_calculs SET index_traduction = NULL');

        DB::select("UPDATE eden_champslibres SET contenu = '' WHERE contenu = '[\"Non\",\"Oui\"]' OR contenu = '[\"Sans valeur\",\"Non\",\"Oui\"]'");

        return true;
    }
}
