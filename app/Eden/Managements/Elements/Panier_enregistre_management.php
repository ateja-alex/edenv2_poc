<?php

namespace App\Eden\Managements\Elements;

class Panier_enregistre_management extends Element_management {
	
	/**
	 * 
	 * Réinitialise un panier avec une liste d'articles donnnés
	 * 
	 */
	public function initialise_commande_avec_articles($articles) {
		
		// on passe le panier en cours en statut OFF
		$paniers = modele('panier')->where('client_id', session()->get('utilisateur_eden_ecommerce'))->where('statut', 0)->get();
		
		foreach($paniers as $panier) {
			
			$panier->statut = 1;
			$panier->save();
		}
		
		// on crée un nouveau panier
		$panier_management = management('panier');
		$panier_management->recupere();
		
		foreach($articles as $article) {
			
			$formulaire = new \StdClass;
			
			$formulaire->tarif_ht = $article->tarif_ht;
			$formulaire->quantite = $article->quantite;
			$formulaire->declinaison_id = $article->declinaison_id;
			$formulaire->article_id = $article->article_id;
			
			$panier_management->ajoute_produit($formulaire);
		}
		
		return true;
	}
	
}