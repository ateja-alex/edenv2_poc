<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Support\Facades\Storage;
use Mail;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Message;
use App\Eden\Models\Element_piece_jointe;

class Ticket_client_echange_management extends Element_management {

    protected $pieces_jointes;
    private $pieces_jointes_mail = array();

	/**
	 *
	 * Retourne le lien vers l'élément parent et l'affiche
	 *
	 */
	public function affiche_lien($ancre = false, $element_id = false, $recherche = false) {

		$management = management('ticket_client', $this->modele->suivi_recette);

		return $management->affiche_lien($ancre, $this->modele->suivi_recette, $recherche);
	}

    /*
	 *
	 * On garde les pièces jointes en mémoire et on les unset pour les traiter dans les méthodes post modification.
	 *
	 */
    public function enregistre($modifications = array(), $modele = false) {

        if(!empty($modifications['pieces_jointes'])){
    
            $this->pieces_jointes = json_decode($modifications['pieces_jointes']);
            unset($modifications['pieces_jointes']);
        }

        return parent::enregistre($modifications, $modele);
    }

	/**
	 *
	 * Envoie de l'email pour une réponse au ticket
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

        $management_entete = management('ticket_client', $this->modele->suivi_recette);

        $modifications_entete = array();

        // On vérifie si le ticket doit être blacklisté
        if(!empty($modifications['auteur_extranet']) && empty($management_entete->modele->blackliste)){

            // On extrait le nom de domaine de l'adresse mail
            $nom_domaine = substr($modifications['auteur_extranet'], strrpos($modifications['auteur_extranet'], '@') + 1);

            $blacklist = modele('ticket_client_blacklist_emails')
                ->where(function($r) use ($modifications, $nom_domaine) {
                    $r->where('adresse_email', $modifications['auteur_extranet'])
                        ->orWhere('nom_domaine', $nom_domaine);
                })
                ->count();

            if($blacklist > 0)
                $modifications_entete['blackliste'] = 1;
        }

        if(empty(moi()))
            $modifications_entete['nouvel_echange'] = true;
        else
            $modifications_entete['nouvel_echange'] = false;

        //On crée les pièces jointes liées à l'échange s'il y en a
        if(!empty($this->pieces_jointes)){

            $ids_pieces_jointes = array();

            foreach($this->pieces_jointes as $piece_jointe){

                $fichier = new Element_piece_jointe();
                $fichier->nom = $piece_jointe->nom_original;
                $fichier->titre = $piece_jointe->nom_original;
                $fichier->type_element = 'ticket_client';
                $fichier->element_id = $modele->suivi_recette;

                // Selon la provenance de l'échange, on doit soit utiliser l'attribut chemin, soit url_public
                if(!empty($piece_jointe->chemin))
                    $fichier->chemin = $piece_jointe->chemin;
                else
                    $fichier->chemin = str_replace('storage/', '', $piece_jointe->url_public);

                if(!empty(moi())) {
                    $fichier->type_element_createur = 'utilisateur';
                    $fichier->element_id_createur = moi()->id;
                }
                else if(!empty(moi_extranet())){
                    $fichier->type_element_createur = 'contact';
                    $fichier->element_id_createur = moi_extranet()->contact_selectionne->id;
                }

                $fichier->save();

                $ids_pieces_jointes[] = $fichier->id;
                $this->pieces_jointes_mail[] = [
                    'chemin' => storage_path('app/public/' . $fichier->chemin),
                    'informations' => ['as' => $fichier->nom],
                ];
            }

            $modifications_entete['pieces_jointes'] = json_encode($ids_pieces_jointes);
        }

        if(!empty($modifications_entete))
            $management_entete->enregistre_modele($modifications_entete);

        if(empty($modele_avant->id)){
            if($this->modele->type_message == 0 && empty($this->modele->auteur_extranet) && empty($management_entete->modele->blackliste))
                $this->envoie_email_suite_a_reponse_ticket();
            else if($this->modele->type_message == 2 && empty($management_entete->modele->blackliste))
                $this->envoie_email_cloture_ticket();
        }
	}

    protected function methodes_post_suppression($modele){

        parent::methodes_post_suppression($modele);

        try{
            $ids_pieces_jointes = json_decode($modele->pieces_jointes);
        }
        catch(\Exception $e){
            $ids_pieces_jointes = [];
        }

        if(empty($ids_pieces_jointes))
            return;

        $pieces_jointes = Element_piece_jointe::whereIn('id',$ids_pieces_jointes)->get();

        foreach($pieces_jointes as $piece_jointe){

            Storage::delete("public/{$piece_jointe['chemin']}");

            $piece_jointe->delete();
        }
    }

    /**
	 *
	 * Structure envoie mail
	 *
	 */
	public function envoie_email($modele_de_mail) {

        $management_entete = management('ticket_client', $this->modele->suivi_recette);

		$modele_email = modele('modele_email', $modele_de_mail);
        $service_publipostage = service('publipostage');

        $sujet = $service_publipostage->publipostage_texte($modele_email->sujet_modele, 'ticket_client_echange', [$this->modele->id]);
        $contenu_reponse = $service_publipostage->publipostage_texte($modele_email->modele, 'ticket_client_echange', [$this->modele->id]);
        
		$mail_destinataire = false;

		$ticket_client = $management_entete->modele;

		if(!empty($ticket_client->contact)) {

			$contact = modele('contact', $ticket_client->contact);

			if(!empty($contact->adresse_email)) {

				$mail_destinataire = $contact->adresse_email;
			}
		}

		if(empty($mail_destinataire)) {

			if(!empty($ticket_client->client_id)) {

				$client = modele('client', $ticket_client->client_id);

				if(!empty($client->adresse_email)) {

					$mail_destinataire = $client->adresse_email;
				}
			}
		}

		if(empty($mail_destinataire))
			return;

        $connexion_microsoft = fonctionnalite('releve_mail_ticket_client_microsoft');

        if($connexion_microsoft){

            $adresse_email = fonctionnalite('releve_mail_ticket_client_microsoft_email');

            $vue = view('eden::mails.envoi_mail_info_ticket_client_reponse', ['management_entete' => $management_entete, 'contenu_reponse' => $contenu_reponse])->render();

            if(!empty($management_entete->modele->mail_id))
                service('microsoft_email')->envoie_mail_reponse($adresse_email,$management_entete->modele->mail_id,$vue,$this->pieces_jointes_mail);
            else
                service('microsoft_email')->envoie_mail($adresse_email,$mail_destinataire,$vue,$sujet,$this->pieces_jointes_mail);
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
                'pieces_jointes' => $this->pieces_jointes_mail,
            ];

            $variables_email = [
                'management_entete' => $management_entete,
                'contenu_reponse' => $contenu_reponse
            ];

            $retour = $service_email->envoyer('eden::mails.envoi_mail_info_ticket_client_reponse', $variables_email, $parametres_email);
        }

    }
	
	/**
	 * 
	 * Envoie l'email suite à une réponse à un ticket
	 * 
	 */
	public function envoie_email_suite_a_reponse_ticket() {

		if(!empty($this->modele->type_message))
			return;

        if(defined('methode_appelee') && methode_appelee == "relever_tickets_support")
            $modele_de_mail = fonctionnalite('releve_mail_ticket_client_modele_email_reponse_client_mail_ticket');
        else
            $modele_de_mail = fonctionnalite('releve_mail_ticket_client_modele_email_reponse_ticket');

		if(empty($modele_de_mail))
			return;

        $this->envoie_email($modele_de_mail);
	}

    /**
	 *
	 * Envoie l'email à la cloture d'un ticket
	 *
	 */
	public function envoie_email_cloture_ticket() {

		if($this->modele->type_message != 2)
			return;

        $management_entete = management('ticket_client', $this->modele->suivi_recette);

        $management_entete->enregistre(
            array('statut'=> !empty($this->modele->auteur_extranet) ? 60 : 50)
        );

		$modele_de_mail = fonctionnalite('releve_mail_ticket_client_modele_email_cloture_ticket');

		if(empty($modele_de_mail) || !empty($this->modele->auteur_extranet))
			return;

        $this->envoie_email($modele_de_mail);

	}
}