<?php

namespace App\Eden\Champs;

use Session;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Managements\Cache_management;
use DB;

class Champ_liste_libre extends Champ_liste {

    public string $type_filtre = 'filtre-liste-libre';

	public string $nom_composant = 'champ-liste-libre';

	public function __construct($champ_libre, $valeur = false) {

		parent::__construct($champ_libre, $valeur);

        $this->charge_valeurs_possibles();
	}

	public function cree(){

		$id_cl = $this->modele->id_cl;

        if(!empty($this->modele->liste_choix))
            $id_cl = $this->modele->liste_choix;

        $this->attr('id_cl',$id_cl);

		$this->attr('type_element', $this->modele->type_element);

		$afficher_sans_valeur = 'true';
        if(!empty($this->modele->cacher_sans_valeur))
			$afficher_sans_valeur = 'false';

		$this->attr('afficher_sans_valeur', $afficher_sans_valeur,1);

		$modele_obligatoire = 'false';

        if(!empty($this->champ_formulaire->condition_obligatoire))
            $modele_obligatoire = $this->champ_formulaire->condition_obligatoire;

        else if(!empty($this->modele->obligatoire))
            $modele_obligatoire = 'true';

		$this->attr('modele_obligatoire', $modele_obligatoire,1);

		return $this->cree_champ();
	}

	public function affiche($valeur = false) {

        if($valeur !== false)
			$this->value($valeur);

        if($this->modele->liste_choix == 0) {

			$id_cl = $this->modele->id_cl;
		}
		else {

			$id_cl = $this->modele->liste_choix;
		}

		if(Session::has('cache.valeurs_listes_libres.informations.'.$id_cl) && cache_actif())
            $informations_liste_libre = Session::get('cache.valeurs_listes_libres.informations.'.$id_cl);
        else{

            $informations_liste_libre = Champ_libre_liste::where('id_cl',$id_cl)->get()->keyBy('id_valeur');

            Session::put('cache.valeurs_listes_libres.informations.'.$id_cl,$informations_liste_libre);
        }

		// on a une valeur en paramètre
		if($this->valeur !== null) {

			if(isset($this->valeurs_possibles[$this->valeur])) {

				$valeur = $this->valeur;

				if(isset($informations_liste_libre[$valeur]) && !empty($informations_liste_libre[$valeur]->couleur_fond)) {

                    $couleur = $informations_liste_libre[$valeur];

					return '<span class="badge badge-default" style="background: '.$couleur->couleur_fond.';border: solid '. $couleur->couleur_fond .' 1px; color: '.$couleur->couleur_police.'">'.$this->valeurs_possibles[$valeur].'</span>';
				}
				else {

					return $this->valeurs_possibles[$valeur];
				}
			}

			return '';
		}


		// cas étrange
		return '';
	}

	/**
	 * 
	 * On charge la liste des valeurs possibles pour ce champ
	 * 
	 */
	protected function charge_valeurs_possibles() { 

		$this->liste_libres();
	}

    /**
     *
     * On charge la liste des valeurs possibles pour ce champ
     *
     */
    public function liste_valeurs() {

        $this->liste_libres();

        return $this->valeurs_possibles;
    }

    public function liste_valeurs_ordre() {

        return '';
    }

	/**
	 * 
	 * On retouche éventuellement une valeur pour l'import de données en masse
	 * 
	 */
	public function prepare_pour_import($valeur) {

		// on vérifie qu'on a bien les listes préenregistrées de chargées
		if(!isset($this->valeurs_possibles))
			$this->listes_preenregistrees();

		$valeurOrg = $valeur;
		$test = in_array(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles));

		/*
		if($this->modele->nom_sql == 'echeance_de_paiement' && $valeur != 'echeance_de_paiement')
			dd($valeur, $this->valeurs_possibles, in_array(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles)), array_map('strtolower', $this->valeurs_possibles));*/

		// On vérifie sans tenir compte de la casse
		if(!in_array(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles))) {

			// On ajoute la nouvelle valeur
			$maximum_ordre = Champ_libre_liste::where('id_cl',$this->modele->id_cl)->max('ordre');
			$nouvelle_valeur = new Champ_libre_liste;
			$nouvelle_valeur->id_cl = $this->modele->id_cl;
			$nouvelle_valeur->valeur = $valeur;
			$nouvelle_valeur->ordre= $maximum_ordre+1;
			$nouvelle_valeur->save();
			
			//if($this->modele->nom_sql == 'echeance_de_paiement')dd($retour, $nouvelle_valeur);

			oublie_cache_eden('listes_libres.'.$this->modele->id_cl);
			Cache_management::invalide();
			
			$valeur = array_search(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles));
			
		} else {
			
			$valeur = array_search(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles));
		}
		
		return $valeur;
	}


	/**
	 * 
	 * Ajoute une valeur à la volée, ou alors retourne l'id de la valeur si elle existe
	 * 
	 */
	public function ajout_valeur_volee($valeur) {

		// elle existe déjà
		if(in_array(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles)))
			return array_search(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles));
		
		// elle n'existe pas, on va la créer
		if($this->modele->liste_choix == 0) {
			
			$id_cl = $this->modele->id_cl;
		}
		else {
			
			$id_cl = $this->modele->liste_choix;
		}
		
		$nouvelle_valeur = new Champ_libre_liste;
		
		$nouvelle_valeur->id_cl = $id_cl;
		$nouvelle_valeur->valeur = $valeur;
		
		$nouvelle_valeur->save();
		
		// on supprime le cache
		oublie_cache_eden('listes_libres.'.$id_cl);
		
		// on met à jour 
		$this->charge_valeurs_possibles();
		
		return $nouvelle_valeur->id_valeur;
	}

	public function applique_filtre_sur_requete($filtre, $requete) {

		if(!is_array($filtre))
			$filtre = array($filtre);

		foreach($filtre as $cle => $valeur) {

			if($valeur === 'false')
				unset($filtre[$cle]);
		}

		if(empty($filtre))
			return $requete;

        $alias_champ = $this->alias_champ_requete();

		if(in_array(0, $filtre)) {
			
			return $requete->where(function($r) use ($filtre, $alias_champ) {
				
				$r->whereIn($alias_champ, $filtre)->orWhereNull($alias_champ);
			});
		}
		else {
			
			return $requete->whereIn($alias_champ, $filtre);
		}

	}
		
}