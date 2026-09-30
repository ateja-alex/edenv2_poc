<?php

namespace App\Eden\Managements\Elements;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Notification_management extends Element_management {
	
	/**
	 * 
	 * Enregistre le suivi pour l'utilisateur connecté pour l'élément passé en paramètre
	 * 
	 */
	public function active_suivi_element($management, $id_utilisateur = false) {

		if(empty($management->modele))
			return;
		
		if(empty($management->modele->id))
			return;
		
		if(empty(moi()))
			return;
		
		if($id_utilisateur === false)
			$id_utilisateur = moi()->id;

		$notification = modele('notification_element')->where('type_element', $management->_type_element)->where('element_id', $management->modele->id)->where('utilisateur_id', $id_utilisateur)->first();
		
		if($notification !== null)
			return;
		
		$notification_element = management('notification_element');
		
		$infos = array(
			
			'utilisateur_id' => $id_utilisateur,
			'element_id' => $management->modele->id,
			'type_element' => $management->_type_element,
		);

		$notification_element->enregistre($infos);
			

        return true;
	}
	
}

