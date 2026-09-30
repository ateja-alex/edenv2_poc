<?php

namespace App\Eden\Models\Elements;

class Devis_vente extends Element {

    /**
	 *
     * Récupère les contacts du document
	 *
     */
    public function contacts() {
		
        return $this->belongsToMany('App\Eden\Models\Elements\Contact', 'devis_vente_contacts_ids', 'cle_locale', 'valeur');
    }


   
	
}
