<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Models\Champ_libre;
use DB;

class S20251121_champ_expediteur_questionnaire_ajax implements Script {

    public function execute(){

        $chemin_migration_questionnaire = app_path() . '/Migrations/questionnaire.php';

        if(file_exists($chemin_migration_questionnaire)){

            $champ_expediteur = Champ_libre::where('type_element','questionnaire')->where('nom_sql','expediteur')->get();

            $nouvelles_informations = array(
                'type' => 42,
                'type_element_ajax' => 'compte_email',
                'liste_choix' => null,
                'filtrage' => "[{\"champ\":\"mail_public\",\"condition_ou\":false,\"condition\":\"Where\",\"symbole\":\"=\",\"valeur\":\"1\"}]",
            );

            Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champ_expediteur);

        }

        return true;
       
    }
}