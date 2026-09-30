<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Famille_theme_de_filtres;

class Theme_de_filtres_management extends Element_management {
	
	/**
	 * @cf description sur Element_management
	 * 
	 * On traite le cas particulier des filtres et familles associés
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {
		
		
		if(isset($modifications['filtre'])) {
			
			$filtres = $modifications['filtre'];
			unset($modifications['filtre']);
		}
		
		if(isset($modifications['famille'])) {
			
			$familles = $modifications['famille'];
			unset($modifications['famille']);
		}
		
		$retour = parent::enregistre($modifications, $modele);
		
		if($retour !== true)
			return $retour;
		
		// on gère l'enregistrement des familles et filtres
		if(isset($filtres)) {
				
			$ids_filtres = array();
			
			foreach($filtres as $filtre) {
				
				if(empty($filtre) || $filtre == null)
					continue;
				
				$tmp = modele('filtre')->where('filtre', $filtre)->first();
				
				if($tmp !== null)
					$ids_filtres[] = $tmp->id;
				else {
					
					// on crée un filtre à la volée
					$filtre_management = management('filtre');
					$filtre_management->enregistre(array('filtre' => $filtre));
					
					$ids_filtres[] = $filtre_management->modele->id;
				}
			}
			
			$this->modele->filtres()->sync($ids_filtres);
		}
		
		// on gère les familles
		if(isset($familles)) { 
		
			// on supprime tous les liens
			Famille_theme_de_filtres::where('theme_de_filtres_id', $this->modele->id)->delete();
			
			if(is_array($familles)) {
				
				foreach($familles as $famille_id) {
					
					$lien = new Famille_theme_de_filtres;
					$lien->famille_id = $famille_id;
					$lien->theme_de_filtres_id = $this->modele->id;
					$lien->save();
				}
			}
		}
		
		return $retour;
	}
	
	
}