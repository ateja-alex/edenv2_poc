<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Models\Elements\Utilisateur_easydev;
use App\Eden\Models\Elements\Utilisateur;

/**
 * Gestion des fiches ticket
 */
class Fiche_ticket_management extends Fiche_management {

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

        $donnees['ticket_message'] = modele('ticket_message')->where('ticket_id',$donnees['id_element'])->get();
       
        
        foreach ($donnees['ticket_message'] as $item) {

                if($item->easydev == 1) {

                    $item->utilisateur = Utilisateur_easydev::find($item->cree_par);
                }
                else {

                    $item->utilisateur = modele('utilisateur', $item->cree_par);
                }
        }

		return $donnees;
	}
}
