<?php

namespace App\Eden\Managements\Elements;

class Maintenance_management extends Element_management {

	/**
	 * 
	 * Génère la prochaine intervention annuelle de maintenance
	 * 
	 */
	public function genere_intervention() {
		
		$maintenance_intervention = management('maintenance_intervention');
		
		// on va créer la nouvelle date
		$date = $this->prochaine_date_intervention();
		
		if($date === false)
			return;
		
		// on met à jour la maintenance avec la prochaine date d'intervention
		// on ne crée pas la 1ere intervention, on suppose que c'est l'installation
		if(empty($this->modele->prochaine_date_maintenance)) {
			
			// si la date est dans le passé, on n'ajoute pas 1 an
			if($this->modele->date_debut <= date('Y-m-d')) {
								
				$infos = array('prochaine_date_maintenance' => $date);
			}
			else {
				
				$infos = array('prochaine_date_maintenance' => date('Y-m-d', strtotime($date.' +1 year')));
			}
			
		}
		else {
			
			$infos = array(
				
				'maintenance_id' => $this->modele->id,
				'date' => $date,
				'statut' => 1,
			);
			
			$retour = $maintenance_intervention->enregistre($infos);
			
			if($retour !== true)
				return $retour;
			
			$infos = array('prochaine_date_maintenance' => date('Y-m-d', strtotime($date.' +1 year')));
		}
		
		$this->enregistre($infos);
	}
	
	/**
	 * 
	 * Retourne la prochaine date d'intervention
	 * 
	 */
	public function prochaine_date_intervention() {
		
		// on part de la date de début
		$date = $this->modele->date_debut;
		
		// si elle est >= à aujourd'hui, on garde celle ci
		if($date >= date('Y-m-d'))
			return $date;
		
		// la date de début est passée, on va donc chercher la 1ere date N+1
		for($i=1; $i<=100; $i++) {
			
			$date = date('Y-m-d', strtotime($date.' +1 year'));
			
			if($date >= date('Y-m-d'))
				return $date;
		}
		
		return false;
	}

}
