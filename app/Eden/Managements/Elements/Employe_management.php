<?php

namespace App\Eden\Managements\Elements;


class Employe_management extends Element_management {

	/**
	 *
	 * @cf Element_management::retraite_modifications()
	 *
	 */
	protected function retraite_modifications($modifications) {

		// On vérifie si le mot de passe a été saisi
		if(isset($modifications['mot_de_passe'])) {
			
			// Si le mot de passe n'est pas déjà crypté
			if(strlen($modifications['mot_de_passe']) != 32) {
				
				// On crypte le mot de passe
				$modifications['mot_de_passe'] = md5('easy'.$modifications['mot_de_passe'].'dev');
			}
		}
		
		return parent::retraite_modifications($modifications);
	}
}
