<?php

namespace App\Eden\Managements;

use Log;
use Cookie;


class Intranet_management {
	
	/**
	 * 
	 * Gère la connexion d'un employe
	 * 
	 */
	
	public function connexion($email, $mot_de_passe) {
		
		$employe = modele('utilisateur')->where('email', $email)->first();
		
		// S'il n'y a aucun employe qui porte cette adresse mail

		if($employe === null)
			
			return array(false, traduction('messages.php.connexion.email_introuvable'));
		
		// Si le mot de passe ne correspond pas 
		
		if($employe->mot_de_passe != md5('easy'.$mot_de_passe.'dev'))
			
			return array(false, traduction('messages.php.connexion.mdp_incorrect'));

		return array(true, $employe);
	}

	/**
	 * 
	 * Gère la déconnexion d'un utilisateur
	 * 
	 */
	
	public function deconnexion() {
		
		// on supprime le cookie

		Cookie::queue(Cookie::forget('remember_token'));
		
		// on vide la session

		session()->flush();
		
		return true;
	}



    
}