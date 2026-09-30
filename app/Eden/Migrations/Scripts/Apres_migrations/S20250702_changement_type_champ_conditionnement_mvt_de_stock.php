<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use Illuminate\Support\Facades\DB;

class S20250702_changement_type_champ_conditionnement_mvt_de_stock implements Script {
    
    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'conditionnement_id')->where('type_element', 'mouvement_de_stock')->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'conditionnement',
        );

        $changement_bdd = DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA", env("DB_DATABASE"))->where("TABLE_NAME", 'mouvement_de_stock')->where("COLUMN_NAME", "conditionnement_id")->where("COLUMN_TYPE", "int(11) unsigned")->count() != 1
        ? true : false;

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, $changement_bdd);

        return true;
    }
}