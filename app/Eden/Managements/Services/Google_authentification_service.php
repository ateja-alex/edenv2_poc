<?php

namespace App\Eden\Managements\Services;

use Google;

class Google_authentification_service {
	
	/*
	 *
	 * Instancie le client Google avec le paramétrage déjà fait
	 *
	 */
	public function instancie_google_client() {

		try{
			$config_auth_google = $this->retourne_config_auth_google();
			
			// Création du client Google et initialisation de ses paramètres
			$client = new \Google_Client();
			$client->setApplicationName('Eden-PME');
			$client->setScopes(['https://www.googleapis.com/auth/calendar.events', 'https://www.googleapis.com/auth/gmail.metadata','https://www.googleapis.com/auth/userinfo.email','openid','https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/calendar.calendarlist.readonly']);

			$client->setAccessType('offline');
			$client->setRedirectUri(fonctionnalite('google_redirect_uri'));
			$client->setPrompt('select_account consent');
			$client->setAuthConfig($config_auth_google);
					
			return $client;
		}
		catch(\Exception | \Throwable $e){
			return null;
		}
	}
	
	/*
	 *
	 * Instancie le client Google avec le paramétrage déjà fait
	 *
	 */
	public function instancie_google_client_application() {

		try{
			$config_auth_google_application = $this->retourne_config_auth_google_application();

			// Création du client Google et initialisation de ses paramètres
			$client = new \Google_Client();
			$client->setApplicationName('Eden-PME');
			$client->setScopes(['https://www.googleapis.com/auth/calendar.events', 'https://www.googleapis.com/auth/gmail.metadata','https://www.googleapis.com/auth/userinfo.email','openid','https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/calendar.calendarlist.readonly']);
			$client->setAccessType('offline');
			$client->setAuthConfig($config_auth_google_application);
			$client->useApplicationDefaultCredentials();
			return $client;
		}
		catch(\Exception | \Throwable $e){
			return null;
		}
	}

	/*
	 *
	 * Prépare la configuration d'authentification pour le client Google
	 *
	 */
	public function retourne_config_auth_google() {

		$config_auth_google = ['web' => [
		
			'client_id' => fonctionnalite('google_app_id'),
			'project_id' => fonctionnalite('google_projet_id'),
			'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
			'token_uri' => 'https://oauth2.googleapis.com/token',
			'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
			'client_secret' => fonctionnalite('google_app_secret'),
		]];
			
		return $config_auth_google;
	}	
	
	/*
	 *
	 * Prépare la configuration d'authentification pour le client Google du compte de service
	 *
	 */
	public function retourne_config_auth_google_application() {

        $config_auth_google = [

            "type" => "service_account",
            "project_id" => fonctionnalite('google_projet_id'),
            "private_key_id" => fonctionnalite('google_id_cle_privee'),
            "private_key" => fonctionnalite('google_cle_privee'),
            "client_email" => fonctionnalite('google_compte_service_email'),
            "client_id" => fonctionnalite('google_compte_service_id_client'),
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
        ];
			
		return $config_auth_google;
	}
}