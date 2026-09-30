<?php

namespace App\Eden;

use App\Eden\Models\Champ_libre;

/**
 *
 * Liste des tables libres & champs libres standards, à mettre à jour lorsque de nouvelles tables libres et champs libres sont créés
 *
 */
class Champs_libres {

    public static function champs_libres_defaut($seulement_standard = false) {

		$champs_libres = [];

		// on vient ajouter les champs libres
		$repertoire_champs_libres = scandir(app_path('Eden/Migrations'));

		foreach($repertoire_champs_libres as $fichier) {

			if(in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches','Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees' ,'Scripts', 'Vue_sql')))
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

            $champs_libres[$index_tableau] = $contenu_tmp['champs_libres'] ?? [];

			foreach($champs_libres[$index_tableau] as $nom_sql => $valeurs) {

				$champs_libres[$index_tableau][$nom_sql]['standard'] = 1;
			}

		}

		if($seulement_standard === true) {

			return $champs_libres;
		}

		// on vient ajouter les tables libres spécifiques
		if(is_dir(app_path('Migrations'))) {

			$repertoire_champs_libres = scandir(app_path('Migrations'));

			foreach($repertoire_champs_libres as $fichier) {

				if(in_array($fichier, array('.', '..', 'Rapports', 'Listes_libres', 'Listes_libres_fiches', 'Listes_libres_export', 'Tables', 'Formulaires_libres', 'Sous_formulaire', 'Champs_libres_listes', 'Champs_libres_listes_formatees','Listes_libres_couleurs','Listes_libres_autresvues','Scripts', 'Vue_sql')))
					continue;

				$contenu_tmp = require(app_path('Migrations/'.$fichier));

				$index_tableau = str_replace('.php', '', $fichier);

                $champs_libres[$index_tableau] = array_merge($champs_libres[$index_tableau] ?? [],$contenu_tmp['champs_libres'] ?? []);

                if(isset($contenu_tmp['champs_libres_supprimes'])){

                    foreach($contenu_tmp['champs_libres_supprimes'] as $champ_libre_supprime){

                        if(isset($champs_libres[$index_tableau][$champ_libre_supprime]))
                            unset($champs_libres[$index_tableau][$champ_libre_supprime]);
                    }
                }
			}
		}

		$valeurs_defaut = array(

			'nom' => '',
			'type' => 0,
			'inactif' => 0,
			'aide' => '',
			'liste_choix' => 0,
			'format_champ' => '',
			'recherche' => 0,
			'obligatoire' => 0,
			'unique' => 0,
			'unique_entite' => 0,
			'lecture_seule' => 0,
			'type_element_ajax' => '',
			'modification_post_validation' => 0,
            'index' => false,
		);

		// on ajoute les valeurs par défaut
		foreach($champs_libres as $table_libre => $liste_champs_libres) {

			foreach($liste_champs_libres as $nom_sql => $infos_champ_libre) {

				foreach($valeurs_defaut as $cle => $valeur) {

					if(!isset($infos_champ_libre[$cle]))
						$champs_libres[$table_libre][$nom_sql][$cle] = $valeur;
				}
			}
		}

		return $champs_libres;
	}


    public static function champs_libres() {

		$valeurs_defaut = array(

			'nom' => '',
			'type' => 0,
			'inactif' => 0,
			'aide' => '',
			'liste_choix' => 0,
			'format_champ' => '',
			'recherche' => 0,
			'obligatoire' => 0,
			'lecture_seule' => 0,
			'type_element_ajax' => '',
			'modification_post_validation' => 0,
			'aide' => '',
		);

		$champs_libres = self::champs_libres_defaut();

		// on écrase avec les données en base
		$champs_libres_bdd = Champ_libre::orderBy('ordre')->orderBy('id_cl')->select('*', \DB::raw('COALESCE(ordre, 0) as ordre'))->get();

		if($champs_libres_bdd !== null) {

			foreach($champs_libres_bdd as $champ_libre) {

				if(!isset($champs_libres[$champ_libre->type_element]))
					$champs_libres[$champ_libre->type_element] = array();

				$champs_libres[$champ_libre->type_element][$champ_libre->nom_sql] = array();

				foreach($valeurs_defaut as $cle => $valeur) {

					if(!isset($champs_libres[$champ_libre->type_element][$champ_libre->nom_sql][$cle]))
						$champs_libres[$champ_libre->type_element][$champ_libre->nom_sql][$cle] = $champ_libre->$cle;
				}
			}
		}

		self::cree_fichier($champs_libres, 'eden_champs_libres');
		self::cree_fichier($champs_libres, 'eden_champs_libres_obligatoires', 'obligatoire', 1);
		self::cree_fichier($champs_libres, 'eden_champs_libres_recherche', 'recherche', 1);

		return $champs_libres;
    }

	protected static function cree_fichier($champs_libres, $nom_fichier, $filtre = false, $valeur_filtre = false) {

		$contenu_fichier = "<?php\n\nreturn [\n";

		// on retourne la liste des champs libres
		foreach($champs_libres as $table_libre => $liste_champs_libres) {


			$contenu_fichier .= "\t'$table_libre' => [\n";

			foreach($liste_champs_libres as $nom_sql => $infos_champ_libre) {

				if($filtre !== false && $infos_champ_libre[$filtre] !== $valeur_filtre)
					continue;

				$contenu_fichier .= "\t\t'$nom_sql' => [\n";

				foreach($infos_champ_libre as $cle => $valeur) {

					if(is_integer($valeur))
						$contenu_fichier .= "\t\t\t'$cle' => $valeur,\n";
					else
						$contenu_fichier .= "\t\t\t'$cle' => \"".str_replace('"', '\"', $valeur)."\",\n";
				}

				$contenu_fichier .= "\t\t],\n";
			}

			$contenu_fichier .= "\t],\n";
		}

		$contenu_fichier .= "];";

		// on stocke dans un fichier
		\Storage::put($nom_fichier.'.php', $contenu_fichier);
	}

    public static function migration_vue_sql($seulement_standard = false) {

        $vue_sql = [];

        // on vient ajouter les champs libres
        $repertoire_vue_sql = scandir(app_path('Eden/Migrations/Vue_sql'));

        $valeurs_defaut = array(

            'nom' => "",
			'nom_sql' => "",
            'joins' => "",
            'tables' => "",
            'alias_tables' => "",
            'table_par_defaut' => 0,
            'type_de_vue' => 0,
            'requete' => "",
            'autres_conditions' => "",
        );

        foreach($repertoire_vue_sql as $fichier) {

            if(in_array($fichier, array('.', '..')))
                continue;
            
            $contenu_tmp = require(app_path('Eden/Migrations/Vue_sql/'.$fichier));
            
            $nom_fichier = str_replace('.php', '', $fichier);

            if(isset($contenu_tmp) && is_array($contenu_tmp)){
                $vue_sql[$nom_fichier] = array_merge($valeurs_defaut, $contenu_tmp);
            }else{
                $vue_sql[$nom_fichier] = $valeurs_defaut;
            }

        }

        if($seulement_standard === true)
            return $vue_sql;
        

        // on vient ajouter les tables libres spécifiques
        if(is_dir(app_path('Migrations/Vue_sql'))) {

            $repertoire_vue_sql = scandir(app_path('Migrations/Vue_sql'));

            foreach($repertoire_vue_sql as $fichier) {
                
                if(in_array($fichier, array('.', '..')))
                    continue;
                
                $contenu_tmp = require(app_path('Migrations/Vue_sql/'.$fichier));

                $nom_fichier = str_replace('.php', '', $fichier);

                if (isset($contenu_tmp) && is_array($contenu_tmp)) {
                    $vue_sql[$nom_fichier] = array_merge($valeurs_defaut, $contenu_tmp);
                }else{
                    $vue_sql[$nom_fichier] = $valeurs_defaut;
                }
            }
        }
        return $vue_sql;
    }

}






