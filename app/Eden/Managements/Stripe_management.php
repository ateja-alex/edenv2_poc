<?php

namespace App\Eden\Managements;

class Stripe_management {
	
	private $stripe_configuration;

    public function __construct(){
		$this->stripe_configuration =  \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
	 *
	 * Contrôle les entrées utilisateurs
	 *
	 */
	public function controler_entrees_utilisateur ($formulaire) {

		$statut = true;
		$message = traduction('messages.php.payline.controle_realise');
		$retour = null;

		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}
	

	public function retourne_cartes($token_stripe) {
		
		if(empty($token_stripe))
			return array();

		//Todo : vérifier si le client a un id
		$this->stripe_configuration;
		// $stripe_id = 'cus_DVAFoTH8wEqLOn';
		
		$customer = \Stripe\Customer::retrieve($token_stripe);
		$cartes = $customer->sources->all(array( 'object' => 'card'));
		
		return $cartes->data;
	}

	/**
	 *
	 * Crée un client chez Stripe en l'identifiant par son adresse email et en lui associant un moyend de paiement
	 *
	 */
	public function creer_client($email, $source){

		$statut = true;
		$message = traduction('messages.php.stripe.creation_client_succes');
		$retour = null;

		try {

			$parametres = array(

				'email' => $email,
				'source' => $source,
			);

			$client = \Stripe\Customer::create($parametres);

			$retour = $client->id;
		} catch(\Stripe\Error\RateLimit $error) {

			$statut = false;
			$message = traduction('messages.php.stripe.creation_client_erreur');
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\InvalidRequest $error) {

			$statut = false;
			$message = traduction('messages.php.stripe.creation_client_erreur');
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Authentication $error) {

			$statut = false;
			$message = traduction('messages.php.stripe.creation_client_erreur');
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\ApiConnection $error) {

			$statut = false;
			$message = traduction('messages.php.stripe.creation_client_erreur');
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Base $error) {

			$statut = false;
			$message = traduction('messages.php.stripe.creation_client_erreur');
			$retour = $error->getJsonBody()['error'];
		}

		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}

	/**
	 *
	 * Récupérer la carte depuis un token
	 *
	 */
	public function recuperer_carte($token) {

		$token = \Stripe\Token::retrieve($token);

		return $token->card;
	}

	/**
	 *
	 * Ajouter carte
	 *
	 */
	public function ajouter_carte($utilisateur, $carte) {


		$statut = true;
		$message = traduction('messages.php.paiement.carte_enregistree');
		$retour = null;
		$client_stripe = null;

		try{
			$client_stripe =  \Stripe\Customer::retrieve($utilisateur->portefeuille_stripe);
	        $carte = $client_stripe->sources->create(["source" => $carte['stripeToken']]);

	        $retour = $client_stripe;
		} catch(\Stripe\Error\RateLimit $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\InvalidRequest $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Authentication $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\ApiConnection $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Base $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		}

		if($statut) {
			$retour_modification_carte_defaut = $this->modifier_carte_defaut($client_stripe->id, $carte->id);

		    if(!$retour_modification_carte_defaut['statut']) {

		    	$statut = false;
				$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
				$retour = $retour_modification_carte_defaut['retour'];
		    }
		}			
			
		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}

	/**
	 *
	 * Changer la carte par défaut d'un client
	 *
	 */
	function modifier_carte_defaut($client, $nouvelle_carte) {

		$statut = true;
		$message = traduction('messages.php.paiement.carte_enregistree');
		$retour = null;

		try{

			$donnees = array(

				"default_source" => $nouvelle_carte
			);

			$client_stripe =  \Stripe\Customer::update($client, $donnees);
	        

	        $retour = $client_stripe;
		} catch(\Stripe\Error\RateLimit $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\InvalidRequest $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Authentication $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\ApiConnection $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Base $error) {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
			$retour = $error->getJsonBody()['error'];
		}
			
		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}
	
	/**
	 * 
	 * Génère un paiement via stripe
	 * 
	 */

	public function paiement($client, $facture, $description, $source = null) {
		
		// si le client n'a pas de stripe_payment_method  = paiement avant sca
		if(empty($client->stripe_payment_method)) {
			
			return $this->paiement_sans_sca($client, $facture, $description, $source);

		}
		else {

			return $this->paiement_avec_sca($client, $facture, $description, $source);
		}

	}

	/**
	 * 
	 * Gestion du paiement stripe après la norme SCA
	 * 
	 */

	private function paiement_avec_sca($client, $facture, $description, $source = null) {

		$token = false;
		
		if(is_array($client) && !empty($client['portefeuille_stripe'])) {
			
			$token = $client['portefeuille_stripe'];
		}
		
		if(empty($token) && is_object($client) && !empty($client->portefeuille_stripe))
			$token = $client->portefeuille_stripe;
		
		/*
		if(is_object($client) && !empty($client->portefeuille_stripe)) {
			
			$token = $client->portefeuille_stripe;
		}
		
		if($token === false) {
			
			return array("statut" => false, "message" => "Absence de token stripe pour ce client", "donnees" => null);
		}

		if(empty($source)) {
			
			return array("statut" => false, "message" => "Absence de données de carte pour débiter ce client", "donnees" => null);
		}
		*/

		$moyen_de_paiement = $client->stripe_payment_method;
		
		$statut = true;
		$message = traduction('messages.php.paiement.paiement_succes');
		$retour = null;
		
		$payment_intent = false;
		
		try {

			if(!empty($source)) {
				
				$payment_intent = \Stripe\Charge::create([
						'amount' => 999 * 100,
						'currency' => 'eur',
						'customer' => 'cus_JU5qzqMBhxiXBN',
						// 'source' => "card_1Ir72oAYAUkR4JCsr49umDPT",
					]);

				$payment_intent = \Stripe\Charge::create([
					'amount' => intval(strval($facture->solde_document_ttc * 100)),
					'currency' => 'eur',
					"description" => $description,
					'customer' => $token,
					// 'source' => $source,
					'off_session' => true,
					'confirm' => true,
				]);

			} else {
				
				/*
				$payment_intent = \Stripe\PaymentIntent::create([
				
					'amount' => 999 * 100,
					'currency' => 'eur',
					"description" => '',
					'payment_method_types' => ['card'],
					'customer' => session()->get('utilisateur')->portefeuille_stripe,
					// 'payment_method' => "tok_1Ir72oAYAUkR4JCs59HmK7St",
					'payment_method_data' => ['type' => 'card', 'card[token]' => "tok_1Ir72oAYAUkR4JCs59HmK7St"],
					'off_session' => true,
					'confirm' => true,
				]);
				*/
		
				$payment_intent = \Stripe\PaymentIntent::create([
					'amount' => intval(strval($facture->solde_document_ttc * 100)),
					'currency' => 'eur',
					"description" => $description,
					'payment_method_types' => ['card'],
					'customer' => $token,
					'payment_method' => $moyen_de_paiement,
					// 'payment_method_data' => ['type' => 'card', 'card[token]' => $moyen_de_paiement],
					'off_session' => true,
					'confirm' => true,
				]);
			}
				
		} catch(\Stripe\Error\RateLimit $e) {

			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec')." (RateLimit)";
			$retour = $e->getJsonBody()['error'];
		} catch (\Stripe\Error\InvalidRequest $e) {

			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec')." (InvalidRequest)";
			$retour = array("error" => $e->getJsonBody()['error']);
		} catch (\Stripe\Error\Authentication $e) {
		
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec')." (Authentication)";
			$retour = $e->getJsonBody()['error'];			
		} catch (\Stripe\Error\ApiConnection $e) {
		
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec')." (ApiConnection)";
			$retour = $e->getJsonBody()['error'];		
		} catch (\Stripe\Error\Card $e) {
		
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec')." (Card)";
			$retour = $e->getJsonBody()['error'];
		} catch (\Stripe\Error\Base $e) {
			
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec')." (erreur inconnue)";
			$retour = $e->getJsonBody()['error'];
		}
		
		if($payment_intent !== false && $payment_intent->status == 'succeeded') {
			
			return array("statut" => $statut, "message" => $message, "donnees" => $retour);
		}
		
		return array("statut" => $statut, "message" => $message, "donnees" => $retour);
	}


	/**
	 * 
	 * Gestion du paiement stripe sans la norme SCA
	 * 
	 */
	private function paiement_sans_sca($client, $facture, $description, $source = null) {

		$donnees = array(

			"amount" => intval(strval($facture->solde_document_ttc * 100)),
			"currency" => "eur",
			"description" => $description,
		);


		$statut = true;
		$message = traduction('messages.php.paiement.paiement_succes');
		$retour = null;


		if(!empty($source)) {
			
			if(!isset($source['stripeToken']) && empty($client->token_stripe)) {
				
				$statut = false;
				$message = traduction('messages.php.stripe.stripe_token_absent');
				$retour = traduction('messages.php.stripe.stripe_token_absent');
				
				return array("statut" => $statut, "message" => $message, "donnees" => $retour);	
			}
			
			if(!isset($source['stripeToken'])) {
				
				$donnees['source'] = $source;
			}
			else {
				
				$donnees['source'] = $source['stripeToken'];
			}
			
			// $donnees['source'] = $source['stripeToken'];
		}

		if(!empty($client))
			$donnees['customer'] = $client['portefeuille_stripe'];

		
		try {
			
			$retour = (\Stripe\Charge::create($donnees));

		} catch(\Stripe\Error\RateLimit $e) {

			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec');
			$retour = $e->getJsonBody()['error'];
		} catch (\Stripe\Error\InvalidRequest $e) {

			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec');
			$retour = array("error" => $e->getJsonBody()['error']);
		} catch (\Stripe\Error\Authentication $e) {
		
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec');
			$retour = $e->getJsonBody()['error'];			
		} catch (\Stripe\Error\ApiConnection $e) {
		
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec');
			$retour = $e->getJsonBody()['error'];
		} catch (\Stripe\Error\Base $e) {
		
			$statut = false;
			$message = traduction('messages.php.paiement.paiement_echec');
			$retour = $e->getJsonBody()['error'];
		}

		return array("statut" => $statut, "message" => $message, "donnees" => $retour);	
	}
}