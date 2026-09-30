<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20260402_rattrapage_liaisons_champs_liste implements Script{

    public function execute(){

        $champs_liste_libre_liaisons = Champ_libre::whereNotNull('champ_liste_libre_liaisons')
            ->whereNot('champ_liste_libre_liaisons', "")
            ->get();

        foreach($champs_liste_libre_liaisons as $champ){

            $liaisons = json_decode($champ->champ_liste_libre_liaisons, true);
            $chemin_migration = app_path('Migrations/' . $champ->type_element . '.php');

            foreach($liaisons as $index_valeur => $indexs_valeurs_champ_lie){

                if($index_valeur == 'undefined')
                    unset($liaisons[$index_valeur]);
            }
            
            $champ->champ_liste_libre_liaisons = json_encode($liaisons);
            $champ->save();

            if(file_exists($chemin_migration))
                Table_libre_management::generer_fichier_migration($champ->type_element, true);
        }

        return true;
    }
}