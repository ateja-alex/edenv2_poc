<?php

namespace App\Eden\Managements\Services;

use App\Eden\Models\Champ_libre;
use Mail;

class Notifications_service {
	
	/**
	 * 
	 * Retourne le texte lorsqu'un utilisateur est mentionné dans un textarea
	 * 
	 */
	public function texte_notification_mention() {
		
		if(moi() !== null) {
			
			$message = moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. vient de <b>vous mentionner</b> : ';
		}
		else {
			
			$message = 'Vous venez d\'être <b>mentionné</b> : ';
		}
		
		return $message;
	}


	/**
	 * 
	 * Retourne les notifications de l'utilisateur
	 * 
	 */
	public function notifications_pour_zone($zone) {
		
		$notifications[$zone] = modele('notification')
			->where('utilisateur_id', moi()->id)
			->where('zone', $zone)
			->whereNull('vue')
			->where('date', '<=', date('Y-m-d H:i:s'))
			->orderBy('date', 'desc')
			->get();
				
		foreach($notifications[$zone] as $notification) {
					
			if(!empty($notification->notification_flux_id)) {
						
				$flux = modele('notification_flux', $notification->notification_flux_id);
					
				$notification->tag = $flux->nom;
				$notification->couleur_tag = $flux->couleur_tag;
				$notification->couleur_tag_police = $flux->couleur_tag_police;
			}
		}

		return json_encode($notifications[$zone]);
	}
	
}
