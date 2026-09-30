<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eden\Managements\Stripe_management;

class Stripe_controller extends Controller {
	
	/**
	 * 
	 * Test dernière version stripe
	 * 
	 */
	public function test_saisie_carte() {
		
		$service = service('stripe');
		
		// on crée le client
		$client = $service->creation_client();
		
		$payment_intent = $service->creation_payment_intent($client->id, 20);
		
		return view("eden::stripe", ['payment_intent' => $payment_intent, 'client' => $client]);
	}
	
	/**
	 * 
	 * Test dernière version stripe
	 * 
	 */
	public function modification_client($customer, $payment_method) {
		
		$service = service('stripe');
		
		$service->combine_payment_method_et_customer($customer, $payment_method);
		
		$client = modele('client')->where('portefeuille_stripe', $customer)->first();
		
		$management = management('client', $client->id, $client);
		
		$management->enregistre(array('stripe_payment_method' => $payment_method));
		
		return response()->json(array(true));
	}
	
	/**
	 * 
	 * Test dernière version stripe
	 * 
	 */
	public function test_paiement_client($customer, $payment_method) {
		
		$service = service('stripe');
		
		// $customer = "cus_LXejnUlrcdZN82";
		// $payment_method = "pm_1KqZPwCNkz9BzTbN54F5bLCK";
		
		$retour = $service->paiement($customer, $payment_method);
		
		return response()->json($retour);
	}
	
	/**
	 * 
	 * Test dernière version stripe
	 * 
	 */
	public function test_liste_payment_methods($customer) {
		
		$service = service('stripe');
		
		$retour = $service->payment_methods_pour_customer($customer);
		
		dd($retour);
		
		return response()->json($retour);
	}
	
	
	
	
	
	
	
	
	
	
	
	

	/**
	 * 
	 * Page d'ajout du token stripe sur le compte client
	 * 
	 */
    public function ajouter_token_stripe(Request $request, $client_id, $token) {

		if(md5('eden' . $client_id) == $token) {

			$customer_id = service('stripe')->retourne_customer_id($client_id);
			
			$client = management("client", $client_id);
			
			$payment_intent = service('stripe')->creation_setup_intent($customer_id);
			
			return view('eden::maj_token_stripe', array(
				'client' => $client->modele, 
				'token' => $token, 
				'client_id' => $client_id, 
				'payment_intent' => $payment_intent
			));
		}

		exit();
    }

    /**
     *
     * Mise à jour du token Stripe en bdd
     *
    public function maj_token_stripe(Request $request, Stripe_management $stripe_management, $client_id, $token) {

		if(md5('eden' . $client_id) == $token) {

	    	$client_management = management('client', $request->client_id);

	    	$client = $client_management->modele;

	    	$carte = $stripe_management->recuperer_carte($request->stripeToken)->id;

	    	if($client->portefeuille_stripe != null) {

	    		$retour_ajout_carte = $stripe_management->ajouter_carte($client, $request->stripeToken);

	    		if($retour_ajout_carte['statut']) {

	    			$retour_modification_carte_defaut = $stripe_management->modifier_carte_defaut($client->portefeuille_stripe, $carte);

	    			if($retour_modification_carte_defaut['statut']) {

	    				return redirect()->back()->with('ok', $retour_modification_carte_defaut['message']);
	    			}
	    		} else {

	    			return redirect()->back()->withErrors($retour_ajout_carte['message']);
	    		}
	    	} else {

	    		$retour_creation_client = $stripe_management->creer_client($client->adresse_email, $request->stripeToken);

				if($retour_creation_client['statut']) {

	    			$modifications = array(

			    		'portefeuille_stripe' => $retour_creation_client['retour'],
			    	);

			    	$client_management->enregistre($modifications);

			    	return redirect()->back()->with('ok', 'Carte enregistrée avec succès');
	    		} else {

	    			return redirect()->back()->WithErrors("Echec de l'enregistrement de la carte");
	    		}
	    	}
	    }

	    exit();    	
    }
     */

    /**
     *
     * Afficher page de paiement de facture
     *
     */
    public function paiement_facture($facture_id, $token) {

		if(md5('eden' . $facture_id) == $token) {

			$facture = modele("facture_vente", $facture_id);
			
			$customer_id = service('stripe')->retourne_customer_id($facture->client_id);
			
			$payment_intent = service('stripe')->creation_payment_intent($customer_id, $facture->solde_document_ttc);
			
			$plateforme = 'stripe';
			
			return view('eden::paiement_facture', compact('facture', 'token', 'facture_id', 'payment_intent', 'plateforme'));
		}

		exit();
    }

    /**
     *
     * Enregistrer le paiement
     *
     */
    public function paiement_facture_post(Request $requete, Stripe_management $stripe_management, $facture_id, $token) {

    	if(md5('eden' . $facture_id) == $token) {

			$facture = management("facture_vente", $facture_id);
    			
			$infos_paiement = array(
				'mode_paiement_id' => fonctionnalite('id_mode_de_paiement_cb_stripe'), 
				'compte_bancaire_id' => fonctionnalite('compte_bancaire_cb_stripe'), 
				'date' => date('Y-m-d'), 
				'montant_saisi' => $facture->modele->solde_document_ttc,
				'type' => 0,
			);
				
			$retour = $facture->ajouter_paiement('facture_vente', $facture->modele->id, $infos_paiement);
			
			return response()->json(true);
		}

		exit();
    }
	
	

    public function afficher_pdf($facture_id, $token) {

    	if(md5('eden' . $facture_id) == $token) {
		
			$facture = management('facture_vente', $facture_id);

			$facture->creation_pdf();
			
			$chemin_pdf = $facture->modele->pdf;
			
			// On retourne une réponse
			return response()->file(storage_path('app/'.$chemin_pdf));
		}

		exit();
    }
}

