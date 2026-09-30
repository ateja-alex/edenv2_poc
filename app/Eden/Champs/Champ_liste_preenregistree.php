<?php

namespace App\Eden\Champs;

use DB;
use Session;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Variables;

class Champ_liste_preenregistree extends Champ_liste {

    public string $type_filtre = 'filtre-liste-formatee';

	public string $nom_composant = 'champ-liste-formatee';

	public function __construct($champ_libre, $valeur = false) {

		parent::__construct($champ_libre, $valeur);

        $this->charge_valeurs_possibles();

	}

	public function cree(){

		$this->attr('type_element', $this->modele->type_element);

		$this->attr('liste_choix', $this->modele->liste_choix);

		$modele_obligatoire = 'false';

		if(!empty($this->champ_formulaire->condition_obligatoire))
            $modele_obligatoire = $this->champ_formulaire->condition_obligatoire;

        else if(!empty($this->modele->obligatoire))
            $modele_obligatoire = 'true';

		$this->attr('modele_obligatoire', $modele_obligatoire,1);

		$this->attr('zero_possible', in_array($this->modele->liste_choix,Variables::liste_formatees_zero_possible()) ? 'true' : 'false', 1);

		if($this->modele->badge_cliquable == 1 || $this->modele->format_champ == "badge_cliquable") 
			$this->nom_composant = 'champ-liste-badge-cliquable';
		
		else if($this->modele->format_champ == 'toggle')
            $this->nom_composant = 'champ-liste-toggle';

		return $this->cree_champ();
	}

	/**
	*
	* On charge la liste des valeurs possibles pour ce champ
	*
	*/
	protected function charge_valeurs_possibles() {

		$this->listes_preenregistrees();
	}

	/**
	 *
	 * Récupère l'id d'une valeur à partir de la valeur
	 *
	 * @return false si la valeur n'est pas trouvée
	 * @return $id si la valeur est trouvée
	 *
	 */
	public function recupere_id_valeur($valeur) {

		// pas de valeur
		if(empty($valeur))
			return 0;

		if(in_array(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles)))
			return array_search(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles));

		return false;
	}

	/**
	 *
	 * On retouche éventuellement une valeur pour l'import de données en masse
	 *
	 */
	public function prepare_pour_import($valeur) {

		// c'est le champ utilisateurs
		if ($this->modele->liste_choix == 1) {

			// on vérifie qu'on a bien les listes préenregistrées de chargées
			if(!isset($this->valeurs_possibles))
				$this->listes_preenregistrees();

			$initiales = array_map(function($p){$temp = explode(' ', $p);$temp = array_map(function($o){ return $o[0]; }, $temp);return implode($temp);}, $this->valeurs_possibles) ;

			if(in_array(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles))) {

				// On cherche une correspondance parmi les utilisateurs
				$valeur = array_search(strtolower($valeur), array_map('strtolower', $this->valeurs_possibles));

			} elseif(in_array(($valeur), array_map(function($p){$temp = explode(' ', $p);$temp = array_map(function($o){ return $o[0]; }, $temp);return implode($temp);}, $this->valeurs_possibles))) {

				// On cherche une correspondance parmi les initiales des utilisateurs
				$valeur = array_search(($valeur), array_map(function($p){$temp = explode(' ', $p);$temp = array_map(function($o){ return $o[0]; }, $temp);return implode($temp);}, $this->valeurs_possibles));
			}


		// c'est le champ famille d'articles
		} elseif($this->modele->liste_choix == 2) {

			// on vérifie qu'on a bien les listes préenregistrées de chargées
			if(!isset($this->valeurs_possibles))
				$this->listes_preenregistrees();

			if(!in_array($valeur, $this->valeurs_possibles)) {

				$nom_famille = $valeur;

				// on va créer une famille à la volée
				$nouvelle_famille = management('famille');

				$retour = $nouvelle_famille->enregistre(array('nom' => $nom_famille));

				// on recharge la liste des familles
				$this->listes_preenregistrees();

				if(!in_array($nom_famille, $this->valeurs_possibles))
					exception("La famille \"$nom_famille\" n'a probablement pas été créée lors de l'import, erreur rencontrée : ".$retour);

				$valeur = array_search($nom_famille, $this->valeurs_possibles);

			}
			else {

				$valeur = array_search($valeur, $this->valeurs_possibles);
			}
		}

		return $valeur;
	}


	public function applique_filtre_sur_requete($filtre, $requete) {

		if(!is_array($filtre))
			$filtre = array($filtre);

		foreach($filtre as $id => $valeur) {

			if($valeur === '#utilisateur_connecte#' || $valeur === 'utilisateur_connecte')
				$filtre[$id] = moi()->id;

			if($valeur === 'false')
				unset($filtre[$id]);
		}

		if(empty($filtre))
			return $requete;

        $alias_champ = $this->alias_champ_requete();

		if(in_array("0", $filtre)) {

			return $requete->where(function($requete_tmp) use ($filtre,$alias_champ) {

				$requete_tmp->whereNull($alias_champ)->orWhereIn($alias_champ, $filtre);
			});
		}


		return $requete->whereIn($alias_champ, $filtre);
	}


	public function affiche($valeur = false) {

		if($valeur === false && $this->valeur === false)
			return '';

        $couleurs = [];
        $couleurs_polices = [];

		if(session()->has('cache.listes_preenregistrees_couleurs.'.$this->modele->liste_choix)) {

			$couleurs = session()->get('cache.listes_preenregistrees_couleurs.'.$this->modele->liste_choix);
		}

		if(session()->has('cache.listes_preenregistrees_couleurs_polices.'.$this->modele->liste_choix)) {

			$couleurs_polices = session()->get('cache.listes_preenregistrees_couleurs_polices.'.$this->modele->liste_choix);
		}

        $icones = false;

		if(!empty($this->modele->contenu) && is_array(json_decode($this->modele->contenu)) && json_decode($this->modele->contenu)[0] == 'icone'){

		    $liste_icones = json_decode($this->modele->contenu);

            $icones[0] = '<i class="'.$liste_icones[1].'" style="color:'.$liste_icones[2].'" ></i>';
            $icones[1] = '<i class="'.$liste_icones[3].'" style="color:'.$liste_icones[4].'"></i>';

            if(isset($liste_icones[5])) {
                $icones[0] = '<i class="' . $liste_icones[1] . '" style="color:' . $liste_icones[2] . '" ></i>';
                $icones[1] = '<i class="' . $liste_icones[3] . '" style="color:' . $liste_icones[4] . '"></i>';
                $icones[2] = '<i class="' . $liste_icones[5] . '" style="color:' . $liste_icones[6] . '"></i>';
            }

        }


		if($valeur === false && $this->valeur !== false)
			$valeur = $this->valeur;

        if($valeur === null)
            $valeur = 0;

		// on a une valeur en paramètre
		if($valeur !== false) {

			if(isset($this->valeurs_possibles[$valeur])) {


				if(isset($couleurs_polices[$valeur]) && isset($couleurs[$valeur]) ) {

					return '<span class="badge badge-default" style="background: '.$couleurs[$valeur].';color: '.$couleurs_polices[$valeur].';">'.$this->valeurs_possibles[$valeur].'</span>';
				}
				elseif(isset($couleurs[$valeur])) {

					return '<span class="badge badge-default" style="background: '.$couleurs[$valeur].';color: #fff;">'.$this->valeurs_possibles[$valeur].'</span>';
				}
				elseif(isset($couleurs_polices[$valeur])) {

					return '<span class="badge badge-default" style="background: #ffd;color: '.$couleurs_polices[$valeur].';">'.$this->valeurs_possibles[$valeur].'</span>';
				}

                elseif(isset($icones[$valeur])) {

                    return $icones[$valeur];
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

	/*
     *
     * Récupère la valeur du champ sans retourner de badge ou d'html
     *
     */
    public function recuperer_valeur() {

        if(isset($this->valeurs_possibles[$this->valeur]))
            return $this->valeurs_possibles[$this->valeur];

        return '';
    }

}
