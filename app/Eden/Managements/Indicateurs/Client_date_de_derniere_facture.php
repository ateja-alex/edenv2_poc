<?php

namespace App\Eden\Managements\Indicateurs;

class Client_date_de_derniere_facture extends Indicateur {

	/**
	 *
	 * On va calculer la date de la dernière facture du client
	 *
	 */
	public function calcule($management) {
		
		if(in_array($management->_type_element, array('facture_vente', 'avoir_vente')))
			$management = management('client', $management->modele->client_id);
		
		$derniere_facture = modele('facture_vente')->where('client_id', $management->modele->id)->where('valide', 1)->orderBy('date', 'DESC')->first();
		
		if($derniere_facture !== null)
			$management->enregistre_modele(array('date_de_derniere_facture' => $derniere_facture->date));
		else
			$management->enregistre_modele(array('date_de_derniere_facture' => '0000-00-00'));
		
		return true;
	}

	/**
	 *
	 * On va calculer le CA de tous les clients
	 *
	 */
	public function calcule_pour_tous() {

        $requete = \DB::select(\DB::raw("UPDATE client SET date_de_derniere_facture =
           (select max(date) from facture_vente
           where (inactif is null or inactif = 0) and facture_vente.client_id=client.id and facture_vente.valide = 1) WHERE (inactif is null or inactif = 0)"));

        return true;
	}

	
}
