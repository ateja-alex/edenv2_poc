<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use PaylineSDK;
use PDF;
use Mail;

class Encaissement_cb_controller extends Controller {

	public function index() {

    	return view('eden::encaissement_cb');
    }

	public function encaisse(Request $formulaire) {
		
		$paiement_management = false;

		// On vérifie que le montant existe
        if(empty($formulaire->get('montant'))) {

			return redirect()->route('encaissement_cb.index')->withErrors(traduction('messages.php.encaissement.montant_obligatoire'));
		}

        // On vérifie que le montant est un numérique
        if(is_numeric($formulaire->get('montant')) === false) {

			return redirect()->route('encaissement_cb.index')->withErrors(traduction('messages.php.encaissement.erreur_format_montant'));
        }

        // On vérifie que le montant est bien un montant pour un encaissement!
        if($formulaire->get('montant') < 0) {

			return redirect()->route('encaissement_cb.index')->withErrors(traduction('messages.php.encaissement.montant_negatif'));
        }

		// On vérifie que le montant existe
        if(empty($formulaire->get('client_id'))) {

			return redirect()->route('encaissement_cb.index')->withErrors(traduction('messages.php.encaissement.client_obligatoire'));
        }

        // On vérifie que le client a bien un portefeuille
        $client = management('client', $formulaire->get('client_id'));
		
		/*
        if(empty($client->recupere_portefeuille())) {

			if($formulaire->get('debiter_client') == 1) {

				return redirect()->route('encaissement_cb.index')->withErrors("Le client n'a pas de CB (portefeuille) associé à son compte!");
			}
        }
		*/


		if($formulaire->get('debiter_client') == 1) {
			
			$encaissement_service = service('encaissement_cb');
			
			$methodes_de_paiement = $encaissement_service->recupere_methodes_de_paiement();
			
			$portefeuille_trouve = false;
			$encaissement_ok = false;
			
			foreach($methodes_de_paiement as $methode) {
				
				$portefeuille = false;
				
				if($methode == 'payzen') {
					
					$portefeuille = $client->modele->portefeuille_payzen_cb;
					
					if(!empty($portefeuille)) {
						
						$resultat = $encaissement_service->gere_paiement_payzen($formulaire->montant, $portefeuille, $client->modele->id);
						
						if($resultat['succes'] === true) {
							
							$encaissement_ok = true;
							
							$id_mode_paiement = fonctionnalite('id_mode_de_paiement_cb_payzen');
							$id_compte_bancaire = fonctionnalite('compte_bancaire_cb_payzen');
							
							break;
						}
						else {
							
							return redirect()->route('encaissement_cb.index')->withErrors($resultat['erreur']);
						}
					}
				}
				elseif($methode == 'payline') {
					
					$portefeuille = $client->modele->portefeuille_payline;
					
					if(!empty($portefeuille)) {
						
						$resultat = $encaissement_service->gere_paiement_payline($formulaire->montant, $portefeuille);
						
						if($resultat['succes'] === true) {
							
							$encaissement_ok = true;
							
							$id_mode_paiement = fonctionnalite('id_mode_de_paiement_cb_payline');
							$id_compte_bancaire = fonctionnalite('compte_bancaire_cb_payline');
							
							break;
						}
						else {
							
							return redirect()->route('encaissement_cb.index')->withErrors($resultat['erreur']);
						}
					}
				}
				
				if(empty($portefeuille))
					continue;
				
				// on enregistre le fait qu'on ait trouvé un portefeuille
				$portefeuille_trouve = true;
			}
			
			
			if($encaissement_ok === true) {
				
				// on enregistre le paiement
				$paiement_management = management('paiement');

				$infos = array(

					'client_id' => $client->modele->id,
					'mode_paiement_id' => $id_mode_paiement,
					'compte_bancaire_id' => $id_compte_bancaire,
					'entite_id' => $client->modele->entite_id,
					'date' => date('Y-m-d'),
					'titre' => "Encaissement CB manuel",
					'montant' => $formulaire->montant,
				);

				$paiement_management->enregistre_sans_profil($infos);
			}
			else {
				
				return redirect()->route('encaissement_cb.index')->withErrors(traduction('messages.php.encaissement.debitement_impossible'));
			}
		}


		// on génère le PDF du reçu
		$donnees_pour_pdf = array(

			'client' => $client,
			'montant' => $formulaire->montant,
			'adresse_bail' => $formulaire->adresse_bureaux,
			'date_signature' => $formulaire->date_signature_bail,
			'date_debut' => $formulaire->date_debut_bail,
		);

		$pdf = PDF::loadView('eden::pdf.encaissement_cb', $donnees_pour_pdf);

		\Storage::put('public/encaissement_cb.pdf', $pdf->output());

		// c'est le cas ou il n'y a pas eu de paiement par CB
		if($paiement_management === false) {
			
			$paiement_management = management('paiement');
		}
		
		$paiement_management->methode_post_formulaire_encaissement_cb($formulaire);

		return redirect()->route('encaissement_cb.index')->with('message', traduction('messages.php.encaissement.encaissement_succes'));
    }
}
