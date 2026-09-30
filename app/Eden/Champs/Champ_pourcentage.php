<?php

namespace App\Eden\Champs;

use DB;

class Champ_pourcentage extends Champ_montant {

    public string $nom_composant = 'champ-pourcentage';

    public function cree(){

        $nombre_decimale = !empty($this->modele->nombre_decimale) ? $this->modele->nombre_decimale : 'false';
        $this->attr('nombre_decimale', $nombre_decimale,1);

        return $this->cree_champ();
    }

    /**
     *
     * Affiche proprement la valeur d'un champ
     *
     */
    public function affiche($valeur = false) {

        if($valeur === false)
            $valeur = $this->valeur;

        $valeur = floatval($valeur) * 100;

        return parent::affiche($valeur) .' %';
    }
	
	/**
	 * 
	 * On doit diviser le montant par 100, pour que ça soit plus simple niveau saisie pour l'utilisateur
	 * 
	 */
	public function applique_filtre_sur_requete($filtre, $requete) {
		
		// pas de variable, on traite le cas classique
		if(empty($filtre['montant']))
			return $requete;
		
		$filtre['montant'] /= 100;
		
		return parent::applique_filtre_sur_requete($filtre, $requete);
		
	}
}