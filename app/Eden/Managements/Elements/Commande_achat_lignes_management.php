<?php

namespace App\Eden\Managements\Elements;

class Commande_achat_lignes_management extends Document_lignes_management {
	
	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {
		
		// on met à jour les stocks réservés
		$this->mise_a_jour_stocks(false);

		parent::methodes_post_suppression($modele);
		
		// on met à jour les commandes ventes
        $this->met_a_jour_commande_vente_avec_receptions();
	}
	
	/**
	 * 
	 * On crée les lignes à réceptionner pour les fournisseurs
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		// on met à jour les infos sur la ligne comme les reliquats de réception
		$this->mise_a_jour_statut_ligne_commande_achat();

		// on met à jour les stocks réservés
		$this->mise_a_jour_stocks($this->modele->suppression_manuelle_reliquat == 1 ? false : true);
	}
	
	/**
	 *
	 * On met à jour les lignes du document d'origine
	 *
	 */
	protected function mise_a_jour_lignes_document_origine() {
		
		parent::mise_a_jour_lignes_document_origine();
		
		if($this->modele->type_element_source != 'commande_vente')
			return;
		
		// notre article vient effectivement d'une commande vente
		// on va voir cb on été commandés
		$quantite_transformee = 0;
		
		$lignes = modele('commande_achat_lignes')->where(array(
			
			'type_element_source' => 'commande_vente',
			'id_element_source' => $this->modele->id_element_source,
			'id_ligne_source' => $this->modele->id_ligne_source,
		))
		->get();
		
		foreach($lignes as $ligne) {
			
			$nombre = $ligne->quantite;
			
			// on regarde s'il y a un conditionnement
			$conditionnement_de_la_ligne = $this->conditionnement_de_la_ligne();
			
			$quantite_transformee += $nombre * $conditionnement_de_la_ligne;
		}
		
		
		$management_ligne_origine = management('commande_vente_lignes', $this->modele->id_ligne_source);
		
		$ligne_element_source = $management_ligne_origine->modele;
		$conditionnement_origine = $management_ligne_origine->conditionnement_de_la_ligne();
		
		if($quantite_transformee == 0) {
			
			// non traité
			$transforme = 0;
			$transforme_reliquat = $ligne_element_source['quantite'] * $conditionnement_origine;
		}
		elseif($quantite_transformee < $ligne_element_source['quantite'] * $conditionnement_origine) {
			
			// partiellement traité
			$transforme = 1;
			$transforme_reliquat = ($ligne_element_source['quantite'] * $conditionnement_origine) - $quantite_transformee;
		}
		else {
			
			// traité
			$transforme = 2;
			$transforme_reliquat = 0;
		}
		
		// on enregistre le changement sur l'élément source
		if($transforme != $ligne_element_source->transforme_fournisseur || $transforme_reliquat != $ligne_element_source->transforme_reliquat_commande_fournisseur) {
			
			
			$modifications_ligne = array(
				
				'transforme_fournisseur' => $transforme,
				'transforme_reliquat_commande_fournisseur' => $transforme_reliquat,
			);
			
			$management_ligne_origine->enregistre($modifications_ligne);
		}
		
	}
		
	/**
	 * 
	 * On met à jour les status de la ligne
	 * 
	 */
	public function mise_a_jour_statut_ligne_commande_achat() {
		
		// la ligne est reçue
		if($this->modele->quantite <= $this->modele->quantite_recue || $this->modele->suppression_manuelle_reliquat == 1) {
			
			if($this->modele->recue != 1) {
				
				$this->enregistre_modele(array(
					'recue' => 1, 
					'reliquat_reception' => 0,
				));
			}
			
			// on met à jour la commande d'origine avec les statuts qui concernent la livraison
			$this->met_a_jour_commande_vente_avec_receptions();
			
			return true;
		}
		
		// on va chercher le conditionnement de la ligne
		$conditionnement = $this->conditionnement_de_la_ligne();
		
		$this->enregistre_modele(array(
			'reliquat_reception' => $this->modele->quantite * $conditionnement - $this->modele->quantite_recue, 
			'recue' => 0, 
		));
		
		// on met à jour la commande d'origine avec les statuts qui concernent la livraison
		$this->met_a_jour_commande_vente_avec_receptions();
		
		return true;
	}
	
	/**
	 * 
	 * Met à jour les lignes de la commande vente avec les réceptions
	 * 
	 */
	public function met_a_jour_commande_vente_avec_receptions() {
		
		// on met à jour la commande vente
		if($this->modele->type_element_source != 'commande_vente')
			return true;
		
		// on va voir cb on déjà été livrés 
		// (on doit faire comme ça car on prend en compte le fait qu'il peut y avoir plusieurs commandes achat pour une même commande vente)
		// il faut récupérer toutes les commandes achat liées à cette ligne d'origine, puis tous les bl_achat liés aux commandes achat
		$lignes = modele('commande_achat_lignes')->where(array(
			
			'type_element_source' => 'commande_vente',
			'id_element_source' => $this->modele->id_element_source,
			'id_ligne_source' => $this->modele->id_ligne_source,
		))
		->get();
		
		$reliquat_reception = 0;
		$deja_livre = 0;
		
		foreach($lignes as $ligne) {
			
			$reliquat_reception += $ligne->reliquat_reception;
			
			// maintenant on va chercher tous les bl
			$lignes_bl = modele('bl_achat_lignes')->where(array(
				
				'type_element_source' => 'commande_achat',
				'id_element_source' => $ligne->document_id,
				'id_ligne_source' => $ligne->id,
			))
			->get();
			
			foreach($lignes_bl as $ligne_bl) {
				
				$deja_livre += $ligne_bl->quantite * management('bl_achat_lignes', $ligne_bl->id, $ligne_bl)->conditionnement_de_la_ligne();
			}
			
		}
		
		$ligne_origine = $this->management_ligne_origine();
		
		if(!$ligne_origine)
			return true;
		
		$quantite_origine = $ligne_origine->modele->quantite * $ligne_origine->conditionnement_de_la_ligne();
		
		if($reliquat_reception >= $quantite_origine) {
			
			$transforme_livraison = 0;
			$transforme_reliquat_reception_fournisseur = $quantite_origine;
		}
		elseif($reliquat_reception <= 0) {
			
			$transforme_livraison = 2;
			$transforme_reliquat_reception_fournisseur = 0;
		}
		else {
			
			// on gère le cas des commandes achat partielles
			if($deja_livre > 0) {
				
				$transforme_livraison = 1;
				$transforme_reliquat_reception_fournisseur = $quantite_origine - $deja_livre;
			}
			else {
				
				$transforme_livraison = 0;
				$transforme_reliquat_reception_fournisseur = $quantite_origine;
			}
		}
		
		$ligne_origine->enregistre_modele(array(
			
			'transforme_livraison' => $transforme_livraison,
			'transforme_reliquat_reception_fournisseur' => $transforme_reliquat_reception_fournisseur,
		));
		
		return true;
		
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'reception_fractionnee';
        $liste_options[] = 'recu';
        $liste_options[] = 'zoom';
		$liste_options[] = 'suppression_manuelle_reliquat';

        return $liste_options;
    }
	
	/**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element, $type_element,$id_liste_parent) {
		
		$infos_commande_client = array();
		
		$management = management($type_element, $id_element);
		
		if(!empty($management->modele->type_element_source) && $management->modele->type_element_source == 'commande_vente') {
			
			$commande_ligne_origine = management('commande_vente_lignes', $management->modele->id_ligne_source);
			
			$commande_origine = $commande_ligne_origine->management_entete();
			
			$infos_commande_client = array(
			
				'client' => management('client', $commande_origine->modele->client_id)->affiche_lien(),
				'projet' => management('projet', $commande_origine->modele->projet_id)->affiche_lien(),
			);
		}

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_commande_achat_lignes";
		$vue_render = view($vue, array(

			'infos_commande_client' => $infos_commande_client,
		))->render();

		return $vue_render;
	}
	
	/**
	 *
	 *
	 * Retourne les actions sur les listes
	 * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
	 *
	 */
	public function actions_a_afficher($id_liste) {

		// on recupère les actions principales
		$actions = parent::actions_a_afficher($id_liste);

		// on supprime la suppression classique
		unset($actions['supprimer']);

		$actions['reception_commande'] = '<span class="dropdown-item" @click="modale_reception_commande = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.enregistrer_reception\')"></span></span>';
		$actions['suppression_manuelle_reliquat'] = '<span class="dropdown-item"
			@click="modale_suppression_manuelle_reliquat = true">
			<i class="fas fa-truck-loading"></i>
			<span v-html="$root.traduction(\'document.suppression_manuelle_reliquat.etat_1\')"></span></span>';
		$actions['annulation_suppression_manuelle_reliquat'] = '<span class="dropdown-item"
			@click="modale_annulation_suppression_manuelle_reliquat = true">
			<i class="fas fa-truck-loading"></i>
			<span v-html="$root.traduction(\'document.suppression_manuelle_reliquat.etat_0\')"></span></span>';
		
		return $actions;
	}

    /**
     *
     * On affiche une ligne rouge si urgent
     *
     */
    public function reference_fournisseur($modele){

        if(empty($modele['fournisseur_id_ligne']))
            return '';

        $article_fournisseur = modele('article_fournisseur')->where('article_id', $modele['article_id'])->where('fournisseur_id', $modele['fournisseur_id_ligne'])->first();

        if(empty($article_fournisseur))
            return '';

        return $article_fournisseur->reference;
    }
}