<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;

use DB;

/**
 * Gestion des fiches fournisseurs
 */
class Fiche_suivi_recette_easydev_management extends Fiche_management {

	/**
	 * 
	 * Prépare les données pour la fiche
	 * 
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);
		
		$donnees['echanges'] = modele('suivi_recette_easydev_echange')
									->where('suivi_recette', $donnees['id_element'])
									->select(
										DB::raw(
											"CONCAT(utilisateur.prenom,' ',utilisateur.nom) as auteur,
											utilisateur.*,
											suivi_recette_easydev_echange.*"
										)
									)
									->join('utilisateur', 'suivi_recette_easydev_echange.cree_par', 'utilisateur.id')
									->orderBy('suivi_recette_easydev_echange.cree_le')
									->get();
									
		foreach ($donnees['echanges'] as $echange) {
			$echange['cree_le'] = 'le '.formate_date('d/m/Y à H:i', $echange['cree_le']);
			$echange['message'] = nl2br($echange['message']);
		}
		
		return $donnees;
	}
}
