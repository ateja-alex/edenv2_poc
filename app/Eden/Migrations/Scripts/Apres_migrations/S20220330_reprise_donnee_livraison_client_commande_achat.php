<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;
use Illuminate\Support\Facades\Schema;

class S20220330_reprise_donnee_livraison_client_commande_achat implements Script {

    public function execute() {

        if(Schema::hasColumn('commande_achat', 'livraison_client'))
            DB::select('UPDATE commande_achat SET a_livrer_chez_client = livraison_client');

        return true;
    }
}

