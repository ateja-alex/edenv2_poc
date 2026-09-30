<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use DB;

class S20240220_maj_licences_utilisees implements Script {
    public function execute() {

        $chemin_avec_nom_document = app_path() . '/Migrations/licence.php';

        // Si le fichier existe, on regénére le fichier de migrations
        if (file_exists($chemin_avec_nom_document) == true){

            $champ_libre = Champ_libre::where('type_element','licence')->where('nom_sql','nombre_utilises')->first();

            $champ_libre['donnee_calculee_requete'] .= ' and type_utilisateur != 2 and coalesce(inactif,0) = 0)';

            $champ_libre->save();

            Table_libre_management::generer_fichier_migration('licence',true);
        }

        DB::select('UPDATE utilisateur SET licence_id = NULL WHERE type_utilisateur = 3 OR type_utilisateur = 2 OR inactif = 1 OR coalesce(autorise_a_se_connecter,0) = 0');

        DB::select('UPDATE licence SET nombre_utilises = (SELECT count(*) from utilisateur where utilisateur.licence_id = licence.id and type_utilisateur != 2 and coalesce(inactif,0) = 0)');

        return true;
    }
}