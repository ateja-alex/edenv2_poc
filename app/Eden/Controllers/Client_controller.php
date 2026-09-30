<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Models\Client;
use Mail;


use App\Eden\Managements\Elements\Client_management;


class Client_controller extends Controller {

	/**
	 * 
	 * Permet d'envoyer un nouveau mot de passe au client
	 * 
	 */
    public function envoyer_nouveau_mdp_au_client($id) {

    	// On récupère le client
    	$client_management = management('client', $id);

    	// On génère un nouveau mot de passe
    	$nouveau_mdp = $client_management->generer_nouveau_mdp();

    	// on va chercher l'utilisateur
		$client = Client::where('id', $id)->first();

		// on enregistre le nouveau mot de passe attribué au client
		$management = management('client', $id);
		$resultat = $management->enregistre_nouveau_mot_de_passe_ecommerce($nouveau_mdp);
		
		if($resultat === true) {

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 2,
                'expediteur' => [
                    'email' => config('eden.email_expediteur'),
                    'nom' => config('eden.email_nom_expediteur'),
                ],
                'destinataire' => [$client->adresse_email],
                'sujet' => traduction('messages.php.email.nouveau_mdp'),
            ];

            $variables_email = [
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'nouveau_mdp' => $nouveau_mdp
            ];

            // on envoie un email au client avec le nouveau mot de passe
            $retour = $service_email->envoyer('eden::mails.nouveau_mdp', $variables_email, $parametres_email);

            if($retour === false)
                return redirect()->back()->with('erreur', $retour);

			return redirect()->back()->with('succes', traduction('messages.php.email.envoi_succes'));
		}
		else {
			
			return redirect()->back()->with('erreur', $resultat);
		}

    }
	
}
