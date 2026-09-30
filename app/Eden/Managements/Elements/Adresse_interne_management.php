<?php

namespace App\Eden\Managements\Elements;

class Adresse_interne_management extends Element_management {
	
	/**
	 *
	 * On ajoute les données de longitude et latitude / on met à jour les tags de recherche du client lié
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		$this->maj_latitude_longitude();

		$adresse_par_defaut = $modele['par_defaut'];
		
		// On vérifie qu'il n'y ai pas d'autre adresse par défaut, sinon on les passes a 0
		if ($adresse_par_defaut == 1) {
				
			// On supprime l'ancienne adresse par défaut si elle existe
			$adresses_par_defaut_element = modele('adresse_interne')->where('par_defaut',1)->get();

			if ($adresses_par_defaut_element->isNotEmpty()) {
				
				foreach ($adresses_par_defaut_element as $adresse_par_defaut) {
					
					if ($adresse_par_defaut->id == $modele['id'])
						continue;
					
					else {

						$management = management('adresse_interne',$adresse_par_defaut->id);
						$modification = array(
							'par_defaut' => 0,
						);
						$management->enregistre($modification);
					}

				}
			}
		}
	}
	
	/**
	 * 
	 * Met à jour la latitude et la longitude de l'adresse
	 * 
	 */
	public function maj_latitude_longitude() {
		
		if(empty(env('GOOGLE_MAP_API_KEY')))
			return;
		
		$adresse = $this->modele->adresse.' '.$this->modele->adresse_complement.' '.$this->modele->code_postal.' '.$this->modele->ville;
		$adresse = str_replace(" ", "+", $adresse);
		
		$data = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$adresse.'&sensor=false&key='.env('GOOGLE_MAP_API_KEY'));
		$data = json_decode($data, true);
		
		// on sauvegarde les coordonnées de géolocalisation
		if(isset($data["results"]) && isset($data["results"][0]) && isset($data["results"][0]["geometry"]) && isset($data["results"][0]["geometry"]["location"])) {
			
			$infos = array();
			$infos['latitude'] = $data["results"][0]["geometry"]["location"]["lat"];
			$infos['longitude'] = $data["results"][0]["geometry"]["location"]["lng"];
			
			$this->enregistre_modele($infos);
		}
		
		return true;
	}
}