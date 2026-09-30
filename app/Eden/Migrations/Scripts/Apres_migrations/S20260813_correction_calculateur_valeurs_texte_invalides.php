<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class S20260813_correction_calculateur_valeurs_texte_invalides implements Script {

    public function execute(){

        $types_elements = Variables::$documents_gescom_lignes;

        foreach($types_elements as $type_element){

            DB::select("UPDATE ".$type_element." SET calculateur = NULL WHERE calculateur IN ('null', 'undefined')");
        }

        DB::select("UPDATE ligne_divers_document SET calculateur = NULL WHERE calculateur IN ('null', 'undefined')");

        return true;
    }
}
