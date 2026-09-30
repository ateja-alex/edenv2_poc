<?php

namespace App\Eden\Managements\Services;

use App\Eden\Variables;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Eden\Managements\Elements\Element_management;
use Log;

class Microsoft_sharepoint_service {

	/*
	 *
	 * Récupère le site et le drive où sont stockés les documents de l'utilisateur actuellement connecté
	 *
	 */
	private function recuperer_drive_document() {

		$graph = service('microsoft_authentification')->instancie_graph();
        $site_id = fonctionnalite('site_racine_eden_sharepoint');

		if(empty($graph))
			return false;

        if(empty($site_id))
            return false;

		//Récupère les drives disponibles pour le site choisi
        try {

            $drives = $graph->createRequest('GET', '/sites/'. $site_id .'/drives')
                ->setReturnType(Model\Drive::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La récupération des drives a échoué sur le site '. $site_id .' pour l\'utilisateur microsoft ' . moi()->id_microsoft, $exception);
            return false;
        }

        $drive_id = false;

        foreach($drives as $drive){

            if($drive->getName() == 'Documents')
                $drive_id = $drive->getId();
        }

        if(empty($drive_id))
            return false;

		return ['site_id' => $site_id, 'drive_id' => $drive_id];
	}

    /*
	 *
	 * Récupère tous les sites disponibles et les met en forme pour l'afficher dans les paramètres
	 *
	 */
    public function lister_tous_les_sites(){

        $graph = service('microsoft_authentification')->instancie_graph();

        if(empty($graph) || empty(fonctionnalite('sharepoint_utiliser_synchronisation')))
            return false;

        //On récupère les sites
        try {

            $sites = $graph->createRequest('GET', '/sites?search=')
                ->setReturnType(Model\Site::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La récupération des sites a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft, $exception);
            return false;
        }

        $liste_sites = array();

        //Pour chaque site, on met en forme son nom pour l'affichage et on le met dans un tableau.
        foreach($sites as $site){

            $nom_site = $site->getDisplayName();
            $id_site = explode(',', $site->getId())[1];
            $liste_sites[$id_site] = $nom_site;
        }

        return $liste_sites;
    }

    /*
     *
     * On initialise graph et les données nécessaires aux requêtes
     *
     */
    protected function initialisation_donnees($dossier_choisi = false){

        if(empty($dossier_choisi)) {

            $dossier_racine = fonctionnalite('dossier_racine_eden_sharepoint');
            $dossier_choisi = !empty($dossier_racine) ? $dossier_racine : 'root';
        }

        if(!empty($this->donnees)) {

            $this->donnees['dossier_choisi'] = $dossier_choisi;
            return $this->donnees;
        }

        $graph = service('microsoft_authentification')->instancie_graph();

        //On récupère les ids du site et du drive documents du site
        $ids = $this->recuperer_drive_document();
				
        if(empty($ids))
             return false;

        $drive_id = $ids['drive_id'];
        $site_id = $ids['site_id'];

        if(empty($graph) || empty(fonctionnalite('sharepoint_utiliser_synchronisation')))
            return false;

        if(empty($site_id) || empty($drive_id))
            return false;

        $this->donnees = ['graph' => $graph, 'drive_id' => $drive_id, 'site_id' => $site_id, 'dossier_choisi' => $dossier_choisi];

        return $this->donnees;
    }

	/*
	 *
	 * Récupère tous les dossiers disponibles sur le site et les met en forme pour l'afficher dans les paramètres
	 *
	 */
	public function lister_tous_les_dossiers(){

		$donnees = $this->initialisation_donnees();

        if($donnees === false)
            return false;

		//On récupère les dossiers et fichiers qui sont dans le drive
        try {

            $dossiers_drive = $donnees['graph']->createRequest('GET', '/sites/'. $donnees['site_id'] .'/drives/'. $donnees['drive_id'] .'/root/children?$top=99999999')
                ->setReturnType(Model\DriveItem::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La récupération des dossiers a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .')', $exception);
            return false;
        }

		$liste_dossiers = array();

		//Pour chaque dossier, on met en forme son nom pour l'affichage et on regarde s'il ne contient pas des dossiers enfants à récupèrer. On le met ensuite dans un tableau.
		foreach($dossiers_drive as $dossier){

			//On ignore les fichiers
			if(empty($dossier->getFolder()))
				continue;

			$nombre_enfants_dossier = $dossier->getFolder()->getChildCount();
			$nom_dossier = substr($dossier->getWebUrl(), strpos($dossier->getWebUrl(), '/', 10));
            $nom_dossier = rawurldecode($nom_dossier);

			$liste_dossiers[$dossier->getId()] = $nom_dossier;

			if($nombre_enfants_dossier > 0){

				//On récupère les dossiers enfants
				$liste_dossiers = $this->recuperer_arborescence_liste_dossiers($liste_dossiers, false, $dossier);
			}
		}

		return $liste_dossiers;
	}

	/*
	 *
	 * Récupère les éléments contenus dans le dossier choisi
	 *
	 */
	public function recuperer_arborescence_liste_dossiers($liste_dossiers, $recuperer_dossiers_entiers = false, $dossier_choisi = false) {

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

		//S'il n'y a pas de dossier choisi on retourne le dossier racine sinon on retourne le dossier choisi
		if(empty($dossier_choisi)){

            //On récupère les dossiers et fichiers qui sont dans le drive
            try {

                $dossiers_sites = $donnees['graph']->createRequest('GET', '/sites/'. $donnees['site_id'] .'/drives/'. $donnees['drive_id'] .'/root/children')
                    ->setReturnType(Model\DriveItem::class)
                    ->execute();
            } catch(\Exception $exception){

                log_mis_en_forme('La récupération des dossiers a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .')', $exception);
                return false;
            }

		}
		else{
            try {

                $dossiers_sites = $donnees['graph']->createRequest('GET', '/sites/'. $donnees['site_id'] .'/drives/'. $donnees['drive_id'] .'/items/'. $dossier_choisi->getId() .'/children')
                    ->setReturnType(Model\DriveItem::class)
                    ->execute();
            } catch(\Exception $exception){

                log_mis_en_forme('La récupération des dossiers a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .', dossier: '. $dossier_choisi->getId() .')', $exception);
                return false;
            }
		}

		//Pour chaque dossier, on met en forme son nom pour l'affichage et on regarde s'il ne contient pas des dossiers enfants à récupèrer. On le met ensuite dans un tableau.
		foreach($dossiers_sites as $dossier_enfant){

			//On ignore les fichiers
			if(empty($dossier_enfant->getFolder()))
				continue;

			$nom_dossier = substr($dossier_enfant->getWebUrl(), strpos($dossier_enfant->getWebUrl(), '/', 10));
			$nom_dossier = rawurldecode($nom_dossier);

			//On regarde si on veut récuperer le dossier entier ou juste son nom pour l'affichage
			if(empty($recuperer_dossiers_entiers))
				$liste_dossiers[$dossier_enfant->getId()] = $nom_dossier;
			else
				$liste_dossiers[] = $dossier_enfant;
		}

		return $liste_dossiers;
	}

	/*
	 *
	 * Crée un fichier dans le dossier choisi
	 *
	 */
	public function creer_fichier($nom_fichier_a_creer, $fichier_a_copier, $dossier_choisi = false) {

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

		//Si on a pas le nom du fichier à créer on retourne false
		if(empty($nom_fichier_a_creer))
			return false;

        $nom_fichier_a_creer = trim(str_replace(["'",'~','"','#','%','&','*',':','<','>','?','/','\\','{','|', '}'], " ", $nom_fichier_a_creer));

		$fichier_a_creer = $fichier_a_copier;
        try {

            $retour_fichier_cree = $donnees['graph']->createRequest('PUT', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $donnees['dossier_choisi'] .':/'. $nom_fichier_a_creer .':/content')
                ->addHeaders(['Content-Type' => 'text/plain'])
                ->setReturnType(Model\DriveItem::class)
                ->attachBody($fichier_a_creer)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La création du fichier '. $nom_fichier_a_creer .' a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .', dossier: '. $donnees['dossier_choisi'] .')', $exception);
            return false;
        }


		return $this->prepare_fichier_pour_bibliotheque($retour_fichier_cree);
	}

	/*
	 *
	 * Crée un dossier dans le dossier choisi
	 *
	 */
	public function creer_dossier($nom_dossier_a_creer, $dossier_choisi = false) {

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

		//Si on a pas le nom du dossier à créer on retourne false
		if(empty($nom_dossier_a_creer))
			return false;

        $nom_dossier_a_creer = trim(str_replace(["'",'~','"','#','%','&','*',':','<','>','?','/','\\','{','|', '}'], " ", strip_tags($nom_dossier_a_creer)));

		//On initialise le dossier à créer
		$nouveau_dossier =	[
			'name' => $nom_dossier_a_creer,
			'folder' => ['childCount' => 0],
			'@microsoft.graph.conflictBehavior' => 'fail',
		];

        if(empty($donnees['dossier_choisi']))
            $donnees['dossier_choisi'] = 'root';

        try{

            //On fait la requête POST pour créer le dossier
            $retour_dossier_cree = $donnees['graph']->createRequest('POST', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $donnees['dossier_choisi'] .'/children')
                ->addHeaders(['Content-Type' => 'application/json'])
                ->setReturnType(Model\DriveItem::class)
                ->attachBody($nouveau_dossier)
                ->execute();
        } catch(\Exception $exception){

            Log::warning("Microsoft_sharepoint_service::creer_dossier : Le dossier portant le nom " . $nom_dossier_a_creer . " n'a pas pu être créé. Erreur : " . $exception->getMessage() . " Stacktrace : " . $exception->getTraceAsString());
            return $exception;
        }

		return $retour_dossier_cree;
	}

    /*
	 *
	 * Permet de modifier le nom d'un dossier existant
	 *
	 */
	public function modifier_dossier($nom_dossier , $id_element_a_modifier, $dossier_choisi = false) {

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

        $nom_dossier = trim(str_replace(["'",'~','"','#','%','&','*',':','<','>','?','/','\\','{','|', '}'], " ", strip_tags($nom_dossier)));
		//On initialise le dossier à créer
		$nouveau_dossier =	[
								'name' => $nom_dossier,
							];

		//On fait la requête POST pour créer le dossier
        try {

            $retour_dossier_cree = $donnees['graph']->createRequest('PATCH', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $id_element_a_modifier)
                ->addHeaders(['Content-Type' => 'application/json'])
                ->setReturnType(Model\DriveItem::class)
                ->attachBody($nouveau_dossier)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La modification du dossier (id: '. $id_element_a_modifier .') a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .', dossier: '. $donnees['dossier_choisi'] .')', $exception);
            return false;
        }


		return $retour_dossier_cree;
	}

	/*
	 *
	 * Déplace un élément dans le dossier choisi
	 *
	 */
	public function deplacer_element($id_element, $id_nouveau_dossier) {

        $donnees = $this->initialisation_donnees();

        if($donnees === false)
            return false;

		//S'il n'y a pas d'id d'élément ou de dossier on retourne false
		if(empty($id_element) || empty($id_nouveau_dossier))
			return false;

		//On initialise l'id du nouveau parent au format que demande l'API
		$nouveau_dossier =	[
								'parentReference' => [
									'id' => $id_nouveau_dossier,
								],
							];

		//On fait une requête PATCH pour modifier un élément existant
        try {

            $retour_dossier_cree = $donnees['graph']->createRequest('PATCH', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $id_element)
                ->addHeaders(['Content-Type' => 'application/json'])
                ->attachBody($nouveau_dossier)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La modification du dossier (id: '. $id_element .') a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .', dossier: '. $donnees['dossier_choisi'] .')', $exception);
            return false;
        }

		return $retour_dossier_cree;
	}

	/*
	 *
	 * Récupère les éléments contenus dans le dossier choisi
	 *
	 */
	public function recuperer_contenu_dossier( $dossier_choisi = false) {

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

		//On récupère le contenu du dossier avec une requête GET
        try {

            $contenu_dossier_choisi = $donnees['graph']->createRequest('GET', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id']  .'/items/'. $donnees['dossier_choisi'] .'/children?$top=9999')
                ->setReturnType(Model\DriveItem::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La récupération du contenu du dossier a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .', dossier: '. $donnees['dossier_choisi'] .')', $exception);
            return false;
        }

		$dossiers = array();
		$fichiers = array();

		//Pour chaque élément du dossier choisi, on les prépare pour l'affichage et on les range selon leur type (fichier ou dossier)
		foreach($contenu_dossier_choisi as $dossier){

            $dossier = $this->prepare_fichier_pour_bibliotheque($dossier);

			if($dossier->est_un_fichier)
                $fichiers[] = $dossier;
            else
			    $dossiers[] = $dossier;
		}

		return [$fichiers, $dossiers];
	}

	/*
	 *
	 * Retourne le lien de téléchargement d'un fichier
	 *
	 */
	public function telecharger_fichier($id_fichier_a_telecharger){

        $donnees = $this->initialisation_donnees();

        if($donnees === false)
            return false;

		//S'il n'y a pas d'id d'élément on retourne false
		if(empty($id_fichier_a_telecharger))
			return false;

		//On récupère l'élément
        try {

            $fichier = $donnees['graph']->createRequest('GET', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $id_fichier_a_telecharger)
                ->setReturnType(Model\DriveItem::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La récupération du fichier a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .', dossier: '. $donnees['dossier_choisi'] .')', $exception);
            return false;
        }


		//On récupère son lien de téléchargement
		$lien_telechargement_fichier = $fichier->getProperties()['@microsoft.graph.downloadUrl'];

		return $lien_telechargement_fichier;
	}

	/*
	 *
	 * Récupère le dossier choisi et ses attributs
	 *
	 */
	public function recuperer_dossier($dossier_choisi, $sans_mise_en_forme = false){

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

		//On récupère le dossier
        try{

            $dossier_choisi = $donnees['graph']->createRequest('GET', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $donnees['dossier_choisi'])
                ->setReturnType(Model\DriveItem::class)
                ->execute();
        }
        catch(\Exception $exception){

            return $exception;
        }

        //On prépare le dossier pour l'affichage s'il le faut
        if(!$sans_mise_en_forme)
            $dossier_choisi = $this->prepare_fichier_pour_bibliotheque($dossier_choisi);

		return $dossier_choisi;
	}

	/*
	 *
	 * Récupère les éléments contenus dans le dossier portant le nom passé en paramètre
	 *
	 */
	public function recuperer_dossier_avec_nom($nom_dossier_choisi, $dossier_choisi = false){

        $donnees = $this->initialisation_donnees($dossier_choisi);

        if($donnees === false)
            return false;

        $nom_dossier_choisi = trim(str_replace(["'",'~','"','#','%','&','*',':','<','>','?','/','\\','{','|', '}'], " ", $nom_dossier_choisi));

        $url = "/sites/". $donnees['site_id'] . "/drives/". $donnees['drive_id'];

        if($dossier_choisi !== false)
            $url .= "/items/" . $donnees['dossier_choisi'];

        $url .= "/search(q='" . $nom_dossier_choisi . "')";

        try {

            $dossiers_sharepoint = $donnees['graph']->createRequest('GET', $url)
                ->setReturnType(Model\DriveItem::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La recherche du dossier '. $nom_dossier_choisi .' a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .')', $exception);
            return false;
        }

        return $dossiers_sharepoint[0] ?? null;
	}

	/*
	 *
	 * Supprime l'élément dans le dossier choisi
	 *
	 */
	public function supprimer_element($element_a_supprimer) {

        $donnees = $this->initialisation_donnees();

        if($donnees === false)
            return false;

		//S'il n'y a pas d'id d'élément à supprimer on retourne false
		if(empty($element_a_supprimer))
			return false;
		//On supprime l'élément dans Sharepoint avec une requête DELETE
        try {

            $suppression_element = $donnees['graph']->createRequest('DELETE', '/sites/'. $donnees['site_id'] . '/drives/'. $donnees['drive_id'] .'/items/'. $element_a_supprimer)
                ->setReturnType(Model\DriveItem::class)
                ->execute();
        } catch(\Exception $exception){

            log_mis_en_forme('La suppression de l\'élément a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft .'. (site: '. $donnees['site_id'] .', drive: '. $donnees['drive_id'] .')', $exception);
            return false;
        }

		return true;
	}

	/*
	 *
	 * Renvoie le fichier pour affichage dans la bibliothèque
	 *
	 */
	private function prepare_fichier_pour_bibliotheque($modele) {

		$fichier = (object)(['modele' => $modele]);
		$fichier->nom = $modele->getName();
		$fichier->nom_original = $modele->getName();
		$fichier->poids = $modele->getSize();
		$fichier->sharepoint = true;
		$fichier->dimensions = 0;
		$fichier->id = $modele->getId();
		$fichier->chemin = $modele->getWebUrl();
        $extension = explode('.', $fichier->nom);
        $fichier->extension = $extension[array_key_last($extension)];
        $fichier->stockage_externe = 1;
        $fichier->est_un_fichier = false;

        if(empty($modele->getFolder()))
            $fichier->est_un_fichier = true;

        if(empty($fichier->poids))
            $fichier->poids = '-';
        elseif($fichier->poids > 1000000)
            $fichier->poids = round($fichier->poids / 1000000, 2) . ' Mo';
        elseif($fichier->poids > 1000)
            $fichier->poids = round($fichier->poids / 1000) . ' Ko';
        else
            $fichier->poids = round($fichier->poids) . ' Octets';

        return $fichier;
	}

    /*
     *
     * Vérifie si le dossier du type_element indiqué existe bien sur Sharepoint
     *
     */
    protected function verification_existence_dossier_type_element($type_element){

        //On vérifie si on doit synchroniser l'élément
        $verification_type_element_mappage = modele('parametrage_mappage_sharepoint')
            ->where('type_element', $type_element)
            ->first();

        if(empty($verification_type_element_mappage))
            return false;

        $id_dossier_type_element = parametre($type_element.'_synchro_sharepoint');

        if(isset($id_dossier_type_element))
            $dossier_type_element = $this->recuperer_dossier(parametre($type_element.'_synchro_sharepoint'), 1);
        else
            $dossier_type_element = $this->creer_dossier($verification_type_element_mappage->nom_dossier);

        if(isset($id_dossier_type_element) && is_a($dossier_type_element, 'Exception'))
            $dossier_type_element = $this->creer_dossier($verification_type_element_mappage->nom_dossier);
        elseif(is_a($dossier_type_element, 'Exception'))
            $dossier_type_element = $this->recuperer_dossier_avec_nom($verification_type_element_mappage->nom_dossier);
        elseif(!isset($id_dossier_type_element))
            parametre($type_element.'_synchro_sharepoint', $dossier_type_element->getId());

        return $dossier_type_element->getId();
    }

    /*
     *
     * Permet de vérifier l'existence du dossier type_element et de l'element passés en paramètres
     * Retourne l'id microsoft du dossier de la fiche consultée
     *
     */
    public function verifier_existence_dossiers_elements_fiche($type_element, $element_id, $type_document = false, $verifier_type_element = true){

        $type_element_choisi = '';
        $id_element_choisi = '';

        // On vérifie s'il n'y a pas déjà un dossier en bdd qui existe dans sharepoint
        $id_dossier_bdd = $this->verifier_existence_dossier_bdd($type_element, $element_id);

        if(!empty($id_dossier_bdd))
            return $id_dossier_bdd;

        //Les documents sont gérés différemment, les dossiers des documents sont créés dans le dossier d'un client ou d'un fournisseur donc plus d'étapes à gérer
        if($verifier_type_element && !empty($type_document))
            $id_dossier_type_element = $this->verification_existence_dossiers_type_document($type_element, $element_id, $type_document);
        else if($verifier_type_element && empty($type_document))
            $id_dossier_type_element = $this->verification_existence_dossier_type_element($type_element);
        else if(!empty($type_document) && empty($verifier_type_element)) {

            $document = modele($type_element)->where('id', $element_id)->first();

            if($type_document == 'vente'){

                $type_element_choisi = 'client';
                $id_element_choisi = $document->client_id;
            } else if ($type_document == 'achat'){

                $type_element_choisi = 'fournisseur';
                $id_element_choisi = $document->fournisseur_id;
            }


            $id_dossier_type_element = modele('bibliotheque_synchro_elements')
                ->where('stockage_externe', 1)
                ->where('type_element', $type_element_choisi)
                ->where('element_id', $id_element_choisi)
                ->where('type_document', $type_element)
                ->whereNull('document_id')
                ->first();

            $id_dossier_type_element = $id_dossier_type_element->id_dossier;
        }
        else
            $id_dossier_type_element = parametre($type_element.'_synchro_sharepoint');

        //On récupère l'élément pour lequel il faut créer le dossier
        $element_a_creer = modele($type_element)->where('id', $element_id)->first();

        return $this->creer_element_fiche($type_element, $element_a_creer, $id_dossier_type_element, $type_element_choisi, $id_element_choisi);
    }

    protected function creer_element_fiche($type_element, $element, $dossier, $type_element_choisi = null, $id_element_choisi = null){

        $retour_element_cree = $this->creer_dossier($this->nom_dossier_element($type_element, $element), $dossier);

        //Si la création du dossier échoue, c'est qu'il existe, sinon il faut enregistrer les infos en bdd
        if(is_a($retour_element_cree, 'Exception')){

            $dossier_element = modele('bibliotheque_synchro_elements')
                ->where('type_element', $type_element)
                ->where('stockage_externe', 1)
                ->where('element_id', $element->id)
                ->first();

            $id_dossier = $dossier_element->id_dossier;
        }
        else {

            $id_dossier = $retour_element_cree->getId();

            //Si un dossier a déjà été créé pour cet élément par le passé et qu'il a été recréé (s'il a été supprimé dans Sharepoint)
            // on supprime l'ancienne entrée de la bdd pour éviter de faire des doublons
            modele('bibliotheque_synchro_elements')
                ->where('stockage_externe', 1)
                ->where('type_element', $type_element)
                ->where('element_id', $element->id)
                ->delete();

            if(!empty($type_document))
                management('bibliotheque_synchro_elements')->enregistre([
                    'type_element' => $type_element_choisi,
                    'element_id' => $id_element_choisi,
                    'type_document' => $type_element,
                    'document_id' => $element->id,
                    'id_dossier' => $id_dossier,
                    'stockage_externe' => 1,
                ]);
            else
                management('bibliotheque_synchro_elements')->enregistre([
                    'type_element' => $type_element,
                    'element_id' => $element->id,
                    'id_dossier' => $id_dossier,
                    'stockage_externe' => 1,
                ]);
        }

        return $id_dossier;
    }

    protected function nom_dossier_element($type_element, $element){
        
        return $this->gestion_caracteres_invalides_nom_dossier($element->id . ' - ' . $element->chaine_affichage);
    }

    protected function gestion_caracteres_invalides_nom_dossier($nom_dossier) {

        $nom_dossier = str_replace(['`', '"', '*', ':', '<', '>', '?', "\t", "\n", "\r"], ' ', $nom_dossier);

        return preg_replace('/[ ]{2,}/', ' ', $nom_dossier);
    }

    /*
     *
     * Permet de vérifier l'existence du dossier type_element et de l'element passés en paramètres.
     * Dans le cas des documents, il y a des étapes supplémentaires : les dossiers des types de document sont soit
     * contenus dans un client soit dans un fournisseur, il faut donc vérifier avant si le dossier du client concerné est créé
     * Retourne l'id microsoft du dossier de la fiche consultée
     *
     */
    public function verification_existence_dossiers_type_document($type_element, $element_id, $type_document = false){

        //On récupère le document pour savoir quel client/fournisseur on doit vérifier
        $document = modele($type_element)->where('id', $element_id)->first();

        $verification_document_mappage = modele('parametrage_mappage_sharepoint')
            ->where('type_element', $type_element)
            ->first();

        if(empty($verification_document_mappage))
            return false;

        if($type_document == 'vente') {

            $element_a_creer_id = $document->client_id;
            $type_element_a_creer = 'client';

            $id_dossier_choisi = $this->verifier_existence_dossiers_elements_fiche($type_element_a_creer, $element_a_creer_id);
        }
        else if($type_document == 'achat') {

            $element_a_creer_id = $document->fournisseur_id;
            $type_element_a_creer = 'fournisseur';

            $id_dossier_choisi = $this->verifier_existence_dossiers_elements_fiche($type_element_a_creer, $element_a_creer_id);
        }

        $dossier_type_document = $this->creer_dossier($verification_document_mappage->nom_dossier, $id_dossier_choisi);

        //Si la création du dossier échoue, c'est qu'il existe, sinon il faut enregistrer les infos en bdd
        if(is_a($dossier_type_document, 'Exception')){

            $dossier_type_document = modele('bibliotheque_synchro_elements')
                ->where('type_element', $type_element_a_creer)
                ->where('stockage_externe', 1)
                ->where('element_id', $element_a_creer_id)->where('type_document', $type_element)
                ->whereNull('document_id')
                ->first();

            $id_dossier_type_document = $dossier_type_document->id_dossier;
        }
        else {

            management('bibliotheque_synchro_elements')->enregistre([
                'type_element' => $type_element_a_creer,
                'element_id' => $element_a_creer_id,
                'type_document' => $type_element,
                'id_dossier' => $dossier_type_document->getId(),
                'stockage_externe' => 1,
            ]);

            $id_dossier_type_document = $dossier_type_document->getId();
        }

        return $id_dossier_type_document;
    }

    public function verifier_existence_dossier_bdd($type_element, $element_id){

        if(in_array($type_element, Variables::$documents_gescom))
            $dossier_element = modele('bibliotheque_synchro_elements')
                ->where('stockage_externe', 1)
                ->where('type_document', $type_element)
                ->where('document_id', $element_id)
                ->first();
        else
            $dossier_element = modele('bibliotheque_synchro_elements')
                ->where('stockage_externe', 1)
                ->where('type_element', $type_element)
                ->where('element_id', $element_id)
                ->first();

        if(empty($dossier_element))
            return false;

        $dossier_sharepoint = $this->recuperer_dossier($dossier_element->id_dossier);

        if(is_a($dossier_sharepoint, 'Exception'))
            return false;

        return $dossier_sharepoint->id;
    }

    /*
     *
     * Permet de mettre à jour le nom du dossier Sharepoint pour un élément après la régénération de la colonne chaine_affichage
     *
     */
    public function changer_nom_dossier_apres_regeneration_chaine_affichage($type_element, $element){

        if(empty($type_element) || empty($element))
            return false;

        if(!empty(moi()->id_microsoft) && fonctionnalite('sharepoint_utiliser_synchronisation') === true){

            $dossier_existant = modele('bibliotheque_synchro_elements')
                ->where('stockage_externe', 1)
                ->where('type_element', $type_element)
                ->where('element_id', $element->id)
                ->first();

            if(!empty($dossier_existant))
                $this->modifier_dossier($this->nom_dossier_element($type_element, $element), $dossier_existant->id_dossier);
        }
    }
}
