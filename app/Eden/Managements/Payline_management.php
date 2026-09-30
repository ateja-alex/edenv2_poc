<?php

namespace App\Eden\Managements;

use PayLineSDK;
use URL;

class Payline_management {
	
	private $payline_sdk;

    public function __construct(){
        $this->payline_sdk = new PayLineSDK(

        	config('laravel-payline-sdk.connection_settings.MERCHANT_ID'), 
        	config('laravel-payline-sdk.connection_settings.ACCESS_KEY'), 
        	config('laravel-payline-sdk.connection_settings.PROXY_HOST'),
        	config('laravel-payline-sdk.connection_settings.PROXY_PORT'),
        	config('laravel-payline-sdk.connection_settings.PROXY_LOGIN'),
        	config('laravel-payline-sdk.connection_settings.PROXY_PASSWORD'),
        	config('laravel-payline-sdk.connection_settings.ENVIRONMENT')
        );        	
    }

	/**
	 *
	 * Contrôle les entrées utilisateurs
	 *
	 */
	public function controler_entrees_utilisateur($formulaire) {

		$statut = true;
		$message = traduction('messages.php.payline.controle_realise');
		$retour = null;

		if($formulaire['ccard-holder'] === null) {

			$statut = false;
			$message = traduction('messages.php.payline.echec_controle');
			$retour = traduction('messages.php.payline.indiquer_titulaire_carte');
		} elseif($formulaire['ccard-num'] === null) {

			$statut = false;
			$message = traduction('messages.php.payline.echec_controle');
			$retour = traduction('messages.php.payline.indiquer_numero_carte');
		} elseif( !isset($formulaire['ccard-exp1']) || !isset($formulaire['ccard-exp2']) || $formulaire['ccard-exp1'] === null || $formulaire['ccard-exp2'] === null) {

			$statut = false;
			$message = traduction('messages.php.payline.echec_controle');
			$retour = traduction('messages.php.payline.indiquer_date_expiration_carte');
		} elseif($formulaire['ccard-cvc'] === null) {

			$statut = false;
			$message = traduction('messages.php.payline.echec_controle');
			$retour = traduction('messages.php.payline.indiquer_cryptogramme_carte');
		}

		return array('statut' => $statut, "message" => $message, "retour" => $retour);
	}

	/**
	 *
	 * Crée un client chez Payline
	 *
	 */
	public function creer_client($client, $informations_carte){

		$statut = true;
		$message = traduction('messages.php.paiement.carte_enregistree');
		$retour = null;

		if(config('app.projet') == '') {

			$statut = false;
			$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,['']);
		} 
		else {
			
			$nouveau_portefeuille = config('app.projet') . '-' . $client->id.'-'.date('YmdHis');

			$type_carte = $this->type_carte($informations_carte['ccard-num']);

			$parametres = array(

				'version' => 12,
				'contractNumber' => config('laravel-payline-sdk.webservices_settings.CONTRACT_NUMBER'),
				'wallet' => array(
					'walletId' => $nouveau_portefeuille,
					'lastName' => $client->nom,
					'firstName' => $client->prenom,
					'email' => $client->adresse_email,
					
				),
				'address' => array(),
				'card' => array(

					'card-holder' => $informations_carte['ccard-holder'],
					'number' => $informations_carte['ccard-num'],
					'type' => $type_carte,
					'expirationDate' => $informations_carte['ccard-exp1'].$informations_carte['ccard-exp2'],
					'cvx' => $informations_carte['ccard-cvc'],
				),
				'3DSecure' => array(),
			);

			$portefeuille = $this->payline_sdk->createWallet($parametres);

			if($portefeuille['result']['code'] != "02500" && $portefeuille['result']['code'] != "02501") {

				$statut = false;
				$message = traduction('messages.php.paiement.erreur_enregistrement_carte',null,[$portefeuille['result']['longMessage']]);
				$retour = $portefeuille;
			} else {
				
				$retour = $nouveau_portefeuille;	
				// $retour = $portefeuille;
			}			
		}

		// $portefeuille['result']['code'], $portefeuille['result']['code']);
		
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
	public function ajouter_carte($client, $informations_carte) {
		
		$retour = $this->creer_client($client, $informations_carte);
		
		// on met à jour directement le compte du client
		if($retour['statut'] === true) {
			
			$client_management = management('client', $client->id);
			
			$modifications = array(

				'portefeuille_payline' => $retour['retour'],
			);

			$erreur = $client_management->enregistre($modifications);
			
			if($erreur !== true) {
				
				$retour['statut'] = false;
				$retour['message'] = traduction('messages.php.payline.echec_mise_a_jour_client_erp')." : ".$erreur;
			}
		}
		
		return $retour;
	}

	/**
	 *
	 * Changer la carte par défaut d'un client
	 *
	 */
	public function modifier_carte_defaut($client, $nouvelle_carte) {

		//@TODO
	}

	/**
	 *
	 * Récupère l'url du formulaire de paiement de Payline
	 *
	 */
	public function paiement($facture) {

		$donnees = array(

			'version' => "",
			'payment' => array(

				"amount" => (int)strval($facture->solde_document_ttc * 100),
				"currency" => 978,
				"action" => 101,
				"mode" => "CPT",
				'contractNumber' => management('facture_vente', $facture->id)->payline_contrat_vad(),
			),
			'order' => array(

				"ref" => $facture->reference_document,
				"country" => "FR",
				"amount" => (int)strval($facture->solde_document_ttc * 100),
				"currency" => 978,
				"date" => date('d/m/Y h:i', strtotime($facture->cree_le)),
			),
			'returnURL' => URL::route(
				'retour_paiement',
				array('plateforme' => 'payline', 'facture_id' => $facture->id)
			),
			'cancelURL' => URL::route(
				'retour_paiement_annulation',
				array('plateforme' => 'payline', 'facture_id' => $facture->id)
			),
			'notificationURL' => URL::route(
				'retour_paiement_notification',
				array('plateforme' => 'payline', 'facture_id' => $facture->id)
			),
			'selectedContractList' => explode(';', config('laravel-payline-sdk.webservices_settings.CONTRACT_NUMBER_LIST')),
			'languageCode' => 'FR',
			'securityMode' => 'SSL',
		);
			
		$retour = $this->payline_sdk->doWebPayment($donnees);

		session()->put('token_payline', $retour['token']);

		return $retour;
	}
	
	/**
	 * 
	 * Génère un paiement via portefeuille payline
	 * 
	 */
	public function genere_paiement_via_portefeuille($document, $portefeuille) {
		
		// on va chercher la carte active
		$cartes = $this->recupere_liste_cb($portefeuille);

		$paiement = array();
		$paiement['walletId'] = $portefeuille;
		$paiement['version'] = '4';
		$paiement['cardInd'] = '';
		$paiement['walletCvx'] = '';


		$paiement['payment']['amount'] = (int)strval($document->solde_document_ttc * 100);
		$paiement['payment']['currency'] = '978';
		$paiement['payment']['action'] = '101';
		$paiement['payment']['mode'] =  'CPT';
		$paiement['payment']['differedActionDate'] = '' ;
		
		$paiement['payment']['contractNumber'] = config('laravel-payline-sdk.webservices_settings.CONTRACT_NUMBER');
		
		//ORDER
		$paiement['order']['ref'] = 'facture '.$document->reference_document.' '.date('Y-m-d h:i:s');
		$paiement['order']['origin'] = '';
		$paiement['order']['country'] = '';
		$paiement['order']['taxes'] = '';
		$paiement['order']['amount'] = (int)strval($document->solde_document_ttc * 100);
		$paiement['order']['date'] = '';
		$paiement['order']['currency'] = '978';
		$paiement['order']['deliveryTime'] = '';
		$paiement['order']['deliveryMode'] = '';
		$paiement['order']['deliveryExpectedDate'] = '';
		$paiement['order']['deliveryExpectedDelay'] = '';

		$reponse = $this->payline_sdk->doImmediateWalletPayment($paiement);
		
		// on tente avec un ancien contrat VAD si on en a un
		if($reponse['result']['code'] == 'XXXXX') {
			
			$anciens_contrats_vad = config('eden.anciens_contrats_vad');
			
			if(!empty($anciens_contrats_vad)) {
				
				foreach($anciens_contrats_vad as $contrat_vad) {
					
					$paiement['payment']['contractNumber'] = $contrat_vad;
					
					$reponse = $this->payline_sdk->doImmediateWalletPayment($paiement);
					
					if($reponse['result']['code'] != 'XXXXX') {
						
						return $reponse;
					}
				}
			}
		}
		
		// on essaie d'ajouter de l'info sur les erreurs
		if($reponse['result']['code'] != '00000') {
			
			if(isset($cartes['isDisabled']) && $cartes['isDisabled'] == 1) {
				
				$reponse['result']['longMessage'] .= " Note Easy Développement : le portefeuille est désactivé, le client doit saisir à nouveau sa CB.";
			}
		}
		
		return $reponse;
	}
	
	/**
	 * 
	 * Récupère la liste des CB du client
	 * 
	 */
	public function recupere_liste_cb($portefeuille) {
		
		$infos = array();
		$infos['walletId'] = $portefeuille;
		$infos['version'] = '4';
		$infos['contractNumber'] = config('laravel-payline-sdk.webservices_settings.CONTRACT_NUMBER');
		$infos['cardInd'] = '';
		

		$reponse = $this->payline_sdk->getWallet($infos);
		
		return $reponse;
	}
	
	/**
	 *
	 * Fait une demande à payline du résultat d'un paiement
	 *
	 */
	public function statut_paiement($informations) {

		$retour_payline = $this->payline_sdk->getWebPaymentDetails(array('token' => $informations['token']));

		if($retour_payline['result']['code'] != "00000")
			return array('statut' => false, 'message' => $retour_payline['result']['longMessage']);

		return array('statut' => true, 'message' => $retour_payline['result']['longMessage']);
	}

	/**
	 * 
	 * Détermine le type de carte bancaire en fonction de son numéro
	 *
	 */
	private function type_carte($numero) {

		$retour = 'CB';

		$debut_numero = substr($numero, 0, 2);

		if($debut_numero >= '40' && $debut_numero <= '49') {

			$retour = "VISA";
		} elseif($debut_numero >= '51' && $debut_numero <= '55') {

			$retour = "MASTERCARD";
		} elseif($debut_numero == '56' || $debut_numero == '67') {

			$retour = "MAESTRO";
		} elseif($debut_numero == '37') {

			$retour ="AMEX";
		}

		return $retour;
	}
}