<?php

namespace App\Eden\Managements\Services;


class Ticket_client_service {

    /*
     *
     * On relève les mails de la boîte configurée dans les fonctionnalités, puis on crée les tickets à partir de ceux-ci
     *
     */
    public function synchronisation_ticket_client(){

        $connexion_microsoft = fonctionnalite('releve_mail_ticket_client_microsoft');

        //On récupère les mails déjà existants pour éviter de les traiter à nouveau
        $adresse_email_microsoft = fonctionnalite('releve_mail_ticket_client_microsoft_email');

        $boite_mail_post_traitement = fonctionnalite('releve_mail_ticket_client_microsoft_dossier_deplacement_apres_traitement');

        $ticket_mails_ids = modele('ticket_client')->whereNotNull('mail_id')->get()->pluck('mail_id')->toArray();
        $ticket_mails_echange_ids = modele('ticket_client_echange')->whereNotNull('mail_id')->get()->pluck('mail_id')->toArray();
        $mails_ids = array_merge($ticket_mails_ids,$ticket_mails_echange_ids);

        if($connexion_microsoft)
            $emails = $this->emails_microsoft($mails_ids,$adresse_email_microsoft);
        else
            $emails = $this->emails($mails_ids);

        foreach ($emails as $email) {

            $ticket_client_id = null;
            $contact_id = null;
            $client_id = null;

            $adresse_email = $email['adresse_email'];
            $nom_de_domaine = $email['nom_de_domaine'];
            $contenu = $email['contenu'];
            $sujet = $email['sujet'];
            $message_id = $email['message_id'];
            $pieces_jointes = $email['pieces_jointes'];
            $date = $email['date'];

            if (strpos($contenu, 'ticket_client_id_releve_ticket_eden_') !== false) {

                $chaine_tmp = explode('ticket_client_id_releve_ticket_eden_', $contenu);

                $ticket_client_id = substr($chaine_tmp[1], 0, strpos($chaine_tmp[1], '<'));
            }

            $contenu = $this->supprimer_apercu_mail_reponse($contenu);

            if (!$ticket_client_id) {

                $ticket_client = management('ticket_client');

                // on essaie de trouver le contact
                $contact = modele('contact')->where('adresse_email', $adresse_email)->first();

                if ($contact !== null) {
                    $contact_id = $contact->id;
                    $client_id = $contact->client_id;
                } else {

                    $client = modele('client')->where('adresse_email', $adresse_email)->first();

                    if ($client === null && !empty($nom_de_domaine) && fonctionnalite('releve_mail_ticket_client_reconnaitre_client_via_domaine'))
                        $client = modele('client')->where('domaine_email', $nom_de_domaine)->first();

                    if ($client !== null) {

                        $client_id = $client->id;

                        //On crée directement un contact, car il n'existe pas
                        $contact_management = management('contact');

                        $contact_management->enregistre(array(
                            'client_id' => $client_id,
                            'adresse_email' => $adresse_email,
                        ));

                        $contact_id = $contact_management->modele->id;
                    }
                }

                $informations = array(

                    'from_email' => $adresse_email,
                    'titre' => $sujet,
                    'description' => $contenu,
                    'contact' => $contact_id,
                    'client_id' => $client_id,
                    'mail_id' => $message_id,
                    'date' => $date,
                );

                $pieces_jointes_sql = array();

                if (!empty($pieces_jointes)) {

                    foreach ($pieces_jointes as $piece_jointe) {

                        $pieces_jointes_sql[] = array(

                            'chemin' => $piece_jointe['fichier'],
                            'nom_original' => $piece_jointe['nom'],
                        );
                    }
                }

                $informations['pieces_jointes'] = json_encode($pieces_jointes_sql);

                $ticket_client->enregistre_sans_champs_obligatoires($informations);

            } // on ajoute un échange sur un ticket
            else {

                $ticket_client = management('ticket_client_echange');

                $informations = array(

                    'suivi_recette' => $ticket_client_id,
                    'message' => $contenu,
                    'mail_id' => $message_id,
                    'type_message' => 0,
                    'auteur_extranet' => $adresse_email,
                    'date' => $date,
                );

                $pieces_jointes_sql = array();

                if (!empty($pieces_jointes)) {

                    foreach ($pieces_jointes as $piece_jointe) {

                        $pieces_jointes_sql[] = array(

                            'chemin' => $piece_jointe['fichier'],
                            'nom_original' => $piece_jointe['nom'],
                        );
                    }
                }

                $informations['pieces_jointes'] = json_encode($pieces_jointes_sql);

                $ticket_client->enregistre($informations);

                $parent_ticket_client = management('ticket_client', $ticket_client_id);

                if ($parent_ticket_client->modele->statut == 50)
                    $parent_ticket_client->enregistre(array('statut' => 0));
            }

            if($connexion_microsoft && !empty($boite_mail_post_traitement)) {

                $retour = service('microsoft_email')->deplacement_mail_dossier($adresse_email_microsoft, $message_id, $boite_mail_post_traitement);

                if($retour['retour'] === true){

                    $ticket_client->enregistre_modele(array(
                        'mail_id' => $retour['id_nouveau_mail']
                    ));
                }
            }
        }
    }

    public function emails($mails_ids){

        $configuration_email = modele('configuration_email')->where('type',3)->first();

        if(empty($configuration_email))
            return array();

        $management_configuration_email = management('configuration_email',$configuration_email->id,$configuration_email);

        $emails = service('email')->recupere_mails(
            $configuration_email->login,
            $configuration_email->mot_de_passe_par_defaut,
            $configuration_email->adresse_host,
            false,
            array(
                'protocol' => $management_configuration_email->champ('protocole')->affiche(),
                'port' => $configuration_email->port,
                'encryption' => $configuration_email->cryptage_utilise
            )
        );

        if(empty($emails))
            return array();

        $emails = service('email')->formate_mails($emails,$mails_ids,'releve_de_mails');

        return $emails;
    }

    public function emails_microsoft($mails_ids,$adresse_email_microsoft){

        $dossier_boite_email_microsoft = fonctionnalite('releve_mail_ticket_client_microsoft_dossier');

        $emails = service('microsoft_email')->emails($adresse_email_microsoft,'releve_de_mails',$dossier_boite_email_microsoft);

        if(empty($emails))
            return array();

        $emails = service('microsoft_email')->formate_mails($emails,$mails_ids);

        return $emails;
    }

    /*
     *
     * Permet de supprimer le mail auquel on a répondu qui apparaît à la fin de l'échange s'il existe
     *
     */
    private function supprimer_apercu_mail_reponse($contenu){

        // Pour Microsoft, on fait une exception car ils modifient les classes des balises HTML, donc on se fie à la div qu'ils ajoutent, sinon on utilise la balise ajoutée dans le mail
        if (strpos($contenu, '<div id="appendonsend"></div>') !== false) {

            $contenu_tmp = explode('<div id="appendonsend"></div>', $contenu);

            $contenu = $contenu_tmp[0];
        }
        else if (strpos($contenu, '<div class="separation_mail"></div>') !== false) {

            $contenu_tmp = explode('<div class="separation_mail"></div>', $contenu);

            $contenu = $contenu_tmp[0];
        }

        return $contenu;
    }
}