<?php

namespace App\Eden\Models\Elements;

use App\Eden\Models\Table_avec_gestion_profil;
use Illuminate\Database\Eloquent\Model;
use App\Eden\Variables;

use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Query_builder\Eden_query_builder;


use Illuminate\Support\Facades\DB;
use Schema;
use Session;

class Element extends Table_avec_gestion_profil {
	
	
	protected $table = '';
	
    protected $primaryKey = '';
	
    protected $avec_inactifs = false;
	
	// est ce que c'est une requête ou on doit appliquer les profils ? (oui par défaut)
    protected $sans_profils = false;

	protected $avec_filtre_extranet = false;
	
    public $timestamps = false;
	
	public function newEloquentBuilder($query) {
		
		return new Eden_query_builder($query);
    }
	
	/**
	* 
	* Renseigne les informations de base issues des tables libres
	* 
	*/
	public function informations_modele($type_element) {

		$table_libre = table_libre($type_element);

		if(!is_object($table_libre)) {
			
			throw new \App\Eden\Exceptions\Eden_exception("La table libre \"$type_element\" n'a pas été trouvée");
		}

		$this->table = $table_libre->type_element;
		$this->primaryKey = 'id';
	}

	/*
	*/
	public function id_element() {

		$cle_primaire = $this->primaryKey;

		return $this->$cle_primaire;
	}
	
	/*
	*/
	public function avec_inactifs() {
		
		$this->avec_inactifs = true;
		
		return $this;
	}
	
	/**
	 *
	 * Permet de ne pas appliquer les profils pour cette requete
	 * 
	 */
	public function sans_profils() {
		
		$this->sans_profils = true;
		
		return $this;
	}

	/**
	 *
	 * Permet d'appliquer les filtres extranet pour cette requete
	 * 
	 */
	public function avec_filtre_extranet() {
		
		$this->avec_filtre_extranet = true;
		
		return $this;
	}
	
	/*
	*/
	public function sans_orderby() {
		
		$this->sans_orderby = true;
		
		return $this;
	}
	
	/**
	 *
	 * Enregistre les paramètres de tri à utiliser
	 * 
	 *
	*/
	public function orderBy($tri, $direction = 'asc') {
		
		if(!isset($this->trie_colonnes))
			$this->trie_colonnes = array();
		
		$tri_colonnes = $this->trie_colonnes;
		
		$tri_colonnes[] = array($tri, $direction);
		
		$this->trie_colonnes = $tri_colonnes;
		
		return $this;
	}

	/**
	 * 
	 * Vérifie si la table peut être utilisé sur l'extranet et si oui filtre la requête
	 * 
	 */
	public function applique_regle_profils_extranet($builder, $table_a_tester,$prefixe = true){

		$parametre_table = table_libre($table_a_tester);

        $champ = null;
        $valeur = null;

        if($table_a_tester == 'client') {
            $champ = 'id';
            $valeur = session()->get('utilisateur_eden_extranet')->contact_selectionne->client_id;
        }
        else if($parametre_table->type_profil_extranet == 'client'){

            $champ = $parametre_table->champ_profil_extranet != null ? $parametre_table->champ_profil_extranet : 'client_id';
            $valeur = session()->get('utilisateur_eden_extranet')->contact_selectionne->client_id;
		}
		else if($parametre_table->type_profil_extranet == 'contact' && $table_a_tester != 'contact'){

            $champ = $parametre_table->champ_profil_extranet != null ? $parametre_table->champ_profil_extranet : 'contact_id';
            $valeur = session()->get('utilisateur_eden_extranet')->contact_selectionne->id;
		}
		else{

            $champ = 'client_id';
            $valeur = session()->get('utilisateur_eden_extranet')->contact_selectionne->client_id;
        }

        if($champ != 'id' && empty(champ_libre_modele($table_a_tester,$champ)))
            return $builder;

        if($prefixe)
            $champ = $table_a_tester.'.'.$champ;

        if(is_array($valeur))
            return $builder->whereIn($champ, $valeur);

        return $builder->where($champ, $valeur);

	}
	
	/**
	 * 
	 * Initie une requete pour la page de liste d'éléments
	 * 
	 * Doit être utilisé en surcharge, notamment pour ajouter des relations dans la requete par exemple (le client sur la facture)
	 * 
	 */
	public function initie_requete_pour_liste() {
		
		return $this;
	}
	
	/**
	 * 
	 * Initie une requete pour la recherche globale de l'ERP
	 * 
	 * Doit être utilisé en surcharge, notamment pour ajouter des règles 
	 * comme prendre en compte les éléments inactifs, certaines règles spécifiques en fonction de statuts de projets, etc
	 * 
	 */
	public function initie_requete_pour_recherche_globale() {
		
		return $this;
	}
	
	
	
	/**
	 * 
	 * Utilisé pour appliquer automatiquement la gestion des profils, et la suppression des éléments inactifs
	 * 
	 */
	public function newQuery($excludeDeleted = true) {
		
		$builder = parent::newQuery($excludeDeleted);

        $colonnes = bdd_colonnes($this->table);

		// on retire automatiquement les éléments inactifs
		if($this->avec_inactifs === false) {

			if(in_array('inactif', $colonnes))
				$builder->where(\DB::raw("coalesce(".$this->table.".inactif, 0)"), 0);
			
			if(in_array('annule', $colonnes) && in_array($this->table, Variables::$documents_gescom) && $this->table != "commande_vente") {
				
				$builder->where(function($query) {

					$query->where($this->table.'.annule', 0);
					$query->orWhereNull($this->table.'.annule');
				});
			}
		}

        if($this->sans_profils === false && !empty(moi()) && !empty(moi()->entites) && $this->table != 'profil_droits_element' && in_array('entite_id', $colonnes) && moi()->acces_toutes_entites != 1)
            $builder->where(function($condition) {
                $condition->whereIn($this->table.'.entite_id', moi()->entites)
                    ->orWhereNull($this->table.'.entite_id')
                    ->orWhere($this->table.'.entite_id',0);
            });

        if(session()->has('cache.droits_profils.element') && $this->sans_profils === false)
            $this->applique_regle_profils($builder, $this->table,$colonnes);

		if(Session::has('utilisateur_eden_extranet') && $this->avec_filtre_extranet === true)
			$this->applique_regle_profils_extranet($builder, $this->table);

		// Tri selon les parametres définis
		if(!empty($this->trie_colonnes)) {
			
			foreach($this->trie_colonnes as $info_tri) {
				
				$builder->orderBy($this->table.'.'.$info_tri[0], $info_tri[1]);
			}
		}
		else  if(!empty($this->tri_par_defaut)) {
			
			// classement croissant ou décroissant par défaut
			$builder->orderBy($this->table.'.'.$this->tri_par_defaut);
		}

		return $builder;
    }

    function getAttribute($attribut){

        $informations_type_element = service('traduction')->informations_type_element($this->table);

        if(!empty($informations_type_element)){

            if(isset($this->attributes['id'])) {

                if (empty($this->attributes['index_traduction']))
                    $this->attributes['index_traduction'] = $this->table . '.' . $this->attributes['id'];

                if (in_array($attribut, $informations_type_element['champs'])) {
                    if(traduction($this->attributes['index_traduction'] . '.' . $attribut,'fr') != $this->attributes['index_traduction'] . '.' . $attribut)
                        return traduction($this->attributes['index_traduction'] . '.' . $attribut);
                }
            }

        }

        return parent::getAttribute($attribut);
    }

    public function save(array $options = []){

        $traduction_service = service('traduction');

        $informations_type_element = $traduction_service->informations_type_element($this->table);

        if(!empty($informations_type_element))
            unset($this->index_traduction);

        parent::save($options);
    }
	
	 /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false) {
		
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array) $attributes);
		
		$model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );
		
		$model->table = $this->table;
		$model->primaryKey = $this->primaryKey;
		
		return $model;
    }

    public function applique_regle_profils($builder,$type_element,$colonnes){

        $droits_profils_entites = session()->get('cache.droits_profils.element')[$type_element] ?? [];

        if(empty($droits_profils_entites))
            return;

        $gestion_profil = function(&$requete,$droits_profils){

            $id_utilisateur = session()->get('utilisateur_eden_extranet')->contact_selectionne->id ?? moi()->id ?? null;

            if(empty($id_utilisateur))
                return;
            
            foreach($droits_profils as $droit_profil){

                if($droit_profil['lecture'] == 1) {

                    if(!empty($droit_profil['nom_sql'])) {

                        $champ_libre = champ_libre_modele($this->table,$droit_profil['nom_sql']);

                        if($champ_libre->type == 42)
                            $requete->orWhere($this->table.'.'.$droit_profil['nom_sql'], $id_utilisateur);
                        else
                            $requete->orWhereRaw($id_utilisateur. " IN (SELECT valeur FROM $champ_libre->table_pivot WHERE cle_locale = ".$this->table.".id)");

                    }
                    else
                        $requete->orWhereRaw('true');
                }
                else{
                    if(!empty($droit_profil['nom_sql'])) {

                        $champ_libre = champ_libre_modele($this->table,$droit_profil['nom_sql']);

                        if($champ_libre->type == 42)
                            $requete->orWhere($this->table.'.'.$droit_profil['nom_sql'], '!=', $id_utilisateur);
                        else
                            $requete->orWhereNotIn($id_utilisateur. "NOT IN (SELECT valeur FROM $champ_libre->table_pivot WHERE cle_locale = ".$this->table.".id)");
                    }
                    else
                        $requete->orWhereRaw('false');
                }
            }
        };

        $builder->where(function($requete) use ($droits_profils_entites,$colonnes,$gestion_profil) {

            foreach ($droits_profils_entites as $entite => $droits_profils) {

                if (empty($entite)) {

                    $entites_ids = array_keys($droits_profils_entites->toArray());

                    $entites_ids = array_filter($entites_ids,function ($entite) {
                        if (!empty($entite))
                            return $entite;
                    });

                    $requete->orWhere(function($sous_requete) use ($droits_profils,$entites_ids,$colonnes,$gestion_profil){

                        if(!empty($entites_ids) && in_array('entite_id', $colonnes))
                            $sous_requete->where(function($condition) use ($entites_ids){
                                $condition->whereNotIn($this->table.'.entite_id',$entites_ids)
                                    ->orWhereNull($this->table.'.entite_id');
                            });

                        $sous_requete->where(function($sous_requete_2) use ($droits_profils,$gestion_profil){
                            $gestion_profil($sous_requete_2,$droits_profils);
                        });
                    });
                }
                else{
                    $requete->orWhere(function($sous_requete) use ($droits_profils,$entite,$gestion_profil){

                        $sous_requete->where($this->table.'.entite_id',$entite);

                        $sous_requete->where(function($sous_requete_2) use ($droits_profils,$gestion_profil){
                            $gestion_profil($sous_requete_2,$droits_profils);
                        });
                    });
                }
            }
        });
    }
}
