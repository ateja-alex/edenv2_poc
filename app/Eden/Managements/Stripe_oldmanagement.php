<?php

namespace App\Eden\Managements;

class Stripe_oldmanagement {
	
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
		$message = "Contrôle réalisé avec succès";
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
		$message = "Client créé avec succès";
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
			$message = "Echec de la création du client";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\InvalidRequest $error) {

			$statut = false;
			$message = "Echec de la création du client";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Authentication $error) {

			$statut = false;
			$message = "Echec de la création du client";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\ApiConnection $error) {

			$statut = false;
			$message = "Echec de la création du client";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Base $error) {

			$statut = false;
			$message = "Echec de la création du client";
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
		$message = "Carte enregistrée avec succès";
		$retour = null;
		$client_stripe = null;

		try{
			$client_stripe =  \Stripe\Customer::retrieve($utilisateur->portefeuille_stripe);
	        $carte = $client_stripe->sources->create(["source" => $carte['stripeToken']]);

	        $retour = $client_stripe;
		} catch(\Stripe\Error\RateLimit $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\InvalidRequest $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Authentication $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\ApiConnection $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Base $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		}

		if($statut) {
			$retour_modification_carte_defaut = $this->modifier_carte_defaut($client_stripe->id, $carte->id);

		    if(!$retour_modification_carte_defaut['statut']) {

		    	$statut = false;
				$message = "Echec de l'enregistrement de la carte";
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
		$message = "Carte enregistrée avec succès";
		$retour = null;

		try{

			$donnees = array(

				"default_source" => $nouvelle_carte
			);

			$client_stripe =  \Stripe\Customer::update($client, $donnees);
	        

	        $retour = $client_stripe;
		} catch(\Stripe\Error\RateLimit $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\InvalidRequest $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Authentication $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\ApiConnection $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		} catch(\Stripe\Error\Base $error) {

			$statut = false;
			$message = "Echec de l'enregistrement de la carte";
			$retour = $error->getJsonBody()['error'];
		}
			
		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}

	/**
	 * 
	 * créer méthode sca
	 *  avant sans sca
	 * stripepaiementmethod champ du client
	 */
	public function paiement($client, $facture, $description, $source = null) {


		dd($client, $facture, $description, $source);
		$donnees = array(

			"amount" => $facture->solde_document_ttc * 100,
			"currency" => "eur",
			"description" => $description,
		);

		$statut = true;
		$message = "Paiement réalisé avec succès";
		$retour = null;

		if(!empty($source))
			$donnees['source'] = $source['stripeToken'];

		if(!empty($client))
			$donnees['customer'] = $client['portefeuille_stripe'];

		try {
			
			$retour = (\Stripe\Charge::create($donnees));
		} catch(\Stripe\Error\RateLimit $e) {

			$statut = false;
			$message = "Erreur lors du paiement";
			$retour = $e->getJsonBody()['error'];
		} catch (\Stripe\Error\InvalidRequest $e) {

			$statut = false;
			$message = "Erreur lors du paiement";
			$retour = array("error" => $e->getJsonBody()['error']);
		} catch (\Stripe\Error\Authentication $e) {
		
			$statut = false;
			$message = "Erreur lors du paiement";
			$retour = $e->getJsonBody()['error'];			
		} catch (\Stripe\Error\ApiConnection $e) {
		
			$statut = false;
			$message = "Erreur lors du paiement";
			$retour = $e->getJsonBody()['error'];
		} catch (\Stripe\Error\Base $e) {
		
			$statut = false;
			$message = "Erreur lors du paiement";
			$retour = $e->getJsonBody()['error'];
		}

		return array("statut" => $statut, "message" => $message, "donnees" => $retour);
	}
}