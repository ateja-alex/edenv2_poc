<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Elements\Filtre;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Eden\Managements\Listes_management;
use App\Eden\Managements\Fiches\Fiches_management;
use App\Eden\Managements\Rapports\Rapports_management;

use App\Eden\Models\Element_image;
use App\Eden\Models\Element_piece_jointe;
use App\Eden\Models\Liste_libre;
use App\Eden\Variables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use Schema;
use App\Eden\Models\Champ_libre;

class Fiche_controller extends Controller {

	/**
	 *
	 * Méthode dynamique qui appelle une méthode sur un contrôleur
	 *
	 * Nous passons par ce procédé un peu complexe pour permettre l'héritage de contrôleur assez simplement
	 *
	 */
    public function execute($type_element, $id_element, $methode = 'afficher', $argument = false) {
		
		$this->type_element = $type_element;
		$this->id_element = $id_element;

		// on va chercher le bon contrôleur
		$controleur = controleur_fiche($type_element, $id_element);

		// on appelle la méthode sur le contrôleur
		if($argument !== false)
			return $controleur->$methode($type_element, $id_element, $argument);
		else
			return $controleur->$methode($type_element, $id_element);
    }

	/**
	 *
	 * Méthode dynamique qui appelle une méthode sur un contrôleur
	 *
	 * Nous passons par ce procédé un peu complexe pour permettre l'héritage de contrôleur assez simplement
	 *
	 */
    public function execute_post(Request $formulaire, $type_element, $id_element, $methode) {
		
		$this->type_element = $type_element;
		$this->id_element = $id_element;

		// on va chercher le bon contrôleur
		$controleur = controleur_fiche($type_element, $id_element);

		// on appelle la méthode sur le contrôleur
		return $controleur->$methode($formulaire, $type_element, $id_element);
    }

	/**
	 *
	 * Permet d'afficher la fiche
	 *
	 */
	public function afficher($type_element, $id_element) {
		

		temps_execution('début controller fiche');
		$nom_page = '<strong>' . management($type_element, $id_element)->affiche() . '</strong> (' . table_libre($type_element)->element . ')';
        $url = 'eden/fiche/'.$type_element.'/'.$id_element;

        enregistrer_log_historique($url,$nom_page);

		$management = fiche($type_element, $id_element);

		// les données de base (les relations)
        if(!management($type_element, $id_element)->existe())
            return view('eden::fiches.fiche_inexistante', ['type_element' => $type_element, 'id_element' => $id_element]);

        $donnees = $management->prepare_donnees_pour_fiche();

        temps_execution('Fiche: après prepare_donnees_pour_fiche');

		$droit_acces_a_fiche = $management->droit_acces_a_fiche();

		if($droit_acces_a_fiche || defined('acces_autorise')) {

			if(view()->exists('eden::fiches.fiche_'.$type_element) && empty(moi_extranet()) && management($type_element, $id_element)->existe())
				return view('eden::fiches.fiche_'.$type_element, $donnees);

			return view('eden::fiches.fiche_generique', $donnees);
		}
		else {

            $url_retour = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : maquette('page_accueil');

            return redirect()->route('extranet.acces_restreint', ['url' => $url_retour]);
        }
    }

	/**
	 *
	 * Supprimer l'élément
	 *
	 */
	public function supprimer($type_element, $id_element) {

		$management = management($type_element, $id_element);

		$management->supprime();
		
		return redirect()->route('base_eden.fiche.index', [$type_element, $id_element]);
	}

	/**
	 *
	 * Permet d'afficher la fiche dans le contexte d'une campagne de prospection
	 *
	 */
	public function afficher_campagne_de_prospection($type_element, $id_element, $campagne_de_prospection_id,$flux_liste = false) {
		
		$nom_page = '<strong>' . management($type_element, $id_element)->affiche() . '</strong> (' . table_libre($type_element)->element . ')';
        $url = 'eden/fiche/'.$type_element.'/'.$id_element;

        enregistrer_log_historique($url,$nom_page);

		$management = fiche($type_element, $id_element);

		// les données de base (les relations)
        $donnees = $management->prepare_donnees_pour_fiche_pour_campagne($campagne_de_prospection_id,$flux_liste);

		// on ajoute les indicateurs
        $donnees = $management->ajouter_indicateurs($donnees);

		// on retraite les données si nécessaire
		$donnees = $management->traiter_donnees($donnees);

		// on récupère le logo @note Tom, v49, 27/10/2022 : ce n'est plus utilisé, je laisse commenter au cas où c'est surchargé en spé sur un projet
//		$management->recupere_logo($donnees);

		// on va chercher les listes enfants (générique)
		$donnees = $management->listes_enfants($donnees);
		
		// on va chercher la structure de la fiche
		$donnees['structure'] = $management->structure_fiche();

		$retour = $management->listes_sur_fiche($donnees['structure'], $donnees['campagne_de_prospection_en_cours']);

		$donnees['listes_sur_fiche'] = $retour['listes_sur_fiche'];
		$donnees['watch_pour_vuejs'] = $retour['watch_pour_vuejs'];
        
		$donnees['historique'] = $management->historique($donnees);

		$donnees['onglet_par_defaut'] = config('fonctionnalites')['fiche_client_onglet_par_defaut'];

		$donnees['extends'] = 'eden::fiches.fiche_generique';

		if(view()->exists('eden::fiches.fiche_'.$type_element))
			$donnees['extends'] = 'eden::fiches.fiche_'.$type_element;

		return view('eden::fiches.fiche_element_campagne_de_prospection', $donnees);
    }
	
	/**
	 * 
	 * Utilisé pour afficher un rapport propre à une fiche
	 * 
	 */
	public function affiche_rapport($type_element, $id_element, $id_rapport) {
		
		$rapport = rapport($id_rapport);
		$rapport->id_element = $id_element;

		$categories = Rapports_management::rapports_disponibles();

		return view('eden::rapports.rapport', array(

			'id_rapport' => $id_rapport,
			'categories' => $categories,
			'rapport' => $rapport->genere(),
			'rapport_modifiable' => false,
		));
	}
	
	/**
	 *
	 * Va chercher les éléments enfants
	 *
	 */
	public function liste_elements_enfants($type_element_parent, $type_element_enfant, $id_element_parent) {

		$management = fiche($type_element_parent, $id_element_parent);

		$infos = $management->liste_elements_enfants($type_element_parent, $type_element_enfant, $id_element_parent);

		return response()->json(array('donnees' => $infos['donnees'], 'donnees_liste' => $infos['donnees_liste']));
	}

	/**
	*
	* Permet de modifier le logo d'un élément
	*
	*/
	public function modifier_logo($formulaire, $type_element, $id_element) {

		$management = fiche($this->type_element, $id_element);

        $management->enregistre_logo($formulaire, $type_element, $id_element);

		return redirect()->route('base_eden.fiche.index', array($type_element, $id_element, 'afficher'));
    }

    /**
     *
     * Permet d'ajouter une image sur la fiche
     *
     */
    public function ajoute_image($formulaire, $type_element, $id_element) {

        // Dans le cas où, on soumet le formulaire sans sélectionner une pièce jointe
        if(empty($formulaire->file('image')))
            return redirect()->back()->with('erreur_formulaire_image', traduction('messages.php.fiche.erreur_formulaire_image'));

        $chemin_enregistrement = str_replace('public/', '', $formulaire->file('image')->store('public'));

        if(config("eden.enregistrement_documents_sur_aws") !== false)
            $chemin_enregistrement_s3 = $formulaire->file('image')->store('/', 's3');

        // on enregistre sur le modèle
        $image = new Element_image;
        $image->type_element = $type_element;
        $image->element_id = $id_element;
        $image->nom = $formulaire->file('image')->getClientOriginalName();
        $image->chemin = $chemin_enregistrement;
        $image->titre = $formulaire->titre;
        $image->alt = $formulaire->alt;
        $image->legende = $formulaire->legende;
        $image->description = $formulaire->description;
        $image->ordre = 999;

        if(empty($formulaire->titre))
            $image->titre = $image->nom;

        //Méthode à surcharger si on veut écraser l'image par la même, mais optimisée (ex : Loueruneauto avec Shortpixel)
        fiche($type_element, $id_element)->optimisation_image($image);

        $image->save();

        return redirect()->route('base_eden.fiche.index', [$type_element, $id_element, 'afficher']);
    }

	public function ajoute_image_ajax($formulaire, $type_element, $id_element) {

		// Dans le cas où, on soumet le formulaire sans sélectionner une pièce jointe
		if(empty($formulaire->file('image')))
			return response()->json(array('succes' => false));

        $chemin_enregistrement = str_replace('public/', '', $formulaire->file('image')->store('public'));

		if(config("eden.enregistrement_documents_sur_aws") !== false)
			$chemin_enregistrement_s3 = $formulaire->file('image')->store('/', 's3');

		// on enregistre sur le modèle
		$image = new Element_image;
		$image->type_element = $type_element;
		$image->element_id = $id_element;
		$image->nom = $formulaire->file('image')->getClientOriginalName();
		$image->chemin = $chemin_enregistrement;
		$image->titre = $formulaire->titre;
		$image->alt = $formulaire->alt;
		$image->legende = $formulaire->legende;
		$image->description = $formulaire->description;
        $image->ordre = 999;

		if(empty($formulaire->titre))
			$image->titre = $image->nom;

		$image->save();

		return response()->json(array('succes' => true));
	}

	/**
	 *
	 * Permet de supprimer une image sur la fiche
     * @todo Tom, 27/10/2022 : À améliorer pour que l'image soit également supprimer du storage
	 *
	 */
	public function supprimer_image($type_element, $id_element, $id_image) {

		// on supprime l'image
		$image = Element_image::find($id_image)->delete();

		return redirect()->back();
    }

    /**
     *
     * Permet de supprimer une image sur la fiche
     * @todo Tom, 27/10/2022 : À améliorer pour que l'image soit également supprimer du storage
     *
     */
	public function supprimer_image_ajax($type_element, $id_element, $id_image) {

		// on supprime l'image
		$image = Element_image::find($id_image)->delete();

		return response()->json(array('retour' => true));
    }

	/**
	 *
	 * Permet d'ajouter une pièce jointe à la fiche
	 *
	 */
	public function ajoute_piece_jointe($formulaire, $type_element, $id_element) {

        $management = fiche($type_element, $id_element);

        $piece_jointe = $management->ajoute_piece_jointe($formulaire, $type_element, $id_element);

        if(!($piece_jointe instanceof Collection))
            $piece_jointe = $management->prepare_piece_jointe_affichage($piece_jointe);

		return $piece_jointe;
	}

	public function generer_pdf_depuis_modele($type_element, $element_id, $id_modele_doc) {

        $formulaire = request()->all();

        $forcer_regeneration = false;

        if(isset($formulaire['regenerer']) && $formulaire['regenerer'] == 1)
            $forcer_regeneration = true;

        $element = management($type_element, $element_id);

        if(!empty($id_modele_doc)){
            $modele_document = modele('modele_de_document',$id_modele_doc);

            $nom_du_pdf = $element->recupere_texte_a_afficher($modele_document->nom_pdf_genere);
        }
        else
            $nom_du_pdf = str_replace(["\r", "\n"], '', $element->affiche());
        
        $chemin_pdf = $element->recupere_chemin_pdf($forcer_regeneration, $id_modele_doc);

        $headers = ['Content-Disposition' => 'inline; filename="'.$nom_du_pdf.'.pdf"'];

        // On retourne une réponse
        return response()->file(storage_path('app/'.$chemin_pdf),$headers);
	}

	/**
	 *
	 * Permet de télécharger une pièce jointe
	 *
	 */
	public function telecharger_piece_jointe($type_element, $id_element, $id_piece_jointe) {

        $management = fiche($type_element, $id_element);

        $retour = $management->telecharger_piece_jointe($type_element, $id_element, $id_piece_jointe);

        return $retour;
	}

	/**
	 *
	 * Permet de supprimer la piece jointe
	 *
	 */
	public function supprimer_piece_jointe($type_element, $id_element, $id_piece_jointe) {

        $management = fiche($type_element, $id_element);

        $piece_jointe = $management->supprimer_piece_jointe($id_piece_jointe);

		return response()->json( ['retour' => true]);
	}

	/**
	 *
	 *
	 * 	Permet de modifier les informations d'une piece jointe
	 *
	 */
	public function modifier_piece_jointe(Request $formulaire) {

		$piece_jointe = Element_piece_jointe::find($formulaire->pj_id);

        $piece_jointe_existante =  Element_piece_jointe::where('titre',$piece_jointe->titre)
            ->where('dossier_parent',$piece_jointe->dossier_parent)
            ->where('id','!=',$formulaire->pj_id)
            ->first();

        if($piece_jointe_existante!=null)
            return response()->json( ['retour' => traduction('messages.php.piece_jointe.erreur_titre')]);

		$piece_jointe->titre = $formulaire->pj_titre;

		if(isset($formulaire->pj_lier_au_document))
			$piece_jointe->lier_au_document = $formulaire->pj_lier_au_document;

		$piece_jointe->save();

        $piece_jointes = fiche($formulaire->type_element, $formulaire->id_element)
            ->recupere_pieces_jointes($formulaire->type_element, $formulaire->id_element,$formulaire->id_dossier_parent);

        return response()->json( ['retour' => true,'pieces_jointes'=>$piece_jointes]);
	}

	public function changement_statut_piece_jointe_lier_au_document(Request $formulaire) {

		$piece_jointe = Element_piece_jointe::find($formulaire->pj_id);

		$piece_jointe->lier_au_document = $formulaire->lier_au_document;
		$piece_jointe->save();

		$management = management($piece_jointe->type_element, $piece_jointe->element_id );
		$management->creation_pdf();

        return response()->json( ['retour' => true ]);
	}

    /**
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer les fichiers et les dossiers contenus dans un certain dossier
     *
     */
    public function recuperer_bibliotheque_parametrer(Request $parametre){

        $management = fiche($parametre->type_element, $parametre->id_element);

        $donnees = $management->recuperer_bibliotheque_parametrer($parametre);

        return response()->json($donnees);
    }


	/**
	 *
	 * Permet de modifier le titre de l'image
	 *
	 */
	public function modifier_titre_image($formulaire, $type_element, $id_element) {

		// on modifie l'image
		$image = Element_image::find($formulaire->id_element_image);

		$image->titre = $formulaire->titre;
		$image->save();

		return response()->json(true);
	}

	/**
	 *
	 * Retourne les adresses liées à l'élément
	 *
	 */
	public function adresses() {

		$colonne_existe = modele('adresse')->getConnection()
           ->getSchemaBuilder()
           ->hasColumn(modele('adresse')->getTable(), $this->type_element.'_id');

        // Cas standard
        if ($colonne_existe)
			return response()->json(modele('adresse')->where($this->type_element.'_id', $this->id_element)->get());

		// Cas dynamique
		else
			return response()->json(modele('adresse')->where('type_element', $this->type_element)->where('element_id', $this->id_element)->get());
	}

	/**
	 *
	 * Retourne la liste des événements associés à un élément
	 *
	 */
	public function calendrier_evenement_post($formulaire, $type_element, $id_element) {

		return $this->calendrier_evenement($type_element, $id_element, $formulaire);
	}

	public function calendrier_evenement($type_element, $id_element, $formulaire = false) {

		$listes_management = new Listes_management();

		$parametres = [
						'avec_inactifs' => 0,
						'filtres_appliques' => [
							['type_element', $type_element],
							['element_id', $id_element],
						],
					  ];

		$joins = array();

		if($formulaire !== false) {

			$formulaire = $formulaire->all();

			foreach ($formulaire as $key => $value) {

				if(!empty($value))
					$parametres['filtres_appliques'][] = [$key, $value];
			}

			// Pour chaque filtre
			foreach (management($type_element)->filtres_pour_calendrier() as $filtre) {

				if(isset($formulaire[$filtre['nom_sql']]))

					$joins['element_id'] = array(

												'join' => array($filtre['type_element'], 'synchro_element_id'),
												'colonnes' => array(),
											);
			}
		}

		$liste_evenements = $listes_management->cree_requete('calendrier_evenement', $parametres, ['in_charge'], $joins)->get();

		$array = [];

		foreach ($liste_evenements as $evenement) {

			$contenu_html = $evenement->nom ;

			if(!empty($evenement->synchro_type_element))
				$contenu_html = management($evenement->synchro_type_element, $evenement->synchro_element_id)->retourne_html_pour_calendrier($evenement);

			$evenement->contenu_html = $contenu_html;

			$debut = new \DateTime($evenement->date_debut);
			$fin   = new \DateTime($evenement->date_fin);

			// On rajoute une journée
			$interval = \DateInterval::createFromDateString('1 day');
			$periode = new \DatePeriod($debut, $interval, $fin);

			foreach ($periode as $dt) {

				$array[$dt->format("Y-m-d")][] = $evenement;
			}
		}

		return response()->json(['calendrier_evenement' => $array, 'calendrier_filtres' => management($type_element)->filtres_pour_calendrier()]);
	}

	/**
	 *
	 * Retourne les commentaires liés à l'élément
	 *
	 */
	public function commentaires($type_element, $id_element, $filtre_projet = false) {

		if(in_array($type_element, Variables::$documents_gescom))
			$commentaires = modele('message')->where('element_id', $this->id_element)->where('type_element', $this->type_element)->orderBy('cree_le', 'DESC')->get();
        elseif($filtre_projet === false) {

            $commentaires_element = modele('message')->where('element_id', $this->id_element)->where('type_element', $this->type_element)->orderBy('cree_le')->get();
            $commentaires_client = collect([]);

            $projet = modele('projet', $this->id_element);

            if($this->type_element === 'projet' && !empty($projet->client_id)) {
                $commentaires_client = modele('message')
                    ->select('message.*')
                    ->where('type_element', 'client')
                    ->where('element_id', $projet->client_id)
                    ->orderBy('message.cree_le')
                    ->get();
            }

            $commentaires = $commentaires_element->merge($commentaires_client)->sortByDesc('cree_le')->values();
        }
		elseif($this->type_element === 'client') {

			// page client avec filtre projet
			$commentaires = modele('message')->select('message.*')->where('type_element', 'projet')->where('message.element_id', $filtre_projet)->join('projet','message.element_id', 'projet.id')->where('client_id', $this->id_element)->orderBy('message.cree_le', 'DESC')->get();
		}

		foreach($commentaires as $commentaire) {

            $projet_id = $commentaire->projet_id;

            if($commentaire->type_element == 'projet')
                $projet_id = $commentaire->element_id;

			$commentaire->nom_projet = modele('projet', $projet_id)->nom;
			$commentaire->commentaire = management('message', $commentaire->id)->champ('commentaire')->affiche();
		}

		$projets = array();

		if($type_element == 'client')
			$projets = modele('projet')->where('client_id', $this->id_element)->get();

		$retour = array(

			'donnees' => $commentaires,
			'projets' => $projets,
		);

		return response()->json($retour);
	}

	/**
	 *
	 * Retourne les activités liées à l'élément
	 *
	 */
	public function activites($type_element, $id_element) {

		$retour = array();

		// on va chercher les échanges
		$colonnes = Schema::getColumnListing('echange');

		if(in_array($type_element.'_id', $colonnes)){

			$echanges = modele('echange')->where($type_element.'_id', $id_element)->orderBy('id')->get();

			foreach($echanges as $echange) {

				$retour[] = array(

					'cree_par' => $echange->cree_par,
					'cree_le' => $echange->cree_le,
					'commentaire' => 'Nouvel échange enregistré : '.$echange->description,
				);
			}
		}

		// on va chercher les sous éléments via les listes libres
		$management = fiche($type_element, $id_element);

		// on va chercher la structure de la fiche
		$structure = $management->structure_fiche();

		$listes = array();

		foreach($structure['modules'] as $module) {

			if(isset($module['module'])) {

				if(strpos($module['module'], 'fiche_'.$type_element) === false)
					continue;

				$listes[] = $module['module'];
			}
			else {

				foreach($module as $sous_module) {

					foreach($sous_module['modules'] as $module_tmp) {

						if(strpos($module_tmp['module'], 'fiche_'.$type_element) === false)
							continue;

						$listes[] = $module_tmp['module'];
					}
				}
			}
		}

		foreach($listes as $liste) {

			$liste_libre = Liste_libre::where('id_rapport', $liste)->first();

			// un bug ?
			if(empty($liste_libre))
				continue;

			$donnees = modele($liste_libre->type_element)->where($liste_libre->cle_etrangere, $id_element)->get();

			foreach($donnees as $donnee) {

				$retour[] = array(

					'cree_par' => $donnee->cree_par,
					'cree_le' => $donnee->cree_le,
					'commentaire' => 'Nouvel élément enregistré ('.table_libre($liste_libre->type_element)->element.') : '.management($liste_libre->type_element, $donnee->id)->affiche_lien(),
				);
			}
		}

		// on classe par date
		usort($retour, function($a, $b) {

			if($a['cree_le'] >= $b['cree_le'])
				return -1;

			return 1;
		});

		$retour_definitif = array();

		foreach($retour as $id => $infos) {

			$date = date('Y-m-d', strtotime($infos['cree_le']));

			if(!isset($retour_definitif[$date]))
				$retour_definitif[$date] = array();

			$retour_definitif[$date][] = $infos;

			if($id == 9)
				break;
		}

		return response()->json($retour_definitif);
	}

	/**
	 *
	 * Retourne les contacts liés à l'élément
	 *
	 */
	public function contacts() {

        $filtres = array();

        if(request()->has('filtres_pour_liste'))
            $filtres = request()->get('filtres_pour_liste');

        $contacts = management('contact')->recuperer_contact_element($this->type_element, $this->id_element,array('npai'),$filtres);

		return response()->json($contacts);
	}

	/**
	 *
	 * Retourne les données ecommerce avec une notion de pagination
	 *
	 */
	public function pagination_commerce($formulaire, $type_element, $id_element) {

		// conversion string en booléen
		// + initialisation des filtres
		$preferences_documents = array(

			// les documents de base
			'devis_vente' => true,
			'commande_vente' => true,
			'acompte_vente' => true,
			'bl_vente' => true,
			'avoir_vente' => true,
			'facture_vente' => true,

			// les filtres spécifiques
			'devis_vente_en_cours' => false,
			'devis_vente_acceptes' => false,
			'devis_vente_refuses' => false,
			'facture_vente_reglees' => false,
			'facture_vente_non_reglees' => false,

			// le projet
			'projet_selectionne' => -1,
		);

		foreach($formulaire->documents as $key => $doc) {

			if($doc == 'true')
				$preferences_documents[$key] = true;
			elseif($doc == 'false')
				$preferences_documents[$key] = false;
			else
				$preferences_documents[$key] = $doc;
		}

		// on sauvegarde le choix dans une variable de session
		session()->put('fiche_filtres_commerce_'.$type_element, $preferences_documents);

		$management = fiche($type_element, $id_element);

		if (isset($formulaire->tri) && isset($formulaire->direction_tri) )
			$donnees = $management->commerce_avec_tri($formulaire->tri, $formulaire->direction_tri);
		else
			$donnees = $management->commerce();

		$resultat = $management->prepare_pagination_commerce($donnees, $formulaire->page, 10);

		$first = false;

		if($formulaire->page == 1 )
            $first = true;

		if(count($donnees) > 0 && empty($resultat)){

			$resultat = $management->prepare_pagination_commerce($donnees, 1, 10);
			$first = true;
		}


		//on ajoute les options

		foreach($resultat as $key => $commerce) {

			$resultat[$key]['colonne_options'] = management($commerce['type_element'], $commerce['modele']->id)->colonne_options($commerce['modele']);
		}

		$total_documents = ceil(count($donnees)/10);

		return response()->json(array(

			'resultat' => $resultat,
			'total_documents' => $total_documents,
			'first' => $first
		));
	}

	/**
	 *
	 * Retourne les données ecommerce avec une notion de pagination
	 *
	 */
	public function pagination_projets($formulaire, $type_element, $id_element) {

		$management = fiche($type_element, $id_element);

		$filtres = array();

		if (isset($formulaire->statut) ) {

			$filtres[] = array(
							'statut', 'in', $formulaire->statut
						);
		}

		$donnees = $management->projets_avec_filtres($filtres);

		$resultat = $management->prepare_pagination($donnees, $formulaire->page, 10);

		$first = false;

		if ($formulaire->page == 1 )
            $first = true;

		if(count($donnees) > 0 && empty($resultat)){

			$resultat = $management->prepare_pagination($donnees, 1, 10);
			$first = true;
		}

		$options_liste_projets = [
									'page' => intval($formulaire->page),
									'nombre_pages_pour_vue' => ceil(count($donnees) / 10),
									'id_liste' => Liste_libre::where('type_element','projet')->first()->id,
								];

		return response()->json(array(

			'resultat' => $resultat,
			'options_liste' => $options_liste_projets,
			'first' => $first,
		));
	}

	/**
	 *
	 * Retourne la liste des tâches
	 *
	 */
	public function taches() {

        $taches = modele('tache')->where('element_id', $this->id_element)->where('type_element', $this->type_element)->get();

        $modele_par_defaut = modele_par_defaut('tache');

		return response()->json(array('taches' => $taches,'modele_par_defaut' => $modele_par_defaut));
	}

	/**
	 *
	 * Retourne la liste des images
	 *
	 */
	public function images() {

		$images = fiche($this->type_element, $this->id_element)->recupere_images($this->type_element, $this->id_element);

		return response()->json($images);
	}

	/**
	 *
	 * Retourne la liste des pièces jointes
	 *
	 */
	public function pieces_jointes() {

		$fiche = fiche($this->type_element, $this->id_element);

        //Permet de savoir si on utilise M-Files ou la gestion de pièces jointes classiques.
        // Dans le cas où on utilise M-Files, mais que le type_element n'apparait pas dans le mappage, on utilise la gestion classique.
        $synchronisation_mfiles = service('mfiles')->verifier_synchronisation_mfiles($this->type_element);

		$fichiers = $fiche->recupere_pieces_jointes($this->type_element, $this->id_element);
		$dossiers = $fiche->recupere_dossiers($this->type_element, $this->id_element);

		$donnees = ['fichiers' => $fichiers, 'synchronisation_mfiles' => $synchronisation_mfiles, 'dossiers' => $dossiers, 'documents' => false, 'dossiers_documents' => false];

		if($this->type_element == 'client') {

			$d_d = $fiche->recupere_documents_et_dossiers_fiche($this->type_element, $this->id_element);

			$donnees['dossiers_documents'] = $d_d['dossiers_documents'];
			$donnees['documents'] = $d_d['documents'];
		}

        header('Content-Type: image/jpeg');
		return response()->json($donnees);
	}

	/**
	 *
	 * Retourne les infos sur le profil
	 *
	 */
	public function infos_profil_pour_bibliotheque() {

        if(!empty(moi_extranet())){

            return response()->json(['admin' => false, 'infos_profil' => array(
                'creation' => false,
                'modification' => false,
                'suppression' => false,
                'pj_suppression' => false
            )]);
        }

		$admin = admin();

        $infos_profil = array(
            'creation' => true,
            'modification' => true,
            'suppression' => true
        );

		$donnees = ['admin' => $admin, 'infos_profil' => $infos_profil];

		return response()->json($donnees);
	}

	public function retourne_utilisateur() {

		return response()->json(['id_utilisateur' => id_utilisateur]);
	}

	/**
	 *
	 * Retourne la liste des messages
	 *
	 */
	public function messages() {

		$messages = modele('message')
					->where('element_id', $this->id_element)
					->where('type_element', $this->type_element)
					->get();

		return response()->json($messages);
	}

    /**
    *
    * Gestion des images de tri
    *
    */
    public function tri_image(Request $request) {

        // on récupère l'article
        $article = modele($this->type_element)->find($this->id_element);

        // on récupère les images
        $images = $request->ordre_images;

		foreach($images as $key => $image) {

            $ordre = $key + 1;
            $element_image = Element_image::find($image)->update(['ordre' => $ordre]);
        }

        return response()->json([$element_image]);
	}

	/**
    *
    * Gestion des pieces jointes
    *
    */
    public function tri_pieces_jointes(Request $request) {

        // on récupère le client
		$client = modele($this->type_element)->find($this->id_element);

        // on récupère les pieces jointes
		$pieces_jointes = $request->ordre_pieces_jointes;

		foreach($pieces_jointes as $key => $piece_jointe) {

            $ordre = $key + 1;
            $element_piece_jointe = Element_piece_jointe::find($piece_jointe)->update(['ordre' => $ordre]);
        }

        return response()->json([$element_piece_jointe]);
	}

	/**
	 *
	 *
	 * Tri l'ordre des articles en fonction d'une famille via la fonction js sortable
	 *
	 */
	public function tri_ordre_article(Request $formulaire) {

        $ordre_articles = $formulaire->ordre_articles;

        foreach($ordre_articles as $cle => $id_article) {

            $ordre = $cle + 1;
            $article = modele('article', $id_article);
            $article->ordre = $ordre;
            $article->save();
		}

		return response()->json(['sucess' => true]);
	}

	/**
	 *
	 * Permet de générer un PDF en fonction des éléments choisis par le client
	 *
	 */
	public function generer_fiche_pdf_modele(Request $formulaire, $type_element, $id_element, $id_modele_de_document, $enregistrement_pdf = "") {

		$management = management($type_element, $id_element);
		$fiche = $management->modele ;

		$modele_de_document = modele('modele_de_document', $id_modele_de_document);

		if(empty($modele_de_document->nom_vue))
			$modele_de_document->nom_vue = 'modele_document';

		// on récupère et on formate le nom de la vue
		$vue_pdf_pour_le_document = \Str::slug($modele_de_document->nom_vue, '_');

		// on vérifie si la vue existe bien
		if (view()->exists("eden::pdf.includes.$vue_pdf_pour_le_document")) {

			$modele_pdf_pour_le_document = "eden::pdf.includes.$vue_pdf_pour_le_document";

			$donnees_pour_pdf['css'] = $modele_de_document->css;
			$donnees_pour_pdf['header'] = json_decode($modele_de_document->header);
			$donnees_pour_pdf['body'] = json_decode($modele_de_document->body);
			$donnees_pour_pdf['footer'] = json_decode($modele_de_document->footer);

			if(empty($donnees_pour_pdf['header']))
				$donnees_pour_pdf['header'] = [];

			if(empty($donnees_pour_pdf['body']))
				$donnees_pour_pdf['body'] = [];

			if(empty($donnees_pour_pdf['footer']))
				$donnees_pour_pdf['footer'] = [];

			// on récupère les données en fonction du type element
			$element = $management->charge_donnees_pour_pdf($type_element, $id_element, 'id');

			if(isset($element[0]))
				$donnees_pour_pdf['element'] = $element[0];

			$pdf = PDF::loadView("eden::pdf.includes.$vue_pdf_pour_le_document", $donnees_pour_pdf);

			// Si on ne doit pas enregistrer le document, on retourne le pdf
			if(empty($enregistrement_pdf))
				return $pdf->download($type_element.'-'.$id_element.'.pdf');

			// Sinon on enregistre le fichier
			$chemin = 'documents_divers/'.$type_element.'_'.time().'.pdf';
			Storage::put($chemin , $pdf->output());

			return response()->json(['chemin' => $chemin]);
		}

		dd('Vue non trouvée');
	}

	public function generer_fiche_pdf($formulaire, $type_element, $id_element) {

		$blocs_a_afficher = [];

		$fiche = modele($type_element, $id_element);

		foreach($formulaire->all() as $type => $bloc) {

            $type_element_tmp = str_replace('bloc_','', $type);

			$vue = "eden::pdf.fiches.$type_element.$type_element_tmp";

			// on vérifie si la vue existe
			if(view()->exists($vue)) {

                if($type_element_tmp == $type_element)
                    $donnees =  modele($type_element)->find($id_element);
                else {

                    $methode = 'charge_donnees_pour_pdf_pour_fiche_'.$type_element;

                    $management = management($type_element_tmp);

                    // on vérifie que la méthode existe
                    if (method_exists($management, $methode))
                        $donnees = management($type_element_tmp)->$methode($id_element);
                    else
                        $donnees = modele($type_element_tmp)->where($type_element . '_id', $id_element)->take(5)->orderBy('id', 'DESC')->get();

                    // Cas particulier des échanges sur client
                    if($type_element_tmp == "echange" && $type_element == "client"){

                    	$contacts_client = modele('contact')
                    		->where('client_id',$id_element)
                    		->get()
                    		->pluck('id')
                    		->toArray();

                    	$donnees = modele('echange')
		                    ->where('type_element', 'client')
		                    ->whereIn('contact_id',$contacts_client)
		                    ->get();
                    }
                }

                $blocs_a_afficher[$type] = array('vue' => $vue, 'donnees' => $donnees);
            }
		}

		$vue_fiche = 'eden::pdf.fiches.fiche_pdf_standard';

		if(view()->exists('eden::pdf.fiches.fiche_pdf_'.$type_element))
		    $vue_fiche = 'eden::pdf.fiches.fiche_pdf_'.$type_element;

        if(!is_dir(storage_path('fonts')))
            mkdir(storage_path('fonts'));

		$pdf = PDF::loadView($vue_fiche, array('fiche' => $fiche,'blocs_a_afficher' => $blocs_a_afficher));

        if($type_element == 'fournisseur')
            $pdf = $pdf->setPaper('a4', 'landscape');

		return $pdf->stream('document.pdf');
    }

    /**
     *
     * Permet de fusionner deux fiches
     *
     */
    public function fusionner_fiche(Request $formulaire,$type_element, $id_element){

        $formulaire = $formulaire->all();

        $id_fiche_source = $formulaire['fiche_source'];

        $id_fiche_destinataire = $formulaire['fiche_destinataire'];

        if($id_fiche_source == $id_fiche_destinataire)
            return response()->json(['retour' => false, 'message' => traduction('messages.php.fiche.erreur_fusion_fiche')]);

        $management_fiche_source = management($type_element,$id_element);

        $element_fusionnable = $management_fiche_source->verification_fusionnable();

        if($element_fusionnable !== true)
            return response()->json(['retour' => false, 'message' => $element_fusionnable]);

        $management_fiche_source->fusionner_element($id_fiche_destinataire, $formulaire['elements_a_fusionner'] ?? []);

        return response()->json(['retour' => true,'route' => route('base_eden.fiche.index', [$type_element, $id_fiche_destinataire])]);
    }

    /**
	 *
	 * Permet d'afficher que l'utilisateur connecté n'ait pas les droits de lecture sur la fiche
	 *
	 */
	public function pas_droits_affichage_fiche($type_element) {

		$donnees = array(
			'type_element' => $type_element,
		);

		return view('eden::fiches.fiche_pas_droits',$donnees);
    }

	/**
	 *
	 * Récupère les lignes d'une table donnée
	 *
	 */
	public function recuperer_liste_table($type_element, $id, $argument){

		$liste = modele($argument)->get()->toArray();

		return response()->json($liste);
	}

	/**
	 *
	 * Récupère l'historique d'un element
	 *
	 */
	public function recuperer_historique($type_element, $id_element){

		temps_execution('début controller fiche');

		$management = fiche($type_element, $id_element);

		// on va chercher la structure de la fiche
		$donnees['management_element'] = management($type_element, $id_element);

		$historique = $management->historique($donnees);

		return response()->json($historique);
	}

	/**
	 *
	 * Récupère la liste des familles
	 *
	 */
	public function recupere_familles() {
		return modele('famille')->get();
	}

	/**
	 *
	 * Récupère les recouvrements du client
	 *
	 */
	public function recupere_recouvrement($type_element, $id_element) {

		return fiche($type_element, $id_element)->recouvrement();
	}


	/**
	 *
	 * Récupère les utilisateurs abonnés à une fiche et ceux non abonné
	 *
	 */
	public function recuperer_utilisateurs_abonnees($type_element, $element_id){

		$utilisateurs = modele('utilisateur')
							->get()
							->keyBy('id')
							->toArray();

		// On note ceux qui sont déjà abonnés
		foreach ($utilisateurs as $id => &$utilisateur) {

			$utilisateur_abonne = modele('notification_element')
									->where('utilisateur_id',$id)
									->where('type_element',$type_element)
									->where('element_id',$element_id)
									->first();

			if ($utilisateur_abonne != null) {

				$utilisateur['abonne_fiche'] = true;
				$utilisateur['notification_element_id'] = $utilisateur_abonne->id;
			}
			else
				$utilisateur['abonne_fiche'] = false;
		}

		return response()->json($utilisateurs);
	}

	/**
	 *
	 * Permet d'abonner ou désabonner un utilisateur
	 *
	 */
	public function ajouter_supprimer_abonnement_fiche(Request $formulaire){

		if($formulaire->utilisateur['abonne_fiche'] == "false")
			$this->ajouter_abonnement_utilisateur($formulaire);
		else
			$this->supprimer_abonnement_utilisateur($formulaire);

		return response()->json('ok');
	}

	/**
	 *
	 * Permet d'abonner un utilisateur
	 *
	 */
	public function ajouter_abonnement_utilisateur($formulaire){

		$management = management('notification_element');

		$donnees = array(
			'type_element' =>$formulaire->type_element,
			'element_id' =>$formulaire->element_id,
			'utilisateur_id' =>$formulaire->utilisateur['id'],
		);

		$management->enregistre($donnees);
	}

	/**
	 *
	 * Permet de désabonner un utilisateur
	 *
	 */
	public function supprimer_abonnement_utilisateur($formulaire){

		$management = management('notification_element',$formulaire->utilisateur['notification_element_id']);

		$management->supprime();
	}



    /**
     *
     * Permet de gérer les élements du client qui va être supprimé
     *
     */
    public function gestion_transfert_suppression(Request $formulaire, $type_element) {

        if($formulaire->action === "transfert")
            $this->transfert_contact_ou_adressse($formulaire->element_id, $formulaire->transfert, $formulaire->type_element_a_gerer, $type_element);

        else if($formulaire->action === "supprimer")
            $this->supprimer_contact_ou_adressse($formulaire->element_id, $formulaire->type_element_a_gerer, $type_element);

        return response()->json(true);
    }

    /**
     *
     * On fait le transfert des contacts ou adressses
     *
     */
    public function transfert_contact_ou_adressse($element_id, $nouvel_element, $type_element, $type_element_parent) {

        $elements_a_transferer = modele($type_element)->where($type_element_parent . '_id',$element_id)->get();

        foreach ($elements_a_transferer as $element){

            $management_element = management($type_element, $element->id);

            $management_element->enregistre(array($type_element_parent . '_id' => $nouvel_element));
        }

        return true;
    }

    /**
     *
     * Permet de supprimer les contacts ou adresses du client qui va être supprimé
     *
     */
    public function supprimer_contact_ou_adressse($element_id, $type_element, $type_element_parent) {

        $elements_a_transferer = modele($type_element)->where($type_element_parent . '_id',$element_id)->get();

        foreach ($elements_a_transferer as $element){

            $management_element = management($type_element, $element->id);

            $management_element->supprime();
        }

        return true;
    }

    public function telecharger_toutes_pieces_jointes($type_element, $element_id, $dossier = null){


        $pieces_jointes = Element_piece_jointe::where('type_element', $type_element)->where('element_id', $element_id);

        if($dossier !== null)
            $pieces_jointes = $pieces_jointes->where('dossier_parent', $dossier);

        $pieces_jointes = $pieces_jointes->get();

        if($pieces_jointes->isEmpty())
            return response()->json(['retour' => false, 'message' => traduction('messages.php.pieces_jointes.telecharger_tout.erreur')]);

        $chemin_dossier = storage_path('app/public/pj/'. $type_element . '/' . $element_id);
        $chemin_zip = $chemin_dossier . '/' . $type_element . '_' . $element_id . '.zip';

        if(!is_dir($chemin_dossier))
            mkdir($chemin_dossier, 0777, true);

        if(file_exists($chemin_zip))
            unlink($chemin_zip);

        $zip = new \ZipArchive();
        $zip->open($chemin_zip, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE);

        foreach ($pieces_jointes as $piece_jointe) {

            if(file_exists(storage_path('app/public/' . $piece_jointe->chemin)))
                $zip->addFile(storage_path('app/public/' . $piece_jointe->chemin), $piece_jointe->nom);
        }

        $zip->close();

        return response()->json(['retour' => true, 'chemin' => url('storage/pj/'. $type_element . '/' . $element_id . '/' . $type_element . '_' . $element_id . '.zip')]);
    }
}
