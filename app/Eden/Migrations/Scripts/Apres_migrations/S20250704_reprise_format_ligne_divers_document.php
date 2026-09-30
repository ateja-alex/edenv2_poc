<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class S20250704_reprise_format_ligne_divers_document implements Script {
    
    public function execute(){

        if(Schema::hasTable("ligne_divers_document") && Schema::hasColumns('ligne_divers_document', ['format', 'type']))
            DB::update("UPDATE ligne_divers_document SET format = (110 * format + 110) WHERE type = 'image'");

        return true;
    }
}