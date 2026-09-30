<?php

namespace App\Eden\Managements\Elements;

class Acompte_management extends Document_management {

    /**
	 *
	 * Vérifie si on doit taguer l'acompte comme annulée partiellement par avoir
	 *
	 */
	public function verifie_tag_facture_avoir_partiel() {

		// on va chercher la facture liée à l'avoir
		if($this->est_un_achat())
			$avoirs = $this->documents_lies('avoir_achat');
		else
			$avoirs = $this->documents_lies('avoir_vente');

		$montant_des_avoirs = 0;

		foreach($avoirs as $avoir) {

			if(!empty($avoir['management']->modele->inactif))
				continue;

			$montant_des_avoirs += $avoir['management']->modele->montant_document_ttc;
		}

		if(empty($montant_des_avoirs)) {

			$this->enregistre_modele(array('avoir_partiel' => 0,'avoir_total' => 0));
		}
		elseif($montant_des_avoirs < $this->modele->montant_document_ttc)
			$this->enregistre_modele(array('avoir_partiel' => 1,'avoir_total' => 0));
		else
			$this->enregistre_modele(array('avoir_partiel' => 0,'avoir_total' => 1));
	}

}
