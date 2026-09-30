<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Element_piece_jointe;
use Mail;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;
use Hash;

class Ticket_client_management extends Element_management {

    protected $pieces_jointes;

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

        if(empty($this->modele->lien_ticket) || isset($modifications['contact'])) {

            $lien_ticket = fonctionnalite('url_extranet');

            if (strpos(fonctionnalite('url_extranet'), 'http') === false)
                $lien_ticket = 'https://' . fonctionnalite('url_extranet');

            $lien_ticket .= '/extranet/ticket_client/' . $this->modele->id  . '/' . $this->modele->contact . '?tt='.Hash::make('easy' . $this->modele->id . 'dev').'&tu=' . Hash::make('easy' . $this->modele->contact . 'dev');

            if (empty($this->modele->contact))
                $lien_ticket = '';

            $this->enregistre_modele(array(
                'lien_ticket' => $lien_ticket
            ));
        }

        //On crée les pièces jointes liées au ticket s'il y en a
        if(!empty($this->pieces_jointes)){

            foreach($this->pieces_jointes as $piece_jointe){

                $fichier = new Element_piece_jointe();
                $fichier->nom = $piece_jointe->nom_original;
                $fichier->titre = $piece_jointe->nom_original;
                $fichier->type_element = $this->_type_element;
                $fichier->element_id = $modele->id;
                $fichier->chemin = $piece_jointe->chemin ?? str_replace('storage/','',$piece_jointe->url_public);

                if(!empty(moi())) {
                    $fichier->type_element_createur = 'utilisateur';
                    $fichier->element_id_createur = moi()->id;
                }
                else if(!empty(moi_extranet())){
                    $fichier->type_element_createur = 'contact';
                    $fichier->element_id_createur = moi_extranet()->contact_selectionne->id;
                }

                $fichier->save();
            }
        }

		// on crée un échange automatiquement
		if(empty($modele_avant->id) && empty($this->modele->blackliste)) {

            if(fonctionnalite('alimentation_timeline_creation_ticket_client'))
                service('alimentation_timeline')->alimentation_timeline('creation_' . $this->_type_element, 'client', $this->modele->client_id, traduction('module_sur_fiche.client.timeline.nouveau_ticket') . " : #lien/ticket_client/" . $this->modele->id . "#");

            $this->envoie_mail_prise_en_charge();
        }
	}


	
	/**
	 * 
	 * Affiche les derniers échanges
	 * 
	 */
	public function colonne_affichage_derniers_echanges($modele) {
		
		$echanges = modele('ticket_client_echange')->where('suivi_recette', $modele->id)->orderBy('cree_le', 'desc')->take(2)->get();

		$chaine = '' ;

		foreach($echanges as $echange) {

			$auteur = modele('utilisateur', $echange->cree_par);
			$chaine .= '<br><span style="font-size:10px;">['.formate_date('d/m/y', $echange->cree_le).']</span> <strong>'.$auteur->prenom.' '.$auteur->nom.'</strong> - '.$echange->message;
		}

		return $chaine;
	}


	/*
	 *
	 * On enregistre le statut au minimum à "en cours"
	 * On garde les pièces jointes en mémoire et on les unset pour les traiter dans les méthodes post modification.
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        $utilisateur_extranet = moi_extranet();
        $utilisateur_erp = moi();

        // On vérifie si le ticket doit être blacklisté
        if(!empty($modifications['from_email'])){

            // On extrait le nom de domaine de l'adresse mail
            $nom_domaine = substr($modifications['from_email'], strrpos($modifications['from_email'], '@') + 1);

            $blacklist = modele('ticket_client_blacklist_emails')
                ->where(function($r) use ($modifications, $nom_domaine) {
                    $r->where('adresse_email', $modifications['from_email'])
                        ->orWhere('nom_domaine', $nom_domaine);
                })
                ->count();

            if($blacklist > 0)
                $modifications['blackliste'] = 1;
        }

        if(empty($this->modele->id) && (empty($utilisateur_erp) || $utilisateur_erp->service == 1))
            $modifications['nouvel_echange'] = 1;

        if(empty($this->modele->statut) && empty($modifications['statut']))
			$modifications['statut'] = 0;

        if(empty($modifications['contact']) && !empty($utilisateur_extranet))
            $modifications['contact'] = $utilisateur_extranet->contact_selectionne->id;

        if(!empty($modifications['pieces_jointes'])){

            $this->pieces_jointes = json_decode($modifications['pieces_jointes']);
            unset($modifications['pieces_jointes']);
        }

		return parent::enregistre($modifications, $modele);
	}

	public function liste_affectation($modele) {

		if(empty($modele->en_charge))
			return '';
		
		$utilisateur = modele('utilisateur', $modele->en_charge);
		
		
		if(!empty($utilisateur->avatar))
			$avatar = '<img src="'.asset('storage/'.$utilisateur->avatar).'" alt="" style="max-width: 50px;max-height: 50px;">';
		else
			$avatar = '<img src="eden/images/no_avatar.jpg" alt="" style="max-width: 50px;max-height: 50px;">';
		
		return $avatar;		
	}

    /**
     *
     * Envoie un email de prise en charge lors de la réception d'un ticket mail via synchro
     *
     */
    public function envoie_mail_prise_en_charge(){

		$modele_de_mail = fonctionnalite('releve_mail_ticket_client_modele_email_creation_ticket');

		if(empty($modele_de_mail))
			return;

		$modele_email = modele('modele_email', $modele_de_mail);

        $service_publipostage = service('publipostage');
        
        $sujet = $service_publipostage->publipostage_texte($modele_email->sujet_modele, 'ticket_client', [$this->modele->id]);
        $contenu_reponse = $service_publipostage->publipostage_texte($modele_email->modele, 'ticket_client', [$this->modele->id]);
        
		$mail_destinataire = false;

		$ticket_client = $this->modele;

		if(!empty($ticket_client->contact)) {

			$contact = modele('contact', $ticket_client->contact);

			if(!empty($contact->adresse_email)) {

				$mail_destinataire = $contact->adresse_email;
			}
		}

		if(empty($mail_destinataire))
			return;

        $connexion_microsoft = fonctionnalite('releve_mail_ticket_client_microsoft');

        if($connexion_microsoft){

            $adresse_email = fonctionnalite('releve_mail_ticket_client_microsoft_email');

            $vue = view('eden::mails.envoi_mail_info_ticket_client_reponse', ['management_entete' => $this, 'contenu_reponse' => $contenu_reponse])->render();

            if(!empty($this->modele->mail_id))
                service('microsoft_email')->envoie_mail_reponse($adresse_email,$this->modele->mail_id,$vue);
            else
                service('microsoft_email')->envoie_mail($adresse_email,$mail_destinataire,$vue,$sujet);
        }
        else {

            $configuration_email = modele('configuration_email')->where('type', 3)->where('valeur_par_defaut', 1)->first();

            if (empty($configuration_email))
                return;

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 3,
                'destinataire' => [$mail_destinataire],
                'repondre_a' => [
                    'email' => $configuration_email->adresse_email_par_defaut,
                    'nom' => $configuration_email->nom_expediteur_par_defaut,
                ],
                'sujet' => $sujet,
            ];

            $variables_email = [
                'management_entete' => $this,
                'contenu_reponse' => $contenu_reponse
            ];

            $retour = $service_email->envoyer('eden::mails.envoi_mail_info_ticket_client_reponse', $variables_email, $parametres_email);
        }
	}

    public function charge_donnees_pour_pdf_pour_fiche_client($client_id){

        $donnees = array();

        $annee_en_cours = date("Y");

        $donnees['nombre_tickets_total'] = modele('ticket_client')->where('client_id', $client_id)->where('cree_le', 'like', '%' . $annee_en_cours . '%')->count();
        $donnees['nombre_tickets_annee'] = modele('ticket_client')->where('client_id', $client_id)->where('cree_le', 'like', '%' . $annee_en_cours . '%')->where('statut', '!=', 50)->count();

        return $donnees;
    }
}