<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Listes_management;
use App\Eden\Managements\Tableau_de_bord_management;

class Tableau_de_bord_controller extends Controller {

	/**
	 * 
	 * Tableau de bord
	 * 
	 */
    public function affiche($id_tableau) {

        $colonnes_non_acces = [];

        if (session()->has('cache.droits_profils.divers_non_acces'))
            $colonnes_non_acces = session()->get('cache.droits_profils.divers_non_acces')['tableau_de_bord'] ?? [];

        if(in_array($id_tableau,$colonnes_non_acces))
            abort(403);
		
		$tableau = modele('tableau_de_bord', $id_tableau);
    	$contenu = modele('tableau_de_bord_contenu')->where('tableau_de_bord', $id_tableau)->orderByRaw('ISNULL(ordre), ordre ASC')->get()->keyBy('id');

        $correspondances_filtres = modele('tableau_de_bord_contenu_correspondance_filtres')
            ->whereIn('tableau_de_bord_contenu',$contenu->pluck('id')->toArray())
            ->get()->groupBy('tableau_de_bord_contenu')->toArray();

        foreach($contenu as $bloc){
            $bloc->correspondances_filtres = $correspondances_filtres[$bloc->id] ?? [];
        }

        define('TABLEAU_DE_BORD_ID',$id_tableau);

        if(!empty(moi_extranet()))
				if($tableau->disponible_extranet != 1)
					return redirect()->route('extranet.acces_restreint');

		foreach($contenu as $clef => $element) {

            if($element->type == 1) {
                list($rapport_management) = Rapports_management::instancie_management($element['element']);
                if(isset($rapport_management->donnees) && $rapport_management->donnees->type_rapport == "liste_libre") {
                    $liste_libre = Liste_libre::where('id_rapport', $element['element'])->first();
                    $table_libre = table_libre($rapport_management->donnees->type_element);

                    if(!empty($rapport_management->donnees->kanban)) {
                        $liste = liste_rapport($element['element']);
                        $donnees_liste = $liste->recupere_liste($liste_libre->id, ['kanban' => $rapport_management->donnees->kanban]);
                        $donnees_liste['options_liste']['kanban'] = $rapport_management->donnees->kanban;
                        $donnees_liste['kanban'] = $rapport_management->donnees->kanban;
                        $element['type_rapport'] = "kanban";
                    } else {
                        $donnees_liste['type_element'] = $liste_libre->type_element;
                        $element['type_rapport'] = 'liste_libre';
                    }

                    $donnees_liste['tableau_de_bord'] = true;
                    $donnees_liste['id_liste'] = $liste_libre->id;
                    $donnees_liste['id_rapport'] = $element['element'];
                    $donnees_liste['table_libre'] = $table_libre;
                    $donnees_liste['modele_par_defaut'] = modele_par_defaut($liste_libre->type_element);
                    $element['donnees_liste'] = $donnees_liste;
                }
            }

    		if($element->type == 2){
				
				$dans_une_boucle = false;

    			$enfants_bloc = modele('tableau_de_bord_contenu')->where('tableau_de_bord', $id_tableau)->where('bloc_parent',$element->id)->orderBy('ordre', 'asc')->get()->toArray();
    			$element->section = array();
				
				foreach($enfants_bloc as $enfant) {
					
					// c'est un début de section
					if($enfant['type'] == 5) {
						
						$dans_une_boucle = true;
						
						// on va devoir boucler suivant les informations de la boucle pour remplir le bloc
						$enfants_bloc_dans_boucle = modele('tableau_de_bord_contenu')
														->where('tableau_de_bord', $id_tableau)
														->where('bloc_parent',$element->id)
														->orderBy('ordre', 'asc')
														->where('id', '>', $enfant['id'])
														->get()
														->toArray();
														
						$valeurs = explode(',', str_replace(array(';', ' '), array(',', ''), $enfant['valeurs']));
						
						foreach($valeurs as $valeur) {
							
							foreach($enfants_bloc_dans_boucle as $enfant_dans_boucle) {
								
								// fin de la boucle
								if($enfant_dans_boucle['type'] == 6)
									break;
									
								// on ajoute les éléments
								
								// une section
								if($enfant_dans_boucle['type'] == 3) {
									
									// c'est nue liste d'utilisateurs
									if($enfant['element'] == 1) {
										
										$type_element = 'utilisateur';
									}
									
									$champs_libres = Champ_libre::where('type_element', $type_element)->get();
									
									foreach($champs_libres as $champ_libre) {
										
										$enfant_dans_boucle['element'] = str_replace('#'.$champ_libre->nom_sql.'#', management($type_element, $valeur)->champ($champ_libre->nom_sql)->affiche(), $enfant_dans_boucle['element']);
									}
									
									$element->section = array_merge($element->section,[$enfant_dans_boucle]);
								}
								
								// un indicateur
								if($enfant_dans_boucle['type'] == 4) {
									
									// on remplace quelques valeurs variables
									$valeur = str_replace('#utilisateur_connecte#', moi()->id, $valeur);
									
									$rapport = rapport($enfant_dans_boucle['element']);
									$rapport->theme = $enfant_dans_boucle['theme'];
									$rapport->parametres_filtres = array('parametre1' => $valeur);
									
									$enfant_dans_boucle['rapport'] = $rapport;

									$element->section = array_merge($element->section,[$enfant_dans_boucle]);									
								}
							}
						}
						
						
						continue;						
					}
					
					
					if($enfant['type'] == 6) {
						
						$dans_une_boucle = false;
						continue;
					}
					
					// on est dans une boucle, on zappe la ligne
					if($dans_une_boucle === true)
						continue;
					
					// Indicateur
					if($enfant['type'] == 4) {

						$rapport = rapport($enfant['element']);
						$rapport->theme = $enfant['theme'];
						$enfant['rapport'] = $rapport;

					}
					
					
					// Rapport
					if($enfant['type'] == 7) {

						$rapport = rapport($enfant['element']);
						$enfant['rapport'] = $rapport;

					}
					
					

					$element->section = array_merge($element->section,[$enfant]);
				}

    		}
    	}

		foreach($contenu as $id => $un_contenu) {

			$sections = $un_contenu->section;
			
			if(is_array($sections)) {
				
				foreach($sections as $id_section => $contneu) {
					
					$sections[$id_section]['rapport'] = '';
				}
			}
			
			$un_contenu->section = $sections;
    		
    	}
        if(empty($liste_taches))
            $liste_taches = [];

		$liste_indicateurs = Rapport_libre::where('type_rapport',"indicateur")->get();
		
		$categories = array();
		$liste_rapports_rapport_par_defaut = '';
		if($tableau->type == 1) {
			
			$categories = Tableau_de_bord_management::recupere_informations_tableau_de_bord_type_liste_de_rapports($tableau);
			
			foreach($categories as $categorie) {
				
				foreach($categorie['rapports'] as $rapport) {
					
					$liste_rapports_rapport_par_defaut = $rapport['rapport']->id_rapport;
					break(2);
				}
			}
		}

        $filtres = management('tableau_de_bord',$id_tableau)->filtres();

		if(fonctionnalite('dashboard_transmission_de_filtres'))
        	$valeurs_filtres = Rapports_management::recupere_parametres('tableau_de_bord_global');
		else
        	$valeurs_filtres = Rapports_management::recupere_parametres('tableau_de_bord_'.$tableau->id);

		return view('eden::tableau_de_bord', [
			'tableau_de_bord' => $tableau,
			'contenu' => $contenu,
			'categories' => $categories,
			'liste_rapports_rapport_par_defaut' => $liste_rapports_rapport_par_defaut,
			'rapports_disponibles' => Rapports_management::rapports_disponibles(),
			'indicateurs_disponibles' => $liste_indicateurs,
			'liste_taches' => $liste_taches,
            'filtres' => $filtres,
            'valeurs_filtres' => $valeurs_filtres,
		]);
    }

	/**
	 * 
	 * Enregistre le nouvel ordre du tableau de bord
	 * 
	 */
    public function change_ordre($id_tableau) {
		
    	$nouvel_ordre = request()->all();

    	foreach($nouvel_ordre as $ordre => $id_contenu) {
    		management('tableau_de_bord_contenu', intval($id_contenu))->enregistre(['ordre' => $ordre]);
    	}

    	return json_encode(['retour' => true]);
    }


	/**
	 * 
	 * Permet d'enregistrer une catégorie sur un tableau de bord de type liste de rapports
	 * 
	 */
	public function enregistrer_categorie() {
		
		$formulaire = request()->all();
		
		if(!empty($formulaire['id'])) {
			
			$management = management('tableau_de_bord_liste_categorie', $formulaire['id']);
		}
		else {
			
			$management = management('tableau_de_bord_liste_categorie');
		}
		
		$donnees = array(
			
			'tableau_de_bord_id' => $formulaire['tableau_de_bord_id'],
			'nom' => $formulaire['nom'],
			'ordre' => $formulaire['ordre'],
		);
		
		$retour = $management->enregistre($donnees);
		
		return response()->json(['retour' => $retour]);
	}

	/**
	 * 
	 * Permet de supprimer une catégorie et ses potentiels rapports
	 * 
	 */
	public function supprimer_categorie() {
		
		$formulaire = request()->all();
			
		$management_categorie = management('tableau_de_bord_liste_categorie', $formulaire['id']);
		
		$rapports_categorie = modele('tableau_de_bord_liste_rapport')->where('tableau_de_bord_id',$formulaire['tableau_de_bord_id'])->where('tableau_de_bord_liste_categorie_id',$formulaire['id'])->get();

		if (!$rapports_categorie->isEmpty()) {
			
			foreach ($rapports_categorie as $rapport) {
				
				$rapport->inactif = 1;
				$rapport->save();
			}
		}

		$retour = $management_categorie->supprime();
		
		return response()->json(['retour' => $retour]);
	}

	/**
	 * 
	 * Permet de supprimer un rapport
	 * 
	 */
	public function supprimer_rapport() {
		
		$formulaire = request()->all();
		
		$modele_rapport = modele('tableau_de_bord_liste_rapport')->where('tableau_de_bord_id', $formulaire['tableau_id'])->where('tableau_de_bord_liste_categorie_id', $formulaire['id_categorie'])->where('id_rapport', $formulaire['id_rapport'])->first();

		$modele_rapport->inactif = 1;

		$retour = $modele_rapport->save();
		
		return response()->json(['retour' => $retour]);
	}
	
	/**
	 * 
	 * Permet d'ajouter un rapport à une catégorie sur un tableau de bord
	 * 
	 */
	public function enregistrer_rapport() {
		
		$formulaire = request()->all();
		
		$donnees = array(
			
			'tableau_de_bord_id' => $formulaire['tableau_de_bord_id'],
			'tableau_de_bord_liste_categorie_id' => $formulaire['tableau_de_bord_liste_categorie_id'],
			'id_rapport' => $formulaire['id_rapport'],
		);
		
		$retour = management('tableau_de_bord_liste_rapport')->enregistre($donnees);
		
		return response()->json(['retour' => $retour]);
	}

    /**
	 *
	 * Permet d'enregistrer les parametres du tableau de bord
	 *
	 */
	public function enregistrer_parametres($tableau_de_bord_id) {

		$parametres = request()->parametres;

		if(fonctionnalite('dashboard_transmission_de_filtres')){

			$filtres_tdb = management('tableau_de_bord',$tableau_de_bord_id)->filtres();
			$ids_filtres_presents = array_column($filtres_tdb, 'id');

			$parametres_avant = Rapports_management::recupere_parametres('tableau_de_bord_global');

			if(!empty($parametres_avant)){

				$parametres_a_reprendre = array_filter($parametres_avant, function($p) use ($ids_filtres_presents) { 
					return !in_array($p['id'], $ids_filtres_presents);
				});

				$parametres = array_merge($parametres_a_reprendre, $parametres ?? []);
			}

        	$retour = Rapports_management::enregistre_parametres('tableau_de_bord_global', $parametres);
		}
		else
        	$retour = Rapports_management::enregistre_parametres('tableau_de_bord_'.$tableau_de_bord_id,$parametres);

		$retour = Rapports_management::enregistre_parametres('tableau_de_bord_'.$tableau_de_bord_id,$parametres);

		return response()->json(['retour' => $retour]);
	}
	
}
