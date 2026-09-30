<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Controllers\Parametrage\Champs_libres_controller;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use App\Eden\Models\Champs_liste_formatee;

class S20220708_liste_formatee_migrations implements Script {

    public function execute() {

        $ids_liste_choix = Champs_liste_formatee::select('id_liste_choix')->distinct()->get()->pluck('id_liste_choix')->toArray();

        foreach($ids_liste_choix as $id_liste_choix){

            Champs_libres_controller::generer_fichier_migration_champ_libre_liste_formatee($id_liste_choix,true);
        }
        return true;
    }
}
