<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Menus_categories_management extends Element_management {

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

		$liens = modele('menus_liens')->where('id_categorie_parent', $modele->id)->get();
		$management_liens = management('menus_liens');
		$management_liens->suppression_categorie = true;

		foreach($liens as $lien) {

			$management_liens->modele = $lien;
			$management_liens->supprime();
		}

		if(defined('migration_en_cours'))
			return;

		Cache_management::genere_menus();
	}
}