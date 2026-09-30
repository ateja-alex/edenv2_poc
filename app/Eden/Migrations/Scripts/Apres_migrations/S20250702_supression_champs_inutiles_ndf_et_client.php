<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Models\Champ_libre;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Parametrage\Table_libre_management;
use Illuminate\Support\Facades\Schema;

class S20250702_supression_champs_inutiles_ndf_et_client implements Script {

    public function execute() {

        if(strpos(env('APP_URL'), 'capvisio') !== false)
            return true;

        $champs_a_verifier = [
            'note_de_frais' => [
                'pre_valide_dp',
                'date_pre_validation'
            ],
            'client' => [
                'conditions_de_paiement'
            ]
        ];
        
        foreach($champs_a_verifier as $type_element => $champs){

            $chemin_migration = app_path() . '/Migrations/' . $type_element . '.php';
            $champs_a_supprimer = array();

            foreach($champs as $champ){

                if(!Schema::hasColumn($type_element, $champ))
                    continue;

                $verification = modele($type_element)->whereNotNull($champ)->count();

                if($verification === 0)
                    $champs_a_supprimer[] = $champ;
            }

            if(empty($champs_a_supprimer))
                continue;

            Champ_libre::where('type_element', $type_element)->whereIn('nom_sql', $champs_a_supprimer)->delete();
            Schema::dropColumns($type_element, $champs_a_supprimer);

            // Si le fichier existe, on regénére le fichier de migrations
            if (file_exists($chemin_migration))
                Table_libre_management::generer_fichier_migration($type_element,true);
        }
        
        return true;
    }
}