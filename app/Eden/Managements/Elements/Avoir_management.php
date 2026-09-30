<?php

namespace App\Eden\Managements\Elements;

class Avoir_management extends Document_management {

	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {
		
		parent::methodes_post_suppression($modele);
		
		$this->verifie_tag_facture_avoir_partiel();
	}
	
	/**
	 *
	 * 
	 *
	 */
	protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification_document($modele, $modele_avant, $modifications);
		
		$this->verifie_tag_facture_avoir_partiel();
	}
	
	/**
	 * 
	 * Vérifie si on doit taguer la facture comme annulée partiellement par avoir
	 * 
	 */
	public function verifie_tag_facture_avoir_partiel() {

		// on va chercher la facture liée à l'avoir
		if($this->est_un_achat())
			$factures = $this->documents_lies('facture_achat');
		else
			$factures = $this->documents_lies('facture_vente');
		
		foreach($factures as $facture) {
			
			$facture['management']->verifie_tag_facture_avoir_partiel();
		}
		
		return true;
	}
}
