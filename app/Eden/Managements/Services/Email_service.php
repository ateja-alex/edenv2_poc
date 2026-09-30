<?php

namespace App\Eden\Managements\Services;

use App\Eden\Champs\Champ;
use App\Eden\Managements\Email_management;
use App\Eden\Exceptions\Eden_exception;
use Illuminate\Support\Facades\Log;
use Mail;
use App\Eden\Variables;
use Webklex\IMAP\Facades\Client;
use Webklex\IMAP\Exceptions\ConnectionFailedException;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use DB;

/**
 *
 * Gestion de l'envoi des mails dans EDEN
 *
 * Attention, avant cette classe était un management classique (App\Eden\Managements\Email_management)
 * Il se peut donc que l'ancienne classe soit encore appelée, donc si des modifications sont faites
 * Ici et qu'elle n'ont pas d'impact, c'est probablement que c'est l'ancienne classe qui est appelée
 *
 */
class Email_service {

	public function __construct() {

		$this->pieces_jointes = array();

		return $this;
	}

	public function piece_jointe($fichier) {

		$this->pieces_jointes[] = $fichier;

		return $this;
	}

    /**
     *
     * Permet d'envoyer un email
     *
     */
    public function envoyer_modele($modele_email_id, $destinataires, $type_element, $element_id) {

		$modele = modele('modele_email', $modele_email_id);

        $service_publipostage = service('publipostage');

        $modele->sujet_modele = $service_publipostage->publipostage_texte($modele->sujet_modele, $type_element, [$element_id]);
        $modele->modele = $service_publipostage->publipostage_texte($modele->modele, $type_element, [$element_id]);

        if(!is_array($destinataires))
            $destinataires = array('to' => array($destinataires));

        if(!empty(config('eden.copie_de_tous_les_mails'))) {

            if(is_array(config('eden.copie_de_tous_les_mails')))
                foreach(config('eden.copie_de_tous_les_mails') as $email)
                    $destinataires['bcc'][] = $email;
            else
                $destinataires['bcc'][] = config('eden.copie_de_tous_les_mails');
        }

		// on envoie l'email
		try {

            $parametres_email = [
                'type_configuration' => 2,
                'destinataire' => $destinataires['to'],
                'cc' => !empty($destinataires['cc']) ? $destinataires['cc'] : null,
                'bcc' => !empty($destinataires['bcc']) ? $destinataires['bcc'] : null,
                'pieces_jointes' => !empty($this->pieces_jointes) ? $this->pieces_jointes : null,
                'sujet' => $modele->sujet_modele,
            ];

            $variables_email = [
                'contenu_email' => $modele->modele
            ];

            $retour = $this->envoyer('eden::mails.template_standard', $variables_email, $parametres_email);
		}
		catch(\Exception $e){

			dd($e);
		}

		return $retour;
    }

	/**
	 *
	 * Récupère les mails d'une boite mail
	 *
	 */
	public function recupere_mails($identifiant, $password, $host, $dossier = false, $options = array()) {


		if(empty($options['port']))
			$options['port'] = 993;

		if(empty($options['protocol']))
			$options['protocol'] = 'imap';

        if(empty($options['encryption']))
			$options['encryption'] = 'ssl';

		$options['validate_cert'] = true;
		$options['host'] = $host;
		$options['username'] = $identifiant;
		$options['password'] = $password;
		$options['options']['debug'] = true;

		// Connexion au compte mail
		$client = Client::setConfig([
            'accounts' => [
                'default' => $options,
            ]
        ]);

		// On vérifie que la connexion fonctionne
		try {

			$client->connect();

		} catch (ConnectionFailedException $e) {

			exception('Une erreur est survenue en essayant de se connecter au compte "'.$identifiant.'" => '.$e->getMessage());
		}

		if($dossier === false)
			$le_dossier = $client->getFolder('INBOX');
		else
			$le_dossier = $client->getFolder($dossier);

		$array = array();

		$tous_les_mails = $le_dossier->messages()->leaveUnread()->since(date('Y-m-d', strtotime('-'.fonctionnalite('releve_mail_jour_delai').' days')))->get();

		return $tous_les_mails;
	}

	/**
	 *
	 * Prépare le tableau des données de l'échange que l'on va enregistrer suire à l'envoi d'un mail
	 *
	 */
	public function prepare_tableau_donnees_echange($type_element, $id_destinataire) {

        $retour = [
			'date' => date('Y-m-d H:i:s'),
			'type' => 4,
			'utilisateur_id' => moi()->id,
		];

        if(in_array($type_element, Variables::$documents_gescom)){
            $document = management($type_element, $id_destinataire)->modele;

            $retour['entite_id'] = $document->entite_id;

            if(in_array($type_element, Variables::$documents_vente_gescom)){
                $retour += [
                    'client_id' => $document->client_id,
                    'type_element' => 'client',
                    'element_id' => $document->client_id,
                ];
            }

            else{
                $retour += [
                    'fournisseur_id' => $document->fournisseur_id,
                    'type_element' => 'fournisseur',
                    'element_id' => $document->fournisseur_id,
                ];
            }
        }
        
        else if(in_array($type_element, ['fournisseur', 'client', 'contact', 'lead', 'coupon_reduction', 'tache'])){
            $modele = modele($type_element, $id_destinataire);

            if($type_element == 'tache'){
                if(!empty($modele->client_id))
                    $modele = modele('client', $modele->client_id);
                elseif($modele->type_element == 'client' && !empty($modele->element_id))
                    $modele = modele('client', $modele->element_id);
            }

            $retour['entite_id'] = $modele->entite_id;

            if ($type_element === 'fournisseur') 
                $retour['fournisseur_id'] = $modele->id;
            elseif ($client_id = ($type_element === 'client') ? $modele->id : ($modele->client_id ?? null))
                $retour['client_id'] = $client_id;
            
            $retour['type_element'] = $type_element === 'coupon_reduction' ? 'client' : $type_element;
            $retour['element_id'] = $type_element === 'coupon_reduction' ? $modele->client_id : $modele->id;

        } else {

            $champ_type_element_echange = Champ_libre::where('type_element', 'echange')->where('nom_sql', 'type_element')->value('contenu');
            $types_element_disponibles = collect(json_decode($champ_type_element_echange, true))->pluck('valeur', 'type_element')->toArray();

            if(!empty($types_element_disponibles[$type_element])){
                $modele = modele($type_element, $id_destinataire);
                $retour += [
                    'entite_id' => $modele->entite_id,
                    'element_id' => $modele->id,
                    'type_element' => $type_element,
                ];
            }
        }

		return $retour;
	}

	/**
	 *
	 * Enregistre un échange lié à un envoi de mail
	 *
	 */
	public function enregistre_echange($type_element, $echange, $mail, $compte_email_id, $alias_mail) {

		$contenu_du_mail = strip_tags(str_replace(array('<br/>', '<br />'), "\n", $mail['contenu']));
        $contenu_du_mail = '<b>' . strip_tags($mail['sujet']) . '</b>' . "\n\n" . $contenu_du_mail;

		$echange['description'] = $contenu_du_mail;

        if(!empty($alias_mail))
            $echange['emetteur_email'] = $alias_mail;
        else if ($compte_email_id !== null) {

			$compte_email = modele('compte_email', $compte_email_id);

			if($compte_email->alias == null || $compte_email->alias == "")
				$echange['emetteur_email'] = $compte_email->adresse_email;
			else
				$echange['emetteur_email'] = $compte_email->alias;
		} else {

			$echange['emetteur_email'] = config('eden.email_expediteur');
		}

        $echange['destinataires_email'] = implode(',',$mail['destinataires'][1]);

        if($type_element === 'client'){

            $contacts_client_destinataire = modele('contact')->where('client_id', $echange['client_id'])->get()->keyBy('adresse_email')->toArray();

            // On vérifie si le destinataire n'est pas un contact du client, le cas échéant on remplis le contact id
            foreach ($mail['destinataires'][1] as $destinataire_mail){

                // On ne garde que le premier contact
                if(array_key_exists($destinataire_mail, $contacts_client_destinataire)){

                    $echange['contact_id'] = $contacts_client_destinataire[$destinataire_mail]['id'];
                    break;
                }
            }
        }

		$echange_management = management('echange');
		$echange_management->enregistre($echange);
	}

	/**
	 *
	 * Nettoie le contenue d'un body html pour un mail reçu
	 *
	 */
	public function nettoie_body_html_email($body, $email) {

        $extensions_bloques = explode(';',fonctionnalite('releve_mail_ticket_client_extension_piece_jointe_bloque'));
        $taille_bloque = intval(fonctionnalite('releve_mail_ticket_client_taille_maximale_piece_jointe_en_mo')) * pow(10,6) ;

		foreach($email->getAttachments() as $item) {

			$attributes = $item->getAttributes();

            if($item->getSize() > $taille_bloque || in_array($item->getExtension(),$extensions_bloques))
                continue;

			if($attributes['disposition'] == 'INLINE') {

				$contenu = $attributes['content'];

				$time = date('YmdHis');

				\Storage::put('public/releve_de_mails/'.$time.$attributes['name'], $contenu);

				$body = str_replace('src="cid:'.$attributes['id'].'"', 'src="/storage/releve_de_mails/'.$time.$attributes['name'].'"', $body);
			}

		}

		return $body;
    }

	/**
	 *
	 * Récupère les PJ d'un mail reçu
	 *
	 */
	public function recupere_pj_du_mail($email,$dossier_piece_jointe){

		$pieces_jointes = array();

		$extensions_bloques = explode(';',fonctionnalite('releve_mail_ticket_client_extension_piece_jointe_bloque'));
        $taille_bloque = intval(fonctionnalite('releve_mail_ticket_client_taille_maximale_piece_jointe_en_mo')) * pow(10,6) ;

		foreach($email->getAttachments() as $item) {

            if($item->getSize() > $taille_bloque || in_array($item->getExtension(),$extensions_bloques))
                continue;

			$attributes = $item->getAttributes();

			if($attributes['disposition'] != 'INLINE') {

				$contenu = $attributes['content'];

                $route_fichier = $dossier_piece_jointe.'/'.uniqid() .'.'.$item->getExtension();

				\Storage::put('public/'.$route_fichier, $contenu);

				$pieces_jointes[] = array(
                    'fichier' => $route_fichier,
                    'nom' => $attributes['name']
                );
			}

		}

		return $pieces_jointes;
    }

    /*
     *
     * Récupère le compte email associé à l'adresse passée en paramètre, puis la configuration email associée s'il y en a.
     * Si on trouve une config, on l'applique en surchargeant les valeurs indiquées dans le fichier de configuration mail.php.
     *
     */
    public function recupere_compte_et_configuration_email($expediteur = ''){

        $compte_email = modele('compte_email')
            ->join('configuration_email', 'configuration_email.id', 'compte_email.configuration_email')
            ->where('adresse_email', $expediteur)
            ->first();

        if(!empty($compte_email))
            $this->applique_configuration($compte_email);

        return $compte_email;
    }

    /*
     *
     * Surcharge les valeurs de la configuration email définie dans le .env
     *
     */
    private function applique_configuration($configuration_email){

        $fonctionnalite_identifiants_compte = fonctionnalite('email_utiliser_identifiants_comptes_emails');

        if(!empty($configuration_email['adresse_host']))
            config(['mail.host' => $configuration_email['adresse_host']]);

        if(!empty($configuration_email['port']))
            config(['mail.port' => $configuration_email['port']]);

        if(!empty($configuration_email['protocole'])){

            $protocole_email = management('configuration_email')->champ('protocole')->valeurs_possibles;

            if(isset($protocole_email[$configuration_email['protocole']]))
                config(['mail.driver' => $protocole_email[$configuration_email['protocole']]]);
        }

        if ($fonctionnalite_identifiants_compte === true && !empty($configuration_email['adresse_email']))
            config(['mail.username' => $configuration_email['adresse_email']]);
        else if(!empty($configuration_email['login']))
            config(['mail.username' => $configuration_email['login']]);

        if ($fonctionnalite_identifiants_compte === true && !empty($configuration_email['mot_de_passe_smtp']))
            config(['mail.password' => $configuration_email['mot_de_passe_smtp']]);
        else if(!empty($configuration_email['mot_de_passe_par_defaut']))
            config(['mail.password' => $configuration_email['mot_de_passe_par_defaut']]);

        if(!empty($configuration_email['cryptage_utilise']))
            config(['mail.encryption' => $configuration_email['cryptage_utilise']]);
    }

    /**
     * @param $emails
     * @param $mails_ids_a_eviter
     *
     * Formate les mails pour avoir un format commun
     *
     */
    public function formate_mails($emails,$mails_ids_a_eviter = array(),$dossier_piece_jointe){

        $emails_a_enregistrer = array();

        $email_managament = new Email_management();

        foreach ($emails as $email) {

            $message_id = $email->message_id;

            if (in_array($message_id, $mails_ids_a_eviter))
                continue;

            if (!isset($email->bodies))
                continue;

            if (!isset($email->bodies['text']) && !isset($email->bodies['html']))
                continue;

            $contenu_texte = $email->getTextBody();

            $to = array();
            $from = array();
            $bcc = array();
            $cc = array();

            foreach ($email->to->all() as $le_mail)
                $to[] = $le_mail->mail;

            foreach ($email->from->all() as $le_mail)
                $from[] = $le_mail->mail;

            foreach ($email->bcc->all() as $le_mail)
                $bcc[] = $le_mail->mail;

            foreach ($email->cc->all() as $le_mail)
                $cc[] = $le_mail->mail;

            $sujet = $email->subject;

            $adresse_email = $email->from->first()->mail;
            $nom_de_domaine = $email->from->first()->host;

            $date = is_object($email->date) ? $email->date->first()->toDateTimeString() : '';

            $email_a_enregistrer = array(
                'message_id' => $message_id,
                'sujet' => $sujet,
                'contenu_texte' => $contenu_texte,
                'adresse_email' => $adresse_email,
                'nom_de_domaine' => $nom_de_domaine,
                'to' => $to,
                'from' => $from,
                'bcc' => $bcc,
                'cc' => $cc,
                'date' => $date,
            );

            $verification_email = $email_managament->verification_mail_interne($email_a_enregistrer);

            if($verification_email)
                continue;

            if (isset($email->bodies['html'])) {

                $contenu = $email->bodies['html'];

                $contenu = $this->nettoie_body_html_email($contenu, $email);
            }
            else
                $contenu = $email->bodies['text']->content;

            $pieces_jointes = $this->recupere_pj_du_mail($email,$dossier_piece_jointe);

            $email_a_enregistrer['pieces_jointes'] = $pieces_jointes;
            $email_a_enregistrer['contenu'] = $contenu;

            $emails_a_enregistrer[] = $email_a_enregistrer;

        }

        return $emails_a_enregistrer;
    }

    /**
     *
     * Permet d'envoyer un mail via la classe Mail de Laravel ou via l'API Graph dans le cas de l'utilisation d'Office
     * @param $vue : Nom de la vue utilisée comme template de mail
     * @param $variables_mail : variables utilisées dans la vue
     * @param $parametres : tableau contenant les paramètres du mail : expéditeur, destinataires, etc...
     * @return void
     *
     */
    public function envoyer($vue, $variables_mail, $parametres){

        $contenu = view($vue, $variables_mail)->render();

        return $this->envoyer_avec_contenu($contenu, $variables_mail, $parametres);
    }

    public function envoyer_avec_contenu($contenu, $variables_mail, $parametres){

        if($parametres['type_configuration'] == 1 && env('BASE_MAIL') !== true)
            return $this->envoie_mail_base_mail($contenu, $variables_mail, $parametres);


        $configuration = $this->recuperer_configuration_pour_envoi($parametres);

        if(empty($configuration)){

            Log::critical("[Envoi mail] Le mail n'a pas pu être envoyé car aucune configuration email n'a été trouvée pour le type demandé.
                Variables pour la vue : " . json_encode($variables_mail) . ". Paramètres d'envoi : " . json_encode($parametres));
            return true;
        }

        if(empty($parametres['expediteur']) && !empty($configuration['expediteur']))
            $parametres['expediteur'] = $configuration['expediteur'];
        else if(empty($parametres['expediteur']) && !empty($configuration['adresse_email_par_defaut']))
            $parametres['expediteur'] = ['email' => $configuration['adresse_email_par_defaut'], 'nom' => $configuration['nom_expediteur_par_defaut']];

        if(isset($configuration['type_de_compte']) && $configuration['type_de_compte'] == 1){

            try {
                service('microsoft_email')->envoyer_mail($contenu, $parametres);
            } catch(\Exception $e){

                Log::warning("[Envoi mail microsoft] Le mail n'a pas pu être envoyé.
                Variables pour la vue : " . json_encode($variables_mail) . ".
                Paramètres d'envoi : " . json_encode($parametres) . " . Erreur : " . $e->getMessage() . ". Stacktrace : " . $e->getTraceAsString());

                return traduction('messages.php.email.erreur_envoi_mail_microsoft');
            }

            return true;
        }

        try {

            Mail::html($contenu, function($m) use ($parametres) {

                if(!empty($parametres['expediteur']['nom']))
                    $m->from($parametres['expediteur']['email'], $parametres['expediteur']['nom']);
                else
                    $m->from($parametres['expediteur']['email']);

                foreach($parametres['destinataire'] as $destinataire)
                    $m->to($destinataire);

                if(!empty($parametres['cc'])){

                    foreach($parametres['cc'] as $destinataire_copie)
                        $m->cc($destinataire_copie);
                }

                if(!empty($parametres['bcc'])){

                    foreach($parametres['bcc'] as $destinataire_copie_cachee)
                        $m->bcc($destinataire_copie_cachee);
                }

                if(!empty($parametres['pieces_jointes'])){

                    foreach($parametres['pieces_jointes'] as $piece_jointe){

                        if(isset($piece_jointe['contenu_fichier']))
                            $m->attachData($piece_jointe['contenu_fichier'], $piece_jointe['informations']);
                        else if(is_array($piece_jointe) && isset($piece_jointe['informations']))
                            $m->attach($piece_jointe['chemin'], $piece_jointe['informations']);
                        else
                            $m->attach($piece_jointe);
                    }
                }

                if(!empty($parametres['repondre_a']))
                    $m->replyTo($parametres['repondre_a']['email'], $parametres['repondre_a']['nom']);

                if(!empty($parametres['contenu']))
                    $m->setBody($parametres['contenu']['contenu'], $parametres['contenu']['type']);

                $m->subject($parametres['sujet']);
            });
        } catch(\Exception $e){

            Log::warning("[Envoi mail classique] Le mail n'a pas pu être envoyé.
                Variables pour la vue : " . json_encode($variables_mail) . ".
                Paramètres d'envoi : " . json_encode($parametres) . " . Erreur : " . $e->getMessage() . ". Stacktrace : " . $e->getTraceAsString());

            return traduction('messages.php.email.erreur_envoi_mail_client');
        }

        return true;
    }

    /**
     * @return mixed
     *
     * Retourne les comptes emails de l'utilisateur connecté
     *
     */
    public function comptes_emails()
    {
        $comptes = modele('compte_email')
            ->where('valide', 1)
            ->where('utilisateur_id', moi()->id)->get();

        foreach ($comptes as $index => $compte) {
            $comptes[$index]->chaine_affichage = management('compte_email', $compte->id, $compte)->affichage_pour_select();
        }

        foreach(champs_libres('compte_email')->where('type',10) as $champ_libre){

            $element_multiple = \DB::table($champ_libre->table_pivot)
                ->whereIn('cle_locale',$comptes->pluck('id')->toArray())
                ->get()->groupBy('cle_locale')->map(fn($valeurs) => $valeurs->pluck('valeur'))->toArray();

            foreach($comptes as $compte){

                $compte->{$champ_libre->nom_sql} = $element_multiple[$compte->id] ?? [];
            }
        }

        if(($comptes->isEmpty() || $comptes->doesntContain(fn($compte) => $compte->type_de_compte === 1))){

            $utilisateur_connecte = moi();

            if(!empty($utilisateur_connecte->id_microsoft))
                $comptes->push($this->compte_email_utilisateur_microsoft($utilisateur_connecte));
        }

        return $comptes;
    }

    public function modeles_emails($type_element,$elements){

        $entites = modele('entite')->get()->pluck('id')->toArray();

        $table_libre = Table_libre::where('type_element', $type_element)->first();

        $entites[] = 0;

        // on va chercher les modèles d'emails
        $modeles = modele('modele_email')->orderBY('sujet_modele')
            ->where(function ($r) use ($entites) {
                $r->whereNull('entite_id')->orWhereIn('entite_id', $entites);
            })
            ->where('type_element_id', $table_libre->id)
            ->get();

        $modeles = $modeles->filter(function($modele) use ($type_element, $elements) {

            $modele_utilisable = true;

            foreach($elements as $element){

                if(!empty($modele->condition_affichage) && !eval_condition_js($modele->condition_affichage,$type_element,$element)) {
                    $modele_utilisable = false;
                    break;
                }
            }

            return $modele_utilisable;
        });

        foreach(champs_libres('modele_email')->where('type',10) as $champ_libre){

            $element_multiple = \DB::table($champ_libre->table_pivot)
                ->whereIn('cle_locale',$modeles->pluck('id')->toArray())
                ->get()->groupBy('cle_locale')->map(fn($valeurs) => $valeurs->pluck('valeur'))->toArray();

            foreach($modeles as $modele){

                $modele->{$champ_libre->nom_sql} = $element_multiple[$modele->id] ?? [];
            }
        }

        $management_champ_categorie = management('modele_email')->champ('categorie');
        $management_champ_langue = management('modele_email')->champ('langue');

        foreach($modeles as $modele){
            $modele->affichage_categorie = $management_champ_categorie->affiche($modele->categorie);
            $modele->affichage_langue = $management_champ_langue->affiche($modele->langue);
        }

        return $modeles->values();
    }

    public function recuperer_configuration_pour_envoi($parametres){

        $type_configuration = $parametres['type_configuration'];

        if($parametres['type_configuration'] == 1)
            $configuration_email = modele('compte_email')
                ->where('adresse_email', 'hello@eden-erp.fr')
                ->first();
        else if($parametres['id_compte_email'] == -1)
            $configuration_email = $this->compte_email_utilisateur_microsoft(moi());
        else{

            $configuration_email = modele('compte_email')
                ->leftJoin('configuration_email', 'configuration_email.id', 'compte_email.configuration_email');

            if (isset($parametres['utilisateur']))
                $configuration_email = $configuration_email->where('utilisateur_id', $parametres['utilisateur']->id)->where('adresse_email', $parametres['utilisateur']->email);
            else if (isset($parametres['id_compte_email']))
                $configuration_email = $configuration_email->where('compte_email.id', $parametres['id_compte_email']);
            else if (isset($parametres['adresse_mail_compte_email']))
                $configuration_email = $configuration_email->where('adresse_email', $parametres['adresse_mail_compte_email']);
            else if (!empty(moi()))
                $configuration_email = $configuration_email->where('utilisateur_id', moi()->id)->where('adresse_email', moi()->email);

            $configuration_email = $configuration_email->where(function ($where) {
                    $where->whereNotNull('configuration_email.id')->orWhere('type_de_compte', 1);
                })
                ->first();
        }

        if(!empty($configuration_email))
            $configuration_email->expediteur = [
                'email' => $configuration_email->alias ?? $configuration_email->adresse_email,
                'nom' => $configuration_email->nom_expediteur,
            ];
        else
            $configuration_email = modele('configuration_email')->where('type', $type_configuration)->where('valeur_par_defaut', 1)->first();

        //Si toujours vide retourner une erreur
        if(empty($configuration_email))
            return false;

        $this->applique_configuration($configuration_email);

        return is_array($configuration_email) ? $configuration_email : $configuration_email->toArray();
    }

    /**
     *
     * Permet d'envoyer des mails std sans avoir de SMTP projet paramétrer, notamment utilie pour la gestion des mots de passes oubliés
     *
     */
    public function envoie_mail_base_mail($contenu, $variables_mail, $parametres){

        $url_index = env('EDEN_MODEL_API_URL').'api/envoyer_mail';

        if(!empty($parametres['pieces_jointes'])){

            foreach($parametres['pieces_jointes'] as &$piece_jointe){

                if(is_array($piece_jointe) && isset($piece_jointe['informations']))
                    $chemin = $piece_jointe['chemin'];
                else
                    $chemin = $piece_jointe;

                $fichier_contenu = base64_encode(file_get_contents($chemin));
                $informations_fichier = pathinfo($chemin);

                $piece_jointe = [
                    'contenu_fichier' => $fichier_contenu,
                    'informations' => [
                        'as' => $piece_jointe['informations']['as'] ?? $informations_fichier['basename'],
                        'mime' => $piece_jointe['informations']['mime'] ?? $informations_fichier['extension']
                    ]
                ];
            }
        }

        $variables = http_build_query(
            array(
                'contenu' => $contenu,
                'variables_mail' => $variables_mail,
                'parametres' => $parametres,
            )
        );

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_MODEL_API_KEY') . "\r\n",
                'method' => 'POST',
                'content' => $variables,
            )
        );

        $contexte = stream_context_create($options);

        try {
            $requete = file_get_contents($url_index, false, $contexte);

            $requete = json_decode($requete, true);

            if ($requete['statut'] !== true) {
                Log::warning("[Envoi mail base mail] Le mail n'a pas pu être envoyé.
                Variables pour la vue : " . json_encode($variables_mail) . ".
                Paramètres d'envoi : " . json_encode($parametres) . " . Erreur : " . $requete['donnees']);

                return traduction('messages.php.email.erreur_envoi_mail_eden');
            }
        }
        catch(\Exception $e){

            Log::warning("[Envoi mail base mail] Le mail n'a pas pu être envoyé.
                Variables pour la vue : " . json_encode($variables_mail) . ".
                Paramètres d'envoi : " . json_encode($parametres) . " . Erreur : " . $e->getMessage() . ". Stacktrace : " . $e->getTraceAsString());

            return traduction('messages.php.email.erreur_envoi_mail_eden');
        }

        return true;
    }

    public function chargement_elements($type_lien, $groupes_ids, $parametres){

        $management_lien_champ = management($type_lien);

        $champs_libres_lien = champs_libres($type_lien)->pluck('nom_sql')->toArray();

        $parametrages = modele($type_lien)
            ->where(function($requete) use ($management_lien_champ) {
                return $requete->whereNotNull('lien_champ')->orWhereNotNull($management_lien_champ->champ_valeur_dur);
            });

        if(isset($parametres['modele_email_id'])){
            $modele_email = modele('modele_email', $parametres['modele_email_id']);
            $type_element_id = $modele_email->type_element_id ?? null;
        }
        else if(isset($parametres['notification_manuelle_id'])){
            $notification_manuelle = modele('notification_manuelle', $parametres['notification_manuelle_id']);
            $type_element_id = $notification_manuelle->type_element_id ?? null;
        }
        else if(isset($parametres['type_element'])){
            $table_libre = Table_libre::where('type_element', $parametres['type_element'])->first();
            $type_element_id = $table_libre->id ?? null;
            $parametres['type_element_id'] = $type_element_id;
            unset($parametres['type_element']);
        }
        else
            $type_element_id = $parametres['type_element_id'] ?? null;

        $case = 'CASE ';

        if(in_array('type_element_id', $champs_libres_lien))
            $case .= "WHEN type_element_id IS NOT NULL AND type_element_id = ".$type_element_id." THEN 'type_element' ";
        if(in_array('modele_email_id', $champs_libres_lien))
            $case .= "WHEN modele_email_id IS NOT NULL THEN CONCAT('modele_email.', modele_email_id) ";
        if(in_array('notification_manuelle_id', $champs_libres_lien))
            $case .= "WHEN notification_manuelle_id IS NOT NULL THEN CONCAT('notification_manuelle_.', notification_manuelle_id) ";

        $case.= 'ELSE NULL END as type_existant';

        $sous_requete = modele($type_lien);

        $sous_requete = modele($type_lien)->selectRaw("id,".$case);

        $union = modele($type_lien)
            ->select('pdm_existant.*')
            ->joinSub($sous_requete, 'pdm_lien', function($join) use ($type_lien) {
                $join->on($type_lien.'.parametrage_existant', 'pdm_lien.type_existant');
            })
            ->join($type_lien.' as pdm_existant', 'pdm_existant.id', 'pdm_lien.id');

        foreach($parametres as $champ => $valeur){

            $prefixe = $champ == 'niveau' ? 'pdm_existant.' : $type_lien.'.';
            $parametrages = $parametrages->{is_array($valeur) ? 'whereIn' : 'where'}($type_lien.'.'.$champ, $valeur);
            $union = $union->{is_array($valeur) ? 'whereIn' : 'where'}($prefixe.$champ, $valeur);
        }

        $parametrages->union($union);

        if(in_array('niveau', $champs_libres_lien))
            $parametrages->orderBy('niveau', 'desc');

        $parametrages = $parametrages->get();

        $valeurs_globales = $management_lien_champ->valeurs_globales($parametrages, $parametres);

        if(empty($groupes_ids))
            return ['valeurs_globales' => $valeurs_globales, 'valeurs' => []];

        $valeurs_par_groupe = $management_lien_champ->valeurs_par_groupe($parametrages, $groupes_ids, $parametres);

        return ['valeurs_globales' => $valeurs_globales, 'valeurs' => $valeurs_par_groupe];
    }

    /*
     *
     * Retourne un compte email factice 
     * pour l'utilisateur connecté n'ayant pas de compte email mais un compte microsoft
     * 
     */
    public function compte_email_utilisateur_microsoft($utilisateur){

        return modele('compte_email')->forceFill([
            'id' => -1,
            'utilisateur_id' => $utilisateur->id,
            'chaine_affichage' => traduction('messages.php.modale_email.compte_utilisateur_microsoft') ." ". 
                management('utilisateur', $utilisateur->id, $utilisateur)->affichage_pour_select(),
            'adresse_email' => $utilisateur->email,
            'nom_expediteur' => $utilisateur->prenom . ' ' . $utilisateur->nom,
            'image_signature' => null,
            'type_de_compte' => 1,
            'valide' => 1,
        ]);
    }
}
