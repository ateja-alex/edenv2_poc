<?php

namespace App\Eden\Managements\Elements;

class Facture_management extends Document_management {

	/**
	 * 
	 * Vérifie si on doit taguer la facture comme annulée partiellement par avoir
	 * 
	 */
	public function verifie_tag_facture_avoir_partiel() {

		$type_element = $this->est_un_achat() ? 'avoir_achat' : 'avoir_vente';
		$type_element_source = $this->est_un_achat() ? 'facture_achat' : 'facture_vente';
		
		// on va chercher la facture liée à l'avoir
		$avoirs = $this->documents_lies($type_element);

		$articles_par_avoir = modele($type_element.'_lignes')
			->where('type_element_source',$type_element_source)
			->where('id_element_source',$this->modele->id)->get()->groupBy('document_id');
		
		$montant_des_avoirs = 0;
		
		foreach($avoirs as $avoir) {
			
			if(!empty($avoir['management']->modele->inactif) || empty($articles_par_avoir[$avoir['management']->modele->id]))
				continue;
			
			$montant_des_avoirs += $avoir['management']->calcule_total_document($articles_par_avoir[$avoir['management']->modele->id])['ttc'] ?? 0;	
		}
		
		if(empty($montant_des_avoirs)) {
			
			$this->enregistre_modele(array('avoir_partiel' => 0,'avoir_total' => 0));
		}
		elseif($montant_des_avoirs < $this->modele->montant_document_ttc)
			$this->enregistre_modele(array('avoir_partiel' => 1,'avoir_total' => 0));
		else
			$this->enregistre_modele(array('avoir_partiel' => 0,'avoir_total' => 1));
	}

	/**
	 *
	 * Retouche les informations de la facture générée lors de la transforamtion d'un BL ou d'une commande en facture
	 *
	 * Cette méthode, à surcharger, sert notamment à ajouter les champs obligatoires
	 *
	 */
	public function retouche_infos_pour_facturation_depuis_autre_document($infos) {

		return $infos;
	}
}
