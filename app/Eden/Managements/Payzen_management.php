<?php

namespace App\Eden\Managements;

use Lyra;

class Payzen_management {
	
	public function __construct(){

		
    }

	/**
	 *
	 * Contrôle les entrées utilisateurs
	 *
	 */
	public function controler_entrees_utilisateur($formulaire) {

		// Plus utile, a priori

		$statut = true;
		$message = "Contrôle réalisé avec succès";
		$retour = null;/*

		if($formulaire['ccard-holder'] === null) {

			$statut = false;
			$message = "Echec du contrôle.";
			$retour = 'Veuillez indiquer le titulaire du compte.';
		} elseif($formulaire['iban'] === null) {

			$statut = false;
			$message = "Echec du contrôle.";
			$retour = 'Veuillez indiquer le RIB.';
		}*/

		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}

	/**
	 *
	 * Crée un client chez Payzen
	 *
	 */
	public function creer_client($client, $informations_carte){
		/*
		$statut = true;
		$message = "Compte enregistré avec succès";
		$retour = null;

		if(config('app.projet') == '') {

			$statut = false;
			$message = "Echec de l'enregistrement du compte (erreur #1)";
		} 
		else {
			
			$nouveau_portefeuille = config('app.projet') . '-' . $client->id.'-'.date('YmdHis');

			// On défini les codes d'accès
			Lyra\Client::setDefaultUsername("92031954");
			Lyra\Client::setDefaultPassword("testpassword_tIIY2YNYZuqyyAkF63E2OLlEZuHhSidfJd4S5yvN6cHJ4");
			Lyra\Client::setDefaultEndpoint("https://api.payzen.eu");
			// publicKey and used by the javascript client 
			Lyra\Client::setDefaultPublicKey("69876357:testpublickey_DEMOPUBLICKEY95me92597fd28tGD4r5");
			// SHA256 key 
			Lyra\Client::setDefaultSHA256Key("38453613e7f44dc58732bad3dca2bca3");

			$client = new Lyra\Client();  

			// On défini les parametres
			$parametres_post = array(
			  "currency" => "EUR", 
			  "formAction" => "REGISTER",
			  "orderId" => uniqid("MyOrderId"),
			  "customer" => array(
			    "email" => "sample@example.com"
			));

			// on exécute la requete et on récupère la réponse
			$response = $client->post("V4/Charge/CreateToken", $parametres_post);

			dd($response);

			if ($response['status'] != 'SUCCESS') {

				$statut = false;
				$message = $response['answer']['detailedErrorMessage'];
				$retour = array();
				$retour['result']['longMessage'] = $message;
			} else {

				$retour = $response["answer"]["formToken"];	
			}
		}
		
		return array('statut' => $statut, "message" => $message, "retour" => $retour);
		*/
	}

	/**
	 *
	 * Récupérer la carte depuis un token
	 *
	 */
	public function recuperer_carte($token) {

		// Utile ?
	}

	/**
	 *
	 * Ajouter carte
	 *
	 */
	public function ajouter_carte($client, $informations_carte) { 
		
		return $this->creer_client($client, $informations_carte);
	}

	/**
	 *
	 * Changer la carte par défaut d'un client
	 *
	 */
	public function modifier_carte_defaut($client, $nouvelle_carte) {

		// Utile ?
	}


	/*
	 * Génère la signature pour le formulaire FORM
	 */
    public static function genere_signature($params, $key)
	{
	    /**
	     * Fonction qui calcule la signature.
	     * $params : tableau contenant les champs à envoyer dans le formulaire.
	     * $key : clé de TEST ou de PRODUCTION
	     */
	    //Initialisation de la variable qui contiendra la chaine à chiffrer
	    $contenu_signature = "" ;
		                
	    // Tri des champs par ordre alphabétique
	    ksort($params);
	    foreach ($params as $nom =>$valeur){ 
	    
	         // Récupération des champs vads_ 
	        if (substr($nom,0,5)=='vads_') { 
	            
	            // Concaténation avec le séparateur "+" 
	            $contenu_signature .= $valeur."+";
	        }
	    }
	    // Ajout de la clé à la fin
	    $contenu_signature .= $key;

	    // Application de l’algorythme SHA-1
   		$signature = base64_encode(hash_hmac('sha256',$contenu_signature, $key, true));
	    return $signature ;
	}



	/**
	 *
	 * Récupère l'url du formulaire de paiement de payzen
	 *
	 */
	public function paiement($facture) {
	}
	
	/**
	 * 
	 * Génère un paiement via portefeuille payzen
	 * 
	 */
	public function genere_paiement_via_portefeuille($montant, $client_id, $portefeuille, $document_id) { 
		
		$montant = (int)strval($montant * 100);

    	$client = modele('client', $client_id) ;

		$transaction = management('transaction_paiement');
		$transaction->creer_transaction_facture('payzen', $document_id);
	    $trans_id = $transaction->generer_numero_transaction(6);

    	$username = config('services.payzen.site_id');
    	$mot_de_passe = config('services.payzen.mot_de_passe') ;
    	$clef_publique = $username.':'.$mot_de_passe ;

		// On défini les codes d'accès
		Lyra\Client::setDefaultUsername($username);
		Lyra\Client::setDefaultPassword($mot_de_passe);
		Lyra\Client::setDefaultEndpoint("https://api.payzen.eu");
		Lyra\Client::setDefaultPublicKey($clef_publique);
		$lyraclient = new Lyra\Client();  

		// On défini les parametres
		$store = array(
		
			"paymentMethodToken" => $portefeuille,
			"amount" => $montant,
			"currency" => 'EUR',
			"formAction" =>'SILENT',
			"customer" => array(
			
				"email" => ( !empty($client->mail_comptable) ? $client->mail_comptable : $client->adresse_email )
			)
		);

		// On exécute la requete et on récupère la réponse
		return $lyraclient->post("V4/Charge/CreatePayment", $store);
	}
	
	/**
	 * 
	 * Récupère la liste des CB du client
	 * 
	 */
	public function recupere_liste_cb($portefeuille) {

		// Pas utile ?
	}
	
	/**
	 *
	 * Fait une demande à payzen du résultat d'un paiement
	 *
	 */
	public function statut_paiement($informations) {

		// Pas utile ?
	}

}