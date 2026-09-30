<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20240614_suppression_colonne_recherche_avancee implements Script {

    public function execute(){

        try {
            DB::select('ALTER TABLE `eden_listeslibres` DROP `recherche_avancee`');
        }
        catch (\Exception $e){
            return true;
        }

        return true;
    }
}