<?php

namespace App\Eden\Champs;

class Champ_sous_formulaire extends Champ {

	/**
	 * 
	 * Permet de créer un champ pour la saisie des champs de type liste, donc un champ select.
	 * 
	 */
	public function cree($valeur = null) {

		$html = $this->modele->contenu;
		return $html;
	}

}