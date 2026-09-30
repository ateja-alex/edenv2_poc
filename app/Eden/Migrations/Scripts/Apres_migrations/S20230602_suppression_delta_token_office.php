<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;

class S20230602_suppression_delta_token_office implements Script {

    public function execute() {

        DB::table('eden_parametres')->where('nom', 'LIKE', '%delta_token_office%')->delete();
        DB::table('eden_parametres')->where('nom', 'LIKE', '%date_initialisation_delta_office%')->delete();

        return true;
    }
}

