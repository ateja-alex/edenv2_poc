<?php

namespace App\Eden;

use App\Eden\Models\Table_libre;

/**
 *
 * Liste des tables libres & champs libres standards
 *
 */
class Tables_libres {

	/**
	 *
	 * Retourne la liste des tables libres
	 *
	 */
    public static function tables_libres() {

		$tables_libres = [];

		// on vient ajouter les tables libres standards
		$repertoire_tables_libres = scandir(app_path('Eden/Migrations'));

		foreach($repertoire_tables_libres as $fichier) {

			if(in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees', 'Scripts', 'Vue_sql')))
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/'.$fichier));

			if(isset($contenu_tmp['table_libre'])) {

				$index_tableau = str_replace('.php', '', $fichier);

				$tables_libres[$index_tableau] = $contenu_tmp['table_libre'];
            }
		}

		// on vient ajouter les tables libres spécifiques
		if(is_dir(app_path('Migrations'))) {

			$repertoire_tables_libres = scandir(app_path('Migrations'));

			foreach($repertoire_tables_libres as $fichier) {

				if(in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches','Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees','Listes_libres_couleurs','Listes_libres_autresvues', 'Scripts', 'Vue_sql')))
					continue;

				$contenu_tmp = require(app_path('Migrations/'.$fichier));

				if(isset($contenu_tmp['table_libre'])) {

					$index_tableau = str_replace('.php', '', $fichier);

					$tables_libres[$index_tableau] = $contenu_tmp['table_libre'];
				}
			}
		}

		return $tables_libres;
    }

    /**
	 *
	 * Retourne la liste des tables libres standard
	 *
	 */
    public static function tables_libres_standard() {

        $tables_libres = [];

		// on vient ajouter les tables libres standards
		$repertoire_tables_libres = scandir(app_path('Eden/Migrations'));

		foreach($repertoire_tables_libres as $fichier) {

			if(in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches','Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Scripts', 'Vue_sql')))
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/'.$fichier));

			if(isset($contenu_tmp['table_libre'])) {

				$index_tableau = str_replace('.php', '', $fichier);

				$tables_libres[] = $index_tableau;
			}
		}

        return $tables_libres;
    }
}






