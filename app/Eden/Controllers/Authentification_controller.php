<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cache_management;

use App\Eden\Managements\Authentification_management;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Crypt;

use App\Eden\Models\Utilisateur;

use Cookie;

class Authentification_controller extends Controller {
	
	/**
	 * 
	 * Affiche le formulaire de login
	 * 
	 */
    public function login() {

        if($_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet'))
            return redirect()->route('extranet.login');

		$remember_token = Cookie::get('remember_token');

		if($remember_token !== null && !session()->has('utilisateur_eden')) {
			
			// on ne peut pas passer par modele() ici
			// car si la gestion des droits est appliquée sur la table utilisateur,
			// nous avons un bug
			// $utilisateur = modele('utilisateur')->where('remember_token', $remember_token)->first();
			$utilisateur = Utilisateur::where('remember_token', $remember_token)->first();
			
			if($utilisateur !== null) {

                management('utilisateur',$utilisateur->id,$utilisateur)->chargement_entites();

				// on met l'utilisateur en session
				session()->put('utilisateur_eden', $utilisateur);

                // on redirige vers l'accueil
                return redirect()->route('base_eden.accueil.index');
			}

        }

    	// Si on est déjà connecté, on redirige vers la page d'accueil
    	if(!empty(moi()))
    		return redirect()->route('base_eden.accueil.index');

    	// Sinon, on affiche la page de login
		return view('eden::authentification.login');
    }

    /**
	 * 
	 * Affiche le formulaire de login initial
	 * 
	 */
    public function login_eden() {
		
		$remember_token = Cookie::get('remember_token');

		if($remember_token !== null && !session()->has('utilisateur_eden')) {
			
			// on ne peut pas passer par modele() ici
			// car si la gestion des droits est appliquée sur la table utilisateur,
			// nous avons un bug
			// $utilisateur = modele('utilisateur')->where('remember_token', $remember_token)->first();
			$utilisateur = Utilisateur::where('remember_token', $remember_token)->first();
			
			if($utilisateur !== null) {

                management('utilisateur',$utilisateur->id,$utilisateur)->chargement_entites();
				
				// on met l'utilisateur en session
				session()->put('utilisateur_eden', $utilisateur);

                // on redirige vers l'accueil
                return redirect()->route('base_eden.accueil.index');
			}

        }

    	// Si on est déjà connecté, on redirige vers la page d'accueil
    	if(!empty(moi()))
    		return redirect()->route('base_eden.accueil.index');

    	// Sinon, on affiche la page de login
		return view('eden::authentification.login_eden');
    }
	
	/**
	 * 
	 * On checke la connexion d'un utilisateur
	 * 
	 */
    public function connexion(Request $formulaire, Authentification_management $authentification_management) {

		list($resultat, $message) = $authentification_management->connexion($formulaire->email, $formulaire->password);

		if($resultat === false)
			return redirect()->back()->withErrors([$message]);
		
		$utilisateur = $message;

		$email_utilisateur = $utilisateur->email;

		if(!empty($utilisateur->email_mot_de_passe_oublie)) {

			$email_utilisateur = $utilisateur->email_mot_de_passe_oublie;
		}

		$management_utilisateur = management('utilisateur', $utilisateur->id, $utilisateur);

		$token_base_de_donnees = md5($email_utilisateur.$utilisateur->id.$utilisateur->mot_de_passe);

		if($utilisateur->double_facteur_authentification == 1) {

			$parametres_email = [
				'type_configuration' => 1,
				'destinataire' => [$utilisateur->email],
				'sujet' => "EDEN - Code à usage unique d'authentification / Unique code for connexion",
			];

			$code_connexion = rand(100000, 999999);
			$date_expiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

			$management_utilisateur->enregistre_modele([
				'code_connexion' => Crypt::encryptString($code_connexion),
				'expiration_code_connexion' => $date_expiration,
			]);

			$variables_email = ['code_connexion' => $code_connexion];

			$service_email = service('email');

			$retour = $service_email->envoyer('eden::mails.code_connexion', $variables_email, $parametres_email, 'eden');

			return redirect()->route('code_connexion', [$utilisateur->id, $token_base_de_donnees, $formulaire->get('remember') == 'on' ? 1 : 0]);
		}
		else if($utilisateur->double_facteur_authentification == 2) {
			return redirect()->route('code_connexion', [$utilisateur->id, $token_base_de_donnees, $formulaire->get('remember') == 'on' ? 1 : 0]);
		}

		if($management_utilisateur->mot_de_passe_a_renouveler())
			return redirect()->route('renouvellement_mot_de_passe', [$utilisateur->id, $token_base_de_donnees]);
		
		$management_utilisateur = management('utilisateur', $utilisateur->id, $utilisateur);

		$donnees = [
			'date_derniere_connexion' => date('Y-m-d H:i:s')
		];

		// on met l'utilisateur en session
		session()->put('utilisateur_eden', $utilisateur);
		
		// il y a un remember token
		if($formulaire->get('remember') == 'on') {
			
			// on crée un remember token
			$remember_token = md5($utilisateur->id . date('dmyHis') . $formulaire->password);

			$donnees['remember_token'] = $remember_token;
			
			Cookie::queue(Cookie::make('remember_token', $remember_token, 60 * 24 * 7));
		}

		$management_utilisateur->enregistre_modele($donnees);

        if(session()->has('redirection_post_login')){

            $redirection = session()->get('redirection_post_login');

            session()->forget('redirection_post_login');

            return redirect($redirection);
        }

		// on redirige vers l'accueil
		return redirect()->route('base_eden.accueil.index');
    }
	
	/**
	 * 
	 * Permet d'usurper l'identité d'un utilisateur
	 * 
	 */
    public function usurpation($id_utilisateur) {

		
		management('utilisateur', moi()->id)->droit_usurpation();

		
		// on renseigne l'info d'usurpation en session
        if(!session()->has('eden_usurpation_origine'))
		    session()->put('eden_usurpation_origine', moi());
		
		// on vide la session de l'utilisateur courant
		session()->forget('utilisateur_eden');

        $utilisateur = Utilisateur::find($id_utilisateur);

        management('utilisateur',$utilisateur->id,$utilisateur)->chargement_entites();
		
		// on met l'utilisateur en session
		session()->put('utilisateur_eden', $utilisateur);

        session()->forget('cache.droits_profils');
		
		// sauvegarde de la session
		session()->save();

		return redirect()->back();
    }
	
	/**
	 * 
	 * Permet de revenir à l'utilisateur d'origine
	 * 
	 */
    public function usurpation_retour() {
		
		if(!session()->has('eden_usurpation_origine'))
			exit;

        $utilisateur = session()->get('eden_usurpation_origine');
		
		// on met l'utilisateur en session
		session()->put('utilisateur_eden', $utilisateur);

		session()->forget('eden_usurpation_origine');

        session()->forget('cache.droits_profils');

		// sauvegarde de la session
		session()->save();

        Cache_management::vider(true);

		return redirect()->back();
    }
	
	/**
	 * 
	 * Déconnecte un utilisateur
	 * 
	 */
	public function deconnexion(Authentification_management $authentification_management) {
		
		$extranet = false;
		
		if(empty(moi()))
			$extranet = true;
		
		// on déconnecte l'utilisateur
		$authentification_management->deconnexion();
		
		// on redirige vers la page de connexion
		if(fonctionnalite('url_extranet') != "" && $_SERVER['SERVER_NAME'] == fonctionnalite('url_extranet'))
			return redirect()->route('extranet.accueil');

		if($extranet == true)
			return redirect()->route('extranet.accueil');

		return redirect()->route('base_eden.accueil.index');
	}
	
	/**
	 * 
	 * Affiche le formulaire de mot de passe oublié
	 * 
	 */
    public function mot_de_passe_oublie() {
		
		return view('eden::authentification.mot_de_passe_oublie');
    }
	
	/**
	 * 
	 * Envoie l'email de réinitialisation de mot de passe
	 * 
	 */
    public function mot_de_passe_oublie_envoi_mail(Request $formulaire) {
		
		// on va chercher l'utilisateur
		$utilisateur = Utilisateur::where('email', $formulaire->email)->orWhere('email_mot_de_passe_oublie', $formulaire->email)->first();


		
		// aucun utilisateur trouvé
		if($utilisateur === null) {
			
			return redirect()->back()->withErrors([traduction('messages.php.connexion.email_introuvable')]);
		}


		$email_utilisateur = $utilisateur->email;

		if(!empty($utilisateur->email_mot_de_passe_oublie)) {

			$email_utilisateur = $utilisateur->email_mot_de_passe_oublie;
		}
		

		
		// on doit calculer un token via l'utilisateur
		$token = md5($email_utilisateur.$utilisateur->id.$utilisateur->mot_de_passe);
		
		$lien_reinitialisation_mot_de_passe = route('reinitialisation_mot_de_passe', [$utilisateur->id, $token]);

		// on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$email_utilisateur],
            'sujet' => "Réinitialisation de mot de passe / Reset password",
        ];

        $variables_email = ['lien_reinitialisation_mot_de_passe' => $lien_reinitialisation_mot_de_passe];

        $retour = $service_email->envoyer('eden::mails.mot_de_passe_oublie', $variables_email, $parametres_email, 'eden');

		return view('eden::authentification.mot_de_passe_oublie_ok', ['retour' => $retour]);
    }
	
	/**
	 * 
	 * Envoie l'email de réinitialisation de mot de passe
	 * 
	 */
    public function reinitialisation_mot_de_passe($id, $token) {

		$authentification_management = new Authentification_management();

		$utilisateur = $authentification_management->verification_utilisateur_et_token($id,$token);
		
		if($utilisateur === null)
			return redirect()->route('mot_de_passe_oublie')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);
		
		return view('eden::authentification.reinitialisation_mot_de_passe', [
			
			'id_utilisateur' => $utilisateur->id,
			'token' => $token,
		]);
    }

	public function renouvellement_mot_de_passe($id, $token) {

		$authentification_management = new Authentification_management();

		$utilisateur = $authentification_management->verification_utilisateur_et_token($id,$token);
		
		if($utilisateur === null)
			return redirect()->route('login')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);
		
		return view('eden::authentification.renouvellement_mot_de_passe', [
			
			'id_utilisateur' => $utilisateur->id,
			'token' => $token,
		]);
	}

    public function acces_restreint() {
		
		return view('eden::authentification.acces_restreint');
    }
	
	/**
	 * 
	 * Enregistre le nouveau mot de passe de l'utilisateur
	 * 
	 */
    public function reinitialisation_mot_de_passe_post(Request $formulaire) {

		$authentification_management = new Authentification_management();

		$utilisateur = $authentification_management->verification_utilisateur_et_token($formulaire->id_utilisateur,$formulaire->token);
		
		if($utilisateur === null)
			return redirect()->route('mot_de_passe_oublie')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);

		$management = management('utilisateur',$utilisateur->id,$utilisateur);
        $management->chargement_entites();

		if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $formulaire->mot_de_passe))
			return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_invalide')]);

		if($formulaire->mot_de_passe != $formulaire->mot_de_passe_confirmation)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_differents')]);

		$nouveau_mdp = md5('easy'.$formulaire->mot_de_passe.'dev');
		
		if($utilisateur->mot_de_passe == $nouveau_mdp)
            return redirect()->back()->withErrors([traduction('messages.php.connexion.mdp_pareils_precedent')]);

        $informations = [
            'mot_de_passe' => $nouveau_mdp,
			'renouveler_mot_de_passe' => 0,
			'date_derniere_modification_mot_de_passe' => date('Y-m-d H:i:s'),
        ];

		if($formulaire->get('remember') == 'on') {

            $informations['remember_token'] = md5($utilisateur->id . date('dmyHis') . $formulaire->password);
            Cookie::queue(Cookie::make('remember_token', $informations['remember_token'], 60 * 24 * 365));
		}

        $management->enregistre_modele($informations);
        session()->put('utilisateur_eden', $utilisateur);

		// on redirige vers l'accueil
		return redirect()->route('base_eden.accueil.index');
    }
	
	/**
	 * 
	 * Affiche le formulaire de login pour microsoft
	 * 
	 */
	public function login_microsoft() {
		 
		// On affiche la page de login
		return view('eden::authentification.login_microsoft');
	}
	
	/**
	 * 
	 * Crée la connexion et redirige vers la page de connexion de Microsoft
	 * 
	 */
    public function connexion_microsoft() {
		
		// Initialise le client OAuth
		$oauth_client = service('microsoft_authentification')->retourne_oauth_client();

		//Récupération de l'url de la page de connexion de Microsoft
		$url_authentification_chez_microsoft = $oauth_client->getAuthorizationUrl();

		// Sauvegarde l'état du client pour le valider dans le retour
		session(['statut_oauth_attendu' => $oauth_client->getState()]);
		
		// Redirige vers la page de connexion Microsoft
		if(isset($url_authentification_chez_microsoft)){

			return redirect()->away($url_authentification_chez_microsoft);
		}
		// Sinon, on affiche la page de login
		return redirect('/eden/login');
    }
	
	/**
	 * 
	 * Gère la récupération des informations du token lorsqu'on revient sur Eden
	 * 
	 */
	public function retour_microsoft(Request $requete, Authentification_management $authentification_management) {

		// Valide l'état
		$statut_attendu = session('statut_oauth_attendu');
		$requete->session()->forget('statut_oauth_attendu');
		$statut_recu = $requete->query('state');
		
		// Si l'état récupéré dans la session ne correspond pas avec l'état récupéré dans la requête on redirige vers la page d'accueil avec une erreur
		if (!isset($statut_recu, $statut_attendu) || $statut_attendu != $statut_recu)
			return redirect('/eden/login')->withErrors([traduction('messages.php.connexion_microsoft.erreur_statut')]);

		// On récupère le code d'autorisation dans le paramètre code de la requête
		$code_authentification = $requete->query('code');
		
		//On vérifie que le code d'autorisation existe
		if (isset($code_authentification)) {
			
			// Initialisation du client OAuth
			$client_oauth = service('microsoft_authentification')->retourne_oauth_client();

			try {
				// Fais la requête du token
				$access_token = $client_oauth->getAccessToken('authorization_code', [
				
					'code' => $code_authentification
				]);
				
				// Récupère le token grâce à Graph
				$graph = new Graph();
				$graph->setAccessToken($access_token->getToken());
				
				// Récupération des infos voulues
				$utilisateur = $graph->createRequest('GET', '/me?$select=displayName,mail,mailboxSettings,userPrincipalName,id')
					->setReturnType(Model\User::class)
					->execute();

				list($succes, $utilisateur_eden) = $authentification_management->connexion($utilisateur->getMail(), '', false);
				
				if($succes === false) {
					
					// dans ce cas là, $utilisateur_eden contient le message d'erreur
					return redirect('/eden/login')->withErrors([$utilisateur_eden]);
				}

                $management_utilisateur = management('utilisateur',$utilisateur_eden->id, $utilisateur_eden);
                $modifications = array();

                if(empty($management_utilisateur->modele->id_microsoft))
                    $modifications['id_microsoft'] = $utilisateur->getId();
				
				// Instanciation de la classe pour mettre en mémoire les infos et tokens
				$token = service('microsoft_token');
				$token->mise_memoire_tokens($access_token, $utilisateur_eden->id);
				
				// on met l'utilisateur en session
				session()->put('utilisateur_eden', $utilisateur_eden);
				//On crée un remember token et un cookie
				
				$remember_token = md5($utilisateur_eden->id . date('dmyHis') . $access_token);
                $modifications['remember_token'] = $remember_token;

                $management_utilisateur->enregistre_modele($modifications);
			
				Cookie::queue(Cookie::make('remember_token', $remember_token, 60 * 24 * 365));
				
				// On redirige l'utilisateur vers la page d'accueil
				return redirect('/eden/accueil');
			}
			catch (\Exception | \Throwable $e) {
				
				return redirect('/eden/login')->withErrors([traduction('messages.php.connexion_microsoft.probleme_recuperation_token')]);
			}
		}
		
		// Si le code d'autorisation n'existe pas on redirige vers la page de connexion
		return redirect('/eden/login')->withErrors([traduction('messages.php.connexion_microsoft.probleme_recuperation_token')]);
	}
	
	/**
	 * 
	 * Déconnecte l'utilisateur et supprime les tokens et les infos
	 * 
	 */
	public function deconnexion_microsoft() {
		
		$token = service('microsoft_token');
		
		$token->efface_tokens();
		$token->efface_infos();
		
		return redirect('/eden/login');
	}
	
	/**
	 * 
	 * Crée la connexion et redirige vers la page de connexion de Google
	 * 
	 */
    public function connexion_google() {
		
		// Initialise le client OAuth
		$google_client = service('google_authentification')->instancie_google_client();
		
		//Récupération de l'url de la page de connexion de Google
		$url_authentification_chez_google = $google_client->createAuthUrl();
		
		// Redirige vers la page de connexion Google
		if(isset($url_authentification_chez_google)){
			
			return redirect()->away($url_authentification_chez_google);
		}
		
		// Sinon, on affiche la page de login
		return redirect('/eden/login');
    }
	
	/**
	 * 
	 * Gère la récupération des informations du token lorsqu'on revient sur Eden
	 * 
	 */
	public function retour_google(Request $requete, Authentification_management $authentification_management){
		
		// Si le code d'autorisation n'existe pas on redirige vers la page de connexion
		if(!isset(request()->code)) 
			return redirect('/eden/login')->withErrors([traduction('messages.php.connexion_google.erreur_code_auth').' : '.$requete->query('error_description')]);
		
		$google_client = service('google_authentification')->instancie_google_client();
		$code_authentification = request()->code;

		// Échange du code d'authentification avec le serveur pour pouvoir récupèrer le token
		$access_token = $google_client->fetchAccessTokenWithAuthCode($code_authentification);
		
		$google_client->setAccessToken($access_token);

		// On vérifie s'il y a une erreur
		if (isset($access_token['error']))
			return 'erreur';
		
		//On crée un service Gmail pour pouvoir récupérer l'adresse mail de l'utilisateur
		$gmail_service = new \Google_Service_Gmail($google_client);
		$utilisateur = $gmail_service->users->getProfile('me');

		// On vérifie que l'adresse corresponde à celle d'un compte Eden
		list($succes, $utilisateur_eden) = $authentification_management->connexion($utilisateur->getEmailAddress(), '', false);
				
		if($succes === false) {
			
			// dans ce cas là, $utilisateur_eden contient le message d'erreur
			return redirect('/eden/login')->withErrors([$utilisateur_eden]);
		}
			
				
		// on met l'utilisateur en session
		session()->put('utilisateur_eden', $utilisateur_eden);
		
		// Récupérer le profil de l'utilisateur et vérifier qu'il existe sur Eden
		$token = service('google_token');
		$token->mise_memoire_tokens($access_token, $utilisateur_eden->id);
		
		// On crée un remember token et un cookie
		$remember_token = md5($utilisateur_eden->id . date('dmyHis') . $access_token['access_token']);
        management('utilisateur',$utilisateur_eden->id, $utilisateur_eden)->enregistre_modele(['remember_token' => $remember_token]);
	
		Cookie::queue(Cookie::make('remember_token', $remember_token, 60 * 24 * 365));
		
		// On redirige l'utilisateur vers la page d'accueil
		return redirect('/eden/accueil');
		
	}

	public function code_connexion($id,$token,$remember_token){

		$authentification_management = new Authentification_management();

		$utilisateur = $authentification_management->verification_utilisateur_et_token($id,$token);
		
		if($utilisateur === null)
			return redirect()->route('login')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);

		if($utilisateur->double_facteur_authentification == 1 && (empty($utilisateur->code_connexion) || $utilisateur->expiration_code_connexion < date('Y-m-d H:i:s')))
			return redirect()->route('login');

		return view('eden::authentification.retour_code_connexion', [
			'id_utilisateur' => $utilisateur->id,
			'token' => $token,
			'remember_token' => $remember_token,
			'double_facteur_authentification' => $utilisateur->double_facteur_authentification
		]);
	}

	public function retour_code_connexion(Request $formulaire){

		$id = $formulaire->input('id_utilisateur');
		$token = $formulaire->input('token');
		$code_connexion = $formulaire->input('code_connexion');

		$authentification_management = new Authentification_management();

		$utilisateur = $authentification_management->verification_utilisateur_et_token($id,$token);
		
		if($utilisateur === null)
			return redirect()->route('login')->withErrors([traduction('messages.php.connexion.erreur_survenue',null,array("(Error #1)"))]);

		if($utilisateur->double_facteur_authentification == 1){

			if(empty($utilisateur->code_connexion) || $utilisateur->expiration_code_connexion < date('Y-m-d H:i:s'))
				return redirect()->route('login');

			if($code_connexion != Crypt::decryptString($utilisateur->code_connexion))
				return redirect()->back()->withErrors([traduction('messages.php.connexion.code_invalide')]);
		}
		else{
			$google2fa = new Google2FA();

			$valide = $google2fa->verifyKey(Crypt::decryptString($utilisateur->google2fa_secret), $code_connexion);

			if(!$valide)
				return redirect()->back()->withErrors([traduction('messages.php.connexion.code_invalide')]);
		}

		$management_utilisateur = management('utilisateur', $utilisateur->id, $utilisateur);

		if($management_utilisateur->mot_de_passe_a_renouveler())
			return redirect()->route('renouvellement_mot_de_passe', [$utilisateur->id, $token]);

		// on met l'utilisateur en session
		session()->put('utilisateur_eden', $utilisateur);
		
		// il y a un remember token
		if($formulaire->get('remember_token') == 1) {
			
			// on crée un remember token
			$remember_token = md5($utilisateur->id . date('dmyHis') . $formulaire->password);

			$management_utilisateur->enregistre_modele(['remember_token' => $remember_token]);
			
			Cookie::queue(Cookie::make('remember_token', $remember_token, 60 * 24 * 7));
		}

        if(session()->has('redirection_post_login')){

            $redirection = session()->get('redirection_post_login');

            session()->forget('redirection_post_login');

            return redirect($redirection);
        }

		// on redirige vers l'accueil
		return redirect()->route('base_eden.accueil.index');
	}

	public function renvoi_mail_code_connexion(Request $formulaire){

		$authentification_management = new Authentification_management();

		$utilisateur = $authentification_management->verification_utilisateur_et_token($formulaire->input('id_utilisateur'),$formulaire->input('token'));
		
		if($utilisateur === null)
			return response()->json(false);

		$parametres_email = [
			'type_configuration' => 1,
			'destinataire' => [$utilisateur->email],
			'sujet' => "EDEN - Code à usage unique d'authentification / Unique code for connexion",
		];

		$code_connexion = rand(100000, 999999);
		$date_expiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

		$management_utilisateur = management('utilisateur', $utilisateur->id, $utilisateur);

		$management_utilisateur->enregistre_modele([
			'code_connexion' => Crypt::encryptString($code_connexion),
			'expiration_code_connexion' => $date_expiration,
		]);

		$variables_email = ['code_connexion' => $code_connexion];

		$retour = service('email')->envoyer('eden::mails.code_connexion', $variables_email, $parametres_email, 'eden');

		return response()->json(true);
	}

	public function verification_connexion(){
		$utilisateur = moi() ?? moi_extranet();

        if(empty($utilisateur))
            $statut = 0;
        else if($utilisateur->id != request()->id)
            $statut = 2;
        else
            $statut = 1;

        return response()->json([
            'statut' => $statut
        ]);
	}
	
}