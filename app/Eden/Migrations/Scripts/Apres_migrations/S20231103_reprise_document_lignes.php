<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class S20231103_reprise_document_lignes implements Script {

    public function execute() {

        $types_elements = Variables::$documents_gescom;

        foreach($types_elements as $type_element){

            $type_element_lignes = $type_element.'_lignes';

            DB::select('DELETE '.$type_element_lignes.' FROM '.$type_element_lignes.' LEFT JOIN '.$type_element.' ON '.$type_element_lignes.'.document_id = '.$type_element.'.id WHERE '.$type_element.'.id IS NULL;');
        }

        return true;
    }

}