<?php

namespace App\Eden\Managements\Tests;

class Gestion_des_stocks_test extends Feature_test {
	
	/**
	 *
	 * On va réaliser les différents tests
	 *
	 */
	public function teste() {
		
		// on cleane les données
		$ids = modele('article')->where('code_article', 'test_gestion_des_stocks')->get()->pluck('id')->toArray();
		
		modele('stock_initial')->whereIn('article_id', $ids)->delete();
		modele('conditionnement')->whereIn('article_id', $ids)->delete();
		modele('article_fournisseur')->whereIn('article_id', $ids)->delete();
		modele('conditionnement')->whereIn('article_id', $ids)->delete();
		modele('article')->whereIn('id', $ids)->delete();
		
		// test 1 : on crée un article
		$management_article = management('article');
		
		$donnees = array(
			'code_article' => 'test_gestion_des_stocks',
			'designation' => 'test gestion des stocks',
			'stockable' => 1,
			'famille_id' => modele('famille')->first()->id,
		);
		
		$retour = $management_article->enregistre($donnees);
		
		if($retour !== true)
			return $retour;

        // On energistre un conditionnement qui sera utilisé pour la table conversion unite
        $management_conditionnement = management('conditionnement');

        $informations = array(

            'article_id' => $management_article->modele->id,
            'quantite' => 5,
            'nom' => 'Test conditionnement conversion unite',

        );

        $retour = $management_conditionnement->enregistre($informations);

        if($retour !== true)
            return $retour;

        $id_conditionnement = $management_conditionnement->modele->id;

        // On energistre un conditionnement qui sera utilisé pour la table conversion unite
        $management_conditionnement_fournisseur = management('conditionnement');

        $informations = array(

            'article_id' => $management_article->modele->id,
            'quantite' => 10,
            'nom' => 'Test conditionnement fournisseur',

        );

        $retour = $management_conditionnement_fournisseur->enregistre($informations);

        if($retour !== true)
            return $retour;

        $id_conditionnement_fournisseur = $management_conditionnement_fournisseur->modele->id;
		
		// on lui crée un conditionnement d'achat
		$fournisseur_id = $this->recupere_premier_id_pour_type_element('fournisseur');
		
		$informations = array(
			
			'article_id' => $management_article->modele->id,
			'fournisseur_id' => $fournisseur_id,
			'conditionnement_id' => $id_conditionnement_fournisseur,
		);
		
		$management_article_fournisseur = management('article_fournisseur');
		
		$retour = $management_article_fournisseur->enregistre($informations);
		
		if($retour !== true)
			return $retour;
		
		// on lui crée un conditionnement de vente
		$fournisseur_id = $this->recupere_premier_id_pour_type_element('fournisseur');

		// on lui affecte un stock initial
		$informations = array(
			
			'article_id' => $management_article->modele->id,
			'entrepot_id' => 1,
			'stock_initial' => 10,
			'conditionnement_id' => $id_conditionnement,
			'quantite_conditionnement' => 1,
			'type_conditionnement' => 'vente',
		);
		
		$management_stock_initial = management('stock_initial');
		
		$retour = $management_stock_initial->enregistre($informations);
		
		if($retour !== true)
			return $retour;
		
		// on vérifie les stocks
		$modele_article = modele('article', $management_article->modele->id);

		if($modele_article->stock_actuel != 10)
			return "Erreur dans le stock actuel après initialisation du stock initial (théoriquement : 10, en bdd : ".$modele_article->stock_actuel.")";

		if(!empty($modele_article->stock_reserve))
			return "Erreur dans le stock réservé après initialisation du stock initial (théoriquement : 0, en bdd : ".$modele_article->stock_reserve.")";

		if(!empty($modele_article->stock_a_recevoir))
			return "Erreur dans le stock à recevoir après initialisation du stock initial (théoriquement : 0, en bdd : ".$modele_article->stock_a_recevoir.")";
		
		// on crée une commande de vente
		$management_commande_vente = management('commande_vente');
		
		$client_id = $this->recupere_premier_id_pour_type_element('client');
		
		$informations = array(
			
			'client_id' => $client_id,
			'date' => date('Y-m-d'),
			'articles' => array(
				
				array(
					
					'article_id' => $modele_article->id,
					'tarif' => 100,
					'quantite' => 4,
					'designation' => 'test mouvement de stocks',
				),
			),
		);
		
		$retour = $management_commande_vente->enregistre($informations);
		
		if($retour !== true)
			return $retour;
		
		$this->verifie_stocks(10, -4, 0, $modele_article->id, 'enregistrement de la commande');
		
		// on valide la commande
		$retour = $management_commande_vente->valide();
		
		if($retour !== true)
			return $retour;
		
		$this->verifie_stocks(10, -4, 0, $modele_article->id, 'validation de la commande');
		
		// on supprime la commande pour vérifier les stocks après suppression
		$retour = $management_commande_vente->supprime();
		
		if($retour !== true)
			return $retour;
		
		$this->verifie_stocks(10, 0, 0, $modele_article->id, 'suppression de la commande');
		
		// on recrée la commande et on la valide à nouveau
		$informations = array(
			
			'client_id' => $client_id,
			'date' => date('Y-m-d'),
			'articles' => array(
				
				array(
					
					'article_id' => $modele_article->id,
					'tarif' => 100,
					'quantite' => 4,
					'designation' => 'test mouvement de stocks',
				),
			),
		);
		
		$management_commande_vente = management('commande_vente');
		
		$management_commande_vente->enregistre($informations);
		
		// on valide la commande
		$retour = $management_commande_vente->valide();
		
		// ensuite on crée un BL
		$informations = array(

			'client_id' => $client_id,
			'date' => date('Y-m-d'),
			'articles' => array(
				
				array(
					
					'article_id' => $modele_article->id,
					'tarif' => 100,
					'quantite' => 4,
					'designation' => 'test mouvement de stocks',
				),
			),
		);
		
		$management_bl_vente = management('bl_vente');
		$retour = $management_bl_vente->enregistre($informations);
		
		if($retour !== true)
			return $retour;
		
		// tant que le BL n'est pas validé, les stocks ne sont pas décomptés
		$this->verifie_stocks(6, 0, 0, $modele_article->id, 'création du BL');
		
		// on valide le BL
		$retour = $management_bl_vente->valide();
		
		if($retour !== true)
			return $retour;
		
		// bl validé, les stocks sont décomptés
		$this->verifie_stocks(6, 0, 0, $modele_article->id, 'validation du BL', true);
		
		return true;
	}
	
	protected function verifie_stocks($stock_actuel, $stock_reserve, $stock_a_recevoir, $id_article, $moment, $debug = false) {
		
		$modele_article = modele('article', $id_article);

		// stock actuel
		if($modele_article->stock_actuel != $stock_actuel)
			exception("Erreur #1 dans le stock actuel après $moment (théoriquement : $stock_actuel, en bdd : ".$modele_article->stock_actuel." (article_id = $id_article))");

		// stock réservé
		if(!empty($modele_article->stock_reserve) && empty($stock_reserve))
			exception("Erreur #2 dans le stock réservé après $moment (théoriquement : 0, en bdd : ".$modele_article->stock_reserve.") (article_id = $id_article)");

		if($modele_article->stock_reserve != $stock_reserve)
			exception("Erreur #3 dans le stock réservé après $moment (théoriquement : $stock_reserve, en bdd : ".$modele_article->stock_reserve.") (article_id = $id_article)");
		
		// stock à recevoir
		if(!empty($modele_article->stock_a_recevoir) && empty($stock_a_recevoir))
			exception("Erreur #4 dans le stock à recevoir après $moment (théoriquement : 0, en bdd : ".$modele_article->stock_a_recevoir.") (article_id = $id_article)");

		if($modele_article->stock_a_recevoir != $stock_a_recevoir)
			exception("Erreur #5 dans le stock à recevoir après $moment (théoriquement : $stock_a_recevoir, en bdd : ".$modele_article->stock_a_recevoir.") (article_id = $id_article)");
		
		return true;
	}
}