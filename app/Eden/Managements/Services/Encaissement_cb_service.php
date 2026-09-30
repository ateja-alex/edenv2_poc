<?php

namespace App\Eden\Managements\Services;

use PaylineSDK;

/**
 * 
 * Ce service permet de gérer l'encaissement manuel via les portefeuilles
 * 
 */
class Encaissement_cb_service {

	/**
	 *
	 * Récupère la liste des méthodes de paiement utilisées sur ce projet
	 *
	 * Pour le moment cette méthode doit forcément être surchargée
	 *
	 */
	public function recupere_methodes_de_paiement() {

		return array();
	}	

	/**
	 *
	 * Effectue un encaissement avec Payline
	 *
	 */
	public function gere_paiement_payline($montant, $portefeuille) {

		$payline = new PayLineSDK(

			config('laravel-payline-sdk.connection_settings.MERCHANT_ID'),
			config('laravel-payline-sdk.connection_settings.ACCESS_KEY'),
			config('laravel-payline-sdk.connection_settings.PROXY_HOST'),
			config('laravel-payline-sdk.connection_settings.PROXY_PORT'),
			config('laravel-payline-sdk.connection_settings.PROXY_LOGIN'),
			config('laravel-payline-sdk.connection_settings.PROXY_PASSWORD'),
			config('laravel-payline-sdk.connection_settings.ENVIRONMENT')
		);

		$somme = strval($montant * 100);
		$somme = (int) $somme;

		$paiement = array();
		$paiement['walletId'] = $portefeuille;
		$paiement['cardInd'] = '01';
		$paiement['version'] = '4';

		$paiement['payment']['amount'] = $somme;
		$paiement['payment']['currency'] = '978';
		$paiement['payment']['action'] = '101';
		$paiement['payment']['mode'] =  'CPT';
		$paiement['payment']['differedActionDate'] = '' ;
		$paiement['payment']['contractNumber'] = config('laravel-payline-sdk.webservices_settings.CONTRACT_NUMBER');

		//ORDER
		$paiement['order']['ref'] = 'Encaissement LMDC '.date('Y-m-d h:i:s');
		$paiement['order']['origin'] = '';
		$paiement['order']['country'] = '';
		$paiement['order']['taxes'] = '';
		$paiement['order']['amount'] = $somme;
		$paiement['order']['date'] = '';
		$paiement['order']['currency'] = '978';
		$paiement['order']['deliveryTime'] = '';
		$paiement['order']['deliveryMode'] = '';
		$paiement['order']['deliveryExpectedDate'] = '';
		$paiement['order']['deliveryExpectedDelay'] = '';

		// ???
		$paiement['walletCvx'] = null;

		$reponse = $payline->doImmediateWalletPayment($paiement);
		
		if($reponse['result']['code'] != '0000') {
				
			// il y a eu une erreur
			return array('succes' => false, 'erreur' => traduction('messages.php.encaissement.echec_paiement')." : ".$reponse['result']['longMessage']);
		}
		
		// ok ça a fonctionné
		return array('succes' => true);
	}	

	/**
	 *
	 * Effectue un encaissement avec Payzen
	 *
	 */
	public function gere_paiement_payzen($montant, $portefeuille, $client_id) {
		
		$payzen_management = new \App\Eden\Managements\Payzen_management();

		$retour = $payzen_management->genere_paiement_via_portefeuille($montant, $client_id, $portefeuille, 0);

		if($retour['status'] != 'SUCCESS') {
			
			// il y a eu une erreur
			return array('succes' => false, 'erreur' => traduction('messages.php.encaissement.echec_paiement')." : ".$retour['answer']['detailedErrorMessage']);
		}
		
		if($retour['answer']['orderStatus'] == 'UNPAID') {
			
			// il y a eu une erreur
			return array('succes' => false, 'erreur' => traduction('messages.php.encaissement.echec_paiement')." : ".$retour['answer']['transactions'][0]['errorMessage']);
		}
		
		// ok ça a fonctionné
		return array('succes' => true, 'retour' => $retour);
	}	
}
