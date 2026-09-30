<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20260114_fix_types_elements_carte implements Script {

    public function execute() {

        $rapports_carte = Rapport_libre::where('type_rapport', 'carte')->get();

        foreach($rapports_carte as $rapport){
            $parametrage = json_decode($rapport->parametrage_rapport_libre, true);
            $parametrage['types_elements_carte'] = array_values(
                array_filter(
                    $parametrage['types_elements_carte'],
                    fn($v) => !empty($v)
                )
            );

            $rapport->parametrage_rapport_libre = json_encode($parametrage);
            $rapport->save();
        }

        return true;
    }
}