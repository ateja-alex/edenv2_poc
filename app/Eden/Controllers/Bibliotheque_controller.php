<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Elements\Utilisateur_management;
use App\Eden\Models\Element_piece_jointe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Eden\Managements\Services\Google_service;
use Illuminate\Support\Str;


class Bibliotheque_controller extends Controller {

	/**
	 *
	 * Permet d'afficher la bibliothèque
	 *
	 */
    public function afficher() {

		$fichiers = modele('fichier_bibliotheque');

        if(!empty(moi_extranet()))
            $fichiers = $fichiers->where('disponible_extranet', 1);

        $fichiers = $fichiers->where('dossier_parent','=',null)->get();

		foreach($fichiers as $fichier) {

		    if ($fichier->poids > 1000000) {

		        $fichier->poids = round($fichier->poids / 1000000, 2) . ' Mo';
		    } elseif ($fichier->poids > 1000) {

		        $fichier->poids = round($fichier->poids / 1000) . ' Ko';
		    } else {

		        $fichier->poids = round($fichier->poids) . ' Octets';
		    }

		    $fichier->image = false;

		    if (in_array(strtolower($fichier->type), array('png', 'bmp', 'jpg', 'gif', 'jpeg'))) {

		        $fichier->image = true;
            }

			$nom_fichier = $fichier->chemin;

			$fichier->url_sur_serveur = 'public/'.$nom_fichier;

			$infos_pj = pathinfo(asset('public/'.$nom_fichier));

			if(!isset($infos_pj['extension']))
				$fichier->extension = '???';
			else
				$fichier->extension = $infos_pj['extension'];
		}

        $dossiers = $this->recuperer_dossiers();

        $gdrive = service('google');
        list($fichiers_gdrive, $dossiers_gdrive) = $gdrive->recupere_fichiers_drive(parametre('synchro_gdrive_dossier_racine'));

		$dossiers_sharepoint = array();
		$fichiers_sharepoint = array();

		if(!empty(moi()->id_microsoft) && config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation')) {

			$service_sharepoint = service('microsoft_sharepoint');
			list($fichiers_sharepoint, $dossiers_sharepoint) = $service_sharepoint->recuperer_contenu_dossier();
            return view('eden::bibliotheque', ['fichiers' => collect($fichiers_sharepoint), 'dossiers' => collect($dossiers_sharepoint)]);
		}
		// On affiche la vue
		return view('eden::bibliotheque', ['fichiers' => collect(array_merge($fichiers->toArray(), $fichiers_gdrive, $fichiers_sharepoint)), 'dossiers' => collect(array_merge($dossiers->toArray(),$dossiers_gdrive, $dossiers_sharepoint))]);
    }

	/**
	 *
	 * Permet d'ajouter un fichier à la bibliothèque
	 *
	 */
    public function ajouter_fichier(Request $formulaire) {


		$fichier_helper = $formulaire->file('image');

		$dossier_parent = json_decode($formulaire->get('dossier_parent'),true);

        // Si le dossier parent est de type Google Drive, on crée le fichier via le service eponyme
        if(isset($dossier_parent['gdrive']) && $dossier_parent['gdrive']) {

            $fichier = service('google')->upload_fichier_drive($fichier_helper->getClientOriginalName(), $fichier_helper->getPathname(), $dossier_parent['id']);

            return collect($fichier);
        }
		elseif(isset($dossier_parent['sharepoint']) && $dossier_parent['sharepoint']) {

            $service = service('microsoft_sharepoint');
			$contenu_fichier = file_get_contents($fichier_helper);

            $fichier = $service->creer_fichier($fichier_helper->getClientOriginalName(), $contenu_fichier, $dossier_parent['id']);

            return collect($fichier);
        }

        $chemin='public/';
        $dossier_parent_id=null;

        if(!empty($dossier_parent)){

            $chemin=$chemin.$dossier_parent['chemin'];
            $dossier_parent_id = $dossier_parent['id'];
        }
        else
            $chemin=$chemin.'bibliotheque';

        $fichier_existant=modele('fichier_bibliotheque')
            ->where('nom_original','=',$fichier_helper->getClientOriginalName())
            ->where('dossier_parent','=',$dossier_parent_id)
            ->first();

        $nom= $fichier_helper->getClientOriginalName();
        $extension = pathinfo($nom, PATHINFO_EXTENSION);
        $nom_uniquement= pathinfo($nom, PATHINFO_FILENAME);

        if($fichier_existant!=null){

            $i = 2;

            while(modele('fichier_bibliotheque')->where('nom_original',$nom_uniquement.' ('.$i.').'.$extension)
                    ->where('dossier_parent',$dossier_parent_id)
                    ->first() !=null){
                $i++;
            }

            $nom=$nom_uniquement.' ('.$i.').'.$extension;
        }

        if(fonctionnalite('pieces_jointes_garder_nom_originel') === true) {

            $nom_fichier = retraite_caracteres_speciaux(pathinfo($fichier_helper->getClientOriginalName(),PATHINFO_FILENAME),'_');

            $extension = pathinfo($fichier_helper->getClientOriginalName(),PATHINFO_EXTENSION);

            $nom_original = $nom_fichier.'.'.$extension;

            $path = $fichier_helper->storeAs($chemin, $nom_original);
        }
		else {
            $hash = Str::random(40);

            $path = $fichier_helper->storeAs($chemin,$hash.'.'.$fichier_helper->getClientOriginalExtension());
        }

		$dimensions = 0;

		if(in_array(strtolower(pathinfo($nom, PATHINFO_EXTENSION)), array('png', 'bmp', 'jpg', 'gif', 'jpeg'))) {

			list($width, $height) = getimagesize(storage_path('app/'.$path));

			$dimensions = $width.' * '.$height.' px';
		}

		$fichier = management('fichier_bibliotheque');

		$fichier->enregistre(array(
			'chemin' => str_replace('public/', '', $path),
			'nom_original' => $nom,
			'poids' => $fichier_helper->getSize(),
			'dimensions' => $dimensions,
			'type' => $fichier_helper->extension(),
            'dossier_parent' => $dossier_parent_id,
		));

		$fichier = $fichier->modele;

		$fichier->image = false;

		if(in_array(strtolower(pathinfo($nom, PATHINFO_EXTENSION)), array('png', 'bmp', 'jpg', 'gif', 'jpeg'))) {

			$fichier->image = true;
		}

		if($fichier->poids > 1000000) {

			$fichier->poids = round($fichier->poids / 1000000, 2) .' Mo';
		}
		elseif($fichier->poids > 1000) {

			$fichier->poids = round($fichier->poids / 1000) .' Ko';
		}
		else {

			$fichier->poids = round($fichier->poids) .' Octets';
		}

        return $fichier;
    }

	/**
	 *
	 * Permet d'ajouter un fichier dans un champ dropzone
	 *
	 */
    public function ajouter_fichier_element(Request $formulaire) {

		$fichier_helper = $formulaire->file('image');

		// $path = $fichier_helper->store('public');
        $chemin = 'public';

        if(fonctionnalite('pieces_jointes_garder_nom_originel') === true) {

            $nom_fichier = retraite_caracteres_speciaux(pathinfo($fichier_helper->getClientOriginalName(),PATHINFO_FILENAME),'_');

            $extension = pathinfo($fichier_helper->getClientOriginalName(),PATHINFO_EXTENSION);

            $nom_original = $nom_fichier.'.'.$extension;

			if(file_exists(storage_path('public/'.$nom_original)))
				return array('retour' => traduction('messages.php.bibliotheque.nom_fichier_existant'));

			$path = $fichier_helper->storeAs($chemin, $nom_original);
		}
		else {

            $hash = Str::random(40);

            $path = $fichier_helper->storeAs($chemin,$hash.'.'.$fichier_helper->getClientOriginalExtension());

        }
		$fichier = array(

			'url_storage' => $path,
			'url_public' => str_replace('public/', 'storage/', $path),
			'type' => $fichier_helper->extension(),
			'nom_original' => $fichier_helper->getClientOriginalName(),
		);

		$fichier['image'] = false;

		if(in_array(strtolower(pathinfo($fichier_helper->getClientOriginalName(), PATHINFO_EXTENSION)), array('png', 'bmp', 'jpg', 'gif', 'jpeg'))) {

			$fichier['image'] = true;
		}

		return array('fichier' => $fichier);
    }

	/**
	 *
	 * Permet d'afficher un fichier de la bibliothèque
	 *
	 */
    public function afficher_fichier($chemin) {

		$chemin = base64_decode($chemin);

		// on la télécharge en local
		$contenu = \Storage::disk('s3')->get($chemin);

		$basename = basename($chemin);

		// On enregistre l'image sur le serveur.
		if(file_exists(public_path('tmp/'.$basename)))
			unlink(public_path('tmp/'.$basename));

		$image_sur_serveur = fopen(base_path('').'/public/tmp/'.$basename, "w+");

		fwrite($image_sur_serveur, $contenu);
		fclose($image_sur_serveur);

		// On retourne une réponse
		return response()->file('tmp/'.$basename);
    }

	/**
	 *
	 * Permet d'afficher un fichier de la bibliothèque, via un champ_libre
	 *
	 */
    public function afficher_fichier_via_champ_libre($type_element, $nom_sql, $id_element, $token = false) {

		// vérification via un token md5
		if($token !== false && md5('eden'.$id_element) != $token)
			exit;

		$element = management($type_element, $id_element);

		$fichier = json_decode($element->modele->{$nom_sql});

		if(in_array($type_element, \App\Eden\Variables::$documents_gescom) && $nom_sql == 'pdf') {

			return response()->file(storage_path('app/'.$element->modele->pdf));
		}

		if(empty($fichier))
			dd("Aucun fichier à afficher / télécharger");

		return $this->afficher_fichier(base64_encode($fichier->chemin));
    }

    /**
     * @param $id_fichier
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de supprimer un fichier
     */
	public function supprimer_fichier($id_fichier){

        // Si le dossier parent est de type Google Drive, on supprime le dossier via le service eponyme
        if(strlen($id_fichier) > 10) {

		if(!empty(moi()->id_microsoft) && config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation')) {

				$service = service('microsoft_sharepoint');

				$dossier_supprime = $service->supprimer_element($id_fichier);

				return response()->json(array('retour' => true));
			}

            service('google')->supprime_drive($id_fichier);

            return response()->json(array('retour' => true));
        }

        $fichier_management = management('fichier_bibliotheque',$id_fichier);

        $fichier = $fichier_management->modele;

        // Storage::delete("public/{$fichier->chemin}");

        $fichier_management->supprime();

        // On affiche la vue
        return response()->json( ['retour' => true]);
    }

    /**
     * @param $id_fichier
     * @return mixed
     *
     * Permet de télécharger un fichier
     */
	public function telecharger_fichier($id_fichier){

        if(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation') && !empty(moi()->id_microsoft)){

            $lien_telechargement_fichier = service('microsoft_sharepoint')->telecharger_fichier($id_fichier);
            return redirect()->away($lien_telechargement_fichier);
        }

        $fichier_management = management('fichier_bibliotheque',$id_fichier);

        $fichier = modele('fichier_bibliotheque')->where('id', $id_fichier)->first();

        return Storage::download("public/{$fichier->chemin}", $fichier->nom_original);
    }

    /**
     * @param Request $request
     * @return mixed
     *
     * Permet de télécharger un fichier d'un champ dropzone
     */
	public function telecharger_fichier_element(Request $request){

		return redirect(str_replace('public','storage',$request->url));
    }

    /**
     * @param Request $formulaire
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet d'ajouter un dossier à la bibliothéque
     *
     */
	public function ajouter_dossier(Request $formulaire){

        $dossier_parent['id'] = null;

        if(isset($formulaire->dossier['dossier_parent'])){

            $dossier_parent = $formulaire->dossier['dossier_parent'];
        }

        $type_element = null;
        $type_element_pour_chemin = 'bibliotheque';

        if(isset($formulaire->dossier['type_element'])){

            $type_element = $formulaire->dossier['type_element'];
            $type_element_pour_chemin=$type_element;

        }

        $element_id = null;

        if(isset($formulaire->dossier['id_element'])){

            $element_id = $formulaire->dossier['id_element'];

        }

        $element_id_a_enregister = null;
        $creation_dossier_pour_fiche = 0;

        if(isset($formulaire->dossier['creation_dossier_pour_fiche'])){

            $creation_dossier_pour_fiche = $formulaire->dossier['creation_dossier_pour_fiche'];

            if($creation_dossier_pour_fiche == 1){
                $element_id_a_enregister = $element_id;
            }

        }

		$creation_dossier_confidentiel = 0;

        if(isset($formulaire->dossier['creation_dossier_confidentiel']))
            $creation_dossier_confidentiel = $formulaire->dossier['creation_dossier_confidentiel'];



        $dossier_existant = modele('dossier_bibliotheque')
            ->where('nom','=',$formulaire->dossier['nom'])
            ->where('dossier_parent','=',$dossier_parent['id'])
            ->where('type_element', $type_element)
            ->where(function($r) use ($element_id) {
                $r->where('element_id', $element_id)->orWhereNull('element_id');
            })
            ->first();

        if($formulaire->dossier['nom'] =='' || $dossier_existant!=null){
            return response()->json(array('retour' => traduction('messages.php.bibliotheque.nom_invalide_ou_existant')));
        }

        $dossier = management('dossier_bibliotheque');

		$informations = array(
            'nom' => $formulaire->dossier['nom'],
            'dossier_parent' => $dossier_parent['id'],
            'element_id' => $element_id_a_enregister,
            'type_element' => $type_element,
            'confidentiel' => $creation_dossier_confidentiel,
            'droit_entites' => array(),
        );

        //Si l'utilisateur a un id microsoft et que la synchro sharepoint est activée, on indique qu'il faut créer le dossier dans sharepoint
		if(!empty(moi()->id_microsoft) && !empty(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation')))
            $informations['sharepoint'] = true;

        if(fonctionnalite('gestion_droit_entites_repertoire')) {

            if (isset($formulaire->dossier['droit_entites'])) {

                foreach($formulaire->dossier['droit_entites'] as $entite_id => $valeur){

                    if($valeur == "true"){
                        $informations['droit_entites'][]= $entite_id;
                    }
                }
            }

			if(empty($informations['droit_entites']))
				return response()->json(array('retour' => traduction('messages.php.bibliotheque.choix_entite')));

        }

		// Si le dossier parent est de type Google Drive, on crée le dossier via le service eponyme
        if(isset($dossier_parent['gdrive']) && $dossier_parent['gdrive']) {

            $service = service('google');
            $dossier = $service->cree_dossier_drive($formulaire->dossier['nom'], $dossier_parent['id']);

            return response()->json(array('retour' => true,'dossiers' => $service->recupere_fichiers_drive($dossier_parent['id'], true)));
        }
		// Si le dossier parent est de type Microsoft Sharepoint, on crée le dossier via le service eponyme
        elseif(isset($dossier_parent['sharepoint']) && $dossier_parent['sharepoint']) {

            $service = service('microsoft_sharepoint');

			$service->creer_dossier($formulaire->dossier['nom'], $dossier_parent['id']);

            //On l'enregistre dans la bdd car ce sera un dossier à créer dans toutes les fiches
            if($creation_dossier_pour_fiche < 1)
                $dossier->enregistre($informations);

            return response()->json(array('retour' => true,'dossiers' => $service->recuperer_contenu_dossier($dossier_parent['id'])[1]));
        }

        // On crée un dossier dans les pièces jointes, donc associé à un type_element et un id_élément
        if(isset($formulaire->dossier['type_element']) && parametre('type_synchro_bibliotheque') == 'gdrive') {

            $dossier_parent = modele('bibliotheque_synchro_elements')->where('type_element', $formulaire->dossier['type_element'])->where('element_id', $formulaire->dossier['id_element'])->first();

            $service = service('google');
            $service->cree_dossier_drive($formulaire->dossier['nom'], $dossier_parent->id_dossier);

            return response()->json(array('retour' => true,'dossiers' => $service->recupere_fichiers_drive($dossier_parent->id_dossier, true)));

        }
		elseif(isset($formulaire->dossier['type_element']) && !empty(moi()->id_microsoft) && !empty(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation'))) {

			$dossier_parent = modele('bibliotheque_synchro_elements')->where('type_element', $formulaire->dossier['type_element'])->where('element_id', $formulaire->dossier['id_element'])->first();

            $service = service('microsoft_sharepoint');
            $service->creer_dossier($formulaire->dossier['nom'], $dossier_parent->id_dossier);

            //On l'enregistre dans la bdd car ce sera un dossier à créer dans toutes les fiches
            if($creation_dossier_pour_fiche < 1)
                $dossier->enregistre($informations);

            return response()->json(array('retour' => true,'dossiers' => $service->recuperer_contenu_dossier($dossier_parent->id_dossier)[1]));
        }
        elseif(isset($informations['sharepoint']) && !empty(moi()->id_microsoft) && !empty(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation'))) {

            $service = service('microsoft_sharepoint');

            $dossier = $service->creer_dossier($formulaire->dossier['nom']);

            return response()->json(array('retour' => true,'dossiers' => $service->recuperer_contenu_dossier()[1]));
        }

        $dossier->enregistre($informations);

        $chemin= '/'.$dossier->modele->id;

        if(isset($formulaire->dossier['dossier_parent'])){

            $chemin=$formulaire->dossier['dossier_parent']['chemin'].$chemin;
        }

        else{

            $chemin=$type_element_pour_chemin.$chemin;
        }


        $dossier->enregistre(array(
            'chemin' => $chemin,
        ));

        $retour = Storage::makeDirectory("public/{$chemin}");

        $dossiers = $this->recuperer_dossiers($dossier_parent['id'],$type_element,$element_id);

        return response()->json(array('retour' => $retour,'dossiers' => $dossiers));
    }

    /**
     * @param Request $formulaire
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de modifier un dossier
     *
     */
	public function modifier_dossier(Request $formulaire){

        $dossier_parent = null;

        if(isset($formulaire->dossier['dossier_parent']))
            $dossier_parent = $formulaire->dossier['dossier_parent'];

        $dossier_parent_id = null;

        if (isset($dossier_parent['id']))
            $dossier_parent_id = $dossier_parent['id'];

        if(isset($dossier_parent['sharepoint']) && $dossier_parent['sharepoint']) {

            $service = service('microsoft_sharepoint');

			$dossier = $service->modifier_dossier($formulaire->dossier['nom'], $formulaire->dossier['id']);

            return response()->json(array('retour' => true,'dossiers' => $service->recuperer_contenu_dossier($dossier_parent['id'])[1]));
        }
		elseif(isset($formulaire->dossier['type_element']) && !empty(moi()->id_microsoft) && !empty(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation'))) {

			$dossier_parent = modele('bibliotheque_synchro_elements')->where('type_element', $formulaire->dossier['type_element'])->where('element_id', $formulaire->dossier['id_element'])->first();

            $service = service('microsoft_sharepoint');

            $dossier = $service->modifier_dossier($formulaire->dossier['nom'], $formulaire->dossier['id']);

            return response()->json(array('retour' => true,'dossiers' => $service->recuperer_contenu_dossier($dossier_parent->id_dossier)[1]));
        }
        elseif(isset($formulaire->dossier['sharepoint']) && !empty(moi()->id_microsoft) && !empty(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation'))) {

            $service = service('microsoft_sharepoint');

            $dossier = $service->modifier_dossier($formulaire->dossier['nom'], $formulaire->dossier['id']);

            return response()->json(array('retour' => true,'dossiers' => $service->recuperer_contenu_dossier($dossier->getId())[1]));
        }

        $type_element = null;

        if(isset($formulaire->dossier['type_element'])){

            $type_element = $formulaire->dossier['type_element'];

        }

        $element_id = null;

        if(isset($formulaire->dossier['id_element'])){

            $element_id = $formulaire->dossier['id_element'];

        }

        if ($dossier_parent_id !== null) {

            $dossier_existant = modele('dossier_bibliotheque')
            ->where('nom','=',$formulaire->dossier['nom'])
            ->where('dossier_parent','=',$dossier_parent['id'])
            ->where('type_element', $type_element)
            ->where(function($r) use ($element_id) {
                $r->where('element_id', $element_id)->orWhereNull('element_id');
            })
            ->first();
        }
        else{

            $dossier_existant = modele('dossier_bibliotheque')
            ->where('nom','=',$formulaire->dossier['nom'])
            ->where('type_element', $type_element)
            ->where(function($r) use ($element_id) {
                $r->where('element_id', $element_id)->orWhereNull('element_id');
            })
            ->first();
        }

        if($formulaire->dossier['nom'] =='' || $dossier_existant!=null){
            if ($dossier_existant->id != $formulaire->dossier['id']) {
                return response()->json(array('retour' => traduction('messages.php.bibliotheque.nom_invalide_ou_existant')));
            }
        }

        $dossier = management('dossier_bibliotheque',$formulaire->dossier['id']);

        $dossier->enregistre(array(
            'nom' => $formulaire->dossier['nom'],
            'confidentiel' => (isset($formulaire->dossier['confidentiel'])) ? $formulaire->dossier['confidentiel'] : 0,
        ));

        $dossiers = $this->recuperer_dossiers($formulaire->dossier['id'],$type_element,$element_id);

        return response()->json(array('retour' => true,'dossiers' => $dossiers));
    }

    /**
     * @param $id_dossier
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de supprimer un dossier
     */
	public function supprimer_dossier($id_dossier){

        // Si le dossier parent est de type Google Drive, on supprime le dossier via le service eponyme
        if(strlen($id_dossier) > 10) {

			if(!empty(moi()->id_microsoft) && config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation')){

				$service = service('microsoft_sharepoint');

				$dossier_supprime = $service->supprimer_element($id_dossier);

				return response()->json(array('retour' => true));
			}

            service('google')->supprime_drive($id_dossier);

            return response()->json(array('retour' => true));
        }


        $dossier_management = management('dossier_bibliotheque',$id_dossier);

        $dossier = $dossier_management->modele;

        $dossiers_enfant = modele('dossier_bibliotheque')
            ->where('dossier_parent',$dossier->id)
            ->get();

        foreach($dossiers_enfant as $dossier_enfant){

            $this->supprimer_dossier($dossier_enfant);
        }

        $fichiers = modele('fichier_bibliotheque')
            ->where('dossier_parent','=',$dossier->id)
            ->get();

        foreach($fichiers as $fichier){

            $this->supprimer_fichier($fichier->id);
        }

        // Storage::deleteDirectory("public/{$dossier->chemin}");

        $dossier_management->supprime();

        // On affiche la vue
        return response()->json( ['retour' => true]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer les fichiers et les dossiers contenus dans un certain dossier
     */
	public function recuperer_bibliotheque_parametrer(Request $parametre){

        $dossier_parent_id = $parametre->dossier_parent_id == 0 ? null : $parametre->dossier_parent_id;
        $nouveau_dossier_parent=null;

        $fichiers = modele('fichier_bibliotheque');

        if(!empty(moi_extranet()))
            $fichiers = $fichiers->where('disponible_extranet', 1);

        $fichiers = $fichiers
            ->where('dossier_parent','=',$dossier_parent_id)
            ->get();

        $dossiers = $this->recuperer_dossiers($dossier_parent_id);

        if(isset($dossier_parent_id)){

            $dossier_parent = modele('dossier_bibliotheque',$dossier_parent_id);

            foreach ($dossiers as $dossier) {

                $dossier->dossier_parent = $dossier_parent;
            }

            $nouveau_dossier_parent = $this->recuperation_dossier_parent($dossier_parent);
        }

        foreach($fichiers as $fichier) {

            if ($fichier->poids > 1000000)
                $fichier->poids = round($fichier->poids / 1000000, 2) . ' Mo';
            elseif ($fichier->poids > 1000)
                $fichier->poids = round($fichier->poids / 1000) . ' Ko';
            else
                $fichier->poids = round($fichier->poids) . ' Octets';

            $fichier->image = false;

            if (in_array(strtolower($fichier->type), array('png', 'bmp', 'jpg', 'gif', 'jpeg')))
                $fichier->image = true;
        }

        $fichiers_gdrive = [];
        $dossiers_gdrive = [];
		$fichiers_sharepoint = [];
        $dossiers_sharepoint = [];

        // Si on est sur un dossier Google Drive
        if($dossier_parent_id == 0 || strlen($dossier_parent_id) > 10) {

            $gdrive = service('google');

            if($dossier_parent_id != 0) {

                $nom_dossier = '';
                $dossiers_gdrive = $gdrive->recupere_fichiers_drive('', true);

                foreach ($dossiers_gdrive as $dossier) {

                    if($dossier->id == $dossier_parent_id) {

                        $nom_dossier = $dossier->nom;
                        continue;
                    }
                }

                $nouveau_dossier_parent = collect([
                                                    "id"                => $dossier_parent_id,
                                                    "nom"               => $nom_dossier,
                                                    "chemin"            => "/".$dossier_parent_id,
                                                    "dossier_parent"    => (isset($parametre->dossier_parent) ? $parametre->dossier_parent : null),
                                                    "element_id"        => $dossier_parent_id,
                                                    "gdrive"            => true,
                                                ]);
            }
            list($fichiers_gdrive, $dossiers_gdrive) = $gdrive->recupere_fichiers_drive($dossier_parent_id == 0 ? parametre('synchro_gdrive_dossier_racine') : $dossier_parent_id);



        }
		if(!empty(moi()->id_microsoft) && !empty(config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation')) != null && ($dossier_parent_id == 0 || strlen($dossier_parent_id) > 10)) {

            $sharepoint = service('microsoft_sharepoint');

            // Si on est dans un sous-dossier
            if($dossier_parent_id != 0) {

            	// Si on charge le même dossier que celui dans lequel on se trouve, on ne change pas le dossier parent
            	if($dossier_parent_id == (isset($parametre->dossier_parent) ? $parametre->dossier_parent['id'] : null)) {

            		$nouveau_dossier_parent = collect($parametre->dossier_parent);

            	// Sinon, on charge les dossiers parents
            	} else {

	                $nom_dossier = '';
				$dossier_sharepoint = $sharepoint->recuperer_dossier($dossier_parent_id);

	                $nom_dossier = $dossier_sharepoint->nom;

	                $nouveau_dossier_parent = collect([
	                                                    "id"                => $dossier_parent_id,
	                                                    "nom"               => $nom_dossier,
	                                                    "chemin"            => "/".$dossier_parent_id,
	                                                    "dossier_parent"    => (isset($parametre->dossier_parent) ? $parametre->dossier_parent : null),
	                                                    "element_id"        => $dossier_parent_id,
	                                                    "sharepoint"        => true,
	                                                ]);
            	}

            } else {

            	// On charge la racine de la fiche
                $nouveau_dossier_parent = modele('bibliotheque_synchro_elements')
                								->where('type_element', $parametre->type_element)
                								->where('element_id', $parametre->id_element)
                								->first();
				if(!empty($nouveau_dossier_parent))
					$dossier_parent_id = $nouveau_dossier_parent->id_dossier;

                $nouveau_dossier_parent = null;

            }


            list($fichiers_sharepoint, $dossiers_sharepoint) = $sharepoint->recuperer_contenu_dossier($dossier_parent_id == 0 ? config('dossier_racine_eden_sharepoint') : $dossier_parent_id);

            return response()->json(array('retour' => true,'dossiers' => collect($dossiers_sharepoint),'fichiers' => collect($fichiers_sharepoint),'nouveau_dossier_parent'=>$nouveau_dossier_parent));
        }
        return response()->json(array('retour' => true,'dossiers' => collect(array_merge($dossiers->toArray(),$dossiers_gdrive, $dossiers_sharepoint)),'fichiers' => collect(array_merge($fichiers->toArray(), $fichiers_gdrive, $fichiers_sharepoint)),'nouveau_dossier_parent'=>$nouveau_dossier_parent));

    }

	/**
	 *
	 *
	 *
	 */
    public function recuperation_dossier_parent($dossier){

        if($dossier->dossier_parent != null ){

            if(!is_object($dossier->dossier_parent)){

                $dossier->dossier_parent = modele('dossier_bibliotheque',$dossier->dossier_parent);

            }

            $dossier->dossier_parent = $this->recuperation_dossier_parent($dossier->dossier_parent);
        }

        if(fonctionnalite('gestion_droit_entites_repertoire')) {
            $dossier->droit_entites = management('dossier_bibliotheque', $dossier->id)->droits_entites();
        }
        return $dossier;
    }

	/**
	 *
	 *
	 *
	 */
    public function deplacer_element_dans_un_dossier(Request $request){

        // Si on est sur une bibliothèque GDrive
        if(parametre('type_synchro_bibliotheque') == 'gdrive') {

            // Si on est dans un sous-dossier, on prends celui-ci comme origine.
            if(!empty($request->dossier_parent)) {

                service('google')->deplace_drive($request->id_element, $request->dossier_parent['id'], $request->id_nouveau_dossier_parent);

            // Sinon, c'est qu'on est à la racine. Fiche ou Bibliothèque
            } else {

                // Si on est à la racine d'une fiche
                if(isset($request->type_element_fiche) && isset($request->id_element_fiche)) {

                    $dossier_parent = modele('bibliotheque_synchro_elements')
                                            ->where('type_element', $request->type_element_fiche)
                                            ->where('element_id', $request->id_element_fiche)
                                            ->first();
                    service('google')->deplace_drive($request->id_element, $dossier_parent->id_dossier, $request->id_nouveau_dossier_parent);

                // Sinon, c'est qu'on est à la racine de la bibliothèque
                } else {

                    service('google')->deplace_drive($request->id_element, parametre('synchro_gdrive_dossier_racine'), $request->id_nouveau_dossier_parent);
                }
            }

            return response()->json(array('retour' => true));
        }
		if(!empty(moi()->id_microsoft) && config('fonctionnalites_integrations.sharepoint_utiliser_synchronisation')){

			// Si on est dans un sous-dossier, on prends celui-ci comme origine.
            if(!empty($request->dossier_parent)) {

                service('microsoft_sharepoint')->deplacer_element($request->id_element, $request->id_nouveau_dossier_parent);

            // Sinon, c'est qu'on est à la racine. Fiche ou Bibliothèque
            } else {

                // Si on est à la racine d'une fiche
                if(isset($request->type_element_fiche) && isset($request->id_element_fiche)) {

                    $dossier_parent = modele('bibliotheque_synchro_elements')
                                            ->where('type_element', $request->type_element_fiche)
                                            ->where('element_id', $request->id_element_fiche)
                                            ->first();
				service('microsoft_sharepoint')->deplacer_element($request->id_element, $request->id_nouveau_dossier_parent);
                // Sinon, c'est qu'on est à la racine de la bibliothèque
                } else {

					service('microsoft_sharepoint')->deplacer_element($request->id_element, $request->id_nouveau_dossier_parent);
				}
            }

            return response()->json(array('retour' => true));
		}

        $type_element = $request->type_element;
        $id_element = $request->id_element;
        $id_nouveau_dossier_parent = $request->id_nouveau_dossier_parent;

        $chemin_nouveau_dossier_parent = null;
        $nouveau_dossier_parent = null;

        if($id_nouveau_dossier_parent!=null){

            $nouveau_dossier_parent = modele('dossier_bibliotheque',$id_nouveau_dossier_parent);

            $chemin_nouveau_dossier_parent=$nouveau_dossier_parent->chemin;
        }

        if($type_element == 'fichier'){

            $fichier_management = management('fichier_bibliotheque',$id_element);

            $fichier = $fichier_management->modele;

            $fichier_existant=modele('fichier_bibliotheque')
                ->where('nom_original','=',$fichier->nom_original)
                ->where('dossier_parent','=',$id_nouveau_dossier_parent)
                ->first();

            $nom= $fichier->nom_original;
            $extension = pathinfo($nom, PATHINFO_EXTENSION);
            $nom_uniquement= pathinfo($nom, PATHINFO_FILENAME);

            if($fichier_existant!=null){
                $i = 2;
                while(modele('fichier_bibliotheque')->where('nom_original',$nom_uniquement.' ('.$i.').'.$extension)
                        ->where('dossier_parent',$id_nouveau_dossier_parent)
                        ->first() !=null){
                    $i++;
                }
                $nom=$nom_uniquement.' ('.$i.').'.$extension;
            }

            $fichier->nom_original = $nom;

            $nouveau_chemin = 'bibliotheque/'.$fichier->nom_original;

            if($chemin_nouveau_dossier_parent != null){
                $nouveau_chemin=$nouveau_dossier_parent->chemin.'/'.$fichier->nom_original;
            }

            Storage::move('public/'.$fichier->chemin, 'public/'.$nouveau_chemin);

            $fichier_management->enregistre(array(
                'chemin' => $nouveau_chemin,
                'dossier_parent' => $id_nouveau_dossier_parent
            ));

        }

        elseif ($type_element == 'piece_jointe'){

            $piece_jointe =  Element_piece_jointe::where('id',$id_element)
                ->first();

            $piece_jointe_existant =  Element_piece_jointe::where('nom',$piece_jointe->nom)
                ->where('dossier_parent',$id_nouveau_dossier_parent)
                ->first();

            $nom= $piece_jointe->nom;
            $extension = pathinfo($nom, PATHINFO_EXTENSION);
            $nom_uniquement= pathinfo($nom, PATHINFO_FILENAME);

            if($piece_jointe_existant!=null){
                $i = 2;
                while(Element_piece_jointe::where('nom',$nom_uniquement.' ('.$i.').'.$extension)
                        ->where('dossier_parent',$id_nouveau_dossier_parent)->where('type_element',$type_element)
                        ->first() !=null){
                    $i++;
                }
                $nom=$nom_uniquement.' ('.$i.').'.$extension;
            }

            $piece_jointe->nom = $nom;

            $piece_jointe_existant =  Element_piece_jointe::where('titre',$piece_jointe->titre)
                ->where('dossier_parent',$id_nouveau_dossier_parent)
                ->first();

            if($piece_jointe_existant!=null) {

                $piece_jointe->titre = $piece_jointe->nom;
            }

            $nouveau_chemin = $piece_jointe->type_element.'/'.$piece_jointe->nom;

            if($chemin_nouveau_dossier_parent != null){
                $nouveau_chemin=$nouveau_dossier_parent->chemin.'/'.$piece_jointe->nom;
            }

            Storage::move('public/'.$piece_jointe->chemin, 'public/'.$nouveau_chemin);

            $piece_jointe->chemin=$nouveau_chemin;
            $piece_jointe->dossier_parent=$id_nouveau_dossier_parent;

            $piece_jointe->save();

        }

        else{

            $dossier_management = management('dossier_bibliotheque',$id_element);

            $dossier= $dossier_management->modele;

            $dossier_existant = modele('dossier_bibliotheque')
                ->where('nom','=',$dossier->nom)
                ->where('dossier_parent','=',$id_nouveau_dossier_parent)
                ->where('type_element', $dossier->type_element)
                ->where(function($r) use ($dossier) {
                    $r->where('element_id', $dossier->element_id)->orWhereNull('element_id');
                })
                ->first();

            if($dossier_existant!=null){
                return response()->json(array('retour' => traduction('messages.php.bibliotheque.erreur_deplacement_dossier')));
            }

            if($chemin_nouveau_dossier_parent == null && $dossier->type_element == null){

                $chemin_nouveau_dossier_parent = 'bibliotheque/';

            }

            elseif($chemin_nouveau_dossier_parent == null && $dossier->type_element != null){

                $chemin_nouveau_dossier_parent = $dossier->type_element.'/';
            }

            $nouveau_chemin = $chemin_nouveau_dossier_parent.'/'.$dossier->id;

            Storage::move('public/'.$dossier->chemin, 'public/'.$nouveau_chemin);

            $this->changement_chemin_dossier($chemin_nouveau_dossier_parent,$dossier,$nouveau_dossier_parent);

            $dossier_management = management('dossier_bibliotheque',$dossier->id);

            $dossier_management->enregistre(array(
                'dossier_parent' => $id_nouveau_dossier_parent,
            ));
        }

        return response()->json(array('retour' => true));
    }

	/**
	 *
	 *
	 *
	 */
    public function changement_chemin_dossier($chemin_nouveau_dossier_parent,$dossier,$nouveau_dossier_parent){

        $nouveau_chemin = $chemin_nouveau_dossier_parent.'/'.$dossier->id;

        $dossiers_enfants = modele('dossier_bibliotheque')->where('dossier_parent',$dossier->id)->get();

        if($dossier->type_element != null ){

            $pieces_jointes =  Element_piece_jointe::where('dossier_parent',$dossier->id)
                ->get();

            foreach($pieces_jointes as $piece_jointe){

                $piece_jointe->chemin = $nouveau_chemin.'/'.$piece_jointe->nom;
                $piece_jointe->save();

            }
        }

        else{

            $fichiers = modele('fichier_bibliotheque')->where('dossier_parent',$dossier->id)->get();

            foreach($fichiers as $fichier){

                $fichier_management = management('fichier_bibliotheque',$fichier->id);

                $fichier_management->enregistre(array(
                    'chemin' => $nouveau_chemin.'/'.$fichier->nom_original,
                ));
            }
        }

        foreach($dossiers_enfants as $dossier_enfant){

            $this->changement_chemin_dossier($nouveau_chemin,$dossier_enfant,$nouveau_dossier_parent);

        }

        $dossier_management = management('dossier_bibliotheque',$dossier->id);

        $element_id = $dossier->element_id;

        if($nouveau_dossier_parent != null){
            $element_id =  $nouveau_dossier_parent->element_id;
        }

        $dossier_management->enregistre(array(
            'chemin' => $nouveau_chemin,
            'element_id' => $element_id,
        ));

    }

    /**
     *
     * Récupérer les dossiers
     *
     */
    public function recuperer_dossiers($dossier_parent = null,$type_element = null,$element_id = null){

        $dossiers = modele('dossier_bibliotheque');

        if(!fonctionnalite('gestion_droit_entites_repertoire')) {

            $dossiers->sans_profils();
        }

		$dossiers = $dossiers->select('dossier_bibliotheque.*')
            ->where('dossier_parent','=',$dossier_parent);

        if(!empty(moi_extranet()))
            $fichiers = $dossiers->where('disponible_extranet', 1);

        $dossiers = $dossiers->where('type_element','=',$type_element)
            ->leftJoin('dossier_bibliotheque_droit_entites','dossier_bibliotheque_droit_entites.cle_locale','dossier_bibliotheque.id');

        if($element_id){

            $dossiers->where(function($r) use ($element_id) {
                $r->where('element_id', $element_id)->orWhereNull('element_id');
            });
        }


        $dossiers = $dossiers->distinct()->get();

        return $dossiers;
    }

}
