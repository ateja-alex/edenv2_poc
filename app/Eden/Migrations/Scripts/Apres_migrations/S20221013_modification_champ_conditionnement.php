<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use Illuminate\Support\Facades\DB;

class S20221013_modification_champ_conditionnement implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'conditionnement_id')->where('type_element', 'stock_initial')->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'conditionnement',
        );

        $changement_bdd = DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA", env("DB_DATABASE"))->where("TABLE_NAME", "stock_initial")->where("COLUMN_NAME", "conditionnement_id")->where("COLUMN_TYPE", "int(11) unsigned")->count() != 1
        ? true : false;

		Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, $changement_bdd);

        return true;
    }
}

