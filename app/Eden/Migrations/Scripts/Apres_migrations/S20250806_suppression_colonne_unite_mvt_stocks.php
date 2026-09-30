<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Models\Champ_libre;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Parametrage\Table_libre_management;
use Illuminate\Support\Facades\Schema;

class S20250806_suppression_colonne_unite_mvt_stocks implements Script {

    public function execute() {

        $champs_a_verifier = [
            'transformation_stocks' =>'unite_depart',
            'mouvement_de_stock' => 'unite_id'
        ];
        
        foreach($champs_a_verifier as $type_element => $champ){

            $chemin_migration = app_path() . '/Migrations/' . $type_element . '.php';

            if(!Schema::hasColumn($type_element, $champ))
                continue;

            $verification = modele($type_element)->whereNotNull($champ)->count();

            if($verification > 0)
                continue;

            Champ_libre::where('type_element', $type_element)->where('nom_sql', $champ)->delete();
            Schema::dropColumns($type_element, [$champ]);

            // Si le fichier existe, on regénére le fichier de migrations
            if (file_exists($chemin_migration))
                Table_libre_management::generer_fichier_migration($type_element,true);
        }
        
        return true;
    }
}