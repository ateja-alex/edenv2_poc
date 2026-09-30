<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Managements\Parametrage\Champ_libre_management;

use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;

class S20230125_type_colonne_article_id_stock_initial implements Script
{

    public function execute()
    {

        $champ_article_id = Champ_libre::where('type_element', 'stock_initial')->where('nom_sql', 'article_id')->first();

        $champs_libres_maj_par_type_element['stock_initial'] = [$champ_article_id];

        if(DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA",  env("DB_DATABASE"))->where("TABLE_NAME", "stock_initial")->where("COLUMN_NAME", "article_id")->where("DATA_TYPE", "int")->count() != 1)
            Champ_libre_management::maj_champs_libres($champs_libres_maj_par_type_element, 'INT');

        return true;
    }
}
