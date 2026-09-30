<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Table_libre;
use DB;

class S20250228_compte_comptable_parametre implements Script{

    public function execute(){

        $table_categorie_comptable = Table_libre::where('type_element', 'compte_comptable')->first();

        if(empty($table_categorie_comptable) || empty($table_categorie_comptable->parametre))
            return true;

        $table_categorie_comptable->parametre = 0;
        $table_categorie_comptable->save();

        if(!file_exists(app_path()."/Migrations/compte_comptable.php"))
            return true;

        Table_libre_management::generer_fichier_migration("compte_comptable", true);

        return true;
    }
}