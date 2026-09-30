<?php

namespace App\Eden\Managements\Elements;

class Devis_management extends Document_management {
	
	/**
	 * 
	 * On met à jour le statut du document
	 * 
	 * Cette classe est surchargée dans les managements fils
	 * 
	 */
	public function gere_statut_automatique() {
		
		if(empty($this->modele->accepte) && $this->modele->statut != 10) {
			
			$this->enregistre_modele(array('statut' => 10));
			return;
		}
		
		if($this->modele->accepte == 1 && $this->modele->statut != 20) {
			
			$this->enregistre_modele(array('statut' => 20));
			return;
		}
		
		if($this->modele->accepte == 2 && $this->modele->statut != 30) {
			
			$this->enregistre_modele(array('statut' => 30));
			return;
		}
		
		if($this->modele->accepte == 3 && $this->modele->statut != 40) {
			
			$this->enregistre_modele(array('statut' => 40));
			return;
		}
	}

	/**
	 *
	 * Trigger post validation mise en attente d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_validation_mise_en_attente_document($modele) {

		// on change son statut
		$this->gere_statut_automatique();
	}
	
	/**
	 *
	 * Trigger post acceptation d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_acceptation_document($modele) {
		
		// on change son statut
		$this->gere_statut_automatique();
	}

	/**
	 *
	 * Trigger post refus d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_refus_document($modele) {
		
		// on change son statut
		$this->gere_statut_automatique();

	}

	/**
	 *
	 * Trigger post annulation d'un document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_annulation_devis($modele) {
		
		// on change son statut
		$this->gere_statut_automatique();
	}
	
	/**
	 * 
	 * Utilisé sur les listes pour afficher la colonne statut
	 * 
	 */
	public function affiche_statut_pour_liste($modele) {
		
		$html = array();
		
		if($modele->valide == 0) {
			
			$html[] = '<span class="badge badge-default">Brouillon</span>';
		}
		else {
			
			if($modele->accepte == 0) {
			
				$html[] = '<span class="badge badge-default">En attente</span>';
			}
			
			if($modele->accepte == 1) {
				
				$html[] = '<span class="badge badge-success">Accepté</span>';
			}
			
			if($modele->accepte == 2) {
				
				$html[] = '<span class="badge badge-danger">Refusé</span>';
			}
			
			if($modele->accepte == 3) {
				
				$html[] = '<span class="badge badge-danger">Annulé</span>';
			}
		}
		
		return implode(' ', $html);
	}
	
	/**
	 *
	 * Affiche une liste de tags pour les listes
	 *
	 */
	public function tags_pour_liste($modele) {

		$tags = array();

		if($modele->valide != 1) {

			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.pro_forma').'</span>';
		}
		else {

			if($modele->accepte == 1) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.accepte').'</span>';
			}
			elseif($modele->accepte == 2) {

				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.refuse').'</span>';
			}
			elseif($modele->accepte == 3) {

				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.annule').'</span>';
			}
			else {

				$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.en_attente').'</span>';
			}

		}

		return implode(' ', $tags);
	}
	
	/**
	 *
	 * Accepte un document (pour les devis)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 */
	public function accepte() {

        $modele_avant = clone($this->modele);

		if($this->modele->accepte == 1)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'accepte' => 1,
			'date_changement_statut' => date('Y-m-d'),
		);

        $this->enregistre($modifications);

		// on logue la validation du document
        $this->log_acceptation();

		// méthodes post modification / création
		$this->methodes_post_acceptation_document($this->modele);

		// on met à jour la marge sur le projet
		if(!empty($this->modele->projet_id)) {

			$projet_management = management('projet', $this->modele->projet_id);

			$projet_management->calcul_marge();
		}

		// on gère les notifications
		if(fonctionnalite('notifications') !== false) {

			// on récupère les utilisateurs abonnés
			if(strpos($this->_type_element, 'vente') !== false) {

				$utilisateurs = modele('notification_element')->where('type_element', 'client')->where('element_id', $this->modele->client_id)->get();
				$tiers = management('client', $this->modele->client_id)->affiche_lien();
			}
			else {

				$utilisateurs = modele('notification_element')->where('type_element', 'fournisseur')->where('element_id', $this->modele->fournisseur_id)->get();
				$tiers = management('fournisseur', $this->modele->fournisseur_id)->affiche_lien();
			}

			foreach($utilisateurs as $utilisateur) {

				$notification = management('notification');

				$info = array(

					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => strtoupper(substr(moi()->nom,0,1)).'. a <b>accepté un devis</b> pour '.$tiers.' : '.$this->affiche_lien(),
				);

				$notification->enregistre($info);
			}
		}

		if(fonctionnalite('gescom_creation_commande_automatique_validation_devis')){
			list($retour_transformation, $nouveau_management) = $this->transformer_document('commande_vente', array('date' => date('Y-m-d')));

            if($retour_transformation === true){
				$nouveau_management->valide();
				$retour = $nouveau_management;
			} else
				$retour = $retour_transformation;
				
			return $retour;
		}

		return true;
	}

	/**
	 *
	 * Refuse un document (pour les devis)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function refuse() {

        $modele_avant = clone($this->modele);

		if($this->modele->accepte == 2)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'accepte' => 2,
			'date_changement_statut' => date('Y-m-d'),
		);

        $this->enregistre_modele($modifications);

		// on logue le refus du document
        $this->log_refus();

		// méthodes post modification / création
		$this->methodes_post_refus_document($this->modele);

		// on met à jour la marge sur le projet
		if(!empty($this->modele->projet_id)) {

			$projet_management = management('projet', $this->modele->projet_id);

			$projet_management->calcul_marge();
		}

		// on gère les notifications
		if(fonctionnalite('notifications') !== false) {

			// on récupère les utilisateurs abonnés
			if(strpos($this->_type_element, 'vente') !== false) {

				$utilisateurs = modele('notification_element')->where('type_element', 'client')->where('element_id', $this->modele->client_id)->get();
				$tiers = management('client', $this->modele->client_id)->affiche_lien();
			}
			else {

				$utilisateurs = modele('notification_element')->where('type_element', 'fournisseur')->where('element_id', $this->modele->fournisseur_id)->get();
				$tiers = management('fournisseur', $this->modele->fournisseur_id)->affiche_lien();
			}

			foreach($utilisateurs as $utilisateur) {

				$notification = management('notification');

				$info = array(

					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. a <b>refusé un devis</b> pour '.$tiers.' : '.$this->affiche_lien(),
				);

				$notification->enregistre($info);
			}
		}

		return true;
	}
	
	/**
	 *
	 * Annule un document (pour les devis)
	 *
	 * @return true si tout va bien, une erreur (string) sinon
	 *
	 *
	 */
	public function annule_devis() {

        $modele_avant = clone($this->modele);

		if($this->modele->accepte == 3)
			return true;

		/**
		@todo vérifier les droits pour les profils
		*/

		/**
		@todo vérifier les conditions pour la validation (comme l'adresse)
		*/

        $modifications = array(

			'accepte' => 3,
			'date_changement_statut' => date('Y-m-d'),
		);

        $this->enregistre_modele($modifications);

		// on logue le refus du document
        $this->log_annulation_devis();

		// méthodes post modification / création
		$this->methodes_post_annulation_devis($this->modele);

		// on met à jour la marge sur le projet
		if(!empty($this->modele->projet_id)) {

			$projet_management = management('projet', $this->modele->projet_id);

			$projet_management->calcul_marge();
		}

		// on gère les notifications
		if(fonctionnalite('notifications') !== false) {

			// on récupère les utilisateurs abonnés
			if(strpos($this->_type_element, 'vente') !== false) {

				$utilisateurs = modele('notification_element')->where('type_element', 'client')->where('element_id', $this->modele->client_id)->get();
				$tiers = management('client', $this->modele->client_id)->affiche_lien();
			}
			else {

				$utilisateurs = modele('notification_element')->where('type_element', 'fournisseur')->where('element_id', $this->modele->fournisseur_id)->get();
				$tiers = management('fournisseur', $this->modele->fournisseur_id)->affiche_lien();
			}

			foreach($utilisateurs as $utilisateur) {

				$notification = management('notification');

				$info = array(

					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. a <b>annulé un devis</b> pour '.$tiers.' : '.$this->affiche_lien(),
				);

				$notification->enregistre($info);
			}
		}

		return true;
	}

}
