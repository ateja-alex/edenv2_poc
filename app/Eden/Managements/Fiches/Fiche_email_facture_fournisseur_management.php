<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;


/**
 *
 * Gestion des fiches de saisie des factures fournisseurs via la banette (mails reçus de fournisseurs)
 *
 */
class Fiche_email_facture_fournisseur_management extends Fiche_management {

	/**
	 * 
	 * Vérifie les données envoyées via le formulaire pour la création de la facture d'achat
	 * 
	 */
	public function verifie_informations_pour_saisie_facture_achat($donnees) {
		
		return true;
	}
	
	/**
	 * 
	 * Complète les informations qui seront enregistrées via le formulaire pour la création de la facture d'achat
	 * 
	 * Prévu pour gérer le spécifique
	 * 
	 */
	public function complete_informations_pour_saisie_facture_achat($donnees, $facture_achat) {
		
		return $facture_achat;
	}
}
