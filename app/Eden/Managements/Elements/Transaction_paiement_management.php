<?php

namespace App\Eden\Managements\Elements;


class Transaction_paiement_management extends Element_management {

	/**
	 * 
	 * Crée une transaction
	 * Parametre 1 : Nom du prestataire
	 * Parametre 2 : Collection de factures
	 * 
	 */
	public function creer_transaction($prestataire, $factures) {
		
		// @todo mettre des sécurités sur les factures (model facture_vente, non vide...)

		$modifications = array(
		
			'prestataire' => $prestataire,
			'factures' => json_encode($factures->pluck('id')->toArray()),
		);
		
		$this->enregistre($modifications);
	}

	/**
	 * 
	 * Crée une transaction
	 * Parametre 1 : Nom du prestataire
	 * Parametre 2 : ID facture
	 * 
	 */
	public function creer_transaction_facture($prestataire, $facture_id) {
		
		$modifications = array(
		
			'prestataire' => $prestataire,
			'factures' => $facture_id,
		);
						
		$this->enregistre($modifications);
	}

	public function creer_transaction_pour_prelevement_manuel($prestataire, $client_id) {

		$modifications = [
			'prestataire' => $prestataire,
			'type' => 'prelevement manuel',
			'factures' => $client_id,
		];
		
		$this->enregistre($modifications);
	}


	/**
	 * 
	 * Génère un numéro de transaction de type "000006" si on lui demande d'afficher 6 caractères.
	 * 
	 */
	public function generer_numero_transaction($nombre_caracteres) {

		return sprintf("%0".$nombre_caracteres."s", $this->modele->id);
	}

}