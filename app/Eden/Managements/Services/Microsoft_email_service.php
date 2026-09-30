<?php

namespace App\Eden\Managements\Services;


use App\Eden\Managements\Email_management;
use App\Eden\Models\Microsoft_email;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\FileAttachment;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\MailFolder;
use Microsoft\Graph\Model\User;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;
use App\Eden\Exceptions\Eden_exception;

class Microsoft_email_service {

    public $graph = null;

    public function __construct(){

        $this->graph = service('microsoft_authentification')->instancie_graph_application();
    }

    public function emails($adresse_email,$dossier_piece_jointe,$dossier_parent_id = null){

        if(empty($this->graph))
            return array();

        try {

            $route_api='/users/' . $adresse_email;

            if(!empty($dossier_parent_id))
                $route_api .= '/mailFolders/'.$dossier_parent_id;

            $mails = $this->graph->createRequest('GET', $route_api.'/messages?$top=' . fonctionnalite('releve_mail_microsoft_nombre_a_recuperer'))
                ->setReturnType(Microsoft_email::class)
                ->execute();

        }
        catch(\Exception | \Throwable $e){

            $mails = [];
        }

        foreach($mails as $mail){
            $mail->graph = $this->graph;
            $mail->adresse_email_boite = $adresse_email;
            $mail->dossier_piece_jointe = $dossier_piece_jointe;
        }

        return $mails;
    }

    public function envoie_mail_reponse($adresse_email,$email_id,$vue, $pieces_jointes = null){

        if(empty($this->graph)){

            Log::warning("[Envoi mail ticket client API Graph] Impossible d'instancier l'objet Graph via le compte d'application. Veuillez vérifier que les valeurs dans les fonctionnalités sont toutes remplies et correctes.");
            return false;
        }
        
        $item_body = new ItemBody();
        $item_body->setContent($vue);

        $bodyType = new BodyType('html');
        $item_body->setContentType($bodyType);

        $message = new Message();
        $message->setBody($item_body);

        // On ajoute les pièces jointes
        if(!empty($pieces_jointes)){
            
            $pieces_jointes_formatees = array();

            foreach($pieces_jointes as $piece_jointe){

                if(is_array($piece_jointe))
                    $chemin = $piece_jointe['chemin'];
                else
                    $chemin = $piece_jointe;

                $informations_fichier = pathinfo($chemin);

                $piece_jointe_microsoft = new FileAttachment();
                $piece_jointe_microsoft->setOdataType('#microsoft.graph.fileAttachment');
                $piece_jointe_microsoft->setName($piece_jointe['informations']['as'] ?? $informations_fichier['basename']);
                $piece_jointe_microsoft->setContentType($piece_jointe['informations']['mime'] ?? $informations_fichier['extension']);
                $piece_jointe_microsoft->setContentBytes(base64_encode(file_get_contents($chemin)));
                $pieces_jointes_formatees[]= $piece_jointe_microsoft;
            }
            $message->setAttachments($pieces_jointes_formatees);
        }

        $this->graph->createRequest('POST', '/users/'.$adresse_email.'/messages/'.$email_id.'/reply')
            ->attachBody(array('message' => $message))
            ->execute();
    }

    public function envoie_mail($adresse_email,$destinataire_email,$vue,$sujet, $pieces_jointes = null){

        if(empty($this->graph)){

            Log::warning("[Envoi mail ticket client API Graph] Impossible d'instancier l'objet Graph via le compte d'application. Veuillez vérifier que les valeurs dans les fonctionnalités sont toutes remplies et correctes.");
            return false;
        }

        $item_body = new ItemBody();
        $item_body->setContent($vue);

        $bodyType = new BodyType('html');
        $item_body->setContentType($bodyType);

        $message = new Message();
        $message->setBody($item_body);

        $message->setSubject($sujet);

        $microsoft_adresse_mail = new EmailAddress();
        $microsoft_adresse_mail->setAddress($destinataire_email);

        $destinataire = new Recipient();
        $destinataire->setEmailAddress($microsoft_adresse_mail);

        $message->setToRecipients([$destinataire]);

        // On ajoute les pièces jointes
        if(isset($pieces_jointes)){

            $pieces_jointes_formatees = array();

            foreach($pieces_jointes as $piece_jointe){

                if(is_array($piece_jointe))
                    $chemin = $piece_jointe['chemin'];
                else
                    $chemin = $piece_jointe;

                $informations_fichier = pathinfo($chemin);

                $piece_jointe_microsoft = new FileAttachment();
                $piece_jointe_microsoft->setOdataType('#microsoft.graph.fileAttachment');
                $piece_jointe_microsoft->setName($piece_jointe['informations']['as'] ?? $informations_fichier['basename']);
                $piece_jointe_microsoft->setContentType($piece_jointe['informations']['mime'] ?? $informations_fichier['extension']);
                $piece_jointe_microsoft->setContentBytes(base64_encode(file_get_contents($chemin)));
                $pieces_jointes_formatees[]= $piece_jointe_microsoft;
            }
            $message->setAttachments($pieces_jointes_formatees);
        }

        $this->graph->createRequest('POST', '/users/' . $adresse_email . '/sendMail')
            ->attachBody(array('message' => $message))
            ->execute();
    }

    /**
     *
     * Envoie un mail via l'API Graph via un utilisateur
     * @param $vue : Nom de la vue utilisée comme modèle
     * @param $variables_mail : Array des variables utilisées dans la vue
     * @param $parametres : Array des paramètres du mail (expéditeur, destinataire, pièces jointes, etc...)
     * @return true
     * @throws \Throwable
     *
     */
    public function envoyer_mail($contenu, $parametres){

        $utilisateur = modele('utilisateur')->where('email', $parametres['expediteur']['email'])->first();
        $graph = service('microsoft_authentification')->instancie_graph($utilisateur->id, $utilisateur);

        if(empty($graph))
            throw new Eden_exception("L'objet graph n'a pu être récupéré");
        
        // On initialise le contenu du mail et son type pour l'appliquer au mail
        $item_body = new ItemBody();
        $item_body->setContent($contenu);

        $bodyType = new BodyType('html');
        $item_body->setContentType($bodyType);

        $message = new Message();
        $message->setBody($item_body);

        // On ajoute le sujet
        $message->setSubject($parametres['sujet']);

        // On ajoute les destinataires
        $destinataires = array();

        if(!empty($parametres['alias'])){
            $microsoft_adresse_mail = new EmailAddress();
            $microsoft_adresse_mail->setAddress($parametres['alias']);

            $expediteur_microsoft = new Recipient;
            $expediteur_microsoft->setEmailAddress($microsoft_adresse_mail);

            $message->setFrom($expediteur_microsoft);
        }

        foreach($parametres['destinataire'] as $destinataire){

            $microsoft_adresse_mail = new EmailAddress();
            $microsoft_adresse_mail->setAddress($destinataire);

            $destinataire_microsoft = new Recipient();
            $destinataire_microsoft->setEmailAddress($microsoft_adresse_mail);

            $destinataires[] = $destinataire_microsoft;
        }

        $message->setToRecipients($destinataires);

        if(!empty($parametres['cc'])) {

            // On ajoute les CC
            $ccs = array();

            foreach ($parametres['cc'] as $cc) {

                $microsoft_adresse_mail = new EmailAddress();
                $microsoft_adresse_mail->setAddress($cc);

                $destinataire_microsoft = new Recipient();
                $destinataire_microsoft->setEmailAddress($microsoft_adresse_mail);

                $ccs[] = $destinataire_microsoft;
            }


            $message->setCcRecipients($ccs);
        }

        if(!empty($parametres['bcc'])) {
            // On ajoute les BCC
            $bccs = array();

            foreach ($parametres['bcc'] as $bcc) {

                $microsoft_adresse_mail = new EmailAddress();
                $microsoft_adresse_mail->setAddress($bcc);

                $destinataire_microsoft = new Recipient();
                $destinataire_microsoft->setEmailAddress($microsoft_adresse_mail);

                $bccs[] = $destinataire_microsoft;
            }

            $message->setBccRecipients($bccs);
        }

        if(!empty($parametres['pieces_jointes'])) {
            // On ajoute les pièces jointes
            $pieces_jointes = array();

            foreach ($parametres['pieces_jointes'] as $piece_jointe) {

                if(isset($piece_jointe['contenu_fichier']))
                    $contenu_fichier = $piece_jointe['contenu_fichier'];
                else{

                    if (is_array($piece_jointe))
                        $chemin = $piece_jointe['chemin'];
                    else
                        $chemin = $piece_jointe;

                    $informations_fichier = pathinfo($chemin);
                    $contenu_fichier = file_get_contents($chemin);
                }

                $piece_jointe_microsoft = new FileAttachment();
                $piece_jointe_microsoft->setOdataType('#microsoft.graph.fileAttachment');
                $piece_jointe_microsoft->setName($piece_jointe['informations']['as'] ?? $informations_fichier['basename']);
                $piece_jointe_microsoft->setContentType($piece_jointe['informations']['mime'] ?? $informations_fichier['extension']);
                
                $piece_jointe_microsoft->setContentBytes(base64_encode($contenu_fichier));

                $pieces_jointes[] = $piece_jointe_microsoft;
            }
            $message->setAttachments($pieces_jointes);
        }

        //On envoie le mail
        $graph->createRequest('POST', '/users/' . $parametres['expediteur']['email'] . '/sendMail')
            ->attachBody(array('message' => $message))
            ->execute();

        return true;
    }

    /**
     * @param $emails 
     * @param $mails_ids_a_eviter
     *
     * Formate les mails pour avoir un format commun
     *
     */
    public function formate_mails($emails,$mails_ids_a_eviter = array()){

        $emails_a_enregistrer = array();

        $email_managament = new Email_management();

        foreach($emails as $email){

            $message_id = $email->getId();

            $internet_message_id = $email->getInternetMessageId();

            if (in_array($message_id, $mails_ids_a_eviter) || in_array($internet_message_id, $mails_ids_a_eviter))
                continue;

            if(empty($email->getSender()))
                continue;

            $emetteur = $email->getSender()->getEmailAddress();

            if($emetteur->getAddress() == fonctionnalite('releve_mail_ticket_client_microsoft_email'))
                continue;

            $auteur = $email->getFrom()->getEmailAddress();

            $adresse_email = $auteur->getAddress();

            $nom_de_domaine = null;

            if(filter_var($adresse_email,FILTER_VALIDATE_EMAIL)) {
                $nom_de_domaine = explode('@', $adresse_email);

                $nom_de_domaine = end($nom_de_domaine);
            }

            $sujet = $email->getSubject();

            $to = [];
            $bcc = [];
            $cc = [];

            foreach ($email->getToRecipients() as $toRecipient){
                $to[] = $toRecipient['emailAddress']['address'];
            }

            foreach ($email->getBccRecipients() as $bccRecipient){
                $bcc[] = $bccRecipient['emailAddress']['address'];
            }

            foreach ($email->getCcRecipients() as $ccRecipient){
                $cc[] = $ccRecipient['emailAddress']['address'];
            }

            $date = $email->getSentDateTime()
                ->setTimezone(new \DateTimeZone('Europe/Paris'))
                ->format('Y-m-d H:i:s');

            $email_a_enregistrer = array(
                'message_id' => $message_id,
                'sujet' => $sujet,
                'adresse_email' => $adresse_email,
                'nom_de_domaine' => $nom_de_domaine,
                'to' => $to,
                'from' => [$adresse_email],
                'bcc' => $bcc,
                'cc' => $cc,
                'date' => $date,
                'internet_message_id' => $internet_message_id,
            );

            $verification_email = $email_managament->verification_mail_interne($email_a_enregistrer);

            if($verification_email)
                continue;

            $contenu = $email->contenu_nettoye();

            $contenu_texte = strip_tags($contenu);

            if (empty($contenu))
                continue;

            $pieces_jointes = $email->getAttachments();

            $pieces_jointes_a_enregistrer = array();

            foreach($pieces_jointes as $piece_jointe){

                $pieces_jointes_a_enregistrer[] = [
                    'fichier' => $piece_jointe->localisation_eden,
                    'nom' => $piece_jointe->getName()
                ];
            }

            $email_a_enregistrer['pieces_jointes'] = $pieces_jointes_a_enregistrer;
            $email_a_enregistrer['contenu'] = $contenu;
            $email_a_enregistrer['contenu_texte'] = $contenu_texte;

            $emails_a_enregistrer[] = $email_a_enregistrer;
        }

        return $emails_a_enregistrer;
    }

    /**
     *
     * Permet de récupérer les dossiers d'un utilisateur
     *
     */
    public function dossiers($adresse_email){

        try {

            $dossiers = $this->graph->createRequest('GET', '/users/' . $adresse_email . '/mailFolders?top=1000')
                ->setReturnType(MailFolder::class)
                ->execute();


        }
        catch (\Exception | \Throwable $e){
            
            $dossiers = [];
        }

        $dossiers_formates = [];

        foreach ($dossiers as $dossier) {
            $dossiers_formates[$dossier->getId()] = $dossier->getDisplayName();
        }

        return $dossiers_formates;
    }

    /**
     *
     * Permet de déplacer un message dans un dossier
     *
     */
    public function deplacement_mail_dossier($adresse_email,$id_mail,$id_nouveau_dossier){

        $retour = true;
        $id_nouveau_mail = null;

        try {
            $retour_deplacement = $this->graph->createRequest('POST', '/users/' . $adresse_email . '/messages/'.$id_mail.'/move')
                ->attachBody(array(
                    'destinationId' => $id_nouveau_dossier,
                ))
                ->execute();

            $id_nouveau_mail = $retour_deplacement->getBody()['id'];
        }
        catch (\Exception | \Throwable $e){

            Log::error('Déplacement de dosser impossible : '.$e->getMessage());
            $retour = $e;
        }

        return array('retour' => $retour,'id_nouveau_mail' => $id_nouveau_mail);
    }

    public function alias_disponibles($utilisateur){

        $graph = service('microsoft_authentification')->instancie_graph($utilisateur->id, $utilisateur);

        try {

            $aliases = $graph->createRequest('GET', '/users/' . $utilisateur->email.'?$select=proxyAddresses')
                ->setReturnType(User::class)
                ->execute()->getproxyAddresses();

        }
        catch (\Exception | \Throwable $e){
            $aliases = [];
        }

        foreach($aliases as $index => &$alias){

            $alias = str_replace(['smtp:','SMTP:'],'',$alias);

            if($alias == $utilisateur->email)
                unset($aliases[$index]);
        }

        return array_values($aliases);
    }
}