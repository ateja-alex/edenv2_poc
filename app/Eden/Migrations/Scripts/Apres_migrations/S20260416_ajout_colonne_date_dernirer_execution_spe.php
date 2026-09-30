<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;

class S20260416_ajout_colonne_date_dernirer_execution_spe implements Script{
    public function execute(){
        if (file_exists(app_path() . '/Migrations/Listes_libres/trigger_eden.php')){
            $colonne = new Colonne;
            $colonne->liste_libre_id = Liste_libre::where('type_element', 'trigger_eden')->first()->id;
            $colonne->nom = traduction(Champ_libre::where('type_element', 'trigger_eden')->where('nom_sql', 'date_derniere_execution')->first()->index_traduction . '.nom');
            $colonne->valeur = '#date_derniere_execution#';
            $colonne->type = 'standard';
            $colonne->ordre = 8;
            $colonne->save();

            Liste_libre_management::generer_fichier_migration_liste_libre($colonne->liste_libre_id);
        }

        return true;
    }
}