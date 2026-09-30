<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Controllers\Parametrage\Champs_libres_controller;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;
use App\Eden\Models\Table_libre;

class S20240909_conditionnement_a_0 implements Script{

    public function execute(){

        $types_elements = Variables::$documents_gescom_lignes;

        foreach($types_elements as $type_element){

            DB::select('UPDATE '.$type_element.' SET conditionnement = NULL WHERE conditionnement = 0');
        }

        return true;
    }
}