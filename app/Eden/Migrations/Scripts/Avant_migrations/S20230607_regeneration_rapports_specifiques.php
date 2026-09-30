<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;

use App\Eden\Controllers\Fiches\Vue_sql_controller;
use App\Eden\Models\Liste_libre;
use Illuminate\Support\Facades\DB;

class S20230607_regeneration_rapports_specifiques implements Script {

    public function execute() {

        $listes_libres = Liste_libre::select(
            'id',
            DB::raw('IF(id_rapport IS NULL OR id_rapport = "",CONCAT("liste_",type_element),CONCAT("rapport_",id_rapport)) as identifiant')
        )->get()->pluck('id', 'identifiant')->toArray();

        if(is_dir(app_path('Migrations/Rapports'))) {

            $repertoire = scandir(app_path('Migrations/Rapports'));

            foreach ($repertoire as $fichier) {

                if ($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Rapports/' . $fichier));

                $id_rapport = str_replace('.php', '', $fichier);

                if (!isset($listes_libres['rapport_' . $id_rapport]))
                    continue;

                Liste_libre_management::generer_fichier_migration_liste_libre($listes_libres['rapport_' . $id_rapport], true);

            }
        }

        return true;
    }
}
