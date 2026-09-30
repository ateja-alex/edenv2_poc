<?php

namespace App\Eden\Managements\Services;

class Google_token_service {
	
	/**
	 * 
	 * Mets en mémoire les tokens pour pouvoir les appeler plus tard
	 * 
	 */
	public function mise_memoire_tokens($access_token, $id_utilisateur_eden) {
		
		$management_utilisateur_google = management('utilisateur', $id_utilisateur_eden);
		$management_utilisateur_google->enregistre_modele(array('refresh_token_google' => $access_token['refresh_token']));
		$management_utilisateur_google->enregistre_modele(array('access_token_google' => $access_token['access_token']));
		session([
			'google.access_token' => $access_token['access_token'],
			'google.refresh_token' => $access_token['refresh_token'],
			'google.token' => $access_token,

		]);
	}
	
	/**
	 * 
	 * Efface les tokens 
	 * 
	 */
	public function efface_tokens() {
		  
		session()->forget('google.access_token');
		session()->forget('google.refresh_token');
	}
	
	/**
	 * 
	 * Retourne l'access token et le met à jour s'il est expiré
	 * 
	 */
	public function recupere_access_token() {
		
		// Vérifie si le token existe
		if (empty(session('google.access_token')) || empty(session('google.refresh_token'))) {
			
			$retour = $this->recupere_access_token_base();
			
			// traiter les différents cas en fonction du retour
			return $retour;
		}
		
		$google_client = service('google_authentification') -> instancie_google_client();
		
		$google_client -> setAccessToken(session('google.token'));
		
		// S'il n'y a pas de token ou s'il est expiré on le demande/rafraîchit
			if ($google_client -> isAccessTokenExpired()) {

				// Rafraîchit le token si c'est possible sinon on en demande un nouveau
				if ($google_client -> getRefreshToken()) {

					$google_client -> fetchAccessTokenWithRefreshToken($google_client -> getRefreshToken());

				} else {

					if(isset(request() -> code)) {

						$code_authentification = request() -> code;

						// Échange du code d'authentification avec le serveur pour pouvoir récupèrer le token
						$access_token = $google_client -> fetchAccessTokenWithAuthCode($code_authentification);
						$google_client->setAccessToken($access_token);


						// On vérifie s'il y a une erreur
						if (isset($access_token['error'])) {
							
							return 'erreur';
						}

						$token = service('google_token');
						$token -> mise_memoire_tokens($access_token, session('utilisateur_eden')->id);
					} else {
						
						// Demande l'autorisation à l'utilisateur
						$authUrl = $google_client->createAuthUrl();

						printf("<a href=\"%s\">Cliquez ici</a><br>", $authUrl);
						die();
					}
				}
			}
			
			return $google_client;
	}
	
	
	/**
	 * 
	 * Retourne l'access token et le met à jour s'il est expiré
	 * 
	 */
	public function recupere_access_token_base() {
		
		$id_utilisateur = session('utilisateur_eden')->id;
		$access_token_utilisateur_google = modele('utilisateur', $id_utilisateur)->access_token_google;
		$refresh_token_utilisateur_google = modele('utilisateur', $id_utilisateur)->refresh_token_google;
		
		// Vérifie si le token existe
		if (empty($refresh_token_utilisateur_google) || empty($access_token_utilisateur_google)) {
			
			return 'erreur';
		}
		
		$google_client = service('google_authentification') -> instancie_google_client();
		
		$google_client -> setAccessToken($access_token_utilisateur_google);
		
		// S'il n'y a pas de token ou s'il est expiré on le demande/rafraîchit
		if ($google_client -> isAccessTokenExpired()) {

			// Rafraîchit le token si c'est possible sinon on en demande un nouveau
			if ($google_client -> getRefreshToken()) {

				$google_client -> fetchAccessTokenWithRefreshToken($google_client -> getRefreshToken());

			} else {

				if(isset(request() -> code)) {

					$code_authentification = request() -> code;

					// Échange du code d'authentification avec le serveur pour pouvoir récupèrer le token
					$access_token = $google_client -> fetchAccessTokenWithAuthCode($code_authentification);
					$google_client->setAccessToken($access_token);


					// On vérifie s'il y a une erreur
					if (isset($access_token["error"])) {
						
						return 'erreur';
					}

					$token = service('google_token');
					$token -> mise_memoire_tokens($access_token, session('utilisateur_eden')->id);
				} else {
					
					// Demande l'autorisation à l'utilisateur
					$authUrl = $google_client->createAuthUrl();

					printf("<a href=\"%s\">Cliquez ici</a><br>", $authUrl);
					die();
				}
			}
		}
		
		return $google_client;
	}
}