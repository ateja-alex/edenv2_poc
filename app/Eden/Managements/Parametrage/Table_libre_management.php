<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Champs_libres;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Utilisateur;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Maintenance_management;

use App\Eden\Variables;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use DB;


class Table_libre_management {


	/**
	 *
	 * Classe les tables libres par module
	 *
	 */
	public static function classer_tables_libres_par_module($tables_libres) {

		$modules = $tables_libres->pluck('module')->unique();

		$liste_tables_libres = array();
		$tables_libres_divers = array();

		foreach ($modules as $module) {

			if($module == null) {

				$tables_libres_divers = $tables_libres->where('module', null);

				$module = "Divers";
			}
			else {

				$tables_libres_module = $tables_libres->where('module', $module);

				$liste_tables_libres[$module] = collect();

				foreach ($tables_libres_module as $table_libre) {

					$liste_tables_libres[$module]->push($table_libre);
				}
			}
		}

		if(!empty($tables_libres_divers)) {

			$liste_tables_libres['Divers'] = $tables_libres_divers;
		}

		return $liste_tables_libres;
	}
    

    /**
     * Récupère les informations concernant les types d'éléments données en paramètre
     *
     * @param array $types_elements Liste des types d'éléments recherchés sous
     *                              forme de tableau de string
     *
     * @return Collection
     */
    public function recuperer_informations_types_elements($types_elements) {

        $liste_types_elements = Table_libre::whereIn('nom_table_sql', $types_elements)->get();

        $liste_finale = array();

        foreach($liste_types_elements as $type_element) {

            $liste_finale[$type_element->nom_table_sql] = $type_element;
        }

        return $liste_finale;
    }

    /**
     * Ajoute une colonne utilisateur à la requête de modification de la table
     *
     * @param string $table nom de la table à laquelle ajouter une colonne utilisateur
     *
     * @param int $id_utilisateur id de l'utilisateur pour lequel il faut ajouter une colonne
     *
     * @return string chaîne de caractères à ajouter à la requête si la colonne n'existe pas sinon une chaîne vide
     */
    static private function ajouter_colonne_utilisateur($table, $id_utilisateur) {

        $colonne_utilisateur = "utilisateur_" . $id_utilisateur;

        if (!Schema::hasColumn($table, $colonne_utilisateur)) {

            return "add `". $colonne_utilisateur . "` int not null";
        }

        return "";
    }

	/**
	 *
	 * Crée la table sur la base de données
	 *
	 */
	public static function cree_table($type_element, $initialisation = false, $informations_tables = null) {

        $colonne_inactif = !in_array($type_element,Variables::$documents_gescom_lignes);

		// on crée la table si elle n'existe pas
		if((isset($informations_tables['tables']) && !in_array($type_element,$informations_tables['tables']))
            ||
            ($informations_tables === null && !\Schema::hasTable($type_element))) {
			DB::select('CREATE TABLE`'.$type_element.'` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`modifie_par` int(11) NOT NULL,`modifie_le` datetime NOT NULL, `cree_par` int(11) NOT NULL,`cree_le` datetime NOT NULL, '.($colonne_inactif ? '`inactif` int(11),' : '').'`chaine_tags_recherche` longtext, `chaine_affichage` text, PRIMARY KEY (`id`), FULLTEXT INDEX (`chaine_tags_recherche`)) ENGINE=InnoDB DEFAULT CHARSET=latin1');
			return;
		}

        $colonnes_a_verifier = [
            'chaine_tags_recherche' => 'longtext',
            'chaine_affichage' => 'text',
        ];

        if($colonne_inactif)
            $colonnes_a_verifier['inactif'] = 'int(11)';

        foreach($colonnes_a_verifier as $colonne =>$type){

            if(isset($informations_tables['colonnes_par_type_element']))
                $presence_colonne = !empty($informations_tables['colonnes_par_type_element'][$type_element]) && in_array($colonne,$informations_tables['colonnes_par_type_element'][$type_element]);
            else
                $presence_colonne = !empty(DB::select('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = "'.env('DB_DATABASE').'" AND table_name = "'.$type_element.'" AND column_name = "'.$colonne.'"'));

            if($presence_colonne === false) {
                DB::select('ALTER TABLE`' . $type_element . '` ADD `' . $colonne . '` ' . $type);

            }

			if($colonne == 'inactif' || $colonne == 'chaine_tags_recherche') {

				if(isset($informations_tables['indexes_par_table']))
					$presence_index = !empty($informations_tables['indexes_par_table'][$type_element]) && in_array($colonne,$informations_tables['indexes_par_table'][$type_element]);
				else
					$presence_index = !empty(DB::select('SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = "'.env('DB_DATABASE').'" AND table_name = "'.$type_element.'" AND index_name = "'.$colonne.'"'));

				if ($presence_index === false){

					if($colonne == 'inactif')
						DB::select('CREATE INDEX `inactif` ON `' . $type_element . '` (`inactif`)');
					else if($colonne == 'chaine_tags_recherche')
						DB::select('ALTER TABLE`' . $type_element . '` ADD FULLTEXT INDEX `chaine_tags_recherche` (`chaine_tags_recherche`)');

				}
			}
        }
    }


	/**
	 *
	 * Vérifie si les champs par défaut sont bien présents et les crée, le cas échéant
	 *
	 */
    public static function champs_libres_par_defaut($type_element, $initialisation = false) {

        if(defined('migration_en_cours') || (!empty(table_libre($type_element)) && table_libre($type_element)->vue_sql == 1))
            return true;
        
    	// On crée les champs par défaut
    	$champs = [
    				'modifie_le'  => [
    					'nom' => 'Modifié le',
						'nom_sql' => 'modifie_le',
						'type' => 5,
    				],

    				'cree_le' 	  => [
						'nom' => 'Créé le',
						'nom_sql' => 'cree_le',
						'type' => 5,
    				],

    				'cree_par' 	  => [
						'nom' => 'Créé par',
						'nom_sql' => 'cree_par',
                        'type' => 42,
                        'type_element_ajax' => 'utilisateur',
    				],

    				'modifie_par' => [

						'nom' => 'Modifié par',
						'nom_sql' => 'modifie_par',
                        'type' => 42,
                        'type_element_ajax' => 'utilisateur',
    				],

    				'cle_externe' => [
		                'nom' => 'Clé Externe',
		                'nom_sql' => 'cle_externe',
		                'type' => 0,
    				],
    			];

    	foreach($champs as $nom_sql => $donnees) {

	        $count = Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql)->count();
	        if(empty($count)) {
	            Champ_libre_management::enregistre($type_element, $donnees, $initialisation);
	        }
    	}
    }

	/**
	 *
	 * Enregistre une nouvelle table libre & sa table correspondante
	 *
	 */
	public static function enregistre($element, $donnees, $type_element = false, $initialisation = false) {

		if(fonctionnalite('utiliser_extranet')){

			if(empty($donnees['type_profil_extranet']))
				$donnees['type_profil_extranet'] = "client";

			if(empty($donnees['champ_profil_extranet'])) {

				if(!empty($donnees['type_profil_extranet']))
					$donnees['champ_profil_extranet'] = $donnees['type_profil_extranet'] . "_id";

				else
					$donnees['champ_profil_extranet'] = "client_id";
			}

			if(empty($donnees['acces_extranet']))
				$donnees['acces_extranet'] = 0;
		}

		// on vérifie l'intégrité des données
		if(empty($donnees['nom_table']))
			return "Le champ nom est obligatoire";

		elseif(empty($donnees['element']))
			return "Le champ element est obligatoire";

		elseif(empty($donnees['element_pluriel']))
			return "Le champ element pluriel est obligatoire";

		elseif(empty($donnees['id_table'])){

			$table_libre = new Table_libre;

			// on crée le nom_table_sql
			if($type_element === false) {

				$accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å',
					'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
					'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'é', // (le dernier 'é' n'est pas un doublon, c'est un caractère spécial)
					'Ç', 'ç',
					'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï',
					'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü',
					'ÿ',
					'Ñ', 'ñ');

				$sans_accents = str_split('AAAAAAaaaaaaOOOOOOooooooEEEEeeeeeCcIIIIiiiiUUUUuuuuyNn');

				$nom_table_sql = str_replace($accents, $sans_accents, $element);

				$nom_table_sql = preg_replace("[^A-Za-z0-9]", '', $nom_table_sql);

				// autres caractères spéciaux
				$a_remplacer = str_split("&-(){}!?,;.:/\#[]'\" ~`^@°+=£€¤*%§<>\$²");

				$a_retirer = str_split('’̀');

				$nom_table_sql = str_replace($a_remplacer, '_', $nom_table_sql);
				$nom_table_sql = str_replace($a_retirer, '', $nom_table_sql);

				$nom_table_sql = strtolower(str_replace('__', '_', $nom_table_sql));

				$nom_table_sql_tmp = $nom_table_sql;
			}
			else {

				$nom_table_sql = $type_element;
				$nom_table_sql_tmp = $type_element;
			}

			$count = 1;

			//Permet d'éviter les doublons nom_table_sql en ajoutant 1 en fin de nom sql
			while(Table_libre::where('nom_table_sql', $nom_table_sql_tmp)->count() > 0) {

				$nom_table_sql_tmp = $nom_table_sql.'_'.$count;
				$count++;
			}

			//On enregistre chacun des champs qui sont fixes ou variables. Si élément n'est pas rempli, il est identique à nom_table
            if(empty($donnees['creation_rapide']))
                $table_libre->creation_rapide = 0;
			else
				$table_libre->creation_rapide = $donnees['creation_rapide'];

			$table_libre->element = $donnees['element'];
		    $table_libre->element_pluriel = $donnees['element_pluriel'];
			$table_libre->nom_table_sql = $nom_table_sql_tmp;
			$table_libre->type_element = $nom_table_sql_tmp;
			$table_libre->nom_table = $donnees['nom_table'];
	    	$table_libre->description = '';
	        $table_libre->feminin = '';
	    	$table_libre->fiche = 0 ;
		    $table_libre->disponible_recherche_rapide = 0 ;
            $table_libre->vue_sql = 0;
            $table_libre->non_logue = 0;

			if(fonctionnalite('utiliser_extranet')){

				$table_libre->type_profil_extranet = $donnees['type_profil_extranet'];
				$table_libre->champ_profil_extranet = $donnees['champ_profil_extranet'];
				$table_libre->acces_extranet = $donnees['acces_extranet'];
			}

			if(isset($donnees['fiche']))
				$table_libre->fiche = $donnees['fiche'];

			if(isset($donnees['disponible_recherche_rapide']))
				$table_libre->disponible_recherche_rapide = $donnees['disponible_recherche_rapide'];

			if(isset($donnees['feminin']))
				$table_libre->feminin = $donnees['feminin'];

			if(isset($donnees['template_responsive']))
				$table_libre->template_responsive = $donnees['template_responsive'];

            if(isset($donnees['vue_sql']))
                $table_libre->vue_sql = $donnees['vue_sql'];

            if(isset($donnees['non_logue']))
                $table_libre->non_logue = $donnees['non_logue'];

            if(!empty($donnees['index_traduction']))
                $table_libre->index_traduction = $donnees['index_traduction'];
            elseif(!$initialisation){

                if(!in_array($type_element,array('traduction_valeur','traduction_index','traduction_langue'))) {

                    $table_libre->index_traduction = service('traduction')->calcul_index_traduction(
                        2,
                        array(
                            'tables_libres',
                            $nom_table_sql_tmp,
                        ),
                        array(
                            'nom_table' => $donnees['nom_table'],
                            'element' => $donnees['element'],
                            'element_pluriel' => $donnees['element_pluriel']
                        ),
                        false
                    );
                }
            }

		    $table_libre->save();

		    // Cas particulier d'une vue
            if($table_libre->vue_sql != 1){

                // on crée la table sur la base de données
                self::cree_table($nom_table_sql_tmp, $initialisation);

                self::champs_libres_par_defaut($table_libre->type_element);

                self::generer_fichier_migration($nom_table_sql_tmp);
            }

			return true;
		}
		else
			return true ;
	}

	/**
	 *
	 * Génère le nouveau fichier de migration
	 *
	 */
	public static function generer_fichier_migration($type_element,$script = false){

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours') && $script === false)
			return true;

		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations';
        $chemin_avec_nom_document = $chemin_dossier_migrations . '/' .$type_element.'.php';

		if(!\File::isDirectory($chemin_dossier_migrations))
        	\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
        	return false;

		$table_libre = Table_libre::where('type_element',$type_element)->first();
		$les_champs_libres = Champ_libre::where('type_element',$type_element)->get();
		$recherches_avancees = modele('recherche_avancee')
            ->where('type','like','champs_libres.'.$type_element.'%')
            ->get()->groupBy('type');

        $champs_supprimes = array();

        $champs_libres_standard = Champs_libres::champs_libres_defaut(true);

        if(isset($champs_libres_standard[$type_element]) && !$script)
            $champs_supprimes = array_diff(array_keys($champs_libres_standard[$type_element]),$les_champs_libres->pluck('nom_sql')->toArray());
        else if (file_exists($chemin_avec_nom_document) && $script) {
            $contenu = include($chemin_avec_nom_document);
            if(!empty($contenu['champs_libres_supprimes']))
                $champs_supprimes = $contenu['champs_libres_supprimes'];
        }

		// on convertit l'array en texte pour le document de migration

		$la_table_libre = $table_libre->toArray();

		$texte =
		'<?php '."\n\n".'return ['."\n\t\t".'\'table_libre\' => ['."\n";
		foreach ($la_table_libre as $clef => $valeur) {

			if($clef == 'id')
				continue;

			if(!in_array($clef,['template_responsive','valeurs_forcees_creation_extranet']))
				$texte .= "\t\t\t".'\''.$clef.'\' => "'.$valeur.'",'."\n";
			else
				$texte .= "\t\t\t".'\''.$clef.'\' => "'.str_replace('"', '\\"', $valeur).'",'."\n";
		}

		$texte = $texte."\t\t".'],'."\n\t\t".'\'champs_libres\' => ['."\n";
					
		foreach ($les_champs_libres as $champ_libre) {

			$liste_valeur = $champ_libre->toArray();

			$texte = $texte."\t\t\t".'\''.$liste_valeur['nom_sql'].'\' => [';
			
			foreach ($liste_valeur as $clef => $valeur) {

				if ($clef == 'id_cl' || $clef == 'profils')
					continue;

				$texte = $texte."\n\t\t\t\t".'\''.$clef.'\' => "'.str_replace('"', '\\"', $valeur).'",';
			}

			if(!empty($recherches_avancees['champs_libres.'.$type_element.'.'.$champ_libre->nom_sql])){

				$filtres = '';

				$filtres .= "[";

				foreach($recherches_avancees['champs_libres.'.$type_element.'.'.$champ_libre->nom_sql] as $recherche_avancee){

					$structure = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)
						->structure(true);

					$filtres .= "\n\t\t\t\t\t".'\''.$recherche_avancee->id_cible.'\' => '.str_replace("\n","\n\t\t\t\t\t",var_export($structure, true)).', ';
				}

				$filtres .= "\n\t\t\t\t".']';

				$texte.= "\n\t\t\t\t'filtres' => ".$filtres.",";
			}

			$texte = $texte."\n\t\t\t".'],'."\n";
		}
            		
		$texte = $texte."\t\t".'],'."\n\t\t".'\'champs_libres_supprimes\' => ['."\n";
                    
		foreach ($champs_supprimes as $nom_sql_champ_supprime) {

			$texte = $texte."\t\t\t".'\''.$nom_sql_champ_supprime.'\','."\n";
		}
		
		$texte = $texte."\t\t".'],'."\n\t".'];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
		// Si le fichier existe, on le supprime
	    if (file_exists($chemin_avec_nom_document) == true)
	    	unlink($chemin_avec_nom_document);

	    // Enregistrement du fichier
	    $fichier = fopen($chemin_avec_nom_document, "x+");
	    fputs($fichier, $texte );
	    fclose($fichier);

	    return true;

	}

	/**
	*
	* Enregistre une modification d'etat
	*
	*/
	public static function changement_etat($type_element, $nom_sql, $valeur, $parametre) {

		$table_libre = Table_libre::where('type_element', $type_element)->first();
		$table_libre->$parametre = $valeur;
		$table_libre->save();

		$retour = Table_libre_management::generer_fichier_migration($type_element);

		if (!$retour) {

			return 'Le répertoire Migrations n\'est pas  accessible en écriture !';
		}

		return true;
	}

    /**
     *
     *
     *
     */
    public static function verification_affichage($donnee_table_libre){

        $verifications_a_effectuer = array(
            array(
                'nom' => 'Affichage recherche',
                'index' => 'affichage_recherche'
            ),
            array(
                'nom' => 'Affichage fiche type',
                'index' => 'affichage_fiche_type'
            ),
            array(
                'nom' => 'Affichage dans la liste',
                'index' => 'affichage_dans_liste'
            ),
            array(
                'nom' => 'Affichage dans select',
                'index' => 'affichage_pour_select'
            ),
            array(
                'nom' => 'Affichage extranet',
                'index' => 'affichage_extranet'
            ),
			array(
                'nom' => 'Affichage kanban',
                'index' => 'affichage_dans_kanban'
            ),
			array(
                'nom' => 'Affichage dédoublonnage',
                'index' => 'affichage_dedoublonnage'
            ),
			array(
				'nom' => 'Affichage planning',
				'index' => 'affichage_planning'
			),
			array(
				'nom' => 'Affichage calendrier',
				'index' => 'affichage_calendrier'
			),
        );

        $champs = champs_libres($donnee_table_libre['type_element'])->pluck('nom_sql')->toArray();

        foreach($verifications_a_effectuer as $verification){

            $valeur = $donnee_table_libre[$verification['index']];

            if(empty($valeur))
                continue;

            $correspondances = array();
            preg_match_all('/#(.*?)#/', $valeur, $correspondances);

            $differences = array_diff($correspondances[1],$champs);

            if(!empty($differences))
                return 'Le champ '.$verification['nom'].' comporte des champs inexistants';
        }

        return true;
    }

    public function chargement_valeurs_forcees_extranet(&$table){

        if(empty($table->valeurs_forcees_creation_extranet))
            $table->valeurs_forcees_creation_extranet = [];
        else
            $table->valeurs_forcees_creation_extranet = json_decode($table->valeurs_forcees_creation_extranet);

        $type_element = $table->type_element;

        $champs_libres = champs_libres($type_element);

        $champs_libres_tries = [];

        foreach($champs_libres as $champ_libre){

            if(in_array($champ_libre->nom_sql,['modifie_le','modifie_par','cree_le','cree_par']))
                continue;

            $champ_libre->champ_creation = management($type_element)
                ->champ($champ_libre->nom_sql)
				->vmodel(true,$type_element,$champ_libre->nom_sql)
                ->cree();

            $champs_libres_tries[] = $champ_libre;
        }

        $champs_libres = $champs_libres_tries;

        return $champs_libres;
    }
}
