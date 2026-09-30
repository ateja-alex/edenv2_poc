<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Controllers\Parametrage\Champs_libres_controller;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;

class S20221010_regeneration_valeurs_listes_libres_migrations implements Script
{

    public function execute()
    {

        $controller = new Champs_libres_controller();

        $tables = [];
        
        if(is_dir(app_path('Migrations/Champs_libres_listes'))) {

			$repertoire = scandir(app_path('Migrations/Champs_libres_listes'));

			foreach($repertoire as $fichier) {

				if($fichier == '.' || $fichier == '..')
					continue;

				$contenu_tmp = require(app_path('Migrations/Champs_libres_listes/'.$fichier));

				$index_tableau = str_replace('.php', '', $fichier);

				if(isset($tables[$index_tableau])) {

					foreach($contenu_tmp as $nom_colonne => $infos) {

						$tables[$index_tableau][$nom_colonne] = $infos;
					}
				}
				else {

					$tables[$index_tableau] = $contenu_tmp;
				}
			}
		}

        foreach ($tables as $unetable => $le_repertoire) {

            $type_element_cl = $le_repertoire['options']['type_element'];
            $nom_sql_cl = $le_repertoire['options']['nom_sql'];

            $le_champ_libre = Champ_libre::where('type_element', $type_element_cl)->where('nom_sql', $nom_sql_cl)->first();

            if ($le_champ_libre == null)
                continue;

            $id_cl = $le_champ_libre['id_cl'];

            $cles_valeurs = array_keys($le_repertoire['champs']);

            $migration_deja_regenere = false;

            foreach($cles_valeurs as $cle_valeur){

                if($migration_deja_regenere)
                    continue;

                if(strpos($cle_valeur,'valeurs_listes_libres.') !== false)
                    $migration_deja_regenere = true;

            }
            
            if($migration_deja_regenere === false)
                $controller->generer_fichier_migration_champ_libre_liste($id_cl,true);

        }

        return true;

    }
}