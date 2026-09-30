<?php

namespace App\Eden\Managements\Elements;
use App\Eden\Managements\Cache_management;

class Compte_bancaire_management extends Element_management {
	
	/**
	*
	* On ajoute le lien de MAJ CB stripe
	*
	*/
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
        Cache_management::vider();
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}
}