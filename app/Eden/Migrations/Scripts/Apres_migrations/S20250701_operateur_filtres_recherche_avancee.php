<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20250701_operateur_filtres_recherche_avancee implements Script{

    public function execute(){

        DB::select('UPDATE recherche_avancee_filtre SET operateur = 0;');
        DB::select('UPDATE recherche_avancee_bloc SET exclu = 1, operateur = 0 WHERE operateur = 2');

        return true;
    }
}