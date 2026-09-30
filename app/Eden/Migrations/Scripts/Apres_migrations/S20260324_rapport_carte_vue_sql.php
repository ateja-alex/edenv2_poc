<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20260324_rapport_carte_vue_sql implements Script {

    public function execute(){

        $rapports_carte = Rapport_libre::where('type_rapport', 'carte')->get();

        foreach($rapports_carte as $rapport){

            $rapport->type_vue_carte = 'adresse';

            $parametrage = json_decode($rapport->parametrage_rapport_libre, true);
            $parametrage['mappage_latitude'] = 'latitude';
            $parametrage['mappage_longitude'] = 'longitude';

            $rapport->parametrage_rapport_libre = json_encode($parametrage);
            $rapport->save();
        }

        return true;
    }
}