<?php

namespace App\Eden\Controllers\Rapports;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Parametrage\Rapport_management;
use App\Eden\Managements\Rapports\Rapport_tableau_management;
use App\Eden\Variables;
use App\Http\Controllers\Controller;

use App\Eden\Models\Table_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Rapport_favoris;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;

use App\Eden\Managements\Rapports\Rapports_management;

use Illuminate\Http\Request;
use PDF;



/**
 * Ventes par article
 */
class Rapport_controller extends Controller {
	
	/**
	 * 
	 * On affiche un rapport classique en pleine page
	 * 
	 */	
    public function rapport($id_rapport) {

        $rapport_modele = Rapport_libre::where('id_rapport',$id_rapport)->first();

		if($rapport_modele->inactif == 1)
			return redirect()->route('base_eden.rapport.liste')->withErrors([traduction('rapports.erreur_rapport_inactif')]);

        $rapport_modele->parametrage_rapport_libre = (array)json_decode($rapport_modele->parametrage_rapport_libre);

		if(!empty(moi_extranet()) && $rapport_modele->extranet != 1)
			return redirect()->route('extranet.acces_restreint');
		
		if(isset($rapport_modele->titre)) {
			
			$nom_page = '<strong>' . $rapport_modele->titre . '</strong> (Rapport)';
			$url = 'eden/rapport/'.$id_rapport;

			enregistrer_log_historique($url,$nom_page);
		}

		$nom_rapport = false;
        $type = null;
		
		if ($rapport_modele != null) {
            $nom_rapport = traduction($rapport_modele->index_traduction.'.titre');
            $type = $rapport_modele->type_rapport;
        }

        $liste_libre = null;

        $donnees_liste = [];

        if($type == 'liste_libre') {
            $liste_libre = Liste_libre::where('id_rapport', $id_rapport)->first();
            $table_libre = table_libre($rapport_modele->type_element);
            $donnees_liste['type_element'] = $liste_libre->type_element;
            $donnees_liste['id_liste'] = $liste_libre->id;
            $donnees_liste['table_libre'] = $table_libre;
            $donnees_liste['modele_par_defaut'] = service('modele_par_defaut')->recupere($rapport_modele->type_element);
        }

		return view('eden::rapports.rapport', array(
			
			'id_rapport' => $id_rapport,
			'rapport_modifiable' => $rapport_modele->type_rapport !== null,
			'nom_rapport' => $nom_rapport,
			'type_rapport' => $type,
            'liste_libre' => $liste_libre,
            'kanban' => $rapport_modele->kanban,
            'donnees_liste' => $donnees_liste,
		));
    }
	
	/**
	 * 
	 * On affiche un rapport classique en pleine page
	 * 
	 */	
    public function rapport_ajax(Request $formulaire, $id_rapport) {
		
		list($rapport) = Rapports_management::instancie_management($id_rapport);

        $html = $rapport->genere(true);

        $rapport_retour = (array) ($rapport->rapport ?? (!empty($rapport->donnees) ? $rapport->donnees->getAttributes() : $rapport));

        if(
            (isset($rapport_retour['rapport_libre']) && in_array($rapport_retour['rapport_libre']->type_rapport, ['tableau']))
            || (isset($rapport_retour['type_rapport']) && in_array($rapport_retour['type_rapport'], ['tableau']))
        ) {
            $rapport_retour = array_merge($rapport_retour, ['html' => $html]);
        }

		return response()->json($rapport_retour);
    }
	
	
	/**
	 * 
	 * Affiche la liste des rapports
	 * 
	 */
	public function liste_des_rapports() {

        $nom_page = 'Liste des rapports';
        $url = 'eden/rapports';

        enregistrer_log_historique($url,$nom_page);
		
		$categories = Rapports_management::rapports_disponibles();

		return view('eden::rapports.liste_des_rapports', [
			'categories' => $categories,
		]);
    }
	
	/**
	 * 
	 * Modifier le rapport voulu
	 * 
	 */
	public function modifier_rapport(Request $formulaire) {

		if($formulaire->id !== null)
			$rapport = Rapport_libre::where('id', $formulaire->id)->first();
		else
			$rapport = new Rapport_libre;

        if($formulaire->has('titre'))
		    $rapport->titre = $formulaire->titre;
		$rapport->categorie = $formulaire->categorie;
		$rapport->icone = $formulaire->icone;
		$rapport->inactif = $formulaire->inactif;
        if($formulaire->has('description'))
		    $rapport->description = $formulaire->description;
		$rapport->objectif = json_encode($formulaire->objectif);
		
		if($formulaire->parametrage !== null)
			$rapport->parametrage = json_encode($formulaire->parametrage);
		
		$parametrage_rapport_libre = array();
		
		if(!empty($rapport->parametrage_rapport_libre)) {
			
			$parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre, true);
		}
		
		if($formulaire->parametrage_rapport_libre !== null) {
			
			foreach($formulaire->parametrage_rapport_libre as $champ => $valeur) {
				
				$parametrage_rapport_libre[$champ] = $valeur;
			}
		}
		
		$rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);
		
		
		if($formulaire->type !== null)
			$rapport->type = $formulaire->type;

		// Gestion du kanban
		if ($formulaire->kanban != null) {
			
			$cl_kanban = Champ_libre::where('id_cl',$formulaire->kanban)->first();
			if ($cl_kanban == null) 
				$cl_kanban = Champ_libre::where('nom_sql',$formulaire->kanban)->first();	

			
			$rapport->kanban = $cl_kanban->nom_sql;

			$array_colonnes_kanban = array();
			
			$formulaire->colonnes_kanban = json_decode($formulaire->colonnes_kanban);
			
			// On gère pour la colonne kanban_colonne
			foreach ($formulaire->colonnes_kanban as $colonnes) {
				
				// On vérifie que l'on récupère les colonnes coché du bon champ qui est sélectionné en kanban
				if ($formulaire->kanban == $colonnes->champ_libre_sql) {
					
					// On parcourt les choix pour savoir si ils sont cochés ou non
					foreach ($colonnes->colonnes as $index => $choix) {
						
						// Le choix est oché, on l'ajoute à notre array initialisé plus haut
						if ($choix->checked === true) 
							$array_colonnes_kanban[] = $index+1;
						
					}
				}
			}
			$rapport->kanban_colonnes = json_encode($array_colonnes_kanban);
		}
		else{
			$rapport->kanban_colonnes = null;
			$rapport->kanban = null;
		}
		
		if (empty(json_decode($rapport->kanban_colonnes))) {
			$rapport->kanban_colonnes = null;

		}

		$rapport->save();
		
        if($rapport->id_rapport === null) {
			
			$rapport->id_rapport = 'rapport_parametrable_'.$rapport->id;
			$rapport->save();
		}
    }
    

    /**
	 * 
	 * Afficher le rapport
	 * 
	 */
	public function info_rapport($id_rapport) {
		
		$rapport = Rapport_libre::where('id_rapport', $id_rapport)->first();
		
		$rapport->objectif = json_decode($rapport->objectif);
		
		if(!is_object($rapport->objectif))
			$rapport->objectif = new \StdClass;
		
		$rapport->parametrage = json_decode($rapport->parametrage);
		$rapport->parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre);
		
		if(!is_object($rapport->parametrage))
			$rapport->parametrage = new \StdClass;
		
		if(!is_object($rapport->parametrage_rapport_libre))
			$rapport->parametrage_rapport_libre = new \StdClass;

		$liste_count = Liste_libre::where('id_rapport',$rapport->id_rapport)->count();
		
		// Le rapport est de type liste
		if ($liste_count > 0) {
						
			$rapport->type_liste = 1;
			$liste = Liste_libre::where('id_rapport',$rapport->id_rapport)->first();
			$champs_libres_kanban = Champ_libre::where('type_element',$liste->type_element)->whereIn('type',[1,20])->get();



			// on a au moins un champ, on va chercher ses valeurs possibles
			if (!$champs_libres_kanban->isEmpty()) {
			
				$array_colonnes = array();
				foreach ($champs_libres_kanban as $champ_libre) {

					$champs_possibles = management($champ_libre->type_element)->champ($champ_libre->nom_sql)->valeurs_possibles;
					ksort($champs_possibles);
					
					// Le rapport doit avoir un kanban pour avoir des colonnes kanban
					if ($rapport->kanban != null) {
						
						// Le kanban doit être le meme que le champ actuel
						if ($rapport->kanban == $champ_libre->nom_sql) {
							
							$kanban_actuel = true;
						}
						else{
							$kanban_actuel = false;
						}
					}
					else{
						$kanban_actuel = false;
					}

					$array_colonnes_possibles = array();

					// Cas où on doit chercher les colonnes kanban à précocher
					if ($kanban_actuel) {

						foreach ($champs_possibles as $clef => $valeur) {

							if (json_decode($rapport->kanban_colonnes) != null) {
								
								// On coche car cela se trouve en base
								if (in_array($clef, json_decode($rapport->kanban_colonnes)) ) {
									$array_colonnes_possibles[] = [
										'checked' => true,
										'valeur' => $valeur,
									];
								}
								//On coche pas
								else{
										$array_colonnes_possibles[] = [
										'checked' => false,
										'valeur' => $valeur,
									];
								}
							}

							else{
								$array_colonnes_possibles[] = [
										'checked' => false,
										'valeur' => $valeur,
									];
							}
						}
					}
					// Cas où tout doit être décoché
					else{

						foreach ($champs_possibles as $clef => $valeur) {

							$array_colonnes_possibles[] = [
								'checked' => false,
								'valeur' => $valeur,
							];
						}
					}
								
					$array_colonnes[] = [
											'champ_libre_sql' => $champ_libre->nom_sql,
											'champ_libre_id' => $champ_libre->id_cl,
											'colonnes' => $array_colonnes_possibles,
										];
					
				}
				$rapport->colonnes_kanban = $array_colonnes;								
			}

			$rapport->champs_libres_kanban = $champs_libres_kanban->toArray();

		}
		else{

			$rapport->type_liste = 0;	
		}
		
		return $rapport;
    }
    
	/**
	 *
	 * Ajoute ou retire un rapport des favoris pour l'utilisateur
	 *
	 */
	public function gestion_favoris($id_rapport) {
		
		$rapport_favoris = Rapport_favoris::where('utilisateur_id', moi()->id)->where('id_rapport', $id_rapport)->first();
		
		if($rapport_favoris !== null) {
			
			$rapport_favoris->delete();
		}
		else {
			
			$rapport_favoris = new Rapport_favoris;
			$rapport_favoris->utilisateur_id = moi()->id;
			$rapport_favoris->id_rapport = $id_rapport;
			$rapport_favoris->save();
		}
		
		return response()->json(array(true));
	}
	
	/**
	 * 
	 * Modifier ordre du rapport
	 * 
	 */
	public function modifier_ordre_rapport(Request $formulaire) {
		
		$rapport = Rapport_libre::where('id_rapport', $formulaire->id)->first();
		$rapport->ordre=$formulaire->ordre;
		$rapport->save();
		return $rapport;
    }
	
	/**
	 * 
	 * Permet de créer un nouveau rapport directement depuis l'interface
	 * 
	 */
	public function creer_nouveau_rapport($id_rapport = false,$duplication = false){

		if($id_rapport !== false) {
			
			$rapport_libre = Rapport_libre::where('id_rapport', $id_rapport)->first();
			$rapport_libre->parametrage_rapport_libre = json_decode($rapport_libre->parametrage_rapport_libre);
			
			if(empty($rapport_libre->parametrage_rapport_libre))
				$rapport_libre->parametrage_rapport_libre = new \StdClass;
			
			if(empty($rapport_libre->parametrage_rapport_libre->series))
				$rapport_libre->parametrage_rapport_libre->series = array();
			
			if(empty($rapport_libre->parametrage_rapport_libre->objectif))
				$rapport_libre->parametrage_rapport_libre->objectif = array();

            if($duplication) {

                $rapport_libre->id_rapport_dupliquer = $rapport_libre->id_rapport;
                $rapport_libre->id = null;
                $rapport_libre->id_rapport = null;
                $rapport_libre->titre = null;
                $rapport_libre->index_traduction = null;
                $rapport_libre->ordre = null;
                $rapport_libre->rapport_sur_fiche = 0;

                foreach ($rapport_libre->parametrage_rapport_libre->series as $serie) {
                    $serie->nom = traduction($serie->index_traduction . '.nom');
                    unset($serie->index_traduction);
                }
            }
			
		}
		else {
			
			$rapport_libre = new Rapport_libre;
			$rapport_libre->parametrage_rapport_libre = new \StdClass;

            $serie_modele = new \StdClass;

            $serie_modele->id = 1;
            $serie_modele->nom = '';
            $serie_modele->type_calcul = '';
            $serie_modele->champ_calcul = '';
			
			$rapport_libre->parametrage_rapport_libre->series = array($serie_modele);

			$rapport_libre->parametrage_rapport_libre->objectif = array();
				
			$rapport_libre->type_rapport = 'liste_libre';
            $rapport_libre->rapport_sur_fiche = 0;
		}
		
		$elements = Table_libre::orderBy('type_element')->get();

		$categories = Rapports_management::rapports_disponibles();

        $champs_libres = Champ_libre::orderBy('nom')
                        ->where(function($r) {
                            $r->whereNull('inactif')
                                ->orWhere('inactif', 0);
                        })
                        ->where('type', '>=', 0)
                        ->whereNotIn('type', array(10,13))
                        ->get();

        $types_elements_fiche = $elements->where('fiche', 1)->sortBy('element')->pluck('type_element')->toArray();

        $types_elements_fiche = array_merge(Variables::$documents_gescom,$types_elements_fiche);

        sort($types_elements_fiche);

        $champs_element = champs_libres_elements($elements->pluck('type_element')->toArray());

        $champs_selection_element= [];

		$types_vues_carte = Table_libre::where('type_element', 'adresse')
			->orWhereIn('type_element', 
				modele('vue_sql')
					->where('table_par_defaut', 'adresse')
					->select('nom_sql')
			)
			->get();

        foreach($champs_element as $type_element => $champs_par_type) {

            $champs_selection_type_element_fiche[$type_element] = [];

            if (!empty($champs_par_type['42']))
                $champs_selection_element[$type_element] = $champs_par_type['42']->keyBy('nom_sql')->toArray();

            if (!empty($champs_par_type['22']))
                $champs_selection_element[$type_element] = array_merge($champs_selection_element[$type_element] ?? [],$champs_par_type['22']->keyBy('nom_sql')->toArray());
        }

        return view('eden::rapports.creer_nouveau_rapport',[
			
			'elements' => $elements,
			'types_vues_carte' => $types_vues_carte,
            'types_elements_fiche' => $types_elements_fiche,
			'categories' => $categories,
            'champs_selection_element' => $champs_selection_element,
			'rapport_libre' => $rapport_libre,
			'duplication' => $duplication,
		]);
	}

    /**
	 *
	 * Permet de dupliquer rapport directement depuis l'interface
	 *
	 */
	public function dupliquer_rapport($id_rapport){

        return $this->creer_nouveau_rapport($id_rapport,true);
	}

	public function parametrer_rapport($id_rapport){
		
		$rapport_libre = Rapport_libre::where('id_rapport', $id_rapport)->first();
		$rapport_libre->parametrage_rapport_libre = json_decode($rapport_libre->parametrage_rapport_libre);
		
		if(empty($rapport_libre->parametrage_rapport_libre))
			$rapport_libre->parametrage_rapport_libre = new \StdClass;
		
		if(empty($rapport_libre->parametrage_rapport_libre->series))
			$rapport_libre->parametrage_rapport_libre->series = array();
		
		if(empty($rapport_libre->parametrage_rapport_libre->objectif))
			$rapport_libre->parametrage_rapport_libre->objectif = array();

		if(empty($rapport_libre->parametrage_rapport_libre->positionnement))
			$rapport_libre->parametrage_rapport_libre->positionnement = '';

		$categories = Rapports_management::rapports_disponibles();

        $elements = Table_libre::orderBy('type_element')->get();

        $type_element = $rapport_libre->type_element;

        if($rapport_libre->type_rapport == 'carte')
            $type_element = 'adresse';

        $champs_libres = Champ_libre::where('type_element', $type_element)
            ->orderBy('nom')
            ->where(function($r) {
                $r->whereNull('inactif')->orWhere('inactif', 0);
            })
            ->where('type', '>=', 0)
            ->whereNotIn('type', array(10,13))
            ->get();

        $types_elements_carte = [];

        if($rapport_libre->type_rapport == 'carte'){
			$types_elements_carte = $champs_libres->where('type', 42)->pluck('type_element_ajax');
			$champs_mappage_geolocalisation = champs_libres($rapport_libre->type_element);
		}

		$champs_listes = $champs_libres->whereIn('type', array(1,20,42))->groupBy('type_element');
		$champs_nombre = $champs_libres->whereIn('type', array(2,3))->groupBy('type_element');
		$champs_dates = $champs_libres->whereIn('type', array(4,5))->groupBy('type_element');
		$champs_dates_et_liste = collect([
			$type_element => ($champs_listes[$type_element] ?? collect())->concat($champs_dates[$type_element] ?? collect())
		]);

        $champs_libres_elements_carte = Champ_libre::whereIn('type_element', [...$types_elements_carte, 'adresse'])
            ->orderBy('nom')
            ->where(function($r) {
                $r->whereNull('inactif')->orWhere('inactif', 0);
            })
            ->where('type', '>=', 0)
            ->whereNotIn('type', array(10,13))
            ->get();

        $types_elements_fiche = $elements->where('fiche', 1)->sortBy('element')->pluck('type_element')->toArray();

        $types_elements_fiche = array_merge(Variables::$documents_gescom,$types_elements_fiche);

        sort($types_elements_fiche);
		
		$serie_vide = ['nom' => 'Nouvelle série'];

        $champs_libres_elements_carte = $champs_libres_elements_carte->groupBy('type_element');

		if(($rapport_libre->type_rapport == 'diagramme_circulaire' || $rapport_libre->type_rapport == 'graphique_funnel') && !isset($rapport_libre->parametrage_rapport_libre->serie)) {
			$rapport_libre->parametrage_rapport_libre->serie = $serie_vide + ['type_calcul' => ''];
		}

		if($rapport_libre->type_rapport == 'indicateur' && empty($rapport_libre->lien_rapport)) {
			$rapport_libre->lien_rapport = "";
		}

        return view('eden::parametrage.parametrer_rapport',[
			
			'categories' => $categories,
			'types_elements_carte' => $types_elements_carte,
			'champs_dates_et_liste' => $champs_dates_et_liste,
			'champs_dates' => $champs_dates,
			'champs_nombre' => $champs_nombre,
			'champs_libres' => $champs_libres,
			'rapport_libre' => $rapport_libre,
			'serie_vide' => collect($serie_vide),
            'types_elements_fiche' => $types_elements_fiche,
            'champs_libres_elements_carte' => $champs_libres_elements_carte,
			'champs_mappage_geolocalisation' => $champs_mappage_geolocalisation ?? collect(),
		]);
	}
	
	/**
	 * 
	 * Enregistre un nouveau rapport libre
	 * 
	 */
	public function enregistrer_parametrage_rapport(Request $formulaire) {

        $rapport_management = new Rapport_management();

        $retour = $rapport_management->enregistrer_nouveau_rapport($formulaire->all());

        return response()->json($retour);
	}

	/**
	 * 
	 * Lancer l'export du rapport
	 * 
	 */
	
	public function export_excel($id_rapport, Request $request) {
		
		list($rapport) = Rapports_management::instancie_management($id_rapport);
		
		define('export_excel_en_cours', true);

		$filtres_selectionnes = $request->input('filtres', []);
		$valeurs_filtres   = $request->input('valeurs_filtres', []);

		$filtres = !empty($filtres_selectionnes) && !empty($valeurs_filtres)
			? [
				'filtres' => array_column($filtres_selectionnes, null, 'id'),
				'valeurs_filtres' => $valeurs_filtres,
			]
			: null;

		$rapport->genere(false, $filtres);

		$titres = $rapport->titres;
		$resultats = $rapport->resultats;

		$champ_axe_x = $rapport->parametrage_rapport_libre['axe_x'] ?? null;
		$champ_axe_y = $rapport->parametrage_rapport_libre['axe_y'] ?? null;

		$entetes = $titres[$champ_axe_x] ?? [];

		$lignes = [];

		foreach($titres[$champ_axe_y] as $axe_y) {

			$ligne = ['axe_y' => $axe_y['nom']];

			$resultats_y = collect($resultats)->where($champ_axe_y, $axe_y['id']);

			foreach($entetes as $axe_x) {

				$valeur = $resultats_y->where($champ_axe_x,$axe_x['id'])->first()['total_1'] ?? 0;

				$ligne[$axe_x['id']] = $valeur;
			}

			$lignes[] = $ligne;
		}
		
		$nom_du_ficher = "export_$id_rapport"."_".date('Y-m-d H:i:s').".xlsx";

		$colonnes = array_merge(
				[['id'=> 'axe_y','nom' => traduction('champs_libres.'.$rapport->rapport_libre->type_element.'.'.$champ_axe_y.'.nom')]],
				$entetes);

		$colonnes = collect(array_map(function($colonne) { return (object) $colonne; }, $colonnes));

		$donnees = [
			'colonnes' => $colonnes,
			'lignes' => $lignes,
		];

		service('export')->exporter_xlsx_csv($donnees, $nom_du_ficher);

		return response()->json(['url_fichier' => asset('storage/exports/'.$nom_du_ficher)]);
	}

    /*
     *
     * Génère le PDF du rapport, le stocke sur le serveur puis l'affiche dans le navigateur.
     *
     */
	public function export_pdf($id_rapport, $orientation = 'portrait', $format_papier = 'a4') {

		list($rapport) = Rapports_management::instancie_management($id_rapport);
		
		$rapport = $rapport->genere();	

		$donnees_pour_pdf = array(
			
			'lignes' => $rapport->lignes,
			'titre' => $rapport->titre_du_rapport,
			'id_rapport' => $id_rapport,
			'options' => $rapport->options,
		);
				
		// on génère le PDF 
        $view = view('eden::pdf.export_pdf', $donnees_pour_pdf);
        $html = $view->render();
        $html = preg_replace('/>\s+</', "><", $html);
        $pdf = PDF::loadHTML($html);
        $pdf = $pdf->setPaper($format_papier, $orientation);

		$nom_du_ficher = "export_$id_rapport.pdf";

		\Storage::put("public/$nom_du_ficher", $pdf->output());

		return redirect(asset('storage/' . $nom_du_ficher));
	}

	/**
	 *
	 * Enregistrer l'abonnement au rapport
	 *
	 */
	public function abonnement($liste_id, $frequence) {

		$type = '';
		$id_rapport = '';
		
		if(request()->type !== null) {
			
			$type = request()->type;
			$id_rapport = $liste_id;
			
			// on va chercher les paramètres actuels du rapport
			$parametres = base64_encode(serialize(Rapports_management::recupere_parametres($liste_id)));
			
			
		}
		else {
			
			$parametres = json_encode(request()->all());
		}
			

		if($frequence == 'jour') {

			$frequence_detail = request()->heure.'h';

		} elseif($frequence == 'semaine') {

			$frequence_detail = request()->jour_semaine.' '.request()->heure.'h';

		} elseif($frequence == 'mois') {

			$frequence_detail = request()->jour_mois.' du mois '.request()->heure.'h';

		} elseif($frequence == 'annee') {

			$frequence_detail = request()->jour_annee.' '.request()->mois_annee.' '.request()->heure.'h';

		} else {

			$frequence_detail = 'erreur';
		}

		management('rapport_abonnement')
					->enregistre([
									'nom' 	 			=> request()->nom,
									'liste_id' 	     	=> $liste_id,
									'id_rapport' 	    => $id_rapport,
									'utilisateur_id' 	=> moi()->id,
									'parametres' 	 	=> $parametres,
									'frequence' 	 	=> $frequence,
									'frequence_detail' 	=> $frequence_detail,
									'type' 				=> $type,
								]);

		return json_encode(['retour' => true]);
	}

    /**
     *
     * Permet de supprimer ou désactiver un rapport
     *
     */
    public function supprimer($id_rapport){

        $rapport = Rapport_libre::where('id_rapport',$id_rapport)->first();

        if(empty($rapport))
            return response()->json(array('retour' => false, 'message' => traduction('messages.php.rapport.rapport_introuvable')));

        $liste = Liste_libre::where('id_rapport', $id_rapport)->first();

        $retour = Rapport_management::verification_utilisation($id_rapport,$liste);

        if($retour !== true)
            return response()->json(array('retour' => false, 'message' => $retour));

        $rapport_standard = Rapport_management::rapport_standard($rapport);

        // Si c'est un rapport, on désactive le rapport
        if($rapport_standard === true){

            $rapport->inactif = 1;
            $rapport->save();

            if($liste !== null)
                $retour = Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);
            else
                $retour = Rapport_management::generer_fichier_migration_rapport($rapport->id);

        }
        // Si c'est un rapport spécifique, on supprimer le rapport
        else{

            $retour = Rapport_management::supprimer($rapport,$liste);
        }

        return response()->json(array('retour' => $retour));

    }

    /**
     *
     * Permet de réactiver un rapport
     *
     */
    public function activer($id_rapport){

        $rapport = Rapport_libre::where('id_rapport',$id_rapport)->first();

        if(empty($rapport))
            return response()->json(array('retour' => false, 'message' => traduction('messages.php.rapport.rapport_introuvable')));

        $liste = Liste_libre::where('id_rapport', $id_rapport)->first();

        $rapport->inactif = 0;
        $rapport->save();

        if($liste !== null)
            $retour = Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);
        else
            $retour = Rapport_management::generer_fichier_migration_rapport($rapport->id);

        return response()->json(array('retour' => $retour));
    }
}
