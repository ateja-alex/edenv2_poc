<?php

namespace App\Eden\Managements;

use App\Eden\Models\Element_image;
use App\Eden\Models\Element_log;
use App\Eden\Models\Element_piece_jointe;
use App\Eden\Models\Eden_fiche_liens;
use App\Eden\Models\Colonne;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Formulaire;

use App\Eden\Managements\Rapports\Rapports_management;

use App\Eden\Variables;
use App\Eden\Managements\Cache_management;
use Illuminate\Support\Str;

use Storage;

/**
 * Gestion des fiches
 */
class Fiche_management {

    private $infos_synchro_externe = array();

    /**
	 *
	 * Prépare la structure de la fiche
	 *
	 */
	public function structure_fiche($donnees = array()) {

		if(!empty(moi_extranet()) && file_exists(storage_path('app/eden_fiche_'.$this->type_element.'_extranet.php')))
			$structure = include(storage_path('app/eden_fiche_'.$this->type_element.'_extranet.php'));
		else if(file_exists(storage_path('app/eden_fiche_'.$this->type_element.'.php')))
			$structure = include(storage_path('app/eden_fiche_'.$this->type_element.'.php'));
		else
			$structure = $this->structure_fiche_par_defaut();

        $structure = $this->gestion_licence($structure);

		return $structure;
	}

    /**
	 *
	 * Retourne la structure par défaut de la fiche
     *
	 * !! La Methode est en final pour NE PAS être herité et ne plus mettre dans le code les fiches par defaut
	 */
    final function structure_fiche_par_defaut() {
        if(file_exists(app_path("Eden/Config/fiches/eden_fiche_$this->type_element.php")))
            return include app_path("Eden/Config/fiches/eden_fiche_$this->type_element.php");

        return [
            'modules' => [
                [
                    'module' => 'formulaire_edition_element',
                    'afficher_par_defaut' => true,
                    'taille_avant' => 0,
                    'taille' => 12,
                    'taille_apres' => 0,
                ]
            ]
        ];
	}

	/**
	 *
	 * On récupère la liste des modules utilisés
	 *
	 */
	public function modules_utilises() {

		$structure = $this->structure_fiche(['sans_traitement' => true]);

		$modules = array();

		// pour la fiche classique
		if(isset($structure['modules'])) {

			foreach($structure['modules'] as $info_structure) {

				if(isset($info_structure['module'])) {

					$modules[] = $info_structure['module'];
					continue;
				}

				foreach($info_structure as $info_structure_tmp) {
					foreach($info_structure_tmp['modules'] as $info_structure_niveau_2) {

						if(isset($info_structure_niveau_2['module']))
							$modules[] = $info_structure_niveau_2['module'];
					}
				}
			}
		}

		// pour la colonne de droite
		if(isset($structure['colonne_droite'])) {

			foreach($structure['colonne_droite'] as $info_structure) {

				if(isset($info_structure['module']))
					$modules[] = $info_structure['module'];
			}
		}

		return $modules;
	}

	/**
	 *
	 * Récupère le nom du module pour les onglets
	 *
	 */
	public function recupere_nom_module($module) {

		if(in_array($module,array(
                'formulaire_edition_element',
                'pieces_jointes',
                'commentaires',
                'messages',
                'calendrier',
                'affichage_calendrier',
                'abonnement_fiche',
                'historique',
                'timeline',
                'formulaire_affichage_element',
                'annuaire_facturation',
            )
        )){
            return 'module_sur_fiche.'.$module;
        }

		// un formulaire libre
		if(\App\Eden\Models\Formulaire::where('nom_formulaire', $module)->first() !== null) {

			$formulaire = \App\Eden\Models\Formulaire::where('nom_formulaire', $module)->first();

            return $formulaire->index_traduction.'.titre';
        }

		// une liste libre
		if(\App\Eden\Models\Liste_libre::where('id_rapport', $module)->first() !== null) {

			$liste_libre = \App\Eden\Models\Liste_libre::where('id_rapport', $module)->first();
            $rapport_libre_lie = \App\Eden\Models\Rapport_libre::where('id_rapport', $module)->first();

            if(!empty($rapport_libre_lie))
                return $rapport_libre_lie->index_traduction . '.titre';

            $rapport = Rapport_libre::where('id_rapport', $module)->first();

            if($rapport !== null)
                return $rapport->index_traduction.'.titre';

            return table_libre($liste_libre->type_element)->index_traduction.'.nom_table';
		}

		// on n'a pas trouvé le nom pour l'instant
		return 'module_sur_fiche.'.$this->type_element.'.'.$module;
	}

    /**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		// on rajoute quelques données pour toutes les fiches
		$donnees['type_element'] = $this->type_element;
		$donnees['id_element'] = $this->id_element;
		$donnees['management_fiche'] = $this;
		$donnees['management_element'] = management($this->type_element, $this->id_element);
        $donnees['management_element']->retraite_modele_recuperation();
		$donnees['management_element']->charge_valeurs_champs_multiselection();
        $donnees['modification_possible'] = profil_modification($this->type_element, $donnees['management_element']->modele->entite_id ?? null, $donnees['management_element']->modele);

		$donnees[$this->type_element] = $donnees['management_element']->modele;

        $this->rapports_fiche = Rapport_libre::where('type_element_fiche', $this->type_element)->get()->keyBy('id_rapport');
        $donnees['presence_carte'] = !empty($this->rapports_fiche->where('type_rapport', 'carte'));

		// on récupère les modules utilisés pour optimiser
		$modules = $this->modules_utilises();

        $donnees['images'] = $this->recupere_images($this->type_element, $this->id_element);

        if(in_array('logo', $modules)) {
            $donnees['champ_logo'] = $this->recupere_logo($donnees);
        }

		if(in_array('liste_adresses', $modules)) {

			$donnees['adresses'] = $this->adresses();

			$colonne_existe = modele('adresse')->getConnection()
			   ->getSchemaBuilder()
			   ->hasColumn(modele('adresse')->getTable(), $this->type_element.'_id');

			// Cas standard
			if($colonne_existe)
				$donnees['element_standard'] = true;
			else
				$donnees['element_standard'] = false;
		}

		$formulaire = Formulaire::where('nom_formulaire','fiche_'.$this->type_element)->first();

		if($formulaire != null)
			$donnees['formulaire_id'] = $formulaire->id;
		else
			$donnees['formulaire_id'] = null;

        $donnees['blocs_impression_fiche'] = $this->blocs_impression_fiche();

        // on ajoute les indicateurs
        $donnees = $this->ajouter_indicateurs($donnees);

        // on retraite les données si nécessaire
		$donnees = $this->traiter_donnees($donnees);

        // on va chercher les listes enfants (générique)
		$donnees = $this->listes_enfants($donnees);

		// on va chercher la structure de la fiche
		$donnees['structure'] = $this->structure_fiche($donnees['structure'] ?? []);

        $retour = $this->listes_sur_fiche($donnees['structure']);

        $donnees['listes_sur_fiche'] = $retour['listes_sur_fiche'] ?? [];
        $donnees['watch_pour_vuejs'] = $retour['watch_pour_vuejs'] ?? [];

        $donnees['rapports_sur_fiche'] = $this->rapports_fiche;
        $rapports_liste_libre = $this->rapports_fiche->where('type_rapport', 'liste_libre');
        $listes_libres = Liste_libre::whereIn('id_rapport',array_keys($rapports_liste_libre->toArray()))->get()->keyBy('id_rapport');

        foreach ($rapports_liste_libre as $id_rapport => $rapport) {
            if(!empty($listes_libres[$id_rapport]))
                $donnees['rapports_sur_fiche'][$id_rapport]['rapport_liste_libre'] = $listes_libres[$id_rapport];
        }

        $donnees['onglet_par_defaut'] = config('fonctionnalites')['fiche_client_onglet_par_defaut'];

        $donnees['fil_ariane'] = $this->fil_ariane($donnees);
        $donnees['options_fil_ariane'] = $this->options_fil_ariane_filtres($donnees);

		return $donnees;
	}

	/**
	 *
	 * Méthode pour générer une liste sur la fiche
	 *
	 */
	public function liste($type_element, $filtres) {

		$management = new Listes_management();

		// on va chercher la liste libre
		$liste = Liste_libre::where('id_rapport', $this->type_element.'_'.$type_element)->first();

		// on n'a pas trouvé la liste libre
		if($liste === null)
			throw new \App\Eden\Exceptions\Eden_exception("Aucune liste libre n'a été créée pour le type élément ".$this->type_element."_$type_element. Avez vous pensé à bien la créer dans Elements_libres ou via l'interface ?");

		$id_liste = $liste->id;

		// on va chercher les paramètres enregistrés
		$parametres = Rapports_management::recupere_parametres('liste_'.$id_liste);

		// on réinitialise la recherche
		if(isset($parametres['recherche']) && !empty($parametres['recherche']))
			$parametres['recherche'] = null;

		if(!isset($parametres['filtres']))
			$parametres['filtres'] = array();

		// on applique les filtres pour la liste
		foreach($filtres as $nom_filtre => $filtre) {

			$parametres['filtres'][$nom_filtre] = $filtre;
		}

		// on récupère la liste
		$donnees = $management->recupere_liste($id_liste, $parametres, 10);

		// pour passer les infos à la vue
		$donnees['filtres_sur_fiche'] = $filtres;

		$donnees['table_libre'] = table_libre($type_element);

		$donnees['id_liste'] = $id_liste;

		$donnees['champs_piece_jointe'] = table_libre($type_element)->champs_libres()->where('type', 7)->get();
		$donnees['champs_multi_selection'] = table_libre($type_element)->champs_libres()->where('type', 10)->get();

		$donnees['modele_par_defaut'] = modele_par_defaut($type_element);

		return $donnees;
	}

	/**
	 *
	 * On rassemble tout en un seul tableau pour la partie commerce sur la fiche
	 *
	 */
	protected function prepare_donnees_commerce($documents) {

		return $this->prepare_donnees_commerce_avec_tri($documents);
	}

	/**
	 *
	 * On rassemble tout en un seul tableau pour la partie commerce sur la fiche
	 *
	 */
	protected function prepare_donnees_commerce_avec_tri($documents, $tri = 'date', $direction = 'desc') {

		$commerce = array();

		$management_facture = management('facture_vente');

		// on fait un tableau "merge"
		foreach($documents as $type_element => $liste_documents) {

			if($liste_documents === null)
				continue;

			foreach($liste_documents as $document) {

				$client = '';

				if($this->type_element == 'article') {

					$client = $management_facture->champ('client_id')->affiche($document->client_id);
				}

				$commerce[] = array(

					'type_element' => $type_element,
					'nom_element' => ucfirst(table_libre($type_element)->element),
					'modele' => $document,
					'client' => $client,
					'date' => $document->date,
					'valide' => $document->valide,
					'accepte' => $document->accepte,
					'regle' => $document->regle,
					'date_fr' => formate_date('d/m/Y', $document->date),
					'lien_vers_document' => route('document.afficher', [$type_element, $document->id]),
				);
			}
		}

		// on le trie par date
		usort($commerce, function($a, $b) use ($tri, $direction) {

			if ( isset($a[$tri]) ) {

				$aa = $a[$tri] ;
				$bb = $b[$tri] ;
			} else {

				$aa = $a['modele'][$tri] ;
				$bb = $b['modele'][$tri] ;
			}

			// Si direction == false, on échange les variables pour inverser l'ordre de tri
			if ($direction == 'false')
				list($aa, $bb) = array($bb, $aa);

			if( $aa > $bb )
				return -1;
			elseif( $aa == $bb )
				return 0;
			else
				return 1;
		});

		return $commerce;
	}

	/**
	 *
	 * Destiné à être surchargé pour retraiter les données
	 *
	 */
	public function traiter_donnees($donnees) {

		return $donnees;
	}

	/**
	 *
	 * Va chercher les listes enfants
	 *
	 */
	public function listes_enfants($donnees) {

		// quelles sont les listes enfants ?
		$listes = Eden_fiche_liens::where('type_element_parent', $this->type_element)->get();

		$donnees['listes_enfants'] = array();

		if(empty($listes))
			return $donnees;

		// on traite chaque liste
		foreach($listes as $liste) {

			$donnees['listes_enfants'][$liste->type_element_enfant] = $this->liste_elements_enfants($this->type_element, $liste->type_element_enfant, $this->id_element);

		}

		return $donnees;
	}

	public function liste_elements_enfants($type_element_parent, $type_element_enfant, $id_element_parent) {

		$liste = Eden_fiche_liens::where('type_element_parent', $type_element_parent)->where('type_element_enfant', $type_element_enfant)->first();

		// on va chercher les données
		$donnees_liste = modele($type_element_enfant)->where($liste->cle_etrangere, $id_element_parent)->get();

		$infos = array(

			'titre' => table_libre($type_element_enfant)->nom_table,
			'type_element' => $type_element_enfant,
			'cle_etrangere' => $liste->cle_etrangere,
			'colonnes' => array(),
			'donnees' => array(),
			'donnees_liste' => $donnees_liste,
		);

		// on va chercher les colonnes de la liste
		$colonnes = Colonne::where('liste_libre_id', $liste->liste_id)->orderBy('ordre')->get();

		$colonne_id = false;

		foreach($colonnes as $id => $colonne) {

			if($colonne->nom == '#')
				$colonne_id = $id;

			$infos['colonnes']['colonne_'.$id] = $colonne->nom;
		}

		if(empty($donnees_liste))
			return $infos;

		// on va chercher les champs libres
		$champs_libres = table_libre($type_element_enfant)->champs_libres()->get();

		foreach($donnees_liste as $donnees_element) {

			$champs = array();
			$management = management($type_element_enfant, $donnees_element->id);

			foreach($champs_libres as $champ_libre) {

				$champs['#'.$champ_libre->nom_sql.'#'] = $management->champ($champ_libre->nom_sql)->affiche();
			}

			$donnees_ligne = array();

			foreach($colonnes as $id => $colonne) {

				$donnees_ligne['colonne_'.$id] = str_replace(array_keys($champs), $champs, $colonne->valeur);
			}

			if($colonne_id !== false)
				$donnees_ligne['colonne_'.$colonne_id] = $donnees_element->id;

			$donnees_ligne['id'] = $donnees_element->id;

			$infos['donnees'][] = $donnees_ligne;
		}

		return $infos;
	}

	/**
	 *
	 * Retourne les adresses liées à l'élément
	 *
	 * @return collection
	 *
	 */
	public function adresses() {

		$colonne_existe = modele('adresse')->getConnection()
           ->getSchemaBuilder()
           ->hasColumn(modele('adresse')->getTable(), $this->type_element.'_id');

        // Cas standard
        if ($colonne_existe)
			$adresses = modele('adresse')->where($this->type_element.'_id', $this->id_element)->get();

		// Cas dynamique
		else
			$adresses = modele('adresse')->where('type_element', $this->type_element)->where('element_id', $this->id_element)->get();

		if($adresses === null)
			return collect(array());

		return collect($adresses);
	}

	/**
	 *
	 * Retourne les contacts liés à l'élément
	 *
	 * @return collection
	 *
	 */
	public function contacts() {

        $contacts = management('contact')->recuperer_contact_element($this->type_element, $this->id_element,array('npai'));

        if($contacts === null)
			return collect(array());

		return collect($contacts);
	}


	/**
	*
	* Permet d'enregistrer un logo pour une fiche
	*
	*/
	public function enregistre_logo($formulaire, $type_element, $id_element) {

		$chemin_enregistrement = $formulaire->file('logo')->store('logos', 's3');

		// on enregistre sur le modèle
		$management = management($type_element, $id_element);

		$management->enregistre_modele(array('logo' => $chemin_enregistrement));

		return true;
	}

	/**
	 *
	 * Récupère les images liées à une fiche via Element_image
	 *
	 */
	public function recupere_images($type_element, $id_element) {

		$a_retourner = array();

		$images = Element_image::where('type_element', $type_element)->where('element_id', $id_element)->orderBy('ordre')->get();

		if($images === null)
			return $a_retourner;


		foreach($images as $image) {


			$nom_fichier = $image->chemin;

			// On récupère le nom du fichier
			// On recupère le contenu du  fichier sur aws.
			$storage = \Storage::disk('local');

			// le str_replace c'est pour corriger un vieux bug de l'erp qui date du début, ne pas retirer
			$nom_fichier = str_replace('uploads', '', $image->chemin);

			$basename = basename($nom_fichier);

			$image->url_sur_serveur = 'storage/'.$basename;

			if ( !$storage->exists('public/'.$nom_fichier) )
				return ;

			$image->poids = $storage->size('public/'.$nom_fichier);

			if($image->poids > 1000000)
				$image->poids = round($image->poids / 1000000, 2) .' Mo';
			elseif($image->poids > 1000)
				$image->poids = round($image->poids / 1000) .' Ko';

			$image->extension = pathinfo($nom_fichier, PATHINFO_EXTENSION);

			list($width, $height) = getimagesize(storage_path('app/public/'.$basename));

			$image->width = $width;
			$image->height = $height;

			$a_retourner[] = $image;
		}

		return $a_retourner;
	}

	/**
	 *
	 * Récupère les pieces jointes liées à une fiche via Element_piece_jointe
	 *
	 */
	public function recupere_pieces_jointes($type_element, $id_element,$id_dossier_parent = null) {

		$a_retourner = array();
        $this->infos_synchro_externe();

        if($id_dossier_parent==0)
            $id_dossier_parent=null;

        if(isset($this->infos_synchro_externe['type']) && $this->infos_synchro_externe['type'] === 'gdrive') {

	        $id_dossier_parent = table_libre($type_element)->synchro_bibliotheque;

	        // Si le paramètre de provider bibliothèque est à GDrive,
	        // Et si le dossier de la table libre est paramétré
	        if(!empty($id_dossier_parent) ) {

        		$gdrive = service('google');
	        	$dossier_element = modele('bibliotheque_synchro_elements')->where('type_element', $type_element)->where('element_id', $id_element)->first();

	        	// Si le dossier de l'élément n'existe pas dans bibliotheque_synchro_elements, on le crée
	        	if(empty($dossier_element)) {

	        		$nom_dossier 	= management($type_element, $id_element)->affiche();
	        		$dossier 		= $gdrive->cree_dossier_drive($nom_dossier, $id_dossier_parent);
	        		$id_dossier 	= $dossier->id;

	        		management('bibliotheque_synchro_elements')->enregistre([
				        														'type_element' => $type_element,
				        														'element_id' => $id_element,
																				'id_dossier' => $id_dossier,
																			]);

		        } else
		        	$id_dossier = $dossier_element->id_dossier;

	        	list($fichiers_gdrive, $dossiers_gdrive) = $gdrive->recupere_fichiers_drive($id_dossier);

	        	return $fichiers_gdrive;
	        }
	    }
        if(!empty(moi()->id_microsoft) && isset($this->infos_synchro_externe['type'], $this->infos_synchro_externe['mappage']) &&
            $this->infos_synchro_externe['type'] === 'sharepoint' && in_array($type_element, $this->infos_synchro_externe['mappage'])) {

            $type_document = false;
            $service_sharepoint = service('microsoft_sharepoint');

            if(strpos($this->type_element, '_vente') !== false)
                $type_document = 'vente';
            else if (strpos($this->type_element, '_achat') !== false)
                $type_document = 'achat';

            $id_dossier_element_sharepoint = $service_sharepoint->verifier_existence_dossiers_elements_fiche($type_element, $id_element, $type_document);

            if(empty($id_dossier_element_sharepoint))
                return collect();

            $fichiers_sharepoint = $service_sharepoint->recuperer_contenu_dossier($id_dossier_element_sharepoint)[0];

            return collect($fichiers_sharepoint);
        }

		$pieces_jointes = Element_piece_jointe::where('type_element', $type_element)->where('element_id', $id_element)->orderby('ordre');

        if(!empty(moi_extranet()))
            $pieces_jointes->where('disponible_extranet', 1);
        
        if((!isset($this->infos_synchro_externe['type']) || $this->infos_synchro_externe['type'] !== 'mfiles') && empty($this->infos_synchro_externe['jeton_authentification']))
            $pieces_jointes = $pieces_jointes->where('dossier_parent',$id_dossier_parent);

        $pieces_jointes = $pieces_jointes->get();
		if($pieces_jointes === null)
			return $a_retourner;

		foreach($pieces_jointes as $piece_jointe) {

            $piece_jointe = $this->prepare_piece_jointe_affichage($piece_jointe);
						$nom_fichier = $piece_jointe->chemin;

			$a_retourner[] = $piece_jointe;
		}

		return $a_retourner;
	}

    public function prepare_piece_jointe_affichage($piece_jointe){

        if($piece_jointe->stockage_externe == 2){

            $piece_jointe->extension = explode('.', $piece_jointe->nom);
            $piece_jointe->extension = $piece_jointe->extension[array_key_last($piece_jointe->extension)];
            $piece_jointe->poids = 'Stocké dans M-Files';

            return $piece_jointe;
        }

        $nom_fichier = $piece_jointe->chemin;

        $piece_jointe->url_sur_serveur = 'public/'.$nom_fichier;

        $infos_pj = pathinfo(asset('public/'.$nom_fichier));

        if(!isset($infos_pj['extension']))
            $piece_jointe->extension = '???';
        else
            $piece_jointe->extension = $infos_pj['extension'];

        if (is_file(storage_path('app/public/'.$nom_fichier)))
            $piece_jointe->poids = round(filesize(storage_path('app/public/'.$nom_fichier)) / 1000) .' Ko';
        else
            $piece_jointe->poids = 'fichier non présent';

        return $piece_jointe;
    }

    /**
     *
     * Récupère les dossiers d'une fiche
     *
     */
    public function recupere_dossiers($type_element, $id_element) {

        $this->infos_synchro_externe();

    	if(isset($this->infos_synchro_externe['type']) && $this->infos_synchro_externe['type'] === 'gdrive') {

	        $id_dossier_parent = table_libre($type_element)->synchro_bibliotheque;

	        // Si le paramètre de provider bibliothèque est à GDrive,
	        // Et si le dossier de la table libre est paramétré
	        if(!empty($id_dossier_parent) ) {

        		$gdrive = service('google');
	        	$dossier_element = modele('bibliotheque_synchro_elements')->where('type_element', $type_element)->where('element_id', $id_element)->first();

	        	// Si le dossier de l'élément n'existe pas dans bibliotheque_synchro_elements, on le crée
	        	if(empty($dossier_element)) {

	        		$nom_dossier 	= management($type_element, $id_element)->affiche();
	        		$dossier 		= $gdrive->cree_dossier_drive($nom_dossier, $dossier_parent);
	        		$id_dossier 	= $dossier->id;

	        		management('bibliotheque_synchro_elements')->enregistre([
				        														'type_element' => $type_element,
				        														'element_id' => $id_element,
																				'id_dossier' => $id_dossier,
																			]);

		        } else {
		        	$id_dossier = $dossier_element->id_dossier;
	        	}

	        	$dossiers_gdrive = $gdrive->recupere_fichiers_drive($id_dossier, true);

	        	// création automatique des dossiers communs
		        $dossiers = modele('dossier_bibliotheque')
		            ->where('dossier_parent','=',null)
		            ->where('type_element', $type_element)
		            ->WhereNull('element_id')
		            ->get();

		        // On crée les dossiers si nécessaire
		        $dossier_cree = false;
	        	foreach ($dossiers as $dossier) {

	        		// On parcourt les dossiers pour vérifier si les dossiers existent déjà ou s'il faut les créer
	        		$present = false;

	        		foreach ($dossiers_gdrive as $dossier_gdrive) {

	        			if($dossier_gdrive->nom == $dossier->nom)
	        				$present = true;
	        		}

	        		// Le dossier n'existe pas, on le crée
	        		if(!$present) {

	        			$gdrive->cree_dossier_drive($dossier->nom, $id_dossier);
	        			$dossier_cree = true;
	        		}
	        	}

	        	// Si on a créé des dossiers, on recharge la liste
	        	if($dossier_cree)
	        		$dossiers_gdrive = $gdrive->recupere_fichiers_drive($id_dossier, true);

	        	return collect($dossiers_gdrive);
	        }
	    }
        if(!empty(moi()->id_microsoft) && isset($this->infos_synchro_externe['type'], $this->infos_synchro_externe['mappage']) &&
            $this->infos_synchro_externe['type'] === 'sharepoint' && in_array($type_element, $this->infos_synchro_externe['mappage'])) {

            $sharepoint = service('microsoft_sharepoint');

            $type_document = false;

            if(strpos($this->type_element, '_vente') !== false)
                $type_document = 'vente';
            else if (strpos($this->type_element, '_achat') !== false)
                $type_document = 'achat';

            $id_dossier_element_sharepoint = $sharepoint->verifier_existence_dossiers_elements_fiche($type_element, $id_element, $type_document);

            if(empty($id_dossier_element_sharepoint))
                return collect();
            
            //Gestion des dossiers communs du type_element
            $dossiers_sharepoint = $sharepoint->recuperer_contenu_dossier($id_dossier_element_sharepoint)[1];
            $noms_dossiers = array();
            $dossier_cree = false;
            $dossiers_communs = modele('dossier_bibliotheque')
                ->where('dossier_parent','=',null)
                ->where('type_element', $type_element)
                ->WhereNull('element_id')
                ->get();

            foreach($dossiers_sharepoint as $dossier)
                $noms_dossiers[] = $dossier->nom;

            // On crée les dossiers si nécessaire
            foreach ($dossiers_communs as $dossier) {

                if(in_array($dossier->nom, $noms_dossiers))
                    continue;

                $sharepoint->creer_dossier($dossier->nom, $id_dossier_element_sharepoint);
                $dossier_cree = true;
            }

            // Si on a créé des dossiers, on recharge la liste
            if($dossier_cree)
                $dossiers_sharepoint = $sharepoint->recuperer_contenu_dossier($id_dossier_element_sharepoint)[1];

            return collect($dossiers_sharepoint);
        }


        $dossiers = modele('dossier_bibliotheque')
            ->where('dossier_parent','=',null)
            ->where('type_element', $type_element)
            ->where(function($r) use ($id_element) {
                $r->where('element_id', $id_element)->orWhereNull('element_id');
            })
            ->get();

        foreach ($dossiers as &$dossier){

            $dossier->nombre_de_fichiers_enfants = Element_piece_jointe::where('type_element', $type_element)->where('element_id', $id_element)->where('dossier_parent', $dossier->id)->get()->count();

        }

        return $dossiers;
    }

    /**
     *
     * Récupère les documents d'une fiche
     *
     */
    public function recupere_documents_et_dossiers_fiche($type_element, $id_element) {

        $type_documents = Variables::$documents_vente_gescom;
        $fonctionnalite = fonctionnalite('pieces_jointes_documents_commerciaux_disponibles');

        $a_retourner = [];
        $dossiers = [];

        foreach($type_documents as $type_document) {

            if(empty($fonctionnalite[$type_document]))
                continue;

			// on regarde si la colonne existe
			$colonnes = bdd_colonnes($type_document);

			if(!in_array($type_element . '_id', $colonnes))
				continue;

            $documents = modele($type_document)->where($type_element . '_id', $id_element)->get()->toArray();

            if(! empty($documents)){

                $dossier = management('dossier_biblitoheque');

                $dossier->nom = table_libre($type_document)->element_pluriel;

                $dossier->nombre_de_fichiers_enfants = count($documents);

                $dossiers[] = $dossier;
            }

            foreach($documents as $document) {

                $pdf = management($type_document, $document['id'])->recupere_chemin_pdf();

                $document_final=new Element_piece_jointe;

                $document_final->element_id = $document['id'];

                $document_final->dossier_parent = $dossier->nom;

                $document_final->type_element = $type_document;

                $nom_fichier = $pdf;

                $document_final->chemin=$nom_fichier;

                $document_final->nom = pathinfo(asset($nom_fichier),PATHINFO_FILENAME );

                $document_final->titre = pathinfo(asset($nom_fichier),PATHINFO_FILENAME );

                $infos_pj = pathinfo(asset($nom_fichier));

                if(!isset($infos_pj['extension']))
                    $document_final->extension = '???';
                else
                    $document_final->extension = $infos_pj['extension'];

                if (is_file(storage_path('app/'.$nom_fichier)))
                    $document_final->poids = round(filesize(storage_path('app/'.$nom_fichier)) / 1000) .' Ko';
                else
                    $document_final->poids = 'fichier non présent';

                $a_retourner[] = $document_final;
            }
        }

        return ['documents'=>$a_retourner,'dossiers_documents'=>$dossiers];
    }

	/**
	 *
	 * Ajoute les indicateurs pour la fiche
	 *
	 * @param $donnees array, le tableau des données récupérées précédemment via informations_fiche()
	 *
	 * @return ce même tableau éventuellement modifié
	 *
	 */
	public function ajouter_indicateurs(&$donnees) {

		return $donnees;
	}

	/**
	 *
	 * Gestion de la pagination pour les données commerce
	 *
	 * @param $donnees array, le tableau des données préparées
	 * @param $page int, id de la page
	 *
	 * @return ce même tableau filtrer sur la page
	 *
	 */
	public function prepare_pagination_commerce($donnees,$page, $nb_elements) {

		$pagination_min = ($nb_elements * $page) - $nb_elements;
		$pagination_max = $nb_elements * $page;

		$count = 0;
		$nouvelles_donnees = array();

		foreach($donnees as $key => $donnee) {

			$count++;

			if($count > $pagination_min && $count <= $pagination_max)
				$nouvelles_donnees[$key] = $donnee;
		}

		return $nouvelles_donnees;
	}

	/**
	 *
	 * Gestion de la pagination pour d'autres données (projets)
	 *
	 * @param $donnees : collection
	 * @param $page : int, id de la page
	 * @param $nb_elements : int, nombre d'éléments à afficher
	 *
	 * @return collection avec uniquement les données souhaitées
	 *
	 */
	public function prepare_pagination($donnees,$page, $nb_elements) {

		return $donnees->slice(($page-1)*$nb_elements, $nb_elements);
	}

	/**
	 *
	 * L'historique du document pour la page de saisie
	 *
	 */
	public function historique($donnees) {

		// si on a un logo, on le télécharge d'aws
		if(!isset($donnees['management_element']))
			return false;

		// On va chercher l'ensemble de l'historique
		$historique = $donnees['management_element']->historique();

        $element_autre_table = array(
            'utilisateur' => array()
        );
		$utilisateur_ids = array();

		// On va chercher les utilisateurs associés aux éléments de l'historique
		foreach($historique as $element_historique) {

            if(!empty($element_historique->id_utilisateur))
                $element_autre_table['utilisateur'][] = $element_historique->id_utilisateur;

            if(!empty($element_historique->type_element_modificateur) && !empty($element_historique->element_id_modificateur)){

                $type = $element_historique->type_element_modificateur;

                if(!isset($element_autre_table[$type]))
                    $element_autre_table[$type] = array();

                $element_autre_table[$type][] = $element_historique->element_id_modificateur;
            }
		}

        $donnees_elements = array();

        foreach($element_autre_table as $element => $valeurs){

            $valeurs = array_unique($valeurs);

            $donnees_elements[$element] = modele($element)->whereIn('id', $valeurs)->get()->keyBy('id');
        }

		foreach($historique as $element_historique) {

			$type_element = null;
			$element_id = null;
			$element = null;

            if(!empty($element_historique->id_utilisateur)){
                $type_element = 'utilisateur';
                $element_id = $element_historique->id_utilisateur;
            }
            elseif(!empty($element_historique->type_element_modificateur) && !empty($element_historique->element_id_modificateur)){
                $type_element = $element_historique->type_element_modificateur;
                $element_id = $element_historique->element_id_modificateur;
            }

            if(isset($donnees_elements[$type_element][$element_id]))
                $element = $donnees_elements[$type_element][$element_id];

			$affichage_element = traduction('composant.historique.inconnu');

            $element_historique->avatar = null;

			if($element !== null) {

                if($type_element == 'utilisateur') {
                    $element_historique->avatar = $element->avatar;
                    $affichage_element = $element->prenom . " " . $element->nom;
                }
                else{
                    $nom_table = ucfirst(traduction('tables_libres.'.$type_element.'.element'));
                    $affichage_element = $nom_table .' : '.management($type_element, $element_id, $element)->affiche_lien();
                }
            }

			$element_historique->classe = Variables::$historique_intitule[$element_historique->type_action][0];
			$element_historique->intitule = $element_historique->intitule ?? Variables::$historique_intitule[$element_historique->type_action][1];
			$element_historique->date = date('d/m/Y H:i:s', strtotime($element_historique->date));
			$element_historique->nom = $affichage_element;
		}


		return $historique;
	}

	/**
	 *
	 * Permet de gérer l'accès aux fiches
	 *
	 * @return si l'utilisateur a le droit d'accéder à la fiche
	 *
	 */
	public function droit_acces_a_fiche() {

		if(!empty(moi_extranet())){

            $element = modele($this->type_element)->avec_filtre_extranet()->where('id', $this->id_element)->first();
            $table_libre = Table_libre::where('type_element',$this->type_element)->where('acces_extranet',1)->first();

            if($element == null || $table_libre == null)
                return false;
        }

		return true;
	}

	/**
     *
     * Envoie les blocs que l'on peut imprimer sur la fiche
     *
     */
	public function blocs_impression_fiche(){

        return array(
            'information_principale' => array(
                'nom' => "Information principale :",
                'blocs' => array(
                    'bloc_'.$this->type_element => 'Généralités',
                )
            )
        );
    }

    /**
     * @param $nom_module
     *
     * Fonction qui retourne si un module est utilisé pour une fiche
     *
     */
    public function presence_module($nom_module){

        $modules = $this->modules_utilises();

        return in_array($nom_module,$modules);
    }

		/**
     *
     * À surcharger si on veut réenregistrer l'image sous un autre format ou l'écraser en l'optimisant
     * Ex : Loueruneauto avec Shortpixel
     *
     */
    public function optimisation_image($image) {

        return true;
    }

    /**
     *
     * Prépare les données pour les fiches en mode campagne de prospection
     *
     */
    public function prepare_donnees_pour_fiche_pour_campagne($campagne_de_prospection_id,$flux_liste)
    {

        $donnees = $this->prepare_donnees_pour_fiche();

        $campagne_de_prospection_en_cours = modele('campagne_de_prospection', $campagne_de_prospection_id);
        // on ajoute les infos de la campagne de prospection
        $donnees['campagne_de_prospection_en_cours'] = $campagne_de_prospection_en_cours;

        $donnees['management_element']->campagne_en_cours = $campagne_de_prospection_en_cours;

		$campagne_de_prospection_element = modele('campagne_de_prospection_client')->where('client_id', $this->id_element)
            ->where('campagne_de_prospection_id', $campagne_de_prospection_id)
            ->first();

        $donnees['campagne_de_prospection_element'] = $campagne_de_prospection_element;

        $parametres_lancement_campagne = parametre_utilisateur('parametres_lancement_campagne_de_prospection_'.$campagne_de_prospection_id);

        if (empty($parametres_lancement_campagne) || $flux_liste === false)
            return $donnees;

        $parametres_lancement_campagne = json_decode($parametres_lancement_campagne, true);

        $liste_ordre = Liste_libre::find($parametres_lancement_campagne['id_liste']);

        $liste = liste($liste_ordre->type_element, $liste_ordre->id_rapport);

        $donnees_liste = $liste->recupere_liste($parametres_lancement_campagne['id_liste'], $parametres_lancement_campagne['parametres_liste']);

        if (!empty($donnees_liste['ids'])) {

            $ids = $donnees_liste['ids']->toArray();

            $index_id = array_search($this->id_element, $ids);

            if ($index_id !== false) {

                $donnees['campagne_lien_element_precedent'] = null;
                $donnees['campagne_lien_element_suivant'] = null;

                if ($index_id > 0) {
                    $element_id_precedent = $ids[$index_id - 1];
                    $donnees['campagne_lien_element_precedent'] = route('campagne_de_prospection.fiche_element', ['client', $element_id_precedent, $campagne_de_prospection_en_cours->id, true]);

                }

                if ($index_id < sizeof($ids) - 1) {
                    $element_id_suivant = $ids[$index_id + 1];
                    $donnees['campagne_lien_element_suivant'] = route('campagne_de_prospection.fiche_element', ['client', $element_id_suivant, $campagne_de_prospection_en_cours->id, true]);
                } else
                    $donnees['source_lancement_prospection'] = $parametres_lancement_campagne['source_lancement_prospection'];
            }

        }

        return $donnees;

    }

    /**
     *
     * Prépare les données des listes sur la fiche
     *
     */
    public function listes_sur_fiche($structure, $campagne_de_prospection_en_cours = false){

        $id_rapports = $this->instancie_listes($structure);

        $id_rapports_categorie_par_fiche = [];

        foreach ($id_rapports as $cle => $valeur) {
            foreach($valeur as $fiche) {
                $id_rapports_categorie_par_fiche[$fiche] = array_merge($id_rapports_categorie_par_fiche[$fiche] ?? [],[$cle]);
            }
        }

        $listes_libres = Liste_libre::whereIn('id_rapport', array_keys($id_rapports_categorie_par_fiche))->get()->keyBy('id_rapport');

        $listes_sur_fiche = [];
        $watch_pour_vuejs = [];

        foreach ($id_rapports_categorie_par_fiche as $id_rapport => $modules){

            if(empty($listes_libres[$id_rapport]))
                return exception("La liste libre avec l'id_rapport $id_rapport n'a pas été trouvée");

            $liste_libre = [];

            $liste_libre['type_element'] = $this->type_element;
            $liste_libre['liste_libre'] = $listes_libres[$id_rapport];
            $liste_libre['modele_par_defaut'] = modele_par_defaut($listes_libres[$id_rapport]->type_element);

            $liste_libre['cle_primaire'] = 'id';

            $liste_libre['v_model'] = !empty($this->document) ? 'document' : $this->type_element;

            if(!empty($liste_libre->cle_primaire))
                $liste_libre['cle_primaire'] = $listes_libres[$id_rapport]->cle_primaire;

            $liste_libre['cle_etrangere'] = $listes_libres[$id_rapport]->cle_etrangere;
            $liste_libre['type_element_primaire'] = $listes_libres[$id_rapport]->type_element_primaire;

            $type_element_pour_champ = $this->recupere_type_element_pour_champ($listes_libres[$id_rapport]);

            $champ_libre_cle = champ_libre($type_element_pour_champ, $listes_libres[$id_rapport]->cle_etrangere);

            if ($champ_libre_cle->modele->type == 22)
                $liste_libre['contenu_cle_etrangere'] = $champ_libre_cle->modele->contenu;

            if($liste_libre['cle_primaire'] != 'id'){
                $champ_libre_cle_primaire = champ_libre($this->type_element, $liste_libre['cle_primaire']);

                if ($champ_libre_cle_primaire->modele->type == 22)
                    $liste_libre['contenu_cle_primaire'] = $champ_libre_cle_primaire->modele->contenu;
                else if($champ_libre_cle_primaire->modele->type == 42 && $champ_libre_cle->modele->type == 22)
                    $liste_libre['type_element_primaire'] = $champ_libre_cle_primaire->modele->type_element_ajax;

            }

            if(!isset($watch_pour_vuejs[$liste_libre['cle_primaire']]))
                $watch_pour_vuejs[$liste_libre['cle_primaire']] = [];

            if(!empty($liste_libre['type_element_primaire']) && !empty($liste_libre['contenu_cle_primaire']))
                $watch_pour_vuejs[$liste_libre['cle_primaire']][$listes_libres[$id_rapport]->id] = ['type_element_primaire' => $liste_libre['type_element_primaire'], 'contenu_cle_primaire' => $liste_libre['contenu_cle_primaire']];

            $watch_pour_vuejs[$liste_libre['cle_primaire']][$listes_libres[$id_rapport]->id]['donnees_pour_vuejs'] = [
                'filtres_pour_fiche_' => [
                    $liste_libre['cle_etrangere']
                ],
                'modele_par_defaut_' => [
                    $liste_libre['cle_etrangere']
                ],
            ];

            // Cas spécial des fiches en mode campagne de prospection
            if($campagne_de_prospection_en_cours !== false){

                $champs_libres = champs_libres($listes_libres[$id_rapport]->type_element);

                foreach($champs_libres as $champ_libre){

                    if($champ_libre->type == 42 && $champ_libre->type_element_ajax == 'campagne_de_prospection') {
                        $liste_libre['champs_campagne_prospection'][] = $champ_libre->nom_sql;
                    }
                }
            }

            $this->structure_filtres_modele_par_defaut($liste_libre, $campagne_de_prospection_en_cours);

            foreach($modules as $module){
                $listes_sur_fiche[$module][$id_rapport] = $liste_libre;
            }
        }
        return ['listes_sur_fiche'=> $listes_sur_fiche, 'watch_pour_vuejs' => $watch_pour_vuejs];

    }

    public function structure_filtres_modele_par_defaut(&$liste_libre, $campagne_de_prospection_en_cours){
        $liste_libre['modele_par_defaut'] = [
            'dynamique' => 1,
            'cle' => $liste_libre['cle_etrangere'],
            'valeur' => $liste_libre['cle_primaire'],
        ];

        if (!empty($liste_libre['contenu_cle_etrangere'])) {
            $liste_libre['filtres_pour_fiche'] = [
                'dynamique' => 1,
                'cle' => $liste_libre['contenu_cle_etrangere'],
                'valeur' => [
                    'elements_ids' => [
                        [
                            'type_element' => $liste_libre['type_element_primaire'] ?: $liste_libre['type_element'],
                            'id' => $liste_libre['cle_primaire'],
                        ]
                    ],
                    'types_elements' => [
                        $liste_libre['type_element_primaire'] ?: $liste_libre['type_element']
                    ]
                ]
            ];

            $liste_libre['modele_par_defaut'] = [
                'dynamique' => 0,
                'cle' => $liste_libre['contenu_cle_etrangere'],
                'valeur' => $liste_libre['type_element'],
            ];
        } else {
            $liste_libre['filtres_pour_fiche'] = [
                'dynamique' => 1,
                'cle' => $liste_libre['cle_etrangere'],
                'valeur' => $liste_libre['cle_primaire'],
            ];
        }

        if (!empty($liste_libre['champs_campagne_prospection']) && $campagne_de_prospection_en_cours) {

            foreach ($liste_libre['champs_campagne_prospection'] as $nom_sql) {

                $liste_libre['filtres_pour_fiche'] = [
                    'dynamique' => 0,
                    'cle' => $nom_sql,
                    'valeur' => $campagne_de_prospection_en_cours->id
                ];

                $liste_libre['modele_par_defaut'] = [
                    'dynamique' => 0,
                    'cle' => $nom_sql,
                    'valeur' => $campagne_de_prospection_en_cours->id
                ];
            }
        }
    }

    /**
     * 
     * Récupère le type élément pour la fonction champ() dans listes_sur_fiche
     * 
     */
    public function recupere_type_element_pour_champ($liste_libre){
        return $liste_libre->type_element;
    }

    /**
     *
     * Filtre la structure de la fiche pour ne récupérer que les listes sur fiche
     *
     */
    public function instancie_listes($structure){

        $listes = [];

        foreach ($structure['modules'] as $module){

            if(!isset($module['module'])){

                foreach ($module as $onglets){

                    foreach ($onglets['modules'] as $sous_module) {

                        if ($this->modules_pour_fiche($sous_module['module']) === false)
                            $listes['unitaire'][] = $sous_module['module'];
                        else
                            $this->exception_listes_sur_fiches($listes,$sous_module['module']);

                    }

                }

                continue;

            }

            if($this->modules_pour_fiche($module['module']) === false)
                $listes['unitaire'][] = $module['module'];
            else
                $this->exception_listes_sur_fiches($listes,$module['module']);
        }

        if(!empty($structure['colonne_droite'])) {
            foreach ($structure['colonne_droite'] as $module) {

                if ($this->modules_pour_fiche($module['module']) === false)
                    $listes['unitaire'][] = $module['module'];
                else
                    $this->exception_listes_sur_fiches($listes, $module['module']);
            }
        }

        return $listes;

    }

    /**
     *
     * Retourne les exceptions des blocs sur fiches pour les listes sur ficher
     *
     */
    public function exception_listes_sur_fiches(&$listes,$nom_module){

        $prefixe_doc = 'fiche_'.$this->type_element.'_';

        if($nom_module == 'commerce') {

            $documents_actifs = Variables::documents_gescom_disponibles();

            $ordre = array_map(fn($type_document) => $prefixe_doc . $type_document, $documents_actifs);

            $listes_sur_fiches = Liste_libre::whereIn('id_rapport',$ordre)
            ->orderByRaw('FIELD(id_rapport, "' . implode('","', $ordre) . '")')
            ->pluck('id_rapport')->toArray();

            $listes['commerce'] = $listes_sur_fiches;
        }

        else if($nom_module == 'feuilles_de_temps') {
            $listes['unitaire'][] = $this->type_element . '_feuille_de_temps';
        }
        else if($nom_module == 'interventions') {
            $listes['unitaire'][] = 'fiche_'.$this->type_element . '_maintenance_intervention';
        }
        else if($nom_module == 'emails_recus') {
            $listes['unitaire'][] = 'fiche_'.$this->type_element . '_email_recus';
        }
        else if($nom_module == 'paiements') {
            $listes['unitaire'][] = 'fiche_'.$this->type_element . '_paiement';
        }
        else if($nom_module == 'cheque') {
            $listes['unitaire'][] = 'fiche_'.$this->type_element . '_paiement';
        }
        else if($nom_module == 'annuaire_facturation') {
            $listes['unitaire'][] = 'fiche_'.$this->type_element . '_annuaire_facturation';
        }
        else if($nom_module == 'commerce_lignes'){
            $documents_lignes_actifs = Variables::documents_gescom_lignes_disponibles();
            $ordre = array_map(fn($type_document) => $prefixe_doc . $type_document, $documents_lignes_actifs);
            $listes_sur_fiches_lignes = Liste_libre::whereIn('id_rapport',$ordre)
            ->orderByRaw('FIELD(id_rapport, "' . implode('","', $ordre) . '")')
            ->pluck('id_rapport')->toArray();

            $listes['commerce_lignes'] = $listes_sur_fiches_lignes;
        }
        else if($nom_module == 'cheque') {
            $listes['unitaire'][] = 'fiche_'.$this->type_element . '_paiement';
        }
    }

    /**
     *
     * Retourne la vue associé au module
     *
     */
    public function modules_pour_fiche($module){

        if(view()->exists('eden::fiches.include.'.$this->type_element.'.'.$module))
            return 'eden::fiches.include.'.$this->type_element.'.'.$module;
        else if(view()->exists('eden::fiches.include.'.$module))
            return 'eden::fiches.include.'.$module;
        else if($module == 'pieces_jointes')
            return 'eden::fiches.include.gestion_pieces_jointes';
        else if(strpos($module, 'workflow_') !== false)
            return 'eden::fiches.include.workflow';
        else if(Formulaire::where('nom_formulaire', $module)->first() !== null)
            return 'eden::fiches.include.formulaire_libre_sur_fiche';
        else if(isset($this->rapports_fiche[$module])) {
            return 'eden::fiches.include.rapport_sur_fiche';
        }
        return false;

    }

    public function genere_fichier_fiche($structure, $extranet){

        if(isset($structure['structure']))
            $structure = $structure['structure'];

        $contenu_fichier = "<?php\n\nreturn [\n";

        $contenu_fichier .= "\n\t'modules' => [";

        foreach($structure['modules'] as $info_module) {

            // cas d'une ligne simple
            if(isset($info_module['taille'])) {

                if(empty($info_module['module']))
                    continue;

                if(!isset($info_module['cacher_bloc_v_if']))
                    $info_module['cacher_bloc_v_if'] = true;

                if(!isset($info_module['afficher_par_defaut']))
                    $info_module['afficher_par_defaut'] = true;

                $contenu_fichier .= "\n";

                $contenu_fichier .= "\t\t[\n";

                $contenu_fichier .= "\t\t\t'module' => '".$info_module['module']."',\n";
                $contenu_fichier .= "\t\t\t'afficher_par_defaut' => ".$info_module['afficher_par_defaut'].",\n";
                $contenu_fichier .= "\t\t\t'cacher_bloc_v_if' => '".str_replace("'","\'",$info_module['cacher_bloc_v_if'])."',\n";
                $contenu_fichier .= "\t\t\t'taille_avant' => 0,\n";
                $contenu_fichier .= "\t\t\t'taille' => ".$info_module['taille'].",\n";
                $contenu_fichier .= "\t\t\t'taille_apres' => 0,\n";
                $contenu_fichier .= "\t\t],";
            }
            // cas d'une ligne avec 2 colonnes
            else {

                $contenu_fichier .= "\n";

                $contenu_fichier .= "\t\t[\n";

                foreach($info_module as $colonne) {

                    if(isset($colonne['onglets']) && !isset($colonne['onglet']))
                        $colonne['onglet'] = $colonne['onglets'];

                    if(!isset($colonne['onglet']))
                        $colonne['onglet'] = 0;

                    if(!isset($colonne['cacher_bloc_v_if']))
                        $colonne['cacher_bloc_v_if'] = true;

                    $contenu_fichier .= "\t\t\t[\n";
                    $contenu_fichier .= "\t\t\t\t'taille' => ".$colonne['taille'].",\n";
                    $contenu_fichier .= "\t\t\t\t'onglets' => ".$colonne['onglet'].",\n";
                    $contenu_fichier .= "\t\t\t\t'cacher_bloc_v_if' => '".str_replace("'","\'",$colonne['cacher_bloc_v_if'])."',\n";

                    if(!empty($colonne['bouton_suivant']))
                        $contenu_fichier .= "\t\t\t\t'bouton_suivant' => ".$colonne['bouton_suivant'].",\n";

                    $contenu_fichier .= "\t\t\t\t'modules' => [\n\n";

                    if(isset($colonne['modules'])) {

                        foreach($colonne['modules'] as $module) {

                            if(empty($module['module']))
                                continue;

                            if(!isset($module['afficher_par_defaut']))
                                $module['afficher_par_defaut'] = true;

                            if(!isset($module['cacher_bloc_v_if']))
                                $module['cacher_bloc_v_if'] = true;

                            $contenu_fichier .= "\t\t\t\t\t[\n";
                            $contenu_fichier .= "\t\t\t\t\t\t'module' => '".$module['module']."',\n";
                            $contenu_fichier .= "\t\t\t\t\t\t'cacher_bloc_v_if' => '".str_replace("'","\'",$module['cacher_bloc_v_if'])."',\n";
                            $contenu_fichier .= "\t\t\t\t\t\t'afficher_par_defaut' => ".$module['afficher_par_defaut'].",\n";
                            $contenu_fichier .= "\t\t\t\t\t\t'taille_avant' => ".$module['taille_avant'].",\n";
                            $contenu_fichier .= "\t\t\t\t\t\t'taille' => ".$module['taille'].",\n";
                            $contenu_fichier .= "\t\t\t\t\t\t'taille_apres' => ".$module['taille_apres'].",\n";
                            $contenu_fichier .= "\t\t\t\t\t],\n";
                        }
                    }

                    $contenu_fichier .= "\t\t\t\t],\n";
                    $contenu_fichier .= "\t\t\t],\n";
                }

                $contenu_fichier .= "\t\t],";
            }
        }

        $contenu_fichier .= "\n\t],";

        $contenu_fichier .= "\n\t'colonne_droite' => [";

        if(isset($structure['colonne_droite'])) {

            foreach($structure['colonne_droite'] as $info_module) {

                // cas d'une ligne simple
                if(isset($info_module['taille'])) {

                    if(empty($info_module['module']))
                        continue;

                    if(!isset($info_module['cacher_bloc_v_if']))
                        $info_module['cacher_bloc_v_if'] = true;

                    $contenu_fichier .= "\n";

                    $contenu_fichier .= "\t\t[\n";

                    $contenu_fichier .= "\t\t\t'module' => '".$info_module['module']."',\n";
                    $contenu_fichier .= "\t\t\t'afficher_par_defaut' => true,\n";
                    $contenu_fichier .= "\t\t\t'cacher_bloc_v_if' => '".str_replace("'","\'",$info_module['cacher_bloc_v_if'])."',\n";
                    $contenu_fichier .= "\t\t\t'taille_avant' => 0,\n";
                    $contenu_fichier .= "\t\t\t'taille' => 12,\n";
                    $contenu_fichier .= "\t\t\t'taille_apres' => 0,\n";
                    $contenu_fichier .= "\t\t],";
                }
            }
        }

        $contenu_fichier .= "\n\t],";

        if(isset($structure['options'])) {

            $contenu_fichier .= "\n\t'options' => ";
            $contenu_fichier .= var_export($structure['options'], true);
        }

        $contenu_fichier .= "\n];";

        // on stocke dans un fichier
        if ($extranet)
            \Storage::put('eden_fiche_'.$this->type_element.'_extranet.php', $contenu_fichier);
        else
            \Storage::put('eden_fiche_'.$this->type_element.'.php', $contenu_fichier);

        return true;

    }

    public function modules_disponibles(){

        $type_element = $this->type_element;

        $dossiers = array(
            app_path('Eden/Views/fiches/include/' . $type_element),
            resource_path('views/vendor/eden/fiches/include/' . $type_element),
        );

        $modules = array();

        foreach($dossiers as $index_dossier => $dossier) {

            // le répertoire standard
            if (is_dir($dossier)) {

                $repertoire = scandir($dossier);

                foreach ($repertoire as $fichier) {

                    if ($fichier == '.' || $fichier == '..')
                        continue;

                    $fichier = str_replace('.blade.php', '', $fichier);

                    $index_traduction = 'module_sur_fiche.' . $type_element . '.' . $fichier;

                    $traduction = traduction('module_sur_fiche.' . $type_element . '.' . $fichier);

                    if ((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction) {

                        $nom_module = str_replace('_', ' ', $fichier);

                        $nom_module = ucfirst($nom_module);

                        service('traduction')->calcul_index_traduction(
                            18,
                            array(
                                'module_sur_fiche',
                                $type_element
                            ),
                            array(
                                $fichier => $nom_module,
                            ),
                            $index_dossier == 0
                        );

                        $traduction = $nom_module;

                        Cache_management::partage_oublie_traductions();
                        Cache_management::invalide();
                    }

                    $modules[$fichier] = $traduction;
                }
            }
        }

        if(!isset($modules['commerce'])){

            $prefixe_doc = 'fiche_'.$this->type_element.'_';
            
            $documents_gescom = Variables::documents_gescom_disponibles();

            $listes_libres_fiches = Liste_libre::whereIn(
                'id_rapport',
                array_map(fn($type_document) => $prefixe_doc . $type_document, $documents_gescom)
            )->pluck('id_rapport')->toArray();

            if(count($listes_libres_fiches) > 0)
                $modules['commerce'] = 'Commerce';
        }

        return $modules;
    }

    /**
     * @param $structure
     * @return void
     *
     * On vérifie la licence et on retire les modules non disponibles
     *
     */
    public function gestion_licence($structure){

        if(editeur() || !session()->has('cache.droits_licences.3'))
            return $structure;

        $droits = session()->get('cache.droits_licences.3');

        if(in_array('tous',$droits))
            return $structure;

        $modules_a_verifier = [];

        if(isset($droits['general']))
            $modules_a_verifier = $droits['general'];

        if(isset($droits[$this->type_element]))
            $modules_a_verifier = array_merge($modules_a_verifier,$droits[$this->type_element]);

        // pour la fiche classique
		if(isset($structure['modules'])){

			foreach($structure['modules'] as $index_info_structure => &$info_structure) {

				if(isset($info_structure['module'])) {

                    if(!in_array($info_structure['module'],$modules_a_verifier))
                        unset($structure['modules'][$index_info_structure]);

					continue;
				}

				foreach($info_structure as &$info_structure_tmp) {
					foreach($info_structure_tmp['modules'] as $index_info_structure_niveau_2 => $info_structure_niveau_2) {

						if(isset($info_structure_niveau_2['module'])){

                            if(!in_array($info_structure_niveau_2['module'],$modules_a_verifier))
                                unset($info_structure_tmp['modules'][$index_info_structure_niveau_2]);
                        }
					}
				}
			}
		}

		// pour la colonne de droite
		if(isset($structure['colonne_droite'])) {

			foreach($structure['colonne_droite'] as $index_info_structure => $info_structure) {

				if(isset($info_structure['module'])){

                    if(!in_array($info_structure['module'],$modules_a_verifier))
                        unset($structure['colonne_droite'][$index_info_structure]);
                }
			}
		}

        return $structure;
    }

    public  function recupere_logo($donnees) {
        $champ_logo = Champ_libre::where('type_element', $this->type_element)
                      ->where('type', 7)
                      ->where('format_champ', 'logo')
                      ->first();

        if(empty($champ_logo))
            return null;

        $champ = $donnees['management_element']->champ($champ_logo->nom_sql);

        return $champ;
    }

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = array();
        
        $modeles_de_document = modele('modele_de_document')
            ->where('type_de_document', 1)
            ->where('type_element_autres', $this->type_element)
            ->orderBy('ordre')
            ->get();

        if(isset($donnees['type_element'], $donnees['id_element']))
            $conversions = $this->conversions_possibles($donnees);

        if($modeles_de_document->isNotEmpty())
            $options_fil_ariane[] = [
                'id' => 'pdf_modele_document',
                'ordre' => 1,
                'parametres' => [
                    'modeles_de_document' => $modeles_de_document
                ]
            ];

        if(!empty($conversions))
            $options_fil_ariane[] = [
                'id' => 'convertir',
                'ordre' => 1,
                'parametres' => [
                    'conversions' => $conversions
                ]
            ];

        if(service('transformation_document_temps')->transformation_possible($this->type_element, $donnees['id_element'] ?? null))
            $options_fil_ariane[] = [
                'id' => 'transformation_document_temps',
                'ordre' => 1,
                'parametres' => [
                    'modeles' => modele('transformation_document_temps_modele')
                        ->where('type_element_cible',$this->type_element)
                        ->get()
                ]
            ];

        if(table_libre($this->type_element)->envoyer_email && !in_array($this->type_element, Variables::$documents_gescom))
            $options_fil_ariane[] = [
                'id' => 'envoi_mail',
                'ordre' => 1,
            ];

        if(fonctionnalite('open_ai_activation') && modele('modele_enrichissement')->where('type_element', $this->type_element)->exists()) {
            $options_fil_ariane[] = [
                'id' => 'enrichissement',
                'ordre' => -3,
            ];
        }

        $options_fil_ariane[] = [
            'id' => 'enregistrement',
            'ordre' => 1,
            'option_a_droite' => true,
        ];

        $options_fil_ariane[] = [
            'id' => 'suppression',
            'ordre' => 0,
            'option_a_droite' => true,
        ];

        return $options_fil_ariane;
    }

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane_filtres($donnees){

        $options_fil_ariane = $this->options_fil_ariane($donnees);

        $options_fil_ariane = array_values($options_fil_ariane);

        if($donnees['management_element']->verifie_profil_suppression() !== true){

            $index_suppression = array_search('suppression', array_column($options_fil_ariane, 'id'));

            if($index_suppression !== false)
                unset($options_fil_ariane[$index_suppression]);

            $index_suppression = array_search('modale_suppression_element', array_column($options_fil_ariane, 'id'));

            if($index_suppression !== false)
                unset($options_fil_ariane[$index_suppression]);

            $options_fil_ariane = array_values($options_fil_ariane);
        }

        if(isset($donnees['structure']['options']['options_desactives'])){

            foreach($donnees['structure']['options']['options_desactives'] as $option){

                $index_option = array_search($option, array_column($options_fil_ariane, 'id'));

                if($index_option !== false) {
                    unset($options_fil_ariane[$index_option]);

                    $options_fil_ariane = array_values($options_fil_ariane);
                }
            }
        }
        if(!empty(moi_extranet()))
            $options_fil_ariane = array_filter($options_fil_ariane, fn($option) => $option['id'] == 'enregistrement');
        
        usort($options_fil_ariane, function($a, $b) {
            return $a['ordre'] <=> $b['ordre'];
        });

        return $options_fil_ariane;
    }

    public function fil_ariane($donnees) {

        $fil_ariane = [];

        if(!empty($donnees['structure']['options']['afficher_fil_ariane'])) {
            $traduction_element = traduction(table_libre($this->type_element)->index_traduction . ".element");
            $traduction_element_pluriel = traduction(table_libre($this->type_element)->index_traduction . ".element_pluriel");

            $fil_ariane = array(
                array('route' => 'base_eden.liste.index', 'arguments' => [$this->type_element], 'nom' => $traduction_element_pluriel),
                array('nom' => $traduction_element.' - <span v-pre>' . str_replace(array('<br/>', '<br>'), ', ', management($this->type_element, $this->id_element)->affiche()).'</span>')
            );

            if(admin() && mode_parametrage() === true) {
                $fil_ariane[1]['nom'] .= '<a href="'.route('parametrage.table_libre.zoom', ['type_element' => $this->type_element], false).'" class="css_bouton_modifier_liste_primaire">
                                            <i class="fas fa-cog"></i> Paramétrer "'.$traduction_element_pluriel.'"
                                        </a>';
            }
        }

        return $fil_ariane;
    }

    /*
     *
     * Récupère les conversions possibles pour le type_element de la fiche sur laquelle on se trouve
     *
     */
    public function conversions_possibles($donnees){

        $conversions = modele('mappage_table_conversion')->where('type_element_depart', $donnees['type_element'])->get();

        if($conversions->isEmpty())
            return array();

        $logs_conversions = Element_log::where('type_element', $donnees['type_element'])->where('id_element', $donnees['id_element'])->where('type_action', 28)->get();
        $conversions_faites = array();
        $conversions_valides = array();

        foreach($logs_conversions as $log){

            $details = json_decode($log->details);

            foreach($details as $type_element => $id_element)
                $conversions_faites[$type_element] = $id_element;
        }

        foreach($conversions as $conversion)
            if(!$conversion->conversion_unique || !array_key_exists($conversion->type_element_arrivee, $conversions_faites))
                $conversions_valides[$conversion->id] = $conversion->type_element_arrivee;

        return $conversions_valides;
    }

    /*
     *
     * Récupère les données nécessaires à l'affichage du bloc pieces_jointes
     *
     */
    public function recuperer_bibliotheque_parametrer($parametre){

        $dossier_parent_id = $parametre->dossier_parent_id;
        $this->infos_synchro_externe();

        // Si on est sur un dossier Google Drive
        if(isset($this->infos_synchro_externe['type']) && $this->infos_synchro_externe['type'] === 'gdrive' &&
            ($dossier_parent_id == 0 || strlen($dossier_parent_id) > 10)) {

            $gdrive = service('google');

            // Si on est dans un sous-dossier
            if($dossier_parent_id != 0) {

                // Si on charge le même dossier que celui dans lequel on se trouve, on ne change pas le dossier parent
                if($dossier_parent_id == (isset($parametre->dossier_parent) ? $parametre->dossier_parent['id'] : null)) {

                    $nouveau_dossier_parent = collect($parametre->dossier_parent);

                    // Sinon, on charge les dossiers parents
                } else {

                    $nom_dossier = '';
                    $dossiers_gdrive = $gdrive->recupere_fichiers_drive('', true);

                    foreach ($dossiers_gdrive as $dossier) {

                        if($dossier->id == $dossier_parent_id)
                            $nom_dossier = $dossier->nom;
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

            } else {

                // On charge la racine de la fiche
                $nouveau_dossier_parent = modele('bibliotheque_synchro_elements')
                    ->where('type_element', $parametre->type_element)
                    ->where('element_id', $parametre->id_element)
                    ->first();

                $dossier_parent_id = $nouveau_dossier_parent->id_dossier;

                $nouveau_dossier_parent = null;
            }

            list($fichiers_gdrive, $dossiers_gdrive) = $gdrive->recupere_fichiers_drive($dossier_parent_id == 0 ? parametre('synchro_gdrive_dossier_racine') : $dossier_parent_id);

            return ['retour' => true, 'dossiers' => $dossiers_gdrive, 'pieces_jointes' => $fichiers_gdrive, 'nouveau_dossier_parent' => $nouveau_dossier_parent];
        }
        if(!empty(moi()->id_microsoft) && ($dossier_parent_id == 0 || strlen($dossier_parent_id) > 10) &&
            isset($this->infos_synchro_externe['type'], $this->infos_synchro_externe['mappage']) &&
            $this->infos_synchro_externe['type'] === 'sharepoint' && in_array($parametre->type_element, $this->infos_synchro_externe['mappage'])) {

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
                if(in_array($parametre->type_element, Variables::$documents_gescom))
                    $nouveau_dossier_parent = modele('bibliotheque_synchro_elements')->where('type_document', $parametre->type_element)->where('document_id', $parametre->id_element)->first();
                else
                    $nouveau_dossier_parent = modele('bibliotheque_synchro_elements')->where('type_element', $parametre->type_element)->where('element_id', $parametre->id_element)->first();

                if(!empty($nouveau_dossier_parent))
                    $dossier_parent_id = $nouveau_dossier_parent->id_dossier;

                $nouveau_dossier_parent = null;
            }

            list($fichiers_sharepoint, $dossiers_sharepoint) = $sharepoint->recuperer_contenu_dossier($dossier_parent_id == 0 ? config('dossier_racine_eden_sharepoint') : $dossier_parent_id);

            foreach ($dossiers_sharepoint as $dossier) {

                $dossier->dossier_parent = $nouveau_dossier_parent;
            }

            return ['retour' => true, 'dossiers' => $dossiers_sharepoint, 'pieces_jointes' => $fichiers_sharepoint, 'nouveau_dossier_parent' => $nouveau_dossier_parent];
        }

        $nouveau_dossier_parent=null;

        $type_element = $parametre->type_element;

        $element_id = $parametre->id_element;

        if($dossier_parent_id==0){

            $dossiers = modele('dossier_bibliotheque')
                ->where('dossier_parent','=',null)
                ->where('type_element', $type_element)
                ->where(function($r) use ($element_id) {
                    $r->where('element_id', $element_id)->orWhereNull('element_id');
                })
                ->get();
        }
        else {
            $dossiers = modele('dossier_bibliotheque')
                ->where('dossier_parent', '=', $dossier_parent_id)
                ->where('type_element', $type_element)
                ->where(function($r) use ($element_id) {
                    $r->where('element_id', $element_id)->orWhereNull('element_id');
                })
                ->get();

            foreach ($dossiers as $dossier) {

                $dossier->dossier_parent = modele('dossier_bibliotheque', $dossier->dossier_parent);
            }

            $nouveau_dossier_parent = modele('dossier_bibliotheque', $dossier_parent_id);

            $nouveau_dossier_parent = $this->recuperation_dossier_parent($nouveau_dossier_parent);

        }

        foreach ($dossiers as &$dossier){

            $dossier->nombre_de_fichiers_enfants = Element_piece_jointe::where('type_element', $type_element)->where('element_id', $element_id)->where('dossier_parent', $dossier->id)->get()->count();

        }

        $management = fiche($type_element, $element_id);

        $pieces_jointes = $management->recupere_pieces_jointes($type_element,$element_id,$dossier_parent_id);

        $documents = $management->recupere_documents_et_dossiers_fiche($type_element, $element_id)['documents'];
        $dossiers_documents = $management->recupere_documents_et_dossiers_fiche($type_element, $element_id)['dossiers_documents'];

        return array(
            'retour' => true,
            'dossiers' => $dossiers,
            'pieces_jointes' => $pieces_jointes,
            'nouveau_dossier_parent'=>$nouveau_dossier_parent,
            'documents'=>$documents,
            'dossiers_documents'=>$dossiers_documents,
        );
    }

    public function recuperation_dossier_parent($dossier){

        if($dossier->dossier_parent != null ){

            if(!is_object($dossier->dossier_parent))
                $dossier->dossier_parent = modele('dossier_bibliotheque',$dossier->dossier_parent);

            $dossier->dossier_parent = $this->recuperation_dossier_parent($dossier->dossier_parent);
        }

        return $dossier;
    }

    /*
     *
     * Crée la pièce jointe liée à un élément depuis le bloc pièces jointes
     *
     */
    public function ajoute_piece_jointe($formulaire, $type_element, $id_element) {

        $pj = $formulaire->file('piece_jointe');
        $dossier_parent = json_decode($formulaire->get('dossier_parent'), true);
        $this->infos_synchro_externe();

        // Si le dossier parent est de type Google Drive, on crée le fichier via le service éponyme
        if(isset($this->infos_synchro_externe['type']) && $this->infos_synchro_externe['type'] === 'gdrive') {

            if(empty($dossier_parent)) {

                $dossier_element = modele('bibliotheque_synchro_elements')->where('type_element', $type_element)->where('element_id', $id_element)->first();
                $dossier_id = $dossier_element->id_dossier;

            } else
                $dossier_id = $dossier_parent['id'];

            $fichier = service('google')->upload_fichier_drive($pj->getClientOriginalName(), $pj->getPathname(), $dossier_id);

            $fichier->retour = 'gdrive';

            return collect($fichier) ;
        }
        //Si le dossier parent est de type Sharepoint, on crée le fichier via le service éponyme
        if(!empty(moi()->id_microsoft) && isset($this->infos_synchro_externe['type'], $this->infos_synchro_externe['mappage']) &&
            $this->infos_synchro_externe['type'] === 'sharepoint' && in_array($type_element, $this->infos_synchro_externe['mappage'])) {

            if(empty($dossier_parent)) {

                if(in_array($type_element, Variables::$documents_gescom))
                    $dossier_element = modele('bibliotheque_synchro_elements')->where('type_document', $type_element)->where('document_id', $id_element)->first();
                else
                    $dossier_element = modele('bibliotheque_synchro_elements')->where('type_element', $type_element)->where('element_id', $id_element)->first();

                $dossier_id = $dossier_element->id_dossier;

            } else
                $dossier_id = $dossier_parent['id'];

            $contenu_fichier = file_get_contents($pj);
            $fichier = service('microsoft_sharepoint')->creer_fichier($pj->getClientOriginalName(), $contenu_fichier, $dossier_id);

            $fichier->retour = 'sharepoint';

            return collect($fichier) ;
        }
        if(service('mfiles')->verifier_synchronisation_mfiles($type_element)) {

            try {

                $fichier = service('mfiles')->creer_fichier($pj->getClientOriginalName(), file_get_contents($pj), $type_element, $id_element);
            } catch (\App\Eden\Exceptions\Eden_exception $e) {

                return response()->json(['succes' => false, 'mfiles' => true, 'message' => $e->getMessage()]);
            }

            $fichier->retour = 'mfiles';

            $fichier = $this->prepare_piece_jointe_affichage($fichier);

            return collect($fichier) ;
        }

        $chemin='public/';
        $dossier_parent_id = null;

        if(!empty($dossier_parent)){

            $chemin=$chemin.$dossier_parent['chemin'];
            $dossier_parent_id = $dossier_parent['id'];
        }
        else
            $chemin=$chemin.$type_element;

        $piece_jointe_existant =  Element_piece_jointe::where('titre',$pj->getClientOriginalName())
            ->where('dossier_parent',$dossier_parent_id)
            ->first();

        $nom= $pj->getClientOriginalName();
        $extension = pathinfo($nom, PATHINFO_EXTENSION);
        $nom_uniquement= pathinfo($nom, PATHINFO_FILENAME);

        if($piece_jointe_existant!=null){

            $i = 2;

            while(Element_piece_jointe::where('titre',$nom_uniquement.' ('.$i.').'.$extension)
                    ->where('dossier_parent',$dossier_parent_id)->where('type_element',$type_element)
                    ->first() !=null){
                $i++;
            }

            $nom=$nom_uniquement.' ('.$i.').'.$extension;
        }

        if(isset($this->infos_synchro_externe['type']) && $this->infos_synchro_externe['type'] === 'gdrive') {

            service('google')->upload_fichier_drive($nom, $pj->getPathName());
            $path = '' ;

        } else {

            // on stocke dans le local storage
            if(fonctionnalite('pieces_jointes_garder_nom_originel') === true) {

                $nom_fichier = retraite_caracteres_speciaux(pathinfo($pj->getClientOriginalName(),PATHINFO_FILENAME),'_');

                $extension = pathinfo($pj->getClientOriginalName(),PATHINFO_EXTENSION);

                $nom_original = $nom_fichier.'.'.$extension;

                $path = $pj->storeAs($chemin, $nom_original);

            }
            else {
                $hash = Str::random(40);

                $path = $pj->storeAs($chemin,$hash.'.'.$pj->getClientOriginalExtension());
            }

            $path = str_replace('public/', '', $path);
        }

        // on enregistre sur le modèle
        $piece_jointe = new Element_piece_jointe;
        $piece_jointe->type_element = $type_element;
        $piece_jointe->element_id = $id_element;
        $piece_jointe->nom = $nom;
        $piece_jointe->chemin = $path;
        $piece_jointe->dossier_parent = $dossier_parent_id;
        $piece_jointe->cree_le = date('Y-m-d H:i:s');

        if(!empty(moi())) {
            $piece_jointe->type_element_createur = 'utilisateur';
            $piece_jointe->element_id_createur = moi()->id;
        }
        else if(!empty(moi_extranet())){
            $piece_jointe->type_element_createur = 'contact';
            $piece_jointe->element_id_createur = moi_extranet()->contact_selectionne->id;
            $piece_jointe->disponible_extranet = 1;
        }

        if(empty($formulaire->titre))
            $piece_jointe->titre = $piece_jointe->nom;

        $piece_jointe->save();

        if(in_array($type_element, Variables::$documents_gescom)) {

            // on régénère le PDF directement pour éviter que l'utilisateur doive le réenregistrer
            management($type_element, $id_element)->creation_pdf();
        }

        return $piece_jointe;
    }

    /*
     *
     * Supprime la pièce jointe liée à un élément depuis le bloc pièces jointes
     *
     */
    public function supprimer_piece_jointe($id_piece_jointe) {

        $piece_jointe = Element_piece_jointe::find($id_piece_jointe);
        $this->infos_synchro_externe();

        if((!empty(moi_extranet()) && (
                $piece_jointe->type_element_createur != 'contact' ||
                $piece_jointe->element_id_createur != moi_extranet()->contact_selectionne->id
            )))
            return response()->json(array('retour' => traduction('composant.messages.piece_jointe_suppression_impossible_profil')));

        if(isset($this->infos_synchro_externe['type']) && $this->infos_synchro_externe['type'] === 'gdrive') {

            service('google')->supprime_drive($id_piece_jointe);

            return response()->json(array('retour' => true));
        }
        if(!empty(moi()->id_microsoft) && isset($this->infos_synchro_externe['type']) &&
            $this->infos_synchro_externe['type'] === 'sharepoint' && empty($piece_jointe)) {

            service('microsoft_sharepoint')->supprimer_element($id_piece_jointe);

            return response()->json(array('retour' => true));
        }

        if(!isset($piece_jointe))
            return response()->json( ['retour' => traduction('composant.messages.piece_jointe_inexistante')]);

        if($piece_jointe->stockage_externe == 2 && isset($this->infos_synchro_externe['type']) &&
            $this->infos_synchro_externe['type'] !== 'mfiles' && !empty($this->infos_synchro_externe['jeton_authentification'])) {

            try {

                service('mfiles')->supprimer_piece_jointe($piece_jointe->dossier_parent);
            } catch (\App\Eden\Exceptions\Eden_exception $e) {

                return response()->json(['succes' => false, 'mfiles' => true, 'message' => $e->getMessage()]);
            }

            $piece_jointe->delete();

            return response()->json(array('retour' => true));
        }

        Storage::delete("public/{$piece_jointe['chemin']}");

        $piece_jointe->delete();
    }

    /*
     *
     * Retourne le lien ou la réponse de téléchargement de la pièce jointe liée à un élément depuis le bloc pièces jointes
     *
     */
    public function telecharger_piece_jointe($type_element, $id_element, $id_piece_jointe) {

        $piece_jointe = Element_piece_jointe::where('id', $id_piece_jointe)->where('element_id', $id_element)->where('type_element', $type_element)->first();
        $this->infos_synchro_externe();

        //Si on ne trouve pas de pièce jointe dans la base et qu'on utilise la synchro Sharepoint on télécharge le fichier via le service associé
        if(empty($piece_jointe) && isset($this->infos_synchro_externe['type'], $this->infos_synchro_externe['mappage']) &&
            $this->infos_synchro_externe['type'] === 'sharepoint' && in_array($type_element, $this->infos_synchro_externe['mappage'])){

            $service = service('microsoft_sharepoint');
            $lien_telechargement_fichier = $service->telecharger_fichier($id_piece_jointe);
            return redirect()->away($lien_telechargement_fichier);
        }

        if($piece_jointe->stockage_externe == 2 && isset($this->infos_synchro_externe['type']) &&
            $this->infos_synchro_externe['type'] !== 'mfiles' && !empty($this->infos_synchro_externe['jeton_authentification'])){

            try {

                return service('mfiles')->telecharger_fichier($piece_jointe);
            } catch (\Exception $e) {

                return response()->json(['succes' => false, 'mfiles' => true, 'message' => $e->getMessage()]);
            }
        }

        if(file_exists(storage_path('app/public/'.$piece_jointe->chemin)))
            return response()->download(storage_path('app/public/'.$piece_jointe->chemin), $piece_jointe->titre);

        if(file_exists(storage_path('app/'.$piece_jointe->chemin)))
            return response()->download(storage_path('app/'.$piece_jointe->chemin), $piece_jointe->titre);

        exception("Le fichier n'a pas été trouvé");
    
    }

    /*
     *
     * Charge dans le management les infos liées à la synchro externe utilisée pour les pièces jointes
     *
     */
    private function infos_synchro_externe(){

        if(!empty($this->infos_synchro_externe))
            return;

        if(parametre('type_synchro_bibliotheque') == 'gdrive')
            $this->infos_synchro_externe['type'] = 'gdrive';
        else if(fonctionnalite('sharepoint_utiliser_synchronisation') === true) {
            $this->infos_synchro_externe['type'] = 'sharepoint';
            $this->infos_synchro_externe['mappage'] = modele('parametrage_mappage_sharepoint')->get()->pluck('type_element', 'nom_dossier')->toArray();
        }
        else if(fonctionnalite('mfiles_utiliser_synchronisation')) {
            $this->infos_synchro_externe['type'] = 'mfiles';
            $this->infos_synchro_externe['jeton_authentification'] = fonctionnalite('mfiles_jeton_authentification');
        }
    }
}
