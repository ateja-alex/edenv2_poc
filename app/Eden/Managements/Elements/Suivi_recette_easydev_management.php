<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Support\Facades\Log;

use Mail;

class Suivi_recette_easydev_management extends Element_management {
	
	/**
	 * 
	 * Affiche la priorité pour les listes
	 * 
	 */
	public function liste_priorite($modele) {
		
		$evolution = '';
		
		if($modele->evolution == 1)
			$evolution = ' <span class="badge badge-default" style="background: #7271db; color: white;">Evolution</span>';
		
		if(empty($modele->priorite)) {
			
			return 'Non précisée'.$evolution;
		}
		elseif($modele->priorite == 1) {
			
			return '<span class="badge badge-default" style="background: #eeeeee">Faible</span>'.$evolution;
		}
		elseif($modele->priorite == 2) {
			
			return '<span class="badge badge-warning" style="background: #f6d776">Moyenne</span>'.$evolution;
		}
		elseif($modele->priorite == 3) {
			
			return '<span class="badge badge-warning" style="background: #f0aa41">Urgent</span>'.$evolution;
		}
		elseif($modele->priorite == 4) {
			
			return '<span class="badge badge-danger">Critique</span>'.$evolution;
		}
		
		return '';
	}
	
	/**
	 *
	 * On regarde si y'a des utilisateurs abonnés au flux ticket
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// si c'est défini, c'est qu'on enregistre via l'API
		if(!defined('api_ticket')) {
			
//			$this->envoie_mail_utilisateurs($modele, $modele_avant, $modifications);
			
			$this->envoie_ticket_chez_easydev($modifications);
		}


		$this->cree_echange_changement_statut($modele, $modele_avant, $modifications);
		
		// On attribue un ordre par défaut
		$modele = modele('suivi_recette_easydev', $modele->id);
		
		if(empty($modele->ordre_kanban)) {

			$ordre = modele('suivi_recette_easydev')->where('statut', $modele->statut)->max('ordre_kanban')+1;
			$this->enregistre_modele(['ordre_kanban' => $ordre]);
		}

		// On va mettre à jour la date de clôture si besoin
		if(isset($modele_avant->statut) && $modele->statut != $modele_avant->statut && $modele->statut == 3)
			$this->enregistre_modele(['date_de_cloture' => date('Y-m-d')]);
	}
	
	/**
	 *
	 * Crée un échange lors du changement de statuts
	 *
	 */
	protected function cree_echange_changement_statut($modele, $modele_avant, $modifications) {

		if(isset($modele_avant->statut) && $modele->statut != $modele_avant->statut) {

            // Echange créé par l'utilisateur ou alors par le cron
            if(!empty(moi()))
                $utilisateur = moi();
            else
                $utilisateur = modele('utilisateur')->where('service', 1)->first();

            if($utilisateur === null)
                return

			management('suivi_recette_easydev_echange')
				->enregistre(
					[
						'suivi_recette' => $modele->id,
						'message' =>
								'<div class="changement_statut">'.
									'<i class="fas fa-flag"></i>'.
									' de <strong>'.$this->champ('statut')->affiche($modele_avant->statut).'</strong> à <strong>'.$this->champ('statut')->affiche().'</strong>'.
									'<div class="changement_statut_auteur">'.
										'<span>'.'par '.$utilisateur->prenom .' '. $utilisateur->nom.'<br>'.
										'le '.date('d/m/Y').' à '.date('H:i').'</span>'.
									'</div>'.
								'</div>',
						'utilisateur' => $utilisateur->id,
					]);
		}
	}

	/**
	 * 
	 * Affiche les derniers échanges
	 * 
	 */
	public function colonne_affichage_derniers_echanges($modele) {
		
		$echanges = modele('suivi_recette_easydev_echange')->where('suivi_recette', $modele->id)->orderBy('cree_le', 'desc')->take(2)->get();

		$chaine = '' ;

		foreach($echanges as $echange) {

			$auteur = modele('utilisateur', $echange->cree_par);
			$chaine .= '<br><span style="font-size:10px;">['.formate_date('d/m/y', $echange->cree_le).']</span> <strong>'.$auteur->prenom.' '.$auteur->nom.'</strong> - '.$echange->message;
		}

		return $chaine;
	}

	/**
	 * 
	 * Affiche proprement un ticket dans la liste
	 * 
	 */
	public function liste_affichage_ticket($modele) {

		// On retire les images de la description + on limite a 100 caractère l'aperçu
		$modele->description = preg_replace("/<img[^>]+\>/i", "", $modele->description);

		$description_taille = strlen($modele->description);

		$description = substr($modele->description, 0, 200);

		if ($description_taille >= 100)
			$description = $description."...";
		
		$html = '<b>'.$modele->titre.'</b> ';
		
		$html .= '<span class="badge badge-default">'.$this->champ('statut')->affiche($modele->statut).'</span> ';
		
		if($modele->priorite == 4)
			$html .= '<span class="badge badge-default" style="background: #8262fe">'.$this->champ('priorite')->affiche($modele->priorite).'</span> ';
		elseif($modele->priorite == 3)
			$html .= '<span class="badge badge-default" style="background: #ff6d6d">'.$this->champ('priorite')->affiche($modele->priorite).'</span> ';
		elseif($modele->priorite == 2)
			$html .= '<span class="badge badge-default" style="background: #fcdc09">'.$this->champ('priorite')->affiche($modele->priorite).'</span> ';
		else
			$html .= '<span class="badge badge-default" style="background: #94dc7e">'.$this->champ('priorite')->affiche($modele->priorite).'</span> ';
		
		$html .= '<br/><span style="font-size: 11px;">'.$description.'</span>';
		
		return $html;
	} 
	 
	 
	/**
	 *
	 * On enregistre le statut au minimum à "en cours"
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        if(env('APP_ENV') != 'prod')
            return "Merci de créer votre demande depuis l'environnement de production";

		if(empty($this->modele->statut) && empty($modifications['statut']))
			$modifications['statut'] = 5;
		
		return parent::enregistre($modifications, $modele);
	}

	/**
	 * 
	 * 
	 * Retourne un tableau avec les emails des utilisateurs qui ont participés aux tickets
	 * 
	 */
	public function retourne_emails_utilisateurs() {

		// on recupère les membres d'easy dev
		$emails_utilisateurs_easydev = modele('utilisateur')->where('super_admin', 1)->get()->pluck('email')->toArray();

		// on recupère le créateur du ticket et le dernier utilisateur qui a modifié le ticket
		$emails_utilisateurs_participent_ticket = modele('utilisateur')->whereIn('id', [$this->modele->cree_par, $this->modele->modifie_par])->get()->pluck('email')->toArray();

		// on recupère les échanges du tickets
		$echanges_du_ticket = modele('suivi_recette_easydev_echange')->where('suivi_recette', $this->modele->id)->get();

		// on recupère les adresses emails des utilisateurs qui ont participés aux échanges
		$emails_utilisateurs_participent_echanges = modele('utilisateur')->whereIn('id', $echanges_du_ticket->pluck('cree_par')->toArray())->get()->pluck('email')->toArray();

		// on merge les différents tableau et on supprime les doublons
		$emails = array_unique(array_merge($emails_utilisateurs_easydev, $emails_utilisateurs_participent_ticket, $emails_utilisateurs_participent_echanges));

		$index = false;

		// on supprime l'adresse email de l'utilisateur qui a modifié le ticket
		if(!empty(moi()))
			$index = array_search(moi()->email, $emails);

		if($index !== false) 
			unset($emails[$index]);

		return $emails;
	}


	/**
	 * 
	 * Envoi le ticket chez EasyDev
	 * 
	 */
	protected function envoie_ticket_chez_easydev($modifications) {
		
		// on recupère la clé du projet
		$cle = config('services.ticket_eden.cle');

		if(!empty($cle)) {

			$donnees = $this->modele->toArray();
            $donnees['cle'] = $cle;

			// on va chercher l'auteur du ticket
			$auteur = modele('utilisateur', $this->modele->cree_par);
			
			if($auteur !== null) {
				
				$donnees['utilisateur_client'] = $auteur->email;
			}

			$url = env('EDEN_CONSOLE_API_URL')."eden/api/maj-ticket";
            file_get_contents_post($url, $donnees);
		}
		else {

			exception('Veuillez configurer la clé du projet (config/services/ticket_eden).');
		}
	}
}