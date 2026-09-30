<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Menus_liens_management extends Element_management {

	public $suppression_categorie = false;

	public function enregistre($modifications = array(), $modele = false) {

        if($modifications['type_lien'] == 1)
			$modifications['route'] = 'base_eden.liste.index';
		else if($modifications['type_lien'] == 2)
			$modifications['route'] = 'base_eden.rapport.index';

        return parent::enregistre($modifications, $modele);

    }

	/**
	 *
	 * On régénére le composant menus
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		if(defined('migration_en_cours'))
			return;

        		Cache_management::genere_menus();
	}

	/**
	 *
	 * On supprime les liens associés et régénère le composant menus
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);

		if(defined('migration_en_cours'))
			return;

		if(!$this->suppression_categorie) {
			
						Cache_management::genere_menus();
		}
	}
}