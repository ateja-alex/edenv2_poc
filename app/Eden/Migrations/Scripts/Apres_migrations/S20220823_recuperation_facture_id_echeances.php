<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;
use Illuminate\Support\Facades\Schema;

class S20220823_recuperation_facture_id_echeances implements Script {

    public function execute() {

        if(Schema::hasColumn('echeance', 'facture_id'))
            DB::select('UPDATE echeance SET type_element = "facture_vente", element_id = facture_id, pourcentage = 0, montant_pourcentage = 0, facture_id = NULL WHERE facture_id IS NOT NULL');

        return true;
    }
}

