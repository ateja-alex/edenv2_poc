<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Profil_droits_divers_management extends Element_management{

    /**
	 *
	 * On régénére le composant menus
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		if(defined('migration_en_cours'))
			return;

        Cache_management::invalide();
	}
}