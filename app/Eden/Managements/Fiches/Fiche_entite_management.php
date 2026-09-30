<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Parametres_erp_management;

use DB;

/**
 * Gestion des fiches fournisseurs
 */
class Fiche_entite_management extends Fiche_management {
	
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
		
		// les paramètres
		$donnees['parametres'] = $this->parametres();

		
		return $donnees;
	}
	
	/**
	 * 
	 * Retourne les parametres liés à l'entité (notamment pour la gescom)
	 * 
	 * @return collection
	 * 
	 */
	public function parametres() {
		
		$liste_parametres = array(
		
			$this->id_element => array(
				
				// cgv et cga
				'cgv_sur_devis_vente',
				'cgv_sur_commande_vente',
				'cgv_sur_facture_vente',
				'cgv_sur_bl_vente',
				'cgv_sur_acompte_vente',
				'cgv_sur_avoir_vente',
                'cgv_sur_bon_retour_vente',
                'cgv_sur_bon_preparation_vente',

				'cga_sur_devis_vente',
				'cga_sur_commande_vente',
				'cga_sur_facture_vente',
				'cga_sur_bl_vente',
				'cga_sur_acompte_vente',
				'cga_sur_avoir_vente',
                'cga_sur_bon_retour_vente',
                'cga_sur_bon_preparation_vente',
				
				// numérotation des documents
				'numerotation_devis_vente',
				'numerotation_commande_vente',
				'numerotation_bl_vente',
				'numerotation_acompte_vente',
				'numerotation_facture_vente',
				'numerotation_avoir_vente',
                'numerotation_bon_retour_vente',
                'numerotation_bon_preparation_vente',
				'numerotation_devis_achat',
				'numerotation_commande_achat',
				'numerotation_bl_achat',
				'numerotation_acompte_achat',
				'numerotation_facture_achat',
				'numerotation_avoir_achat',
				'numerotation_bon_retour_achat',

				// autres parametres
				'compte_bancaire_defaut',
			),
		);
		
		$parametres = Parametres_erp_management::liste($liste_parametres);
		
		return collect($parametres);
	}
	
	
	
}
