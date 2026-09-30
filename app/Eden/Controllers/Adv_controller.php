<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;




class Adv_controller extends Controller {

	/**
	 * 
	 * Accueil de l'Administration Des Ventes
	 * 
	 */
    public function index() {
		
		$listes = array();
		
		$listes['devis_vente_lignes'] = service('page_adv')->informations_pour_liste('devis_vente_lignes', 'devis_vente_lignes_a_commander');
		$listes['commande_vente_lignes'] = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_recapitulatif');
		$listes['commande_vente_lignes_a_commander'] = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_a_commander');
		$listes['commande_vente_lignes_a_recevoir'] = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_a_recevoir');
		$listes['commande_vente_lignes_a_livrer'] = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_a_livrer');
		$listes['bon_preparation_vente_lignes_a_expedier'] = service('page_adv')->informations_pour_liste('bon_preparation_vente_lignes', 'bon_preparation_vente_lignes_a_expedier');
		$listes['bl_vente_lignes_a_facturer'] = service('page_adv')->informations_pour_liste('bl_vente_lignes', 'bl_vente_lignes_a_facturer');
		$listes['commande_achat_lignes_a_recevoir'] = service('page_adv')->informations_pour_liste('commande_achat_lignes', 'commande_achat_lignes_a_recevoir');
		$listes['bl_achat_lignes_recues'] = service('page_adv')->informations_pour_liste('bl_achat_lignes', 'bl_achat_lignes_recues');

		
		return view('eden::adv', array('listes' => $listes));
    }

    /**
	 * 
	 * On recharge les données du rapport voulu
	 * 
	 */
    public function recuperer_rapport($nom_liste) {
		
		$rapport = array();
		
		if ($nom_liste == "devis_vente_lignes_a_commander")
			$rapport = service('page_adv')->informations_pour_liste('devis_vente_lignes', 'devis_vente_lignes_a_commander');

		if ($nom_liste == "commande_vente_lignes_recapitulatif")
			$rapport = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_recapitulatif');

		if ($nom_liste == "commande_vente_lignes_a_commander")
			$rapport = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_a_commander');

		if ($nom_liste == "commande_vente_lignes_a_recevoir")
			$rapport = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_a_recevoir');

		if ($nom_liste == "commande_vente_lignes_a_livrer")
			$rapport = service('page_adv')->informations_pour_liste('commande_vente_lignes', 'commande_vente_lignes_a_livrer');

		if ($nom_liste == "bl_vente_lignes_a_facturer")
			$rapport = service('page_adv')->informations_pour_liste('bl_vente_lignes', 'bl_vente_lignes_a_facturer');
		
		if ($nom_liste == "commande_achat_lignes_a_recevoir")
			$rapport = service('page_adv')->informations_pour_liste('commande_achat_lignes', 'commande_achat_lignes_a_recevoir');
		
		if ($nom_liste == "bon_preparation_vente_lignes_a_expedier")
			$rapport = service('page_adv')->informations_pour_liste('bon_preparation_vente_lignes', 'bon_preparation_vente_lignes_a_expedier');
		
		return response()->json(['rapport' => $rapport]);
    }
	
	
	
	
	

    
}
