<?php

namespace App\Eden\Managements\Elements;

class Facture_achat_lignes_management extends Document_lignes_management {
	
	 /**
	 *
	 * On met à jour le statut des documents liés
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

       // on met à jour les stocks
        if($this->modele->type_element_source != 'bl_achat')
            $this->mise_a_jour_stocks();

    }

    /**
	 *
	 * On met à jour le statut des documents liés
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);

        // on met à jour les stocks
        if($this->modele->type_element_source != 'bl_achat')
            $this->mise_a_jour_stocks(false);
		
	}
}