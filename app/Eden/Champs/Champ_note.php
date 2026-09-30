<?php

namespace App\Eden\Champs;

class Champ_note extends Champ_texte {

	public string $nom_composant = 'champ-note';

	public function cree(){

		$contenu = json_decode($this->modele->contenu, true) ?? [];

		$this->attr('parametres',[
			'logo' => $contenu[0] ?? 'fa fa-star',
			'couleur_plein' => $contenu[1] ?? '#fbd71c',
			'couleur_vide' => $contenu[2] ?? '#aaa'
		]);

        return $this->cree_champ();
    }

	/**
	 * 
	 * Affiche proprement la valeur d'un champ
	 * 
	 */
	public function affiche($valeur = false) {
		
		
		if($valeur === false && !empty($this->valeur))
			$valeur = $this->valeur;
		
		if($valeur === false)
			return '';
		
		// on gère les cas ou la note est une moyenne...
		$valeur = round($valeur);
		
		$etoiles = '';

		$contenu = json_decode($this->modele->contenu, true) ?? [];

		$logo = $contenu[0] ?? 'fa fa-star';
		$couleur_plein = $contenu[1] ?? '#fbd71c';
		$couleur_vide = $contenu[2] ?? '#aaa';

		
		for($i=1; $i <= $valeur; $i++) {
			
			$etoiles .= '<span class="'.$logo.'" style="color: '.$couleur_plein.'"></span>';
		}
		
		for($i=$valeur; $i<5; $i++) {
			
			$etoiles .= '<span class="'.$logo.'" style="color: '.$couleur_vide.'"></span>';
		}
		
		return $etoiles;
	}

	public function affiche_export($element) {

		$colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;
        
        return $element->{$colonne_valeur};
	}
	
	/**
	 *
	 * Applique les filtres sur les listes (les listes d'éléments génériques)
	 * 
	 */
	public function applique_filtre_sur_requete($filtre, $requete) {
		
		return $requete->whereIn($this->alias_champ_requete(), $filtre);
	}	
}