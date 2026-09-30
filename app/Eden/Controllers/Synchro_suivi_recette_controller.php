<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;


class Synchro_suivi_recette_controller extends Controller {

    public function recois_modifications_ticket(){

		define('api_ticket', 1);

    	$modifications = request()->modifications;

		if(isset($modifications['projet_id']))
			unset($modifications['projet_id']);
		if(isset($modifications['estimation_temps']))
			unset($modifications['estimation_temps']);
		

    	// On vérifie si le ticket existe
    	if(empty(request()->ticket_id) || empty(modele('suivi_recette_easydev', request()->ticket_id))) {

    		// Nouveau ticket
    		$ticket = management('suivi_recette_easydev');

    	} else {

    		// On charge le ticket à modifier
    		$ticket = management('suivi_recette_easydev', request()->ticket_id);
    	}


        // on transforme le tableau d'adresses email en ID
		if(isset($modifications['en_charge'])) {
			
			$nouveau_en_charge = array();
			
			foreach($modifications['en_charge'] as $adresse_email) {
				
				$utilisateur = modele('utilisateur')->where('email', $adresse_email)->first();
				
				if(empty($utilisateur))
					continue;
				
				$nouveau_en_charge[] = $utilisateur->id;
			}
			
			$modifications['en_charge'] = $nouveau_en_charge;
		}

        // On applique les modifications
    	$retour = $ticket->enregistre($modifications);

        // Et on retourne le ticket_id.
    	return (json_encode(['success' => $retour, 'ticket_id' => $ticket->modele->id, 'modifications' => $modifications ]));

    }

    public function recois_ticket_echange(){

        define('api_ticket', 1);
        define('api_recette_echange', 1);

        $modifications = request()->modifications;
		
		$email = false;
		
		// on vire les champs inutiles
		if(isset($modifications['email_utilisateur'])) {
			
			$email = $modifications['email_utilisateur'];
			
			unset($modifications['email_utilisateur']);
		}

        // On applique les modifications
		$management = management('suivi_recette_easydev_echange');
		
        $retour = $management->enregistre($modifications);
		
		if($retour === true && $email) {
			
			$utilisateur = modele('utilisateur')->where('email', $email)->first();
			
			if($utilisateur) {
				
				$modele = modele('suivi_recette_easydev_echange', $management->modele->id);
				
				$modele->cree_par = $utilisateur->id;
				$modele->modifie_par = $utilisateur->id;
				$modele->save();
			}
		}

        // Et on retourne le ticket_id.
        return (json_encode(['success' => $retour]));

    }

}
