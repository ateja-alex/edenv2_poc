<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Champs\Champ;
use App\Eden\Champs_libres;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Parametrage\Champ_libre_management;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Liste_libre;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Element_log;
use App\Eden\Models\Element_log_detail;
use App\Eden\Models\Element_image;
use App\Eden\Models\Recurrence;
use App\Eden\Models\Utilisateur;
use App\Eden\Models\Element_piece_jointe;

use App\Eden\Variables;
use DateTime;
use Dompdf\Css\Color;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MongoDB\Driver\Exception\Exception;
use Session;
use Illuminate\Support\Facades\Log;
use DB;
use Schema;
use PDF;
use File;
use Illuminate\Support\Facades\Bus;
use setasign\Fpdi\Fpdi;

class Element_management {

	/*
	 * Le type_element du management
	 */
	public $_type_element = false;


	public $champs_obligatoire_a_retourner = [];

    public $fonction_a_eviter = [];

    public $managements_champs = [];

    public $changement_enregistrement = [];

    public $forcer_affichage = false;

    public $vue_sql_bdd = false;

	public $trigger_lance = [];

	public $modele;

	public $modele_avant;
	
	public $elements_synchronises_service_externe = [];

	/**
	 *
	 * Initialise le management pour le type élément
	 *
	 * @param $type_element string, le type_element concerné
	 * @param $id_element, si $id_element est non fourni, le modèle ne sera pas chargé (c'est par exemple pour utiliser les méthodes du management qui n'ont rien à voir avec un modèle donné)
	 * @param $id_element, si $id_element est un ID, le modèle sera chargé avec cet ID
	 * @param $id_element, si $id_element est un objet, cela doit être le modèle concerné, le modèle sera directement affecté
	 *
	 */
	public function __construct($type_element, $id_element = false, $modele = false) {

		$this->_type_element = $type_element;
		$this->reload_modele($id_element, $modele);

        if($this->droit_acces_element() === false)
            abort(404);
	}

	/**
	*
	* Est ce que le modèle existe ?
	*
	* @return true si oui
	* @return false si non
	*
	*/
	public function existe() {

		if(is_object($this->modele) && !empty($this->modele->id))
			return true;

		return false;
	}

	/**
	 *
	 * Retourne un management champ libre
	 *
	 * @param $nom_sql le nom du champ dans la table
	 * @param $valeur_defaut si on veut charger une valeur par défaut pour ce champ
	 * (si un modèle est lié au management, alors la valeur de ce modèle pour ce champ sera prise en compte quoi qu'il en soit)
	 *
	 * @return une instance management de champ libre
	 *
	 */
	public function champ($nom_sql, $valeur_defaut = false) {

		$champ_libre = champ_libre($this->_type_element, $nom_sql);

		$champ = $champ_libre->champ;

		if($champ === null)
			throw new \App\Eden\Exceptions\Eden_exception(traduction('messages.php.champ_introuvable',null,[$nom_sql,$this->_type_element]));

		if(isset($this->modele) && isset($this->modele->{$nom_sql}))
            $champ->value($this->modele->{$nom_sql});
		elseif (isset($this->modele) && empty($this->modele->{$nom_sql}) && $champ->modele->type == 10)
			$champ->value(array_flip($champ->recupere_valeurs_du_modele($this->modele->id)));
		elseif($valeur_defaut !== false)
			$champ->value($valeur_defaut);
		elseif(isset($champ->modele) && $champ->modele->valeur_defaut !== "" && $champ->modele->valeur_defaut !== null && empty($this->modele))
			$champ->value($this->remplace_variables_modele_par_defaut($champ->modele));

		$champ->name($nom_sql);
		
		return $champ;
	}

	/**
	 *
	 * Permet d'ajouter des conditions particulières pour certains types d'élément pour les recherche
	 *
	 */
	public function conditions_specifiques_recherche($element, $filtrage = false) {

		if($filtrage === false)
            return $element;

        foreach ($filtrage as $index => $options) {

            if ($options['champ'] == 'id_element_pour_transfert') {

                $element_tmp = modele($this->_type_element)->where('id', $options['valeur'])->first();

                if(empty($element_tmp))
                    continue;

                $filtrage['filtrage'][] = array(
                    'champ' => 'entite_id',
                    'condition' => 'where',
                    'valeur' => $element_tmp->entite_id,
                );

                unset($filtrage[$index]);

                continue;
            }

            $champ_libre_modele = champ_libre_modele($this->_type_element,$options['champ']);

            $condition = ucfirst($options['condition']);

            if(in_array($condition, array('WhereIn','WhereNotIn')) && !is_array($options['valeur']))
                $options['valeur'] = is_array($options['valeur']) ? $options['valeur'] : explode(',', $options['valeur']);

            if(!empty($options['condition_ou']) && $options['condition_ou'] != 'false')
                $condition = 'or'.$condition;

            if(!empty($options['symbole']) && in_array($condition, ['Where', 'orWhere']))
                $element = $element->{$condition}($options['champ'],$options['symbole'], $options['valeur']);
			elseif(in_array($condition, array('WhereIn','WhereNotIn')) && in_array(0, $options['valeur']))
				$element = $element->where(function($where) use ($options,$condition) {
                    $where->{$condition}($options['champ'],$options['valeur'])->orWhereNull($options['champ']);
                });
            elseif((!empty($options['valeur']) || (in_array($champ_libre_modele->type,[2,3]) && $options['valeur'] == 0)) && !in_array($condition, array("WhereNull", "WhereNotNull")))
                $element = $element->{$condition}($options['champ'], $options['valeur']);
            elseif($options['valeur'] == 0 && $condition == 'where')
                $element = $element->where(function($where) use ($options) {
                    $where->whereNull($options['champ'])->orWhere($options['champ'],0);
                });
            else
                $element = $element->{$condition}($options['champ']);
        }

		return $element;
	}

	/**
	 *
	 * Regénère les données du modèle après une modification de ce dernier
	 *
	 * @param id_element INT si non fourni, on essaiera de prendre le modèle lié au management s'il existe
	 *
	 * @return void
	 *
	 */
	public function reload_modele($id_element = false, $modele = false) {

		if($id_element === false && !isset($this->modele->id)) {

			$this->modele = null;
			return;
		}

		if($id_element === false)
			$id_element = $this->modele->id;

		if(is_object($id_element)) {

			$this->modele = $id_element;
		}
		elseif($modele !== false) {

			$this->modele = $modele;
		}
		else {

			$this->modele = modele($this->_type_element, $id_element);
		}

		// @note frédéric 19/08/2022 je commente cette ligne, car elle est appelée beaucoup trop de fois pour rien,
		// il faudra appeler la méthode prepare uniquement quand ça sera nécessaire, manuellement
		// $this->prepare();
	}

	/**
	*
	* "Prépare" l'élément : c'est une méthode qui est appelée lorsqu'on charge le management avec un modèle
	* Elle est utilisée par exemple pour charger / calculer des valeurs (l'adresse pour un client par exemple)
	* Elle est prévue pour être surchargée dans les managements spécifiques
	*
	* @return void
	*
	*/
	public function prepare() {

		/**
		@note Frédéric 22/06/2022 je retire le affiche_lien() car au final ce n'est plus utilisé, et on perd bcp en perf à cause de ça.
		*/

		// le lien vers la fiche de l'élément (ou la vue READ)
		// on met une exception pour ne pas ajouter cela sur un élément que l'on n'a pas chargé,
		// par exemple à cause d'un souci de profil
		if(empty($this->modele) || empty($this->modele->id)) {

			return;
		}

		$this->modele->affiche_lien = $this->affiche_lien();
	}

	/**
	 *
	 * Récupère les valeurs des champs multi sélection pour les mettre dans le modèle
	 *
	 */
	public function charge_valeurs_champs_multiselection(&$modele = false) {

		if(!$this->existe())
			return true;

        if($modele === false)
            $modele = $this->modele;

        $champs_libres_multi_selection = champs_libres_multiselection($this->_type_element);

		// on va chercher les valeurs pour les multi sélections
		foreach($champs_libres_multi_selection as $champ_libre) {

            $modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);

            $valeurs_selectionnees = $modele_table_pivot
                                            ->where('cle_locale', $this->modele->id)
                                            ->get()
                                            ->pluck('valeur')
                                            ->toArray();

			$valeurs_pour_vue = array();

			foreach($valeurs_selectionnees as $valeur) {

                $valeurs_pour_vue[] = $valeur;
			}

			$modele->{$champ_libre->nom_sql} = $valeurs_pour_vue;

		}

		return true;
	}

    /**
     *
     * Permet d'enlever les valeurs array des multiselect pour les saves
     *
     */
    public function dechargement_valeurs_multiselection(){

        $champs_libres_multi_selection = champs_libres_multiselection($this->_type_element);

        $this->modele_avec_multiselect = clone $this->modele;

        // on va chercher les valeurs pour les multi sélections
		foreach($champs_libres_multi_selection as $champ_libre) {

            $this->modele->{$champ_libre->nom_sql} = null;
        }
    }

	/**
	*
	* Retire les données préparées pour l'enregistrement
	* (les données qui ne peuvent pas être sauvegardées sur le modèle car elles ne correspondent pas à des colonnes en base)
	*
	* @param $modele l'instance du modèle que l'on s'apprète à enregistrer
	*
	*/
	protected function retire_donnees_preparees(&$modele) {

		unset($modele->affiche_lien);
		unset($modele->prepare_adresse);
		unset($modele->entites);
	}

	/**
	 *
	 * Vérifie les champs obligatoires
	 *
	 * @param $modele le modèle ou on doit vérifier les champs obligatoires
	 * (si le modèle existe, les champs obligatoires ne sont pas obligatoires !)
	 * @param $modifications array, la liste des modifications
	 *
	 * @return true si pas de problème, une erreur (string) sinon
	 *
	 */
	protected function verifie_champs_obligatoires($modele, $modifications) {


		$champs_libres = champs_libres_obligatoires($this->_type_element);

        if(!empty($this->champ_liaison_creation_sous_formulaire)) {
            foreach ($champs_libres as $index => $champ_libre) {

                if ($champ_libre['nom_sql'] == $this->champ_liaison_creation_sous_formulaire)
                    unset($champs_libres[$index]);

            }
        }

		return $this->verifie_champs_obligatoires_avec_champs($modele, $modifications, $champs_libres);
	}


	/**
	 *
	 * Vérifie les si champs doivent être uniques
	 *
	 * @param $modele le modèle ou on doit vérifier les champs obligatoires
	 *
	 * @param $modifications array, la liste des modifications
	 *
	 * @return true si pas de problème, une erreur (string) sinon
	 *
	 */
	protected function verification_champs_uniques($modele, $modifications) {

		$champs_libres = champs_libres_uniques($this->_type_element);

		return $this->verifie_champs_uniques_avec_champs($modele, $modifications, $champs_libres);

	}


	/**
	 *
	 * Vérifie si il y a un changement d'entité
	 *
	 */
	protected function verification_pas_de_changement_entite($modifications) {

		if(!isset($modifications['entite_id']))
			return true;

		if(empty($this->modele) || $this->modele->exists === false)
			return true;

		if($this->modele->entite_id == $modifications['entite_id'])
			return true;

		if(empty($this->modele->entite_id))
			return true;

		if($this->modification_entite_autorisee() === true)
			return true;

		// pas d'entité sur le modèle actuellement
		// c'est le cas ou on rajoute un champ client_id ou entite_id après coup par exemple
		if(empty($this->modele->entite_id))
			return true;

		return traduction('messages.php.element.modification_entite_impossible').' ('.$this->_type_element." ".$this->modele->entite_id." => ".$modifications['entite_id'].")";
	}

	/**
	 *
	 * Vérifie les si champs sont uniques ou uniques par entité
	 *
	 *
	 */
	protected function verifie_champs_uniques_avec_champs($modele, $modifications, $champs_libres) {

		foreach($champs_libres as $champ_libre) {

			if(array_key_exists($champ_libre->nom_sql, $modifications)) {

				$valeur_existe = false;

				if(empty($modifications[$champ_libre->nom_sql]))
					continue;


				if($champ_libre->unique == 1 ) {

					// on est en création
					if($modele->exists === false)
						$valeur_existe = modele($this->_type_element)->where($champ_libre->nom_sql, $modifications[$champ_libre->nom_sql])->exists();
					else
						$valeur_existe = modele($this->_type_element)->where($champ_libre->nom_sql, $modifications[$champ_libre->nom_sql])->where('id', '!=', $modele->id)->exists();


					if($valeur_existe)
						return traduction('messages.php.element.valeur_unique', null, [$champ_libre->nom]);

				}

				if($champ_libre->unique_entite == 1 ) {

					$entite_id = null;
					$modele_unique_entite = false;
					$modifications_unique_entite = false;

					if(isset($this->modele->entite_id)) {
						$entite_id = $this->modele->entite_id;
						$modele_unique_entite = true;
					}

					if(isset($modifications['entite_id'])) {

						$entite_id = $modifications['entite_id'];
						$modifications_unique_entite = true;
					}

					if(is_null($entite_id)) {
						continue;
					}
					else {

						if($modele_unique_entite && $modifications_unique_entite) {

							if($this->modele->entite_id != $modifications['entite_id']) {
								$entite_id = $modifications['entite_id'];
							}
						}
					}

					// on est en création
					if($modele->exists === false)
						$valeur_existe = modele($this->_type_element)->where($champ_libre->nom_sql, $modifications[$champ_libre->nom_sql])->where('entite_id', $entite_id)->exists();
					else
						$valeur_existe = modele($this->_type_element)->where($champ_libre->nom_sql, $modifications[$champ_libre->nom_sql])->where('id', '!=', $modele->id)->where('entite_id', $entite_id)->exists();


					if($valeur_existe)
						return traduction('messages.php.element.valeur_unique', null, [$champ_libre->nom]);

				}
			}
		}

		return true;
	}

	/**
	 *
	 * Vérifie une liste de champs obligatoires donnée
	 *
	 */
	protected function verifie_champs_obligatoires_avec_champs($modele, $modifications, $champs_libres) {

		$champs_obligatoires = array();
		$champs_obligatoires_pour_erreur = array();

		foreach($champs_libres as $champ_libre) {

			if(!is_object($champ_libre))
				dd($champs_libres);

			if(array_key_exists($champ_libre->nom_sql, $modifications)) {

				// if($champ_libre->valeur_vide($modifications)) {
                if($champ_libre->type == 2 || $champ_libre->type == 3 || ($champ_libre->type == 20 && in_array($champ_libre->liste_choix, Variables::liste_formatees_zero_possible()))){

                    if(in_array($modifications[$champ_libre->nom_sql],['',null,false], true)){

                        $champs_obligatoires[$this->_type_element][] = $champ_libre->nom;
                        $champs_obligatoires_pour_erreur[] = $champ_libre->nom;
                        $this->champs_obligatoire_a_retourner[] = $champ_libre->nom_sql;

                    }

                }
				else if(empty($modifications[$champ_libre->nom_sql])) {

					$champs_obligatoires[$this->_type_element][] = $champ_libre->nom;
					$champs_obligatoires_pour_erreur[] = $champ_libre->nom;
					$this->champs_obligatoire_a_retourner[] = $champ_libre->nom_sql;

				}
			}
			else {

				// est on en création ?
				if(!$this->existe()) {

					$champs_obligatoires[$this->_type_element][] = $champ_libre->nom;
					$champs_obligatoires_pour_erreur[] = $champ_libre->nom;
					$this->champs_obligatoire_a_retourner[] = $champ_libre->nom_sql;
				}
				else {

					if(empty($modele->{$champ_libre->nom_sql})) {

                        if($champ_libre->type == 2 || $champ_libre->type == 3 || ($champ_libre->type == 20 && in_array($champ_libre->liste_choix, Variables::liste_formatees_zero_possible()))){

                            if(!in_array($modele->{$champ_libre->nom_sql},['',null], true) && $modele->{$champ_libre->nom_sql} !== false)
                                continue;

                        }

						else if($champ_libre->type == 10){

							// Cas particulié à vérifier
							// Exemple de cas qui pose pb : sur l'aana on a un formulaire fiche client avec un champ obligatoire type 11, sur le modele, le champ est à null car les data sont sur une table pivot. On a également un autre formulaire client sur la fiche client qui lui n'a pas le champ en question. Le code ne regardais que le modèle et non la table pivot.
							// On gère ce cas

                            $modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);

							$verification_champ_table_pivot = $modele_table_pivot->where('cle_locale', $modele->id)->first();

							// il y a une valeur, donc c'est OK
							if($verification_champ_table_pivot != null)
								continue;

							$champs_obligatoires[$this->_type_element][] = $champ_libre->nom;
							$champs_obligatoires_pour_erreur[] = $champ_libre->nom;
							$this->champs_obligatoire_a_retourner[] = $champ_libre->nom_sql;

						}
						else {

							$champs_obligatoires[$this->_type_element][] = $champ_libre->nom;
							$champs_obligatoires_pour_erreur[] = $champ_libre->nom;
							$this->champs_obligatoire_a_retourner[] = $champ_libre->nom_sql;
						}
					}

				}
			}
		}

		if(!empty($champs_obligatoires_pour_erreur)) {

			if(count($champs_obligatoires_pour_erreur) == 1)
				return traduction('messages.php.champ_obligatoire').implode(', ', $champs_obligatoires_pour_erreur);
			else
				return traduction('messages.php.champs_obligatoires').implode(', ', $champs_obligatoires_pour_erreur);

		}

		// pas d'erreur, on continue
		return true;
	}

    /**
     *
     * Vérifie que les champs avec un format spécifique sont bien formatés
     *
     * @param $modele le modèle que l'on va modifier
     * @param $modifications array les modifications appliquées sur le modèle
     *
     * @return true si formatage est bon, une erreur (string) sinon
     *
     */
    public function verifie_formatage_champs($modele, &$modifications){

        $champs_libres_existants = champs_libres($this->_type_element)->keyBy('nom_sql');

        foreach($modifications as $champ => $valeur) {

            if (!isset($champs_libres_existants[$champ]))
                continue;

            $modele_champ_libre = $champs_libres_existants[$champ];

            $format_champ = $modele_champ_libre['format_champ'];

			$valeurs = !is_array($valeur) ? [$valeur] : $valeur;
			
			if(!empty($format_champ))
				$classe_champ = champ($modele_champ_libre);

			foreach($valeurs as $cle => &$valeur_a_verifier){
				// On vérifie que les champs emails sont corrects
				if ($format_champ == 'email' && $valeur_a_verifier != '' && !verifie_email_valide($valeur_a_verifier))
					return traduction('messages.php.element.adresse_email_valide', null, [$modele_champ_libre['nom']]);
				
				if (!empty($modele_champ_libre['doit_etre_plus_petit_que']) &&
					((isset($modifications[$modele_champ_libre['doit_etre_plus_petit_que']]) && $valeur_a_verifier > $modifications[$modele_champ_libre['doit_etre_plus_petit_que']]) ||
						(isset($modele[$modele_champ_libre['doit_etre_plus_petit_que']]) && !isset($modifications[$modele_champ_libre['doit_etre_plus_petit_que']]) &&
							$valeur_a_verifier > $modele[$modele_champ_libre['doit_etre_plus_petit_que']]))){

					return traduction('messages.php.element.doit_etre_plus_petit_que_valide', null, [$modele_champ_libre['nom'], champ_libre($this->_type_element, $modele_champ_libre['doit_etre_plus_petit_que'])->modele->nom]);
				}

				$format_champ_nombres_caractres = array(
					'nic' => 5,
				);

				if (!empty($valeur_a_verifier) && isset($format_champ_nombres_caractres[$format_champ]) && $format_champ_nombres_caractres[$format_champ] != strlen($valeur_a_verifier))
					return traduction('messages.php.element.nombre_de_caracteres_invalide', null, [$modele_champ_libre['nom'],$format_champ_nombres_caractres[$format_champ]]);

				if(isset($classe_champ) && in_array($format_champ, Variables::$formats_champ_texte_verifiables) && !empty($valeur_a_verifier)){
					
					if(in_array($format_champ, ['siren', 'siret']) && is_array($valeur))
						$modifications[$champ][$cle] = str_replace(' ', '', $valeur_a_verifier);
					else if(in_array($format_champ, ['siren', 'siret']))
						$modifications[$champ] = str_replace(' ', '', $valeur_a_verifier);
						
					$classe_champ->value($valeur_a_verifier);
					$retour = $classe_champ->valider();

					if($retour['succes'] == false)
						return $retour['message'];
				}
			}
		}

        return true;
    }

	/**
	*
	* Vérifie les profils pour la modification ou la création
	*
	* @param $modele le modèle que l'on va modifier
	* @param $modficiations array les modifications appliquées sur le modèle
	*
	* @return true si tout va bien, une erreur (string) sinon
	*
	*/
	protected function verifie_profil_enregistrement($modele, $modifications) {

        if(!empty(moi_extranet()))
            return true;

		// par défaut, nous n'avons pas d'entité
		$id_entite = false;

		// on va chercher l'id entité
		if(isset($modifications['entite_id']))
			$id_entite = $modifications['entite_id'];
		elseif(!empty($this->modele) && !empty($this->modele->entite_id))
			$id_entite = $this->modele->entite_id;

		if(!$this->existe())
			$retour = profil_creation($this->_type_element, $id_entite);
		else
			$retour = profil_modification($this->_type_element, $id_entite, $this->modele);

        if(!$retour)
            return "Vous n'avez pas les droits nécessaires pour éditer cet élément.";

		return $retour;
	}

	/**
	*
	* Vérifie les profils pour la suppression
	*
	* @param $modele le modèle que l'on va supprimer
	*
	* @return true si tout va bien, une erreur (string) sinon
	*
	*/
	public function verifie_profil_suppression($modele = false) {

        if(!empty(moi_extranet())){

            $createur = Element_log::where('id_element',$this->modele->id)
                ->where('type_element',$this->_type_element)
                ->where('type_action',1)
                ->where('type_element_modificateur','contact')
                ->where('element_id_modificateur',moi_extranet()->contact_selectionne->id)->first();

            return $createur != null;
        }

        if(empty($modele))
            $modele = $this->modele;

		// par défaut, nous n'avons pas d'entité
		$id_entite = false;

		// on va chercher l'id entité
		if(!empty($modele) && !empty($modele->entite_id))
			$id_entite = $modele->entite_id;

		return profil_suppression($this->_type_element, $id_entite, $modele);
	}

	/**
	 *
	 * Rempli les champs de type numérotation automatique (type 14)
	 *
	 */
	protected function rempli_valeur_numero_automatique($champ, $modele) {

		$format_champ = $champ['format_champ'];

		// on va chercher tous les champs de la table
		$champs_date = Champ_libre::where('type_element', $this->_type_element)->whereIn('type', array(4,5))->get();


		// On traite la date
		foreach($champs_date as $champ_date) {

            if(empty($this->modele->{$champ_date['nom_sql']}) || $this->modele->{$champ_date['nom_sql']} == "0000-00-00" || $this->modele->{$champ_date['nom_sql']} == "0000-00-00 00:00:00")
                continue;

			if (strpos($format_champ,'{'.$champ_date['nom_sql'].'-Y}') !== false) {

				$valeur_date = date("Y", strtotime($this->modele->{$champ_date['nom_sql']}));
				$index_date = '{'.$champ_date['nom_sql'].'-Y}';

				// on met la bonne date
				$format_champ = str_replace($index_date,$valeur_date , $format_champ);
			}

			elseif (strpos($format_champ,'{'.$champ_date['nom_sql'].'-y}') !== false) {

				$valeur_date = date("y", strtotime($this->modele->{$champ_date['nom_sql']}));
				$index_date = '{'.$champ_date['nom_sql'].'-y}';

				// on met la bonne date
				$format_champ = str_replace($index_date,$valeur_date , $format_champ);
			}

			elseif (strpos($format_champ,'{'.$champ_date['nom_sql'].'-Ym}') !== false) {

				$valeur_date = date("Y-m", strtotime($this->modele->{$champ_date['nom_sql']}));
				$index_date = '{'.$champ_date['nom_sql'].'-Ym}';

				// on met la bonne date
				$format_champ = str_replace($index_date,$valeur_date , $format_champ);
			}

			elseif (strpos($format_champ,'{'.$champ_date['nom_sql'].'-Ymd}') !== false) {

				$valeur_date = date("Y-m-d", strtotime($this->modele->{$champ_date['nom_sql']}));
				$index_date = '{'.$champ_date['nom_sql'].'-Ymd}';

				// on met la bonne date
				$format_champ = str_replace($index_date,$valeur_date , $format_champ);
			}

			elseif (strpos($format_champ,'{'.$champ_date['nom_sql'].'-dmY}') !== false) {

				$valeur_date = date("d-m-Y", strtotime($this->modele->{$champ_date['nom_sql']}));
				$index_date = '{'.$champ_date['nom_sql'].'-dmY}';

				// on met la bonne date
				$format_champ = str_replace($index_date,$valeur_date , $format_champ);
			}

			elseif (strpos($format_champ,'{'.$champ_date['nom_sql'].'-mY}') !== false) {

				$valeur_date = date("m-Y", strtotime($this->modele->{$champ_date['nom_sql']}));
				$index_date = '{'.$champ_date['nom_sql'].'-mY}';

				// on met la bonne date
				$format_champ = str_replace($index_date, $valeur_date , $format_champ);
			}
		}



		// on retire le break
		$format_champ = str_replace('{break}','' , $format_champ);

		// On traite le numéro
		list($chaine_avant_numero) = explode('{numero}', $format_champ);

		$count_numero = modele($this->_type_element)
            ->avec_inactifs()
            ->sans_profils()
            ->where($champ['nom_sql'], 'LIKE', $chaine_avant_numero.'%')->count();
		$numero_element = $count_numero+1;

		$numero_element = substr('000000'.$numero_element, -6);

		$format_champ = str_replace('{numero}',$numero_element , $format_champ);

		// On enregistre le nouveau champ de numérotation automatique
		// $modele[$champ['nom_sql']] = $format_champ;

        // Si on a pas remplacé une valeur, on vide le tout
        if(strpos($format_champ, '{') !== false && strpos($format_champ,'}') !== false)
            $format_champ = '';

        $this->enregistre_modele(array($champ['nom_sql'] => $format_champ));
	}

	/**
	 *
	 * Trigger post création ou modification
	 *
	 * @note pour les documents de gestion commerciale il faut appeler methodes_post_modification_document()
	 * Cette méthode est appelée après l'enregistrement des articles, du pdf et de la référence document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		$compteur = 0;
        if(!in_array('trigger_applicatif',$this->fonction_a_eviter))
            $this->trigger_applicatif();

		// gestion des workflows
		if(empty($modele_avant) || empty($modele_avant->id))
			$this->gestion_workflow(1, $modele, $modele_avant, $modifications);
		else
			$this->gestion_workflow(2, $modele, $modele_avant, $modifications);

		// gestion des indicateurs
		$this->gestion_indicateurs();

		$type_element = $this->_type_element;


		if($type_element != "log_page") {

			$champs_libres_numerotation_automatique = champs_libres_numerotation_automatique($type_element);

			// On a au moins un champ de type numérotation automatique
			if($champs_libres_numerotation_automatique->count() > 0) {

				// On boucle sur le ou les type 14
				foreach($champs_libres_numerotation_automatique as $numero) {

					$nom_sql_numero_auto = $numero['nom_sql'];

					// Si la numérotation automatique est vide
					if(empty($modele->$nom_sql_numero_auto)) {

						$this->rempli_valeur_numero_automatique($numero, $modele);
					}

				}
			}
		}

		// gestion des notifications
        if($this->_type_element !== 'notification')
		    $this->notifications($modele, $modele_avant, $modifications);

        $calendrier_synchro = calendrier_synchro($this->_type_element);

		foreach ($calendrier_synchro as $synchro) {

			$champ_date_debut = $synchro->champ_date_debut;
			$champ_date_fin   = $synchro->champ_date_fin;

			$element_parent   = (!empty($synchro->type_element_parent) ? $synchro->type_element_parent : $this->_type_element);

			$champ_element_parent   = (!empty($synchro->champ_element_parent) ? $synchro->champ_element_parent : 'id');

			// On supprime l'élément si il existe
			modele('calendrier_evenement')
						->where('synchro_element_id', $this->modele->id)
						->where('synchro_type_element', $this->_type_element)
						->delete();

			$date_debut = $this->modele->$champ_date_debut;
			$date_fin = $this->modele->$champ_date_fin;

			if(empty($date_fin))
				$date_fin = $date_debut;

			if(empty($date_fin) || empty($date_debut))
				continue;

			$modifications = [
							'type_element' 				=> $element_parent,
							'element_id' 				=> $this->modele->$champ_element_parent,
							'date_debut' 				=> $date_debut,
							'date_fin' 					=> $date_fin,
							'nom' 						=> $this->affiche(),
							'synchro_element_id' 		=> $this->modele->id,
							'synchro_type_element' 		=> $this->_type_element,
						];

			// Si la date de début est la même que la date de fin, on rajoute une seconde
			if($modifications['date_debut'] == $modifications['date_fin'])
				$modifications['date_fin'] = date('Y-m-d H:i:s', strtotime($modifications['date_debut'].' +1 second'));

			// On crée l'élément
			management('calendrier_evenement')->enregistre($modifications);
		}

        //On initialise les champs de traductions
        $traduction_service = service('traduction');

        $informations_type_element = $traduction_service->informations_type_element($this->_type_element);

        if(!empty($informations_type_element)) {

            $base_traduction = $this->_type_element.'.'.$modele->id;

            $index_traductions = modele('traduction_index')
                ->where('index','Like',$base_traduction.'.%')
                ->get()->pluck('index')->toArray();

            $champs_traduits = array();

            foreach ($informations_type_element['champs'] as $champ) {

                if(!in_array($base_traduction.'.'.$champ,$index_traductions))
                    $champs_traduits[$champ] = $modele->getOriginal($champ);
            }

            if(!empty($champs_traduits)) {
                $traduction_service->calcul_index_traduction(
                    $informations_type_element['categorie'],
                    array(
                        $this->_type_element,
                        $modele->id
                    ),
                    $champs_traduits
                );
            }
        }

		$this->generation_modele_pdf();

		if(!in_array('synchronisation_service_externe',$this->fonction_a_eviter))
			$this->synchronisation_service_externe($modele_avant, $modifications);
	}

	public function notifications($modele, $modele_avant, $modifications) {

		if(!fonctionnalite('notifications') || in_array('notifications',$this->fonction_a_eviter))
			return;

		// on récupère les utilisateurs abonnés
		$utilisateurs = modele('notification_element')->where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->get();

		foreach($utilisateurs as $utilisateur) {

			if(moi() !== null) {

				if(moi()->id == $utilisateur->utilisateur_id)
					continue;

				$message = moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. vient de <b>modifier un élément</b> : '.$this->affiche_lien();
			}
			else {

				$message = 'Un élément que vous suivez vient d\'être <b>modifié</b> : '.$this->affiche_lien();
			}

			$notification = management('notification');

			if($this->_type_element == 'suivi_recette_easydev_echange'){

				$info = array(

					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => $message,
					'type_element' => 'suivi_recette_easydev',
					'element_id' => $this->modele->suivi_recette,
				);

			}
			else {
				$info = array(

					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => $message,
				);
			}

			$notification->enregistre($info);
		}

		// on envoie une notification si il y a une @mention
		$utilisateurs = false;

		$champs_textarea = champs_libres_textarea($this->_type_element);
		foreach($champs_textarea as $champ) {

			if(
					// Si le champ existe
					isset($modifications[$champ->nom_sql]) &&

					// Si le champ contient un @
					strpos($modifications[$champ->nom_sql], '@') !== false &&

					// Si la modification est différente de la valeur précédente
					$modifications[$champ->nom_sql] != $modele_avant->{$champ->nom_sql}
				) {

				// On vérifie pour tous les utilisateurs
				if($utilisateurs === false)
					$utilisateurs = modele('utilisateur')->get();

				foreach($utilisateurs as $utilisateur) {

					// On a trouvé une mention
					$nom_utilisateur_tag = retraite_caracteres_speciaux($utilisateur->prenom.$utilisateur->nom);
					if(strpos($modifications[$champ->nom_sql], '@'.$nom_utilisateur_tag) !== false) {

						$message = service('notifications')->texte_notification_mention();

						if($this->_type_element == "message" && (table_libre($this->modele->type_element)->fiche == 1 || in_array($this->modele->type_element, Variables::$documents_gescom))){

                            $management_element_concerne = management($this->modele->type_element,$this->modele->element_id);
                            $message .= $management_element_concerne->affiche_lien(strip_tags($management_element_concerne->affiche())).'<br/>';
                        }
						else if(empty($this->affiche()))
							$message .= $this->affiche_lien('ici').'<br/>';
						else
							$message .= $this->affiche_lien().'<br/>';

						$message .= $this->champ($champ->nom_sql)->affiche();

						$notification = management('notification');

						if($this->_type_element == 'suivi_recette_easydev_echange'){

							$info = array(

								'date' => date('Y-m-d H:i:s'),
								'utilisateur_id' => $utilisateur->id,
								'zone' => 'navbar_notifications',
								'contenu_html' => $message,
								'type_element' => 'suivi_recette_easydev',
								'element_id' => $this->modele->suivi_recette,
							);

						}
						else {
							$info = array(

								'date' => date('Y-m-d H:i:s'),
								'utilisateur_id' => $utilisateur->id,
								'zone' => 'navbar_notifications',
								'contenu_html' => $message,
							);
						}

						$notification->enregistre($info);
					}
				}
			}
		}

		$this->notifications_elements_parents($modele, $modele_avant, $modifications);
	}

	/**
	 *
	 * Regarde s'il est nécessaire de notifier des utilisateurs pour des modifications sur des éléments
	 *
	 * Cette méthode doit être surchargée, par exemple dans document management pour les utilisateurs abonnés au client ou au fournisseur
	 *
	 */
	protected function notifications_elements_parents($modele, $modele_avant, $modifications) {

	}

	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

        $traduction_service = service('traduction');

        $informations_type_element = $traduction_service->informations_type_element($this->_type_element);

        if(!empty($informations_type_element))
            $traduction_service->supprime_index_traduction($this->_type_element.'.'.$modele->id);

		// gestion des workflows
		$this->gestion_workflow(3);

        if(!in_array('trigger_applicatif',$this->fonction_a_eviter))
            $this->trigger_applicatif();

		$this->synchronisation_service_externe($modele, [], true);
	}

	/**
	 *
	 * Gestion des différents workflows possibles
	 *
	 */
	protected function gestion_workflow($trigger, $modele = false, $modele_avant = false, $modifications = array()) {

		$workflows = workflows($this->_type_element)->where('trigger', $trigger);

		foreach($workflows as $workflow) {

			management('workflow', $workflow->id, $workflow)->execute($this, $modele, $modele_avant, $modifications);
		}
	}

	/**
	 *
	 * Gestion des différents indicateurs possibles
	 *
	 */
	protected function gestion_indicateurs() {

		$indicateurs = service('indicateurs')->indicateurs($this->_type_element);

		if(!empty($indicateurs)) {

			foreach($indicateurs as $indicateur) {

				$indicateur = indicateur($indicateur);

				$indicateur->calcule($this);
			}
		}
	}

	/**
	*
	* Enregistre les logs pour la création et modification (basés sur les champs libres)
	* @note cette méthode est appelée par enregistre() mais pas par enregistre_modele()
	*
	* @param $modele le modèle que l'on a modifié
	* @param $modele_avant le modèle tel qu'il était avant la modification
	* @param $modifications array, la liste des modifications apportées
	*
	* @return void
	*
	*/
	protected function log_modifications($modele, $modele_avant, $modifications) {

		// on va chercher le type de log
		if($modele_avant->exists === true) {

            if ($modele->exists === true)
                $type_log = Variables::$types_logs['modification'];
			else
                $type_log = Variables::$types_logs['suppression'];
		}
		else
			$type_log = Variables::$types_logs['creation'];

		// on va chercher la liste des modifications
		$modifications_a_loguer = array();

        $champs_libres = champs_libres($this->_type_element)->keyBy('nom_sql');

		foreach($modifications as $champ => $valeur) {

            $modele_champ = $champs_libres[$champ];

            if(in_array($modele_champ->type, array(4,5))){

                if(!$modele_avant->exists || strtotime($modele_avant->$champ) != strtotime($valeur)){
                
					$this->changement_enregistrement[$champ] = $modifications[$champ];

					if($modele_champ->ne_pas_loguer)
						continue;

					$modifications_a_loguer[] = $champ;
				}
            }
			elseif(!$modele_avant->exists || $modele_avant->$champ != $valeur) {

				$this->changement_enregistrement[$champ] = $modifications[$champ];

				if($modele_champ->ne_pas_loguer)
					continue;

                $modifications_a_loguer[] = $champ;
            }
		}

		// il n'y a rien à loguer
		if(empty($modifications_a_loguer) || in_array('valide', $modifications_a_loguer) || in_array('regle', $modifications_a_loguer))
			return;

		// on enregistre le log parent
         $log = $this->enregistrer_log($type_log);

		if($type_log == Variables::$types_logs['creation'] || $type_log == Variables::$types_logs['modification']) {

            $logs = array();

            // on enregistre les logs détails
        	foreach($modifications_a_loguer as $champ) {

                $modele_champ = $champs_libres[$champ];

                $valeurs_avant_txt = [];
                $valeurs_apres_txt = [];

				$management_champ = $this->champ($champ);

				$valeurs_avant_txt = !$modele_avant->exists ? null : $management_champ->affiche($modele_avant->$champ);
				$valeurs_apres_txt = $management_champ->affiche($modifications[$champ]);

				if($modele_champ->type == 10){
					$valeur_avant = !$modele_avant->exists ? null : json_encode($modele_avant->$champ);
					$valeur_apres = json_encode($modifications[$champ]);
				}
				else{
					$valeur_avant = !$modele_avant->exists ? null : $modele_avant->$champ;
					$valeur_apres = $modifications[$champ];
				}
				
        		$donnees = array(
                    'id_element_log' => $log->id_element_log,
					'champ' => $champ,
					'valeur_avant' => null,
					'valeur_apres' => null,
                    'valeur_avant_txt' => null,
                    'valeur_apres_txt' => null,
				);

				if(!empty($valeurs_avant_txt)) {
					$donnees['valeur_avant_txt'] = $valeurs_avant_txt;
				}
				if(!empty($valeurs_apres_txt)) {
					$donnees['valeur_apres_txt'] = $valeurs_apres_txt;
				}
				if(!empty($valeur_avant)) {
					$donnees['valeur_avant'] = $valeur_avant;
				}
				if(!empty($valeur_apres)) {
					$donnees['valeur_apres'] = $valeur_apres;
				}

                if(buffer_sql()->est_actif())
                    buffer_sql()->insert('element_log_detail', $donnees);

				$logs[] = $donnees;

        	}

            if(!buffer_sql()->est_actif())
                Element_log_detail::insert($logs);

        }
	}

	/**
	*
	* Cherche dans un tableau les valeurs qui sont à utiliser pour une modification (typiquement lorsqu'on poste le formulaire de création)
	*
	* @param $formulaire array
	*
	*/
	public function recupere_donnees_pour_modification($formulaire) {

		$modifications = array();

		$table_libre = table_libre($this->_type_element);
		$champs_libres = $table_libre->champs_libres()->get();

		foreach($champs_libres as $champ_libre) {

			if(isset($formulaire[$champ_libre->nom_sql]))
				$modifications[$champ_libre->nom_sql] = $formulaire[$champ_libre->nom_sql];
		}

		return $modifications;
	}

	/**
	 *
	 * Récupère les informations de recherche via les champs libres recherche,
	 * Il est possible de surcharger cette méthode pour ajouter des informations arbitrairement par exemple
	 *
	 */
	protected function recupere_informations_pour_index_recherche(&$sql_set, &$sql_join, &$table_join_count) {}

	/**
	 *
	 * On gère les champs de type pièce jointe
	 *
	 */
	protected function gere_champs_pieces_jointes($modifications) {

		// pour le moment cette fonction n'est plus utilisée,
		// on doit passer par le composant vue piece-jointe
		return $modifications;

		// on gère les suppressions
		$champs_pj = champs_libres_pj($this->_type_element);

		foreach($champs_pj as $champ) {

			if(isset($modifications[$champ->nom_sql.'_supprimer_fichier']) && $modifications[$champ->nom_sql.'_supprimer_fichier'] == 1) {

				unset($modifications[$champ->nom_sql.'_supprimer_fichier']);
				$modifications[$champ->nom_sql] = '';

				continue;
			}

			// le champ n'a pas été rempli, on ne fait rien
			if(!isset($modifications[$champ->nom_sql]))
				continue;

			// on doit donc gérer l'upload sur aws s3
			$chemin_enregistrement = $modifications[$champ->nom_sql]->store('uploads', 's3');

			$modifications[$champ->nom_sql] = json_encode(array('chemin' => $chemin_enregistrement, 'nom' => $modifications[$champ->nom_sql]->getClientOriginalName()));
		}


		return $modifications;
	}

	/**
	 *
	 * On gère les champs de type sous_formulaires
	 *
	 */
	protected function gere_champs_sous_formulaires($modifications) {

		// on regarde s'il y a des sous formulaires pour l'élément actuel
		$sous_formulaires = array();

		$champ_sous_formulaires = champs_libres_sous_formulaire($this->_type_element);

		if($champ_sous_formulaires->count() == 0)
			return array($modifications, $sous_formulaires);

		foreach($champ_sous_formulaires as $champ_sous_formulaire) {

			if(isset($modifications[$champ_sous_formulaire->nom_sql])) {

				$sous_formulaires[] = array(

					'type_element' => $champ_sous_formulaire->type_element_ajax,
					'modifications' => $modifications[$champ_sous_formulaire->nom_sql],
					'colonne_source' => $champ_sous_formulaire->colonne_source,
				);

				unset($modifications[$champ_sous_formulaire->nom_sql]);
			}
		}

		return array($modifications, $sous_formulaires);
	}

	/**
	 *
	 * On gère la création d'éléments pour les sous formulaires
	 *
	 */
	protected function creation_elements_sous_formulaires($sous_formulaires) {

		foreach($sous_formulaires as $sous_formulaire) {

			$element = management($sous_formulaire['type_element']);

			$modifications = $sous_formulaire['modifications'];

			$modifications[$sous_formulaire['colonne_source']] = $this->modele->id;

			$retour = $element->enregistre($modifications);

			// comment gérer l'erreur ?
			// créer une notification ?
			/**
			 *
			 * @todo
			 *
			 */
		}

		// ça serait bien de trouver une méthode pour gérer les erreurs
		return true;
	}


	/**
	 *
	 * Retraite le tableau $modifications en fonction des règles de gestion des éléments
	 * Cette méthode est prévue pour être utilisée en surcharge
	 *
	 * @param $modifications le tableau des modifications qui seront enregistrées
	 *
	 * @return $modifications ce même tableau mis à jour
	 *
	 */
	protected function retraite_modifications($modifications) {

		// on retire les champs du type "_recherche" à cause des sélections ajax d'éléments
		// on se base sur la liste des champs libres et on cherche nom_sql + '_recherche'
		$champs = Champ_libre_management::champs_pour_une_table($this->_type_element);

		// on ajoute le champ entite_id
		if(!empty($modifications['client_id'])) {

			$client = management('client', $modifications['client_id']);

            if(empty($modifications['entite_id']) && !empty($client->modele->entite_id))
			    $modifications['entite_id'] = $client->modele->entite_id;
		}

        if($this->_type_element != 'profil_droits_element') {

            // on voit si on peut / doit ajouter le champ entite_id automatiquement
            $champ_entite = false;

            foreach ($champs as $champ) {

                if ($champ->nom_sql == 'entite_id' && $champ_entite === false)
                    $champ_entite = true;

                if ($champ->type == 42 && isset($modifications[$champ->nom_sql]) && empty($modifications[$champ->nom_sql]))
                    $modifications[$champ->nom_sql] = null;
            }

            if ($champ_entite === true && empty($modifications['entite_id']) && (empty($this->modele) || (!empty($this->modele) && empty($this->modele->entite_id)))) {

                // est ce qu'on a une seule entité existente ?
                $nombre_entites = modele('entite')->count();

                if ($nombre_entites == 1) {

                    $entite = modele('entite')->first();

                    $modifications['entite_id'] = $entite->id;
                }
            }
        }

		// on regarde via le fournisseur éventuellement
		if(isset($modifications['fournisseur_id']) && empty($modifications['entite_id'])) {

			if(empty($modifications['client_id']) && (empty($this->modele) || (!empty($this->modele) && empty($this->modele->client_id)))) {

				// on vérifie s'il y a bien une colonne entite_id
				$colonnes = Schema::getColumnListing($this->_type_element);

				if(in_array('entite_id', $colonnes)) {

					$fournisseur = management('fournisseur', $modifications['fournisseur_id']);

					$modifications['entite_id'] = $fournisseur->modele->entite_id;
				}
			}

		}

		$profil_id = null;

		if(!is_null(moi()))
			$profil_id = moi()->profil_id;


		// si profil_id = null ? connexion e-commerce que faire ?
		foreach($champs as $champ) {

			if(isset($modifications[$champ->nom_sql.'_recherche']))
				unset($modifications[$champ->nom_sql.'_recherche']);

			if(isset($modifications[$champ->nom_sql.'_supprimer_fichier']))
				unset($modifications[$champ->nom_sql.'_supprimer_fichier']);

		}

		// on affecte les valeurs par défaut en création
		$modifications = $this->affecte_valeurs_par_defaut_en_creation($modifications, $champs);

        if(!empty($this->modele)){

            $this->charge_valeurs_champs_multiselection();

            $modifications_a_sauvergarder = [];

            foreach($modifications as $champ => $modification){

                if($modification !== $this->modele->{$champ})
                    $modifications_a_sauvergarder[$champ] = $modification;
            }

            $modifications = $modifications_a_sauvergarder;

            $this->dechargement_valeurs_multiselection();
        }
        
		return $modifications;
	}

	/**
	 *
	 * Lors de la création d'un élément, si la valeur d'un champ n'est pas transmise mais qu'il y a une valeur par défaut,
	 * Alors on applique la valeur par défaut
	 *
	 */
	public function affecte_valeurs_par_defaut_en_creation($modifications, $champs) {

		// ne s'applique pas en modification
		if($this->existe())
			return $modifications;

		foreach($champs as $champ) {

			if(!empty($modifications[$champ->nom_sql]) || ($champ->type == 10 && array_key_exists($champ->nom_sql,$modifications)) || ($champ->type === 20 && isset($modifications[$champ->nom_sql]) && ($modifications[$champ->nom_sql] === 0 || $modifications[$champ->nom_sql] === "0")))
                continue;

			if(empty($champ->valeur_defaut))
				continue;

			$modifications[$champ->nom_sql] = $this->remplace_variables_modele_par_defaut($champ);
		}

		return $modifications;
	}

	/**
	 *
	 * Comment doit on présenter les résultats de la recherche AJAX
	 *
	 */
	public function affichage_pour_recherche_ajax() {

		return strip_tags($this->affiche());
	}

	/**
	 *
	 * Affichage pour les champs select
	 *
	 */
	public function affichage_pour_select() {

        $table_libre = table_libre($this->_type_element);

        if(!empty($table_libre->affichage_pour_select))
		    return $this->recupere_texte_a_afficher($table_libre->affichage_pour_select);
        else
            return $this->affichage_pour_recherche_ajax();

	}

	/**
	 *
	 * Test l'enregistrement un élément
	 *
	 * @param $modele le modèle en question
	 * @param $modifications un tableau avec les champs à modifier (similaire à create() de laravel)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 */
	public function test_enregistre($modifications = array(), $modele = false) {

		$this->test_enregistrement = true;

		return $this->enregistre($modifications, $modele);
	}

	/**
	 *
	 * Enregistre un élément
	 *
	 * @param $modele le modèle en question
	 * @param $modifications un tableau avec les champs à modifier (similaire à create() de laravel)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        // si pas de modèle fourni, on prend le modèle de base
		if($modele === false) {

			if(isset($this->modele) && $this->modele->exists === true)
				$modele = $this->modele;
			else {

				$this->modele = modele($this->_type_element);
				$modele = $this->modele;
			}
		}
		
		$this->changement_enregistrement = [];

        $liste_sous_formulaire = $this->retourne_sous_formulaire();

        //On test l'enregistrement des sous_formulaire pour retourner une erreur si un sous_formulaire n'est pas correct
        $retour_sous_formulaire = $this->enregistre_sous_formulaire($modifications, $liste_sous_formulaire, true);

        if($retour_sous_formulaire['retour'] !== true)
            return $retour_sous_formulaire['retour'];

        // avant tout, on gère les champs de type pièce jointe...
        if(!in_array('gere_champs_pieces_jointes',$this->fonction_a_eviter))
		    $modifications = $this->gere_champs_pieces_jointes($modifications);

		// avant tout, on gère les champs de type pièce jointe...
        if(!in_array('gere_champs_sous_formulaires',$this->fonction_a_eviter))
		    list($modifications, $sous_formulaires) = $this->gere_champs_sous_formulaires($modifications);

		// 0)  on retraite le tableau $modifications en fonction des règles de gestion
        if(!in_array('retraite_modifications',$this->fonction_a_eviter)) {

            $modifications = $this->retraite_modifications($modifications);

            if (!is_array($modifications))
                return $modifications;
        }

        if(!in_array('verification_champs_uniques',$this->fonction_a_eviter)) {

            $erreur = $this->verification_champs_uniques($modele, $modifications);

            if ($erreur !== true)
                return $erreur;
        }

        if(!in_array('verifie_formatage_champs',$this->fonction_a_eviter)) {

            // 1) on vérifie les champs obligatoires
            $erreur = $this->verifie_formatage_champs($modele, $modifications);

            if ($erreur !== true)
                return $erreur;
        }

        if(!in_array('verifie_champs_obligatoires',$this->fonction_a_eviter)) {

            // 1) on vérifie les champs obligatoires
            $erreur = $this->verifie_champs_obligatoires($modele, $modifications);

            if ($erreur !== true)
                return $erreur;
        }

        // dans le cas ou on décider d'enregistrer un élémant sans se soucier des profils
        if(!in_array('verifie_profil_enregistrement',$this->fonction_a_eviter)) {

			// 2) on vérifie les profils
			$erreur = $this->verifie_profil_enregistrement($modele, $modifications);

			if($erreur !== true)
				return $erreur;
		}

		// 2) on vérifie qu'il n'y a pas de changement d'entité
        if(!in_array('verification_pas_de_changement_entite',$this->fonction_a_eviter)) {

            $erreur = $this->verification_pas_de_changement_entite($modifications);

            if ($erreur !== true)
                return $erreur;
        }

        // Si on est en mode test, on renvoie "succes test"
		if(isset($this->test_enregistrement) && $this->test_enregistrement === true)
			return 'test_ok' ;

        if(isset($modifications['conversion_id_element'], $modifications['conversion_type_element']))
            $this->management_conversion = management($modifications['conversion_type_element'], $modifications['conversion_id_element']);


		// si on arrive là, c'est normalement que l'enregistrement ira jusqu'au bout, on peut loguer
		// 3) on enregistre
		$this->modele_avant = clone($modele);

        $this->charge_valeurs_champs_multiselection($this->modele_avant);

		$selections_multiples = array();

		$champs_tableau = array();

		// on va chercher tous les champs de la table
		$this->retire_modifications_sans_champs_libres($modifications, $modele, $selections_multiples, $champs_tableau);

		$modele->modifie_le = date('Y-m-d H:i:s');

		$modele->modifie_par = id_utilisateur_systeme();

		if(defined('id_utilisateur') && !empty(id_utilisateur))
			$modele->modifie_par = id_utilisateur;
        elseif(!empty(moi()))
			$modele->modifie_par = moi()->id;

		if($modele->exists === false) {

			$modele->cree_le = date('Y-m-d H:i:s');

			if(empty($modifications["cree_par"])){
				$modele->cree_par = id_utilisateur_systeme();

				if(defined('id_utilisateur') && !empty(id_utilisateur))
					$modele->cree_par = id_utilisateur;
				elseif(!empty(moi()))
					$modele->cree_par = moi()->id;
			}
		}

		/**
		@todo trouver une manière plus propre & dynamique de le faire
		*/
		// les données préparées
        if(!in_array('retire_donnees_preparees',$this->fonction_a_eviter))
		    $this->retire_donnees_preparees($modele);

		// c'est pour forcer le fait de regénérer la chaine de recherche
		// @todo cette partie pourrait être optimisée en vérifiant si ce traitement est nécessaire...
		// 22/06/2022, Frédéric : pour des questions de perf,
		// et vu que quoi qu'il en soit, on repasse dans affiche() après l'enregistrement
		// et si ce n'est pas le cas, ça permet de tout de suitre mettre "" dans chaine_affichage
		// ainsi on ne repassera pas dans affiche() et on économise un save()

        $table_libre = table_libre($this->_type_element);

		if(!empty($table_libre->affichage_dans_liste))
			$modele->chaine_affichage = null;
		else
			$modele->chaine_affichage = '';

		$modele->save();

        if(isset($this->management_conversion))
            $this->management_conversion->enregistrer_log(Variables::$types_logs['conversion'], null, [$this->_type_element => $modele->id]);

		log_eden("Element_management::On a enregistré ".$this->_type_element.", ".$modele->id, 2);

		// on met à jour les tables liées aux champs selection multiple
		if(!empty($selections_multiples)) {

			foreach($selections_multiples as $table_pour_selection_multiple => $donnees) {

				// On récupère les données, pour log des modifications
                if(table_libre_existe($table_pour_selection_multiple)){

                    $elements_pour_gestion_element = modele($table_pour_selection_multiple)
                        ->where('cle_locale', $modele->id)->get();

                    if (is_array($donnees)) {

                        foreach ($donnees as $donnee) {

                            if ($donnee == '')
                                continue;

                            $cle_multiselect = $elements_pour_gestion_element->where('valeur',$donnee)->keys()->first();

                            if($cle_multiselect !== null){
                                unset($elements_pour_gestion_element[$cle_multiselect]);
                                continue;
                            }

                            management($table_pour_selection_multiple)->enregistre([
                                'cle_locale' => $this->modele->id,
                                'valeur' => $donnee
                            ]);
                        }
                    }

                    foreach($elements_pour_gestion_element as $element_gestion_element){

                        management($table_pour_selection_multiple,$element_gestion_element->id,$element_gestion_element)->supprime();
                    }
                }
                else {

                    // on supprime toutes les données
                    DB::select("DELETE FROM $table_pour_selection_multiple WHERE cle_locale = '" . $modele->id . "'");

                    if (is_array($donnees)) {

                        foreach ($donnees as $donnee) {

                            if ($donnee == '')
                                continue;

                            DB::insert('INSERT INTO ' . $table_pour_selection_multiple . ' (cle_locale, valeur) values (?, ?)', [$modele->id, $donnee]);
                        }
                    }
                }
            }
		}
		$this->modele = $modele;

        $this->gestion_champs_tableau($champs_tableau);

        // 4) on logue les modifications
        if(!in_array('log_modifications',$this->fonction_a_eviter) && !empty($modifications) && empty($table_libre->non_logue)) {
            $this->log_modifications($modele, $this->modele_avant, $modifications);
        }
        else if(in_array('log_modifications',$this->fonction_a_eviter) && empty($table_libre->non_logue))
            $this->informations_logs = [
                'modele_avant' => clone $this->modele_avant,
                'modifications' => $modifications,
            ];

        // 5) on appelle les méthodes post modification
        if(!in_array('methodes_post_modification',$this->fonction_a_eviter))
		    $this->methodes_post_modification($modele, $this->modele_avant, $modifications);

         // pour regénérer la chaine d'affichage de l'élément
        if(!in_array('affiche',$this->fonction_a_eviter)) {

            $this->charge_valeurs_champs_multiselection();
		    $this->affiche();
            $this->dechargement_valeurs_multiselection();
		}

        // 6) on met à jour les index de recherche
        // pour regénérer la chaine d'affichage de l'élément
        if(!in_array('maj_index_recherche',$this->fonction_a_eviter)) {
            $this->maj_index_recherche();
        }

        if(table_libre($this->_type_element)->parametre == 1){

			Cache_management::genere_valeurs_champs_listes();
		}

        //On enregistre les sous_formulaire une fois le champ créé.
        $retour_enregistre_sous_formulaire = $this->enregistre_sous_formulaire($retour_sous_formulaire['valeurs_pour_enregistrement'], $liste_sous_formulaire, false);

        if($retour_enregistre_sous_formulaire['retour'] !== true)
            return $retour_enregistre_sous_formulaire['retour'];

        // 7) on gère la création d'éléments pour les sous formulaires
        if(!in_array('creation_elements_sous_formulaires',$this->fonction_a_eviter))
		    $this->creation_elements_sous_formulaires($sous_formulaires);

		return true;
	}

	/**
	 *
	 * Retire les modifications qui ne font pas référence à des champs libres
	 *
	 */
	protected function retire_modifications_sans_champs_libres(&$modifications, &$modele, &$selections_multiples, &$champs_tableau) {

		$champs_libres_existants = champs_libres($this->_type_element)->keyBy('nom_sql');

		foreach($modifications as $champ => $valeur) {

			if(!isset($champs_libres_existants[$champ])) {

				unset($modifications[$champ]);
				continue;
			}

			$modele_champ_libre = $champs_libres_existants[$champ];

			// On gère les champs de type sélection multiple
			if($modele_champ_libre['type'] == 10) {

				$index = $modele_champ_libre['table_pivot'];

				initialise_tableau($selections_multiples, array(), $index);

				$selections_multiples[$index] = $valeur;

			}
            // On gère les champs de type tableau
            elseif($modele_champ_libre['type'] ==  16){

			    $champs_tableau[$modele_champ_libre['nom_sql']] = $valeur;
            }
			elseif(in_array($modele_champ_libre['type'], array(4,5))) {

				if($modele_champ_libre['type'] == 4)
					$modele->$champ = formate_date('Y-m-d', $valeur);

				if($modele_champ_libre['type'] == 5)
					$modele->$champ = formate_date('Y-m-d H:i:s', $valeur);
			}
			else {

				$modele->$champ = $valeur;
			}

		}

		return;
	}

	/**
	 *
	 * Cette méthode est utilisée pour modifier le modèle
	 *
	 * Elle doit être utilisée uniquement lorqu'on modifie des champs qui ne sont pas des champs libres, sinon on doit utiliser la méthode enregistre()
	 *
	 * @param $modifications array le tableau des données à modifier, index = colonne dans la table, cle = valeur
	 *
	 * @return void
	 *
	 */
	public function enregistre_modele($modifications = array()) {

		// on retire les données qui ne doivent pas être sauvegardées
		// eden ajoute automatiquement des données sur les modèles comme les liens vers l'élément
		// mais ces données ne correspondent pas à des colonnes dans la table
		$this->retire_donnees_preparees($this->modele);

        $selections_multiples = array();

        $champs_libres_existants = champs_libres($this->_type_element)->keyBy('nom_sql');

		foreach($modifications as $colonne => $valeur) {

            if(isset($champs_libres_existants[$colonne]) && $champs_libres_existants[$colonne]->type == 10) {

                $index = $champs_libres_existants[$colonne]->table_pivot;
                $selections_multiples[$index] = $valeur;
            }
            else
                $this->modele->$colonne = $valeur;
		}

		// on logue qui a fait les modifs et quand
		$this->modele->modifie_le = date('Y-m-d H:i:s');

		if(defined('id_utilisateur') && !empty(id_utilisateur))
			$this->modele->modifie_par = id_utilisateur;
		else
			$this->modele->modifie_par = id_utilisateur_systeme();

        $this->modele->save();

        if(!empty($selections_multiples)) {

            foreach($selections_multiples as $table_pour_selection_multiple => $donnees) {

                // on supprime toutes les données
                DB::select("DELETE FROM $table_pour_selection_multiple WHERE cle_locale = '".$this->modele->id."'");

                // Note Thibaut : problème rencontré sur les checkbox de campagne de prospection : les données sont encodé
                if(!is_array($donnees))
                    $donnees = json_decode($donnees);

                if(is_array($donnees)) {

                    foreach($donnees as $donnee) {

                        if($donnee == '')
                            continue;

                        DB::insert('INSERT INTO '.$table_pour_selection_multiple.' (cle_locale, valeur) values (?, ?)', [$this->modele->id, $donnee]);
                    }
                }
            }

            $this->modele->save();
        }

		if(table_libre($this->_type_element)->parametre == 1){

			Cache_management::vider();
            Cache_management::genere_valeurs_champs_listes();
		}

		// on reload le modèle
		// c'est notamment utile pour re générer les données préparées
		$this->reload_modele($this->modele->id, $this->modele);

        return true;
	}



	/**
	 *
	 * Enregistre un élément sans se soucier du profil
	 *
	 *
	 */
	public function enregistre_sans_profil($modifications = array(), $modele = false) {

        $this->fonction_a_eviter[] = 'verifie_profil_enregistrement';

		return $this->enregistre($modifications);
	}

    /**
	 *
	 * Enregistre un élément sans se soucier des champs obligatoires
	 *
	 *
	 */
	public function enregistre_sans_champs_obligatoires($modifications = array(), $modele = false) {

        $this->fonction_a_eviter[] = 'verifie_champs_obligatoires';

		return $this->enregistre($modifications,$modele);
	}

	/**
	 *
	 * Supprime un élément
	 *
	 * @param $modele le modèle que l'on veut supprimer (si non fourni, on se base sur le modèle lié au management)
	 *
	 * @return true si tout va bien, une erreur (string) si il y a une erreur (impossible de supprimer)
	 *
	 */
	public function supprime($modele = false) {

		if($modele === false) {

			if(isset($this->modele) && $this->modele->exists === true)
				$modele = $this->modele;
			else {

				exception("Erreur lors de la suppression : le modèle n'a pas été trouvé");
			}
		}

		if($this->verifie_profil_suppression($modele) !== true)
			return traduction('message.php.droits.suppression_impossible');

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

		// on gère le soft delete (flag avec la colonne inactif)
		$colonnes = bdd_colonnes($this->_type_element);

		if(!isset($this->modele) || empty($this->modele))
			$this->modele = $modele;

		// on logue la suppression
		$this->log_suppression();

		if(in_array('inactif', $colonnes))
			$this->enregistre_modele(array('inactif' => 1));
		else {

            $champs_libres_multiples = table_libre($this->_type_element)->champs_libres()->where('type',10)->get();

            //On gére les champs multiséléction
            foreach($champs_libres_multiples as $champ_libre) {

                if(table_libre_existe($champ_libre->table_pivot)){
                    $elements_multiples = modele($champ_libre->table_pivot)->where('cle_locale',$modele->id)->get();

                    foreach($elements_multiples as $element_multiple){
                        management($champ_libre->table_pivot,$element_multiple->id,$element_multiple)->supprime();
                    }
                }
                else
                    DB::select("DELETE FROM ".$champ_libre->table_pivot." WHERE cle_locale = '".$modele->id."'");
            }

			// on supprime pour de vrai
			$modele->where('id', $modele->id)->delete();
		}

		// on appelle les méthodes post suppression
		$this->methodes_post_suppression($modele);
		
		if(table_libre($this->_type_element)->parametre == 1){

			Cache_management::vider();
            Cache_management::genere_valeurs_champs_listes();
		}

		return true;
	}

    /**
     *
     * Teste la suppression d'un élément
     *
     */
    public function test_supprime($modele = false) {

        $this->test_suppression = true;

        return $this->supprime($modele);
    }


	/**
	 *
	 * On duplique un élément
	 *
	 */
	public function duplique_avec_modifications($modifications = []) {

		$clone = management($this->_type_element);

		$champs_libres = table_libre($this->_type_element)->champs_libres()->get()->pluck('nom_sql')->toArray();

		$infos = array();

		foreach($this->modele->getAttributes() as $champ => $valeur) {

			// les champs non modifiables sur tous les documents
			$champs_non_transformables = array(

				'id',
				'valide',
				'annule',
				'cree_le',
				'cree_par',
				'modifie_le',
				'modifie_par',
				'accepte',
				'regle',
				'livre',
				'comptabilise',
				'annulee_par_avoir',
				'reference_document',
				'pdf',
				'id_recurrence',
				'maj_droits',
				'chaine_tags_recherche',
				'date_changement_statut',
				'commande_achat_id'
			);

			if(in_array($champ, $champs_non_transformables))
				continue;

			if(!in_array($champ, $champs_libres))
				continue;

			if(isset($modifications[$champ]))
				$infos[$champ] = $modifications[$champ];
			else
				$infos[$champ] = $valeur;
		}

		$infos = $this->retouche_donnees_pour_duplication($infos);

		$retour = $clone->enregistre($infos);

		// On enregistre maintenant les lignes de table pivot
		$champs_libres = table_libre($this->_type_element)->champs_libres()->where('type',10)->get()->pluck('nom_sql')->toArray();

        $infos = array();

        // les champs non modifiables sur tous les documents
        $champs_non_transformables = array(

            'id',
            'valide',
            'annule',
            'cree_le',
            'cree_par',
            'modifie_le',
            'modifie_par',
            'accepte',
            'regle',
            'livre',
            'comptabilise',
            'annulee_par_avoir',
            'reference_document',
            'pdf',
            'id_recurrence',
            'maj_droits',
            'chaine_tags_recherche',
            'date_changement_statut',
        );

		foreach($this->modele->getAttributes() as $champ => $valeur) {

			if(in_array($champ, $champs_non_transformables))
				continue;

			if(!in_array($champ, $champs_libres))
				continue;

			$le_champ = table_libre($this->_type_element)->champs_libres()->where('nom_sql',$champ)->first();

            $modele_table_pivot = table_libre_existe($le_champ->table_pivot) ? modele($le_champ->table_pivot) : \DB::table($le_champ->table_pivot);

            // On va chercher les lignes de la table qui concerne l'élément que l'on duplique
            $lignes_a_dupliquer = $modele_table_pivot->where('cle_locale',$this->modele->id)->get();

            foreach ($lignes_a_dupliquer as $ligne) {

                DB::insert('INSERT INTO ' . $le_champ->table_pivot . ' (cle_locale, valeur) values (?, ?)', [$clone->modele->id, $ligne->valeur]);
            }
		}

		if($retour !== true)
			return $retour;

		if(table_libre($this->_type_element)->parametre == 1){

			Cache_management::vider();
            Cache_management::genere_valeurs_champs_listes();
		}

		return $clone;
	}


	/**
	 *
	 * On duplique un élément
	 *
	 */
	public function duplique() {

		return $this->duplique_avec_modifications([]);
	}

	/**
	 *
	 * Permet de retoucher les données avant une duplication
	 *
	 * Par exemple en gestion commerciale, on ajoute les articles et les lignes divers
	 *
	 */
	protected function retouche_donnees_pour_duplication($infos) {

		return $infos;
	}

	/**
	 *
	 *
	 *
	 */
	public function retourne_pour_api($donnees = array()) {

		$donnees['modele'] = $this->modele;

		return $donnees;
	}

	/**
	 *
	 * Affiche l'élément en fonction de la chaine d'affichage
	 *
	 * Permet d'afficher l'élément courant (le modèle lié au management) en fonction de la chaine d'affichage
	 *
	 * @return string le texte à afficher par exemple "Frédéric Bry" pour un client
	 *
	 */
	public function affiche() {

		if(empty($this->modele))
			return '';

        if(!$this->forcer_affichage && empty(moi()))
            return $this->affiche_extranet();

        $colonne_traduction = !empty(service('traduction')->informations_type_element($this->_type_element));


        if($this->modele->chaine_affichage !== null && !$colonne_traduction) {
            $chaine = $this->modele->chaine_affichage;

            $this->traitement_indisponibilite($chaine);

            return $chaine;
        }

		$table_libre = table_libre($this->_type_element);

        if(empty($table_libre->affichage_dans_liste))
            return '';

        $chaine = $this->recupere_texte_a_afficher($table_libre->affichage_dans_liste);

        $this->ajout_indisponibilite($chaine);

        if($table_libre->vue_sql != 1 && $this->existe() && !$colonne_traduction) {
            // c'est volonataire de ne pas faire $this->enregistre_modele
            // car le modèle a déjà été retouché sur les listes, et ça peut générer des erreurs
            // $modele = modele($this->_type_element)->find($this->modele->id);

            if($this->modele != null && !empty($this->modele->id)) {

                $this->modele->chaine_affichage = $chaine;

                $this->dechargement_valeurs_multiselection();

                $this->modele->save();

                $this->modele = clone $this->modele_avec_multiselect;

                service('microsoft_sharepoint')->changer_nom_dossier_apres_regeneration_chaine_affichage($this->_type_element, $this->modele);
            }
        }

        $this->traitement_indisponibilite($chaine);

		return $chaine;
	}

    /**
     *
     *  Affiche l'élément en fonction de la chaine d'affichage pour l'extranet
     *
     *  Permet d'afficher l'élément courant (le modèle lié au management) en fonction de la chaine d'affichage
     *
     * @return string le texte à afficher par exemple "Frédéric Bry" pour un client
     *
     */
    public function affiche_extranet() {

        $table_libre = table_libre($this->_type_element);

        if(empty($table_libre->affichage_extranet)) {
            $this->forcer_affichage = true;
            return $this->affiche();
        }

        $chaine = $this->recupere_texte_a_afficher($table_libre->affichage_extranet);

        $this->ajout_indisponibilite($chaine);

        $this->traitement_indisponibilite($chaine);

        return $chaine;
    }

	/**
     *
     *  Affiche l'élément en fonction de la chaine d'affichage pour les kanbans
     *
     *  Permet d'afficher l'élément courant (le modèle lié au management) en fonction de la chaine d'affichage
     *
     * @return string le texte à afficher par exemple "Frédéric Bry" pour un client
     *
     */
    public function affichage_dans_kanban() {

        $table_libre = table_libre($this->_type_element);
		
        if(empty($table_libre->affichage_dans_kanban)) {
            $this->forcer_affichage = true;
            return $this->affiche();
        }

        $chaine = $this->recupere_texte_a_afficher($table_libre->affichage_dans_kanban);

        $this->ajout_indisponibilite($chaine);

        $this->traitement_indisponibilite($chaine);

        return $chaine;
    }

	/**
	 *
	 * On retourne le titre d'une fiche en fonction de variables prédéfinis
	 *
	 */
	public function affiche_fiche_type() {

		if(empty($this->modele))
			return '';

		//on récèpère la table libre
		$table_libre = table_libre($this->_type_element);

        if(!empty($table_libre->affichage_fiche_type))
            return $this->recupere_texte_a_afficher($table_libre->affichage_fiche_type);
        else
            return $this->affiche();
	}


	/**
	 *
	 * Affiche l'élément en fonction de la chaine d'affichage
	 *
	 * Permet d'afficher l'élément courant (le modèle lié au management) en fonction de la chaine d'affichage lorsqu'on réalise une recherche
	 *
	 * @return string le texte à afficher par exemple "Frédéric Bry" pour un client
	 *
	 */
	public function affiche_lien_pour_recherche() {

		if(empty($this->modele))
			return '';

		// on récupère la table libre
		$table_libre = table_libre($this->_type_element);

		return $this->recupere_texte_a_afficher($table_libre->affichage_recherche);
	}

	/**
	 *
	 * Affiche l'élément en fonction de la chaine d'affichage
	 *
	 * Permet d'afficher l'élément courant (le modèle lié au management) en fonction de la chaine d'affichage lorsqu'on réalise une recherche
	 *
	 * @return string 
	 *
	 */
	public function affichage_dedoublonnage() {

		if(empty($this->modele))
			return '';

		// on récupère la table libre
		$table_libre = table_libre($this->_type_element);

		if(!empty($table_libre->affichage_dedoublonnage))
            return $this->recupere_texte_a_afficher($table_libre->affichage_dedoublonnage);
        else
            return $this->affiche();
	}

	public function affichage_pour_planning(){
		if(empty($this->modele))
			return '';

		// on récupère la table libre
		$table_libre = table_libre($this->_type_element);
		if(!empty($table_libre->affichage_planning))
            return $this->recupere_texte_a_afficher($table_libre->affichage_planning);
        else
            return $this->affiche();
	}

	public function affichage_pour_calendrier(){
		if(empty($this->modele))
			return '';

		// on récupère la table libre
		$table_libre = table_libre($this->_type_element);
		if(!empty($table_libre->affichage_calendrier))
            return $this->recupere_texte_a_afficher($table_libre->affichage_calendrier);
        else
            return $this->affiche();
	}


	/**
	 *
	 * Retourne un string en remplaçant les variables par les bonnes valeurs
	 *
	 */
	public function recupere_texte_a_afficher($affichage_recherche) {

        try {
            // on récupère les variables
            $variables = $this->recupere_variable('#', '#', $affichage_recherche);
            $variables_a_remplacer = [];

            foreach ($variables as $variable) {

                $variables_a_remplacer["#$variable#"] = $this->champ($variable)->affiche();
            }

            $texte = strtr($affichage_recherche, $variables_a_remplacer);
        }
        catch (\Exception | \Throwable $e){
            $texte = '';
        }

		return $texte;
	}

	/**
	*
	* Retourne un tableau des valeurs qui se situent entre deux caractères
	*
 	*/
	public function recupere_variable($debut, $fin, $string){

		$matches = array();
		$regex = "/$debut([a-zA-Z0-9_]*)$fin/";
		preg_match_all($regex, $string, $matches);

		return $matches[1];
	}


	/**
	*
	* Retourne le lien vers l'élément et l'affiche
	*
	*/
	public function affiche_lien($ancre = false, $element_id = false, $recherche = false) {

		if($ancre === false) {

			if($recherche === false) {

				return '<a href="'.$this->lien_vers_element($element_id).'">'.$this->affiche().'</a>';

			}
			else {

				return '<a href="'.$this->lien_vers_element($element_id).'">'.$this->affiche_lien_pour_recherche().'</a>';
			}

		}
		else {

			return '<a href="'.$this->lien_vers_element($element_id).'">'.$ancre.'</a>';
		}
	}

    /**
	*
	* Retourne le lien vers l'élément et l'affiche pour le select
	*
	*/
	public function affiche_lien_pour_select() {

        return '<a href="' . $this->lien_vers_element() . '" target="_blank">' . $this->affichage_pour_select() . '</a>';
	}

	/**
	*
	* Retourne le lien vers l'élément et l'affiche pour le select
	*
	*/
	public function affiche_lien_dans_kanban() {

        return '<a href="' . $this->lien_vers_element() . '" target="_blank">' . $this->affichage_dans_kanban() . '</a>';
	}

	/**
	 *
	 * Retourne le lien vers l'élément
	 *
	 * @param $id_element l'id élément en question. Si non fourni, on prendra l'id du modèle lié au management
	 *
	 * @return string l'url d'affichage de l'élément (généralement une fiche ou un formulaire de création / modification)
	 *
	 */
	public function lien_vers_element($id_element = false) {

		$table = table_libre($this->_type_element);

		if($id_element === false)
			$id_element = $this->modele->id;

        if(empty($id_element))
            return null;

		if($table->fiche == 1)
			return route('base_eden.fiche.index', [$this->_type_element, $id_element]);

		// on n'a pas de fiche, on regarde du coup si on peut le lier à une fiche client, fournisseur, ou projet
		if(isset($this->modele->client_id) && !empty($this->modele->projet_id))
			return route('base_eden.fiche.index', ['projet', $this->modele->projet_id]);

		if(isset($this->modele->client_id) && !empty($this->modele->client_id))
			return route('base_eden.fiche.index', ['client', $this->modele->client_id]);

		if(isset($this->modele->client_id) && !empty($this->modele->fournisseur_id))
			return route('base_eden.fiche.index', ['fournisseur', $this->modele->fournisseur_id]);

		if(isset($this->modele->client_id) && !empty($this->modele->lead_id))
			return route('base_eden.fiche.index', ['lead', $this->modele->lead_id]);

		if(isset($this->modele->type_element) && !empty($this->modele->element_id))
			return route('base_eden.fiche.index', [$this->modele->type_element, $this->modele->element_id]);


		return route('base_eden.liste.index', [$this->_type_element]);
	}

	/**
	*
	* Affiche le formulaire pour un type element donné
	*
	* @note : non utilisé pour le moment
	*
	*/
	public function affiche_formulaire($champs_libres_pour_titre, $modele, $champs_traites = array(), $html = '') {

		// on ne refait pas les champs qui on déjà été traités
		foreach($champs_libres_pour_titre as $id => $champ_libre) {

			if(in_array($champ_libre->nom_sql, $champs_traites))
				unset($champs_libres_pour_titre[$id]);
		}

		foreach($champs_libres_pour_titre as $champ_libre) {

			$html .= $champ_libre->nom.' : '.$modele->{$champ_libre->nom_sql}.' ('. $champ_libre->nom_sql .') -- ';
			$html .= champ($champ_libre, $modele->{$champ_libre->nom_sql})->placeholder($champ_libre->nom)->cree().' -- ';
			$html .= management($champ_libre->type_element, $modele->id_element())->champ($champ_libre->nom_sql)->affiche();
			$html .= '<br/><br/>';
		}

		return $html;
	}

	/**
	*
	* Enregistre un log d'action sur l'élément, par exemple la validation d'un document en gestion commerciale
	*
	* @param $type_log, @todo à décrire
	*
	* @return le modèle du log enregistré
	*
	*/
    protected function enregistrer_log($type_log, $description = null, $details = null) {

        $log = new Element_log();

        $log->type_action = $type_log;
        $log->type_element = $this->_type_element;
        $log->date = date('Y-m-d H:i:s');

        if(isset($this->modele->id)) {

            $log->id_element = $this->modele->id;
        } else {

            $log->id_element = $this->modele->id_utilisateur;
        }

        if(isset($description))
            $log->description = $description;

        if(isset($details))
            $log->details = json_encode($details);

        if(defined('id_utilisateur'))
            $log->id_utilisateur = id_utilisateur;
        elseif(moi() !== null)
            $log->id_utilisateur = moi()->id;
        else
            $log->id_utilisateur = 0;

        if(!empty(moi_extranet())){
            $log->type_element_modificateur = 'contact';
            $log->element_id_modificateur = moi_extranet()->contact_selectionne->id;
        }


        $log->save();

        return $log;

    }

	/**
	 *
	 * Gère les variables dans les valeurs par défaut des champs
	 *
	 */
	public function remplace_variables_modele_par_defaut($champ_libre) {

        if($champ_libre->type == 10) {

            try {
                $informations_par_defaut = json_decode($champ_libre->valeur_defaut, true);
            }
            catch(Exception $e){
                return [];
            }

            foreach($informations_par_defaut as &$valeur_par_defaut){

                if($valeur_par_defaut == '#utilisateur_connecte#')
                    $valeur_par_defaut = moi()->id;
                else
                    $valeur_par_defaut = intval($valeur_par_defaut);
            }

            return $informations_par_defaut;
        }

		$variables = array(

			'#aujourdhui#' => date('Y-m-d'),
			'#aujourdhui+7j#' => date('Y-m-d', strtotime('now +7 days')),
			'#aujourdhui+14j#' => date('Y-m-d', strtotime('now +14 days')),
		);

		if(defined('id_utilisateur'))
			$variables['#id_utilisateur#'] = id_utilisateur;

        if($champ_libre->type == 5){
            $variables['#aujourdhui#'] = date('Y-m-d H:i:s');
            $variables['#aujourdhui+1h#'] = date('Y-m-d H:i:s',strtotime(' + 1 hours'));
        }

		if($champ_libre->type == 8)
            $variables['#maintenant#'] = date($champ_libre->format_champ);

		if(in_array($champ_libre->type, [4, 5, 8]) && $champ_libre->valeur_defaut !== null && !isset($variables[$champ_libre->valeur_defaut])) {

            $valeur_defaut = str_replace("#", "", $champ_libre->valeur_defaut);

            $valeur_defaut = explode("+",$valeur_defaut);

            $temps = false;

            $valeur = "";
            if(isset($valeur_defaut[1])) {

                $valeurs = explode(' ',$valeur_defaut[1]);

                if(isset($valeurs[1]) && $valeurs[1] == 'personnalise')
                    $temps = $valeurs[0];
                else
                    $valeur .= "+" . $valeur_defaut[1];
            }

            if(isset($valeur_defaut[2]))
                $valeur .= "+" . $valeur_defaut[2];

            if($temps !== false)
                $variables[$champ_libre->valeur_defaut] = date('Y-m-d',strtotime('now')).' '.$temps;
            else if($valeur !== "" && $champ_libre->type == 8)
                $variables[$champ_libre->valeur_defaut] = date($champ_libre->format_champ,strtotime('now ' . $valeur));
            else if($valeur !== "" && $champ_libre->type == 5)
                $variables[$champ_libre->valeur_defaut] = date('Y-m-d H:i:s',strtotime('now ' . $valeur));
            else if($valeur !== "")
                $variables[$champ_libre->valeur_defaut] = date('Y-m-d', strtotime('now ' . $valeur));
        }

		$champs_libres_utilisateur = champs_libres('utilisateur')->where('type','!=',10);

        if(moi() !== null) {
            $utilisateur = moi();
            foreach ($champs_libres_utilisateur as $le_champ) {
                $nom_sql = $le_champ['nom_sql'];

                if(isset($utilisateur->$nom_sql))
                	$variables['#moi-' . $nom_sql . '#'] = $utilisateur->$nom_sql;
            }

            if($utilisateur)
                $variables['#moi#'] = $utilisateur->id;

            if ($champ_libre->type == 42){
				$variables['#utilisateur_connecte#'] = $utilisateur->id;
				$variables['#utilisateur_connecte.entite_id_defaut#'] = $utilisateur->entite_id_defaut;
			}   
        }
        else{
            $variables['#moi#'] = null;
            $variables['#utilisateur_connecte#'] = null;

            foreach ($champs_libres_utilisateur as $le_champ) {
                $variables['#moi-' . $le_champ['nom_sql'] . '#'] = null;
            }
        }

		$retour = str_replace(array_keys($variables), $variables, $champ_libre->valeur_defaut);

		if($retour == '')
			$retour = null;

		return $retour;
	}

	/**
	 *
	 * Retourne un modèle avec des données par défaut
	 *
	 */
	public function modele_par_defaut() {

		$modele = modele($this->_type_element);

		$informations_par_defaut = $this->retourne_informations_par_defaut();

		foreach($informations_par_defaut as $champ => $valeur) {

			$modele->{$champ} = $valeur;
		}

        if(empty(moi()) && !empty(moi_extranet()))
            $modele = $this->ajout_information_extranet($modele);

        $modele = $this->ajout_information_sous_formulaire($modele);

		session()->put('cache.modeles_par_defaut.'.$this->_type_element, $modele);

		return $modele;
	}

    /**
     * @param $modele
     * @return void
     *
     * Ajoute les modeles par défaut des sous-formulaires
     *
     */
    public function ajout_information_sous_formulaire($modele){

        $association_sous_formulaire = $this->retourne_sous_formulaire();

        foreach ($association_sous_formulaire as $donnees_sous_formulaire){

            if(!is_array($donnees_sous_formulaire))
                $donnees_sous_formulaire->toArray();

            if($donnees_sous_formulaire['optionnel'] === 1)
                continue;

            $type_element_remplace = $donnees_sous_formulaire['type_element_enfant'];

            if(!empty($donnees_sous_formulaire['type_element_remplacement']))
                $type_element_remplace = $donnees_sous_formulaire['type_element_remplacement'];

            $modele->{$type_element_remplace} = clone modele_par_defaut($donnees_sous_formulaire['type_element_enfant']);

            if(empty($donnees_sous_formulaire['data_vue']))
                continue;

            $donnees_sous_formulaire['data_vue'] = json_decode($donnees_sous_formulaire['data_vue']);

            foreach ($donnees_sous_formulaire['data_vue'] as $nom_donnee_vue => $donnee_vue){

                $modele->{$type_element_remplace}->{$nom_donnee_vue} = $donnee_vue;

            }

        }

        return $modele;
    }

	/**
	 *
	 * Retourne les informations par défaut d'un modèle sous forme de tableau
	 *
	 */
	public function retourne_informations_par_defaut() {

		$informations_par_defaut = array();

		$champs_libres = champs_libres($this->_type_element);

        $colonnes = Schema::getColumnListing($this->_type_element);

        foreach($champs_libres as $champ_libre) {

            if(!in_array($champ_libre->nom_sql, $colonnes))
                continue;

			// @note frédéric 15/11/2022 v50 je retire la condition valeur_defaut == 0
			// je ne vois pas ce qu'elle fait là, et elle pose problème pour les champs type 20 affichage toggle,
			// car la méthode ci dessus remplace 0 (int) en "0" (string)
			// if(!empty($champ_libre->valeur_defaut) || $champ_libre->valeur_defaut == "0") {
			if(!empty($champ_libre->valeur_defaut)) {
                $informations_par_defaut[$champ_libre->nom_sql] = $this->remplace_variables_modele_par_defaut($champ_libre);
			}
			elseif($champ_libre->valeur_defaut === "0" || $champ_libre->valeur_defaut === 0) {

				$informations_par_defaut[$champ_libre->nom_sql] = 0;
			}
			else {

				// champs de type multi sélection
				if($champ_libre->type == 10) {

					$informations_par_defaut[$champ_libre->nom_sql] = [];
				}
				// champ sous formulaire
				elseif($champ_libre->type == -5) {

					$informations_par_defaut[$champ_libre->nom_sql] = management($champ_libre->type_element_ajax)->modele_par_defaut();
				}
				else {

					$informations_par_defaut[$champ_libre->nom_sql] = '';
				}
			}
		}

		return $informations_par_defaut;
	}

	/**
	 *
	 * Gère la colonne options pour les listes
	 *
	 * @param $modele le modèle en question
	 * @param $options array le tableau des options héritées
	 *
	 * @return html
	 *
	 */
	public function colonne_options($liste_libre) {

        $options = false;

        if(!empty($liste_libre->id_rapport)) {
            $rapport = Rapport_libre::where('id_rapport', $liste_libre->id_rapport)->first();

            if($rapport !== null && $rapport->intranet == 1)
                $options =  ["apercu","supprimer"];
        }

        if($options == false){

            $options = $this->liste_colonnes_options();

            $options[] = 'retablir';
        }

        if(!empty($liste_libre->desactiver_options_individuelle)){

            foreach(json_decode($liste_libre->desactiver_options_individuelle) as $option){

                if(in_array($option,$options))
                    unset($options[array_search($option,$options)]);
            }
        }

        return $options;
	}

    public function liste_colonnes_options(){

        $options = ["apercu","dupliquer","supprimer"];

        $modeles_de_document = modele('modele_de_document')
            ->select('id', 'nom')
            ->where('type_de_document', 1)
            ->where('type_element_autres', $this->_type_element)
            ->get();

        if($modeles_de_document->count() > 0)
            $options[] = "imprimer_modele_document";

		$table_libre = table_libre($this->_type_element);

        if($table_libre->fiche == 1)
            $options[] = "lien";

        if($table_libre->envoyer_email)
            $options[] = "mail";

		if(in_array($this->_type_element, Variables::$documents_comptabilisable))
            array_unshift($options, 'comptabiliser');

        return $options;
    }

	/**
	 *
	 * Cette méthode doit être surchargée pour chaque type element pour faire un truc sensé
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element, $type_element,$id_liste_parent) {

		return '';
	}


	/**
	 *
	 * Retourne les actions sur les listes
	 * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
	 *
	 */
	public function actions_a_afficher($id_liste) {

		$actions = array();

		$liste_libre = Liste_libre::find($id_liste);

		$table_libre = table_libre($this->_type_element);

		if($table_libre == null)
			return $actions;

		// on initialise les actions classiques

		if($table_libre->vue_sql == 1){

            $vue_sql = modele('vue_sql')->where('nom',$this->_type_element)->first();

            if(!empty($vue_sql->table_par_defaut) && table_libre($vue_sql->table_par_defaut)->champs_libres()->where('modifier_en_masse', 1)->count() > 0)
                $actions['modifier'] = '<span class="dropdown-item" @click="$refs.modification_en_masse.ouvrir()"><i class="fa fa-fw fa-pencil-alt"></i> <span v-html="$root.traduction(\'interface.listes.modifier\')"></span></span>';

        }
        else if($table_libre->champs_libres()->where('modifier_en_masse', 1)->count() > 0)
			$actions['modifier'] = '<span class="dropdown-item" @click="$refs.modification_en_masse.ouvrir()"><i class="fa fa-fw fa-pencil-alt"></i> <span v-html="$root.traduction(\'interface.listes.modifier\')"></span></span>';

		$actions['supprimer'] = '<span class="dropdown-item" @click="modale_supprimer = true"><i class="fa fa-fw fa-trash"></i> <span v-html="$root.traduction(\'interface.listes.supprimer\')"></span></span>';

		if(!empty($liste_libre->creation_taches_en_masse))
		    $actions['taches'] = '<span class="dropdown-item" @click="modale_taches = true;"><i class="fas fa-tasks"></i> <span v-html="$root.traduction(\'interface.listes.taches\')"></span></span>';

		// Modèles de document
		$modeles_de_document = modele('modele_de_document')
									->where('type_de_document', 1)
									->where('type_element_autres', $this->_type_element)
									->count();

		if($modeles_de_document > 0)
		    $actions['imprimer_modele_document'] = '<span class="dropdown-item" @click="modale_imprimer_modele = true"><i class="fas fa-print"></i> <span v-html="$root.traduction(\'interface.listes.imprimer_modeles_en_masse\')"></span></span>';
        
        if($table_libre->envoyer_email)
            $actions['envoyer_par_mail'] = '<span class="dropdown-item" @click="modale_envoi_email = true"><i class="fa fa-fw fa-check"></i> <span v-html="$root.traduction(\'interface.listes.envoyer_un_mail\')"></span></span>';
		
        return $actions;
	}

    public function actions_listes($liste_libre){

        $actions = $this->actions_a_afficher($liste_libre->id);

        if(isset($actions['modifier']) && !profil($this->_type_element, 0, 'modification_en_masse'))
            unset($actions['modifier']);

        if(isset($actions['supprimer']) && !profil($this->_type_element, 0, 'suppression_en_masse'))
            unset($actions['supprimer']);

        if(!empty($liste_libre->desactiver_actions_individuelle)) {

            $liste_libre->desactiver_actions_individuelle = json_decode($liste_libre->desactiver_actions_individuelle);

            foreach ($actions as $nom_action => $action)
                if (in_array($nom_action, $liste_libre->desactiver_actions_individuelle))
                    unset($actions[$nom_action]);
        }

        return $actions;
    }

	/**
	 *
	 * On logue le fait d'avoir supprimé un élément
	 *
	 * @return void
	 *
	 */
    public function log_suppression() {

        return $this->enregistrer_log(Variables::$types_logs['suppression']);
	}

	/**
	 *
	 * On logue avec une description sur mesure
	 *
	 * @return void
	 *
	 */
    public function log_sur_mesure($description) {

        return $this->enregistrer_log(Variables::$types_logs['commentaire_sur_mesure'], $description);
	}

	/**
	*
	* Retourne l'historique des modifications de l'élément
	*
	* @param @todo retirer ce paramètre et récupérer via $this->_type_element
	*
	*/
    public function historique() {

		// pas d'élément
		if(empty($this->modele) || empty($this->modele->id)) {

			return [];
		}

		$historique = Element_log::where('type_element', $this->_type_element)
						->with('details_lignes')
						->where('id_element', $this->modele->id)
						->orderBy('date', 'DESC')
                        ->orderBy('id_element_log', 'DESC')
						->get();

        return $historique;
	}

	/**
	 *
	 * On va chercher les images liées à l'article
	 *
	 */
	public function images() {

		return Element_image::where('element_id', $this->modele->id)->where('type_element', $this->_type_element)->get();
	}

	/**
	 *
	 * A t on le droit de faire des changements d'entités sur cet élément ? (par défaut non pour tout le monde)
	 *
	 */
	public function modification_entite_autorisee() {

		return false;
	}

	/**
	 *
	 * Stoppe la récurrence sur un élément
	 *
	 */
	public function stopper_recurrence($id_recurrence = false) {

		if($id_recurrence === false) {

			if(empty($this->modele) || empty($this->modele->id_recurrence))
				return true;

			$id_recurrence = $this->modele->id_recurrence;
		}

		$recurrence = Recurrence::find($id_recurrence);

		$recurrence->inactif = 1;
		$recurrence->save();

		return true;
	}

	/**
	 *
	 * Tags par défaut pour les listes
	 *
	 */
	public function liste_tags($modele) {

		$tags = array();

		if(isset($modele->inactif) && $modele->inactif == 1)
			$tags[] = '<span class="badge badge-danger">Corbeille</span>';

		return implode(' ', $tags);
	}

	/**
	 *
	 * Retourne la liste des pièces jointes de l'élément concerné, basé sur les champs de type pièces jointes
	 *
	 */
	public function recupere_pj_via_champs_pj() {

		// on recupère les champs libres de type 7
		$champs_libres = Champ_libre::where(['type_element' => $this->_type_element, 'type' => 7, 'inactif' => 0])->get();

		$pieces_jointes = array();

		// on recupère tous les champs libres
		$tous_les_champs_libres = Champ_libre::where('type_element', $this->_type_element)->get();

		foreach($champs_libres as $champ_libre) {

			if(!empty($this->modele) && !empty($this->modele->{$champ_libre->nom_sql})) {

				$document = $this->modele->{$champ_libre->nom_sql};

				$url = storage_path('app/public/'.$this->modele->{$champ_libre->nom_sql});

				if(strpos($document, 'http://') !== false || strpos($document, 'https://') !== false) {

					$options = array(
					    "ssl" => array(
					        "verify_peer"=>false,
					        "verify_peer_name"=>false,
					    ),
					);

					// on la télécharge en local
                    try {
                        $contenu_document = file_get_contents($document, false, stream_context_create($options));
                    }
                    catch (\Exception $e){
                        continue;
                    }

                    $extension = pathinfo($document, PATHINFO_EXTENSION);
					$document = 'document_avec_url_'.rand(1, 999999999).''.time().'.'.$extension;

					$document_sur_serveur = fopen(storage_path('app/public/'.$document), "w+");
					fwrite($document_sur_serveur, $contenu_document);
					fclose($document_sur_serveur);
				}

				// on recupère l'extension
				$extension = pathinfo($document, PATHINFO_EXTENSION);

				// Par défaut, on affecte le nom du champs libre
				$nom_avec_extension = $this->affichage_pour_image($champ_libre).'.'.$extension;


				if(!empty($champ_libre->nom_pj)) {

					$nom_avec_extension = $champ_libre->nom_pj;

					// on boucle sur tous les champs libres pour remplacer les eventuelles valeurs
					foreach($tous_les_champs_libres as $cl) {

						if(strpos($champ_libre->nom_pj, '#'.$cl->nom_sql.'#') !== false) {

							$nom_avec_extension = str_replace('#'.$cl->nom_sql.'#', $this->modele->{$cl->nom_sql}, $nom_avec_extension);
						}
					}

					// on supprime les caractères spéciaux et on ajoute l'extension
					$nom_avec_extension = retraite_caracteres_speciaux($nom_avec_extension, '_').'.'.$extension;
				}

				$pieces_jointes[] = array(
					'nom' => $champ_libre->nom,
					'url' => storage_path('app/public/'.$document),
					'nom_avec_extension' => $nom_avec_extension,
				);
			}
		}

		return $pieces_jointes;
	}

    function affichage_pour_image($champ_libre){
        return $champ_libre->nom;
    }

	/**
	 *
	 * Retourne la liste des pièces jointes de l'élément concerné, basé sur la table des pièces jointes
	 *
	 */
	public function pieces_jointes($filtre = false,$id_dossier_parent = null,$avec_documents_confidentiels = true) {

		if(empty($this->modele))
			return array();

		$liste_pieces_jointes = Element_piece_jointe::where('type_element', $this->_type_element)->where('element_id', $this->modele->id)->get();

		$pieces_jointes = array();

		foreach($liste_pieces_jointes as $id => $piece_jointe) {

			if ($piece_jointe->dossier_parent != null && !$avec_documents_confidentiels) {

				$modele = modele('dossier_bibliotheque', $piece_jointe->dossier_parent);
				if ($modele->confidentiel == 1)
					continue;
			}

			$pieces_jointes[] = array(
				'nom' => $piece_jointe->nom,
				'url' => storage_path('app/'.$piece_jointe->chemin),
				'chemin' => $piece_jointe->chemin,
				'id' => $piece_jointe->id,
				'url_telechargement' => route('base_eden.fiche.index', [$this->_type_element, $this->modele->id, 'telecharger_piece_jointe', $piece_jointe->id]),
			);
		}
		return $pieces_jointes;
	}

	/**
	 *
	 * A surcharger. Retourner la liste des pièces jointes à merger au PDF généré
	 *
	 */
	public function pieces_jointes_a_joindre_pdf($filtre = false,$id_dossier_parent = null,$avec_documents_confidentiels = true) {

		return collect([]);
	}

	/**
	 *
	 *
	 * Retourne les champs obligatoire sous forme de tableau
	 *
	 */
	public function retourne_champs_obligatoires() {

		return $this->champs_obligatoire_a_retourner;
	}

	/**
	 *
	 *
	 * Permet d'annuler une suppresion
	 *
	 */
	public function annule_suppression() {

		// On vérifie la notion de champ unique pour éviter les doublons
		// Si c'est pas possible, on renvoie message d'erreur
		// @todo faire des vérifications spécifiques ?

		$etat = true;

		$this->modele->inactif = 0;
		unset($this->modele->affiche_lien);
		$this->modele->save();

		return $etat;
	}

	public function retourne_exports_sur_mesure() {

        $exports = Liste_libre::join('eden_rapports','eden_rapports.id_rapport','eden_listeslibres.id_rapport')
            ->select('eden_listeslibres.*','eden_rapports.index_traduction')
            ->where('eden_listeslibres.type_element', $this->_type_element)
            ->where('eden_listeslibres.export', 1)->get();

		return $exports;
	}

	public function sous_elements_a_copier_avec_duplication($types_elements = array()){

		return $types_elements;
	}


	/**
	 *
	 * Retourne le code HTML pour afficher un élément.
	 *
	 */
	public function retourne_html_pour_calendrier($evenement) {
		return $evenement->nom;
	}

	/**
	 *
	 * Retourne la liste des filtres actifs pour l'élément pour le calendrier
	 *
	 */
	public function filtres_pour_calendrier() {
		return [];
	}

	/**
	 *
	 * Méthode à surcharger en spécifique
	 *
	 * Cette méthode vérifie les conditions requises pour une approbation manuelle
	 * Elle doit retourner true si ces conditions sont remplies
	 * Un message d'erreur (string) sinon, décrivant le problème
	 *
	 */
	public function verifie_conditions_approbabtion() {

		return true;
	}

	/**
	 *
	 * Enregistre le log d'une demande d'approbation
	 *
	 */
	public function methodes_post_demande_approbation() {

		$this->enregistrer_log(Variables::$types_logs['demande_approbation']);
	}

	/**
	 *
	 * Enregistre le log d'une acceptation d'approbation
	 *
	 */
	public function methodes_post_approbation_emise() {

		$this->enregistrer_log(Variables::$types_logs['approbation_emise']);
	}

	/**
	 *
	 * Enregistre le log d'un refus d'approbation
	 *
	 */
	public function methodes_post_approbation_refusee($description = null) {

		$this->enregistrer_log(Variables::$types_logs['approbation_refusee'],$description);
	}

	/**
	 *
	 * Donne l'approbation
	 *
	 */
	public function valider_action($formulaire) {

		// Il faut aller chercher l'id
		if ($formulaire->approbation_id == 0) {

			$modele_approbation = modele('approbation')
									->where('type_element',$formulaire->type_element)
									->where('element_id',$formulaire->element_id)
									->where('approbation',null)
									->where('action', $formulaire->action)
									->first();

			$formulaire->approbation_id = $modele_approbation->id;
		}


		$approbation_management = management('approbation',$formulaire->approbation_id);

		$retour = traduction('messages.php.element.destinataire_demande_approbation');

		// On vérifie si l'utilisateur est apte a pouvoir valider l'approbation ( il est le destinataire )
		if (moi()->id == $approbation_management->modele->destinataire_id)
			$retour = $approbation_management->enregistre_accord_approbation();

		return response()->json(['succes' => $retour]);
	}

	/**
	 *
	 * Refuser l'approbation
	 *
	 */
	public function refuser_action($formulaire) {

		$management = management('approbation',$formulaire->approbation_id);

		$retour_mauvais = traduction('messages.php.element.destinataire_demande_approbation');

		if(isset(moi()->id) && $management->modele->destinataire_id != moi()->id)
			return $retour_mauvais;

		$modifications = array(
			'approbation' => 2,
			'commentaire_refus' => $formulaire->commentaire_refus,
		);

		$management->enregistre($modifications);

		management($formulaire->type_element,$formulaire->element_id)->methodes_post_approbation_refusee($formulaire->commentaire_refus);

		return true;
	}

	/**
	 *
	 * Retourne le destinataire pour une demande d'approbation
	 *
	 */
	public function recupere_destinataire_approbation() {

		return moi()->validation_conges_n_plus_1;
	}


	public function initaliser_tableau_vide($tableau_vide = array()){

		$tableau_vide['titres'] = array();
		$tableau_vide['contenu'] = array();
		$tableau_vide['ajout_contenu'] = array();

		return $tableau_vide;
	}

	/**
	 *
	 * Vérifie si il faut faire les approbations en cascade
	 * Return false si pas d'approbation en cascade, true sinon
	 *
	 * Cette méthode a été prévue pour les surcharges (de manière à pouvoir annuler des approbation en cascade dans certains cas)
	 *
	 * Par défaut, approbation en cascade
	 *
	 */
	public function verification_approbation_cascade(){

		if(moi() === null)
			return false;

		// pas de N+1 donc pas d'approbation en cascade dans tous les cas
		if(empty(moi()->validation_conges_n_plus_1))
			return false;

		return true;
	}

	/**
	 *
	 * Permet de surcharger pour modifier des informations de l'approbation
	 *
	 */
	public function verifications_informations_approbation($informations) {

		return $informations;
	}

	/**
	 *
	 * Permet de surcharger pour modifier des informations de l'approbation
	 *
	 */
	public function retouche_donnees_creation_depuis_extranet($donnees) {

        if(empty(moi()) && !empty(moi_extranet())) {

            $parametre_table = table_libre($this->_type_element);

            $this->ajout_information_extranet($donnees);

            if(!empty($parametre_table->valeurs_forcees_creation_extranet)){

                $valeurs_forcees_creation_extranet = json_decode($parametre_table->valeurs_forcees_creation_extranet);

                foreach($valeurs_forcees_creation_extranet as $champ => $valeur){

                    if(($valeur == '#client_id#' || $valeur == '#contact_id#') && moi_extranet() !== null)
                        $valeur = $valeur == '#client_id#' ? moi_extranet()->contact_selectionne->client_id : moi_extranet()->contact_selectionne->id;

                    $donnees[$champ] = $valeur;
                }
            }
        }

		return $donnees;
	}


	/**
	 *
	 * Permet de surcharger en spécifique pour des élements particuliers
	 *
	 */
	public function traitements_supplementaires_duplication($id_nouveau,$id_a_dupliquer,$type_element) {

		return true;
	}

	/**
	 *
	 * permet de surcharger en spécifique pour des conditions particulières
	 *
	 */
	public function verifications_si_approbation_possible(){

		return true;
	}

	/*
	 *
	 * Permet de gérer l'enregistrement des champs tableaux
	 *
	 */
	public function gestion_champs_tableau($champs_tableau) {

		foreach($champs_tableau as $nom_sql => $valeurs){

            $tableau = modele('tableaux_libres')->where('type_element',$this->_type_element)->where('element_id',$this->modele->id)->where('colonne',$nom_sql)->first();

            if($tableau == null){

                $tableau = modele('tableaux_libres');
                $tableau->type_element = $this->_type_element;
                $tableau->element_id = $this->modele->id;
                $tableau->colonne = $nom_sql;
            }

            $tableau->contenu = json_encode($valeurs);

            $tableau->save();

        }
	}

	public function verifie_si_document_modifiable_meme_si_valide(){

		return false;
	}

    public function ajout_information_extranet(&$modele){

        $parametre_table = table_libre($this->_type_element);

		if($parametre_table->type_profil_extranet == 'client'){

			if($parametre_table->champ_profil_extranet != null)
                $champ_a_modifier = $parametre_table->champ_profil_extranet;
            else
                $champ_a_modifier = 'client_id';

            $valeur = Session::get('utilisateur_eden_extranet')->contact_selectionne->client_id;

		}
		else if($parametre_table->type_profil_extranet == 'contact'){

			if($parametre_table->champ_profil_extranet != null)
				$champ_a_modifier = $parametre_table->champ_profil_extranet;
			else
                $champ_a_modifier = 'contact_id';

            $valeur = Session::get('utilisateur_eden_extranet')->contact_selectionne->id;

		}
		else{
            $champ_a_modifier = 'client_id';

            $valeur = Session::get('utilisateur_eden_extranet')->contact_selectionne->client_id;
        }

        $modele[$champ_a_modifier] = $valeur;

        return $modele;
    }

    /**
     *
     * Gestion des parametres lors de la création d'un document à partir de cet élément
     *
     */
    public function ajout_parametres_creation_document_avec_element(&$parametres, $type_element_destination = false){

    }

    /**
     *
     * On appelle la méthode 'test_enregistre' pour tester les sous_formulaires
     *
     */
    public function enregistre_sous_formulaire(&$modifications, $liste_sous_formulaires, $test_enregistre = false){

        $valeurs_pour_enregistrement = [];
        $management_pour_retour = [];
        foreach ($liste_sous_formulaires as $sous_formulaire){

            if(!is_array($sous_formulaire))
                $sous_formulaire = $sous_formulaire->toArray();

            $type_element_remplacement = $sous_formulaire['type_element_enfant'];

            if(!empty($sous_formulaire['type_element_remplacement']))
                $type_element_remplacement = $sous_formulaire['type_element_remplacement'];

            $nom_sous_formulaire = $this->_type_element . '_creation_' . $type_element_remplacement;

            foreach($modifications as $nom_donnee => $valeur){

                if (strpos($nom_donnee, $nom_sous_formulaire) === false)
                    continue;

                $valeurs_pour_enregistrement[$nom_donnee] = $modifications[$nom_donnee];
                unset($modifications[$nom_donnee]);

                if (is_array($valeurs_pour_enregistrement[$nom_donnee]) && array_filter($valeurs_pour_enregistrement[$nom_donnee])) {

                    $management = management($sous_formulaire['type_element_enfant']);

                    if($test_enregistre === false)
                        $valeurs_pour_enregistrement[$nom_donnee][$sous_formulaire['champ_liaison']] = $this->modele->id;
                    else
                        $management->champ_liaison_creation_sous_formulaire = $sous_formulaire['champ_liaison'];

                    if($test_enregistre === true)
                        $retour = $management->test_enregistre($valeurs_pour_enregistrement[$nom_donnee]);
                    else {
                        $management->enregistre($valeurs_pour_enregistrement[$nom_donnee]);

                        if(isset($this->management_conversion))
                            $this->management_conversion->enregistrer_log(Variables::$types_logs['conversion'], null, [$sous_formulaire['type_element_enfant'] => $management->modele->id]);

                        $management_pour_retour[$nom_donnee] = $management;
                    }

                    if ($test_enregistre === true && $retour != 'test_ok')
                        return ['retour' => table_libre($sous_formulaire['type_element_enfant'])->element . " : " . $retour];
                }

            }

        }

        $tableau_retour = [
            'retour' => true,
            'valeurs_pour_enregistrement' => $valeurs_pour_enregistrement,
            'management_pour_retour' => $management_pour_retour
        ];

        if($test_enregistre === false)
            $this->methodes_post_modification_sous_formulaire($tableau_retour);

        return $tableau_retour;

    }

    public function methodes_post_modification_sous_formulaire($donnees){

        return true;

    }

    public function retourne_sous_formulaire(){

        if(in_array($this->_type_element, array('traduction_index','traduction_valeur','traduction_langue')) || !table_libre_existe("eden_sous_formulaire"))
            return [];

        if(session()->has("cache.sous_formulaire_lie." . $this->_type_element)) {

            $sous_formulaire_cache = session()->get("cache.sous_formulaire_lie." . $this->_type_element);

            $sous_formulaire = clone $sous_formulaire_cache;
            $sous_formulaire->transform(function($sous_form) { return clone $sous_form; });

            return $sous_formulaire;
        }

        $sous_formulaire = Formulaires_champs::join('eden_sous_formulaire', 'eden_formulaireslibres_champs.nom_sous_formulaire', '=', 'eden_sous_formulaire.nom_sous_formulaire')
            ->where('type_champ', 3)
            ->where('type_element', $this->_type_element)
            ->get();

        $sous_formulaire_cache = clone $sous_formulaire;
        $sous_formulaire_cache->transform(function($sous_form) { return clone $sous_form; });

        session()->put("cache.sous_formulaire_lie." . $this->_type_element, $sous_formulaire_cache);

        return $sous_formulaire;

    }

	/**
	 *
	 * Retourne le chemin vers le fichier PDF
	 *
	 */
	public function recupere_chemin_pdf($forcer_regeneration = false, $id_modele_doc = false) {

        try{

            return $this->creation_pdf($id_modele_doc);

        } catch (\Exception $e){

            $pdf = PDF::loadView("eden::pdf.erreur_generation_pdf", ['source_erreur' => $e->getMessage() . 'Line :' . $e->getLine()]);
            \Storage::put($this->_type_element . '_' . $this->modele->id . '_' . date('d') . '.pdf', $pdf->output());
            return $this->_type_element . '_' . $this->modele->id . '_' . date('d') . '.pdf';
        }
	}

    /**
     *
     * Méthode à surcharger pour chaque type_element pour générer leur pdf
     *
     */
    public function creation_pdf($id_modele_doc = false) {

		return $this->creation_document_pdf($id_modele_doc);
    }

	/**
	 *
     * On ajoute l'indisponibilité dans la chaîne d'affichage
     *
     */
    public function ajout_indisponibilite(&$chaine){

        if(empty(table_libre('indisponibilite')) || in_array($this->_type_element,array('traduction_index','traduction_valeur')))
            return $chaine;

        $date = date('Y-m-d H:i:s');

        $indisponibilite = modele('indisponibilite')
            ->where('type_element',$this->_type_element)
            ->where('element_id',$this->modele->id)
            ->where('date_debut','<=',$date)
            ->where('date_fin','>=',$date)
            ->first();

        if($indisponibilite === null)
            return;

        $chaine .= '[indisponibilite]'.$indisponibilite->date_debut.'|'.$indisponibilite->date_fin.'[/indisponibilite]';
    }

    /**
     *
     * On traite l'indisponibilité dans la chaîne d'affichage pour l'afficher correctement
     *
     */
    public function traitement_indisponibilite(&$chaine){

        if(!table_libre_existe('indisponibilite') || in_array($this->_type_element,array('traduction_index','traduction_valeur')))
            return $chaine;

        if (preg_match('/\[indisponibilite](.*?)\[\/indisponibilite]/', $chaine, $resultat) == 1) {

            $langue = langue_utilisateur();

            $langue = $langue.'_'. strtoupper($langue);

            setlocale(LC_TIME, $langue);

            $resultat = explode('|',$resultat[1]);

            $date = date('Y-m-d H:i:s');
            $date_de_debut = $resultat[0];
            $date_de_fin = $resultat[1];

            if($date_de_debut <= $date && $date <= $date_de_fin){

                $date_de_debut = utf8_encode(strftime('%d %B %Y %T',strtotime($date_de_debut)));
                $date_de_fin = utf8_encode(strftime('%d %B %Y %T',strtotime($date_de_fin)));

                $date_de_debut = str_replace("00:00:00",'',$date_de_debut);
                $date_de_fin = str_replace("00:00:00",'',$date_de_fin);

                $remplacement = '<span class="badge badge-danger" data-toggle="tooltip" title="'.traduction('interface.indisponibilite.affichage_element', null, [$date_de_debut, $date_de_fin]).'" style="margin-left:5px;">
                    '.traduction('interface.indisponibilite.badge').'
                </span>';

                $chaine = preg_replace('/\[indisponibilite](.*?)\[\/indisponibilite]/', $remplacement, $chaine);

            }
            else{

                $chaine = preg_replace('/\[indisponibilite](.*?)\[\/indisponibilite]/', '', $chaine);

                $this->ajout_indisponibilite($chaine);

                $this->modele->chaine_affichage = $chaine;

                $this->modele->save();

                $this->reload_modele();

                $this->traitement_indisponibilite($chaine);
            }

        }
    }

	/**
	 *
	 * Contenu de la colonne pour le recouvrement pour enregistrer les relances
	 *
	 */
	public function liste_enregistre_relance($modele, $arguments) {

        // On va chercher si il y a une relance sur ce document, ce type de relance ( null si non )

        $la_relance = modele('relance_recouvrement')->where('type_relance',$arguments['relance_id'])->where($modele->type_element.'_id',$modele->id)->get();

        $texte_de_retour = '<span id_element="'.$modele['id'].'" type="'.$arguments['relance_id'].'" type_element="'.$modele->type_element.'" class="css__lien js_liste_ajouter_relance">'.traduction('interface.listes.ajouter').'</span>
        <span :chargement="chargement_'.$modele['id'].'_'.$arguments['relance_id'].'" class="js_chargement" style="display: none;">
            <img src="'.asset('eden/images/ajax_loader.gif').'" height="30">
        </span>';

        if (!$la_relance->isEmpty()) {

           foreach ($la_relance as $relance) {

           		$texte_de_retour .= '<div style="display: flex;"><span class="mb-1"><span class="fas fa-check" aria-hidden="true" style="color: green;"></span> <span class="badge badge-success">'.date('d/m/Y',strtotime($relance->date)).'</span> <span class="badge badge-danger js_liste_supprimer_relance" id_relance="'.$relance->id.'">X</span> <span class="js_suppression_" style="display: none;"><img src="'.asset('eden/images/ajax_loader.gif').'" height="30"></span></span></div>
            ';
           }

        }

		return $texte_de_retour;
	}


  /**
   *
   * Permet de retraiter les données envoyer lors de la récupération d'un élément
   *
   */
  public function retraite_modele_recuperation(){

	}

	/**
   *
   * Retourne la couleur de l'icone en fonction du type de client
   *
   */
  public function retourne_icone_gmap() {

      return file_get_contents(public_path('eden/images/icone_carte_gmap.svg'));
  }

    /**
     *
     * Action qui se déroule à la suite d'une réponse à un questionnaire lié à l'élément
     *
     */
    public function action_post_reponse_questionnaire($repondant_id){


    }

    /**
	 *
	 * Permet de gérer l'accès aux fiches
	 *
	 * @return si l'utilisateur a le droit d'accéder à la fiche
	 *
	 */
	public function droit_acces_element() {

		if(!empty(moi_extranet()) && !empty($this->modele->id)){

            $element = collect([$this->modele]);

            $element = $this->modele->applique_regle_profils_extranet($element,$this->_type_element,false)->first();

            if($element == null)
                return false;
        }

		return true;
	}

    /**
	 *
	 * Joue le trigger au moment de l'enregistrement d'un élément
	 *
	 * Cette méthode peut être surchargée si nécessaire pour gérer des cas particuliers
	 *
	 */
    public function trigger_applicatif($remplacements = array()) {

        $triggers = triggers($this->_type_element);

        if(empty($remplacements)){
            $remplacements[0] = '#id_cible#';
            $remplacements[1] = $this->modele->id;
        }

		if(!empty($this->modele->id)){

			if(!empty($this->trigger_lance[$this->_type_element]) && in_array($this->modele->id,$this->trigger_lance[$this->_type_element]))
				return true;

			$this->trigger_lance[$this->_type_element][] = $this->modele->id;
		}	

        foreach($triggers as $trigger){

			$debut_execution = microtime(true);
            $logs_champ = $trigger->logs_champ;

            $management_trigger_eden = management('trigger_eden',$trigger->id,(clone $trigger));

            unset($management_trigger_eden->modele->logs_champ);

            // On vient d'abord récupérer les éléments concernés par le trigger pour vérifier qu'il y a des éléments
            $requete_elements_concernes = $trigger->requete_elements_concernes;

            $requete_elements_concernes = str_replace($remplacements[0], $remplacements[1], $requete_elements_concernes);

            $type_element_concerne = type_element_depuis_id($trigger->type_element_concerne_id);

            // Ensuite on joue la requête du trigger
            $requete = $trigger->requete;

            $requete = str_replace($remplacements[0], $remplacements[1], $requete);

            try {
				if(!empty($trigger->type_requete)){
					$elements_concernes_avant = null;
					$requete.= ' RETURNING '.$type_element_concerne.'.*';
                	$elements_concernes = modele($type_element_concerne)->hydrate(DB::select($requete));
					$nombre_elements = count($elements_concernes);
				}
				else{
					list($elements_concernes_avant, $elements_concernes, $nombre_elements) = DB::transaction(function() use ($requete_elements_concernes, $requete, $type_element_concerne) {
						$elements_concernes_avant = modele($type_element_concerne)->hydrate(DB::select($requete_elements_concernes))->keyBy('id');
						$nombre_elements = DB::update($requete);
						$elements_concernes = modele($type_element_concerne)->whereIn('id', array_keys($elements_concernes_avant->toArray()))->get();
						DB::select('UPDATE ' . $type_element_concerne . ' JOIN (' . $requete_elements_concernes . ') as ec ON ec.id = ' . $type_element_concerne . '.id SET ' . $type_element_concerne . '.chaine_affichage = NULL ');

						return array($elements_concernes_avant, $elements_concernes, $nombre_elements);
					});
				}
            }
            catch(\Exception $e){

                if($e != $trigger->erreur_requete)
                    $management_trigger_eden->enregistre_modele(array(
                        'erreur_requete' => $e->getMessage()
                    ));

                 continue;
            }

            if(!empty($trigger->erreur_requete))
                $management_trigger_eden->enregistre_modele(array(
                    'erreur_requete' => null
                ));

			if($nombre_elements == 0)
                continue;

			foreach($elements_concernes as $element_concerne) {

				$management = management($type_element_concerne,$element_concerne->id,$element_concerne);
				$management->trigger_lance = $this->trigger_lance;
				$management->lancement_par_trigger = true;
				$management->elements_synchronises_service_externe = $this->elements_synchronises_service_externe;

				$modele_avant = $elements_concernes_avant[$element_concerne->id] ?? modele($type_element_concerne);

				if(!empty($logs_champ)){

					foreach ($logs_champ as $champ) {

						if (!empty($modele_avant->id) && $modele_avant->{$champ} != $element_concerne->{$champ})
							$modifications[$champ] = $element_concerne->{$champ};

						$management->log_modifications($element_concerne, $modele_avant, $modifications);
					}
				}

				$management->methodes_post_modification($element_concerne, $modele_avant, []);

				$this->trigger_lance = $management->trigger_lance;
				$this->elements_synchronises_service_externe = $management->elements_synchronises_service_externe;
			}

			// Rechargement des index de recherche
			if(empty($this->lancement_par_trigger))
				$this->maj_index_recherche();
				
			management($type_element_concerne)->maj_index_recherche($elements_concernes->pluck('id')->toArray());

			// Si le trigger concerne l'élément en cours, on recharge le modèle
			if($type_element_concerne == $this->_type_element && !empty($this->modele) && empty($this->lancement_par_trigger))
				$this->reload_modele($this->modele->id);
			
			// On enregistre la dernière éxécution
			$management_trigger_eden->enregistre_modele([
				'duree_derniere_execution' => microtime(true)-$debut_execution,
				'date_derniere_execution' => date_create()->format('Y-m-d H:i:s'),
			]);
        }

		return true;
	}

	/**
     *
     * Joue les triggers pour les éléments dont l'id fait partie du tableau $ids_elements
     * Utilisé pour jouer les triggers sur toutes les tâches d'une récurrence (Recurrence_service et Tache_recurrence_management)
	 * et pour les jouer également sur les lignes des documents (Document_management)
     *
     */
	public function trigger_applicatif_elements_multiples($ids_elements){

		$remplacements = array(
            array(
                '=#id_cible#',
                '= #id_cible#'
            ),
            'IN (' . implode(',', $ids_elements) . ')'
        );

		$this->trigger_lance[$this->_type_element] = $ids_elements;

        $this->trigger_applicatif($remplacements);
	}

    /**
     *
     * Permet de récupérer les valeurs reliées à un élément de manière récursif avec en paramétre les types de champs à rechercher
     *
     */
    public function valeurs_champs_relies($parametres_champ){

        $valeurs_champs_relies = array();
        $elements_deja_recuperes = array();

        $champs = Champ_libre::where('type_element',$this->_type_element);

        foreach($parametres_champ as $colonne => $valeur){

            $champs->where($colonne,$valeur);
        }

        $champs = $champs->get()->pluck('nom_sql')->toArray();

        $valeurs_champs_relies[$this->_type_element] = array();

        foreach($champs as $champ){

            if(empty($this->modele->{$champ}) || in_array($this->modele->{$champ},$elements_deja_recuperes))
                continue;

            $valeurs_champs_relies[$this->_type_element][] = $this->modele->{$champ};
            $elements_deja_recuperes[] = $this->modele->{$champ};
        }

        $this->valeurs_champs_relies_recursif($this->_type_element,$this->modele,$parametres_champ,$valeurs_champs_relies,$elements_deja_recuperes);

        foreach($valeurs_champs_relies as $index => $valeurs){

            if(empty($valeurs_champs_relies[$index]))
                unset($valeurs_champs_relies[$index]);
        }

        return $valeurs_champs_relies;
    }

    /**
     *
     * Récupére les valeurs reliés à un champ pour une utilisateur récursive
     *
     */
    public function valeurs_champs_relies_recursif($type_element,$modele,$parametres_champ,&$valeurs_champs_relies,&$elements_deja_recuperes){

        $champs_lies = Champ_libre::where('eden_champslibres.type_element', $type_element)
            ->whereNotIn('eden_champslibres.type_element_ajax',array_keys($valeurs_champs_relies))
            ->where('eden_champslibres.type', '42')
            ->select('champ_lie.*', 'eden_champslibres.nom_sql as nom_sql_parent')
            ->join('eden_champslibres as champ_lie', 'champ_lie.type_element', 'eden_champslibres.type_element_ajax');

        $champs_multiple_lies = Champ_libre::where('eden_champslibres.type_element_ajax', $type_element)
            ->whereNotIn('champ_lie.type_element',array_keys($valeurs_champs_relies))
            ->select('champ_lie.*', 'eden_champslibres.nom_sql as nom_sql_parent')
            ->where('eden_champslibres.type', '42')
            ->join('eden_champslibres as champ_lie', 'champ_lie.type_element', 'eden_champslibres.type_element');

        if(!isset($valeurs_champs_relies[$type_element]))
            $valeurs_champs_relies[$type_element] = array();

        foreach($champs_lies->get()->pluck('type_element','nom_sql_parent')->toArray() as $nom_sql_parent => $type_element) {

            if (empty($modele->{$nom_sql_parent}))
                continue;

            $valeurs_champs_relies[$type_element] = [];

            $modele_element = modele($type_element, $modele->{$nom_sql_parent});

            $this->valeurs_champs_relies_recursif($type_element, $modele_element, $parametres_champ, $valeurs_champs_relies, $elements_deja_recuperes);

        }

        foreach ($parametres_champ as $colonne => $valeur) {

            $champs_lies->where('champ_lie.' . $colonne, $valeur);
            $champs_multiple_lies->where('champ_lie.' . $colonne, $valeur);
        }

        $champs_par_nom_sql = $champs_lies->get()->groupBy('nom_sql_parent');
        $champs_multiple_lies = $champs_multiple_lies->get();

        foreach ($champs_par_nom_sql as $nom_sql_parent => $champs) {

            if (empty($modele->{$nom_sql_parent}))
                continue;

            $modele_element = modele($champs[0]->type_element, $modele->{$nom_sql_parent});

            foreach ($champs as $champ) {

                if (empty($modele_element->{$champ->nom_sql}) || in_array($modele_element->{$champ->nom_sql}, $elements_deja_recuperes))
                    continue;

                $valeurs_champs_relies[$champ->type_element][] = $modele_element->{$champ->nom_sql};
                $elements_deja_recuperes[] = $modele_element->{$champ->nom_sql};

            }
        }

        foreach($champs_multiple_lies as $champ_multiple_lie){

            $elements_multiples = modele($champ_multiple_lie->type_element)
                ->where($champ_multiple_lie->nom_sql_parent,$modele->id)
                ->whereNotNull($champ_multiple_lie->nom_sql)
                ->get()->pluck($champ_multiple_lie->nom_sql)->toArray();

            $valeurs_champs_relies[$champ_multiple_lie->type_element] = $elements_multiples;

            $elements_deja_recuperes = array_merge($elements_deja_recuperes,$valeurs_champs_relies);
        }
    }

    /**
     *
     * Permet de récupérer tous les fichiers liés à un élément
     *
     */
    public function fichiers_lies(){

        $champs_libres_fichiers = Champ_libre::where(function($where) {
                $where->where('type', 7)->orWhere('type', 15);
            })
            ->where('type_element',$this->_type_element)->get();

        $fichiers_lies = array(
            'champs_libres' => array(
                'nom' => traduction('interface.champs_libres'),
                'valeurs' => array()
            ),
        );

        foreach($champs_libres_fichiers as $champ_libre_fichier){

            if(empty($this->modele->{$champ_libre_fichier->nom_sql}))
                continue;

            if($champ_libre_fichier->type == 15){

                try{
                    $fichiers = json_decode($this->modele->{$champ_libre_fichier->nom_sql});

                    foreach($fichiers as $fichier){

                        $fichiers_lies['champs_libres']['valeurs'][] = array(
                            'nom' => traduction($champ_libre_fichier->index_traduction.'.nom').' ('.$champ_libre_fichier->nom_sql.')'.' : '.$fichier->nom_original,
                            'nom_fichier' => $fichier->nom_original,
                            'chemin' => str_replace('public/','',$fichier->url_storage),
                            'champ' => $champ_libre_fichier->nom_sql,
                            'image' => $fichier->image,
                        );
                    }
                }
                catch (\Exception $e){
                    continue;
                }
            }
            else
                $fichiers_lies['champs_libres']['valeurs'][] = array(
                    'nom' => traduction($champ_libre_fichier->index_traduction.'.nom').' ('.$champ_libre_fichier->nom_sql.')',
                    'nom_fichier' => pathinfo(storage_path('app/public/'.$this->modele->{$champ_libre_fichier->nom_sql}), PATHINFO_FILENAME),
                    'chemin' => $this->modele->{$champ_libre_fichier->nom_sql},
                    'champ' => $champ_libre_fichier->nom_sql,
                    'image' => getimagesize(storage_path('app/public/'.$this->modele->{$champ_libre_fichier->nom_sql})) ? true : false,
                );
        }

        if(in_array($this->_type_element,Variables::$documents_gescom) && !empty($this->modele->pdf))
            $fichiers_lies['champs_libres']['valeurs'][] = array(
                'nom' => traduction('interface.pdf_du_document'),
                'nom_fichier' => $this->modele->reference_document,
                'chemin' => $this->modele->pdf,
                'image' => false,
            );

        $fichiers_blocs_pj = Element_piece_jointe::where('type_element',$this->_type_element)
            ->where('element_id',$this->modele->id)
            ->select('chemin','nom','nom as nom_fichier')
            ->get()->toArray();

        foreach($fichiers_blocs_pj as &$fichier_blocs_pj){

            try {
                $fichier_blocs_pj['image'] = getimagesize(storage_path('app/public/' . $fichier_blocs_pj['chemin'])) ? true : false;
            }
            catch(\Exception $e){
                $fichier_blocs_pj['image'] = false;
            }
        }

        $fichiers_lies['bloc_piece_jointe'] = array(
            'nom' => traduction('interface.bloc_piece_jointe'),
            'valeurs' => $fichiers_blocs_pj
        );

        return $fichiers_lies;
    }

    /**
	*
	* On logue la demande de signature d'un document
	*
	* @return void
	*
	*/
    public function log_demande_signature() {

        return $this->enregistrer_log(Variables::$types_logs['demande_signature']);
    }

    /**
	*
	* On logue la signature d'un document
	*
	* @return void
	*
	*/
    public function log_signature() {

        return $this->enregistrer_log(Variables::$types_logs['signature']);
    }

    /**
     *
     * Méthodes post signature d'un document
     *
     */
    public function methode_post_signature(){}


	public function creation_document_pdf_sur_mesure($modele_document) {


		$donnees_pour_pdf = $this->$modele_document();

		$nom_du_pdf = $modele_document.'.pdf';

		$pdf = PDF::loadView('eden::pdf.'.$this->_type_element.'.'.$modele_document, $donnees_pour_pdf);

		return $pdf->stream();
	}

	public function creation_document_pdf($id_modele_doc = false) {

        // On récupère les données propres au PDF
        $donnees_pour_pdf = $this->genere_donnees_pour_pdf();

		list($modele_pdf, $parametrage_avance) = $this->parametrage_modele_pdf_pour_generation_pdf();

		// on vérifie si on doit afficher un pdf spécifique au document
		$modele_pdf_pour_le_document = false;

		// on vérifie si on a un modèle par défaut
		$modele_par_defaut_type_element = config('fonctionnalites_pdf_par_defaut.'.$this->_type_element);

		$type_modele_document = $id_modele_doc;

		// Sinon, on utilise le type_modele_document (document)
		if(empty($type_modele_document) && isset($this->modele->type_modele_document))
			$type_modele_document = $this->modele->type_modele_document;

		// on instancie le type de modele
		if(empty($modele_pdf) && $modele_par_defaut_type_element > 0 && empty($type_modele_document))
            $type_modele_document = $modele_par_defaut_type_element;

		if(!empty($type_modele_document)) {

			//on recupère le modèle document et on vérifie qu'il existe bien
			$modele_de_document = modele('modele_de_document', $type_modele_document);

			$donnees_pour_pdf['modele_de_document'] = $modele_de_document;
			$this->modele_de_document = $modele_de_document;

			if($modele_de_document->exists) {

				if(empty($modele_de_document->nom_vue))
					$modele_de_document->nom_vue = 'modele_document';

				// on recupère et on formate le nom de la vue
				$vue_pdf_pour_le_document = Str::slug($modele_de_document->nom_vue, '_');

				// on vérifie si la vue existe bien
				if(view()->exists("eden::pdf.includes.$vue_pdf_pour_le_document") || view()->exists("eden::pdf.$vue_pdf_pour_le_document")) {

					$modele_pdf_pour_le_document = "eden::pdf.includes.$vue_pdf_pour_le_document";

					if(view()->exists("eden::pdf.$vue_pdf_pour_le_document"))
						$modele_pdf_pour_le_document = "eden::pdf.$vue_pdf_pour_le_document";

					$donnees_pour_pdf['css'] = $modele_de_document->css;
					$donnees_pour_pdf['header'] = json_decode($modele_de_document->header);
					$donnees_pour_pdf['body'] = json_decode($modele_de_document->body);
					$donnees_pour_pdf['footer'] = json_decode($modele_de_document->footer);
					$donnees_pour_pdf['recap_footer'] = json_decode($modele_de_document->recap_footer);
					$donnees_pour_pdf['annexes'] = json_decode($modele_de_document->annexes);

					if(empty($donnees_pour_pdf['header']))
						$donnees_pour_pdf['header'] = [];

					if(empty($donnees_pour_pdf['body']))
						$donnees_pour_pdf['body'] = [];

					if(empty($donnees_pour_pdf['footer']))
						$donnees_pour_pdf['footer'] = [];

					if(empty($donnees_pour_pdf['recap_footer']))
						$donnees_pour_pdf['recap_footer'] = [];

					if(empty($donnees_pour_pdf['annexes']))
						$donnees_pour_pdf['annexes'] = [];
				}
			}
		}

        // un modèle de document choisi
		if(empty($type_modele_document)) {

			// Spécifique document gescom du type element
			if(file_exists(resource_path().'/views/vendor/eden/pdf/document_gescom_'.$this->_type_element.'.blade.php')) {

				$modele_pdf_pour_le_document = 'eden::pdf.document_gescom_'.$this->_type_element;
			}
			// Spécifique document gescom
			elseif (file_exists(resource_path().'/views/vendor/eden/pdf/document_gescom.blade.php')) {
				$modele_pdf_pour_le_document = 'eden::pdf.document_gescom';
			}
			// Standard document gescom du type element
			elseif (view()->exists('eden::pdf.document_gescom_'.$this->_type_element)) {
				$modele_pdf_pour_le_document = 'eden::pdf.document_gescom_'.$this->_type_element;
			}
			// Standard document gescom
			else{
				$modele_pdf_pour_le_document = 'eden::pdf.document_gescom';
			}
		}

		// on rend la vue une seule fois : le HTML sert à la fois à générer le PDF et, dans
		// Document_management, à détecter si le contenu du document a changé pour le versionning
		$html_pdf = view($modele_pdf_pour_le_document, $donnees_pour_pdf)->render();

		$pdf = PDF::loadHTML($html_pdf);

		list($chemin_final, $nom_du_pdf) = $this->recupere_chemin_nom_pdf($modele_de_document ?? null);

        // Code pour les images en https
		$options = $pdf->getDomPDF()->getOptions();
		$options->set('isRemoteEnabled', true);
		$options->set('isPhpEnabled', true);
		$options->set('isHtml5ParserEnabled', true);
		$pdf->getDomPDF()->setOptions($options);

        $contxt = stream_context_create([
            'ssl' => [
            'verify_peer' => FALSE,
            'verify_peer_name' => FALSE,
            'allow_self_signed'=> TRUE
            ]
        ]);

        $pdf->getDomPDF()->setHttpContext($contxt);

		if(!empty($type_modele_document)) {
			$pdf->output();
			$font =  $pdf->getFontMetrics()->getFont("helvetica", "normal");
			if(!empty($modele_de_document->numero_page_x_1) && !empty($modele_de_document->numero_page_y_1)) {

				$taille = $modele_de_document->numero_page_taille_1;
				if(empty($taille))
					$taille = 10;

				$chaine = (!empty($modele_de_document->numero_page_chaine_1) ? $modele_de_document->numero_page_chaine_1 : "Page {PAGE_NUM} of {PAGE_COUNT}");

                $couleur = !empty($modele_de_document->numero_page_couleur_1) ? $modele_de_document->numero_page_couleur_1: "#000000";

                $couleur = Color::parse($couleur);

				$pdf->getDomPDF()->getCanvas()->page_text($modele_de_document->numero_page_x_1, $modele_de_document->numero_page_y_1, $chaine, $font, $taille, $couleur);
			}

			if(!empty($modele_de_document->numero_page_x_2) && !empty($modele_de_document->numero_page_y_2)) {

				$taille = $modele_de_document->numero_page_taille_2;
				if(empty($taille))
					$taille = 10;

				$chaine = (!empty($modele_de_document->numero_page_chaine_2) ? $modele_de_document->numero_page_chaine_2 : "Page {PAGE_NUM} of {PAGE_COUNT}");

                $couleur = !empty($modele_de_document->numero_page_couleur_2) ? $modele_de_document->numero_page_couleur_2: "#000000";

                $couleur = Color::parse($couleur);

				$pdf->getDomPDF()->getCanvas()->page_text($modele_de_document->numero_page_x_2, $modele_de_document->numero_page_y_2, $chaine, $font, $taille, $couleur);
			}
		}

        if(empty($id_modele_doc))
            $nom_du_pdf = $this->creation_document_versionning($chemin_final, $nom_du_pdf, $html_pdf);

		$contenu_pdf = $pdf->output();

		if(!empty($type_modele_document) && !empty($modele_de_document))
			$contenu_pdf = $this->applique_fonds_de_page($contenu_pdf, $modele_de_document);

		\Storage::put($chemin_final.$nom_du_pdf, $contenu_pdf);

		$this->ajoute_document_au_pdf($chemin_final.$nom_du_pdf);

		if(empty($modele_de_document->retirer_cgv_cga) && in_array($this->_type_element, Variables::$documents_gescom))
			$cgv = service('pdf_gescom')->ajoute_cgv_au_document($this);

		if(!empty($cgv)) {

			$merger = \PDFMerger::init();
			$merger->addPDF(storage_path('app/'.$chemin_final.$nom_du_pdf));
			$merger->addPDF(storage_path('app/public/'.$cgv));
			$merger->merge();

			$nom_du_pdf = str_replace('.pdf', '_avec_cgv.pdf', $nom_du_pdf);

			$merger->save(storage_path('app/'.$chemin_final.$nom_du_pdf));
		}

		// \Storage::disk('s3')->put($nom_du_pdf, \Storage::get($nom_du_pdf));

		// il y a des pièces jointes à ajouter au document ?
		$pieces_jointes_avant = $this->pieces_jointes_a_joindre_pdf('avant_document');
		$pieces_jointes_apres = $this->pieces_jointes_a_joindre_pdf('apres_document');

		if($pieces_jointes_avant->count() > 0 || $pieces_jointes_apres->count() > 0) {

			$merger = \PDFMerger::init();

			// les PJ avant le document
			if($pieces_jointes_avant !== null) {

				foreach($pieces_jointes_avant as $piece_jointe) {

					$chemin = storage_path('app/'.$piece_jointe->chemin);

					if(!file_exists($chemin)) {
						$chemin = storage_path('app/public/'.$piece_jointe->chemin);
					}

					$merger->addPDF($chemin);
				}
			}

			// le document
			$merger->addPDF(storage_path('app/'.$chemin_final.$nom_du_pdf));

			// les PJ après le document
			if($pieces_jointes_apres !== null) {


				foreach($pieces_jointes_apres as $piece_jointe) {

					$chemin = storage_path('app/'.$piece_jointe->chemin);

					if(!file_exists($chemin)) {
						$chemin = storage_path('app/public/'.$piece_jointe->chemin);
					}

					$merger->addPDF($chemin);


				}
			}


			$merger->merge();

			$nom_du_pdf = str_replace('.pdf', '_avec_pj.pdf', $nom_du_pdf);

			$merger->save(storage_path('app/'.$chemin_final.$nom_du_pdf));
		}

		$this->creation_pdf_parametrage_avance($parametrage_avance, $chemin_final, $nom_du_pdf);

		if($id_modele_doc !== false) {

			// On regarde si une pièce jointe existe déjà
			$piece_jointe = Element_piece_jointe::where('type_element', $this->_type_element)
										->where('element_id', $this->modele->id)
										->where('nom', $nom_du_pdf)
										->first();
			// Sinon, on la crée
			if(empty($piece_jointe))
	        	$piece_jointe = new Element_piece_jointe;

			// Un modèle de doc a été passé en paramètre, on va enregistrer le pdf
	        $piece_jointe->type_element = $this->_type_element;
	        $piece_jointe->element_id = $this->modele->id;
	        $piece_jointe->nom = $nom_du_pdf;
	        $piece_jointe->chemin = substr($chemin_final, 7).$nom_du_pdf; // on supprime "public/" en début de $chemin_final
	        $piece_jointe->cree_le = date('Y-m-d H:i:s');
	        $piece_jointe->titre = $nom_du_pdf;

	        $piece_jointe->save();

		} elseif(array_key_exists('pdf', $this->modele->toArray())) {

			// on modifie le nom du pdf dans le modèle
			$this->enregistre_modele(array(
				'pdf' => $chemin_final.$nom_du_pdf,
			));
		}

		$this->applique_facturx($chemin_final.$nom_du_pdf);

		return $chemin_final.$nom_du_pdf;
	}

	/**
	 *
	 * Superpose le contenu du PDF sur les fonds de page configurés sur le modèle de document
	 * (première page / pages intermédiaires / dernière page). Le fond est dessiné avant le
	 * contenu, donc il apparaît en dessous.
	 *
	 **/
	private function applique_fonds_de_page($contenu_pdf, $modele_de_document) {

		if(empty($modele_de_document->fond_page_premiere) && empty($modele_de_document->fond_page_milieu) && empty($modele_de_document->fond_page_derniere))
			return $contenu_pdf;

		$fichier_temporaire = tempnam(sys_get_temp_dir(), 'eden_pdf_fond_');
		file_put_contents($fichier_temporaire, $contenu_pdf);

		try {

			$pdf_fpdi = new Fpdi();
			$nombre_pages = $pdf_fpdi->setSourceFile($fichier_temporaire);

			for($numero_page = 1; $numero_page <= $nombre_pages; $numero_page++) {

				$id_modele_page = $pdf_fpdi->importPage($numero_page);
				$taille = $pdf_fpdi->getTemplateSize($id_modele_page);

				$pdf_fpdi->AddPage($taille['orientation'], [$taille['width'], $taille['height']]);

				$chemin_fond = $this->recupere_chemin_fond_de_page($numero_page, $nombre_pages, $modele_de_document);

				if(!empty($chemin_fond))
					$pdf_fpdi->Image($chemin_fond, 0, 0, $taille['width'], $taille['height']);

				$pdf_fpdi->useTemplate($id_modele_page, 0, 0, $taille['width'], $taille['height']);
			}

			return $pdf_fpdi->Output('S');
		}
		// Un problème sur le fond de page (PDF source atypique, etc.) ne doit jamais
		// empêcher la génération du document lui-même.
		catch(\Throwable $exception) {

			Log::error('Erreur lors de l\'application des fonds de page sur le modèle de document '.$modele_de_document->id.' : '.$exception->getMessage());

			return $contenu_pdf;
		}
		finally {

			@unlink($fichier_temporaire);
		}
	}

	/**
	 *
	 * Détermine le chemin du fond de page à utiliser selon la position de la page :
	 * - 1 seule page au total : fond "première page"
	 * - 2 pages : "première page" puis "dernière page"
	 * - 3 pages ou plus : "première page", "pages intermédiaires" pour les pages du milieu, "dernière page"
	 *
	 **/
	private function recupere_chemin_fond_de_page($numero_page, $nombre_pages, $modele_de_document) {

		if($numero_page == 1)
			$champ_fond = 'fond_page_premiere';
		elseif($numero_page == $nombre_pages)
			$champ_fond = 'fond_page_derniere';
		else
			$champ_fond = 'fond_page_milieu';

		$valeur = $modele_de_document->{$champ_fond};

		if(empty($valeur))
			return null;

		$chemin = storage_path('app/public/'.$valeur);

		return file_exists($chemin) ? $chemin : null;
	}

	/**
	 *
	 * Surchargé dans Document Management pour gérer le paramétrage avancé.
	 *
	 * */
	private function creation_pdf_parametrage_avance($parametrage_avance, $chemin_final, $nom_du_pdf) {

	}

	private function parametrage_modele_pdf_pour_generation_pdf() {
		return false;
	}


	/**
	 *
	 * Gére le versionning du document. Surchargée dans Document_Management
	 *
	 **/
	protected function creation_document_versionning($chemin_final, $nom_du_pdf, $html_pdf = null) {

        return $nom_du_pdf;
	}


	/**
	 *
	 * Retourne le chemin final et le nom du pdf généré
	 *
	 **/
	protected function recupere_chemin_nom_pdf($modele_document) {

        if(empty($modele_document->nom_pdf_genere))
		    $nom_du_pdf = traduction('tables_libres.'.$this->_type_element.'.element').'_'.$this->modele->id;
        else
            $nom_du_pdf = $this->recupere_texte_a_afficher($modele_document->nom_pdf_genere);

        $nom_du_pdf = retraite_caracteres_speciaux($nom_du_pdf).'.pdf';

		$chemin_base = 'public/'.$this->_type_element.'/';

		// Cas où le dossier "gescom" n'existe pas, on le crée
		if(!File::isDirectory($chemin_base))
	        File::makeDirectory($chemin_base, $mode = 0777, true, true);

	    return [$chemin_base, $nom_du_pdf];
	}

	/**
	 *
	 * Ajoute un document au pdf
	 *
	 */
	private function ajoute_document_au_pdf($nom_pdf) {

		$liste_fichiers_a_ajouter = $this->document_a_ajouter();

		if(empty($liste_fichiers_a_ajouter))
			return;

		$merger = \PDFMerger::init();
		$merger->addPDF(storage_path('app/'.$nom_pdf));

		foreach($liste_fichiers_a_ajouter as $fichier_a_ajouter) {

			// c'est un document de gestion commerciale qu'on merge
			if(strpos($fichier_a_ajouter, 'gescom/') === 0) {

				$merger->addPDF(storage_path('app/'.$fichier_a_ajouter));
			}
			// c'est un autre type de document
			else {

				$merger->addPDF(storage_path('app/public/'.$fichier_a_ajouter));
			}
		}

		$merger->merge();

		$merger->save(storage_path('app/'.$nom_pdf));
	}

	/**
	 *
	 * Permet d'ajouter automatiquement des documents à un PDF en les mergant (il faut surcharger cette méthode)
	 *
	 */
	protected function document_a_ajouter() {

		return array();
	}


	/**
	 *
	 * Retourne les données à injecter à la vue PDF
	 *
	 */
	protected function genere_donnees_pour_pdf() {

		$donnees_pour_pdf = [
								'element' => clone $this->modele,
							];

		return $donnees_pour_pdf;
	}

    /**
     *
     * Récupère les éléments de remplacement pour les exports par ligne
     *
     */
    public function export_remplacement_valeurs_par_ligne($requete,$colonne_par_champ_nom_sql,$format_date){

        $elements = (clone $requete);

        $select = array(
            $this->_type_element.'.id as id'
        );

        $informations_parent = array(
            'alias_table' => $this->_type_element,
            'type_element' => $this->_type_element
        );

        $managements_champs = array();

        $this->compteur_join = 0;

        $this->gestion_requete_sous_table($colonne_par_champ_nom_sql,$elements,$select,$informations_parent,$managements_champs);

        $elements_par_id = $elements->select(DB::raw(implode(',',$select)))->get()->keyBy('id')->toArray();

        foreach($elements_par_id as $id => &$elements){

            foreach($elements as $index_element => &$element){

                if($index_element == 'id' || $index_element == '#id#' || strpos($index_element,'.id#') !== false)
                    continue;

                $management_champ_libre = $managements_champs[$index_element];

                if (in_array($management_champ_libre->modele->type, array(4, 5)))
                    $element = formate_date($format_date, $element);
                else
                    $element = $management_champ_libre->affiche($element);
            }
        }

        return $elements_par_id;
    }

    /**
     * @param $colonnes
     * @param $elements
     * @param $select
     * @param $informations_parent
     * @param $managements_champs
     * @return void
     *
     * Gère les tables liées pour les exports
     *
     */
    public function gestion_requete_sous_table($colonnes,$elements,&$select,$informations_parent,&$managements_champs){

        foreach($colonnes as $index_colonne => $colonne){

            if($index_colonne == 'sous_table' || (empty(champ_libre_modele($informations_parent['type_element'],$colonne))
                && $index_colonne != '#id#' && strpos($index_colonne,'.id#') === false))
                continue;

            $select[] = $informations_parent['alias_table'] . '.' . $colonne . ' as  `' . $index_colonne.'`';

            if($index_colonne != '#id#' && strpos($index_colonne,'.id#') === false)
                $managements_champs[$index_colonne] = management($informations_parent['type_element'])->champ($colonne);
        }

        if(!empty($colonnes['sous_table'])){

            foreach($colonnes['sous_table'] as $lien => $colonnes_sous_table){

                $champ_libre_modele = champ_libre_modele($informations_parent['type_element'],$lien);

                if(empty($champ_libre_modele))
                    continue;

                if($champ_libre_modele->type == 42) {

                    $type_element_lien = $champ_libre_modele->type_element_ajax;

                    $compteur_join = $this->compteur_join;

                    $alias_table = 'tj' . $compteur_join;

                    $elements->leftJoin(DB::raw($type_element_lien . ' as ' . $alias_table), $informations_parent['alias_table'] . '.' . $lien, $alias_table . '.id');

                    $this->compteur_join++;

                    $informations_parent_enfant = array(
                        'alias_table' => $alias_table,
                        'type_element' => $type_element_lien
                    );

                    $this->gestion_requete_sous_table($colonnes_sous_table, $elements, $select, $informations_parent_enfant, $managements_champs);
                }
                else if($champ_libre_modele->type == 22){

                    $colonne_type_element = $informations_parent['alias_table'].'.'.$champ_libre_modele->contenu;

                    $types_elements_possibles = (clone $elements)
                        ->select($colonne_type_element)
                        ->groupBy($colonne_type_element)
                        ->whereNotNull($colonne_type_element)
                        ->get()->pluck($champ_libre_modele->contenu)->toArray();

                    foreach($types_elements_possibles as $type_element_lien) {

                        $compteur_join = $this->compteur_join;

                        $alias_table = 'tj' . $compteur_join;

                        $elements->leftJoin(DB::raw($type_element_lien . ' as ' . $alias_table),function($join) use ($colonne_type_element,$informations_parent,$lien,$alias_table,$type_element_lien){
                            $join->on($informations_parent['alias_table'] . '.' . $lien, $alias_table . '.id')
                                ->where($colonne_type_element, $type_element_lien);
                        });

                        $this->compteur_join++;

                        $informations_parent_enfant = array(
                            'alias_table' => $alias_table,
                            'type_element' => $type_element_lien
                        );

                        $this->gestion_requete_sous_table($colonnes_sous_table, $elements, $select, $informations_parent_enfant, $managements_champs);
                    }
                }
            }
        }
    }


    /**
     * Met à jour le champ chaine_tags_recherche sur toute une table ou pour une liste/un element(s)
     *
     * Sans paramètres & un modèle dans le management: on met à jour uniquement l'élément en cours
     *
     * Sans paramètres & sans modèle : on met à jour toute la table du type du management
     *
     * Avec un int ou un array d'int : on met à jour le/les enregistrement(s) du type du management
     *
     * @param array|int|null $ids_elements Les identifiants d'éléments à mettre à jour (facultatif)
     * @return bool Retourne vrai en cas de succès, sinon faux
     */
    public function maj_index_recherche($ids_elements = null): bool
    {
        // Libellé absent (ex. traduction manquante d'une liste préformatée) : chaîne vide
        $escapeSQL = function(?string $value) : string {
            return str_replace("'", "''", (string) $value);
        };

        try {

            if ($ids_elements == null && isset($this->modele->id) && $this->modele->id != null)
                $ids_elements = [$this->modele->id];

            if ($ids_elements != null && !is_array($ids_elements))
                $ids_elements = [$ids_elements];

            if (is_array($ids_elements) && count($ids_elements) == 0)
                return true;

            try {
                $table_libre = table_libre($this->_type_element);
                $colonnes = schema_table($this->_type_element);
            } catch (\Exception $ex) {
                throw new \Exception("ElementManagement | maj_index_recherche | Erreur lors du chargement de la table et des colonnes de l'entité {$this->_type_element} .", 1, $ex);
            }

            if (($table_libre === null || $table_libre->disponible_recherche_rapide != 1) && !in_array('chaine_tags_recherche', $colonnes))
                return true;

            try {
                $champs_libres_recherches = champs_libres_recherche($table_libre->type_element);
            } catch (\Exception $ex) {
                throw new \Exception("ElementManagement | maj_index_recherche | Erreur lors du chargement des champs de recherche de l'entité {$this->_type_element} .", 1, $ex);
            }

            if (count($champs_libres_recherches) == 0)
                return true;

			$champs_filtres = $champs_libres_recherches->filter(function ($champ) {
				return $champ->type == 1 || $champ->type_reference == 1;
			});
		
            //Gestion d'une liste libre relié a une autre liste libre : on affecte dans id_cl la liste selectionner (cas ou une liste est réutilisé plusieurs fois dans Eden)
            foreach ($champs_filtres as $champs_libres_recherche_12_1)
                if (!empty($champs_libres_recherche_12_1->liste_choix))
                    $champs_libres_recherche_12_1->id_cl = $champs_libres_recherche_12_1->liste_choix;

            $sql_udpate = "update `$this->_type_element` ";
            $sql_set = "";
            $sql_join = "";
            $table_join_count = 0;
            $trad_liste_preformates = [];
            $trad_liste_libres = [];
            $langue_default = maquette('langue_par_defaut_code');


            //Chargement de toutes les trads pour les champs recherches que l'on gere

            //// On charge la liste des champs qui ont des trads
            /// Pour chaque champ, on va charger les valeurs des trads dans des tableaux pour les utiliser apres sous forme de CASE dans la requète de la morttttt
            /// Pour les champs formatés, c'est un array key =>  trad
            /// Pour les listes libres, c'est un array 3 dim : id_cl => id_valeur => trad
            $liste_champs_formatees = array_values(
				$champs_libres_recherches->filter(function ($champ) {
					return $champ->type == 20 || $champ->type_reference == 20;
				})->toArray()
			);
            $liste_champs_libres = array_values(
				$champs_libres_recherches->filter(function ($champ) {
					return $champ->type == 1 || $champ->type_reference == 1;
				})->toArray()
			);

            //// Cas des les champs formatés
            if (count($liste_champs_formatees) > 0) {
                try {
                    $trad_liste_formate = \Illuminate\Support\Facades\DB::table("traduction_valeur")->where("langue", $langue_default)->whereNull("inactif");

                    //pour chaque liste formatée utilisées, on ajoute la liste des where pour récup les trads
                    $trad_liste_formate->where(function (Builder $query) use ($liste_champs_formatees) {
                        for ($i = 0; $i < count($liste_champs_formatees); $i++) {
                            if ($i == 0) {
                                $query = $query->where("index", "LIKE", "valeurs_listes_formatees.{$liste_champs_formatees[$i]['liste_choix']}.%");
                                continue;
                            }

                            $query = $query->orWhere("index", "LIKE", "valeurs_listes_formatees.{$liste_champs_formatees[$i]['liste_choix']}.%");
                        }
                    });

                    //cas simple : index => valeur trad
                    $trad_liste_preformates = $trad_liste_formate->select("traduction_valeur.index", DB::raw("coalesce(traduction_valeur.traduction_specifique, traduction_valeur.traduction_standard, '') as trad"))->pluck("trad","index");
                } catch (\Exception $ex) {
                    throw new \Exception("ElementManagement | maj_index_recherche | Erreur lors du chargement des valeurs traduites pour les listes prépformatées", 1, $ex);
                }
            }

            ////Cas ou il y a des champs liste libres
            if (count($liste_champs_libres) > 0) {
                try {
                    $trad_liste_libre = DB::table("eden_champslibres_listes")
                        ->join("traduction_valeur", "traduction_valeur.index", "=", DB::raw("CONCAT(eden_champslibres_listes.index_traduction, '.nom')"))
                        ->where("langue", $langue_default)->whereNull("inactif");

                    $trad_liste_libre->where(function (Builder $query) use ($liste_champs_libres) {
                        for ($i = 0; $i < count($liste_champs_libres); $i++) {
                            if ($i == 0) {
                                $query = $query->where("eden_champslibres_listes.id_cl", "=", $liste_champs_libres[$i]['id_cl']);
                                continue;
                            }

                            $query = $query->orWhere("eden_champslibres_listes.id_cl", "=", $liste_champs_libres[$i]['id_cl']);
                        }
                    });

                    //Cas complexe : id_liste => id_valeur => trad
                    $trad_liste_libre = $trad_liste_libre->select("eden_champslibres_listes.id_cl", "eden_champslibres_listes.id_valeur", DB::raw("coalesce(traduction_valeur.traduction_specifique, traduction_valeur.traduction_standard, '') as trad"))->get();

                    foreach ($trad_liste_libre as $trad_liste_libre_valeur)
                        $trad_liste_libres[$trad_liste_libre_valeur->id_cl][$trad_liste_libre_valeur->id_valeur] = $trad_liste_libre_valeur->trad;
                } catch (\Exception $ex) {
                    throw new \Exception("ElementManagement | maj_index_recherche | Erreur lors du chargement des valeurs traduites pour les listes libres", 1, $ex);
                }
            }

            //pour chaque champ coché Recherche, on ajoute dans le SET et les JOIN si besoin
            foreach ($champs_libres_recherches as $champ_libre_recherche) {
                if ($champ_libre_recherche->type == 42) // Lien autre entité Eden
                {
                    $table_join_count++;
                    $sql_join .= " left join `$champ_libre_recherche->type_element_ajax` as t$table_join_count  on t$table_join_count.id = `$this->_type_element`.`$champ_libre_recherche->nom_sql`";
                    $sql_set .= "IFNULL(t$table_join_count.`chaine_tags_recherche`, '') , '###', ";
                } else if ($champ_libre_recherche->type == 20) { // Lien liste preformaté (dans le code) : on fait un CASE dans le SET de l'update
                    $liste_choix = Champ::recuperer_valeur_listes_preenregistrees($champ_libre_recherche->liste_choix, null, false, $trad_liste_preformates);
                    if (count($liste_choix["liste"]) == 0)
                        continue;

                    $sql_set .= "CAST(CASE ";

                    foreach ($liste_choix["liste"] as $liste_choix_valeur) {
                        $liste_choix_index = array_search($liste_choix_valeur, $liste_choix["liste"]);
                        $sql_set .= " WHEN `$this->_type_element`.`$champ_libre_recherche->nom_sql` = $liste_choix_index THEN '" . $escapeSQL($liste_choix_valeur) . "'";
                    }
                    $sql_set .= " ELSE '' END AS CHAR CHARACTER SET utf8) , '###', ";
                } else if ($champ_libre_recherche->type == 1) { // Lien liste libre : pour perf, on charge les valeurs et on genere un CASE
                    if (!isset($trad_liste_libres[$champ_libre_recherche->id_cl]))
                        continue;

                    $table_join_count++;

                    $sql_set .= "CAST(CASE ";

                    //On genere un CASE en fonction de la valeur du champ
                    foreach ($trad_liste_libres[$champ_libre_recherche->id_cl] as $liste_libre_id_valeur => $liste_libre_trad)
                        $sql_set .= "WHEN `$this->_type_element`.`$champ_libre_recherche->nom_sql` = $liste_libre_id_valeur THEN '" . $escapeSQL($liste_libre_trad) . "'";

                    $sql_set .= " ELSE '' END AS CHAR CHARACTER SET utf8) , '###', ";
                } else if ($champ_libre_recherche->type == 10) { // Lien n-n : on fait une table virtuel qui fait un concat des chaine_tag_recherche dans les elements lié à la table ou l'on fait le recalcul
                    $table_join_count_join_principale = $table_join_count + 1;
                    $table_join_count_element = $table_join_count + 2;
                    $table_join_count_pivot = $table_join_count + 3;
                    $table_join_count_target = $table_join_count + 4;

                    $table_join_count += 4;

                    $sql_join .= " LEFT JOIN ( SELECT `t$table_join_count_element`.id as id, group_concat(`t$table_join_count_target`.`chaine_tags_recherche` SEPARATOR '###') as `chaine_tags_recherche` FROM `$this->_type_element` `t$table_join_count_element` ";
                    $sql_join .= " LEFT JOIN `$champ_libre_recherche->table_pivot` `t$table_join_count_pivot` on `t$table_join_count_pivot`.`cle_locale` = `t$table_join_count_element`.id";
                    $sql_join .= " LEFT JOIN `$champ_libre_recherche->type_element_ajax` `t$table_join_count_target` on `t$table_join_count_target`.id = `t$table_join_count_pivot`.`valeur`";
                    $sql_join .= "GROUP BY `t$table_join_count_element`.id ";
                    $sql_join .= ") `t$table_join_count_join_principale` on `t$table_join_count_join_principale`.id = `$this->_type_element`.id";

                    $sql_set .= "IFNULL(`t$table_join_count_join_principale`.`chaine_tags_recherche`, '') , '###', ";
                } else if ($champ_libre_recherche->type_reference == 20) { // Champ preformaté en multi-select on fait une table virtuel avec la liste des valeurs et on les group_concat dans la chaine tag_recherche
                    $liste_choix = Champ::recuperer_valeur_listes_preenregistrees($champ_libre_recherche->liste_choix, null, false, $trad_liste_preformates);

                    if (count($liste_choix["liste"]) == 0)
                        continue;

                    //On genere la table virtuel avec tt les elements de la liste
                    $sql_sous_select_liste = "(";
                    foreach ($liste_choix["liste"] as $liste_choix_valeur) {
                        $sql_sous_select_liste .= "SELECT " . array_search($liste_choix_valeur, $liste_choix["liste"]) . " AS id, '$liste_choix_valeur' as valeur  UNION ALL ";
                    }

                    $sql_sous_select_liste = substr($sql_sous_select_liste, 0, strlen($sql_sous_select_liste) - 11) . ")";

                    //Maintenant on fait une double jointure en utilisant la table virtuel de la liste préformaté
                    $table_join_count_join_principale = $table_join_count + 1;
                    $table_join_count_element = $table_join_count + 2;
                    $table_join_count_pivot = $table_join_count + 3;
                    $table_join_count_target = $table_join_count + 4;

                    $table_join_count += 4;

                    $sql_join .= " LEFT JOIN ( SELECT `t$table_join_count_element`.id as id, group_concat(`t$table_join_count_target`.`valeur` SEPARATOR '###') as `chaine_tags_recherche` FROM `$this->_type_element` `t$table_join_count_element` ";
                    $sql_join .= " LEFT JOIN `$champ_libre_recherche->table_pivot` `t$table_join_count_pivot` on `t$table_join_count_pivot`.`cle_locale` = `t$table_join_count_element`.id";
                    $sql_join .= " LEFT JOIN $sql_sous_select_liste `t$table_join_count_target` on `t$table_join_count_target`.id = `t$table_join_count_pivot`.`valeur`";
                    $sql_join .= "GROUP BY `t$table_join_count_element`.id ";
                    $sql_join .= ") `t$table_join_count_join_principale` on `t$table_join_count_join_principale`.id = `$this->_type_element`.id";

                    $sql_set .= "IFNULL(CAST(`t$table_join_count_join_principale`.`chaine_tags_recherche` AS CHAR CHARACTER SET utf8), '') , '###', ";
                } else if ($champ_libre_recherche->type_reference == 1) { // Champ liste libre en multi-select on fait une table virtuel avec la liste des valeurs et on les group_concat dans la chaine tag_recherche
                    if (!isset($trad_liste_libres[$champ_libre_recherche->id_cl]))
                        continue;

                    $sql_sous_select_liste = "(";

                    //On genere un SELECT UNION a partie de la liste et des valeurs traduites
                    foreach ($trad_liste_libres[$champ_libre_recherche->id_cl] as $liste_libre_id_valeur => $liste_libre_trad)
                        $sql_sous_select_liste .= "SELECT $liste_libre_id_valeur AS id, '" . str_replace("'", "''", $liste_libre_trad) . "' as valeur  UNION ALL ";

                    $sql_sous_select_liste = substr($sql_sous_select_liste, 0, strlen($sql_sous_select_liste) - 11) . ")";

                    //Maintenant on fait une double jointure en utilisant la table virtuel de la liste préformaté
                    $table_join_count_join_principale = $table_join_count + 1;
                    $table_join_count_element = $table_join_count + 2;
                    $table_join_count_pivot = $table_join_count + 3;
                    $table_join_count_target = $table_join_count + 4;

                    $table_join_count += 4;

                    $sql_join .= " LEFT JOIN ( SELECT `t$table_join_count_element`.id as id, group_concat(`t$table_join_count_target`.`valeur` SEPARATOR '###') as `chaine_tags_recherche` FROM `$this->_type_element` `t$table_join_count_element` ";
                    $sql_join .= " LEFT JOIN `$champ_libre_recherche->table_pivot` `t$table_join_count_pivot` on `t$table_join_count_pivot`.`cle_locale` = `t$table_join_count_element`.id";
                    $sql_join .= " LEFT JOIN $sql_sous_select_liste `t$table_join_count_target` on `t$table_join_count_target`.id = `t$table_join_count_pivot`.`valeur`";
                    $sql_join .= "GROUP BY `t$table_join_count_element`.id ";
                    $sql_join .= ") `t$table_join_count_join_principale` on `t$table_join_count_join_principale`.id = `$this->_type_element`.id";

                    $sql_set .= "IFNULL(CAST(`t$table_join_count_join_principale`.`chaine_tags_recherche` AS CHAR CHARACTER SET utf8), '') , '###', ";

                } else if ($champ_libre_recherche->type == 22) { // Gestion des colonnes type dynamique
                    //On charge la liste des entités lié
                    $champ_type_element_management = new Champ_libre_management($this->_type_element, $champ_libre_recherche->contenu);
                    $type_element_list = $champ_type_element_management->modele->contenu;

                    $table_join_count++;
                    $table_join_count_principale = $table_join_count;

                    $type_element_list = json_decode($type_element_list);
                    $sql_coalesce = "";
                    $sql_join_sub_join = "";

                    //Pour chaque type lié, on fait une jointure et un selectionne la valeur lié via un COALESCE
                    foreach ($type_element_list as $type_element_lie) {
                        $table_join_count++;

                        $table_lie = $type_element_lie->type_element;
                        $sql_join_sub_join .= " LEFT JOIN `$table_lie` t$table_join_count on t$table_join_count.id = t$table_join_count_principale.`$champ_libre_recherche->nom_sql`";
                        $sql_coalesce .= "t$table_join_count.chaine_tags_recherche, ";
                    }

                    $sql_coalesce = substr($sql_coalesce, 0, strlen($sql_coalesce) - 2);

                    $table_join_count++;
                    $sql_join .= "LEFT JOIN (SELECT t$table_join_count_principale.id , COALESCE($sql_coalesce) as chaine_tags_recherche FROM `$this->_type_element` t$table_join_count_principale $sql_join_sub_join GROUP BY t$table_join_count_principale.id ) t$table_join_count on t$table_join_count.id = `$this->_type_element`.id";
                    $sql_set .= "IFNULL(`t$table_join_count`.`chaine_tags_recherche`, '') , '###', ";
                } else {
                    $sql_set .= "IFNULL(`$this->_type_element`.`{$champ_libre_recherche["nom_sql"]}`, '') , '###', ";
                }
            }

            $this->recupere_informations_pour_index_recherche($sql_set, $sql_join, $table_join_count);

            if($table_libre->id_element_recherche)
                $sql_set .= "`$this->_type_element`.`id`, '###', ";

            $sql_set = substr($sql_set, 0, strlen($sql_set) - 9); // on retire le dernier  ", '###', " en trop

            $sql_udpate .= $sql_join;
            $sql_udpate .= " set `$this->_type_element`.chaine_tags_recherche = CONCAT(";
            $sql_udpate .= $sql_set;
            $sql_udpate .= ")";

            if ($ids_elements != null) {
                $sql_where_ids = implode(", ", $ids_elements);
                $sql_udpate .= "WHERE `$this->_type_element`.`id` in ($sql_where_ids)";
            }

            try {
                DB::select($sql_udpate);
            } catch (\Exception $ex) {
                throw new \Exception("ElementManagement | maj_index_recherche | Erreur lors de l'execution de la requète de mise à jour. Requète : {$sql_udpate}", 1, $ex);
            }

            return true;
        } catch (\Exception $ex) {
            log_mis_en_forme("Erreur lors de la mise à jour de la chaine tag recherche", $ex);
            return false;
        }

        return false;
    }

    /**
     * @param $parametres
     *
     * Récupére le nom du fichier lors d'un export
     *
     */
    public function nom_fichier_export($management_modele,$parametres){

        return 'Export_'.$management_modele->champ('nom')->affiche();
    }

    /**
     *
     * Permet de récupérer les champs de modifications en masse
     *
     *
     */
    public function champs_modifications_en_masse(){

        $champs_modif_en_masse = table_libre($this->_type_element)
            ->champs_libres()
            ->where('modifier_en_masse', 1)
            ->get();

        foreach($champs_modif_en_masse as &$champ){
            $champ->champ_creation = $this->champ($champ->nom_sql)->vmodel(false)->cree();
        }

        return $champs_modif_en_masse;
    }

    /*
     *
     * Convertit un élément en autre type_element à partir des tables et des champs mappés dans les tables
     * mappage_table_conversion et mappage_champs_conversion
     *
     */
    public function convertir($donnees, $mappage_table){

        $champs_par_type = modele('mappage_champs_conversion')->where('mappage_table', $donnees['mappage_table_id'])->get()->groupBy('type_element_arrivee');

        if(!$mappage_table->creation_auto)
            return [
                'creation_auto' => $mappage_table->creation_auto,
                'nom_formulaire' => $mappage_table->nom_formulaire ?? $mappage_table->type_element_arrivee,
                'type_arrivee_principal' => $mappage_table->type_element_arrivee,
                'champs_par_type' => $champs_par_type
            ];

        $element = modele($donnees['type_element_depart'])->where('id', $donnees['element_id'])->first();
        $elements_crees = array();

        foreach($champs_par_type as $type_element => $champs)
            if($type_element != $mappage_table->type_element_arrivee)
                $champs_par_type[$type_element] = $champs->groupBy('champ_liaison');

        $management_type_principal = management($mappage_table->type_element_arrivee);
        $element_a_creer = modele_par_defaut($mappage_table->type_element_arrivee)->toArray();

        foreach($champs_par_type[$mappage_table->type_element_arrivee] as $champ){

            $element_a_creer[$champ->champ_arrivee] = $element->{$champ->champ_depart};
        }

        try {
            $retour = $management_type_principal->enregistre($element_a_creer);

        } catch(\Exception $e){

            Log::warning("Échec de la conversion de l'élément " . $donnees['type_element_depart'] . ' ' . $donnees['element_id'] . " en " . $mappage_table->type_element_arrivee . ". Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            return traduction('messages.php.conversion.erreur', [$donnees['type_element_depart'], $mappage_table->type_element_arrivee]);
        }

        if($retour !== true)
            return $retour;

        $management_type_principal->modele->affiche_lien = $management_type_principal->affiche_lien();
        $elements_crees[$mappage_table->type_element_arrivee][] = $management_type_principal->modele;
        $this->enregistrer_log(Variables::$types_logs['conversion'], null, [$mappage_table->type_element_arrivee => $management_type_principal->modele->id]);

        unset($champs_par_type[$mappage_table->type_element_arrivee]);

        //On traite les types restants
        foreach($champs_par_type as $type_element => $champs_par_liaison){

            foreach($champs_par_liaison as $champ_liaison => $champs){

                $element_a_creer = modele_par_defaut($type_element)->toArray();
                $management = management($type_element);

                foreach($champs as $champ)
                    $element_a_creer[$champ->champ_arrivee] = $element->{$champ->champ_depart};

                $element_a_creer[$champ_liaison] = $management_type_principal->modele->id;

                try {

                    $management->enregistre($element_a_creer);
                } catch(\Exception $e){

                    Log::warning("Échec de la conversion de l'élément " . $donnees['type_element_depart'] . ' ' . $donnees['element_id'] . " en " . $type_element . ". Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
                    return traduction('messages.php.conversion.erreur', [$donnees['type_element_depart'], $type_element]);
                }

                $management->modele->affiche_lien = $management->affiche_lien();
                $elements_crees[$type_element][$champ_liaison] = $management->modele;
                $this->enregistrer_log(Variables::$types_logs['conversion'], null, [$type_element => $management->modele->id]);
            }
        }

        return ['creation_auto' => $mappage_table->creation_auto, 'elements_crees' => $elements_crees];
    }

	public function generation_modele_pdf(){

		$modeles_de_documents = modeles_de_documents($this->_type_element)
			->filter(function($element){
				return !empty($element->condition_enregistrement_dans_champ) && !empty($element->champ_enregistrement);
			});

		if($modeles_de_documents->isEmpty())
			return;

		foreach($modeles_de_documents as $modele_de_document){

			if(!empty($this->modele->{$modele_de_document->champ_enregistrement}) || eval_condition_js($modele_de_document->condition_enregistrement_dans_champ, $this->_type_element, $this->modele) !== true)
				continue;

			$chemin_pdf = $this->recupere_chemin_pdf(true, $modele_de_document->id);

			$fichier = file_get_contents(storage_path('app/'.$chemin_pdf));
			$nom = traduction('champs_libres.'.$this->_type_element.'.'.$modele_de_document->champ_enregistrement.'.nom').'_'.$this->modele->id;

			\Storage::put('public/'.$nom.'.pdf', $fichier);
			
			$this->enregistre_modele([
				$modele_de_document->champ_enregistrement => $nom.'.pdf' 
			]);
		}
	}

	public function donnee_lien_champ(){
        return $this->modele->id;
    }

	/**
	 * 
	 * Permet la synchronisation des éléments avec un service externe
	 * 
	 */
	public function synchronisation_service_externe($modele_avant, $modifications, $suppression = false) {
		
        if(defined('migration_en_cours') || synchronisation_service_en_cours())
            return;

		$synchronisations = synchronisation_service_externe($this->_type_element);

		if(in_array($this->_type_element.'_'.$this->modele->id, $this->elements_synchronises_service_externe))
			$synchronisations = $synchronisations->where('type_synchronisation', 3);

		if($synchronisations->isEmpty())
			return;

		$this->elements_synchronises_service_externe[] = $this->_type_element.'_'.$this->modele->id;

        queue('synchronisations_externes')::dispatch($synchronisations, $this->_type_element, $this->modele->id);
	}

	/**
	 *
	 * Permet de transformer le PDF final (après fusion CGV/CGA/pièces jointes) en Factur-X.
	 * Prévue pour être surchargée. Appelée en toute dernière étape de generation_modele_pdf(),
	 * une fois $chemin_pdf figé, pour être sûr d'opérer sur le fichier réellement diffusé.
	 *
	 */
	public function applique_facturx($chemin_pdf) {}
}
