<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Arr;

use App\Eden\Librairies\Ekyna\Component\Dpd\Exception;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint;
use App\Eden\Librairies\Ekyna\Component\Dpd\EPrint\Enum\ELabelType;

class Transporteur_service {

	/*
	 *
	 * Expédie le colis via DPD
	 *
	 */
	public function expedie_via_dpd($reference, $destinataire, $type_element = '') {
		$cache = config('services.dpd.cache');
		$debug = config('services.dpd.debug');

		$ePrintConfig = [
		    'login'    => config('services.dpd.login'),
		    'password' => config('services.dpd.password'),
		    'cache'    => config('services.dpd.cache'),
		    'debug'    => config('services.dpd.debug'),
		    'test'     => config('services.dpd.debug'),
		];

		$classicNumber = config('services.dpd.predictNumber');
		$predictNumber = config('services.dpd.predictNumber');

		$centerNumber  = config('services.dpd.centerNumber');
		$countryCode   = config('services.dpd.countryCode');

		$usePredict = true;


		// TODO
		$commentaire_expedition = 'Commentaire expédition';
		$complement_adresse = 'Complément adresse';

		$poids = (!empty($commande->modele->poids_manuel) ? $commande->modele->poids_manuel : $commande->modele->poids_total);
		$extraInsurance = '100';









		//$reference_2 = 'EDEN001-2';
		//$reference_3 = 'EDEN001-3';

		$date_envoi = date('d/m/Y');

		//$relayPoint = "P50124";

		$api = new Eprint\Api($ePrintConfig);

		// Shipment request
		$request = new EPrint\Request\StdShipmentLabelRequest();
		$request->customer_centernumber = $centerNumber;
		$request->customer_countrycode = $countryCode;

		$request->services = new EPrint\Model\StdServices();


		//if ($usePredict) {
		    // Predict
		    $request->customer_number = $predictNumber;

		    // Predict contact
		    $request->services->contact = new EPrint\Model\Contact();
		    $request->services->contact->type = EPrint\Enum\ETypeContact::PREDICT;
		    $request->services->contact->sms = $destinataire['telephone'];
		/*
		} else {
		    // Classic
		    $request->customer_number = $classicNumber;
		}
		*/

		// (Optional) Label type: PNG, PDF, PDF_A6
		$request->labelType = new EPrint\Model\LabelType();
		$request->labelType->type = $type = EPrint\Enum\ELabelType::PDF_A6;

		// Receiver address
		$request->receiveraddress = new EPrint\Model\Address();
		$request->receiveraddress->name = $destinataire['nom'];
		$request->receiveraddress->countryPrefix = $destinataire['pays'];
		$request->receiveraddress->zipCode = $destinataire['code_postal'];
		$request->receiveraddress->city = $destinataire['ville'];
		$request->receiveraddress->street = $destinataire['adresse'];
		$request->receiveraddress->phoneNumber = $destinataire['telephone'];

		// (Optional) Receiver address optional info
		$request->receiverinfo = new EPrint\Model\AddressInfo();
		$request->receiverinfo->vinfo1 = $complement_adresse;

		// Shipper address
		$request->shipperaddress = new EPrint\Model\Address();
		$request->shipperaddress->name = config('services.dpd.nom');
		$request->shipperaddress->countryPrefix = config('services.dpd.pays');
		$request->shipperaddress->zipCode = config('services.dpd.code_postal');
		$request->shipperaddress->city = config('services.dpd.ville');
		$request->shipperaddress->street = config('services.dpd.adresse');
		$request->shipperaddress->phoneNumber = config('services.dpd.telephone');

		// Shipment weight
		$request->weight = $poids; // kg

		// (Optional) Theoretical shipment date ('d/m/Y' or 'd.m.Y')
		$request->shippingdate = $date_envoi;

		// (Optional) Insurance
		$request->services->extraInsurance = new EPrint\Model\ExtraInsurance();
		$request->services->extraInsurance->type = EPrint\Enum\ETypeInsurance::BY_SHIPMENTS; // Always use this const
		$request->services->extraInsurance->value = $extraInsurance; // 22k euros max

		// (Optional) References and comment
		$request->referencenumber = $reference;
		//$request->reference2 = $reference_2;
		//$request->reference3 = $reference_3;
		$request->customLabelText = $commentaire_expedition;

		/*
		// Relay point
		if (!$usePredict && !empty($relayPoint)) {
		    $request->services = new EPrint\Model\StdServices();
		    $request->services->parcelshop = new EPrint\Model\ParcelShop();
		    $request->services->parcelshop->shopaddress = new EPrint\Model\ShopAddress();
		    $request->services->parcelshop->shopaddress->shopid = $relayPoint;
		}*/

		/* ---------------- Get response ---------------- */

		// Use API helper
		try {
		    /** @var \Ekyna\Component\Dpd\EPrint\Response\CreateShipmentWithLabelsResponse $response */
		    $response = $api->CreateShipmentWithLabels($request);
		} catch (Exception\ExceptionInterface $e) {
		    echo "Error: " . $e->getMessage();
		    if ($debug && $e instanceof Exception\ClientException) {
		        echo "\nRequest:\n" . $e->request;
		        echo "\nResponse:\n" . $e->response;
		    }
		    exit();
		}
		//echo get_class($response) . "\n";


		// Get result model
		/** @var \Ekyna\Component\Dpd\EPrint\Model\ShipmentsWithLabels $result */
		$result = $response->CreateShipmentWithLabelsResult;
		//echo get_class($result) . "\n";

		// Get shipments
		//$idx = 1;
		/** @var \Ekyna\Component\Dpd\EPrint\Model\Shipment $shipment */

		$trackingsURL = [];
		foreach ($result->shipments as $shipment) {

		    $trackingsURL[] = $shipment->getTrackingUrl();
		}

		// Création auto du répertoire
		/*
		$labelDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
		if (!is_dir($labelDir)) {
		    mkdir($labelDir);
		}
		*/

		$PDFs = [];
		$idx = 0;
		foreach ($result->labels as $label) {

		    $idx++;
	        $nom_fichier = 'reference_'.$reference.'-'.$idx.'.pdf';

	        \Storage::put('dpd/'.$nom_fichier, $label->label);
	        $PDFs[] = ['type' => $label->type, 'fichier' => $nom_fichier];
		}

		$this->enregistre('dpd', $reference, $trackingsURL, $PDFs, $type_element);
	}

	/*
	 *
	 * Expédie plusieurs colis via DPD
	 *
	 */
	public function expedie_multiple_via_dpd($reference, $destinataire, $les_colis, $type_element = '') {

		$cache = config('services.dpd.cache');
		$debug = config('services.dpd.debug');

		$ePrintConfig = [
		    'login'    => config('services.dpd.login'),
		    'password' => config('services.dpd.password'),
		    'cache'    => config('services.dpd.cache'),
		    'debug'    => config('services.dpd.debug'),
		    'test'     => config('services.dpd.debug'),
		];

		$classicNumber = config('services.dpd.predictNumber');
		$predictNumber = config('services.dpd.predictNumber');

		$centerNumber  = config('services.dpd.centerNumber');
		$countryCode   = config('services.dpd.countryCode');

		// Shipment request
		$request = new EPrint\Request\MultiShipmentRequest();
		$request->customer_centernumber = $centerNumber;
		$request->customer_countrycode = $countryCode;
		$request->customer_number = $classicNumber;
		$request->shippingdate = date('d/m/Y');

		$api = new Eprint\Api($ePrintConfig);

		// Receiver address
		$request->receiveraddress = new EPrint\Model\Address();
		$request->receiveraddress->name = $destinataire['nom'];
		$request->receiveraddress->countryPrefix = $destinataire['pays'];
		$request->receiveraddress->zipCode = $destinataire['code_postal'];
		$request->receiveraddress->city = $destinataire['ville'];
		$request->receiveraddress->street = $destinataire['adresse'];
		$request->receiveraddress->phoneNumber = $destinataire['telephone'];

		// Receiver address optional info
		$request->receiverinfo = new EPrint\Model\AddressInfo();
		$request->receiverinfo->vinfo1 = $destinataire['adresse_complement'];
		
		$request->customLabelText = $destinataire['complement_adresse'];

		// Shipper address
		$request->shipperaddress = new EPrint\Model\Address();
		$request->shipperaddress->name = config('services.dpd.nom');
		$request->shipperaddress->countryPrefix = config('services.dpd.pays');
		$request->shipperaddress->zipCode = config('services.dpd.code_postal');
		$request->shipperaddress->city = config('services.dpd.ville');
		$request->shipperaddress->street = config('services.dpd.adresse');
		$request->shipperaddress->phoneNumber = config('services.dpd.telephone');

		// On paramètre les multiples colis
		foreach($les_colis as $colis) {

			$slave = new EPrint\Model\SlaveRequest();
			$slave->weight = $colis['poids']; // en kg
			$slave->referencenumber = $colis['reference'];
			$request->addSlave($slave);
		}

		// Appel API
		try {
		    $response = $api->CreateMultiShipment($request);
		} catch (Exception\ExceptionInterface $e) {
		    return "Error DPD : " . $e->getMessage().'<br><br> Expédition à '.$destinataire['adresse'].', '.$destinataire['code_postal'].', '.
$destinataire['ville'].', '.
$destinataire['pays'];
		    
		    if ($debug && $e instanceof Exception\ClientException) {
		        echo "\nRequest:\n" . $e->request;
		        echo "\nResponse:\n" . $e->response;
		    }
		    exit();
		}

		$result = $response->CreateMultiShipmentResult;

		// On génère les URL de tracking
		$trackingsURL = [];
		foreach ($result->shipments as $shipment) {

		    $trackingsURL[] = $shipment->getTrackingUrl();
		}

		// On récupère les PDF d'étiquettes
		$shipements = (array)$result->shipments;
		$PDFs = [];
		foreach ($shipements as $i => $shipement) {

			$shipement = Arr::first((array)$shipement);
			$PDFs = array_merge($PDFs, $this->dpd_recupere_label($shipement['parcelnumber'], $reference.'-'.$i));
		}

		$this->enregistre('dpd', $reference, $trackingsURL, $PDFs, $type_element);
		
		return true;
	}


	/**
	 *
	 * Récupère le PDF d'un colis
	 *
	 * Prends en paramètre un parcelnumber et un texte de référence (par colis)
	 *
	 * Retourne un tableau de fichiers
	 *
	 */
	private function dpd_recupere_label($parcelnumber, $reference) {

		// Config
		$cache = config('services.dpd.cache');
		$debug = config('services.dpd.debug');

		$ePrintConfig = [
		    'login'    => config('services.dpd.login'),
		    'password' => config('services.dpd.password'),
		    'cache'    => config('services.dpd.cache'),
		    'debug'    => config('services.dpd.debug'),
		    'test'     => config('services.dpd.debug'),
		];

		$request = new EPrint\Request\ReceiveLabelRequest();
		$request->parcelnumber = $parcelnumber;
		$request->countrycode = config('services.dpd.countryCode');
		$request->labelType = new EPrint\Model\LabelType();
		$request->labelType->type = $type = EPrint\Enum\ELabelType::PDF_A6;
		$request->centernumber = config('services.dpd.centerNumber');

		// Appel API
		$api = new EPrint\Api($ePrintConfig);
		try {
		    $response = $api->GetLabel($request);
		} catch (Exception\ExceptionInterface $e) {
		    echo "Error: " . $e->getMessage();
		    if ($debug && $e instanceof Exception\ClientException) {
		        echo "\nRequest:\n" . $e->request;
		        echo "\nResponse:\n" . $e->response;
		    }
		    exit();
		}

		// Récupère le résultat
		$result = $response->GetLabelResult;
		$retour = [];
		foreach ($result->labels as $idx => $label) {

		    $nom_fichier = 'reference_'.$reference.'-'.$idx.'.pdf';
	        \Storage::put('dpd/'.$nom_fichier, $label->label);

		    $retour[] = $nom_fichier;
		}

		return $retour;
	}


	/*
	 *
	 * Expédie plusieurs colis via Geodis / France Express
	 *
	 */
	public function expedie_multiple_via_geodis($reference, $destinataire, $les_colis, $type_element = '') {

		$optionLivraison 				= 'RDW';		// Marc ?

		$contreRemboursementQuantite 	= $destinataire['contre_remboursement'];
		$contreRemboursementCodeUnite 	= 'EUR';		// ???

		$codeIncotermConditionLivraison = 'P';			// ???

		//$volumeTotal 					= '0.45';		// ???


		$instructionEnlevement 			= 'Entree fournisseurs';

		$service 						= 'api/wsclient/enregistrement-envois';
		$lang 							= 'fr';

		$codeSa 						= $destinataire['code_sa'];
		$codeProduit 					= $destinataire['code_produit'];
		$codeClient						= $destinataire['code_client'];

		$login 							= config('services.geodis.login');
		$clef_api 						= config('services.geodis.cle_api');
		$uri 							= config('services.geodis.url');

		$reference1 					= $reference;
		$reference2 					= '';

		$poidsTotal 					= collect($les_colis)->sum('poids');
		$emailNotificationDestinataire 	= $destinataire['email'];
		$smsNotificationDestinataire 	= $destinataire['telMobile'];

		$colis_pour_geodis = [];
		foreach ($les_colis as $coli) {
			
			$colis_pour_geodis[] = [
								'palette' => false,					// Marc ?
								'quantite' => 1						// ???
							];
		}

		$indicatifMobile = '';
		if(substr($destinataire['telMobile'], 0, 1) == '+')
			$indicatifMobile = substr($destinataire['telMobile'],0,3);

		if(substr($destinataire['telMobile'], 0, 2) == '00')
			$indicatifMobile = '+'.substr($destinataire['telMobile'],1,3);


		$body = [
			'impressionEtiquette' => true,
			'typeImpressionEtiquette' => 'P',	//P
			'formatEtiquette' => '2',			// 2
			'validationEnvoi' => false,			// true
			'suppressionSiEchecValidation' => false,
			'impressionBordereau' => true,		// false
			'impressionRecapitulatif' => true,	// false
			'listEnvois' => [

					[
						'noRecepisse' => '',
						'noSuivi' => '',
						'horsSite' => false,
						'codeSa' => $codeSa,
						'codeClient' => $codeClient,
						'codeProduit' => $codeProduit,
						'reference1' => $reference1,
						'reference2' => $reference2,
						'expediteur' => [

							'nom' 			=> config('services.geodis.nom'),
							'adresse1' 		=> config('services.geodis.adresse1'),
							'adresse2' 		=> config('services.geodis.adresse2'),
							'codePostal' 	=> config('services.geodis.codePostal'),
							'ville' 		=> config('services.geodis.ville'),
							'codePays' 		=> config('services.geodis.codePays'),
							'nomContact' 	=> config('services.geodis.nomContact'),
							'email' 		=> config('services.geodis.email'),
							'telFixe' 		=> config('services.geodis.telFixe'),
							'indTelMobile' 	=> config('services.geodis.indTelMobile'),
							'telMobile' 	=> config('services.geodis.telMobile'),
							'codePorte' 	=> config('services.geodis.codePorte'),
						],

						'dateDepartEnlevement' => date('Y-m-d'),
						'instructionEnlevement' => $instructionEnlevement,
						'destinataire' => [

							'nom' 			=> $destinataire['nom'],
							'adresse1' 		=> $destinataire['adresse1'],
							'adresse2' 		=> $destinataire['adresse2'],
							'codePostal' 	=> $destinataire['codePostal'],
							'ville' 		=> $destinataire['ville'],
							'codePays' 		=> $destinataire['codePays'],
							'nomContact' 	=> $destinataire['nomContact'],
							'email' 		=> $destinataire['email'],
							'telFixe' 		=> str_replace(['+33', ' '], ['0', ''], $destinataire['telFixe']),
							'indTelMobile' 	=> $indicatifMobile,
							'telMobile' 	=> str_replace(['+33', ' '], ['0', ''], $destinataire['telMobile']),
							'codePorte' 	=> $destinataire['codePorte'],
						],

						'listUmgs' => $colis_pour_geodis,

						'poidsTotal' => $poidsTotal,
						/*'volumeTotal' => volumeTotal,				// Supprimer*/
						'optionLivraison' => $optionLivraison,
						'instructionLivraison' => $destinataire['commentaires_livraison'],
						'contreRemboursement' => [

								'quantite' => $contreRemboursementQuantite,
								'codeUnite' => $contreRemboursementCodeUnite,

						],

						'codeIncotermConditionLivraison' => $codeIncotermConditionLivraison,
						'emailNotificationDestinataire' => $emailNotificationDestinataire,
						'smsNotificationDestinataire' => $smsNotificationDestinataire,
					]
					
			]
		];

		// Execution de la requete
		$inlineBody = json_encode($body);
		$timestamp = intval(microtime(true)*1000);

		$hash = hash('sha256', $clef_api.';'.$login.';'.$timestamp.';'.$lang.';'.$service.';'.$inlineBody);

		$headers = array(
				'X-GEODIS-Service: '.$login.';'.$timestamp.';'.$lang.';'.$hash,
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: '.strlen($inlineBody),
		);

		$fw = fopen('temp-geodis-xgfhf', 'w');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri.$service);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $inlineBody);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_STDERR, $fw);

		//debug
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		$rawResult = curl_exec($ch);
		if (curl_error($ch)) {
				$error_msg = curl_error($ch);
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$curl_errno= curl_errno($ch);
				dump ('Error Message : '.$error_msg) ;
				dump ('Statut http : '.$http_status);
				dump ('Numéro d\'erreur :'.$curl_errno);

				// NE PAS LAISSER EN PROD
				dd($uri, $headers, $error_msg);
		}

		fclose($fw);
		$retour = json_decode($rawResult, true);

		//dump($retour, $rawResult, $body);

		// On gère les erreurs
		if(!is_array($retour))
			return 'Impossible de traiter le retour Geodis';

		if($retour['ok'] != '1')
			return $retour['texteErreur'];

		foreach($retour['contenu']['listRetoursEnvois'] as $retourEnvoi) {

			if(isset($retourEnvoi['msgErreurEnregistrement']) && isset($retourEnvoi['msgErreurEnregistrement']['texte']))
				return $retourEnvoi['msgErreurEnregistrement']['texte'];
		}

		$trackingsURL = [];
		$PDFs = [];

		// On stock les fichiers retournés
		foreach($retour['contenu']['listRetoursEnvois'] as $retourEnvoi) {

			if(isset($retourEnvoi['docEtiquette']) && isset($retourEnvoi['docEtiquette']['nom']))
				\Storage::put('geodis/'.$retourEnvoi['docEtiquette']['nom'], base64_decode($retourEnvoi['docEtiquette']['contenu']));

			if(isset($retourEnvoi['docBordereau']) && isset($retourEnvoi['docBordereau']['nom']))
				\Storage::put('geodis/'.$retourEnvoi['docBordereau']['nom'], base64_decode($retourEnvoi['docBordereau']['contenu']));

			$trackingsURL[] = $retourEnvoi['urlSuiviDestinataire'];

			if(isset($retourEnvoi['docEtiquette']) && isset($retourEnvoi['docEtiquette']['nom']))
				$PDFs[] 		= $retourEnvoi['docEtiquette']['nom'];

			if(isset($retourEnvoi['docBordereau']) && isset($retourEnvoi['docBordereau']['nom']))
				$PDFs[] 		= $retourEnvoi['docBordereau']['nom'];

		}

		$this->enregistre('geodis', $reference, $trackingsURL, $PDFs, $type_element);

		return true;
	}


	/**
	 *
	 * Expédie le colis via DPD
	 *
	 */
	public function expedie_commandes_via_mazet($commandes) {

		$csv = [];

		$commandes = modele('commande_vente')->whereIn('id', $commandes)->get();

		// Liste des codes INSEE sur 2 caractères. A compléter au besoin.
		// cf https://www.insee.fr/fr/metadonnees/cog/pays/PAYS99137-luxembourg
		$pays = [
					'france' => 'FR',
					'belgique' => 'BE',
					'luxembourg' => 'LU',
				];

		// Définitions des tailles max des champs
		$tailles_max = [
						1 => 25,
						2 => 8,
						3 => 8,
						4 => 17,
						5 => 35,
						6 => 35,
						7 => 35,
						8 => 9,
						9 => 35,
						10 => 2,
						11 => 35,
						12 => 15,
						13 => 70,
						14 => 70,
						15 => 3,
						16 => 3,
						17 => 3,
						18 => 7,
						19 => 8,
						20 => 7,
						21 => 7,
						22 => 26,
						23 => 8,
						24 => 7,
						25 => 10,
						26 => 7,
						27 => 5,
						28 => 5,
						29 => 7,
						30 => 5,
						31 => 3,
						32 => 10,
						33 => 3,
						34 => 35,
						35 => 10,
						36 => 1,
						37 => 1,
						38 => 17,
						39 => 35,
						40 => 35,
						41 => 35,
						42 => 9,
						43 => 35,
						44 => 2,
						45 => 35,
						46 => 15,
						47 => 100,
						48 => 35,
						49 => 100,
						50 => 7,
						51 => 10,
						52 => 8,
						53 => 8,
						54 => 4,
						55 => 4,
						56 => 8,
						57 => 70,
						58 => 7,
						59 => 25,
						60 => 35,
						61 => 35,
						62 => 1,
						63 => 35,
						];

		foreach ($commandes as $commande) {

			$commande 	= management('commande_vente', $commande->id, $commande);
			$client 	= management('client', $commande->modele->client_id);
			$colis 		= $commande->recupere_colis();
			$livraison  = management('adresse', $commande->modele->adresse_de_livraison);

			$paiements = $commande->paiements();
			$contreRemboursement = 0;
			foreach ($paiements as $paiement) {
				
				if($paiement->mode_paiement_id_txt == 'A recevoir')
					$contreRemboursement += $paiement->montant;
			}

			$poids = number_format(!empty($commande->modele->poids_manuel) ? $commande->modele->poids_manuel : $commande->modele->poids_total , 2, '.', '');

			$temp = [];
			$i = 1;

			// Numéro de BL 1 25 1
			$temp[$i++] = $commande->modele->reference_document;

			// Numéro de récépissé 26 8 2
			$temp[$i++] = '';

			// Date expédition 34 8 3
			$temp[$i++] = date('dmY');

			// Code destinataire 42 17 4
			$temp[$i++] = '';

			// Nom destinataire 59 35 5
			$temp[$i++] = $livraison->modele->nom.' '.$livraison->modele->prenom;

			// Adresse1 destinataire 94 35 6
			$temp[$i++] = $livraison->modele->adresse;

			// Adresse2 destinataire 129 35 7
			$temp[$i++] = $livraison->modele->adresse_complement;

			// Code postal 164 9 8
			$temp[$i++] = $livraison->modele->code_postal;

			// Ville 173 35 9
			$temp[$i++] = $livraison->modele->ville;

			// Pays INSEE 208 2 10 Code INSEE du pays France par défaut J
			$temp[$i++] = $pays[strtolower($livraison->champ('pays_id')->affiche())];

			// Nom du contact 210 35 11 K
			$temp[$i++] = $livraison->modele->prenom.' '.$livraison->modele->nom;

			// Téléphone 245 15 12 L
			$temp[$i++] = str_replace(' ', '', $livraison->modele->telephone_portable);

			// Instructions de livraison 260 70 13 M
			$temp[$i++] = $commande->modele->commentaires_livraison;

			// Remarques de livraison 330 70 14 N
			$temp[$i++] = '';

			// Nombre de colis 400 3 15 O
			$temp[$i++] = count($colis);

			// Nombre de palettes 403 3 16 Obligatoire(**) P
			$temp[$i++] = '0';

			// Nb palettes consignées 406 3 17 Q
			$temp[$i++] = '0';

			// Poids de l'expédition (Kg) 409 7 18 Obligatoire CHP FIXE : 5,2 : 5 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. R
			$temp[$i++] = $poids;

			// Date de livraison 416 8 19 Format : JJMMAAAA S
			$temp[$i++] = '';

			// Contre remboursement 424 7 20 CHP FIXE : 5,2 : 5 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. T
			$temp[$i++] = ($contreRemboursement > 0 ? number_format($contreRemboursement, 2, '.', '') : '');

			// Valeur déclarée 431 7 21 CHP FIXE : 5,2 : 5 chiffres + 2 décimales CHP CSV : nombre avec séparateur dec. U
			$temp[$i++] = '0.00';

			// Nature marchandise 438 26 22 V
			$temp[$i++] = 'Volets roulants';

			// Compte client 464 8 23 si absent, remettant par défaut utilisé W
			$temp[$i++] = '';

			// Unités taxables 472 7 24 CHP FIXE : 5,2 : 5 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. X
			$temp[$i++] = '';

			// Code préparateur 479 10 25 Permet d'identifier un poste de préparation Y
			$temp[$i++] = '';

			// Matières dangereuses 489 7 26 Code matières dangereuses Z
			$temp[$i++] = '';

			// Code transporteur 496 5 27 AA
			$temp[$i++] = 'M59';

			// Code produit 501 5 28 AB
			$temp[$i++] = 'CLA';

			// Longueur 506 7 29 CHP FIXE : 5,2 : 5 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. AC
			$temp[$i++] = '0.00';

			// Code remettant sur 5 513 5 30 AD
			$temp[$i++] = '';

			// Incoterm 518 3 31 AE
			$temp[$i++] = '';

			// Région 521 10 32 AF
			$temp[$i++] = '';

			// Colis totaux 531 3 33 AG
			$temp[$i++] = '';

			// Nom de l'expéditeur réel 534 35 34 marque commerciale AH
			$temp[$i++] = '';

			// Code remettant sur 10 569 10 35 Code remettant jusqu'à 10 caractères, en remplacement du champ 'Code remettant sur 5' AI Mapping standard
			$temp[$i++] = '38068-AMC';

			// Type de port 579 1 36 P: Payé C: Dû F: Service O: Import R: Port en compte AJ
			$temp[$i++] = 'P';

			// Flag Enlèvement 580 1 37 R: Enlèvement avec retour L: Enlèvement avec re-livraison AK
			$temp[$i++] = '';

			// Code lieu d'enlèvement 581 17 38 AL
			$temp[$i++] = '';

			// Nom lieu d'enlèvement 598 35 39 AM
			$temp[$i++] = '';

			// Adresse1 lieu d'enlèvement 633 35 40 AN
			$temp[$i++] = '';

			// Adresse2 lieu d'enlèvement 668 35 41 AO
			$temp[$i++] = '';

			// Code postal lieu d'enlèvement 703 9 42 AP
			$temp[$i++] = '';

			// Ville lieu d'enlèvement 712 35 43 AQ
			$temp[$i++] = '';

			// Pays INSEE lieu d'enlèvement 747 2 44 AR
			$temp[$i++] = '';

			// Nom du contact lieu d'enlèvement 749 35 45 AS
			$temp[$i++] = '';

			// Téléphone lieu d'enlèvement 784 15 46 AT
			$temp[$i++] = '';

			// Email lieu d'enlèvement 799 100 47 AU
			$temp[$i++] = '';

			// Référence destinataire 899 35 48 AV
			$temp[$i++] = '';

			// Email destinataire 934 100 49 AW
			$temp[$i++] = $client->modele->adresse_email;

			// Volume 1034 7 50 CHP FIXE : 5,2 : 5 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. AX
			$temp[$i++] = '';

			// Montant manuel du transport 1041 10 51 CHP FIXE : 8,2 : 8 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. AY
			$temp[$i++] = '';

			// Date de livraison au plus tot 1051 8 52 Format : JJMMAAAA AZ
			$temp[$i++] = '';

			// Date de livraison au plus tard 1059 8 53 Format : JJMMAAAA BA
			$temp[$i++] = '';

			// Heure de livraison au plus tot 1067 4 54 Format : HHMM BB
			$temp[$i++] = '';

			// Heure de livraison au plus tard 1071 4 55 Format : HHMM BC
			$temp[$i++] = '';

			// Date d'enlèvement 1075 8 56 BD
			$temp[$i++] = '';

			// Instructions enlèvement 1083 70 57 BE
			$temp[$i++] = '';

			// Mètre plancher linéaire 1153 7 58 CHP FIXE : 5,2 : 5 chiffres + 2 décimales(***) CHP CSV : nombre avec séparateur dec. BF
			$temp[$i++] = '';

			// Téléphone 2 1160 25 59 BG
			$temp[$i++] = str_replace(' ', '', $client->modele->telephone_portable);

			// Code tournée 1185 35 60 BH
			$temp[$i++] = '';

			// Adresse3 destinataire 1220 35 61 BI
			$temp[$i++] = '';

			// Prise de rendez vous 1255 1 62 O si prise de rendez vous BJ
			$temp[$i++] = 'O';

			// Adresse4 destinataire 1256 35 63 BK
			$temp[$i++] = '';

			foreach($temp as $i => $ligne) {
				if(strlen($ligne) > $tailles_max[$i])
					dd('Erreur, champ trop long pour l\'API. Colonne '.$i.', taille max : '.$tailles_max[$i].', Valeur : '.$ligne);
			}

			$csv[] = $temp;
		}

		$nom_fichier = 'export_mazet_'.date('Y-m-d_H-i').'.csv';
		\Storage::put('mazet/'.$nom_fichier, $this->array2csv($csv, ';'));

		return $nom_fichier;

	}

	/**
	 *
	 * Transforme un array en CSV
	 *
	 */
	function array2csv($data, $delimiter = ',', $enclosure = '"', $escape_char = "\\") {
	    $f = fopen('php://memory', 'r+');
	    foreach ($data as $item) {
	        fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
	    }
	    rewind($f);
	    return stream_get_contents($f);
	}


	/**
	 *
	 * Enregistre et traite le retour du transporteur
	 *
	 * Cette méthode a été prévue pour être surchargée étant donné que le traitement du retour sera
	 * différent selon le client
	 *
	 */
	public function enregistre($transporteur, $reference, $urlTracking = [], $PDFs = [], $type_element) {

	}	
}
