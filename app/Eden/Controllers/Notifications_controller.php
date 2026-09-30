<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class Notifications_controller extends Controller {

	/**
	 * 
	 * Permet de récupérer les notifications (via ajax) 
	 * 
	 */
    public function recuperer(Request $formulaire) {
		
		if(is_array($formulaire->zone)) {
			
			$notifications = array();
			
			foreach($formulaire->zone as $zone) {
				
				$notifications[$zone] = modele('notification')
				->where('utilisateur_id', id_utilisateur)
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
			}
			
		}
		else {
			
			$notifications = modele('notification')
				->where('utilisateur_id', id_utilisateur)
				->where('zone', $formulaire->zone)
				->whereNull('vue')
				->where('date', '<=', date('Y-m-d H:i:s'))
				->orderBy('date', 'desc')
				->get();
				
			foreach($notifications as $notification) {
				
				if(!empty($notification->notification_flux_id)) {
					
					$flux = modele('notification_flux', $notification->notification_flux_id);
					
					$notification->tag = $flux->nom;
					$notification->couleur_tag = $flux->couleur_tag;
					$notification->couleur_tag_police = $flux->couleur_tag_police;
				}
			}
		}
		
		
		return response()->json($notifications);
    }

	/**
	 * 
	 * Permet d'enregistrer les notifications comme vues (ajax)
	 * 
	 */
    public function enregistrer_comme_vues(Request $formulaire) {
		
		if($formulaire->notifications === null || !is_array($formulaire->notifications))
			return response()->json(true);
		
		$notifications = modele('notification')
			->where('utilisateur_id', id_utilisateur)
			->whereIn('id', $formulaire->notifications)
			->get();
			
		foreach($notifications as $notification) {
			
			$notification_management = management('notification', $notification->id, $notification);
			
			$notification_management->enregistre(array('vue' => 1));
		}
		
		return response()->json(true);
    }

	/**
	 * 
	 * Permet d'activer le suivi d'un élément pour l'utilisateur connecté
	 * 
	 */
    public function activer_suivi_element(Request $formulaire) {
		
		$notification = modele('notification_element')->where('type_element', $formulaire->type_element)->where('element_id', $formulaire->element_id)->where('utilisateur_id', id_utilisateur)->first();
		
		if($notification !== null) {
			
			$notification->delete();
			
			return response()->json(0);
		}
		else {
			
			$management_element_suivi = management($formulaire->type_element, $formulaire->element_id);
			
			management('notification')->active_suivi_element($management_element_suivi);
			
			return response()->json(1);
		}
		
    }

	/**
	 * 
	 * Permet de savoir si l'élément est suivi pour l'utilisateur connecté
	 * 
	 */
    public function recuperer_suivi_element(Request $formulaire) {
		
		$notification = modele('notification_element')->where('type_element', $formulaire->type_element)->where('element_id', $formulaire->element_id)->where('utilisateur_id', id_utilisateur)->first();
		
		if($notification === null) {
			
			return response()->json(0);
		}
		else {
			
			return response()->json(1);
		}
		
    }
}
