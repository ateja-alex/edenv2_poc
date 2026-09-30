<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Rapport_recouvrement_management extends Listes_management {
	
	/**
	 * 
	 * On ajoute des colonnes à la volée pour les rapports basés sur des listes libres
	 * 
	 */
	public function modifie_liste_colonnes($colonnes) {
		
		
		// on va chercher les types de relance de recouvrement
		$relances = modele('type_relance_recouvrement')->get();
		
		foreach($relances as $relance) {
			
			$nouvelle_colonne = new \StdClass;
			
			$nouvelle_colonne->nom = $relance->nom;
			$nouvelle_colonne->methode = 'liste_enregistre_relance';
			$nouvelle_colonne->type = 'methode';
			$nouvelle_colonne->valeur = '';
			$nouvelle_colonne->id = 99999999999 + $relance->id;
			$nouvelle_colonne->arguments = array('relance_id' => $relance->id);
			
			$colonnes->push($nouvelle_colonne);
		}
		
		return $colonnes;
	}
	
	
} 