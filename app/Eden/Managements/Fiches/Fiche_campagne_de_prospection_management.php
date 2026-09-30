<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Variables;

use DB;

/**
 * Gestion des fiches campagne de prospection
 */
class Fiche_campagne_de_prospection_management extends Fiche_management {
	
	/**
	 * 
	 * On retouche le champ utilisateurs
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

        // on récupère les modules utilisés pour optimiser
        $modules = $this->modules_utilises();

        $campagne_de_prospection_management = management('campagne_de_prospection', $this->id_element);

        $donnees['heures_details'] = $campagne_de_prospection_management->heures_details();
		
		if(empty($donnees['campagne_de_prospection']->utilisateurs)) {
			
			$donnees['campagne_de_prospection']->utilisateurs = array();
			$donnees['management_element']->modele->utilisateurs = array();
		}
		else {
			
			// Note Thibaut : Cette ligne fait planter la page si un utilisateur est sélectionné
			// $donnees['campagne_de_prospection']->utilisateurs = json_decode($donnees['campagne_de_prospection']->utilisateurs);
			$donnees['management_element']->modele->utilisateurs = $donnees['campagne_de_prospection']->utilisateurs;
		}
		
		$campagne = $donnees['campagne_de_prospection'];
		
		$clients = array();
		
		$clients[0] = modele('campagne_de_prospection_client')->where('campagne_de_prospection_id', $campagne->id)->where(function($requete) {
			
				$requete->where('utilisateur_id', 0)->orWhereNull('utilisateur_id');
			})->get();
		
		if(is_array($campagne->utilisateurs)) {
			
			foreach($campagne->utilisateurs as $id_utilisateur) {
				
				$clients[$id_utilisateur] = modele('campagne_de_prospection_client')->where('campagne_de_prospection_id', $campagne->id)->where('utilisateur_id', $id_utilisateur)->get();
			}
		}
		
		$campagne->clients = $clients;
		
		$donnees['campagne_de_prospection'] = $campagne;

        if(in_array('indicateurs', $modules)) {

            $donnees['indicateurs'] = $this->indicateurs();
        }
		
		return $donnees;
	}

    public function indicateurs() {

        // on ajoute le nombre d'heures réalisées sur ce projet
        $heures_realisees = modele('feuille_de_temps')
            ->where('type_element', 'campagne_de_prospection')
            ->where('element_id', $this->id_element)->sum('duree');

        return array('heures_realisees' => $heures_realisees);
    }
}
