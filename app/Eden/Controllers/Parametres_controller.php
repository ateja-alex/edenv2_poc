<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Cache_management;

use Illuminate\Http\Request;

class Parametres_controller extends Controller {
	
	/**
	* 
	* Charge la liste des parametres
	* 
	*/
	protected function charge_liste_parametres($type_element, $champs_libres) {
		
		/**
		@todo trouver un moyen propre de gérer cela
		*/
		if($type_element == 'theme_de_filtres') {
			
			$parametres = modele($type_element)->with('filtres')->with('familles')->get();
		}
		else {
			
			$parametres = modele($type_element)->get();
		}

		$parametres_tableau = array();
		
		foreach($parametres as $parametre) {
			
			$parametres_tableau[] = $parametre;
		}
		
		$liste_champs_libres = $champs_libres->pluck('nom_sql')->toArray();
		
		foreach($parametres_tableau as $id => $parametre) {
			
			$relations = $parametre->getRelations();
			
			$parametre = management($type_element, $parametre->id);
			
			foreach($parametre->modele->getAttributes() as $champ => $osef) {
				
				if(!in_array($champ, $liste_champs_libres))
					continue;
				
				$parametre->modele->{$champ} = $parametre->champ($champ)->affiche($osef);
			}
			
			$parametre->modele->relations = $relations;
			
			$parametres_tableau[$id] = $parametre->modele;
		}
		
		return collect($parametres_tableau);
	}
	
	/**
	 * 
	 * Affiche la page ou il y a toutes les listes à paramétrer, comme les comptes bancaires, etc
	 * 
	 */
	public function liste_elements() {

		$liste_elements = array(
		
			'entite' => array('nom' => 'Entités'), 
			'pays' => array('nom' => 'Pays'), 
			'compte_bancaire' => array('nom' => 'Comptes bancaires'),
			'canal_de_vente' => array('nom' => 'Canaux de vente'),
			'compte_email' => array('nom' => 'Comptes email'),
		);
		
		return view('eden::liste_elements_parametres_specifique')->with('liste_elements' , $liste_elements);
	}
    
	
}
