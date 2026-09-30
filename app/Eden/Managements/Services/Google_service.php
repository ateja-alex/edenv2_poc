<?php

namespace App\Eden\Managements\Services;

use Google;

class Google_service {

	var $champs_fichiers_google_drive = 'id, size, thumbnailLink, name, webViewLink, mimeType';

	/*
	 *
	 * Renvoi les fichiers du Google Drive
	 *
	 */
	public function recupere_fichiers_drive($dossier = '', $uniquement_dossier = false) {

    	//define('STDIN',fopen("php://stdin","r"));

    	// On récupère le client Google
		$client = $this->getClient();

		// Si on n'est pas connecté, on renvoi une liste vide
		if($client === false) {

		    if($uniquement_dossier)
		    	return [];

			return [[], []];
		}

		// Appel au service
		$service = new \Google_Service_Drive($client);

		// On charge les paramètres
		$parametres = [
						'pageSize' => 100,
						'fields' => 'nextPageToken, files('.$this->champs_fichiers_google_drive.')',
					  ];

		// Si on a demandé de charger un dossier
		if(!empty($dossier))
			$parametres['q'] = "'".$dossier."' in parents";

		// Appel API
		$results = $service->files->listFiles($parametres);

		// On effectue quelques opérations sur la liste des fichiers récupérées
		$fichiers = [];
		$dossiers = [];
		foreach ($results->getFiles() as $modele) {

			$fichier = $this->prepare_fichier_pour_bibliotheque($modele);


			if($modele->mimeType == 'application/vnd.google-apps.folder') {

				$dossiers[] = $fichier;
			} else {

				$fichiers[] = $fichier;
			}


			/*
				  +iconLink: "https://drive-thirdparty.googleusercontent.com/16/type/application/vnd.google-apps.spreadsheet"
				  +mimeType: "application/vnd.google-apps.spreadsheet"
				  +thumbnailLink: "https://docs.google.com/feeds/vt?gd=true&id=1Djtq7YItOxEom8jXDgOpD3SqLCKDrfrHYIs5OBF_z7U&v=70&s=AMedNnoAAAAAX9ozOJIvNMX1PR_Ta0lyXnAnvW86BFrv&sz=s220"
				  +webViewLink: "https://docs.google.com/spreadsheets/d/1Djtq7YItOxEom8jXDgOpD3SqLCKDrfrHYIs5OBF_z7U/edit?usp=drivesdk"
	        		
	        	echo '<a href="'.$file->webViewLink.'">'.$file->getName().'</a><br>';
			*/
	    }

	    if($uniquement_dossier)
	    	return $dossiers;

	    return [$fichiers, $dossiers];
	}


	/*
	 *
	 * Upload un fichier sur Google Drive
	 *
	 */
	public function upload_fichier_drive($nom_fichier, $chemin_fichier, $repertoire = false) {

		$client = $this->getClient();

		// Si on n'a pas passé de répertoire en paramètre, on spécifie le répertoire racine (via les parametres)
		$repertoire = (!$repertoire ? parametre('synchro_gdrive_dossier_racine') : $repertoire );

		// Si on n'est pas connecté, on renvoi false
		if($client === false)
			return false;

		// Si le fichier est introuvable
		if(!is_file($chemin_fichier))
			return false;

		// Appel au service
		$service 	= new \Google_Service_Drive($client);
		$meta_data 	= new \Google_Service_Drive_DriveFile(['name' => $nom_fichier]);
		$meta_data->setParents([$repertoire]);

		$fichier = $service->files->create($meta_data, [
															'data' 		=> file_get_contents($chemin_fichier),
															'mimeType' 	=> mime_content_type($chemin_fichier),
															'fields'	=> $this->champs_fichiers_google_drive,
														]);

		return $this->prepare_fichier_pour_bibliotheque($fichier);
	}

	/*
	 *
	 * Crée un dossier sur Google Drive
	 *
	 */
	public function cree_dossier_drive($nom_dossier, $repertoire = false) {
		$client = $this->getClient();

		// Si on n'a pas passé de répertoire en paramètre, on spécifie le répertoire racine (via les parametres)
		$repertoire = (!$repertoire ? parametre('synchro_gdrive_dossier_racine') : $repertoire );

		// Si on n'est pas connecté, on renvoi false
		if($client === false)
			return false;

		// Appel au service
		$service 	= new \Google_Service_Drive($client);
		$meta_data 	= new \Google_Service_Drive_DriveFile(['name' => $nom_dossier, 'mimeType' 	=> 'application/vnd.google-apps.folder']);
		$meta_data->setParents([$repertoire]);

		$dossier = $service->files->create($meta_data);

		return $this->prepare_fichier_pour_bibliotheque($dossier);
	}

	/*
	 *
	 * Supprime un dossier sur Google Drive
	 *
	 */
	public function supprime_drive($id_dossier) {

		$client = $this->getClient();

		// Si on n'est pas connecté, on renvoi false
		if($client === false)
			return false;

		// Appel au service
		$service = new \Google_Service_Drive($client);
		$service->files->delete($id_dossier);

		return true;
	}

	/*
	 *
	 * Supprime un dossier sur Google Drive
	 *
	 */
	public function deplace_drive($id_fichier, $id_dossier_parent, $id_dossier_nouveau) {

		$client = $this->getClient();

		// Si on n'est pas connecté, on renvoi false
		if($client === false)
			return false;

		$body = new \Google_Service_Drive_DriveFile;

		// Appel au service
		$service = new \Google_Service_Drive($client);
		$service->files->update($id_fichier, $body, ['removeParents' => $id_dossier_parent, 'addParents' => $id_dossier_nouveau, 'enforceSingleParent' => true ]);

		return true;
	}


	/*
	 *
	 * Renvoi le fichier pour affichage dans la bibliothèque
	 *
	 */
	private function prepare_fichier_pour_bibliotheque($modele) {

		$fichier = (object)(['modele' => $modele]);
		$fichier->nom = $modele->name;
		$fichier->nom_original = $modele->name;
		$fichier->poids = $modele->size;
		$fichier->gdrive = true;
		$fichier->dimensions = 0;
		$fichier->id = $modele->id;
		$fichier->chemin = $modele->webViewLink;
		$fichier->miniature = $modele->thumbnailLink;

	    if (empty($fichier->poids)) {
	    	$fichier->poids = '-';

	    } elseif ($fichier->poids > 1000000) {
	        $fichier->poids = round($fichier->poids / 1000000, 2) . ' Mo';

	    } elseif ($fichier->poids > 1000) {
	        $fichier->poids = round($fichier->poids / 1000) . ' Ko';

	    } else {
	        $fichier->poids = round($fichier->poids) . ' Octets';

	    }

	    $fichier->image = false;
	    /* TODO
	    if (in_array(strtolower($fichier->type), array('png', 'bmp', 'jpg', 'gif', 'jpeg'))) {

	        $fichier->image = true;
        }
        */

        return $fichier;
	}

	/*
	 *
	 * Renvoi le client Google (Drive)
	 *
	 */
	public function getClient() {

		try{
			$client = new \Google_Client();
			$client->setApplicationName('Synchronisation Eden');
			$client->setScopes(\Google_Service_Drive::DRIVE);
			$client->setAccessType('offline');
			$client->setRedirectUri(route('parametrage.gdrive.index'));
			$client->setPrompt('select_account consent');

			if(file_exists(storage_path('app/credentials.json'))) {
				$client->setAuthConfig(storage_path('app/credentials.json'));
			}

			$chemin_token = storage_path('app/token-google-drive.json');
			if (file_exists($chemin_token)) {
				$accessToken = json_decode(file_get_contents($chemin_token), true);
				$client->setAccessToken($accessToken);
			}


			// If there is no previous token or it's expired.
			if ($client->isAccessTokenExpired()) {

				// Refresh the token if possible, else fetch a new one.
				if ($client->getRefreshToken()) {

					$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

				} else {

					if(isset(request()->code)) {

						$authCode = request()->code;

						// Exchange authorization code for an access token.
						$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
						$client->setAccessToken($accessToken);


						// Check to see if there was an error.
						if (array_key_exists($accessToken["error"])) {
							return false;
						}

						
						// Save the token to a file.
						if (!file_exists(dirname($chemin_token))) {
							mkdir(dirname($chemin_token), 0700, true);
						}
						file_put_contents($chemin_token, json_encode($client->getAccessToken()));

					} else {
						
						/*
						// Request authorization from the user.
						$authUrl = $client->createAuthUrl();

						printf("<a href=\"%s\">Cliquez ici</a><br>", $authUrl);
						die();
						//print 'Enter verification code: ';
						*/

						return false;
					}
				}
			}

			return $client;

		}
		catch(\Exception | \Throwable $e){
			return false;
		}
    }

}
