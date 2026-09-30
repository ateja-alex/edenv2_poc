<?php

namespace App\Eden\Models\Elements;

class Avoir_achat extends Element {

    /**
	 *
     * Récupère les contacts du document
	 *
     */
    public function contacts() {
		
        return $this->belongsToMany('App\Eden\Models\Elements\Contact', 'avoir_achat_contacts_ids', 'cle_locale', 'valeur');
    }
	
	
	
}
