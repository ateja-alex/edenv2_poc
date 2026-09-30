<?php

namespace App\Eden\Managements\Services;

class Indicateurs_service {

	/**
	 *
	 * On va retourner les indicateurs activés pour un type élément donné
	 *
	 */
	public function indicateurs($type_element) {
		
		$indicateurs = array();
		
		if($type_element == 'client') {
			
			if(config('indicateurs.client_ca') === true)
				$indicateurs[] = 'client_ca';
			
			if(config('indicateurs.client_ca_12_mois_glissants') === true)
				$indicateurs[] = 'client_ca_12_mois_glissants';
			
			if(config('indicateurs.client_ca_depuis_janvier') === true)
				$indicateurs[] = 'client_ca_depuis_janvier';
			
			if(config('indicateurs.client_date_de_derniere_facture') === true)
				$indicateurs[] = 'client_date_de_derniere_facture';
			
			if(config('indicateurs.client_date_de_dernier_echange') === true)
				$indicateurs[] = 'client_date_de_dernier_echange';
			
			if(config('indicateurs.client_encours') === true)
				$indicateurs[] = 'client_encours';
		}
		
		if(in_array($type_element, array('facture_vente', 'avoir_vente'))) {
			
			if(config('indicateurs.client_ca') === true)
				$indicateurs[] = 'client_ca';
			
			if(config('indicateurs.client_ca_12_mois_glissants') === true)
				$indicateurs[] = 'client_ca_12_mois_glissants';
			
			if(config('indicateurs.client_ca_depuis_janvier') === true)
				$indicateurs[] = 'client_ca_depuis_janvier';
			
			if(config('indicateurs.client_encours') === true)
				$indicateurs[] = 'client_encours';
		}
		
		if(in_array($type_element, array('facture_vente'))) {
			
			if(config('indicateurs.client_date_de_derniere_facture') === true)
				$indicateurs[] = 'client_date_de_derniere_facture';
		}
		
		if(in_array($type_element, array('echange'))) {
			
			if(config('indicateurs.client_date_de_dernier_echange') === true)
				$indicateurs[] = 'client_date_de_dernier_echange';
		}
		
		// les temps de transfo commerciale sur les projets
		if(in_array($type_element, array('projet', 'devis_vente'))) {
			
			if(config('indicateurs.temps_transfo_premier_devis') === true)
				$indicateurs[] = 'temps_transfo_premier_devis';
			
			if(config('indicateurs.delai_reponse_client') === true)
				$indicateurs[] = 'delai_reponse_client';
		}
	
		return $indicateurs;
	}

	
}
