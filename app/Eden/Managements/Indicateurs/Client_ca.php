<?php

namespace App\Eden\Managements\Indicateurs;

class Client_ca extends Indicateur {
	
	/**
	 *
	 * On va calculer le CA du client
	 *
	 */
	public function calcule($management) {
		
		if(in_array($management->_type_element, array('facture_vente', 'avoir_vente')))
			$management = management('client', $management->modele->client_id);
		
		$ca = modele('facture_vente')->where('client_id', $management->modele->id)->where('valide', 1)->sum('montant_document_ht');
		$ca -= modele('avoir_vente')->where('client_id', $management->modele->id)->where('valide', 1)->sum('montant_document_ht');
		
		$management->enregistre_modele(array('ca' => $ca));
		
		return true;
	}

	/**
	 *
	 * On va calculer le CA de tous les clients
	 *
	 */
	public function calcule_pour_tous() {

        $requete = \DB::select(\DB::raw("UPDATE client SET ca =
           (SELECT 
            (SELECT IFNULL(SUM(facture_vente.montant_document_ht),0) FROM facture_vente where (inactif is null or inactif = 0) and client_id=client.id and valide = 1)
            - (SELECT IFNULL(SUM(avoir_vente.montant_document_ht),0) FROM avoir_vente where (inactif is null or inactif = 0) and client_id=client.id and valide = 1)) WHERE (inactif is null or inactif = 0)"));

        return true;
	}

	
}
