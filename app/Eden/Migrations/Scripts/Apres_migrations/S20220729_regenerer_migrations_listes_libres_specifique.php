<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Liste_libre;
use Illuminate\Support\Facades\DB;

class S20220729_regenerer_migrations_listes_libres_specifique implements Script {

    public function execute() {

        $listes_libres = Liste_libre::select(
            'id',
            DB::raw('IF(id_rapport IS NULL OR id_rapport = "",CONCAT("liste_",type_element),CONCAT("rapport_",id_rapport)) as identifiant')
        )->get()->pluck('id', 'identifiant')->toArray();

        if(is_dir(app_path('Migrations/Listes_libres'))) {

            $repertoire = scandir(app_path('Migrations/Listes_libres'));

            foreach ($repertoire as $fichier) {

                if ($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Listes_libres/' . $fichier));

                $type_element = str_replace('.php', '', $fichier);

                Liste_libre_management::generer_fichier_migration_liste_libre($listes_libres['liste_' . $type_element], true);

            }
        }

        if(is_dir(app_path('Migrations/Listes_libres_fiches'))) {

            $repertoire = scandir(app_path('Migrations/Listes_libres_fiches'));

            foreach ($repertoire as $fichier) {

                if ($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Listes_libres_fiches/' . $fichier));

                $id_rapport = str_replace('.php', '', $fichier);

                Liste_libre_management::generer_fichier_migration_liste_libre($listes_libres['rapport_' . $id_rapport], true);

            }
        }

        if(is_dir(app_path('Migrations/Rapports'))) {

            $repertoire = scandir(app_path('Migrations/Rapports'));

            foreach ($repertoire as $fichier) {

                if ($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = require(app_path('Migrations/Rapports/' . $fichier));

                if (!isset($contenu_tmp['liste_libre']))
                    continue;

                $id_rapport = str_replace('.php', '', $fichier);

                Liste_libre_management::generer_fichier_migration_liste_libre($listes_libres['rapport_' . $id_rapport], true);

            }
        }

        return true;
    }
}