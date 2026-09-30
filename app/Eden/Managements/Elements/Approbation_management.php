<?php

namespace App\Eden\Managements\Elements;

use DB;
use App\Eden\Variables;

class Approbation_management extends Element_management {
	
	/**
	 *
	 * Ajout des options pour liste libre
	 *
	 */
	public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'valider';
        $liste_options[] = 'refuser';
        $liste_options[] = 'zoom';

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);
		
		return $liste_options;
	}
	
	/**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element, $type_element,$id_liste_parent) {

		// On va chercher les infos qui nous interessent
		$management = management($type_element, $id_element);
		
		$lignes = array();
		
		// note : attention, la méthode standard pieces_jointes ne retourne pas le même format de donnée sur element_management et document_management
		$pieces_jointes = array();
		
		if(in_array($management->modele->type_element, Variables::$documents_gescom)) {
			
			$lignes = DB::table($management->modele->type_element.'_lignes')->where('document_id',$management->modele->element_id)->get();
			$pieces_jointes = management($management->modele->type_element, $management->modele->element_id)->pieces_jointes();
		}
		
		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_approbation";
		$vue_render = view($vue, array(
			'management' => $management, 
			'lignes' => $lignes, 
			'pieces_jointes' => $pieces_jointes, 
		))->render();
		
		return $vue_render;
	}
	
	/**
	 * 
	 * Enregistre un accord d'approbation
	 * 
	 */
	public function enregistre_accord_approbation() {
		
		$management_cible = management($this->modele->type_element, $this->modele->element_id);

		$modifications = array(
			'approbation' => 1,
			'date_approbation' => date("Y-m-d H:i:s"),
		);
		
		if (!empty($this->modele->approbation)) 
			return "Approbation déjà traitée";

		// on vérifie les conditions d'approbation ou on tente de faire les actions nécessaires
		// uniquement dans le cas ou l'utilisateur connecté n'a pas de supérieur, car dans ce cas
		// il n'y aura pas d'approbation en cascade

		// On valide l'approbation si conditions respectés
		$verification_condition = $management_cible->verifie_conditions_approbabtion();
		
		if ($verification_condition === true)
			$this->enregistre($modifications);
		else
			return $verification_condition;
		
		if(!$management_cible->verification_approbation_cascade() || !$this->verification_parametrage_approbation_cascade()) {
			
			if($this->modele->action == 1  && $management_cible->verifie_conditions_approbabtion()) {
				
				$retour = $management_cible->valide();
				
				if($retour !== true)
					return $retour;
			}
			elseif($this->modele->action == 3) {
				
				$retour = $management_cible->verifie_conditions_approbabtion();
				
				if($retour !== true)
					return $retour;
			}
		}
		
		// on trigger la méthode post acceptation d'approbation sur l'élément concerné
		$management_cible->methodes_post_approbation_emise();
		
		// pas d'approbation en cascade
		if(!$management_cible->verification_approbation_cascade() || !$this->verification_parametrage_approbation_cascade())
			return true;
		
		// Thibaut : je met ça car sinon l'historique n'est pas forcément dans le bon ordre
		sleep(1);

		// On créer la nouvelle demande d'approbation

		// On récupère le document
		if(empty($this->modele->demandeur_initial))
			$this->modele->demandeur_initial == moi()->id;

		foreach(moi()->validation_conges_n_plus_1 as $validateur){

			$management_approbation = management('approbation');

			$information_approbation = array(
				'destinataire_id' => $validateur,
				'utilisateur_id' => moi()->id,
				'type_element' => $this->modele->type_element,
				'element_id' => $this->modele->element_id,
				'action' => $this->modele->action,
				'demandeur_initial' => $this->modele->demandeur_initial,
			);

			$management_approbation->enregistre($information_approbation);
		}

		$management_cible->methodes_post_demande_approbation();
		
		return true;
	}
	
	/**
	 * 
	 * On vérifie si l'approbation doit être faite en cascade (paramétrage de l'approbation)
	 * 
	 */
	public function verification_parametrage_approbation_cascade() {
		
		// on va chercher le workflow
		$approbation_workflow = modele('approbation_workflow')->where('action', $this->modele->action)->where('type_element', $this->modele->type_element)->first();
		
		if(empty($approbation_workflow))
			return false;
		
		if(empty($approbation_workflow->approbation_en_cascade))
			return false;
		
		return true;
	}
	
	/**
	 * 
	 * Affiche l'élément sur lequel porte l'approbation dans les listes
	 * 
	 */
	public function liste_affiche_element($modele) {

		if(!empty($modele->type_element) && !empty($modele->element_id)) {

			return management($modele->type_element, $modele->element_id)->affiche_lien();
		}

		return '';
	}

	/**
	 * 
	 * Affiche une liste de tags pour les listes
	 * 
	 */
	public function tags_pour_liste($modele) {
		
		$tags = array();
		
		if(empty($modele->approbation)) {
			
			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.en_attente').'</span>';
		}
		else if($modele->approbation == 1) {
				
			$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.validee').'</span>';
		}
		else {

            $tags[] = '<span class="badge badge-dark" style="color: #fff !important;background-color: #343a40 !important">'.traduction('interface.listes.tags_pour_liste.refusee').'</span>';
		}
		
		return implode('<br/>', $tags);
	}

	/**
	 * 
	 * On va chercher l'HT du document pour affichage
	 * 
	 */
	public function montant_ht_document($modele) {
		
		if ($modele->type_element === null || $modele->element_id === null)
			return '0 ' . maquette('devise_application_symbole');
		
		$modele_document = modele($modele->type_element,$modele->element_id);

		return montant($modele_document->montant_document_ht) . " " . maquette('devise_application_symbole');
	}

	/**
	 *
	 * Trigger post création ou modification
	 *
	 * @note pour les documents de gestion commerciale il faut appeler methodes_post_modification_document()
	 * Cette méthode est appelée après l'enregistrement des articles, du pdf et de la référence document
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		$this->met_a_jour_statut_sur_element();
		
		// notification pour le destinataire
		if(empty($modele_avant->id) && !empty($this->modele->destinataire_id)) {
			
			$notification = management('notification');
				
			// Notification particulière pour les factures et devis d'achat
			if ($this->modele->type_element == 'facture achat') {
				
				$info = array(
				
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $this->modele->destinataire_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => "Nouvelle demande d'approbation facture achat : ".management($this->modele->type_element, $this->modele->element_id)->affiche_lien(),
				);
			}
			else if ($this->modele->type_element == 'devis_achat'){

				$info = array(
				
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $this->modele->destinataire_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => "Nouvelle demande d'approbation devis achat : ".management($this->modele->type_element, $this->modele->element_id)->affiche_lien(),
				);

			}
			else {

				$info = array(
				
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $this->modele->destinataire_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => "Une nouvelle demande d'approbation de ".$this->modele->type_element." vous a été envoyée : ".management($this->modele->type_element, $this->modele->element_id)->affiche_lien(),
				);
			}
			
			$notification->enregistre($info);
		}
		
		if(!empty($modele_avant->id) && $modele_avant->approbation != $this->modele->approbation && in_array($this->modele->approbation, array(1,2))) {
			
			$notification = management('notification');
			
			if($this->modele->approbation == 1) {
				
				$info = array(
					
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $this->modele->cree_par,
					'zone' => 'navbar_notifications',
					'contenu_html' => "Votre demande d'approbation de ".$this->modele->type_element." (".management($this->modele->type_element, $this->modele->element_id)->affiche_lien().") vient d'être acceptée par ".moi()->prenom.' '.moi()->nom,
				);
			}
			else {
				
				$info = array(
					
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $this->modele->cree_par,
					'zone' => 'navbar_notifications',
					'contenu_html' => "Votre demande d'approbation de ".$this->modele->type_element." (".management($this->modele->type_element, $this->modele->element_id)->affiche_lien().") vient d'être refusée par ".moi()->prenom.' '.moi()->nom."<br>\"".$this->modele->commentaire_refus."\"",
				);
			}
			
			$notification->enregistre($info);

			// On vérifie si il y a un demandeur initial à notifier
			if ($this->modele->demandeur_initial && $this->modele->cree_par != $this->modele->cree_par) {
				
				// On notifie le demandeur initial du document
				$notification = management('notification');
			
				if($this->modele->approbation == 1) {
					
					$info = array(
						
						'date' => date('Y-m-d H:i:s'),
						'utilisateur_id' => $this->modele->demandeur_initial,
						'zone' => 'navbar_notifications',
						'contenu_html' => "Votre demande d'approbation de ".$this->modele->type_element." (".management($this->modele->type_element, $this->modele->element_id)->affiche_lien().") vient d'être acceptée par ".moi()->prenom.' '.moi()->nom,
					);
				}
				else {
					
					$info = array(
						
						'date' => date('Y-m-d H:i:s'),
						'utilisateur_id' => $this->modele->demandeur_initial,
						'zone' => 'navbar_notifications',
						'contenu_html' => "Votre demande d'approbation de ".$this->modele->type_element." (".management($this->modele->type_element, $this->modele->element_id)->affiche_lien().") vient d'être refusée par ".moi()->prenom.' '.moi()->nom."<br>\"".$this->modele->commentaire_refus."\"",
					);
				}
				
				$notification->enregistre($info);
			}
		}
	}
	
	/**
	 * 
	 * Met à jour le statut de la colonne statut_approbation sur l'élément lié à l'approbation
	 * 
	 */
	public function met_a_jour_statut_sur_element() {
		
		if($this->modele->approbation === null)
			$this->enregistre_modele(array('approbation' => 0));
		
		if(!in_array($this->modele->type_element, \App\Eden\Variables::$documents_gescom))
			return;
			
		if(empty($this->modele->element_id))
			return;
			
		$document = management($this->modele->type_element, $this->modele->element_id);
		
		if($document->modele->statut_approbation === $this->modele->approbation)
			return;
			
		$document->enregistre_modele(array('statut_approbation' => $this->modele->approbation));
	}
}