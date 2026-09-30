<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Table_libre;


class Log_page_management extends Element_management {

	public function liste_colonnes_options() {
		return array();
	}
	
	/**
	 * 
	 * Affiche plus d'information pour l'utilisateur concernant les logs
	 * 
	 * Cette méthode est appelée depuis la liste des logs
	 * 
	 */
	public function detaille_log($modele) {
		
		$informations = $modele->url;
		
		if(strpos($modele->url, 'eden/liste/') !== false) {
			
			$type_element = str_replace('/eden/liste/', '', $modele->url);
			
			$table = Table_libre::where('type_element', $type_element)->first();
			
			if($table !== null) {
				
				$informations = 'Liste ('.$table->element.')';
			}
		}
		elseif($modele->url == '/eden/recherche') {
			
			$informations = 'Recherche globale ERP';
		}
		
		return $informations.' ('.$modele->methode.')';
	}
}