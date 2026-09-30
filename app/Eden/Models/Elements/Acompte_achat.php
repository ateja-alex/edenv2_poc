<?php

namespace App\Eden\Models\Elements;

class Acompte_achat extends Element {

    /**
	 *
     * Récupère les contacts du document
	 *
     */
    public function contacts() {
		
        return $this->belongsToMany('App\Eden\Models\Elements\Contact', 'acompte_vente_contacts_ids', 'cle_locale', 'valeur');
    }


   
	
}
