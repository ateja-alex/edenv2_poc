<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cron_management;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Webklex\IMAP\Client;
use Webklex\IMAP\Exceptions\ConnectionFailedException;

class Mail_controller extends Controller {

	/**
	 *
	 * Synchro de la boite de réception des factures fournisseurs
	 *
	 */
	public function synchro_boite_reception_fournisseurs() {
		$les_id_mail_array = modele('email_facture_fournisseur')->avec_inactifs()->sans_profils()->get()->pluck('id_mail')->toArray();

		// on va télécharger les mails de la boite mails pour les factures fournisseurs
		$identifiant = config('services.boite_reception_mails_fournisseurs_eden.identifiant');
		$password = config('services.boite_reception_mails_fournisseurs_eden.mot_de_passe');

		$emails = service('email')->recupere_mails($identifiant, $password, 'imap.mail.eu-west-1.awsapps.com');

		// On parcourt le dossier
		foreach($emails as $un_mail) {

			if(in_array($un_mail->getMessageId(), $les_id_mail_array))
				continue;

			$management = management('email_facture_fournisseur');

			$to_array = array();
			$from_array = array();
			$bcc_array = array();
			$cc_array = array();
			$pieces_jointes = array();

			foreach($un_mail->to as $le_mail)
				$to_array[] = $le_mail->mail;

			foreach($un_mail->from as $le_mail)
				$from_array[] = $le_mail->mail;

			foreach($un_mail->bcc as $le_mail)
				$bcc_array[] = $le_mail->mail;

			foreach($un_mail->cc as $le_mail)
				$cc_array[] = $le_mail->mail;

			// On récupère les PJ
			$document_pdf = false;

			foreach($un_mail->attachments as $piece_jointe) {

				$pathinfo = pathinfo($piece_jointe->name);
				$nom_storage = uniqid().(isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '');

				$retour = \Storage::put('public/email_recus/'.$nom_storage, $piece_jointe->content);

				$piece_jointe_tableau = ['fichier' => $nom_storage, 'nom' => $piece_jointe->name];

				if(strpos($nom_storage, '.pdf') !== false || strpos($nom_storage, '.PDF')) {


					$nom_image = str_replace(array('.pdf', '.PDF'), '.jpg', $nom_storage);

					$im = new \imagick('storage/email_recus/'.$nom_storage.'[0]');
					$im->setImageFormat('jpg');
					$im->writeImage("storage/email_recus/".$nom_image);

					$piece_jointe_tableau['image'] = $nom_image;
				}

				$pieces_jointes[] = $piece_jointe_tableau;

				if(strpos($nom_storage, '.pdf') !== false)
					$document_pdf = true;

				if($retour !== true)
					dd_eden('Erreur lors du stockage de la pièce jointe : '.$retour);
			}

			$infos_mail = array(
				'sujet' => $un_mail->subject,
				'date' => ( is_object($un_mail->date) ? $un_mail->date->toDateTimeString() : '' ),
				'to' => implode(',' , $to_array),
				'from' => implode(',' , $from_array),
				'bcc' => implode(',' , $bcc_array),
				'cc' => implode(',' , $cc_array),
				'pieces_jointes' => json_encode($pieces_jointes),
				'id_mail' => $un_mail->getMessageId(),
				'texte' => $un_mail->getTextBody(),
			);

			$management->enregistre($infos_mail);

			// si pas de PDF dans le mail
			if(fonctionnalite('boite_reception_mails_fournisseurs_eden_pas_traiter_mails_sans_pdf') === true && $document_pdf === false) {

				$management->supprime();
			}

		}
	}

	/**
	 *
	 * Affiche le html d'un email
	 *
	 */
	public function affiche_email($id) {

		$modele = modele('email_facture_fournisseur', $id);

		return nl2br($modele->texte);
	}
}
