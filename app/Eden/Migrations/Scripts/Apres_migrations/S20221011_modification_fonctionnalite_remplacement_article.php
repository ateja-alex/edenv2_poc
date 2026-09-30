<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Managements\Script_management;

class S20221011_modification_fonctionnalite_remplacement_article implements Script {

    public function execute() {
		
		$documents = \App\Eden\Variables::$documents_gescom;
		
		$nouvelles_valeurs = array();
		
		foreach($documents as $type_element) {
			
			$nouvelles_valeurs[$type_element] = false;
		}
	
        Script_management::modifier_fonctionnalites(array(
			
			'gescom_remplacement_article' => $nouvelles_valeurs,
		));

        return true;
    }
}

