<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20250220_vider_filtres_tableaux_de_bord implements Script{

    public function execute(){

        DB::select('DELETE FROM eden_rapports_parametres WHERE id_rapport LIKE "tableau_de_bord_%"');

        return true;
    }
}