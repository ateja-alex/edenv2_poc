<?php

namespace App\Eden\Managements\Services;

class Microsoft_token_service {

	/**
	 *
	 * Mets en mémoire les tokens pour pouvoir les appeler plus tard
	 *
	 */
	public function mise_memoire_tokens($access_token, $id_utilisateur_eden) {

		$management_utilisateur_microsoft = management('utilisateur', $id_utilisateur_eden);

        $modifications = [
            'refresh_token_microsoft' => $access_token->getRefreshToken(),
            'expiration_token_microsoft' => $access_token->getExpires(),
            'access_token_microsoft' => $access_token->getToken()
        ];

		$management_utilisateur_microsoft->enregistre_modele($modifications);

		session([
			'microsoft.access_token' => $modifications['access_token_microsoft'],
			'microsoft.refresh_token' => $modifications['refresh_token_microsoft'],
			'microsoft.expiration_token' => $modifications['expiration_token_microsoft'],
		]);
	}

	/**
	 *
	 * Mets en mémoire les informations liées à l'utilisateur
	 *
	 */
	public function mise_memoire_infos($utilisateur) {

		session([
			'nom_utilisateur' => $utilisateur->getDisplayName(),
			'email_utilisateur' => null !== $utilisateur->getMail() ? $utilisateur->getMail() : $utilisateur->getUserPrincipalName(),
			'fuseau_horaire_utilisateur' => $utilisateur->getMailboxSettings()->getTimeZone()
		]);
	}

	/**
	 *
	 * Efface les tokens
	 *
	 */
	public function efface_tokens() {

		session()->forget('microsoft.access_token');
		session()->forget('microsoft.refresh_token');
		session()->forget('microsoft.expiration_token');
	}

	/**
	 *
	 * Efface les informations liées à l'utilisateur
	 *
	 */
	public function efface_infos() {

		session()->forget('nom_utilisateur');
		session()->forget('email_utilisateur');
		session()->forget('fuseau_horaire_utilisateur');
	}

	/**
	 *
	 * Retourne l'access token et le met à jour s'il est expiré
	 *
	 */
	public function recupere_access_token() {

		// Vérifie si le token existe
		if (empty(session('microsoft.access_token')) || empty(session('microsoft.refresh_token')) || empty(session('microsoft.expiration_token')))
            return $this->recupere_access_token_base();

		// Vérifie si le token a expiré
		//Récupère l'heure actuelle + 5 minutes (pour permettre des petits écarts)
		$maintenant = time() + 300;

		// Le token est toujours valide, retournons le
		if(session('microsoft.expiration_token') > $maintenant)
			return session('microsoft.access_token');

		// Le token a expiré (ou est très proche de l'être) donc rafraîchissons le
		try {

            // Initialise le client OAuth
            $client_oauth = service('microsoft_authentification')->retourne_oauth_client();

			$nouveau_token = $client_oauth->getAccessToken('refresh_token', ['refresh_token' => session('microsoft.refresh_token')]);
			$id_utilisateur = session('utilisateur_eden')->id;

			// Stocke les nouvelles valeurs
			$this->mise_memoire_tokens($nouveau_token, $id_utilisateur);
			return $nouveau_token->getToken();
		}
		catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

			return redirect('/eden/login')->withErrors([traduction('messages.php.microsoft_token.probleme_renouvellement_connexion')]);
		}
	}


	/**
	 *
	 * Retourne l'access token et le met à jour s'il est expiré
	 *
	 */
	public function recupere_access_token_base($id_utilisateur = false, $utilisateur = null) {

        if(empty($id_utilisateur)) {

            if(!empty(session('utilisateur_eden')))
                $id_utilisateur = session('utilisateur_eden')->id;
            else
                return 'erreur';
        }

        if(empty($utilisateur))
            $utilisateur = modele('utilisateur')->where('id', $id_utilisateur)->first();

		$access_token_utilisateur_microsoft = $utilisateur->access_token_microsoft;
		$refresh_token_utilisateur_microsoft = $utilisateur->refresh_token_microsoft;
		$expiration_token_utilisateur_microsoft = $utilisateur->expiration_token_microsoft;

		// Vérifie si le token existe
		if (empty($refresh_token_utilisateur_microsoft) || empty($expiration_token_utilisateur_microsoft) || empty($access_token_utilisateur_microsoft))
			return 'erreur';

		// Vérifie si le token a expiré
		//Récupère l'heure actuelle + 5 minutes (pour permettre des petits écarts)
		$maintenant = time() + 300;

        // Le token est toujours valide, retournons le
		if($expiration_token_utilisateur_microsoft > $maintenant)
			return $access_token_utilisateur_microsoft;

		// Le token a expiré (ou est très proche de l'être) donc rafraîchissons le
		try {

			// Initialise le client OAuth
			$client_oauth = service('microsoft_authentification')->retourne_oauth_client();

			$nouveau_token = $client_oauth->getAccessToken('refresh_token', [

				'refresh_token' => $refresh_token_utilisateur_microsoft
			]);

			// Stocke les nouvelles valeurs
			$this->mise_memoire_tokens($nouveau_token, $id_utilisateur);
			return $nouveau_token->getToken();
		}
		catch (\Exception $e) {

			return false;
		}
	}

	/**
	 *
	 * Mets à jour les informations du token
	 *
	 */
	public function mise_a_jour_tokens($accessToken) {

		session([
			'microsoft.access_token' => $accessToken->getToken(),
			'microsoft.refresh_token' => $accessToken->getRefreshToken(),
			'microsoft.expiration_token' => $accessToken->getExpires()
		]);
	}
}
