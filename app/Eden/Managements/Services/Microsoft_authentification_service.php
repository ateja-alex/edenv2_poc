<?php

namespace App\Eden\Managements\Services;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class Microsoft_authentification_service {

	/**
	 *
	 * On Vérifie la validité de la condition
	 *
	 */
	public function retourne_oauth_client() {
        
        return new \League\OAuth2\Client\Provider\GenericProvider([
		
			'clientId'                => config('fonctionnalites_integrations.microsoft_app_id'),
			'clientSecret'            => config('fonctionnalites_integrations.microsoft_app_secret'),
			'redirectUri'             => config('fonctionnalites_integrations.microsoft_redirect_uri'),
			'urlAuthorize'            => config('azure.authority'). fonctionnalite('microsoft_id_active_directory') . config('azure.authorizeEndpoint'),
			'urlAccessToken'          => config('azure.authority'). fonctionnalite('microsoft_id_active_directory') .config('azure.tokenEndpoint'),
			'urlResourceOwnerDetails' => '',
			'scopes'                  => config('azure.scopes')
		]);
	}

	/**
	 *
	 * Instancie Graph à partir du profil de l'utilisateur
	 *
	 */
	public function instancie_graph($id_utilisateur = false, $utilisateur = null) {
		
		// Récupère l'access token
		$token = service('microsoft_token');

        if(!empty($id_utilisateur))
		    $access_token = $token->recupere_access_token_base($id_utilisateur);
        else
            $access_token = $token->recupere_access_token();

		// si $access_token est false, c'est le cas ou on n'a pas pu récupérer le token
		if($access_token === false || $access_token === 'erreur')
			return false;

		// Crée un client Graph
		$graph = new Graph();
		$graph->setAccessToken($access_token);
		
		return $graph;
	}
	
	/**
	 *
	 * Instancie Graph à partir du profil de l'application
	 *
	 */
	public function instancie_graph_application(){
		
		// Initialisation des variables relatives à l'application nécessaires à la requête de demande de jeton d'accès
		$app_id = config('fonctionnalites_integrations.microsoft_app_id');
		$app_secret = config('fonctionnalites_integrations.microsoft_app_secret');
		
		$id_active_directory = config('fonctionnalites_integrations.microsoft_id_active_directory');

        if(empty($app_id) || empty($app_secret) || empty($id_active_directory))
            return false;

		// Initialisation de la variable qui sera utilisée pour le corps de la requête
		$params = 'grant_type=client_credentials&client_id='. $app_id .'&scope=https%3A%2F%2Fgraph.microsoft.com%2F.default&client_secret=' . $app_secret;
		
		// Requête pour demande le jeton d'accès
		$requete_access_token = curl_init();

		curl_setopt_array($requete_access_token, [
			CURLOPT_URL => "https://login.microsoftonline.com/".$id_active_directory."/oauth2/v2.0/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
			CURLOPT_POSTFIELDS => $params
		]);

		$reponse_requete_access_token = curl_exec($requete_access_token);
		$erreur_requete_access_token = curl_error($requete_access_token);

		curl_close($requete_access_token);

		// On décode la chaîne de caractère écrite au format json pour pouvoir l'exploiter et récupérer la valeur de l'access token
		$reponse_formatee_access_token = json_decode($reponse_requete_access_token, true);

        if(array_key_exists('access_token', $reponse_formatee_access_token))
		    $access_token = $reponse_formatee_access_token['access_token'];
        else
            return false;
		
		$graph = new Graph();
		$graph->setAccessToken($access_token);
		
		return $graph;
	}

	/**
	 *
	 *
	 *
	 */
	public function verifie_connexion_utilisateur_microsoft(){
		
		if(!empty(moi()) && !empty(moi()->id_microsoft)){
			return true;
		} else {
			return false;
		}
	}
}
