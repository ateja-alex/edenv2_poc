<?php

namespace App\Eden\Managements\Services;

class Stripe_service {
	
	/**
	 * 
	 * Retourne la clé privée à utiliser en fonction du mode live / prod
	 * 
	 */
	public function cle_privee() {
		
		if(config('fonctionnalites_integrations.stripe_live') === true) {
			
			return config('fonctionnalites_integrations.stripe_private_key');
		}
		else {
			
			return config('fonctionnalites_integrations.stripe_private_key_test');
		}
		
	}
	
	/**
	 * 
	 * Retourne la clé publique à utiliser en fonction du mode live / prod
	 * 
	 */
	public function cle_publique() {
		
		if(config('fonctionnalites_integrations.stripe_live') === true) {
			
			return config('fonctionnalites_integrations.stripe_public_key');
		}
		else {
			
			return config('fonctionnalites_integrations.stripe_public_key_test');
		}
		
	}
	
	/**
	 * 
	 * Retourne une instance de stripe
	 * 
	 */
	protected function instancie() {
		
		return new \Stripe\StripeClient($this->cle_privee());	
	}
	
	/**
	 * 
	 * Crée le client et renvoie l'objet customer de stripe
	 * 
	 */
	public function creation_client($informations = array()) {
		
		return $this->instancie()->customers->create($informations);
	}
	
	/**
	 * 
	 * Retourne le customer_id pour un client (et s'il n'existe pas, on le crée)
	 * 
	 */
	public function retourne_customer_id($client_id) {
		
		$client = management("client", $client_id);
		
		if(empty($client->modele->portefeuille_stripe)) {
			
			$customer = $this->creation_client(['email' => $client->modele->adresse_email,'description' => ($client->modele->prenom." ".$client->modele->nom)]);
			
			$client->enregistre(array('portefeuille_stripe' => $customer->id));
		}
		
		return $client->modele->portefeuille_stripe;
	}
	
	/**
	 * 
	 * Crée un payment intent et retourne l'objet
	 * 
	 */
	public function creation_payment_intent($client_id, $montant_en_euros = 0,$description = null) {
		
		return $this->instancie()->paymentIntents->create(array(
			
			'customer' => $client_id,
			'currency' => 'eur',
			'amount' => round($montant_en_euros * 100),
            'description' => $description,
			'payment_method_types' => ['card'],
			'setup_future_usage' => 'off_session',
		));
	}

    /**
	 *
	 * Crée un payment intent pour un abonnement et retourne l'objet
	 *
	 */
	public function creation_payment_intent_abonnement($client_id,$paiement_method,$description, $montant_en_euros = 0) {

		return $this->instancie()->paymentIntents->create(array(

			'customer' => $client_id,
			'currency' => 'eur',
			'amount' => round($montant_en_euros * 100),
			'payment_method' => $paiement_method,
			'description' => $description,
			'off_session' => true,
			'confirm' => true,
		));
	}
	
	/**
	 * 
	 * Crée un setup intent et retourne l'objet
	 * 
	 */
	public function creation_setup_intent($client_id,$paiement_method) {
		
		return $this->instancie()->setupIntents->create(array(
			
			'customer' => $client_id,
			'payment_method' => $paiement_method,
			'usage' => 'off_session',
		));
	}
	
	/**
	 * 
	 * Attache le payment method au customer
	 * 
	 */
	public function combine_payment_method_et_customer($customer, $payment_method) {
		
		return $this->instancie()->paymentMethods->attach(
		  $payment_method,
		  ['customer' => $customer]
		);
	}

    /**
	 *
	 * Dissocier le payment method au customer
	 *
	 */
	public function dissocier_payment_method_et_customer($payment_method) {

		return $this->instancie()->paymentMethods->detach(
		  $payment_method
		);
	}
	
	
	
	public function paiement($customer, $payment_method) {
		
		$retour = $this->instancie()->paymentIntents->create([
			'amount' => 200000,
			'currency' => 'eur',
			'customer' => $customer,
			'payment_method' => $payment_method,
			'off_session' => true,
			'confirm' => true,
		  ]);
		  
		return $retour;
	}
	
	/**
	 * 
	 * Retourne la liste des payment methods pour un customer
	 * 
	 */
	public function payment_methods_pour_customer($customer) {
		
		return $this->instancie()->customers->allPaymentMethods(
			$customer,
			['type' => 'card']
		);
	}
	
	
	
	
	
	
	public function combine_payment_method_et_payment_intent() {
		
		$stripe = new \Stripe\StripeClient("sk_test_uObc4u40MjOz1yEuq9aaMK4f00KSKOLy7X");
		
		$retour = $stripe->paymentIntents->update('pi_3KqJ1fCNkz9BzTbN1zShxO8O', ['payment_method' => 'pm_1KqIujCNkz9BzTbNsKx3OjNT']);
	}
	
	
}

