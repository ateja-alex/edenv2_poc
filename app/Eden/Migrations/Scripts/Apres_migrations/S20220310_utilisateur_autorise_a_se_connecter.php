<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220310_utilisateur_autorise_a_se_connecter implements Script {
	
	public function execute() {
		
		$utilisateurs = modele('utilisateur')->get();
		
		foreach($utilisateurs as $utilisateur) {
			
			if($utilisateur->type_utilisateur == 3) {
				
				$utilisateur->autorise_a_se_connecter = 0;
				$utilisateur->save();
			}
			else {
				
				$utilisateur->autorise_a_se_connecter = 1;
				$utilisateur->save();
			}
		}
		
		return true;
	}
}