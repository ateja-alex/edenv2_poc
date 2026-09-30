<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;
use Illuminate\Support\Facades\Schema;

class S20251119_changement_type_colonne_id_document_paiement implements Script {

    public function execute() {

        $informations_colonne = DB::selectOne("SELECT DATA_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '" . env('DB_DATABASE') . "'
            AND TABLE_NAME = 'paiement'
            AND COLUMN_NAME = 'id_document'");

        if(!empty($informations_colonne) && $informations_colonne->DATA_TYPE === 'varchar')
            DB::select('ALTER TABLE paiement MODIFY COLUMN id_document INT(11) NULL');

        return true;
    }
}

