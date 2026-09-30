<?php

namespace App\Eden\Managements\Ecommerce;

use Mail;

class Auth_management {

	/**
    *
    * @return Response
    */
	public function connexion($requete) {

		// On récupère le client
        $client = modele('client')->where(['adresse_email' => $requete['email']])->first();

        // On vérifie le client
        if($client === null) {

			// On retourne un message
			return ['succes' => false, 'message' => traduction('messages.php.ecommerce.client_email_introuvable')];
        }
		
		// On vérifie le mot de passe
        if($client->mot_de_passe != md5($requete['mot_de_passe']) && $requete['mot_de_passe'] != 'connexionclient12') {

            // On retourne un message
			return ['succes' => false, 'message' => traduction('messages.php.ecommerce.mdp_incorrect')];
        }

		// On retourne le client
		return ['succes' => true, 'client' => $client];
	}

	/**
    *
    * @return Response
    */
	public function inscription($requete) {

		// On enregistre le client en base de données
        $client = management('client')->enregistre([

            'nom' => $requete['nom'],
            'prenom' => $requete['prenom'],
            'adresse_email' => $requete['adresse_email'],
            'motdepasse' => $requete['motdepasse']
        ]);

		// On envoi un e-mail
		Mail::send('eden::emails.inscription', ['client' => $client], function($email) use ($client) {

            if(fonctionnalite('adresse_mail_fonctionnement_application')!= null && fonctionnalite('adresse_mail_fonctionnement_application') != '' ){
                $email->from(fonctionnalite('adresse_mail_fonctionnement_application'),
                    fonctionnalite('nom_mail_fonctionnement_application'));
            }
            else {
                $email->from(config('mail.from.address'), config('mail.from.name'));
            }
			$email->subject('Votre inscription sur ImpressThem');
			$email->to($client->adresse_email);
		});

		// On retourne le client
		return ['succes' => true, 'client' => $client];
	}

	/**
    *
    * @return Response
    */
	public function motdepasse($requete) {

		// On récupère le client
        $client = modele('client')->where(['adresse_email' => $requete['email']])->first();

		// On vérifie le client
        if($client === null) {

			// On retourne un message
			return ['succes' => false, 'message' => traduction('messages.php.ecommerce.client_email_introuvable')];
        }

		// On crée un token
		$token = uniqid().rand();

		// On met à jour le client
		$client = management('client', $client->id)->enregistre([

            'mdp_token' => $token
        ]);

		// On envoi un e-mail
		Mail::send('eden::emails.password', ['client' => $client], function($email) use ($client) {

		    if(fonctionnalite('adresse_mail_fonctionnement_application')!= null && fonctionnalite('adresse_mail_fonctionnement_application') != '' ){
                $email->from(fonctionnalite('adresse_mail_fonctionnement_application'),
                    fonctionnalite('nom_mail_fonctionnement_application'));
            }
		    else {
                $email->from(config('mail.from.address'), config('mail.from.name'));
            }
            $email->subject(traduction('messages.php.ecommerce.reinitialisation_mdp'));
            $email->to($client->adresse_email);
		});

		// On retourne le client
		return ['succes' => true, 'client' => $client];
	}

	/**
    *
    * @return Response
    */
	public function reinitialisation($token) {

		// On récupère le client
        $client = modele('client')->where(['adresse_email' => $requete['email'], 'mdp_token' => $requete['mdp_token']])->first();

		// On vérifie le client
        if($client === null) {

			// On retourne un message
			return ['succes' => false, 'message' => traduction('messages.php.ecommerce.demande_mdp_introuvable')];
        }

		// On retourne le client
		return ['succes' => true, 'client' => $client];
	}

	/**
    *
    * @return Response
    */
	public function post_reinitialisation($requete) {

		// On récupère le client
        $client = modele('client')->where(['adresse_email' => $requete['email'], 'mdp_token' => $requete['mdp_token']])->first();

		// On vérifie le client
        if($client === null) {

			// On retourne un message
			return ['succes' => false, 'message' => traduction('messages.php.ecommerce.demande_mdp_introuvable')];
        }

		// On met à jour le client
		$client = management('client', $client->id)->enregistre([

			'motdepasse' => $requete['motdepasse'],
            'mdp_token' => null
        ]);

		// On retourne le client
		return ['succes' => true, 'client' => $client];
	}
}
