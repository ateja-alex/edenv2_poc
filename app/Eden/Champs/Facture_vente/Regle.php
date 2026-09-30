<?php

namespace App\Eden\Champs\Facture_vente;

use App\Eden\Champs\Champ_liste_preenregistree;

class Regle extends Champ_liste_preenregistree {
	
	public function affiche($valeur = false) {
		
		if($valeur === false && $this->valeur === false)
			return '';
		
		// on a une valeur en paramètre
		if($valeur !== false) {
			
			if(isset($this->valeurs_possibles[$valeur])) {
				
				// en cours
				if(empty($valeur)) 
					return '<span class="badge badge-default">Non réglée</span>';
				
				// terminé
				if($valeur == 1) 
					return '<span class="badge badge-success">Réglée</span>';
				
				// au cas où si on rajoute des valeurs
				return $this->valeurs_possibles[$valeur];
			}
			
			return '<span class="badge badge-default">Non réglée</span>';
		}
		
		// on a une valeur en paramètre
		if($this->valeur !== false) {
			
			if(isset($this->valeurs_possibles[$this->valeur])) {
				
				// en cours
				if(empty($this->valeur)) 
					return '<span class="badge badge-default">Non réglée</span>';
				
				// terminé
				if($this->valeur == 1) 
					return '<span class="badge badge-success">Réglée</span>';
				
				return $this->valeurs_possibles[$this->valeur];
			}
			
			return '<span class="badge badge-default">Non réglée</span>';
		}
		
		
		// cas étrange
		return '';
	}
	
	
}