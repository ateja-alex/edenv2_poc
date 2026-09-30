<?php

namespace App\Eden\Managements\Elements;

use Mail;

class Suivi_recette_easydev_echange_management extends Element_management {
	
	/**
	 * 
	 * Regarde s'il est nécessaire de notifier des utilisateurs pour des modifications sur des éléments
	 * 
	 */
	protected function notifications_elements_parents($modele, $modele_avant, $modifications) {
		
		if(fonctionnalite('notifications') !== true)
			return;
		
		$utilisateurs = modele('notification_element')->where('type_element', 'suivi_recette_easydev')->where('element_id', $this->modele->suivi_recette)->get();
		
		$ticket = management('suivi_recette_easydev', $this->modele->suivi_recette);
		
		foreach($utilisateurs as $utilisateur) {
			
			if(!empty(moi()) && $utilisateur->utilisateur_id == moi()->id)
				continue;
			
			$notification = management('notification');
			
			if(empty(moi())) {
				
				$info = array(
					
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => 'Le '.date('d/m').' à '.date('H:i').', une nouvelle réponse à un ticket a été enregistrée : '.$ticket->affiche_lien(),
				);

			}
			else {
				
				$info = array(
					
					'date' => date('Y-m-d H:i:s'),
					'utilisateur_id' => $utilisateur->utilisateur_id,
					'zone' => 'navbar_notifications',
					'contenu_html' => 'Le '.date('d/m').' à '.date('H:i').' '.moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'. a répondu à un ticket : '.$ticket->affiche_lien(),
				);
			}
			
			$notification->enregistre($info);
		}
	}
	
	/**
	 * 
	 * Défini les paramètres de la notification slack
	 * 
	 */
	public function parametres_notification_slack() {
		
		$ticket = management('suivi_recette_easydev', $this->modele->suivi_recette);
		
		$parametres = array(
			
			'attachment' => array(
				'title' => $ticket->modele->titre,
				'url' => route('base_eden.fiche.index', ['suivi_recette_easydev', $this->modele->suivi_recette]),
				'fields' => array(
					
					'Priorité' => $ticket->champ('priorite')->affiche(),
					'Statut' => $ticket->champ('statut')->affiche(),
				),
			),
			'content' => 'Nouveau message à un ticket',
		);
		
		return $parametres;
	}
	
	/**
	 *
	 * On active les notifications pour l'utilisateur courant
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
        
		$notification_management = management('notification');
		
		// on active le suivi de l'élément pour l'utilisateur connecté
		$notification_management->active_suivi_element($this);
		
		// on envoie un mail aux utilisateurs pour les prévenir qu'il y a une nouvelle réponse
//		$this->envoie_email_aux_utilisateurs($modele, $modele_avant, $modifications);
		
		if(!defined('api_ticket')) {	

			$this->envoie_echange_chez_easydev($modifications);	
		}
	}

	/**
	 * 
	 * Envoie un mail aux utilisateurs pour ce ticket
	 * 
	 */
	protected function envoie_email_aux_utilisateurs($modele, $modele_avant, $modifications) {
		
		$notification_management = management('notification');
		
		// et pour l'équipe easy dév qui a la gestion des tickets activés
		$utilisateurs_easydev = modele('utilisateur')->where('gestion_ticket',1)->get();

		// On vérifie que il y a au moins un gestionnaire de ticket sinon, par défaut c'est équipe easydev
		if ($utilisateurs_easydev->isEmpty()) {
			$utilisateurs_easydev = modele('utilisateur')->where('super_admin', 1)->get();
		}
		
		foreach($utilisateurs_easydev as $utilisateur) {
			
			$notification_management->active_suivi_element($this, $utilisateur->id);
		}

		// on recupère les emails des utilisateurs qui ont participé aux tickets
		$emails = management('suivi_recette_easydev', $modele->suivi_recette)->retourne_emails_utilisateurs();
		
		if(!defined('api_ticket')) {	

			$this->envoie_echange_chez_easydev($modifications);	
		}

	}

	/**
	 *
	 * Retourne le lien vers l'élément parent et l'affiche
	 *
	 */
	public function affiche_lien($ancre = false, $element_id = false, $recherche = false) {

		$management = management('suivi_recette_easydev', $this->modele->suivi_recette);

		return $management->affiche_lien($ancre, $this->modele->suivi_recette, $recherche);
	}
	
	/**
	 * 
	 * Envoie l'échange dans l'ERP easy dév
	 * 
	 */
	protected function envoie_echange_chez_easydev($modifications) {

		// on recupère la clé du projet
		$cle = config('services.ticket_eden.cle');

		if(!empty($cle)) {
			
			$donnees = $this->modele->toArray();
			
			$donnees['cle'] = $cle;
			
			$utilisateur = modele('utilisateur', $this->modele->cree_par);
			$donnees['utilisateur_email'] = $utilisateur->email;
			$donnees['utilisateur_nom'] = $utilisateur->nom;
			$donnees['utilisateur_prenom'] = $utilisateur->prenom;

			file_get_contents_post(env('EDEN_CONSOLE_API_URL')."eden/api/creer-echange", $donnees);
		}
		else {
			
			exception('Veuillez configurer la clé du projet (config/services/ticket_eden).');
		}
	}
}