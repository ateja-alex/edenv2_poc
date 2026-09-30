<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;

class S20260202_obligatoire_adresse_email_compte_email implements Script {

    public function execute(){

        $chemin_migration = app_path() . '/Migrations/compte_email.php';
        $chemin_migration_formulaire = app_path() . '/Migrations/Formulaires_libres/compte_email.php';

        if(file_exists($chemin_migration)){

            $champ_adresse_email = Champ_libre::where('type_element','compte_email')->where('nom_sql','adresse_email')->first();

            if($champ_adresse_email->obligatoire)
                Script_management::liste_formatee_a_autre_type_champ(['obligatoire' => null], [$champ_adresse_email]);   
        }

        if(file_exists($chemin_migration_formulaire)){

            $champ_formulaire = Formulaires_champs::where('type_element', 'compte_email')->where('nom_sql', 'adresse_email')->first();

            if($champ_formulaire->condition_obligatoire !== 'compte_email.type_de_compte == 0'){
                
                $champ_formulaire->condition_obligatoire = 'compte_email.type_de_compte == 0';
                $champ_formulaire->save();

                Maintenance_management::generer_fichier_migration_formulaire('compte_email');
            }
        }

        return true;
    }
}