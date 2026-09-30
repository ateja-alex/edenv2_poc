<?php

namespace App\Eden\Managements\Indicateurs;

class Client_encours extends Indicateur {

	/**
	 *
	 * On va calculer l'encours du client
	 *
	 */
	public function calcule($management) {
		
		if(in_array($management->_type_element, array('facture_vente', 'avoir_vente')))
			$management = management('client', $management->modele->client_id);
		
		$encours = modele('facture_vente')->where('client_id', $management->modele->id)->where('valide', 1)->sum('solde_document_ttc');
		$encours -= modele('avoir_vente')->where('client_id', $management->modele->id)->where('valide', 1)->sum('solde_document_ttc');
		
		$management->enregistre_modele(array('encours' => $encours));
		
		return true;
	}

	/**
	 *
	 * On va calculer le CA de tous les clients
	 *
	 */
	public function calcule_pour_tous() {

        $requete = \DB::select(\DB::raw("UPDATE client SET encours =
           (SELECT 
            (SELECT IFNULL(SUM(facture_vente.solde_document_ttc),0) FROM facture_vente where (inactif is null or inactif = 0) and client_id=client.id and valide = 1)
            - (SELECT IFNULL(SUM(avoir_vente.solde_document_ttc),0) FROM avoir_vente where (inactif is null or inactif = 0) and client_id=client.id and valide = 1)) WHERE (inactif is null or inactif = 0)"));
		
		return true;
	}

	
}
