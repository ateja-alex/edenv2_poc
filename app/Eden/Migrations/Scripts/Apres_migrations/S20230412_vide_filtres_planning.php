<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_parametre;

class S20230412_vide_filtres_planning implements Script{

    public function execute(){

        $filtres = Rapport_parametre::where('id_rapport', 'planning')->get();

        foreach ($filtres as $filtre){
           $filtre->delete();
        }

        return true;

    }

}