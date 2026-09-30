<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Champ_libre_management;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20220706_champ_recherche_element_suppresion_0 implements Script
{

    public function execute(){

        $champs_libres = Champ_libre::where('type',42)->whereNull('type_element_origine')->get();

        foreach($champs_libres as $champ_libre){

            if(Schema::hasTable($champ_libre->type_element))
                DB::select('UPDATE '.$champ_libre->type_element.' SET '.$champ_libre->nom_sql .' = NULL WHERE '.$champ_libre->nom_sql.' = "0";');
        }
        return true;
    }
}
