<?php

namespace App\Eden\Managements\Tests;

class Transformation_a_la_ligne_test extends Element_test {

	/*
	 *
	 * On va réaliser les différents tests
	 *
	 */
	public function teste() {
		
		// Création d'un article classique
		$management_article_classique = $this->creation_element('article', array('type_article' => 0, 'stockable' => 1, 'code_article' => 'article_classique'.microtime(true)));
		
		// Création d'un article nomenclature
		$management_article_nomenclature = $this->creation_element('article', array('type_article' => 1, 'stockable' => 1, 'code_article' => 'article_nomenclature'.microtime(true)));
		
		// Création d'un conditionnement de 10 pour cet article
		$management_conditionnement_vente_a_10 = $this->creation_element('conditionnement', array(
		
			'article_id' => $management_article_classique->modele->id, 
			'quantite' => 10,
		));
		
		// Création d'un conditionnement de 5 pour cet article
		$management_conditionnement_vente_a_5 = $this->creation_element('conditionnement', array(
		
			'article_id' => $management_article_classique->modele->id, 
			'quantite' => 5,
		));

		// Création d'un fournisseur
		$management_fournisseur = $this->creation_element('fournisseur');
        
		// Création d'une conditionnement achat
		$management_article_fournisseur = $this->creation_element('article_fournisseur', array(
		
			'article_id' => $management_article_classique->modele->id, 
			'conditionnement' => 10,
			'fournisseur_id' => $management_fournisseur->modele->id,
		));
		
        // Création d'un devis de vente
		$management_devis_vente = $this->creation_element('devis_vente', array(
			
			'articles' => array(
			
				// article classique sans conditionnement
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 10,
				),
				
				// article classique avec conditionnement
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 10,
					'conditionnement' => $management_conditionnement_vente_a_10->modele->id,
				),
				
				// article nomenclature
				array(
					'article_id' => $management_article_nomenclature->modele->id,
					'quantite' => 5,
					// 'conditionnement' => $management_conditionnement_vente_a_10->modele->id,
					'nomenclature' => array(
						
						// un article classique dans la nomenclature, sans conditionnement
						array(
							'article_id' => $management_article_classique->modele->id,
							'quantite' => 10,
							'designation' => 'article 1 dans nomenclature',
							'tarif' => 0,
						),
						
						// un article classique dans la nomenclature, avec conditionnement
						array(
							'article_id' => $management_article_classique->modele->id,
							'quantite' => 10,
							'conditionnement' => $management_conditionnement_vente_a_5->modele->id,
							'designation' => 'article 2 dans nomenclature',
							'tarif' => 0,
						),
					),
				),
				
				// 2eme article nomenclature
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 5,
					'conditionnement' => $management_conditionnement_vente_a_10->modele->id,
					'nomenclature' => array(
						
						// un article classique dans la nomenclature, sans conditionnement
						array(
							'article_id' => $management_article_classique->modele->id,
							'quantite' => 10,
							'designation' => 'article 1 dans nomenclature',
							'tarif' => 0,
						),
						
						// un article classique dans la nomenclature, avec conditionnement
						array(
							'article_id' => $management_article_classique->modele->id,
							'quantite' => 10,
							'conditionnement' => $management_conditionnement_vente_a_5->modele->id,
							'designation' => 'article 2 dans nomenclature',
							'tarif' => 0,
						),
					),
				),
			),
		));
		
		
		// 1ere série de tests :
		// on vérifie les quantités dans le devis, dans les reliquats
		$articles_devis = $management_devis_vente->articles_avec_lignes_nomenclature();
		
		// il faut noter que les 2 premières lignes correspondent aux lignes de la nomenclature car elles n'ont pas le champ "ligne" renseigné
		
		// 1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)
		$this->verification_valeurs($articles_devis[0], array('transforme' => 0, 'transforme_reliquat' => 50), '1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		// 2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)
		$this->verification_valeurs($articles_devis[1], array('transforme' => 0, 'transforme_reliquat' => 250), '2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		
		// 1ere ligne de la nomenclature (2eme nomenclature avec conditionnement)
		$this->verification_valeurs($articles_devis[2], array('transforme' => 0, 'transforme_reliquat' => 500), '1ere ligne de la nomenclature (2eme nomenclature avec conditionnement)');
		// 2eme ligne de la nomenclature (2eme nomenclature avec conditionnement)
		$this->verification_valeurs($articles_devis[3], array('transforme' => 0, 'transforme_reliquat' => 2500), '2eme ligne de la nomenclature (2eme nomenclature avec conditionnement)');
		
		// article classique sans conditionnement
		$this->verification_valeurs($articles_devis[4], array('transforme' => 0, 'transforme_reliquat' => 10), 'article classique sans conditionnement');
		// article classique avec conditionnement
		$this->verification_valeurs($articles_devis[5], array('transforme' => 0, 'transforme_reliquat' => 100), 'article classique avec conditionnement');
		// ligne 1ere nomenclature
		$this->verification_valeurs($articles_devis[6], array('transforme' => 0, 'transforme_reliquat' => 5), 'ligne 1ere nomenclature');
		// ligne 2eme nomenclature
		$this->verification_valeurs($articles_devis[7], array('transforme' => 0, 'transforme_reliquat' => 50), 'ligne 2eme nomenclature');
		
        // on valide le devis
		$management_devis_vente = $this->valide_document($management_devis_vente);
		
		// on le transforme en commande vente
		$management_commande_vente = $this->transforme_document($management_devis_vente, 'commande_vente');
		
		// 2eme série de tests :
		// 1) est ce que sur le devis initial on a bien les bonnes valeurs pour les champs "transforme" et "transforme_reliquat" ?
		$articles_devis = $management_devis_vente->articles_avec_lignes_nomenclature();
		
		foreach($articles_devis as $ligne_devis_vente) {
			
			$this->verification_valeurs($ligne_devis_vente, array('transforme' => 2, 'transforme_reliquat' => 0));
		}
		
		// maintenant on va modifier les quantités de la commande vente, voir si ça colle toujours
		$articles_commande = $management_commande_vente->articles();
		
		$nouveaux_articles = array();
		
		// on fait -1 sur toutes les quantités
		foreach($articles_commande as $article) {
			
			$nouvel_article = array(
				
				'quantite' => $article->quantite - 1,
				'tarif' => $article->tarif,
				'article_id' => $article->article_id,
				'id' => $article->id,
				'nomenclature' => $article->nomenclature,
				'conditionnement' => $article->conditionnement,
				'type_element_source' => $article->type_element_source,
				'id_element_source' => $article->id_element_source,
				'id_ligne_source' => $article->id_ligne_source,
			);
			
			$nouveaux_articles[] = $nouvel_article;
		}
		
		$management_commande_vente->enregistre(array('articles' => $nouveaux_articles));
		
		// 3eme série de tests, on vérifie les quantités reliquat sur la commande + les reliquats sur le devis vente
		// 1ere partie, le devis vente
		
		$articles_devis = $management_devis_vente->articles_avec_lignes_nomenclature();
		
		// 1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)
		$this->verification_valeurs($articles_devis[0], array('transforme' => 1, 'transforme_reliquat' => 10), '1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		// 2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)
		$this->verification_valeurs($articles_devis[1], array('transforme' => 1, 'transforme_reliquat' => 50), '2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		
		// 1ere ligne de la nomenclature (2eme nomenclature avec conditionnement)
		$this->verification_valeurs($articles_devis[2], array('transforme' => 1, 'transforme_reliquat' => 100), '1ere ligne de la nomenclature (2eme nomenclature avec conditionnement)');
		// 2eme ligne de la nomenclature (2eme nomenclature avec conditionnement)
		$this->verification_valeurs($articles_devis[3], array('transforme' => 1, 'transforme_reliquat' => 500), '2eme ligne de la nomenclature (2eme nomenclature avec conditionnement)');
		
		// article classique sans conditionnement
		$this->verification_valeurs($articles_devis[4], array('transforme' => 1, 'transforme_reliquat' => 1), 'article classique sans conditionnement');
		// article classique avec conditionnement
		$this->verification_valeurs($articles_devis[5], array('transforme' => 1, 'transforme_reliquat' => 10), 'article classique avec conditionnement');
		// ligne 1ere nomenclature
		$this->verification_valeurs($articles_devis[6], array('transforme' => 1, 'transforme_reliquat' => 1), 'ligne 1ere nomenclature');
		// ligne 2eme nomenclature
		$this->verification_valeurs($articles_devis[7], array('transforme' => 1, 'transforme_reliquat' => 10), 'ligne 2eme nomenclature');
		
		
		// on va créer une 2eme commande, qui complète la 1ere
		$nouveaux_articles = array();
		
		// on fait -1 sur toutes les quantités
		foreach($articles_commande as $article) {
			
			$nomenclature = json_decode($article->nomenclature);
			
			if(!empty($nomenclature)) {
				
				foreach($nomenclature as $article_nomenclature) {
					
					$article_nomenclature->id = null;
				}
				
				$nomenclature = json_encode($nomenclature);
			}
			
			$nouvel_article = array(
				
				'quantite' => 1,
				'tarif' => $article->tarif,
				'article_id' => $article->article_id,
				'nomenclature' => $nomenclature,
				'conditionnement' => $article->conditionnement,
				'type_element_source' => $article->type_element_source,
				'id_element_source' => $article->id_element_source,
				'id_ligne_source' => $article->id_ligne_source,
			);
			
			$nouveaux_articles[] = $nouvel_article;
		}
		
		$management_2eme_commande_vente = $this->creation_element('commande_vente', array(
			'articles' => $nouveaux_articles,
		));
		
		// on vérifie les champs transforme et reliquat sur le devis
		$articles_devis = $management_devis_vente->articles_avec_lignes_nomenclature();
		
		foreach($articles_devis as $ligne_devis_vente) {
			
			$this->verification_valeurs($ligne_devis_vente, array('transforme' => 2, 'transforme_reliquat' => 0));
		}
		
		// maintenant on va supprimer une commande (la 2eme)
		$this->test_suppression($management_2eme_commande_vente);
		
		// on recharge les articles du devis
		$articles_devis = $management_devis_vente->articles_avec_lignes_nomenclature();
		
		// on revérifie les reliquats et champ tranforme sur le devis
		// 1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)
		$this->verification_valeurs($articles_devis[0], array('transforme' => 1, 'transforme_reliquat' => 10), '1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		// 2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)
		$this->verification_valeurs($articles_devis[1], array('transforme' => 1, 'transforme_reliquat' => 50), '2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		
		// 1ere ligne de la nomenclature (2eme nomenclature avec conditionnement)
		$this->verification_valeurs($articles_devis[2], array('transforme' => 1, 'transforme_reliquat' => 100), '1ere ligne de la nomenclature (2eme nomenclature avec conditionnement)');
		// 2eme ligne de la nomenclature (2eme nomenclature avec conditionnement)
		$this->verification_valeurs($articles_devis[3], array('transforme' => 1, 'transforme_reliquat' => 500), '2eme ligne de la nomenclature (2eme nomenclature avec conditionnement)');
		
		// article classique sans conditionnement
		$this->verification_valeurs($articles_devis[4], array('transforme' => 1, 'transforme_reliquat' => 1), 'article classique sans conditionnement');
		// article classique avec conditionnement
		$this->verification_valeurs($articles_devis[5], array('transforme' => 1, 'transforme_reliquat' => 10), 'article classique avec conditionnement');
		// ligne 1ere nomenclature
		$this->verification_valeurs($articles_devis[6], array('transforme' => 1, 'transforme_reliquat' => 1), 'ligne 1ere nomenclature');
		// ligne 2eme nomenclature
		$this->verification_valeurs($articles_devis[7], array('transforme' => 1, 'transforme_reliquat' => 10), 'ligne 2eme nomenclature');
		
		// on teste de modifier un conditionnement dans la commande restante
		// @todo
		
		// on va vérifier les stocks
		
		// article classique :
		// 1ere ligne : 9 (sans conditionnement)
		// 2eme ligne : 90 (conditionnement par 10)
		// 1ere ligne 1ere nomenclature : 40 (quantité 4 de nomenclature * 10 dans nomenclature)
		// 2eme ligne 1ere nomenclature : 200 (quantité 4 de nomenclature * 10 dans nomenclature * 5 de conditionnement)
		// 2eme nomenclature : 40 (quantité 4 de nomenclature * 10 de conditionnement)
		// 1ere ligne 2eme nomenclature : 400 (quantité 4 de nomenclature * 10 dans nomenclature * 10 de conditionnement pour la nomenclature)
		// 2eme ligne 2eme nomenclature : 2000 (quantité 4 de nomenclature * 10 dans nomenclature * 10 de conditionnement pour la nomenclature * 5 de conditionnement pour la ligne de l'article dans la nomenclature)
		$this->verification_valeurs(modele('article', $management_article_classique->modele->id), array(
			'stock_actuel' => 0, 
			'stock_reserve' => -2779,
			'stock_a_recevoir' => 0,
		));
		
		// on valide la commande
		$management_commande_vente = $this->valide_document($management_commande_vente);
		
		// on va créer un BL
		$management_bl_vente = $this->transforme_document($management_commande_vente, 'bl_vente');
		
		// on va vérifier les stocks
		
		// article classique :
		// 1ere ligne : 9 (sans conditionnement)
		// 2eme ligne : 90 (conditionnement par 10)
		// 1ere ligne 1ere nomenclature : 40 (quantité 4 de nomenclature * 10 dans nomenclature)
		// 2eme ligne 1ere nomenclature : 200 (quantité 4 de nomenclature * 10 dans nomenclature * 5 de conditionnement)
		// 2eme nomenclature : 40 (quantité 4 de nomenclature * 10 de conditionnement)
		// 1ere ligne 2eme nomenclature : 400 (quantité 4 de nomenclature * 10 dans nomenclature * 10 de conditionnement pour la nomenclature)
		// 2eme ligne 2eme nomenclature : 2000 (quantité 4 de nomenclature * 10 dans nomenclature * 10 de conditionnement pour la nomenclature * 5 de conditionnement pour la ligne de l'article dans la nomenclature)
		$this->verification_valeurs(modele('article', $management_article_classique->modele->id), array(
			'stock_actuel' => -2779, 
			'stock_reserve' => 0,
			'stock_a_recevoir' => 0,
		));
		
		// on transforme la commande vente en commande fournisseur
		$articles_commande = $management_commande_vente->articles_avec_lignes_nomenclature();
		
		$management_commande_achat = $this->creation_element('commande_achat', array(
			
			'fournisseur_id' => $management_fournisseur->modele->id,
			'articles' => array(
			
				// article classique sans conditionnement
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 5,
					'type_element_source' => 'commande_vente',
					'id_element_source' => $management_commande_vente->modele->id,
					'id_ligne_source' => $articles_commande[4]->id,
				),
				
				// article classique avec conditionnement
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 9,
					'conditionnement' => $management_article_fournisseur->modele->id,
					'type_element_source' => 'commande_vente',
					'id_element_source' => $management_commande_vente->modele->id,
					'id_ligne_source' => $articles_commande[5]->id,
				),
				
				// les articles de la 1ere nomenclature
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 4 * 10,
					'type_element_source' => 'commande_vente',
					'id_element_source' => $management_commande_vente->modele->id,
					'id_ligne_source' => $articles_commande[0]->id,
				),
				
				array(
					'article_id' => $management_article_classique->modele->id,
					'quantite' => 4 * 5,
					'conditionnement' => $management_article_fournisseur->modele->id,
					'type_element_source' => 'commande_vente',
					'id_element_source' => $management_commande_vente->modele->id,
					'id_ligne_source' => $articles_commande[1]->id,
				),
			),
		));
		
		// on vérifie les stocks
		// commande achat : 5 + 90 + 40 + 200 = 335
		$this->verification_valeurs(modele('article', $management_article_classique->modele->id), array(
			'stock_actuel' => -2779, 
			'stock_reserve' => 0,
			'stock_a_recevoir' => 335,
		));
		
		// on recharge les articles de la commande
		$articles_commande = $management_commande_vente->articles_avec_lignes_nomenclature();
		
		// on revérifie les reliquats et champ tranforme sur la commande
		$this->verification_valeurs($articles_commande[0], array('transforme_fournisseur' => 2, 'transforme_reliquat_commande_fournisseur' => 0), '1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		$this->verification_valeurs($articles_commande[1], array('transforme_fournisseur' => 2, 'transforme_reliquat_commande_fournisseur' => 0), '2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		$this->verification_valeurs($articles_commande[4], array('transforme_fournisseur' => 1, 'transforme_reliquat_commande_fournisseur' => 4), '1ere ligne article classique sans conditionnement');
		$this->verification_valeurs($articles_commande[5], array('transforme_fournisseur' => 2, 'transforme_reliquat_commande_fournisseur' => 0), '2eme ligne article classique avec conditionnement');
		
		// ensuite on vérifie les reliquat sur la commande achat
		$articles_commande_achat = $management_commande_achat->articles();

		$this->verification_valeurs($articles_commande_achat[0], array('transforme' => 0, 'transforme_reliquat' => 5, 'recue' => 0, 'quantite_recu' => 0, 'reliquat_reception' => 5), '1ere ligne commande achat)');
		$this->verification_valeurs($articles_commande_achat[1], array('transforme' => 0, 'transforme_reliquat' => 90, 'recue' => 0, 'quantite_recu' => 0, 'reliquat_reception' => 90), '2eme ligne commande achat)');
		$this->verification_valeurs($articles_commande_achat[2], array('transforme' => 0, 'transforme_reliquat' => 40, 'recue' => 0, 'quantite_recu' => 0, 'reliquat_reception' => 40), '3eme ligne commande achat)');
		$this->verification_valeurs($articles_commande_achat[3], array('transforme' => 0, 'transforme_reliquat' => 200, 'recue' => 0, 'quantite_recu' => 0, 'reliquat_reception' => 200), '4eme ligne commande achat)');
		
		// on transforme la commande achat en BL achat
		// on valide la commande
		$management_commande_achat = $this->valide_document($management_commande_achat);
		
		// on le transforme en bl achat (il faut passer par la méthode sur commande_achat_management)
		$management = management('commande_achat');
		$articles_commande_achat = $management_commande_achat->articles();
		
		foreach($articles_commande_achat as $article) {
			
			$management->enregistre_reception_ligne($article->id);
		}
		
		// on re vérifie la commande vente et la commande achat 
		$articles_commande = $management_commande_vente->articles_avec_lignes_nomenclature();
		
		
		// on revérifie les reliquats et champ tranforme sur la commande vente
		$this->verification_valeurs($articles_commande[0], array('transforme_livraison' => 2, 'transforme_reliquat_reception_fournisseur' => 0), '1ere ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		$this->verification_valeurs($articles_commande[1], array('transforme_livraison' => 2, 'transforme_reliquat_reception_fournisseur' => 0), '2eme ligne de la nomenclature (1ere nomenclature sans conditionnement)');
		// $this->verification_valeurs($articles_commande[4], array('transforme_livraison' => 1, 'transforme_reliquat_reception_fournisseur' => 4), '1ere ligne article classique sans conditionnement');
		$this->verification_valeurs($articles_commande[4], array('transforme_livraison' => 2, 'transforme_reliquat_reception_fournisseur' => 0), '1ere ligne article classique sans conditionnement');
		$this->verification_valeurs($articles_commande[5], array('transforme_livraison' => 2, 'transforme_reliquat_reception_fournisseur' => 0), '2eme ligne article classique avec conditionnement');
		
		// on re vérifie la commande achat
		$articles_commande_achat = $management_commande_achat->articles();

		$this->verification_valeurs($articles_commande_achat[0], array('transforme' => 2, 'transforme_reliquat' => 0, 'recue' => 1, 'quantite_recue' => 5, 'reliquat_reception' => 0), '1ere ligne commande achat)');
		$this->verification_valeurs($articles_commande_achat[1], array('transforme' => 2, 'transforme_reliquat' => 0, 'recue' => 1, 'quantite_recue' => 90, 'reliquat_reception' => 0), '2eme ligne commande achat)');
		$this->verification_valeurs($articles_commande_achat[2], array('transforme' => 2, 'transforme_reliquat' => 0, 'recue' => 1, 'quantite_recue' => 40, 'reliquat_reception' => 0), '3eme ligne commande achat)');
		$this->verification_valeurs($articles_commande_achat[3], array('transforme' => 2, 'transforme_reliquat' => 0, 'recue' => 1, 'quantite_recue' => 200, 'reliquat_reception' => 0), '4eme ligne commande achat)');
		
		
		return true;
	}

}