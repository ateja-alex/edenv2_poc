<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eden\Managements\Payzen_management;
use Lyra;

class Paiement_controller extends Controller {


	/**
	 *
	 * On affiche l'interface de saisie de mandat. WIP attente PCI DSS
	 *
	 */
    public function mise_a_jour_mandat(Request $request, $plateforme, $client_id, $token) {

		if(md5('eden' . $client_id) == $token) {

			$client = modele("client", $client_id);
			
			return view('eden::maj_mandat', compact('plateforme', 'client', 'token', 'client_id', 'params'));
		}

		exit();
	}

	/**
	 *
	 * On enregistre le mandat. Payzen, pour l'instant.  WIP attente PCI DSS
	 *
	 */
    public function mise_a_jour_mandat_post(Request $request, $plateforme, $client_id, $token) {

		$site_id = config('services.payzen.site_id');
		$certificat = config('services.payzen.signature') ;

		if (empty($site_id) || empty($certificat))
			dd_eden('Site ID ou certificat de payzen non renseignés');

		if(md5('eden' . $client_id) == $token) {

			$client = modele("client", $client_id);

			$post = [
					"iban" 			=> $request->iban,
					"last_name" 	=> $client->nom,
					"first_name" 	=> $client->prenom,
					"email" 		=> $client->adresse_email,
					"payment_type" 	=> "RECURR",
					"locale" 		=> "FR",
					];

			$authorization = base64_encode($site_id.':'.$certificat);

			$defaults = array(
				CURLOPT_URL => "https://secure.payzen.eu/sdd/mandates",
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($post),
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => [
						"Authorization: Basic ".$authorization,
						'Content-Type: application/json',
						'Accept:application/json',	
					],
			);

			$ch = curl_init();

			curl_setopt_array($ch, ($defaults));

			$retour = json_decode(curl_exec($ch)) ;

			dd($retour);
			// On devrait récupérer un token qui nous permettra de faire les prélévements automatiques.
			// @TODO enregistrer le token
		}
	}


	/**
	 *
	 * Affichage du bouton prélever un client de son solde dû pour le rapport prélevement payzen
	 *
	 */
	public function paiement_solde_payzen(Request $request, $client_id, $token) {

		if(md5('eden' . $client_id) == $token) {

			$client = management('client', $client_id);
			$montant = intval(strval($client->solde_du_pour_prelevement() * 100)) ;

			// Si il n'y a rien à encaisser, on n'affiche pas le bouton
			if ( $montant == 0 ) {
				die();
			}

			// On créée la transaction
			$factures = $client->factures_dues_pour_prelevement();
			$transaction = management('transaction_paiement');
			$transaction->creer_transaction('payzen', $factures);
		    $trans_id = $transaction->generer_numero_transaction(6);

	    	$site_id = config('services.payzen.site_id') ;
	    	$signature = config('services.payzen.signature') ;
	    	$mode = config('services.payzen.mode') ;

			if (empty($site_id) || empty($signature) || empty($mode))
				dd_eden('Site ID, signature, ou mode de payzen non renseignés');

	    	// @todo mettre dans le management
			$params = [
				'vads_action_mode' => 'IFRAME',
				'vads_ctx_mode' => $mode,
				'vads_currency' => '978',	// EUR
				'vads_identifier' => $client->modele->portefeuille_payzen_prelevement,
				'vads_cust_email' => $client->modele->adresse_email,
				'vads_order_id' => retraite_caracteres_speciaux($client->modele->prenom).' '.retraite_caracteres_speciaux($client->modele->nom),
				'vads_page_action' => 'PAYMENT',
				'vads_payment_config' => 'SINGLE',
				'vads_site_id' => $site_id,
				'vads_trans_date' => date('YmdHis'),
				'vads_trans_id' => $trans_id,
				'vads_amount' => $montant,
				'vads_version' => 'V2',
			];

			$params['signature'] = Payzen_management::genere_signature($params, $signature);

			$chaine = '' ;
			$chaine .= '<form method="POST" action="https://secure.payzen.eu/vads-payment/">';

			foreach($params as $key => $value) {

				$chaine .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}

			$chaine .= '<br>';
			$chaine .= '<input type="submit" name="payer" value="Déclencher le prélévement" style="background-color: rgb(245, 184, 0); border-color: rgb(251, 212, 96); width: 100%;padding:5px;cursor;:pointer;border:solid 1px;" />';
				
			$chaine .= '</form>';

			return $chaine;
		}
	}

	/**
	 *
	 * Affichage du bouton prélever un client d'un montant passé en parametre
	 *
	 */
	public function paiement_prelevenement_manuel_payzen($client_id, $token, $montant) {

		if(md5('eden' . $client_id) == $token) { 

			$client = management('client', $client_id);
			$montant = intval(strval($montant * 100)) ;

			$transaction = management('transaction_paiement');
			$transaction->creer_transaction_pour_prelevement_manuel('payzen', $client_id);
		    $trans_id = $transaction->generer_numero_transaction(6);

			$site_id = config('services.payzen.site_id') ;
	    	$signature = config('services.payzen.signature') ;
	    	$mode = config('services.payzen.mode') ;

			if (empty($site_id) || empty($signature) || empty($mode))
				dd_eden('Site ID, signature, ou mode de payzen non renseignés');

			
			$params = [
				'vads_action_mode' => 'IFRAME',
				'vads_ctx_mode' => $mode,
				'vads_currency' => '978',	// EUR
				'vads_identifier' => $client->modele->portefeuille_payzen_prelevement,
				'vads_cust_email' => $client->modele->adresse_email,
				'vads_order_id' => retraite_caracteres_speciaux($client->modele->prenom).' '.retraite_caracteres_speciaux($client->modele->nom),
				'vads_page_action' => 'PAYMENT',
				'vads_payment_config' => 'SINGLE',
				'vads_site_id' => $site_id,
				'vads_trans_date' => date('YmdHis'),
				'vads_trans_id' => $trans_id,
				'vads_amount' => $montant,
				'vads_version' => 'V2',
			];

			$params['signature'] = Payzen_management::genere_signature($params, $signature);

			$chaine = '' ;
			$chaine .= '<form method="POST" action="https://secure.payzen.eu/vads-payment/">';

			foreach($params as $key => $value) {

				$chaine .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}

			$chaine .= '<br>';
			$chaine .= '<input type="submit" name="payer" value="Déclencher le prélévement" style="background-color: rgb(245, 184, 0); border-color: rgb(251, 212, 96); width: 100%;padding:5px;cursor;:pointer;border:solid 1px;" />';
				
			$chaine .= '</form>';

			return $chaine;
		}

		return '';
	}

	/**
	 *
	 * Réception de la notification Payzen
	 * Définir la route dans l'interface Payzen !
	 * https://www.lesite.fr/eden/mise_a_jour_cb/payzen/notification_token
	 *
	 */
    public function maj_carte_notification_token_payzen(Request $request) {

    	// La route est à paramétrer dans l'interface Payzen.
    	// Paramétrages/boutique/Statut de la règle "URL de notification à la fin du paiement"/URL de notification à la fin du paiement/URL à appeler en mode XXX

    	/* 
    	// Request de test, pour enregistrement d'un token SDD
    	$request = new \StdClass ;
    	$request->vads_trans_id = '000008' ;
    	$request->vads_identifier = 'c76ec58dfa064be7a285bc8db6d664a4' ;
    	$request->vads_page_action = 'REGISTER' ;
    	$request->vads_card_brand = 'SDD' ;
    	/* */

    	/* 
    	// Request de test, pour enregistrement d'un token CB
    	$request = new \StdClass ;
    	$request->vads_trans_id = '000008' ;
    	$request->vads_identifier = 'bc43573fe752474088e49d3576e837cc' ;
    	$request->vads_page_action = 'REGISTER' ;
    	$request->vads_card_brand = 'CB' ;
    	/* */

    	/*
    	// Request de test, pour enregistrement d'un paiement
    	$request = new \StdClass ;
    	$request->vads_cust_email = 'duncan@yopmail.com' ;
    	$request->vads_trans_uuid = '4465c935751e45d9adbadeb6cb61ec71' ;
    	$request->vads_page_action = 'PAYMENT' ;
	    $request->vads_trans_id = '000006' ;
	    $request->vads_trans_status = 'AUTHORISED' ;
    	/* */

		// Si le résultat ne corresponds pas à ce qu'on attends, on le log. Dossier storage/logs
		if(empty($request->vads_operation_type) && empty($request->{'kr-answer'})) {

			\Log::info("vads_operation_type et kr-answer introuvables");
			\Log::info($request);
		}

		if (isset($request->{'kr-answer'})) {

			// Retour via le formulaire javascript

			// Décodage de la réponse
			$reponse = json_decode($request->{'kr-answer'});

			if (!is_object($reponse)) {

				\Log::info("json reponse non objet");
				\Log::info($request);
				die();
			}

			if (!isset($reponse->transactions) || !isset($reponse->transactions[0]) || !isset($reponse->transactions[0]->paymentMethodToken) || !isset($reponse->orderDetails->orderId)) {

				\Log::info("impossible de trouver la transaction");
				\Log::info($request);
				die();
			}

			// On récupère la transaction
			$transaction = modele('transaction_paiement', intval($reponse->orderDetails->orderId));

			// Si on n'a pas la transaction, on log
			if (empty($transaction)) {

				\Log::info("La transaction n'existe pas");
				\Log::info($request);
				die();
			}

			// On récupère le client via son ID dans la transaction
			$client = management("client", $transaction->factures);

			// Si on n'a pas le client, on log
			if (!$client->existe()) {

				\Log::info("Le client n'existe pas");
				\Log::info($request);
				die();
			}

			// On sauvegarde le token obtenu dans le champ correspondant
			$client->enregistre(['portefeuille_payzen_cb' => $reponse->transactions[0]->paymentMethodToken]);



		} elseif($request->vads_operation_type == 'DEBIT') {

			// C'est une notification de paiement.

			// Si on n'a pas le vads trans id, on log
			if (empty($request->vads_trans_id) || empty($request->vads_trans_status) || ($request->vads_trans_status != 'AUTHORISED' && $request->vads_trans_status != 'CAPTURED')) {

				\Log::info($request);
				die('erreur paiement controller notification payment. Voir log');
			}	

			// On récupère la transaction
			$transaction = management('transaction_paiement', intval($request->vads_trans_id));

			// Si c'est un prélèvement manuel, non attribué à une facture
			if ($transaction->modele->type == 'prelevement manuel') {

				// On enregistre le paiement
				if($request->vads_card_brand == 'SDD') {
						
					$compte_bancaire_id = fonctionnalite('compte_bancaire_prelevement_payzen');
					$mode_paiement_id = fonctionnalite('id_mode_de_paiement_prelevement_payzen');
				} else {
					
					$compte_bancaire_id = fonctionnalite('compte_bancaire_cb_payzen');
					$mode_paiement_id = fonctionnalite('id_mode_de_paiement_cb_payzen');
				}

				$client = modele('client', $transaction->modele->factures);

				$informations = array(
					
					'date' => date('Y-m-d', strtotime($request->vads_effective_creation_date)),
					'compte_bancaire_id' => $compte_bancaire_id,
					'mode_paiement_id' => $mode_paiement_id,
					'montant' => $request->vads_effective_amount / 100,
					'client_id' => $client->id,
					'entite_id' => $client->entite_id,
				);
				management('paiement')->enregistre($informations);


			// Sinon, c'est un prélèvement attribué à une facture
			} else {

				$factures_id = json_decode($transaction->modele->factures) ;

				// Si on n'a pas le vads trans id, on log
				if(empty($factures_id)) {

					\Log::info("Les factures liées à la transaction bancaire n'ont pas été trouvées");
					\Log::info($request);
					\Log::info($factures_id);
					die("Les factures liées à la transaction bancaire n'ont pas été trouvées");
				}
				
				if(!empty($factures_id) && !is_array($factures_id))
					$factures_id = array($factures_id);

				// Et on valide le réglement des factures concernées
				$factures = modele('facture_vente')->whereIn('id', $factures_id)->get();
				
				foreach ($factures as $modele_facture) {
					
					if($request->vads_card_brand == 'SDD') {
						
						$compte_bancaire_id = fonctionnalite('compte_bancaire_prelevement_payzen');
						$mode_paiement_id = fonctionnalite('id_mode_de_paiement_prelevement_payzen');
					}
					else {
						
						$compte_bancaire_id = fonctionnalite('compte_bancaire_cb_payzen');
						$mode_paiement_id = fonctionnalite('id_mode_de_paiement_cb_payzen');
					}

					$informations = array(
						
						'date' => date('Y-m-d', strtotime($request->vads_effective_creation_date)),
						'compte_bancaire_id' => $compte_bancaire_id,
						'mode_paiement_id' => $mode_paiement_id,
						// 'montant' => $request->vads_effective_amount / 100,
						'montant' => $modele_facture->solde_document_ttc,
					);
					
					management('facture_vente', $modele_facture->id)->enregistre_paiement($informations);
					
				}
			}

		} else {
	
			// C'est une notification d'enregistrement d'alias

			// Si on n'a pas les champs nécessaires, on log
			if (
					empty($request->vads_trans_id) ||
					empty($request->vads_card_brand) ||
					( $request->vads_card_brand != 'CB' && $request->vads_card_brand != 'SDD' && $request->vads_card_brand != 'E-CARTEBLEUE' && $request->vads_card_brand != 'MASTERCARD' )
				) {

				\Log::info("On n'a pas vads_trans_id, vads_card_brand ou incorrect");
				\Log::info($request);
			}

			// On récupère la transaction
			$transaction = modele('transaction_paiement', intval($request->vads_trans_id));

			// Si on n'a pas la transaction, on log
			if (empty($transaction)) {

				\Log::info("La transaction n'existe pas");
				\Log::info($request);
			}

			// On récupère le client via son ID dans la transaction
			$client = management("client", $transaction->factures);

			// Si on n'a pas le client, on log
			if (!$client->existe()) {

				\Log::info("Le client n'existe pas");
				\Log::info($request);
			}

			// Sinon, on sauvegarde le token obtenu dans le champ correspondant
			if ($request->vads_card_brand == 'CB' || $request->vads_card_brand == 'MASTERCARD' || $request->vads_card_brand == 'VISA') {

				$client->enregistre(['portefeuille_payzen_cb' => $request->vads_identifier]);
			} elseif ($request->vads_card_brand == 'SDD' || $request->vads_card_brand == 'E-CARTEBLEUE') {

				$client->enregistre(['portefeuille_payzen_prelevement' => $request->vads_identifier]);
			}

		}
		
		echo "OK";
		exit;
    }


    public function maj_carte(Request $request, $plateforme, $client_id, $token) {

    	$nom_management = "App\\Eden\\Managements\\" . ucfirst($plateforme) . "_management";

		$paiement_management = new $nom_management();

		if(md5('eden' . $client_id) == $token) {

			$client = modele("client", $client_id);
			
			if ( $plateforme == 'payzen' ) {

				$site_id = config('services.payzen.site_id');
				$signature = config('services.payzen.signature') ;
				$mode = config('services.payzen.mode') ;

				if (empty($site_id) || empty($signature) || empty($mode))
					dd_eden('Site ID, signature, ou mode de payzen non renseignés');

				// On génère une transaction qui contiendra l'ID du client pour MAJ de son alias
		    	$transaction = management('transaction_paiement');
		    	$transaction->enregistre(['prestataire' => 'payzen_enregistrement', 'factures' => $client_id]);
		    	$trans_id = $transaction->generer_numero_transaction(6);

		    	// @todo mettre dans le management
				$params = [
					'vads_action_mode' => 'INTERACTIVE',
					'vads_ctx_mode' => $mode,
					'vads_currency' => '978',
					'vads_cust_email' => ( !empty($client->mail_comptable) ? $client->mail_comptable : $client->adresse_email ),
					'vads_page_action' => 'REGISTER',
					'vads_payment_config' => 'SINGLE',
					'vads_site_id' => $site_id,
					'vads_trans_date' => date('YmdHis'),
					'vads_trans_id' => $trans_id,
					'vads_url_return' => route('maj_carte', [$plateforme, $client_id, $token]),
					'vads_version' => 'V2',
				];

				$params['signature'] = Payzen_management::genere_signature($params, $signature);

				return view('eden::mise_a_jour_paiement_payzen', compact('plateforme', 'client', 'token', 'client_id', 'params'));
			} else {

				return view('eden::maj_carte', compact('plateforme', 'client', 'token', 'client_id'));
			}
		}

		exit();
    }

    /**
     *
     * Mise à jour du token Stripe en bdd
     *
     */
    public function maj_carte_post(Request $request, $plateforme, $client_id, $token) {

		if(md5('eden' . $client_id) == $token) {

			$informations = $request->all();

			$nom_management = "App\\Eden\\Managements\\" . ucfirst($plateforme) . "_management";

			$prestataire_paiement_management = new $nom_management(); 

			$retour_controle = $prestataire_paiement_management->controler_entrees_utilisateur($informations);

			if($retour_controle['statut'] == false) {

				return redirect()->back()->withErrors($retour_controle['retour']);
			}

	    	$client_management = management('client', $client_id);

	    	$client = $client_management->modele;

	    	$nom_portefeuille = 'portefeuille_' . $plateforme;

	    	$portefeuille = $client->{$nom_portefeuille};
			
			if($portefeuille != null) {

	    		$retour_ajout_carte = $prestataire_paiement_management->ajouter_carte($client, $request->all());

	    		if($retour_ajout_carte['statut']) {
					
					return redirect()->back()->with('ok', $retour_ajout_carte['message']);
	    		} 
				else {

	    			return redirect()->back()->withErrors($retour_ajout_carte['message']);
	    		}
	    	} 
			else {

	    		$retour_creation_client = $prestataire_paiement_management->creer_client($client, $request->all());

				if($retour_creation_client['statut']) {

	    			$modifications = array(

			    		'portefeuille_' . $plateforme => $retour_creation_client['retour'],
			    	);

			    	$client_management->enregistre($modifications);

			    	return redirect()->back()->with('ok', traduction('messages.php.paiement.carte_enregistree'));
	    		} 
				else {
	    			return redirect()->back()->WithErrors(traduction('messages.php.paiement.erreur_enregistrement_carte',null,[$retour_creation_client['retour']['result']['longMessage']]));
	    		}
	    	}
	    }

	    exit();    	
    }

    /**
	 *
	 * Cette méthode tente de régler tous les documents non réglés, via payline
	 * 
	 * Elle est normalement appelée via une tache CRON
	 *
     */
    public function interface_paiement_factures_payline() {
		
		return view('eden::interface_paiement_factures_payline');
    }
    
	/**
	 * 
	 * Cette méthode tente de régler tous les documents non réglés, via payzen
	 * 
	 * Elle est normalement appelée via une tache CRON
	 * 
	 */
    public function interface_paiement_factures_payzen() {

		return view('eden::interface_paiement_factures_payzen');
    }


    public function paiement_factures_payzen() {
		
		if(strtolower(env('APP_ENV')) != 'prod') {
			
			exception("Cette page ne peut être qu'appelée depuis un projet avec APP_ENV=prod");
		}

		$payzen_management = new \App\Eden\Managements\Payzen_management();
		
    	$factures = modele('facture_vente')
						->zero_ou_null('regle')
						->zero_ou_null('annulee_par_avoir')
			    		->where('valide', 1)
						->where('date_de_reglement', '<=', date('Y-m-d'))
						->get();
						
		$factures_a_regler = array();
						
		$transactions_reussies = array();
		$total_succes = 0;
		
		$transactions_echouees = array();
		$total_echec = 0;
		
		foreach ($factures as $facture) {
			
			$client = modele('client', $facture->client_id);
			
			if(empty($client->portefeuille_payzen_cb))	// @todo quand PCI DSS, utiliser également portefeuille_payzen_prelevement
				continue;
			
			$document_management = management('facture_vente', $facture->id);

			// On vérifie si on a le droit de procéder au prélèvement
			if($document_management->autoriser_prelevement_automatique() === false)
				continue;
			
			// on regarde si y'a des échéances
			$echeances = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture->id)->get();
			
			if($echeances !== null) {
				
				// il y a des échéances, on va retoucher à la volée 
				// on regarde les échéances à venir
				$echeances_a_venir = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture->id)->where('date', '>', date('Y-m-d'))->sum('montant');
				
				// on les retranche du solde
				$facture->solde_document_ttc -= $echeances_a_venir;
			}
			
			// si on a un solde négatif, on passe
			if($facture->solde_document_ttc <= 0)
				continue;

			$retour = $payzen_management->genere_paiement_via_portefeuille($facture->solde_document_ttc, $facture->client_id, $client->portefeuille_payzen_cb, $facture->id);

			if($retour['status'] == 'SUCCESS' && $retour['answer']['orderStatus'] == 'PAID') {
				
				$informations = array(
					
					'montant' => $facture->solde_document_ttc,
					'mode_paiement_id' => fonctionnalite('id_mode_de_paiement_cb_payzen'),
					'compte_bancaire_id' => fonctionnalite('compte_bancaire_cb_payzen'),
				);

				$document_management->ajouter_paiement('facture_vente', $facture->id, $informations);
				
				$transactions_reussies[] = array(
					
					'document_id' => $facture->id,
					'type_element' => 'facture_vente',
					'client_id' => $facture->client_id,
					'montant' => $facture->solde_document_ttc,
				);
				
				$total_succes += $facture->solde_document_ttc;
			}
			else {
				
				$message_erreur = 'Erreur inconnue';	
				if(isset($retour['answer']['detailedErrorMessage']))	
					$message_erreur = $retour['answer']['detailedErrorMessage'];	
				elseif(isset($retour['answer']['transactions']) && isset($retour['answer']['transactions'][0]) && isset($retour['answer']['transactions'][0]['errorMessage']))	
					$message_erreur = $retour['answer']['transactions'][0]['errorMessage'];	

				
				$transactions_echouees[] = array(
					
					'document_id' => $facture->id,
					'type_element' => 'facture_vente',
					'client_id' => $facture->client_id,
					'montant' => $facture->solde_document_ttc,
					'erreur' => $message_erreur,
				);
				
				$total_echec += $facture->solde_document_ttc;
			}
		}
		
		// on envoie le mail récap
		$paiement_management = management('paiement');
		
		$parametres_email = array(
		
			'transactions_reussies' => $transactions_reussies,
			'transactions_echouees' => $transactions_echouees,
			'total_succes' => $total_succes,
			'total_echec' => $total_echec,
			'prestataire' => 'Payzen',
		);
		
		$resultat = $paiement_management->envoie_mail_recap_debits($parametres_email);
		
		return view('eden::message_retour_standard', array(
			'titre' => traduction('messages.php.paiement.debit_cb'),
			'titre_texte' => traduction('messages.php.paiement.encaisemment_effectue'),
			'texte' => traduction('messages.php.paiement.recap_debit_cb',null,[$total_succes." ".maquette('devise_application_symbole'),$total_echec." ". maquette('devise_application_symbole')]),
		));
    }

    
	/**
	 * 
	 * Cette méthode tente de régler tous les documents non réglés, via payline
	 * 
	 * Elle est normalement appelée via une tache CRON
	 * 
	 */
    public function paiement_factures_payline() {

    	if(strtolower(env('APP_ENV')) != 'prod') {
			
			exception("Cette page ne peut être qu'appelée depuis un projet avec APP_ENV=prod");
		}

		$payline_management = new \App\Eden\Managements\Payline_management();
		
    	$factures = modele('facture_vente')
						->zero_ou_null('regle')
						->zero_ou_null('annulee_par_avoir')
			    		->where('valide', 1)
						->where('date_de_reglement', '<=', date('Y-m-d'))
						// ->where('id', 39686)
			    		->get();
						
		$factures_a_regler = array();
						
		$transactions_reussies = array();
		$total_succes = 0;
		
		$transactions_echouees = array();
		$total_echec = 0;
		
		foreach($factures as $facture) {
			
			$client = modele('client', $facture->client_id);
			
			if(empty($client->portefeuille_payline))
				continue;
			
			
			$document_management = management('facture_vente', $facture->id);
			
			// On vérifie si on a le droit de procéder au prélèvement
			if($document_management->autoriser_prelevement_automatique() === false)
				continue;
			
			// on regarde si y'a des échéances
			$echeances = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture->id)->get();
			
			if($echeances !== null) {
				
				// il y a des échéances, on va retoucher à la volée 
				// on regarde les échéances à venir
				$echeances_a_venir = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture->id)->where('date', '>', date('Y-m-d'))->sum('montant');
				
				// on les retranche du solde
				$facture->solde_document_ttc -= $echeances_a_venir;
			}
			
			// si on a un solde négatif, on passe
			if($facture->solde_document_ttc <= 0)
				continue;
			
			$retour = $payline_management->genere_paiement_via_portefeuille($facture, $client->portefeuille_payline);
			
			if($retour['result']['code'] == '00000') {
				
				$informations = array(
					
					'montant' => $facture->solde_document_ttc,
					'mode_paiement_id' => fonctionnalite('id_mode_de_paiement_cb_payline'),
					'compte_bancaire_id' => fonctionnalite('compte_bancaire_cb_payline'),
				);
				
				$document_management->ajouter_paiement('facture_vente', $facture->id, $informations);
				
				$transactions_reussies[] = array(
					
					'document_id' => $facture->id,
					'type_element' => 'facture_vente',
					'client_id' => $facture->client_id,
					'montant' => $facture->solde_document_ttc,
				);
				
				$total_succes += $facture->solde_document_ttc;
			}
			else {
				
				$transactions_echouees[] = array(
					
					'document_id' => $facture->id,
					'type_element' => 'facture_vente',
					'client_id' => $facture->client_id,
					'montant' => $facture->solde_document_ttc,
					'erreur' => $retour['result']['longMessage'],
				);
				
				$total_echec += $facture->solde_document_ttc;
			}
		}
		
		// on evoie le mail récap
		$paiement_management = management('paiement');
		
		$parametres_email = array(
		
			'transactions_reussies' => $transactions_reussies,
			'transactions_echouees' => $transactions_echouees,
			'total_succes' => $total_succes,
			'total_echec' => $total_echec,
			'prestataire' => 'Payline',
		);
		
		$resultat = $paiement_management->envoie_mail_recap_debits($parametres_email);
		
		return view('eden::message_retour_standard', array(
			'titre' => traduction('messages.php.paiement.debit_cb'),
			'titre_texte' => traduction('messages.php.paiement.encaisemment_effectue'),
			'texte' => traduction('messages.php.paiement.recap_debit_cb',null,[$total_succes." ".maquette('devise_application_symbole'),$total_echec." ". maquette('devise_application_symbole')]),
		));
    }

    /**
     *
     * Afficher page de paiement de facture
     *
     */
    public function paiement_facture($plateforme, $facture_id, $token) {

		if(md5('eden' . $facture_id) == $token) {

			$facture = modele("facture_vente", $facture_id);
			$client = modele("client", $facture->client_id);
			$redirect_url = "";

			if($plateforme == "payline") {

				$paiement_management = new \App\Eden\Managements\Payline_management();

				$redirect_url = $paiement_management->paiement($facture)['redirectURL'];
			
			} elseif($plateforme == "payzen") {

				// @todo ? je sais pas a quoi ca doit servir
				$paiement_management = new \App\Eden\Managements\Payzen_management();

				$redirect_url = $paiement_management->paiement($facture)['redirectURL'];
			}
			
			return view('eden::paiement_facture', compact('client', 'facture', 'plateforme', 'token', 'facture_id', 'redirect_url'));
		}

		exit();
    }

    /**
     *
     * Enregistrer le paiement
     *
     */
    public function paiement_facture_post(Request $requete, $plateforme, $facture_id, $token) {

    	if(md5('eden' . $facture_id) == $token) {

			$nom_management = "App\\Eden\\Managements\\" . ucfirst($plateforme) . "_management";

    		$paiement_management = new $nom_management();

			$facture = management("facture_vente", $facture_id);
    		$client = modele('client', $facture->modele->client_id);

			$paiement = $paiement_management->paiement(null, $facture->modele, 'Paiement de la facture ' . $facture->modele->reference_document, $requete->all());

			if($paiement['statut']) {
				
				$informations = array(
					
					'date' => date('Y-m-d'), 
					'montant' => $facture->modele->solde_document_ttc,
				);
				
				if(strtoupper($plateforme) == 'PAYLINE') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_payline');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_payline');
				}
				if(strtoupper($plateforme) == 'STRIPE') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_stripe');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_stripe');
				}
				if(strtoupper($plateforme) == 'PAYZEN') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_payzen');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_payzen');
				}
				
				$facture->ajouter_paiement('facture_vente', $facture->modele->id, $informations);

				return redirect()->back()->with('ok', traduction('messages.php.paiement.paiement_succes'));
			}
			
			return redirect()->back()->withErrors(traduction('messages.php.paiement.paiement_echec'));
		}

		exit();
    }
	
	/**
	 * 
	 * URL de retour pour le paiement d'une facture (pour l'utilisateur, voir retour_paiement_notification pour la notification)
	 * 
	 */
    public function retour_paiement(Request $requete, $plateforme, $facture_id) {

    	$nom_management = "App\\Eden\\Managements\\" . ucfirst($plateforme) . "_management";

    	$paiement_management = new $nom_management();

    	$retour = $paiement_management->statut_paiement($requete->all());

    	if($retour['statut']) {

			// @note frédéric: a priori ça n'a rien à faire là, ça doit être traité dans la notification
    		$management_facture = management('facture_vente', $facture_id);
			
			if($management_facture->modele->solde_document_ttc > 0) {
				
				$informations = array(
					
					'date' => date('Y-m-d'), 
					'montant' => $facture->modele->solde_document_ttc,
				);
				
				if(strtoupper($nom_management) == 'PAYLINE') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_payline');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_payline');
				}
				if(strtoupper($nom_management) == 'STRIPE') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_stripe');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_stripe');
				}
				if(strtoupper($nom_management) == 'PAYZEN') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_payzen');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_payzen');
				}
				
				$management_facture->ajouter_paiement('facture_vente', $management_facture->modele->id, $informations);
			}

    		return view('eden::retour_paiement', array('ok' => traduction('messages.php.paiement.paiement_succes'), 'message' => $retour['message']));
    	}

    	return view('eden::retour_paiement', array('erreur' => traduction('messages.php.paiement.paiement_echec'), 'message' => $retour['message']));
    }

    public function retour_paiement_annulation(Request $requete) {

    	return view('eden::retour_paiement', array('warning' => traduction('messages.php.paiement.paiement_annule')));
	}
	
	public function prelevement_manuel($client_id) {

		return view('eden::prelevement_manuel', ['client_id' => $client_id]);
	}

	public function prelevement_manuel_post(Request $formulaire) {

		$montant = $formulaire->montant;
		$client_id = $formulaire->client_id;

		$client = modele('client', $client_id);

		// on vérifie si le client a un portefeuille payzen pour le prélévement
		if(empty($client->portefeuille_payzen_prelevement)) {

			session()->flash('erreur_prelevement_mannuel', traduction('messages.php.paiement.client_portefeuille_payzen_introuvable'));
			return redirect()->back();
		}

		// on vérifie si le montant est > 0
		if($montant <= 0) {

			session()->flash('erreur_prelevement_mannuel', traduction('messages.php.paiement.montant_inferieur_zero'));
			return redirect()->back();
		}

		$chaine = '<iframe style="width:400px;height:160px;" src="'.route('paiement_prelevenement_manuel_payzen', [$client->id, md5('eden'.$client->id), $montant]).'"> </iframe>' ;


		return view('eden::paiement_prelevement_manuel', compact('client', 'montant', 'chaine'));
	}

	/**
	 * 
	 * Traite la notification reçue de la part du prestataire de paiement
	 * 
	 */
    public function retour_paiement_notification(Request $requete, $plateforme, $facture_id) {
		
		$nom_management = "App\\Eden\\Managements\\" . ucfirst($plateforme) . "_management";

    	$paiement_management = new $nom_management();

    	$retour = $paiement_management->statut_paiement($requete->all());

    	if($retour['statut'] === true) {

    		$management_facture = management('facture_vente', $facture_id);
			
			if($management_facture->modele->solde_document_ttc > 0) {
				
				// on va chercher le montant via les échéances
				$echeances = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture_id)->get();
				
				if($echeances !== null) {
					
					// il y a des échéances, on va retoucher à la volée 
					// on regarde les échéances à venir
					$echeances_a_venir = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture_id)->where('date', '>', date('Y-m-d'))->sum('montant');
					
					// on les retranche du solde
					$management_facture->modele->solde_document_ttc -= $echeances_a_venir;
				}
				
				$informations = array(
					
					'date' => date('Y-m-d'), 
					'montant' => $management_facture->modele->solde_document_ttc,
				);
				
				if(strtoupper($nom_management) == 'PAYLINE') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_payline');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_payline');
				}
				if(strtoupper($nom_management) == 'STRIPE') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_stripe');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_stripe');
				}
				if(strtoupper($nom_management) == 'PAYZEN') {
					
					$informations['mode_paiement_id'] = fonctionnalite('id_mode_de_paiement_cb_payzen');
					$informations['compte_bancaire_id'] = fonctionnalite('compte_bancaire_cb_payzen');
				}
				
				$management_facture->ajouter_paiement('facture_vente', $management_facture->modele->id, $informations);
			}

    		return view('eden::retour_paiement', array('ok' => 'Paiement réussi', 'message' => $retour['message']));
    	}
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

