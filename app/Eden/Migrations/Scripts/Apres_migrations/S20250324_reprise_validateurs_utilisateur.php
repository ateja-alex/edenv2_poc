<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20250324_reprise_validateurs_utilisateur implements Script{

    public function execute(){

        if(Schema::hasTable('utilisateur') && Schema::hasColumns('utilisateur', [
            'validation_ndf_n_plus_1', 'validation_conges_n_plus_1',
            'validation_ndf_n_plus_2', 'validation_conges_n_plus_2'
        ]))
            DB::update('UPDATE utilisateur SET validation_ndf_n_plus_1 = validation_conges_n_plus_1, validation_ndf_n_plus_2 = validation_conges_n_plus_2');

        return true;
    }
}