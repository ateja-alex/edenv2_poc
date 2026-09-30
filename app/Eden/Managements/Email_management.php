<?php

namespace App\Eden\Managements;

use Mail;
use App\Eden\Variables;
use Webklex\IMAP\Client;
use DB;

/**
 * Gestion des utilisateurs de l'ERP
 */
class Email_management {

	public function __construct() {

		$this->pieces_jointes = array();

		return $this;
	}

	public function piece_jointe($fichier) {

		$this->pieces_jointes[] = $fichier;

		return $this;
	}

    public static function synchro_mail($le_mail_synchro,&$les_id_mail_array, $configurations_emails){
        $emails = array();

        try {

            if ($le_mail_synchro->type == 1) {

                $emails = service('microsoft_email')->emails($le_mail_synchro->email_synchro, "email_recus",$le_mail_synchro->dossier_synchroniser);

                if (empty($emails))
                    return true;

                $emails = service('microsoft_email')->formate_mails($emails, $les_id_mail_array);
            } else {

                if (!isset($configurations_emails[$le_mail_synchro->configuration_email]))
                    return true;

                $configuration_email = $configurations_emails[$le_mail_synchro->configuration_email];

                $management_configuration_email = management('configuration_email', $configuration_email->id, $configuration_email);

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

                if (empty($emails))
                    return true;

                $emails = service('email')->formate_mails($emails, $les_id_mail_array, 'email_recus');
            }

        }

        catch (\Exception|\Throwable $e){

            log_eden('La synchronisation email '.$le_mail_synchro->id." n'a pu aboutir ! Veuillez vérifier le paramétrage");

            $emails = [];
        }

        // On parcourt le dossier
        foreach($emails as $un_mail) {

            $management = management('email_recus');

            foreach($un_mail['pieces_jointes'] as &$pieces_jointe){

                $pieces_jointe['fichier'] = str_replace('email_recus/','',$pieces_jointe['fichier']);
            }

            $infos_mail = array(
                'sujet' => $un_mail['sujet'],
                'date' => $un_mail['date'],
                'to' => implode(',', $un_mail['to']),
                'from' => implode(',', $un_mail['from']),
                'bcc' => implode(',', $un_mail['bcc']),
                'cc' => implode(',', $un_mail['cc']),
                'pieces_jointes' => json_encode($un_mail['pieces_jointes']),
                'id_mail' => $un_mail['message_id'],
                'texte' => $un_mail['contenu_texte'],
                'texte_html' => $un_mail['contenu'],
                'utilisateur_id' => $le_mail_synchro->utilisateur_id,
                'synchro_mail_id' => $le_mail_synchro->id,
                'internet_message_id' => isset($un_mail['internet_message_id']) ? $un_mail['internet_message_id'] : null,
                'pieces_jointes_charges' => 1,
            );

            // On cherche un lien avec un client, fournisseur, ou l'un de leurs contacts
            $from = $un_mail['from'][0];
            $lien_client = modele('client')->where('adresse_email', $from)->first();
            $lien_fournisseur = modele('fournisseur')->where('adresse_email', $from)->first();
            $lien_contact = modele('contact')->where('adresse_email', $from)->first();


            // Si on a trouvé un client
            if (!empty($lien_client)) {

                $infos_mail['client_id'] = $lien_client->id;

                // Si on a trouvé un fournisseur
            } elseif (!empty($lien_fournisseur)) {

                $infos_mail['fournisseur_id'] = $lien_fournisseur->id;

                // Si on a trouvé un contact...
            } elseif (!empty($lien_contact)) {

                // Et que c'est un contact client
                if (!empty($lien_contact->client_id)) {

                    $infos_mail['client_id'] = $lien_contact->client_id;

                    // Et que c'est un contact fournisseur
                } elseif (!empty($lien_contact->fournisseur_id)) {

                    $infos_mail['fournisseur_id'] = $lien_contact->fournisseur_id;

                }
            }

            $management->enregistre($infos_mail);

            if(isset($un_mail['internet_message_id']))
                $les_id_mail_array[] = $un_mail['internet_message_id'];
            else
                $les_id_mail_array[] = $un_mail['message_id'];
        }

        return true;

    }

    /**
     *
     * On vérifie que les pjs enregistrés sont bien reliés à des emails
     *
     */
    public function suppression_pj_inutiles(){

        $emails_recus_derniers_jours = modele('email_recus')
            ->where('date','>=',date('Y-m-d',strtotime('-7 day')))
            ->get();

        $pieces_jointes_a_eviter = [];

        foreach($emails_recus_derniers_jours as $un_mail){

            try{
                $pieces_jointes = json_decode($un_mail->pieces_jointes);
            }
            catch(\Exception $e){
                $pieces_jointes = [];
            }

            if(!empty($pieces_jointes))
                $pieces_jointes_a_eviter = array_merge($pieces_jointes_a_eviter,collect($pieces_jointes)->pluck('fichier')->toArray());

            if(preg_match_all("#src=\"/storage/email_recus/(.*?)\"#", $un_mail->texte_html, $matches))
                $pieces_jointes_a_eviter = array_merge($pieces_jointes_a_eviter,$matches[1]);
        }

        $dossier_email_recus = storage_path('app/public/email_recus');
        $dossier_tmp = storage_path('app/public/tmp_emails_recus');

        if(is_dir($dossier_tmp)){

            $repertoire = scandir($dossier_tmp);

            foreach($repertoire as $fichier) {

                if($fichier == '.' || $fichier == '..')
                    continue;

                unlink($dossier_tmp.'/'.$fichier);
            }
        }

        rename($dossier_email_recus,$dossier_tmp);

				mkdir($dossier_email_recus, 0777, true);

        foreach($pieces_jointes_a_eviter as $fichier){

            if(is_file($dossier_tmp."/".$fichier))
                rename($dossier_tmp."/".$fichier,$dossier_email_recus."/".$fichier);
        }

        DB::select('UPDATE email_recus SET pieces_jointes_charges = NULL WHERE `date` < "'.date('Y-m-d',strtotime('-7 day')).'"');

        return true;
    }

    /**
     * @return void
     *
     * Vérification mail interne
     *
     */
    public function verification_mail_interne($informations){

        $mail_interne = false;

        $ndd_interne = fonctionnalite('email_recus_ndd_interne');

        // on essaie de définir si c'est un mail interne pour ne pas le retélécharger
        if (!empty($ndd_interne)) {

            $mail_interne = true;

            foreach ($informations['to'] as $mail_tmp) {

                if (strpos($mail_tmp, $ndd_interne) === false)
                    $mail_interne = false;
            }

            foreach ($informations['from'] as $mail_tmp) {

                if (strpos($mail_tmp, $ndd_interne) === false)
                    $mail_interne = false;
            }

            foreach ($informations['bcc'] as $mail_tmp) {

                if (strpos($mail_tmp, $ndd_interne) === false)
                    $mail_interne = false;
            }

            foreach ($informations['cc'] as $mail_tmp) {

                if (strpos($mail_tmp, $ndd_interne) === false)
                    $mail_interne = false;
            }
        }

        return $mail_interne;
    }
}
