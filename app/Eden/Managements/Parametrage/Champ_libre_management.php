<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Variables;

use App\Eden\Managements\Cache_management;

use DB;
use Session;
use Illuminate\Support\Collection;

class Champ_libre_management {

	function __construct($type_element, $nom_sql) {


		$this->modele = champ_libre_modele($type_element, $nom_sql);

		if(!is_object($this->modele)) {

			throw new \App\Eden\Exceptions\Eden_exception("Le champ libre \"$nom_sql\" n'a pas été trouvé pour l'élément \"$type_element\"");
		}

		if(empty($this->modele->type_element)) {

			throw new \App\Eden\Exceptions\Eden_exception("Le champ libre \"$nom_sql\" n'a pas été trouvé pour l'élément \"$type_element\"");
		}

		$this->champ = champ($this->modele);

	}

	/**
	*
	* Regarde si cette valeur est une valeur vide pour ce type de champ
	*
	*/
	public function valeur_vide($valeur) {

		if(empty($valeur))
			return true;

		if($this->modele->type == 4 && $valeur == '0000-00-00' || $valeur == '00/00/0000')
			return true;

		if($this->modele->type == 5 && $valeur == '0000-00-00' || $valeur == '00/00/0000')
			return true;

		if($this->modele->type == 5 && $valeur == '0000-00-00 00:00:00' || $valeur == '00/00/0000 00:00:00')
			return true;

		return false;
	}

	/**
	*
	* transforme la valeur du champ en une valeur pour une insertion SQL (notamment les dates !)
	*
	* Attention initialement on appelait cette méthode dans element_management::retire_modifications_sans_champs_libres
	* Mais j'ai copié collé le code ci dessous pour des raisons de perf (ne pas instancier champ_libre_management)
	*
	*/
	public function pour_sql($valeur) {

		if($this->modele->type == 4)
			return formate_date('Y-m-d', $valeur);

		if($this->modele->type == 5)
			return formate_date('Y-m-d H:i:s', $valeur);

		return $valeur;
	}

	/**
	 *
	 * Crée la colonne sur la table pour le champ libre
	 *
	 */
	public static function cree_colonne_sur_table($type_element, $nom_sql_tmp, $type, $donnees = null, $est_table_pivot = false) {

        $types_champs = Variables::types_champs_libres_bdd();

		$unsigned = false;

		// c'est un titre, on ne crée pas de colonne
		if($type == -1)
			return;

		// c'est un onglet, on ne crée pas de colonne
		if($type == -3)
			return;

		// c'est une fin d'onglet, on ne crée pas de colonne
		if($type == -4)
			return;

		// c'est un sous formulaire, on ne crée pas de colonne
		if($type == -5)
			return;

        $champ = $types_champs[$type] ?? 'VARCHAR(1000)';
        $type_champ = is_array($champ) ? $champ['type'] : $champ;
        $unsigned = is_array($champ) ? ($champ['unsigned'] ?? false) : false;

        if($est_table_pivot == true){
            DB::select('ALTER TABLE `' . $type_element.'_'.$donnees['nom_sql'] . '` ADD `' . $nom_sql_tmp . '` ' . $type_champ . ($unsigned ? ' UNSIGNED' : '') . ' NULL;');
            if($donnees['type'] == 10){
                $nom_contrainte = 'CE_table_pivot_' . $donnees['id_cl'];
                try{
                    DB::select('ALTER TABLE `' . $type_element.'_'.$donnees['nom_sql'] . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`cle_locale`) REFERENCES `' . $type_element . '`(`id`);');
                    $infos_return[] = "on crée la clé étrangére sur " . $nom_sql_tmp . " de la table " . $type_element;
                } catch (\Exception $e) {
                    $erreurs_cle_etrangere[] = 'Erreur sur la clé étrangére sur cle_locale de la table ' . $type_element . '<a href="' . route('maintenance.erreur_struc_table', ['type_element' => $type_element]) . '"> Liste des erreurs de clés étrangères.<a>';
                }
            }

            if($type == 42){

                $nom_contrainte = 'CE_valeur_table_pivot_' . $donnees['id_cl'];

                try {
                    DB::select('ALTER TABLE `' . $type_element.'_'.$donnees['nom_sql'] . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`'.$nom_sql_tmp.'`) REFERENCES `' . $donnees['type_element_ajax'] . '`(`id`);');
                    $infos_return[] = "on crée la clé étrangére sur " . $nom_sql_tmp . " de la table " . $type_element;
                } catch (\Exception $e) {
                    $erreurs_cle_etrangere[] = 'Erreur sur la clé étrangére sur ' . $nom_sql_tmp . ' de la table ' . $type_element . '<a href="' . route('maintenance.erreur_struc_table', ['type_element' => $type_element]) . '"> Liste des erreurs de clés étrangères.<a>';
                }
            }
        } else {
            DB::select('ALTER TABLE `' . $type_element . '` ADD `' . $nom_sql_tmp . '` ' . $type_champ . ($type == 42 ? '(11)' : '') . ($unsigned ? ' UNSIGNED' : '') . ' NULL;');

            if($type == 42){
                $nom_contrainte = 'CE_champ_libre_' . $donnees['id_cl'];

                try{
                    DB::select('ALTER TABLE `' . $type_element . '` ADD CONSTRAINT `'.$nom_contrainte.'` FOREIGN KEY (`' . $nom_sql_tmp . '`) REFERENCES `' . $donnees['type_element_ajax'] . '`(`id`);');
                    $infos_return[] = "on crée la clé étrangére sur " . $nom_sql_tmp . " de la table " . $type_element;
                } catch (\Exception $e) {
                    $erreurs_cle_etrangere[] = 'Erreur sur la clé étrangére sur cle_locale de la table ' . $type_element . '<a href="' . route('maintenance.erreur_struc_table', ['type_element' => $type_element]) . '"> Liste des erreurs de clés étrangères.<a>';
                }

            }
        }


	}

	/**
	*
	* Enregistre un champ libre
	*
	*/
	public static function enregistre($type_element, $donnees, $initialisation = false) {

	    $table_libre = Table_libre::where('type_element',$type_element)->first();

		// on vérifie l'intégrité des données
		if(empty($donnees['nom']) && empty($donnees['id_cl'])) {

			return "Le champ nom est obligatoire";
		}

		if($donnees['nom_sql'] == 'id_element') {

			return "Il est impossible de créer un champs libre avec le nom SQL 'id_element'.";
		}

		if(!isset($donnees['id_cl']) || $donnees['id_cl'] == null) {

			$champ_libre = new Champ_libre;


			$nom_sql_tmp = $donnees['nom_sql'];
			$nom_sql = $donnees['nom_sql'];

			$count = 1;

			while(Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql_tmp)->count() > 0) {

				$nom_sql_tmp = $nom_sql.'_'.$count;

				$count++;
			}

			$champ_libre->nom_sql = $nom_sql_tmp;

            if(!in_array($type_element,array('traduction_valeur','traduction_index','traduction_langue')) && !$initialisation) {

                $champ_libre->index_traduction = service('traduction')->calcul_index_traduction(
                    1,
                    array(
                        'champs_libres',
                        $type_element,
                        $nom_sql_tmp,
                    ),
                    array(
                        'nom' => $donnees['nom']
                    ),
                    false
                );

            }
			
            // on crée une table pivot
            if ($donnees['type'] == 10) {

                if($table_libre->vue_sql == 1){

                    if(isset($donnees['table_pivot'])){

                        $champ_libre->table_pivot = $donnees['table_pivot'];

                    }
                }

                else {

                    $nom_de_la_table = $type_element . '_' . $champ_libre->nom_sql;
                    $nom_de_la_table_index = $nom_de_la_table;
                    $numero_table = 1;

                    while(\Schema::hasTable($nom_de_la_table_index)){
                        $nom_de_la_table_index = $nom_de_la_table . "_" . $numero_table;
                        $numero_table++;
                    }

                    $nom_de_la_table = $nom_de_la_table_index;

                    //on vérifie que la table n'existe pas avant de procéder à la création

                    $champ_libre->table_pivot = $nom_de_la_table;

                    \Schema::create($nom_de_la_table, function ($table) use($donnees){
                        $table->increments('id')->unsigned();
                        $table->integer('cle_locale')->unsigned();
                    });

                }
                $champ_libre->type_reference = $donnees['type_reference'] ?? null;
            }

		}
		else {

			$champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $donnees['nom_sql'])->first();
		}

		// Cas champ tableau
		if($donnees['type'] == 16) {

			$colonnes_tableau = $donnees['colonnes_tableau'];

			// On initialise le tableau vide si nouveau champ
			if ($champ_libre->contenu_tableau == null) {

				$contenu_tableau = management('element')->initaliser_tableau_vide();
				$contenu_tableau['titres'] = $colonnes_tableau;
			}

			else{

				$contenu_tableau = json_decode($champ_libre->contenu_tableau);
				$contenu_tableau->titres = $colonnes_tableau;
			}

			$champ_libre->contenu_tableau = json_encode($contenu_tableau);
		}
        if(in_array($donnees['type'], [4, 5, 8]) && isset($donnees['valeur_defaut_oui_non'])) {

            $donnees['valeur_defaut'] = $donnees['type'] == 8 ? "#maintenant" : "#aujourdhui";

            if(isset($donnees['valeur_defaut_ajout_quantite_1']) && isset($donnees['valeur_defaut_ajout_unite_1']))
                $donnees['valeur_defaut'] .= "+" . $donnees['valeur_defaut_ajout_quantite_1'] . " " .$donnees['valeur_defaut_ajout_unite_1'];

            if(isset($donnees['valeur_defaut_ajout_quantite_2']) && isset($donnees['valeur_defaut_ajout_unite_2']))
                $donnees['valeur_defaut'] .= "+" . $donnees['valeur_defaut_ajout_quantite_2'] . " " .$donnees['valeur_defaut_ajout_unite_2'];

            $donnees['valeur_defaut'] .= "#";
        }

		$champ_libre->type_element = $type_element;

		if(isset($donnees['type_element_ajax']))
			$champ_libre->type_element_ajax = $donnees['type_element_ajax'];

		// quelques valeurs par défaut
		if(!isset($donnees['obligatoire']))
			$donnees['obligatoire'] = 0;

		if(!isset($donnees['unique']))
			$donnees['unique'] = 0;

		if(!isset($donnees['unique_entite']))
			$donnees['unique_entite'] = 0;

		if(!isset($donnees['recherche']))
			$donnees['recherche'] = 0;

		if(!isset($donnees['badge_filtre']))
			$donnees['badge_filtre'] = 0;

		if(!isset($donnees['nombre_max_caracteres']))
			$donnees['nombre_max_caracteres'] = 0;

		if(!isset($donnees['lecture_seule']))
			$donnees['lecture_seule'] = 0;

		if(!isset($donnees['aide']))
			$donnees['aide'] = '';

		if(!isset($donnees['format_champ']))
			$donnees['format_champ'] = '';

		if(!isset($donnees['valeur_defaut']))
			$donnees['valeur_defaut'] = '';

		if(!isset($donnees['badge_cliquable']))
			$donnees['badge_cliquable'] = 0;

		if(!isset($donnees['ordre']))
			$donnees['ordre'] = 0;

		if(!isset($donnees['contenu']))
			$donnees['contenu'] = '';

		if(!isset($donnees['visibilite']))
			$donnees['visibilite'] = '';

		if(!isset($donnees['colonne_source']))
			$donnees['colonne_source'] = '';

		if(!isset($donnees['correspondance_fiche_tiers']))
			$donnees['correspondance_fiche_tiers'] = '';

		if(!isset($donnees['conditions_v_show_manuelle']))
			$donnees['conditions_v_show_manuelle'] = '';

		if(!isset($donnees['conditions_v_if_manuelle']))
			$donnees['conditions_v_if_manuelle'] = '';

        if(!isset($donnees['doit_etre_plus_petit_que']))
			$donnees['doit_etre_plus_petit_que'] = '';

        if(isset($donnees['doit_etre_plus_petit_que']))
            $champ_libre->doit_etre_plus_petit_que = $donnees['doit_etre_plus_petit_que'];

        if(isset($donnees['nom']))
		    $champ_libre->nom = $donnees['nom'];

        if(isset($donnees['champ_systeme']) && editeur())
            $champ_libre->champ_systeme = $donnees['champ_systeme'];

		$champ_libre->type = $donnees['type'];
		$champ_libre->obligatoire = $donnees['obligatoire'];
		$champ_libre->unique = $donnees['unique'];
		$champ_libre->unique_entite = $donnees['unique_entite'];
		$champ_libre->badge_cliquable = $donnees['badge_cliquable'];
		$champ_libre->afficher_sur_formulaire = $donnees['afficher_sur_formulaire'] ?? 0;

		$champ_libre->nombre_max_caracteres = $donnees['nombre_max_caracteres'];
		$champ_libre->recherche = $donnees['recherche'];
		$champ_libre->badge_filtre = $donnees['badge_filtre'];
		$champ_libre->lecture_seule = $donnees['lecture_seule'];
		$champ_libre->aide = $donnees['aide'];
		$champ_libre->inactif = 0;
		$champ_libre->contenu = $donnees['contenu'];
		$champ_libre->ordre = $donnees['ordre'];
		$champ_libre->conditions_v_show_manuelle = $donnees['conditions_v_show_manuelle'];
		$champ_libre->conditions_v_if_manuelle = $donnees['conditions_v_if_manuelle'];

		if(isset($donnees['doit_etre_plus_petit_que']))
			$champ_libre->doit_etre_plus_petit_que = $donnees['doit_etre_plus_petit_que'];
		else
			$champ_libre->doit_etre_plus_petit_que = null;

		if(isset($donnees['nombre_decimale']))
			$champ_libre->nombre_decimale = $donnees['nombre_decimale'];
		else
			$champ_libre->nombre_decimale = null;

		if(isset($donnees['decimal_separateur_milliers']))
			$champ_libre->decimal_separateur_milliers = $donnees['decimal_separateur_milliers'];
		else
			$champ_libre->decimal_separateur_milliers = null;

		$champ_libre->valeur_defaut = $donnees['valeur_defaut'];
		$champ_libre->colonne_source = $donnees['colonne_source'];
		$champ_libre->visibilite = $donnees['visibilite'];
		$champ_libre->correspondance_fiche_tiers = $donnees['correspondance_fiche_tiers'];
		$champ_libre->ne_pas_loguer = $donnees['ne_pas_loguer'] ?? null;

        if(array_key_exists('format_champ',$donnees))
			$champ_libre->format_champ = $donnees['format_champ'];

        if(array_key_exists('type_fichier',$donnees))
			$champ_libre->type_fichier = $donnees['type_fichier'];

		if(empty($donnees['aide']))
			$champ_libre->aide = '';

		if(array_key_exists('liste_choix',$donnees))
			$champ_libre->liste_choix = $donnees['liste_choix'];

        if(array_key_exists('nom_pj',$donnees))
			$champ_libre->nom_pj = $donnees['nom_pj'];

        if(array_key_exists('cacher_sans_valeur',$donnees))
			$champ_libre->cacher_sans_valeur = $donnees['cacher_sans_valeur'];

		if(array_key_exists('desactiver_creation_a_la_volee',$donnees))
			$champ_libre->desactiver_creation_a_la_volee = $donnees['desactiver_creation_a_la_volee'];

        if(array_key_exists('modifier_en_masse',$donnees))
			$champ_libre->modifier_en_masse = $donnees['modifier_en_masse'];

        if(array_key_exists('index',$donnees))
			$champ_libre->index = $donnees['index'];

        $filtres = $donnees['filtres'] ?? [];

        if(isset($donnees['type_element_origine']))
            $champ_libre->type_element_origine = $donnees['type_element_origine'];

        if(isset($donnees['nom_sql_origine']))
            $champ_libre->nom_sql_origine = $donnees['nom_sql_origine'];

        if(!empty($donnees['champ_liste_libre_parent']))
            $champ_libre->champ_liste_libre_parent = $donnees['champ_liste_libre_parent'];

        if(!empty($donnees['champ_liste_libre_liaisons']))
		    $champ_libre->champ_liste_libre_liaisons = $donnees['champ_liste_libre_liaisons'];

		$champ_libre->save();

        $recherches_avancees = modele('recherche_avancee')
            ->where('type', 'champs_libres.'.$champ_libre->type_element.'.'.$champ_libre->nom_sql)
            ->get()->keyBy('id_cible');

        foreach($filtres as $type_element_filtre => $structure){

            $id_cible = $type_element_filtre;
            $structure = json_decode($structure,true);

            if(empty($structure))
                continue;

            if(isset($recherches_avancees[$id_cible])){
                $recherche_avancee = $recherches_avancees[$id_cible];
                $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);
                unset($recherches_avancees[$id_cible]);
            }
            else
                $management_recherche_avancee = management('recherche_avancee');

            $management_recherche_avancee->enregistre([
                'type' => 'champs_libres.'.$champ_libre->type_element.'.'.$champ_libre->nom_sql,
                'type_element' => $type_element_filtre,
                'id_cible' => $id_cible,
                'structure' => $structure
            ]);
        }

        foreach($recherches_avancees as $recherche_avancee){
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

        // $champ_libre->modifiable = 1;
		// $champ_libre->ordre = 999;

		// si le champ existe déjà on ne le crée pas
		// c'est un cas un peu spécial, ou on crée les champs libres
		// après avoir créé des colonnes, après un import d'une base par exemple
		// mais c'est pas un cas qui arrive habituellement hors installation d'un projet

        if(defined("migration_en_cours") != true){
            if($table_libre->vue_sql != 1) {

                $colonnes = \Schema::getColumnListing($type_element);

                if (!in_array($champ_libre->nom_sql, $colonnes)) {

                    self::cree_colonne_sur_table($type_element, $champ_libre->nom_sql, $donnees['type'], $champ_libre);
                }

                //On modifie les champs libres des vues liés à cette table
                $champs_libres_vues = service('vue_sql')->recuperer_vues_champs($champ_libre);

                foreach($champs_libres_vues as $champ_libre_vue) {

                    service('vue_sql')->gestion_champ_libre($champ_libre_vue->type_element,$champ_libre, array(), $champ_libre_vue);

                }
            }

            if($champ_libre->type == 10){

                $colonnes_table_pivot = \Schema::getColumnListing($type_element.'_'.$champ_libre->nom_sql);

                if(!in_array('valeur', $colonnes_table_pivot))
                    self::cree_colonne_sur_table($type_element, 'valeur', $champ_libre->type_reference, $champ_libre, true);
            }
        }

        

        if(!empty($donnees['creation_table_libre_pivot']))
            Champ_libre_management::creation_table_libre_pivot($champ_libre->table_pivot);

	    $retour = Table_libre_management::generer_fichier_migration($type_element);

		if (!$retour) {
			return 'Le répertoire Migrations n\'est pas  accessible en écriture !';
		}

        if(in_array($champ_libre->liste_choix,array(3,14)))
            Cache_management::genere_valeurs_liste_formatees($champ_libre->liste_choix);

		return true;
	}

	/**
	*
	* Retourne la liste des champs libres d'une table
	*
	*/
	public static function champs_pour_une_table($type_element) {

		$champs = champs_libres($type_element);

		return $champs;
	}

	/**
	*
	* Enregistre une modification d'etat
	*
	*/
	public static function changement_etat($type_element, $id_cl, $valeur, $parametre) {

		$champ_libre = Champ_libre::where('type_element', $type_element)->where('id_cl', $id_cl)->first();
		$champ_libre->$parametre = $valeur;
		$champ_libre->save();

		// On gére la création de l'index
		if($parametre == 'index'){

		    $nom_sql = $champ_libre->nom_sql;

            try{

                $index_existants = DB::select('SELECT index_name FROM information_schema.statistics WHERE table_schema="'.env('DB_DATABASE').'" AND table_name = "'.$type_element.'" AND index_name = "'.$champ_libre->nom_sql.'"');

                if (empty($index_existants) && $valeur == 1) {

                    DB::select('CREATE INDEX `' . $nom_sql . '` ON `' . $type_element . '` (`' . $nom_sql . '`);');

                } else if (!empty($index_existants) && $valeur == 0) {
                    DB::select('ALTER TABLE `' . $type_element . '` DROP INDEX `' . $nom_sql . '`');
                }
            }
            catch (\Exception $e){

                return "Erreur lors de la création de l'index : ".$e;
            }

        }

		$retour = Table_libre_management::generer_fichier_migration($type_element);

		if (!$retour) {

			return 'Le répertoire Migrations n\'est pas  accessible en écriture !';
		}

		return true;
	}

	/**
	*
	* Enregistre une modification d'ordre de la liste de valeur d'un champ libre
	*
	*/

	public static function changement_ordre_champ_libre_liste($id_cl,$ancienne_position,$nouvelle_position) {

		//changement de l'ordre en fonction du déplacement effectué

		if($ancienne_position>$nouvelle_position){

			$champ_libre_liste = Champ_libre_liste::where('id_cl', $id_cl)->whereBetween('ordre', [$nouvelle_position,$ancienne_position])->get();

			foreach($champ_libre_liste as $unchamp_libre_liste) {

				if($unchamp_libre_liste->ordre==$ancienne_position){

					$unchamp_libre_liste->ordre = $nouvelle_position;
					$unchamp_libre_liste->save();

				}
				else{

					$unchamp_libre_liste->ordre = ($unchamp_libre_liste->ordre)+1;
					$unchamp_libre_liste->save();

				}
			}
		}

		else{

			$champ_libre_liste = Champ_libre_liste::where('id_cl', $id_cl)->whereBetween('ordre', [$ancienne_position,$nouvelle_position])->get();

			foreach($champ_libre_liste as $unchamp_libre_liste) {

				if($unchamp_libre_liste->ordre==$ancienne_position){

					$unchamp_libre_liste->ordre = $nouvelle_position;
					$unchamp_libre_liste->save();

				}
				else{

					$unchamp_libre_liste->ordre = ($unchamp_libre_liste->ordre)-1;
					$unchamp_libre_liste->save();
				}
			}
		}

		return true;
	}

    /**
     *
     * Maj les champs libres double en double(8,4)
     *
     */
    public static function maj_champs_libres($champs_libres_par_type_element, $nouveau_format){

        foreach ($champs_libres_par_type_element as $type_element => $champs_libres){

            foreach ($champs_libres as $champ_libre){

                if (\Schema::hasColumn($type_element, $champ_libre['nom_sql']))
                    DB::select('ALTER TABLE `'.$type_element.'` MODIFY COLUMN `'.$champ_libre['nom_sql'].'` '. $nouveau_format .' NULL;');

            }

        }

    }

    /**
     * @param $id_cl
     *
     * Permet de récupérer les liaisons des valeurs des listes libres ou d'une liste libre
     *
     */
    public static function liaisons_valeurs_listes_libres($id_cl = false){

        $valeur_liste_libre_par_index = Champ_libre_liste::get()
                ->pluck('id_valeur','index_traduction')->toArray();

        $champs_libres_lies = Champ_libre::whereNotNull('champ_liste_libre_parent')
            ->whereNotNull('champ_liste_libre_liaisons')
						->where('champ_liste_libre_parent','!=','')
						->where('champ_liste_libre_liaisons','!=','');

        $champ_libre_enfants = Champ_libre::select('ec2.*','eden_champslibres.id_cl AS id_cl_parent','eden_champslibres.liste_choix AS liste_choix_parent')
            ->join('eden_champslibres AS ec2', function($join){
                $join->on('eden_champslibres.nom_sql','=','ec2.champ_liste_libre_parent')
                    ->on('eden_champslibres.type_element','=','ec2.type_element');
            });

        if($id_cl !== false) {
            $champs_libres_lies->where(function ($sous_requete) use ($id_cl) {
                $sous_requete->where(function($requete_liste_choix){
                        $requete_liste_choix->where('liste_choix','=', 0)
                            ->orWhereNull('liste_choix');
                    })
                    ->where('id_cl',$id_cl)
                    ->orWhere('liste_choix', $id_cl);
            });

            $champ_libre_enfants->where(function ($sous_requete) use ($id_cl) {
                $sous_requete->where(function($requete_liste_choix){
                        $requete_liste_choix->where('eden_champslibres.liste_choix','=', 0)
                            ->orWhereNull('eden_champslibres.liste_choix');
                    })
                    ->where('eden_champslibres.id_cl',$id_cl)
                    ->orWhere('eden_champslibres.liste_choix', $id_cl);
            });
        }

        $champs_libres_lies = $champs_libres_lies->get();
        $champ_libre_enfants = $champ_libre_enfants->get();

        $id_cl_par_champ_libre = Champ_libre::select(
                    DB::raw('IF(liste_choix > 0,liste_choix,id_cl) AS id_cl'),
                    DB::raw('CONCAT(type_element,".",nom_sql) AS type_element_nom_sql')
                )
                ->where(function($query) {
                    $query->where('type', 1)
                        ->orWhere(function($query) {
                            $query->where('type', 10)
                                    ->where('type_reference', 1);
                        });
                })
                ->get()
                ->pluck('id_cl','type_element_nom_sql')
                ->toArray();

        $liaisons_globales = array();

        if($id_cl !== false)
            $liaisons_globales[$id_cl] = [];

        foreach($champs_libres_lies as $champ_libre_lie){

            $champ_libre_lie_id_cl = $champ_libre_lie->id_cl;

            if($champ_libre_lie->liste_choix > 0)
                $champ_libre_lie_id_cl = $champ_libre_lie->liste_choix;

            if(!isset($liaisons_globales[$champ_libre_lie_id_cl]))
                $liaisons_globales[$champ_libre_lie_id_cl] = [];

            $liaisons = $liaisons_globales[$champ_libre_lie_id_cl];

            $champ_liste_libre_liaisons = json_decode($champ_libre_lie->champ_liste_libre_liaisons,true);

            $champ_liste_libre_liaisons_organises = array();

            foreach($champ_liste_libre_liaisons as $cle_liaison => &$champ_liste_libre_liaison){

                $cle_liaison = $valeur_liste_libre_par_index[$cle_liaison];

                foreach($champ_liste_libre_liaison as &$valeur_champ_liste_libre_liaison){

                    if(isset($valeur_liste_libre_par_index[$valeur_champ_liste_libre_liaison]))
                        $valeur_champ_liste_libre_liaison = $valeur_liste_libre_par_index[$valeur_champ_liste_libre_liaison];
                }

                $champ_liste_libre_liaisons_organises[$cle_liaison] = $champ_liste_libre_liaison;
            }

            $champ_liste_libre_liaisons = $champ_liste_libre_liaisons_organises;

            if(!isset($liaisons[$champ_libre_lie->type_element]))
                $liaisons[$champ_libre_lie->type_element] = array();

            if(!isset($liaisons[$champ_libre_lie->type_element][$champ_libre_lie->nom_sql]))
                $liaisons[$champ_libre_lie->type_element][$champ_libre_lie->nom_sql] = array();

            $liaisons[$champ_libre_lie->type_element][$champ_libre_lie->nom_sql]['parent'] = array(
                'champ_liste_libre_parent' => $champ_libre_lie->champ_liste_libre_parent,
                'champ_liste_libre_liaisons' => $champ_liste_libre_liaisons,
                'id_cl_parent' => $id_cl_par_champ_libre[$champ_libre_lie->type_element.'.'.$champ_libre_lie->champ_liste_libre_parent]
            );

            $liaisons_globales[$champ_libre_lie_id_cl] = $liaisons;
        }

        foreach($champ_libre_enfants as $champ_libre_enfant){

            $champ_libre_enfant_id_cl = $champ_libre_enfant->id_cl_parent;

            if($champ_libre_enfant->liste_choix_parent > 0)
                $champ_libre_enfant_id_cl = $champ_libre_enfant->liste_choix_parent;

            if(!isset($liaisons_globales[$champ_libre_enfant_id_cl]))
                $liaisons_globales[$champ_libre_enfant_id_cl] = [];

            $liaisons = $liaisons_globales[$champ_libre_enfant_id_cl];

            $champ_liste_libre_liaisons = json_decode($champ_libre_enfant->champ_liste_libre_liaisons,true);

            $champ_liste_libre_liaisons_organises = array();

            foreach($champ_liste_libre_liaisons as $cle_liaison => &$champ_liste_libre_liaison){

                $cle_liaison = $valeur_liste_libre_par_index[$cle_liaison];

                foreach($champ_liste_libre_liaison as &$valeur_champ_liste_libre_liaison){

                    if(isset($valeur_liste_libre_par_index[$valeur_champ_liste_libre_liaison]))
                        $valeur_champ_liste_libre_liaison = $valeur_liste_libre_par_index[$valeur_champ_liste_libre_liaison];
                }

                $champ_liste_libre_liaisons_organises[$cle_liaison] = $champ_liste_libre_liaison;
            }

            $champ_liste_libre_liaisons = $champ_liste_libre_liaisons_organises;

            if(!isset($liaisons[$champ_libre_enfant->type_element]))
                $liaisons[$champ_libre_enfant->type_element] = array();

            if(!isset($liaisons[$champ_libre_enfant->type_element][$champ_libre_enfant->champ_liste_libre_parent]))
                $liaisons[$champ_libre_enfant->type_element][$champ_libre_enfant->champ_liste_libre_parent] = array();

            if(!isset($liaisons[$champ_libre_enfant->type_element][$champ_libre_enfant->champ_liste_libre_parent]['enfants']))
                $liaisons[$champ_libre_enfant->type_element][$champ_libre_enfant->champ_liste_libre_parent]['enfants'] = array();

            $liaisons[$champ_libre_enfant->type_element][$champ_libre_enfant->champ_liste_libre_parent]['enfants'][] = array(
                'champ_liste_libre_enfant' => $champ_libre_enfant->nom_sql,
                'champ_liste_libre_liaisons' => $champ_liste_libre_liaisons
            );

            $liaisons_globales[$champ_libre_enfant_id_cl] = $liaisons;
        }

        if($id_cl !== false)
            return $liaisons_globales[$id_cl];

        return $liaisons_globales;
    }

    public static function creation_table_libre_pivot($table_pivot){

        $champ_libre_table = Champ_libre::where('table_pivot',$table_pivot)->first();

        $table_libre = Table_libre::where('type_element',$table_pivot)->first();

        if(empty($champ_libre_table) || !empty($table_libre))
            return false;

        Table_libre_management::enregistre($table_pivot, array(

            'nom_table' => $table_pivot,
            'element' => $table_pivot,
            'element_pluriel' => $table_pivot,
            'creation_rapide' => null,
            'fiche' => null,
            'disponible_recherche_rapide' => null,
            'table_systeme' => null,
            'vue_sql' => null,
        ), $table_pivot);

        $champs = array(
            [
                'type' => 42,
                'type_element_ajax' => $champ_libre_table->type_element,
                'nom_sql' => 'cle_locale',
                'nom' => 'Clé locale',
            ]
        );

        $champ_valeur = [
            'type' => $champ_libre_table->type_reference,
            'nom_sql' => 'valeur',
            'nom' => 'Valeur'
        ];

        $parametres_communs = ['type_element_ajax','liste_choix','format_champ','contenu','type_fichier'];

        foreach($parametres_communs as $parametre_commun){

            if(isset($champ_libre_table->$parametre_commun))
                $champ_valeur[$parametre_commun] = $champ_libre_table->$parametre_commun;
        }

        if(empty($champ_valeur['liste_choix']) && $champ_libre_table->type_reference == 1)
            $champ_valeur['liste_choix'] = $champ_libre_table->id_cl;

        $champs[] = $champ_valeur;

        foreach($champs as $champ){
            Champ_libre_management::enregistre($table_pivot,$champ);
        }

        return true;
    }

    public static function filtrage($type_element,$modeles_champs_libres){

        $type_par_filtre = Variables::type_filtre_par_type_champ();

         $champs_libres_element = $modeles_champs_libres[$type_element] ?? collect();

         foreach($champs_libres_element as $champ_libre){

            $type = $champ_libre->type == 10 ? $champ_libre->type_reference : $champ_libre->type;
            $champ_libre->type_filtre = $type_par_filtre[$type .'|'.$champ_libre->type_element_ajax] ?? $type_par_filtre[$type] ?? $type_par_filtre[0];
            $champ_libre->index_traduction_type_element = 'tables_libres.'.$champ_libre->type_element.'.nom_table';
         }

         $champs_libres_element = (new Collection([(object)[
             'type' => 42,
             'nom_sql' => 'id',
             'nom' => 'ID',
             'type_element' => $type_element,
             'type_element_ajax' => $type_element,
             'type_filtre' => $type_par_filtre['42|'.$type_element] ?? $type_par_filtre[42] ?? $type_par_filtre[0],
             'index_traduction_type_element' => 'tables_libres.'.$type_element.'.nom_table',
         ]]))->merge($champs_libres_element);

         $champs_libres[] = array(
             'type_element' => $type_element,
             'champs_libres' => $champs_libres_element,
             'index_traduction' => 'tables_libres.'.$type_element.'.nom_table',
         );

         $champs_libres_pour_liaison = $champs_libres_element->where('type', 42)->where('nom_sql','!=','id');

         foreach($champs_libres_pour_liaison as $champ_libre_pour_liaison) {

             $type_element_ajax = $champ_libre_pour_liaison->type_element_ajax;

             $champs_libres_liaisons = $modeles_champs_libres[$type_element_ajax] ?? [];

             foreach($champs_libres_liaisons as $champ_libre){
                $type = $champ_libre->type == 10 ? $champ_libre->type_reference : $champ_libre->type;
                $champ_libre->type_filtre = $type_par_filtre[$type.'|'.$champ_libre->type_element_ajax] ?? $type_par_filtre[$type] ?? $type_par_filtre[0];
                $champ_libre->index_traduction_type_element = 'tables_libres.'.$champ_libre->type_element.'.nom_table';
             }

            $champs_libres_liaisons = (new Collection([(object)[
                'type' => 42,
                'nom_sql' => 'id',
                'nom' => 'ID',
                'type_element' => $type_element_ajax,
                'type_element_ajax' => $type_element_ajax,
                'type_filtre' => $type_par_filtre['42|'.$type_element_ajax] ?? $type_par_filtre[42] ?? $type_par_filtre[0],
                'index_traduction_type_element' => 'tables_libres.'.$type_element_ajax.'.nom_table',
            ]]))->merge($champs_libres_liaisons);

             $champs_libres[] = array(
                 'type_element' => $type_element_ajax,
                 'champs_libres' => $champs_libres_liaisons,
                 'champ_liaison' => $champ_libre_pour_liaison->nom_sql,
                 'index_traduction' => 'tables_libres.'.$type_element_ajax.'.nom_table',
             );
         }

         // Gestion des questionnaires
         $champ_repondant_questionnaire = champs_libres($type_element)
            ->where('type',42)
            ->where('type_element_ajax','questionnaire_element_repondant')
            ->first();

         if(empty($champ_repondant_questionnaire))
            return $champs_libres;

         return array_merge($champs_libres,management('questionnaire')->filtres_questionnaires_questions($champ_repondant_questionnaire));
    }

}
