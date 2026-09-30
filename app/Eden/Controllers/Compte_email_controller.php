<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class Compte_email_controller extends Controller {

	/**
	 * 
	 * Permet de valider un compte email afin de pouvoir l'utiliser
	 * 
	 */
    public function valider($id, $token) {
		
		$compte_email = modele('compte_email', $id);
		
		if($compte_email->token == $token) {
			
			management('compte_email', $id)->enregistre(array('valide' => 1));
		}
		
		return redirect()->route('base_eden.accueil.index');
    }
	
	
    
}
