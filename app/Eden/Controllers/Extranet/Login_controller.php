<?php

namespace App\Eden\Controllers\Extranet;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use Hash;
use \App\Eden\Managements\Authentification_management;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Crypt;


class Login_controller extends Controller {

   /**
	*
	* Page de connection
	*
	*/
    public function login() {

        if(!empty(moi_extranet()))
    		return redirect()->route('extranet.accueil');

    	return view('eden::extranet.login');
    }


   /**
	*
	* Envoi du formulaire de connexion
	*
	*/
	public function login_post(Request $formulaire_login) {

        $adresse_ip = $this->obtenir_adresse_ip();

        $management_log = management('log_tentative_connexion');

        $informations_log = $this->initialise_log_connexion($adresse_ip, $formulaire_login->email);

        $utilisateur = modele('utilisateur_extranet')->where('email', $formulaire_login->email)->first();

		if ( $utilisateur == null ) {

            $management_log->enregistre($informations_log);
            
			return redirect()->back()->withErrors([traduction('messages.php.connexion.compte_introuvable')]);
		}

		if ($utilisateur->autorise_a_se_connecter != 1) {
            $management_log->enregistre($informations_log);
            return redirect()->back()->withErrors([traduction('messages.php.connexion.connexion_impossible')]);
		}

        $management_utilisateur = management('utilisateur_extranet', $utilisateur->id, $utilisateur);

        $comparaison_mdp = $management_utilisateur->comparaison_mdp_connexion($formulaire_login->password);

        $comparaison_mdp_methode = 0;

        if ($comparaison_mdp === true) {

            $informations_log['succes'] = 1;
            $comparaison_mdp_methode = 1;
        }

        // On gère le blocage des robots / tentatives de hack
        $management_authentification = new Authentification_management;

        $verification_robot = $management_authentification->verification_robot($formulaire_login->email, $comparaison_mdp_methode,$adresse_ip, $management_log, $informations_log);

        if($verification_robot !== true)
            return array(false, $verification_robot);

		if ( $comparaison_mdp !== true ) {

            $management_log->enregistre($informations_log);
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_incorrect')]);
        }

        if(!empty($utilisateur->double_facteur_authentification)){

            $token_double_authentification = Hash::make($utilisateur->email . 'double_facteur_authentification' . date('Y-m-d'));

            if($utilisateur->double_facteur_authentification == 1) {

                $parametres_email = [
                    'type_configuration' => 1,
                    'destinataire' => [$utilisateur->email],
                    'sujet' => maquette('nom_application')." - Code à usage unique d'authentification / Unique code for connexion",
                ];

                $code_connexion = rand(100000, 999999);
                $date_expiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                $management_utilisateur->enregistre_modele([
                    'code_connexion' => Crypt::encryptString($code_connexion),
                    'expiration_code_connexion' => $date_expiration,
                ]);

                $variables_email = ['code_connexion' => $code_connexion];

                $service_email = service('email');

                $retour = $service_email->envoyer('eden::extranet.mails.code_connexion', $variables_email, $parametres_email);

                return redirect()->route('extranet.code_connexion', [$utilisateur->id , 't' => $token_double_authentification]);
            }
            else
                return redirect()->route('extranet.code_connexion', [$utilisateur->id , 't' => $token_double_authentification]);
        }

        if($management_utilisateur->mot_de_passe_a_renouveler()){

            $token_renouvellement = Hash::make($utilisateur->email . 'renouvellement_mdp' . date('Y-m-d'));

			return redirect()->route('extranet.renouvellement_mot_de_passe', [$utilisateur->id,'t' => $token_renouvellement]);
        }

		$management_utilisateur->creation_session();

		return service('extranet')->retourne_url_post_connexion();
	}


   /**
	*
	* Page de récupération du mdp
	*
	*/
	public function mot_de_passe_oublie() {

        if(!empty(moi_extranet()))
    		return redirect()->route('extranet.accueil');

		return view('eden::extranet.mot_de_passe_oublie');
	}

   /**
	*
	* Recup formulaire reset mdp et envoi d'email
	*
	*/
	public function mot_de_passe_oublie_post(Request $formulaire) {

		$utilisateur_extranet = modele('utilisateur_extranet')->where('email', $formulaire->email)->first();

		if(empty($utilisateur_extranet))
			return back()->with('erreur_validation', traduction('messages.php.connexion.email_introuvable'));

		$token_custom = Hash::make($utilisateur_extranet->email .'changement_mdp'.date('Y-m-d'));
        $url_mdp_oublie = route('extranet.changer_mot_de_passe',['id' => $utilisateur_extranet->id, 't' => $token_custom] );

        $destinataire = $formulaire->email;
        
        // on prépare les données pour envoyer le mail
        $service_email = service('email');
        $modele_email = fonctionnalite('extranet_modele_email_mdp_oublie');

        $service_publipostage = service('publipostage');

        if(!empty($modele_email)){

            $modele_email = modele('modele_email', $modele_email);

            $sujet = $service_publipostage->publipostage_texte($modele_email->sujet_modele, 'utilisateur_extranet', [$utilisateur_extranet->id]);
            $contenu_mail = $service_publipostage->publipostage_texte($modele_email->modele, 'utilisateur_extranet', [$utilisateur_extranet->id]);

            if(strpos($contenu_mail, '#url_mdp_oublie#') !== false)
                $contenu_mail = str_replace('#url_mdp_oublie#', $url_mdp_oublie, $contenu_mail);

            if(strpos($sujet, '#url_mdp_oublie#') !== false)
                $sujet = str_replace('#url_mdp_oublie#', $url_mdp_oublie, $sujet);
        }

        $parametres_email = [
            'type_configuration' => 4,
            'id_compte_email' => fonctionnalite('extranet_compte_email'),
            'destinataire' => [$destinataire],
            'sujet' => $sujet ?? traduction('messages.php.connexion.reinitialisation_mdp'),
        ];

        $variables_email = [
            'url_mdp_oublie' => $url_mdp_oublie,
            'contenu_mail' => $contenu_mail ?? '',
        ];

        $retour = $service_email->envoyer('eden::extranet.mails.mail', $variables_email, $parametres_email);

		return view('eden::extranet.mot_de_passe_oublie_ok', array('retour' => $retour));
	}

   /**
    *
    * formulaire de changement de mdp
    *
    */
    public function changer_mot_de_passe($id){

        $token = request()->t ?? null;

        if(!empty(moi_extranet()))
    		return redirect()->route('extranet.accueil');

        $compte = modele('utilisateur_extranet')->find($id);

        if (!Hash::check($compte->email .'changement_mdp'.date('Y-m-d'),$token)){
            return  redirect()->route('extranet.mot_de_passe_oublie')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #2)"))]);
        }

        $langues = langues();

        return view('eden::extranet.reinitialisation_mot_de_passe', array('id' => $id,'token' => $token, 'langue' => langue_utilisateur($langues->where('id',$compte->langue)->first()->code ?? null)));
    }

   /**
	*
	* formulaire de changement de mdp
	*
	*/
	public function changer_mot_de_passe_post(Request $formulaire){

        $id = $formulaire->id_utilisateur;
        $token = $formulaire->token;

		$modele = modele('utilisateur_extranet')->find($id);

		$vrai_token = $modele->email .'changement_mdp'.date('Y-m-d');

		if (!Hash::check($modele->email .'changement_mdp'.date('Y-m-d'),$token))
            redirect()->route('extranet.mot_de_passe_oublie');

		if ($modele->autorise_a_se_connecter != 1)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.connexion_impossible')]);

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $formulaire->mot_de_passe))
			return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_invalide')]);

		if($formulaire->mot_de_passe != $formulaire->mot_de_passe_confirmation)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_differents')]);

        $management_utilisateur = management('utilisateur_extranet', $modele->id, $modele);

        $comparaison_mdp = $management_utilisateur->comparaison_mdp_connexion($formulaire->mot_de_passe);

        if($comparaison_mdp)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_pareils_precedent')]);

        $management_utilisateur->enregistre(
            array('mot_de_passe' => Hash::make($formulaire->mot_de_passe))
        );

        $management_utilisateur->creation_session();

		return redirect(service('extranet')->retourne_url_page_accueil_extranet());
	}

   /**
	*
	* Page de création d'un compte
	*
	*/
	public function inscription($id) {

        if(!empty(moi_extranet()))
    		return redirect()->route('extranet.accueil');

		$valide = false;
		$erreur = null;

		$modele = modele('utilisateur_extranet')->find($id);

		$login = $modele->email;

        $token = request()->t ?? null;

		$exception = Hash::check($modele->email .'inscription',$token);

		if( $exception !== true || !empty($modele->mot_de_passe) || !empty($modele->old_mot_de_passe))
            return redirect()->route('extranet.login');

        $langues = langues();
		    
        return view('eden::extranet.inscription', array(
            'id' => $id,'token' => $token, 'login' => $login, 'langue' => langue_utilisateur($langues->where('id',$modele->langue)->first()->code ?? null)
        ));
	}

   /**
	*
	* Envoi du formulaire d'inscription
	*
	*/
	public function validation_inscription(Request $formulaire) {

        $id = $formulaire->id_utilisateur;
        $token = $formulaire->token;

		$modele = modele('utilisateur_extranet')->find($id);

        $verifie_token_connexion = Hash::check($modele->email .'inscription',$token);

		if ( $verifie_token_connexion !== true)
            return redirect()->back();

        if ($modele->autorise_a_se_connecter != 1) {
            return redirect()->back()->withErrors([traduction('messages.php.connexion.connexion_impossible')]);
		}

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $formulaire->mot_de_passe))
			return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_invalide')]);

		if($formulaire->mot_de_passe != $formulaire->mot_de_passe_confirmation)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_differents')]);

        $management_utilisateur = management('utilisateur_extranet', $id, $modele);
		$management_utilisateur->enregistre(
            array('mot_de_passe' => Hash::make($formulaire->mot_de_passe))
        );

        $management_utilisateur->creation_session();

		return redirect(service('extranet')->retourne_url_page_accueil_extranet());
	}

   /**
	*
	* Bouton de déconnexion
	*
	*/
	public function deconnexion() {

		session()->flush();

		return redirect()->route('extranet.login');
	}

    public function usurpation_extranet($element_id){

        $url_serveur = str_replace(["http://", "https://"], "", $_SERVER['HTTP_REFERER'] ?? '');
        $url_eden = str_replace(["http://", "https://"], "", env('APP_URL'));

        if(empty($url_serveur) || !substr($url_serveur, 0, strlen($url_eden)) == $url_eden)
            return redirect()->route('extranet.login');

        if(substr($url_serveur, 0, strlen($url_eden)) == $url_eden && !empty(moi())) 
            return redirect()->to("https://" . fonctionnalite('url_extranet') . "/extranet/usurpation/{$element_id}?retour_url=" . request()->retour_url.(request()->contact_id_source ? '&contact_id_source='.request()->contact_id_source : ''));

        session()->flush();

        management('utilisateur_extranet',$element_id)->creation_session(request()->contact_id_source ?? null);

        session()->put('extranet_usurpation_retour_eden',request()->retour_url);

        return service('extranet')->retourne_url_post_connexion();
    }

    /**
     *
     * Retourne l'adresse IP du client
     *
     */
    public function obtenir_adresse_ip(){

        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];

        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];

        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];

        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];

        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];

        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    /**
     *
     * Initialise les informations pour le log
     *
     */
    public function initialise_log_connexion($adresse_ip,$email,$verification_mot_de_passe = 0){

        $location = 'inconnu';

        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'ignore_errors' => true, // récupère la réponse même en cas d'erreur HTTP
            ]
        ]);

        $reponse = @file_get_contents("http://ipinfo.io/$adresse_ip/geo", false, $context);

        // $http_response_header est automatiquement rempli par file_get_contents
        $code_http = null;
        if (!empty($http_response_header)) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $match);
            $code_http = $match[1] ?? null;
        }

        if ($reponse !== false && $code_http == 200) {
            $json = json_decode($reponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $pays   = $json['country'] ?? 'inconnu';
                $region = $json['region']  ?? 'inconnu';
                $ville  = $json['city']    ?? 'inconnu';
                $location = "$pays, $region, $ville";
            }
        }
        // Si 429 (too many requests), erreur réseau, ou JSON invalide → $location reste 'inconnu'

        $support = est_un_mobile() ? "Téléphone mobile" : "Ordinateur";

        return [
            'adresse_email' => $email,
            'ip'            => $adresse_ip,
            'navigateur'    => $_SERVER['HTTP_USER_AGENT'],
            'emplacement'   => $location,
            'support'       => $support,
            'date'          => date('Y-m-d H:i:s'),
            'succes'        => $verification_mot_de_passe,
            'source'        => 2,
        ];
    }

    /**
     *
     * Permet une connexion à l'extranet via un site externe
     *
     */
    public function connexion_externe(){

        $parametres = request()->all();

        try{
            $parametres = json_decode(base64_decode($parametres['info']),true);
        }
        catch (\Exception | \Throwable $exception){
            return redirect(route('extranet.login'));
        }

        if(empty($parametres['token']) || empty($parametres['adresse_email']))
            return redirect(route('extranet.login'));

        $retour = service('extranet')->correspondance_token_connexion($parametres['token'],$parametres['adresse_email']);

        if($retour !== true)
            return redirect(route('extranet.login'));

        $compte_extranet = modele('utilisateur_extranet')
            ->where('email',$parametres['adresse_email'])
            ->first();

        $contact_associe = modele('contact')
            ->where('utilisateur_extranet_id', $compte_extranet->id ?? 0);

        if(!empty($parametres['parametres_compte']['contact'])){

            foreach ($parametres['parametres_compte']['contact'] as $champ => $parametre_compte) {

                $contact_associe = $contact_associe->where($champ, $parametre_compte);
            }
        }

        $contact_associe = $contact_associe->first();

        if($compte_extranet == null || $contact_associe == null)
            return redirect(route('extranet.login'));

        management('utilisateur_extranet',$compte_extranet->id,$compte_extranet)->creation_session($contact_associe->id);

        return service('extranet')->retour_connexion_externe($parametres);
    }

    public function renouvellement_mot_de_passe($id) {

		$authentification_management = new Authentification_management();

        $utilisateur = modele('utilisateur_extranet')->find($id);

        $token = request()->t ?? '';

		if($utilisateur->renouveler_mot_de_passe != 1 || !Hash::check($utilisateur->email . 'renouvellement_mdp' . date('Y-m-d'), $token))
			return redirect()->route('extranet.login');
		
		return view('eden::extranet.renouvellement_mot_de_passe', [
			'id_utilisateur' => $utilisateur->id,
			'token' => $token,
		]);
	}

    public function renouvellement_mot_de_passe_post(Request $formulaire) {
        
        $id = $formulaire->id_utilisateur;
        $token = $formulaire->token;

        $utilisateur = modele('utilisateur_extranet')->find($id);

		if($utilisateur === null || $utilisateur->renouveler_mot_de_passe != 1 || !Hash::check($utilisateur->email . 'renouvellement_mdp' . date('Y-m-d'), $token))
			return redirect()->back()->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);

		$management = management('utilisateur_extranet',$utilisateur->id,$utilisateur);

		if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $formulaire->mot_de_passe))
			return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_invalide')]);

		if($formulaire->mot_de_passe != $formulaire->mot_de_passe_confirmation)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_differents')]);

		if(Hash::check($formulaire->mot_de_passe, $utilisateur->mot_de_passe))
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_pareils_precedent')]);

        $management->enregistre(['mot_de_passe' => Hash::make($formulaire->mot_de_passe)]);
        $management->creation_session();

		return service('extranet')->retourne_url_post_connexion();
    }

    public function code_connexion($id){

        $token = request()->t ?? null;

        $utilisateur = modele('utilisateur_extranet')->find($id);
		
		if(!Hash::check($utilisateur->email . 'double_facteur_authentification' . date('Y-m-d'),$token))
			return redirect()->route('extranet.login')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);

		if($utilisateur->double_facteur_authentification == 1 && (empty($utilisateur->code_connexion) || $utilisateur->expiration_code_connexion < date('Y-m-d H:i:s')))
			return redirect()->route('extranet.login');

		return view('eden::extranet.retour_code_connexion', [
			'id_utilisateur' => $utilisateur->id,
			'token' => $token,
			'double_facteur_authentification' => $utilisateur->double_facteur_authentification
		]);
	}

	public function retour_code_connexion(Request $formulaire){

		$id = $formulaire->input('id_utilisateur');
		$token = $formulaire->input('token');
		$code_connexion = $formulaire->input('code_connexion');

        $utilisateur = modele('utilisateur_extranet')->find($id);

		if($utilisateur === null || !Hash::check($utilisateur->email . 'double_facteur_authentification' . date('Y-m-d'),$token))
			return redirect()->route('extranet.login')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);

		if($utilisateur->double_facteur_authentification == 1){

			if(empty($utilisateur->code_connexion) || $utilisateur->expiration_code_connexion < date('Y-m-d H:i:s'))
				return redirect()->route('extranet.login');

			if($code_connexion != Crypt::decryptString($utilisateur->code_connexion))
				return redirect()->back()->withErrors([traduction('messages.php.connexion.code_invalide')]);
		}
		else{
			$google2fa = new Google2FA();

			$valide = $google2fa->verifyKey(Crypt::decryptString($utilisateur->google2fa_secret), $code_connexion);

			if(!$valide)
				return redirect()->back()->withErrors([traduction('messages.php.connexion.code_invalide')]);
		}

		$management_utilisateur = management('utilisateur_extranet', $utilisateur->id, $utilisateur);

		if($management_utilisateur->mot_de_passe_a_renouveler()){

            $token_renouvellement = Hash::make($utilisateur->email . 'renouvellement_mdp' . date('Y-m-d'));

			return redirect()->route('extranet.renouvellement_mot_de_passe', [$utilisateur->id, 't' => $token_renouvellement]);
        }

		$management_utilisateur->creation_session();

		return service('extranet')->retourne_url_post_connexion();
	}

	public function renvoi_mail_code_connexion(Request $formulaire){

        $id = $formulaire->input('id_utilisateur');
        $token = $formulaire->input('token');

		$utilisateur = modele('utilisateur_extranet')->find($id);

		if($utilisateur === null || !Hash::check($utilisateur->email . 'double_facteur_authentification' . date('Y-m-d'),$token))
			return response()->json(false);

		$parametres_email = [
			'type_configuration' => 4,
			'destinataire' => [$utilisateur->email],
			'sujet' => maquette('nom_application')." - Code à usage unique d'authentification / Unique code for connexion",
		];

		$code_connexion = rand(100000, 999999);
		$date_expiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

		$management_utilisateur = management('utilisateur_extranet', $utilisateur->id, $utilisateur);

		$management_utilisateur->enregistre_modele([
			'code_connexion' => Crypt::encryptString($code_connexion),
			'expiration_code_connexion' => $date_expiration,
		]);

		$variables_email = ['code_connexion' => $code_connexion];

		$retour = service('email')->envoyer('eden::extranet.mails.code_connexion', $variables_email, $parametres_email);

		return response()->json($retour);
	}
}
