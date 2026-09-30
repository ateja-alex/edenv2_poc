<?php

namespace App\Eden\Managements\Indicateurs;

class Temps_transfo_premier_devis extends Indicateur {

	/**
	 *
	 * On va calculer le temps de transformation
	 *
	 */
	public function calcule($management) {
		
		
		if(in_array($management->_type_element, array('devis_vente'))) {
			
			// le devis n'est pas lié à un projet
			if(empty($management->modele->projet_id))
				return true;
			
			$management = management('projet', $management->modele->projet_id);
		}
		
		// date de création du projet
		$date_creation = $management->modele->cree_le;
		
		// date du premier devis
		$devis = modele('devis_vente')->sans_profils()->where('projet_id', $management->modele->id)->first();
		
		// pas de devis sur le projet
		if($devis === null) {
			
			$management->enregistre_modele(array('temps_transfo_premier_devis' => null));
			
			return true;
		}
		
		// différence entre les deux
		$difference = strtotime($devis->cree_le) - strtotime($management->modele->cree_le);
		
		// $difference /= (60*60);
		$difference /= (60*60*24);
		
		$management->enregistre_modele(array('temps_transfo_premier_devis' => round($difference)));
		
		return true;
	}

	/**
	 *
	 * On va calculer le CA de tous les clients
	 *
	 */
	public function calcule_pour_tous() {
		
		return $this->calcul_individuel_standard('projet');
	}

	
}
