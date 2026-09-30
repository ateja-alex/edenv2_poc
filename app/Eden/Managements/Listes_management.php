<?php

namespace App\Eden\Managements;

use App\Eden\Models\Liste_libre_parametre_requete;
use App\Eden\Models\Rapport_parametre;
use Illuminate\Support\Str;

use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Liste_libre_filtre_enregistre;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Variables;
use App\Eden\Managements\Parametrage\Champ_libre_management as Parametrage_champ_libre_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use DB;

/**
* Gestion des listes
*/
class Listes_management {

    public $champs_libres = false;

	/**
	 *
	 * On initialise les paramètres de la liste
	 *
	 */
	protected function initialise_parametres_liste($parametres, $liste_libre, $colonnes) {

		// doit on inclure les éléments inactifs ?
		$parametres['avec_inactifs'] = $liste_libre->avec_inactifs;

		if((!isset($parametres['tri']) || empty($parametres['tri'])) && empty($parametres['kanban'])) {

            $tri_colonne_par_defaut = false;

            $parametres['tri'] = null;

            foreach($colonnes as $colonne){

                if($tri_colonne_par_defaut === false && !empty($colonne->tri_par_defaut)){
                    $parametres['tri'] = $colonne->id;
                    $parametres['direction_tri'] = $colonne->sens_tri_par_defaut != 1 ? 0 : 1;
                    $tri_colonne_par_defaut = true;
                }
            }
		}

		$parametres['bloquer_tri'] = $liste_libre->bloquer_tri;

		if(!isset($parametres['page']) || empty($parametres['page']))
			$parametres['page'] = 1;

		// on cast
		$parametres['page'] = (int) $parametres['page'];

		// les filtres sur les fiches
		if(isset($parametres['filtres_sur_fiche'])) {

			if(!is_array($parametres['filtres_sur_fiche']))
				$filtres_sur_fiche = json_decode(base64_decode($parametres['filtres_sur_fiche']), true);
			else
				$filtres_sur_fiche = $parametres['filtres_sur_fiche'];

			if(!isset($parametres['filtres']))
				$parametres['filtres'] = array();

			foreach($filtres_sur_fiche as $nom => $filtre) {

				$parametres['filtres'][$nom] = $filtre;
			}
		}

		// le cas ou on limite à X résultats dans le rapport
		if(isset($liste_libre->limit) && !empty($liste_libre->limit)) {

			$parametres['limit'] = $liste_libre->limit;
		}

		// le cas ou on force un order by par défaut
		if(isset($liste_libre->orderby) && !empty($liste_libre->orderby)) {

			$parametres['orderby'] = $liste_libre->orderby;

			// par défaut
			$parametres['orderby_sens'] = 'ASC';
		}

		// le cas ou on force un sens pour l'order by par défaut
		if(isset($liste_libre->orderby_sens) && !empty($liste_libre->orderby_sens)) {

			$parametres['orderby_sens'] = $liste_libre->orderby_sens;
		}

		// le cas ou l'utilisateur a demandé des sous totaux
		if(!empty($parametres['sous_total_niveau_1'])) {

			// on force un order by
			$parametres['orderby'] = $parametres['sous_total_niveau_1'];
			$parametres['orderby_sens'] = 'ASC';

			if(!empty($parametres['sous_total_niveau_2'])) {


				$parametres['orderby'] .= ', '.$parametres['sous_total_niveau_2'];
			}
		}

		if(!empty($parametres['orderby_avec_sous_totaux'])) {

			$parametres['orderby'] .= ', '.$parametres['orderby_avec_sous_totaux'];
		}

		return $parametres;
	}

	/**
	 *
	 * On récupère les calculs de la liste
	 *
	 */
	public function recupere_informations_calculs($id_liste, $type_element, $parametres) {

		$calculs_libres = Liste_libre_calcul::where('liste_libre_id', $id_liste)->orderBy('ordre')->get();
		$liste_libre = Liste_libre::find($id_liste);

		$traitement_calculs_specifiques = $this->traitement_calculs_specifiques();

        $parametres['calcul'] = true;

		// s'il y a un sous total on l'ajoute à la volée
		// on simule en réalité un calcul habituel
		if(!empty($parametres['sous_total_niveau_1'])) {

			$nouveau_calcul = new Liste_libre_calcul;

			$nouveau_calcul->split = $parametres['sous_total_niveau_1'];
			$nouveau_calcul->nom = 'Sous total #1';
			$nouveau_calcul->taille = 1;

			// par défaut
			$nouveau_calcul->type_calcul = 'COUNT';
			$nouveau_calcul->nom_sql = 'id';

			if(!empty($parametres['sous_total_niveau_1_champ'])) {

				$nouveau_calcul->type_calcul = 'SUM';
				$nouveau_calcul->nom_sql = $parametres['sous_total_niveau_1_champ'];
			}

			$calculs_libres->push($nouveau_calcul);
		}

		temps_execution(' =>  => => calculs : sous total');


		$calculs = array();

		if($calculs_libres === null)
			return $calculs;

		foreach($calculs_libres as $calcul) {
            $type_element_calcul = !empty($calcul->type_element) ? $calcul->type_element : $type_element;

            $index_traduction = $calcul->index_traduction;

            $champ_affichage = !empty($calcul->champ_reference) ? $calcul->champ_reference : $calcul->nom_sql;

			// on doit faire un calcul sur la liste
			if(empty($calcul->split)) {

                $traitement_valeur_changante = '';

				if(isset($traitement_calculs_specifiques['valeurs_changantes'][$calcul->nom_sql]))
                    $traitement_valeur_changante = $traitement_calculs_specifiques['valeurs_changantes'][$calcul->nom_sql];

                if(!empty($this->liste_requete)){

                    $requete = "SELECT ".$calcul->type_calcul . "(". ($type_element_calcul != $type_element ? ' DISTINCT ' . $type_element_calcul.'.' : '') .$calcul->nom_sql . " " . $traitement_valeur_changante . ") as resultat FROM (".
                        $this->liste_requete.") as tmp";

                    if($type_element_calcul != $type_element)
                            $requete .= " JOIN ".$type_element_calcul." ON ".$type_element_calcul.".id = tmp.".$calcul->champ_de_liaison;

                    if(!empty($parametres['lignes_selectionnees']))
                        $requete .= " WHERE tmp.id IN (".implode(',', $parametres['lignes_selectionnees']).")";

                    $resultat_calcul = DB::select($requete)[0];
                }
                else {
                    $resultat_calcul_requete = clone($this->cree_requete($type_element, $parametres));
                    if($type_element_calcul != $type_element)
                        $resultat_calcul_requete = $resultat_calcul_requete->join($type_element_calcul, $type_element_calcul.'.id', '=', $type_element.'.'.$calcul->champ_de_liaison);

                    if(!empty($parametres['lignes_selectionnees']))
                        $resultat_calcul_requete->whereIn($type_element.'.id', $parametres['lignes_selectionnees']);
                    
                    $resultat_calcul = $resultat_calcul_requete->select(DB::raw($calcul->type_calcul . "(" . ($type_element_calcul != $type_element ? ' DISTINCT ' : '') . $type_element_calcul.'.'.$calcul->nom_sql . " " . $traitement_valeur_changante . ") as resultat"))->first();
                }

                $resultat_calcul = $calcul->nom_sql != 'id' ? management($type_element_calcul)->champ($champ_affichage)->affiche($resultat_calcul->resultat) : $resultat_calcul->resultat;

                if($resultat_calcul == '')
                    $resultat_calcul = 0;

				$calculs[] = array(

					'id' => $calcul->id,
					'nom' => traduction($index_traduction.'.nom'),
					'resultat' => $resultat_calcul.' '.$calcul->unite,
					'type' => 'une_valeur',
					'taille' => $calcul->taille,
					'icone' => $calcul->icone,
					'v_if' => $calcul->v_if,
                    'afficher_somme' => $calcul->afficher_somme,
                    'toujours_deploye' => $calcul->toujours_deploye,
					'affichage_resultats' => false,
                    'index_traduction' => $index_traduction,
				);

				temps_execution(' =>  => => calculs : '.$calcul->nom);
			}
			else {

				$type_calcul = $calcul->type_calcul;

                if (str_contains($calcul->split, '.')) {
                    [$champ_liaison, $champ_colonne] = explode('.', $calcul->split, 2);
                    $type_element_champ = $calcul->type_element_split;
                    $split_sql = $type_element_champ.'.'.$champ_colonne;
                    $nom_sql_champ = $champ_colonne;
                }else{
                    $split_sql = $type_element . "." . $calcul->split;
                    $type_element_champ = $type_element;
                    $nom_sql_champ = $calcul->split;
                }

				if($calcul->type_calcul == 'AVG_COUNT')
					$type_calcul = 'COUNT';

				// Si on ne veut afficher que les x premiers résultats
				if(!empty($calcul->top)) {
					$parametres['tri'] = '';
				}

                $parametres['sans_order_id'] = true;

				temps_execution(' => => => calculs : '.$calcul->nom.' => cree_requete');

                $traitement_valeur_changante = '';

                if(isset($traitement_calculs_specifiques['valeurs_changantes'][$calcul->nom_sql]))
                    $traitement_valeur_changante = $traitement_calculs_specifiques['valeurs_changantes'][$calcul->nom_sql];

                if(!empty($this->liste_requete)){

                    $requete = "SELECT ".$type_calcul . "(". ($type_element_calcul != $type_element ? ' DISTINCT ' . $type_element_calcul . '.' : '') .$calcul->nom_sql . " " . $traitement_valeur_changante . ") as resultat, COALESCE(" . (str_contains($calcul->split, '.') ? $split_sql : "tmp.".$nom_sql_champ) . ",0) as colonne_split FROM (".
                        $this->liste_requete.") as tmp";
                    
                    if($type_element_calcul != $type_element)
                        $requete .= " JOIN ".$type_element_calcul." ON ".$type_element_calcul.".id = tmp.".$calcul->champ_de_liaison;

                    if($type_element_calcul != $type_element_champ)
                        $requete .= " JOIN ".$type_element_champ." ON ".$type_element_champ.".id = tmp.".$champ_liaison;
                    
                    if(!empty($parametres['lignes_selectionnees'])){
                        
                        $requete .= " WHERE ".$type_element.".id IN (".implode(',', $parametres['lignes_selectionnees']).")";
                    }

                    $requete .= " GROUP BY colonne_split ".
                        (!empty($calcul->top) ? 'ORDER BY resultat DESC LIMIT '.$calcul->top : 'ORDER BY tmp.id');

                    $resultat_calcul_split = collect(DB::select($requete));
                }
                else {
                    // On récupère la requète
                    $resultat_calcul_split = clone($this->cree_requete($type_element, $parametres));

                    if($type_element_calcul != $type_element)
                        $resultat_calcul_split = $resultat_calcul_split->join($type_element_calcul, $type_element_calcul.'.id', '=', $type_element.'.'.$calcul->champ_de_liaison);

                    if($type_element_calcul != $type_element_champ)
                        $resultat_calcul_split = $resultat_calcul_split->join($type_element_champ, $type_element_champ.'.id', '=', $type_element_calcul.'.'.$champ_liaison);

                    if(!empty($parametres['lignes_selectionnees']))
                        $resultat_calcul_split->whereIn($type_element.'.id', $parametres['lignes_selectionnees']);

                    // Si on ne veut afficher que les x premiers résultats
                    if (!empty($calcul->top)) {
                        $resultat_calcul_split = $resultat_calcul_split
                            ->orderBy('resultat', 'desc')
                            ->take($calcul->top);
                    }

                    $resultat_calcul_split = $resultat_calcul_split
                        ->select(DB::raw($type_calcul . "(" . ($type_element_calcul != $type_element ? ' DISTINCT ' : '') . $type_element_calcul . "." . $calcul->nom_sql . " " . $traitement_valeur_changante . ") as resultat, COALESCE(" . $split_sql . ",0) as colonne_split"))
                        ->orderBy($type_element_calcul . '.id')
                        ->groupBy('colonne_split')->get();
                }

				temps_execution(' => => => calculs : '.$calcul->nom.' => resultat requete');


				if($resultat_calcul_split === null) {

					temps_execution(' =>  => => calculs : '.$calcul->nom);
					continue;
				}

				$resultats_calcul = array();

				if($calcul->type_calcul == 'AVG_COUNT') {

					$resultats_calcul['Moyenne'] = round($resultat_calcul_split->avg('resultat'), 2);
				}
				else {

					$somme = 0;

					foreach($resultat_calcul_split as $resultat) {

						$valeur = $calcul->split != 'id' ? management($type_element_champ)->champ($nom_sql_champ)->affiche($resultat->colonne_split) : $resultat->colonne_split;

						if(empty($valeur))
							$valeur = 'Sans valeur';

						if(empty($resultat->resultat))
							$resultat->resultat = 0;

						if(isset($resultats_calcul[$valeur]))
							$resultats_calcul[$valeur] += $resultat->resultat;
						else
							$resultats_calcul[$valeur] = $resultat->resultat;

						$somme += $resultat->resultat;
					}

                    foreach ($resultats_calcul as $valeur => $resultat_calcul) {
                        $resultats_calcul[$valeur] = ($calcul->nom_sql != 'id' ? management($type_element_calcul)->champ($champ_affichage)->affiche($resultat_calcul) : $resultat_calcul) . ' ' . $calcul->unite;
                    }

					$resultat_titre = ($calcul->nom_sql != 'id' ? management($type_element_calcul)->champ($champ_affichage)->affiche($somme) : $somme).' '.$calcul->unite;
				}

				$temp = array(

					'id' => $calcul->id,
					'nom' => traduction($index_traduction.'.nom'),
					'resultat' => $resultats_calcul,
					'type' => 'liste_de_valeurs',
					'taille' => $calcul->taille,
                    'afficher_somme' => $calcul->afficher_somme,
                    'toujours_deploye' => $calcul->toujours_deploye,
					'affichage_resultats' => false,
                    'index_traduction' => $index_traduction,
				);

				if(isset($resultat_titre)) {
					$temp['titre'] = $resultat_titre;
				}

				$calculs[] = $temp;

				temps_execution(' =>  => => calculs : '.$calcul->nom);
			}


		}

		return $calculs;

	}

    /**
     *
     * Fonction qui permet d'effectuer un traitement particulier sur les calculs
     *
     */
    protected function traitement_calculs_specifiques() {

        return [];
    }

	/**
	 *
	 * Récupère les ids pour une liste pour effectuer des opérations sur la liste (comment envoyer un mail)
	 *
	 */
	protected function recupere_ids($type_element, $parametres, $colonnes = array()) {

        $champ_id_a_recuperer = !empty($parametres['champ_id_a_recuperer']) ? $parametres['champ_id_a_recuperer'] : 'id';

        $requete_initiale = $this->cree_requete($type_element, $parametres, $colonnes)->select($type_element.'.'.$champ_id_a_recuperer)->get();

        $ids = $requete_initiale->pluck($champ_id_a_recuperer);

		return array(
		    'retour' => true,
			'recuperer_les_ids_uniquement' => true,
			'ids' => $ids,
		);
	}

	/**
	 *
	 * Récupère la liste avec les paramètres
	 *
	 */
	public function recupere_liste($id_liste, $parametres = array(), $nombre_par_page = false, $type_export = 'basique', $ajax = false) {

		$this->id_liste = $id_liste;

		if(isset($parametres['kanban']) && $parametres['kanban'] === 'false')
			$parametres['kanban'] = '';

		if(empty($nombre_par_page) && $nombre_par_page !== 'export')
			$nombre_par_page = fonctionnalite('nombre_de_lignes_dans_listes');

		$liste_libre = Liste_libre::find($id_liste);

		$this->liste_libre = $liste_libre;

		$this->infos_affichage_kanban['liste_libre'] = $liste_libre;

        $type_element = $liste_libre->type_element;

        $table_libre = table_libre($type_element);
        $table_libre->modele_vue_sql = $table_libre->vue_sql == 1 ? modele('vue_sql')->where('nom_sql', $type_element)->first() : null;
        
		if(!empty($liste_libre->id_rapport)) {
            $rapport = Rapport_libre::where('id_rapport', $liste_libre->id_rapport)->first();

            $this->rapport = $rapport;

            if(!empty($rapport) && !empty($rapport->kanban)) {

                $this->infos_affichage_kanban['rapport'] = $rapport;
                $parametres['kanban'] = $rapport->kanban;

                if(!empty($rapport->kanban_entete_calcul_unite))
                    $parametres['kanban_unite'] = $rapport->kanban_entete_calcul_unite;

                if(!empty($rapport->kanban_entete_calcul_champ) && $rapport->kanban_entete_calcul_somme == 1)
                    $parametres['kanban_colonne_somme'] = $rapport->kanban_entete_calcul_champ;   
            }

		}

        if(!empty($parametres['kanban'])) {
            $management_champ = management($type_element)->champ($parametres['kanban']);

            if ($management_champ->modele->type === 42)
                $kanban_colonnes_valeurs = $management_champ->recuperation_options_select();
            else
                $kanban_colonnes_valeurs = $management_champ->valeurs_possibles;

            if(!empty($rapport->kanban_colonnes) && empty($parametres['kanban_colonnes']))
                $parametres['kanban_colonnes'] = json_decode($rapport->kanban_colonnes);

            // quand on charge la suite des éléments d'un statut précis ( scroll infini ), on doit toujours se limiter
            // à ce statut, même si le rapport est configuré pour afficher toutes les colonnes
            if (!empty($parametres['offset_depart_kanban_elements']) && !empty($parametres['kanban_colonnes'])) {

                $kanban_colonnes = is_array($parametres['kanban_colonnes']) ? $parametres['kanban_colonnes'] : json_decode($parametres['kanban_colonnes']);

            } else if (!empty($parametres['kanban_colonnes']) && empty($rapport->toutes_les_colonnes)) {

                if (is_array($parametres['kanban_colonnes']))
                    $kanban_colonnes = $parametres['kanban_colonnes'];
                else
                    $kanban_colonnes = json_decode($parametres['kanban_colonnes']);

                if(!empty($rapport->exclusion_colonnes)) {
                    $colonnes_inclues = [];

                    foreach ($kanban_colonnes_valeurs as $valeur => $colonne) {
                        if(!in_array($valeur, $kanban_colonnes))
                            $colonnes_inclues[] = $valeur;
                    }

                    $kanban_colonnes = $colonnes_inclues;
                }

            } else
                $kanban_colonnes = array_keys($kanban_colonnes_valeurs);

            // si un champ de tri est configuré sur l'entité liée, il détermine l'ordre des colonnes/groupes du kanban
            if($management_champ->modele->type === 42 && !empty($rapport->kanban_tri_champ)) {

                $sens = $rapport->kanban_tri_sens == 'DESC' ? 'DESC' : 'ASC';

                $kanban_colonnes = modele($management_champ->modele->type_element_ajax)
                    ->whereIn('id', $kanban_colonnes)
                    ->orderBy($rapport->kanban_tri_champ, $sens)
                    ->pluck('id')
                    ->toArray();
            }

            $parametres['colonnes_kanban_total'] = count($kanban_colonnes);
            $parametres['kanban_colonnes'] = $kanban_colonnes;

            $parametres['kanban_unite'] = '';
            $parametres['kanban_colonne_count'] = false;
            $parametres['kanban_colonne_somme'] = false;

            if(!empty($rapport)){
                if(!empty($rapport->kanban_entete_calcul_unite))
                    $parametres['kanban_unite'] = $rapport->kanban_entete_calcul_unite;

                if(!empty($rapport->kanban_entete_calcul_champ) && $rapport->kanban_entete_calcul_somme == 1)
                    $parametres['kanban_colonne_somme'] = $rapport->kanban_entete_calcul_champ;

                if($rapport->kanban_entete_calcul_nombre == 1)
                    $parametres['kanban_colonne_count'] = true;
            }

            $nombre_par_page = 50;
        }
        
		// on récupère l'info de la recherche avancée
		if(!empty($liste_libre->recherche_avancee))
			$parametres['recherche_avancee'] = json_decode($liste_libre->recherche_avancee);

		$formulaire = Formulaire::where('nom_formulaire', $type_element)->first();

        $parametres['recuperer_les_ids_uniquement'] = false;

		if($formulaire != null)
			$id_formulaire = $type_element;
		else
			$id_formulaire = null;

		// les colonnes de la liste
		if(!empty($parametres['kanban']) && empty($liste_libre->affichage_kanban_vertical) && !defined('export_en_cours'))
			$colonnes = collect(array());
		else {
			$colonnes = $this->obtenir_colonnes($id_liste, $table_libre, $type_export);

			if(count($colonnes) == 0 && empty($parametres['kanban']))
				throw new \App\Eden\Exceptions\Eden_exception("Aucune colonne trouvée pour cette liste libre ($id_liste)");
		}

		// on initialise les paramètres de la liste
		$parametres = $this->initialise_parametres_liste($parametres, $liste_libre, $colonnes);

		// pour le chargement progressif des éléments d'un statut kanban précis, le décalage doit correspondre au nombre d'éléments
		// déjà chargés pour CE statut ( et non à un multiple de la taille de page, puisque le premier chargement vient d'une requête globale non filtrée )
		if(!empty($parametres['offset_depart_kanban_elements']))
			$nombre_debut = intval($parametres['offset_depart_kanban_elements']);
		else
			$nombre_debut = $nombre_par_page != 'export' ? ($parametres['page'] - 1) * $nombre_par_page : 0;

		if(isset($parametres['recupere_ids']) && $parametres['recupere_ids'] === true)
			return $this->recupere_ids($type_element, $parametres, $colonnes);

        if(!empty($this->rapport) && $this->rapport->type == 'requete_sql')
            $this->liste_requete = service('liste_requete_sql')->transformation_requete($this->rapport,$this->liste_libre,$type_element,$parametres, $colonnes);

		if($ajax !== true) {

            if(!empty($this->liste_requete)){
                $elements = modele($type_element)->hydrate(DB::select($this->liste_requete));

                $nombre_elements = $elements->count();
                $nombre_elements_sans_filtres = $nombre_elements;
            }
            else {
                $requete_count = $this->cree_requete($type_element, $parametres, $colonnes);

                $parametres_sans_filtres = $parametres;
                $parametres_sans_filtres['ne_pas_utiliser_ancienne_requete'] = true;
                unset($parametres_sans_filtres['recherche']);
                $requete_count_sans_filtres = $this->cree_requete($type_element, $parametres_sans_filtres, $colonnes);

                $requete_count = clone $requete_count;
                $requete_count_sans_filtres = clone $requete_count_sans_filtres;
                $this->requete_count_sans_filtres = $requete_count_sans_filtres;

                if (!empty($requete_count->getQuery()->groups)) {

                    $group_a_supprimer = array_search($type_element . '.id', $requete_count->getQuery()->groups);

                    if ($group_a_supprimer !== false)
                        unset($requete_count->getQuery()->groups[$group_a_supprimer]);

                    if (empty($requete_count->getQuery()->groups))
                        $requete_count->getQuery()->groups = null;
                }

                if (!empty($requete_count_sans_filtres->getQuery()->groups)) {

                    $group_a_supprimer = array_search($type_element.'.id',$requete_count_sans_filtres->getQuery()->groups);

                        if ($group_a_supprimer !== false)
                            unset($requete_count_sans_filtres->getQuery()->groups[$group_a_supprimer]);

                    if(empty($requete_count_sans_filtres->getQuery()->groups))
                        $requete_count_sans_filtres->getQuery()->groups = null;
                }

                if(!empty($parametres['kanban'])) {

                    $select_kanban = [DB::raw("COUNT(DISTINCT {$type_element}.id) as nombres_elements"),$type_element.'.'.$parametres['kanban']];

                    $requete_count_colonne_kanban = clone $requete_count;

                    $infos_kanban = $requete_count_colonne_kanban->groupBy($type_element.'.'.$parametres['kanban']);

                    $kanban_colonne_somme = $type_element.'.'.$parametres['kanban_colonne_somme'];

                    if(!empty($parametres['kanban_colonne_somme'])) {
                        $select_kanban[] = DB::raw("
                            CASE
                                WHEN COALESCE({$kanban_colonne_somme}, 0) REGEXP '^[0-9]+(\.[0-9]+)?$' THEN SUM(CAST({$kanban_colonne_somme} AS DECIMAL))
                                ELSE COUNT({$kanban_colonne_somme})
                            END AS somme_elements");
                    }

                    $infos_kanban = $infos_kanban->select($select_kanban)->get()->keyBy($parametres['kanban'])->toArray();
                }

                $nombre_elements = $requete_count->select(\DB::raw('count(DISTINCT '.$type_element.'.id) AS nombre_element'));

                $nombre_elements_sans_filtres = $requete_count_sans_filtres->select(\DB::raw('count(DISTINCT '.$type_element.'.id) AS nombre_element'));

                $nombre_elements = select($nombre_elements)[0];
                $nombre_elements_sans_filtres = select($nombre_elements_sans_filtres)[0];

                if (!empty($nombre_elements))
                    $nombre_elements = $nombre_elements->nombre_element;
                else
                    $nombre_elements = 0;

                if (!empty($nombre_elements_sans_filtres))
                    $nombre_elements_sans_filtres = $nombre_elements_sans_filtres->nombre_element;
                else
                    $nombre_elements_sans_filtres = 0;

            }
		}
		else {

			$nombre_elements = 0;
            $nombre_elements_sans_filtres = 0;
		}

		if(isset($this->retourne_seulement_le_nombre_de_lignes) && $this->retourne_seulement_le_nombre_de_lignes === true)
			return $nombre_elements;

		// on fait les calculs

		if($ajax !== true)
			$calculs = $this->recupere_informations_calculs($id_liste, $type_element, $parametres);
		else
			$calculs = array();

        if($nombre_par_page != 'export') {
            $parametres['nombre_pages'] = ceil($nombre_elements / $nombre_par_page);

            $parametres['nombre_par_page'] = $nombre_par_page;

            // c'est le cas ou après un filtre on n'a moins de pages que la page actuelle (probablement)
            if ($parametres['page'] > $parametres['nombre_pages']) {

                $parametres['page'] = 1;
                $nombre_debut = 0;
            }
        }

        if($ajax !== true && empty($this->liste_requete))
            $elements = $this->cree_requete($type_element, $parametres, $colonnes)->groupBy($type_element.'.id');
        else
            $elements = modele($type_element)->where('id', null);

        if(!empty($parametres['uniquement_requete']))
            return $elements;

        if(empty($parametres['limit'])) {
            if(!empty($this->liste_requete)){

                $liste_requete = $this->liste_requete;

                if ($nombre_par_page != 'export')
                    $liste_requete .= ' limit '.$nombre_par_page.' offset '.$nombre_debut;

                $elements = modele($type_element)->hydrate(DB::select($liste_requete));
            }
            else{
                if($nombre_par_page != 'export') {

                    if(!empty($parametres['kanban'])) {
                        $requete_initiale = clone $elements;
                        $elements = false;
                        if(is_array($kanban_colonnes)) {
                            foreach ($kanban_colonnes as $colonne) {
                                $requete_colonne = clone $requete_initiale;
                                $requete_colonne = $requete_colonne
                                    ->where($type_element.'.'.$parametres['kanban'], $colonne)
                                    ->take($nombre_par_page)
                                    ->skip($nombre_debut);

                                if($elements === false)
                                    $elements = $requete_colonne;
                                else
                                    $elements = $elements->union($requete_colonne);
                            }
                        }
                    } else {
                        $elements = $elements->take($nombre_par_page)->skip($nombre_debut);
                    }

                }

                $elements = modele($type_element)->hydrate(select($elements));
            }
        }
		else {

            if(!empty($this->liste_requete))
                $elements = modele($type_element)->hydrate(DB::select($this->liste_requete));
            else
                $elements = $elements->get();

			// pas de pagination
			$parametres['page'] = 1;
			$parametres['nombre_pages'] = 1;
		}

		$lignes = array();

        $type_element_options = service('vue_sql')->recupere_type_element($type_element);

        if(empty($type_element_options))
            $type_element_options = $type_element;

		if($ajax !== true) {

            $champs_libres_autres_tables = collect();

            $champs_libres_multi_selection = collect();
 
            foreach($colonnes as $colonne) {
                $colonne->management->traitement_post_requete($elements);

                if((!empty($colonne->champ_libre) || !empty($colonne->champs_libres)) && $colonne->type != 'calcul'){
                    $champs_libres = $colonne->champs_libres ?? [$colonne->champ_libre];
                    $modeles_champs_libres = collect($champs_libres)->pluck('modele');
                    $champs_libres_multi_selection = $champs_libres_multi_selection->merge($modeles_champs_libres
                        ->where('type', 10));

                    $champs_libres_autres_tables = $champs_libres_autres_tables->merge($modeles_champs_libres
                        ->filter(function($champ_libre){
                            return $champ_libre->type == 42 || $champ_libre->type == 22 || ($champ_libre->type == 10 && $champ_libre->type_reference == 42);
                        }));
                }
            }

            $champs_libres_multi_selection = $champs_libres_multi_selection->merge(
                champs_libres($type_element)->where('type',10)->whereNotIn('nom_sql',$champs_libres_multi_selection->when()));

            foreach($champs_libres_multi_selection as $champ_libre) {

                $nom_colonne = $nom_colonne_requete = 'id';
                $alias_table = $champ_libre->alias_table ?? $champ_libre->type_element;

                if($champ_libre->type_element != $table_libre->type_element)
                    $nom_colonne_requete = $alias_table.'_id';
                if($table_libre->modele_vue_sql && $champ_libre->type_element_origine)
                    $nom_colonne = $nom_colonne_requete = service('vue_sql')->recuperer_nom_colonne_id($table_libre->modele_vue_sql,$champ_libre->type_element_origine);

                $elements_table_pivot = $elements->pluck($nom_colonne_requete)->toArray();

                $champ_libre->elements_table_pivot = $elements_table_pivot;

                $modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);
                
                $valeurs_champs_multi_selection_bdd = $modele_table_pivot
                    ->whereIn('cle_locale', $elements_table_pivot)
                    ->get()
                    ->groupBy('cle_locale')
                    ->map(function($valeurs){
                        return $valeurs->pluck('valeur');
                    })->toArray();

                foreach($elements as $element) {
                    $element->{$champ_libre->alias_champ ?? $champ_libre->nom_sql} = $valeurs_champs_multi_selection_bdd[$element->{$nom_colonne}] ?? [];
                }
            }

            $elements_traitement = clone $elements;
            $elements_traitement->map(function($element) { return clone $element; });  

            $champs_libres_autres_tables_par_type_element = $champs_libres_autres_tables->groupBy(function($champ_libre){
                if($champ_libre->type == 22){
                    
                    if(!empty($champ_libre->type_element_origine))
                        $champ_libre->contenu = champ_libre_modele($champ_libre->type_element_origine,$champ_libre->nom_sql_origine)->contenu;

                    return collect(json_decode(champ_libre_modele($champ_libre->type_element, $champ_libre->contenu)->contenu))->where('valeur','true')->pluck('type_element')->toArray();
                }
                else
                    return $champ_libre->type_element_ajax;
            }); 

            foreach($champs_libres_autres_tables_par_type_element as $type_element_ajax => $champs_libres){

                $resultat_cree_requete = $this->resultat_cree_requete ?? null;

                $modeles = modele($type_element_ajax)->where(function($query) use ($champs_libres,$nombre_par_page,$elements, $resultat_cree_requete, $type_element_ajax){
                    foreach($champs_libres as $champ_libre){

                        $colonne_valeur = $champ_libre->alias_champ ?? $champ_libre->nom_sql;

                        if($nombre_par_page == 'export' && !empty($resultat_cree_requete)){
                            if($champ_libre->type == 10)
                                $query->orWhereIn('id', array_unique(array_merge([], ...$elements->pluck($colonne_valeur)->toArray())));
                            else if($champ_libre->type == 22)
                                $query->orWhereIn('id', $resultat_cree_requete->where($champ_libre->alias_champ_type_element ?? $champ_libre->contenu, $type_element_ajax)->select($colonne_valeur));
                            else
                                $query->orWhereIn('id', $resultat_cree_requete->select($colonne_valeur));
                        }
                        else{
                            if($champ_libre->type == 10)
                                $query->orWhereIn('id', array_unique(array_merge([], ...$elements->pluck($colonne_valeur)->toArray())));
                            else if($champ_libre->type == 22)
                                $query->orWhereIn('id', array_unique($elements->where($champ_libre->alias_champ_type_element ?? $champ_libre->contenu, $type_element_ajax)->pluck($colonne_valeur)->toArray()));
                            else
                                $query->orWhereIn('id', array_unique($elements->pluck($colonne_valeur)->toArray()));
                        }
                    }
                })->get()->keyBy('id');

                foreach($champs_libres as $champ_libre){

                    $colonne_valeur = $champ_libre->alias_champ ?? $champ_libre->nom_sql;

                    if($champ_libre->type == 10){

                        foreach ($elements_traitement as $element) {
                            $ids = $element->{$colonne_valeur} ?? [];

                            if (empty($ids))
                                continue;

                            $element->{"modeles_".$colonne_valeur} = collect($ids)
                                ->map(fn ($id) => $modeles[$id] ?? null)
                                ->filter()->keyBy('id');
                        }
                        
                    }
                    else if($champ_libre->type == 22){

                        $champ_type_element = $champ_libre->alias_champ_type_element ?? $champ_libre->contenu;
                    
                        foreach($elements_traitement as $element) {
                            if($element->{$champ_type_element} == $type_element_ajax)
                                $element->{"modele_".$colonne_valeur} = $modeles[$element->{$colonne_valeur}] ?? null;
                        }
                    }
                    else{
                        foreach($elements_traitement as $element) {
                            $element->{"modele_".$colonne_valeur} = $modeles[$element->{$colonne_valeur}] ?? null;
                        }
                    }
                }
            }
		} 

        $this->elements = $elements;

        $management_kanban = management($type_element);

		// on boucle sur tous les éléments
		foreach($elements as $index_element => $element) {

			$ligne = array();

			$ligne['id'] = $element->id;

            // on est en affichage kanban ?
			if(isset($parametres['kanban']) && !empty($parametres['kanban']))
				$element->affichage_kanban = $this->affichage_kanban($management_kanban, $type_element, $element);

            $ligne['droits_modification'] = $ligne['droits_modification'] ?? profil_modification($type_element, $element->entite_id ?? null, $element);
            $ligne['droits_suppression'] = $ligne['droits_suppression'] ?? profil_suppression($type_element, $element->entite_id ?? null, $element);
            $ligne['droits_comptabilisation'] = $ligne['droits_comptabilisation'] ?? profil_comptabilisation($type_element, $element->entite_id ?? null, $element);

			foreach($colonnes as $colonne) {
                $ligne[$colonne->id] = $colonne->management->traitement_colonne($elements_traitement[$index_element] ??$element);
			}

			$ligne['element'] = $element;

			$lignes[] = $ligne;
		} 

		$filtres = Liste_libre_filtre::where('liste_libre_id', $id_liste)->orderBy('ordre')->get();

		foreach($filtres as $index => $filtre) {

			if(empty($filtre->type_element))
				$champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $filtre->nom_sql)->first();
			else
				$champ_libre = Champ_libre::where('type_element', $filtre->type_element)->where('nom_sql', $filtre->nom_sql)->first();

			if(empty($champ_libre) || $champ_libre->inactif == 1)
                $filtres->forget($index);
            else {

                // on va chercher la valeur du filtre
                if (isset($parametres['filtres']) && is_array($parametres['filtres']) && isset($parametres['filtres'][$filtre->nom_sql]))
                    $filtre->valeur = $parametres['filtres'][$filtre->nom_sql];
                else
                    $filtre->valeur = false;
            }
		}

		$couleurs = Liste_libre_couleur::where('liste_libre_id',$id_liste)->get();

        $couleurs_ids = array();

        $filtres_couleurs = modele('recherche_avancee')
            ->where('type','listes_libres_couleur')
            ->whereIn('id_cible',$couleurs->pluck('id')->toArray())
            ->get()->keyBy('id_cible');

        foreach ($couleurs as $couleur) {

            $requete = modele($type_element)->whereIn($type_element.'.id',$elements->pluck('id')->toArray());

            if(!empty($filtres_couleurs[$couleur->id])){
                $structure = management('recherche_avancee',$filtres_couleurs[$couleur->id]->id,$filtres_couleurs[$couleur->id])->structure();
                management('recherche_avancee')->applique_filtrage($structure,$requete,$type_element);
            }

            $elements_couleurs = $requete->select($type_element.'.id')->get()->pluck('id');

            foreach($elements_couleurs as $id){

                if(!isset($couleurs_ids[$id]))
                    $couleurs_ids[$id] = $couleur['couleur'];
            }
        }

        if(!empty($couleurs_ids)){

            foreach($lignes as $ligne){

                $ligne['element']['couleur_background'] = '';

                if(isset($couleurs_ids[$ligne['element']['id']]))
                    $ligne['element']['couleur_background'] = 'background-color: '.$couleurs_ids[$ligne['element']['id']].';';
            }
        }

		// Si la constante existe, on l'initialise, si elle n'existe pas c'est qu'on réalise une tache cron donc on se refère à l'id utilisateur passée en paramètre
		$id_utilisateur = null;

		if(defined('id_utilisateur'))
			$id_utilisateur = id_utilisateur;

		// on traite un cas particulier pour les kanbans
		$est_liste_kanban = !empty($parametres['kanban']) && $parametres['kanban'] !== 'false';

		$kanban_elements_par_colonnes = array();
		$kanban_somme_par_colonnes = array();
        $kanban_nombre_par_colonnes = array();
        $kanban_colonnes_informations = array();
        $kanban_colonne_count = false;
        $kanban_nombre_elements_affiches = null;

		if(isset($parametres['kanban_colonne_count']) && ($parametres['kanban_colonne_count'] === true || $parametres['kanban_colonne_count'] == 'true'))
			$kanban_colonne_count = true;

		if($est_liste_kanban) {

            foreach($kanban_colonnes as $cle => $id_valeur){

                if(!isset($kanban_colonnes_valeurs[$id_valeur]))
                    continue;

                if($management_champ->modele->type == 20) {

                    $info_colonne = Champs_liste_formatee::where('id_valeur', $id_valeur)->where('id_liste_choix', $management_champ->modele->liste_choix)->first();
                }
                else {

                    $info_colonne = Champ_libre_liste::find($id_valeur);
                }

                $kanban_colonnes_informations[$cle] = array(
                    'id_valeur' => $id_valeur,
                    'nom' => $kanban_colonnes_valeurs[$id_valeur],
                    'info_colonne' => $info_colonne,
                );

                $kanban_elements_par_colonnes['colonne_' . $id_valeur] = array();
                $kanban_somme_par_colonnes['colonne_' . $id_valeur] = $infos_kanban[$id_valeur]['somme_elements'] ?? 0;
                $kanban_nombre_par_colonnes['colonne_' . $id_valeur] = $infos_kanban[$id_valeur]['nombres_elements'] ?? 0;
            }

			foreach($lignes as $ligne) {

                if($ligne['element']->{$parametres['kanban']} == null)
                    $ligne['element']->{$parametres['kanban']} =0;
                
				$kanban_elements_par_colonnes['colonne_'.$ligne['element']->{$parametres['kanban']}][] = $ligne;
            }

            if (!empty($liste_libre->desactiver_kanban_sans_valeur) ) {

                foreach ($kanban_elements_par_colonnes as $nom => $lignes_kanban){

                    $id_valeur_colonne = str_replace('colonne_','',$nom);

                    if(empty($lignes_kanban)){

                        unset($kanban_colonnes_informations[array_search($id_valeur_colonne,$kanban_colonnes)]);
                        $parametres['colonnes_kanban_total']--;
                    }
                }
            }
            
            if(count($kanban_colonnes_informations) > 15) {
                
                $offset_colonnes = 0;

                if(!empty($parametres['offset_depart_kanban_colonnes']))
                    $offset_colonnes = intval($parametres['offset_depart_kanban_colonnes']);

                $nombre_colonnes = 15;

                if(!empty($parametres['nombre_colonnes_garder'])) {
                    $nombre_colonnes = $parametres['nombre_colonnes_garder'];
                }

                $colonnes_supprimees = $kanban_colonnes_informations;
                $kanban_colonnes_informations = array_splice($colonnes_supprimees, $offset_colonnes, $nombre_colonnes);
                
                foreach ($colonnes_supprimees as $colonne) {
                    unset($kanban_elements_par_colonnes['colonne_'.$colonne['id_valeur']]);

                    foreach($lignes as $index => $ligne) {

                        if($ligne['element']->{$parametres['kanban']} == $colonne['id_valeur'])
                            unset($lignes[$index]);

                    }
                }
                
            }
		}

        if($est_liste_kanban)
            $kanban_nombre_elements_affiches = count($lignes);

		foreach($kanban_somme_par_colonnes as $id => $somme) {

			$kanban_somme_par_colonnes[$id] = montant($somme, 0);
		}

		$parametres['kanban_colonne_count'] = $kanban_colonne_count;

        $parametres['nombre_pages'] = empty($kanban_colonnes_informations) && isset($parametres['nombre_pages']) ? $parametres['nombre_pages'] : 0;

        foreach($colonnes as $colonne) {

            if(isset($colonne->champs_libres))
                unset($colonne->champs_libres);

            if(isset($colonne->champ_libre))
                unset($colonne->champ_libre);

            if(isset($colonne->management))
                unset($colonne->management);

            if(isset($colonne->management_methode))
                unset($colonne->management_methode);
        }

		$informations_pour_la_vue = [

            'colonnes'                      => $colonnes,
            'id_formulaire'                 => $id_formulaire,
            'type_element'                  => $type_element,
            'nombre_de_pages'               => $parametres['nombre_pages'],
            'type_element_options'          => $type_element_options ?? null,
            'options_liste'                 => collect($parametres),
            'liste_id'                      => $id_liste,

            'lignes'                        => collect($lignes),
            'retour'                        => true,
            'filtres'                       => $filtres,
            'nombre_elements'               => $nombre_elements . ' ' . ($nombre_elements > 1 ? $table_libre->element_pluriel : $table_libre->element),
            'element_pluriel'               => $table_libre->element_pluriel,
            'calculs'                       => collect($calculs),
            'ids'                           => [],
            'filtres_enregistres'           => Liste_libre_filtre_enregistre::where('liste_id', $id_liste)->where('utilisateur_id', $id_utilisateur)->get(),
            'fiche'                      	=> $table_libre->fiche,
            'type_export'					=> $type_export,
			'liste_libre'					=> $liste_libre,
			'modele_liste_libre'			=> $liste_libre,
			'nombre_elements_nombres'		=> $nombre_elements,
			'nombre_elements_nombres_sans_filtres'	=> $nombre_elements_sans_filtres,
			'autres_vues'					=> $this->autres_vues($id_liste),
			'droits_liste'					=> ['profil_creation' => profil_creation($type_element)],
		];

		if($est_liste_kanban) {

            $informations_pour_la_vue['kanban_colonnes'] = $kanban_colonnes_informations;
            $informations_pour_la_vue['kanban_elements_par_colonnes'] = $kanban_elements_par_colonnes;
            $informations_pour_la_vue['kanban_nombre_par_colonnes'] = $kanban_nombre_par_colonnes;
            $informations_pour_la_vue['kanban_somme_par_colonnes'] = $kanban_somme_par_colonnes;
            $informations_pour_la_vue['kanban_nombre_elements_affiches'] = $kanban_nombre_elements_affiches;
        }

		temps_execution('fin liste '.$id_liste.' ('.$type_element.')', 4);

		return $informations_pour_la_vue;
	}

    /**
     * 
     * Contruit un tableau d'attributs à partir du retour de la fonction affiche_lien()
     * 
    */
    function attributs_depuis_affiche_lien($management_lien, $element_id = false) {

        $affiche_lien = $management_lien->affiche_lien('',$element_id);

        $attributs = [];

        preg_match_all('/(\w[\w-]*)=["\']([^"\']*)["\']/', $affiche_lien, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributs[$match[1]] = $match[2];
        }

        return $attributs;
    }

	/**
	 *
	 * Retourne le html pour la vignette de l'utilisateur (si nécessaire)
	 *
	 */
	protected function affichage_kanban_vignette_utilisateur($type_element, $element, $parametre) {

		if(empty($this->infos_affichage_kanban['rapport']->$parametre))
			return;

		$champ = champ_libre_modele($type_element, $this->infos_affichage_kanban['rapport']->$parametre);

		if(empty($this->infos_affichage_kanban['utilisateurs'])) {

			$this->infos_affichage_kanban['utilisateurs'] = modele('utilisateur')->avec_inactifs()->get()->keyBy('id');
		}


		$utilisateurs = array();

		// champ liste formatée
		if(in_array($champ->type, array(20, 42))) {

			if(empty($element->{$this->infos_affichage_kanban['rapport']->$parametre}))
				return;

				$utilisateurs[] = $element->{$this->infos_affichage_kanban['rapport']->$parametre};
		}
		// champ multi select
		else {

            $modele_table_pivot = table_libre_existe($champ->table_pivot) ? modele($champ->table_pivot) : \DB::table($champ->table_pivot);

			$utilisateurs = $modele_table_pivot->where('cle_locale',$element->id)->get()->pluck('valeur')->toArray();
		}

		$affichage = '<div class="css_avatar_utilisateur css_avatar_utilisateur_kanban">';

		foreach($utilisateurs as $id_utilisateur) {

			if(empty($this->infos_affichage_kanban['utilisateurs'][$id_utilisateur]))
				continue;

			$utilisateur = $this->infos_affichage_kanban['utilisateurs'][$id_utilisateur];

			if(empty($utilisateur->avatar))
				$affichage .= '<img src="'.asset('eden/images/no_avatar.jpg').'" title="'.$utilisateur->chaine_affichage.'">';
			else
				$affichage .= '<img src="storage/'.$utilisateur->avatar.'" title="'.$utilisateur->chaine_affichage.'">';
		}

        $affichage .= '</div>';

		return $affichage;
	}

	/**
	 *
	 * Retourne l'affichage utilisé pour l'élément en mode Kanban
	 *
	 */
	protected function affichage_kanban($management, $type_element, $element) {
        
		$management->reload_modele($element->id, $element);

		if(table_libre($type_element)->fiche == 1 || in_array($type_element, Variables::$documents_gescom)) {

			$affichage = $management->affiche_lien_dans_kanban();
		}
		else {

            $affichage = "<span class='css__lien js_liste_apercu_element' id_element=" . $element->id. ">" . $management->affichage_dans_kanban() . "</span>";
		}


		if(!empty($this->infos_affichage_kanban['rapport']->kanban_afficher_utilisateur_1) || !empty($this->infos_affichage_kanban['rapport']->kanban_afficher_utilisateur_2)) {

			$affichage .= '<br/><div class="css_item_liste_kanban_contenu">';

			if(!empty($this->infos_affichage_kanban['rapport']->kanban_afficher_utilisateur_1)) {

				$affichage .= $this->affichage_kanban_vignette_utilisateur($type_element, $element, 'kanban_afficher_utilisateur_1');
				$affichage .= $this->affichage_kanban_vignette_utilisateur($type_element, $element, 'kanban_afficher_utilisateur_2');
			}


			$affichage .= '</div>';
		}



		return $affichage;
	}

	/**
	 *
	 * Retourne les autres vues liées à cette vue
	 *
	 */
	public function autres_vues($id_liste) {

		$autres_vues = Liste_libre_autresvues::where('liste_libre_id_1', $id_liste)->get();


		// on vérifie si l'utilisateur connecté peut accéder aux rapports
		foreach($autres_vues as $cle => $vue) {

			if($vue->liste_libre_id_1 == $id_liste)
				$liste_libre = Liste_libre::find($vue->liste_libre_id_2);
			else
				$liste_libre = Liste_libre::find($vue->liste_libre_id_2);

			$liste_libre->titre = table_libre($liste_libre->type_element)->nom_table;

			// on ajoute un titre à la vue
			if(!empty($liste_libre->id_rapport)) {

				$rapport = Rapport_libre::where('id_rapport', $liste_libre->id_rapport)->first();

				if(!empty($rapport)) {

					$liste_libre->titre = $rapport->titre;
				}
			}

			$vue->liste_libre = $liste_libre;

			if(empty($liste_libre->id_rapport))
				continue;
		}

		return $autres_vues;
	}

	/**
	 *
	 * Crée la requete pour la liste avec les filtres, order by, tout sauf la pagination
	 *
	 */
	public function cree_requete($type_element, &$parametres, $colonnes = array(), $joins = array()) {

		if(!empty($this->resultat_cree_requete) && empty($parametres['calcul']) && empty($parametres['ne_pas_utiliser_ancienne_requete']))
			return $this->resultat_cree_requete;

		$requete = modele($type_element)->initie_requete_pour_liste();

        if(empty(moi()) && !empty(moi_extranet()))
            $requete = $requete->avec_filtre_extranet();

		// on regarde s'il y a des joins à faire
		$selects = array(DB::raw($type_element . '.*'));

        $joins = [
            'jointures' => $joins,
            'alias_requete_compte' => 0,
        ];

        $filtres_colonnes_calculs = modele('recherche_avancee')
            ->whereIn('type', array_map(function($colonne_calcul) { 
                return 'liste_libre_colonne_calcul_'.$colonne_calcul['id']; }, 
                is_array($colonnes) ? $colonnes : $colonnes->toArray()))
            ->get()->groupBy('type');

        foreach($colonnes as $colonne) {
            $colonne->management->requete_colonne($requete, $selects, $joins, $filtres_colonnes_calculs);
        }

		// on gère l'ordre des kanbans
		if(!empty($parametres['kanban']) && empty($parametres['calcul'])) {

            $liste_libre = $this->liste_libre;

			$requete = $requete->leftJoin('ordre_dans_kanban',function($sous_requete) use($type_element,$liste_libre){
                    $sous_requete->on('ordre_dans_kanban.element_id', $type_element.'.id');
                    $sous_requete->where('id_rapport', $liste_libre->id_rapport);
                })->groupBy($type_element . '.id');
		}

		// on gère le cas des tableaux kanban
		if(isset($parametres['kanban_colonnes']) && !empty($parametres['kanban_colonnes']) && !defined('export_en_cours')) {

            if(is_array($parametres['kanban_colonnes']))
                $kanban_colonnes = $parametres['kanban_colonnes'];
            else
                $kanban_colonnes = json_decode($parametres['kanban_colonnes']);

            if(is_array($kanban_colonnes) && !empty($kanban_colonnes))
			    $requete = $requete->whereIn($type_element.'.'.$parametres['kanban'], $kanban_colonnes);
		}

		if(isset($parametres['avec_inactifs']) && $parametres['avec_inactifs'] == 1)
			$requete = $requete->avec_inactifs();

		if (isset($parametres['seulement_inactif']) && !empty($parametres['seulement_inactif']) && $parametres['seulement_inactif'] != 'false') {

			$requete = $requete->avec_inactifs();

			$requete = $requete->where($type_element.'.inactif', '1');
		}

        $management_element = management($type_element);

		// cas spécifique des fiches
		if(!empty($parametres['filtres_pour_fiche'])) {

			if(!is_array($parametres['filtres_pour_fiche']))
				$parametres['filtres_pour_fiche'] = unserialize(base64_decode($parametres['filtres_pour_fiche']));

            if(!empty($parametres['filtres_pour_fiche'])) {

                foreach ($parametres['filtres_pour_fiche'] as $champ => $valeur) {

                    if ($champ == "article_id" && in_array($type_element, Variables::$documents_gescom)) {

                        $table_pivot = $type_element . '_lignes';

                        $requete = $requete->join($table_pivot, $type_element . '.id', $table_pivot . '.document_id')
                            ->where($table_pivot . '.' . $champ, $valeur);

                        $requete = $requete->groupBy($type_element . '.id');
                        continue;

                    }

                    if($champ == "id") {
                        $requete = $requete->where($type_element . '.id', $valeur);
                        continue;
                    }

                    try{
                        $champ = $management_element->champ($champ);
                    } catch(\App\Eden\Exceptions\Eden_exception $e) {
                        continue;
                    }

                    if(is_array($valeur) && isset($valeur['debut'])) {
                        if(!empty($valeur['variable']))
                            list($debut, $fin) = $champ->transforme_variable_date($valeur['variable']);
                        if(!empty($debut))
                            $valeur['debut'] = date('Y-m-d', strtotime($debut));
                        if(!empty($fin))
                            $valeur['fin'] = date('Y-m-d', strtotime($fin));

                        $valeur['variable'] = '';
                    }

                    if($champ->modele->type == 42 && !is_array($valeur))
                        $valeur = [$valeur];

                    $requete = $champ->applique_filtre_sur_requete($valeur, $requete);
                }
            }
		}

		if(!empty($parametres['recherche'])) {

            if(table_libre($type_element)->vue_sql == 1) {

                $termes = explode(' ',$parametres['recherche']);

                foreach($termes as $terme) {

                    if(empty($terme))
                        continue;

                    $terme = str_replace("'","\'",$terme);

                    $requete = $requete->where($type_element . ".chaine_tags_recherche","LIKE","%".$terme."%");
                }
            }
            else {
                
                $termes = array_diff(explode(' ', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $parametres['recherche'])),array(""));
                $requete = $requete->whereRaw("MATCH(" . $type_element . ".chaine_tags_recherche) AGAINST (\"+" . implode('* +', $termes) . "*\" In BOOLEAN MODE)");
            }

        }

		if(isset($parametres['filtres'])) {

            $filtres = collect($parametres['filtres_affichage']);

            foreach ($parametres['filtres'] as $filtre_valeur) {

				// on va chercher les infos du filtre
                $filtre = (object) $filtres->where('id', $filtre_valeur['id'])->first();

				$type_element_tmp = $type_element;

                if (!empty($filtre->type_element) && $filtre->type_element != $type_element) {

                    $type_element_tmp = $filtre->type_element;

                    $champ_de_liaison = $filtre->champ_de_liaison;

                    $champ_libre_liaison = null;

                    if (empty($filtre->champ_de_liaison)) {

                        $champ_libre_liaison = Champ_libre::where('type_element', $type_element)->where('type_element_ajax', $filtre->type_element)->first();

                        if(!empty($champ_libre_liaison))
                            $champ_de_liaison = $champ_libre->nom_sql;
                    }

                    if (!isset($joins['jointures'][$type_element_tmp])) {

                        $joins['jointures'][$type_element_tmp] = array(
                            'champs_de_liaison' => array(),
                        );
                    }

                    if (!isset($joins['jointures'][$type_element_tmp]['champs_de_liaison'][$champ_de_liaison]) && $champ_de_liaison != null) {
                        $joins['alias_requete_compte']++;
                        $joins['jointures'][$type_element_tmp]['champs_de_liaison'][$champ_de_liaison] = array(
                            'alias' => 'liaison_'.$joins['alias_requete_compte'],
                            'modele_champ_liaison' => $champ_libre_liaison,
                        );
                    }

                }
            }
        }

		if(!empty($joins['jointures'])) {

            foreach($joins['jointures'] as $type_element_tmp => $join) {

                $champs_de_liaison = $join['champs_de_liaison'];

                if(empty($join['champs_de_liaison']))
                    continue;

                foreach($champs_de_liaison as $champ_de_liaison => $informations) {

                    if(!empty($informations['modele_champ_liaison']) && $informations['modele_champ_liaison']->type == 22) {
                        $requete = $requete->leftJoin($type_element_tmp. ' AS '.$informations['alias'],function($sous_requete) use($type_element,$champ_de_liaison,$informations,$type_element_tmp){
                            $sous_requete->on($type_element . '.' . $champ_de_liaison, $informations['alias'] . '.id');
                            $sous_requete->where($type_element . '.' . $informations['modele_champ_liaison']->contenu, $type_element_tmp);
                        });
                    }
                    else if(preg_match('/questionnaire_(\d+)/',$type_element_tmp,$match)) {

                        list($champ_liaison, $question) = explode('|', $champ_de_liaison);

                        preg_match('/question_(\d+)/',$question,$match_question);
                        $id_question = $match_question[1];

                        $requete->leftJoin('questionnaire_reponse AS '.$informations['alias'], function ($join) use ($type_element,$informations, $champ_liaison, $id_question) {
                            $join->on($informations['alias'].'.repondant_id', $type_element . '.' . $champ_liaison);
                            $join->where($informations['alias'].'.question_id', $id_question);
                        });
                    }
                    else
                        $requete = $requete->leftJoin($type_element_tmp. ' AS '.$informations['alias'],$type_element . '.' . $champ_de_liaison, $informations['alias'] . '.id');

                    $selects[] = $informations['alias'].'.id AS '.$informations['alias'].'_id';
                }
            }
        }

        $requete = $requete->select($selects);

		if(isset($parametres['filtres'])) {

            $valeurs_filtres = collect($parametres['filtres']);

            // on va chercher les infos du filtre
            $filtres = collect($parametres['filtres_affichage']);

            foreach ($valeurs_filtres as $valeur_filtre){

                $filtre = (object) $filtres->where('id',$valeur_filtre['id'])->first();

                if(empty($filtre->nom_sql))
                    continue;

                $type_element_filtre = !empty($filtre->type_element) ? $filtre->type_element : $type_element;
                $nom_sql = $filtre->nom_sql;

                $champ = champ_libre($type_element_filtre, $nom_sql);

                if (!empty($filtre->champ_de_liaison)) {
                    $champ->modele->alias_champ = $joins['jointures'][$type_element_filtre]['champs_de_liaison'][$filtre->champ_de_liaison]['alias'] . '.' . $nom_sql;
                    $champ->modele->alias_table = $joins['jointures'][$type_element_filtre]['champs_de_liaison'][$filtre->champ_de_liaison]['alias'];
                }

                $requete = $champ->champ->applique_filtre_sur_requete($valeur_filtre['valeurs'], $requete);
			}
		}

        if(!empty($parametres['filtres_appliques']))
            management('recherche_avancee')->applique_filtrage($parametres['filtres_appliques'],$requete,$type_element,$joins);

        if(!empty($parametres['recherche_avancee']))
            management('recherche_avancee')->applique_filtrage($parametres['recherche_avancee'],$requete,$type_element,$joins);

		// le cas ou on force un order by
		if(!empty($parametres['orderby'])) {

			// c'est probablement le cas ou on est en multi colonne avec les sous totaux
			if(strpos($parametres['orderby'], ',') !== false) {

				$requete = $requete->orderBy(\DB::raw($parametres['orderby']));
			}
			else {

				if(empty($parametres['orderby_sens']))
					$requete = $requete->orderBy($type_element.'.'.$parametres['orderby']);
				else
					$requete = $requete->orderBy($type_element.'.'.$parametres['orderby'], $parametres['orderby_sens']);
			}
		}


		// ensuite on applique un order by sur le tri (moins important que le filtre)
		if(!empty($parametres['kanban']) && empty($parametres['tri']) && empty($parametres['calcul']))
            $requete = $requete->orderBy(\DB::raw("coalesce(ordre_dans_kanban.ordre, 0)"));
		else
            $requete = $this->applique_tri_sur_requete($requete, $parametres, $type_element, $colonnes, $joins);

        if(empty($parametres['sans_order_id']))
            $requete->orderBy($type_element.'.id');

		// le cas ou on force une limit
		if(!empty($parametres['limit'])) {

            $requete = $requete->take($parametres['limit']);
        }

        if(empty($parametres['calcul']) && empty($parametres['ne_pas_utiliser_ancienne_requete']))
		    $this->resultat_cree_requete = $requete;

		return $requete;
	}

	/**
	 *
	 * Applique un tri sur la requete
	 *
	 */
	protected function applique_tri_sur_requete($requete, $parametres, $type_element, $colonnes, &$joins) {

		if(empty($parametres['tri']) || empty($colonnes) || isset($parametres['calcul']))
            return $requete;

        $tri = $parametres['tri'];

        // Cas du tri sur les kanbans
        if(is_array($tri)){
            foreach($tri as $tri_unitaire){
                $requete = champ_libre($type_element, $tri_unitaire->nom_sql)->champ->application_tri_requete($requete, $tri_unitaire->direction, $joins);
            }
            return $requete;
        }

        $groupement = null;
            
        if(str_contains($tri, '_'))
            list($tri,$groupement) = explode('_', $tri);

        $colonne = $colonnes->where('id', $tri)->first();

        if(empty($colonne))
            return $requete;

        $sens = !empty($parametres['direction_tri']) && $parametres['direction_tri'] == 1 ? 'DESC' : 'ASC';

        return $colonne->management->application_tri_requete($requete, $sens, $joins, $groupement);
	}

    /**
	 *
     * Envoie la liste des colonnes à afficher
	 *
     */
    public function obtenir_colonnes($liste_id, $table_libre, $type_export = 'basique') {

		$liste_libre = Liste_libre::find($this->id_liste_colonnes ?? $liste_id);

        if($type_export == 'total') {
            $colonnes = Champ_libre::where('type_element', $liste_libre->type_element)
                ->where(function($requete){
                    $requete->where('champ_systeme',null)
                        ->orWhere('champ_systeme',0);
                })
                ->orderBy('ordre')
                ->orderBy('id_cl')->get();

            foreach($colonnes as $colonne) {
                $colonne->champ_libre = $colonne;
                $colonne->id = ($colonne->id_cl);
                if(!empty($colonne->index_traduction))
                    $colonne->nom = strtoupper(traduction($colonne->index_traduction.'.nom'));
                $colonne->valeur = $colonne->nom_sql;
                $colonne->type = 'standard';

                $colonne_management = new Colonne_liste_management($colonne, $this, $table_libre);
                $colonne->management = $colonne_management;
                $colonne->management->chargement_donnees();
            }
        }
        else {
            $colonnes = Colonne::where('liste_libre_id', $this->id_liste_colonnes ?? $liste_id)->orderBy('ordre')->get();

            foreach($colonnes as $colonne) {
                $colonne_management = new Colonne_liste_management($colonne, $this, $table_libre);
                $colonne->management = $colonne_management;
                $colonne->management->chargement_donnees();
            }

            // si on est dans le cadre d'un rapport, on regarde si on doit retoucher automatiquement la liste des colonnes
            // c'est utilisé notamment dans le cadre du rapport recouvrement, si un exemple est nécessaire
            $colonnes = $this->modifie_liste_colonnes($colonnes);

        }

        if(editeur() && (empty($this->rapport) || $this->rapport->type != 'requete_sql') && $colonnes->filter(function($colonne){ 
                return ($colonne->type == 'standard' || empty($colonne->type)) && $colonne->valeur == 'id';
            })->count() == 0){

            $colonne_id = new Colonne();
            $colonne_id->id = -1;
            $colonne_id->nom = '#';
            $colonne_id->valeur = 'id';
            $colonne_id->type = 'standard';
            $colonne_id->champ_libre = null;
            $colonne_id->management = new Colonne_liste_management($colonne_id, $this, table_libre($liste_libre->type_element));

            $colonnes->prepend($colonne_id);
        }

		return $colonnes;
    }

    /**
     * @param $id_liste
     * @return void
     *
     * Permet d'obtenir les calculs d'une liste
     *
     */
    public function obtenir_calculs($id_liste){

        $calculs = Liste_libre_calcul::where('liste_libre_id', $id_liste)->orderBy('ordre')->get();

        return $calculs;
    }


    /*
     *
     * Stoque les données du rapport pour génération ultérieure
     *
     */
    public function rapport_liste_libre($id_rapport) {

    	$rapport = Rapport_libre::where('id_rapport', $id_rapport)->first();

		if(empty($rapport))
			return false;

        $rapport->type_rapport = 'liste_libre';

    	$this->donnees = $rapport;

		return $this;

    }

	/**
	 *
	 * Permet de retoucher les paramètres d'un rapport en spécifique
	 *
	 */
	public static function retouche_parametres($parametres) {

		return $parametres;
	}

	/**
	 *
	 * On ajoute des colonnes à la volée pour les rapports basés sur des listes libres
	 *
	 */
	public function modifie_liste_colonnes($colonnes) {

		return $colonnes;
	}

    /**
     *
     * Permet de récupérer les informations nécéssaires à la génération d'une liste libre
     *
     */
    public function recuperation_donnees_pour_liste_libre($liste_libre){

        $type_element = $liste_libre->type_element;

        $id_liste = $liste_libre->id;

        $management = liste($type_element);

        $management->id_liste = $liste_libre->id;

        $autres_vues = $management->autres_vues($liste_libre->id);

        $table_libre = table_libre($type_element);

        if(empty($table_libre))
            log_mis_en_forme('Récupération information liste libre', 'table libre' . $type_element);

        $actions = array();

        $type_element_options = service('vue_sql')->recupere_type_element($type_element);

        if(empty($type_element_options))
            $type_element_options = $type_element;

        if($table_libre->vue_sql == 1) {
            $vue_sql = modele('vue_sql')->where('nom_sql', $type_element)->first();

            if(!empty($vue_sql)) {
                $creation_possible = !empty($vue_sql->table_par_defaut);
            }
        }

        $formulaire = Formulaire::where('nom_formulaire', $type_element)->first();

        if($formulaire != null)
            $id_formulaire = $type_element;

        else
            $id_formulaire = null;

		if(!is_object($table_libre))
			exception("La table libre pour le type_element $type_element n'a pas été trouvée");

		$donnees = array(
            'table_libre'=> $table_libre,
            'type_element'=> $type_element,
            'id_formulaire'=> $id_formulaire,
            'id_liste'=> $liste_libre->id,
            'autres_vues'=> $autres_vues,
            'type_element_options'=> $type_element_options,
            'creation_possible' => $creation_possible ?? true,
            'formulaire_libre'=> $liste_libre->formulaire_libre,
            'liste_id'=> $id_liste,
            'fiche'=> $table_libre->fiche,
            'liste_libre'=> $liste_libre,
            'vue_sql' => $vue_sql ?? null,
        );

        $nombre_par_page = fonctionnalite('nombre_de_lignes_dans_listes');

        $donnees['options_liste']['nombre_par_page'] = $nombre_par_page;

        return $donnees;
    }

    /**
     *
     * Permet de récupérer les informations nécéssaires à la génération d'une liste libre rapport
     *
     */
    public function recuperation_donnees_pour_liste_libre_rapport($liste_libre){

        $id_rapport = $liste_libre->id_rapport;

        $management = liste_rapport($id_rapport);

        $type_element = $liste_libre->type_element;

        $infos_rapport = Rapport_libre::where('id_rapport', $id_rapport)->first();

        $table_libre = table_libre($liste_libre->type_element);

        $donnees['vue'] = false;

        $formulaire = Formulaire::where('nom_formulaire', $type_element)->first();

        if($formulaire != null)
            $id_formulaire = $type_element;

        else
            $id_formulaire = null;

        $autres_vues = $management->autres_vues($liste_libre->id);

        $type_element_options = service('vue_sql')->recupere_type_element($type_element);

        if(empty($type_element_options))
            $type_element_options = $type_element;

        if($table_libre->vue_sql == 1) {
            $vue_sql = modele('vue_sql')->where('nom_sql', $type_element)->first();

            if(!empty($vue_sql))
                $creation_possible = !empty($vue_sql->table_par_defaut);
        }

        $management_element = management($type_element);

        if(!empty($infos_rapport) && $infos_rapport->type == 'requete_sql')
            $liste_libre->desactiver_recherche_avancee = true;

        $donnees = array(
            'table_libre'=> $table_libre,
            'type_element'=> $liste_libre->type_element,
            'id_formulaire'=> $id_formulaire,
            'id_liste'=> $liste_libre->id,
            'autres_vues'=> $autres_vues,
            'type_element_options'=> $type_element_options,
            'creation_possible'=> $creation_possible ?? true,
            'formulaire_libre'=> $liste_libre->formulaire_libre,
            'management_element'=> $management_element,
            'liste_id'=> $liste_libre->id,
            'fiche'=> $table_libre->fiche,
            'liste_libre'=> $liste_libre,
            'id_rapport' => $id_rapport,
            'rapport' => $infos_rapport,
            'vue_sql' => $vue_sql ?? null,
        );

        // on a une vue en particulier pour le rapport
        if (view()->exists('eden::rapports.rapports.' . $id_rapport . '_vue_liste'))
            $donnees['vue'] = 'eden::rapports.rapports.' . $id_rapport . '_vue_liste';

        $calculs = Liste_libre_calcul::where('liste_libre_id', $liste_libre->id)->orderBy('ordre')->get();

        $donnees['infos_profils']['sans_profil'] = array(
            'calculs' => $calculs,
        );

        $donnees['infos_profils']['profil_extranet'] = array(
            'calculs' => $calculs,
        );

        if(empty($liste_libre->lignes_par_page))
            $nombre_par_page = fonctionnalite('nombre_de_lignes_dans_listes');
        else
            $nombre_par_page = $liste_libre->lignes_par_page;

        $donnees['options_liste']['nombre_par_page'] = $nombre_par_page;

        return $donnees;
    }

    /**
     *
     * Permet de récupérer les filtres d'une liste libre
     *
     */
    public function filtres_a_afficher($liste_libre,$rapport){

        $id_liste = $liste_libre->id;
        $type_element = $liste_libre->type_element;

        $filtres = Liste_libre_filtre::where('liste_libre_id', $id_liste)
            ->orderBy('ordre')
            ->get()->toArray();

        foreach($filtres as $index => $filtre) {

            if(empty($filtre['type_element']))
                $champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $filtre['nom_sql'])->first();
            else
                $champ_libre = Champ_libre::where('type_element', $filtre['type_element'])->where('nom_sql', $filtre['nom_sql'])->first();

            if(empty($champ_libre) || $champ_libre->inactif == 1)
                unset($filtres[$index]);
            else
                $filtres[$index]['nom_champ'] = $champ_libre->nom;

        }

        if(!empty($rapport->type) && $rapport->type == 'requete_sql'){

            $parametres_requete = Liste_libre_parametre_requete::where('liste_id',$liste_libre->id)->get()->toArray();

            foreach($parametres_requete as $parametre){
                $parametre['id'] = '#parametres_requete_'.$parametre['id'].'#';
                $parametre['type'] = 'parametres_requete';

                $filtres[] = $parametre;
            }
        }

        return array_values($filtres);
    }

    /**
     *
     * Permet de gérer les filtres venant d'un indicateur
     *
     */
    public function gestion_filtres_indicateur($id_liste,&$affichage_filtres,$type_element){

         if(isset($affichage_filtres['options_liste']['indicateur_source']) && !empty($affichage_filtres['options_liste']['indicateur_source'])) {

            $indicateur_source = json_decode($affichage_filtres['options_liste']['indicateur_source'],true);

            $filtres_nom_sql = array();

            foreach($affichage_filtres['filtres'] as $filtre){

                $filtres_nom_sql[$filtre['nom_sql']] = $filtre['id'];
            }

            // Sur une liste simple
            if(!isset($indicateur_source['id_rapport'])) {

            	$liste = Liste_libre::find($id_liste);

            	foreach($indicateur_source as $champ => $valeur) {

                    $id = isset($filtres_nom_sql[$champ]) ? $filtres_nom_sql[$champ] : 'indicateur_'.$champ;

                    if(!isset($filtres_nom_sql[$champ])) {
                        $affichage_filtres['filtres'][] = [
                            'id' => 'indicateur_'.$champ,
                            "liste_libre_id" => $id_liste,
                            'nom_sql' => $champ,
                            "type_element" => null,
                            "type_filtre" => null,
                            "methode_filtre" => null,
                            "emplacement" => "0",
                            "ordre" => sizeof($affichage_filtres['filtres']) + 1,
                            "champ_de_liaison" => null,
                            "nom_champ" => traduction('champs_libres.' . $type_element . '.' . $champ . '.nom'),
                        ];
                    }

                    $id_present = false;
                    foreach ($affichage_filtres['options_liste']['filtres'] as &$filtre) {

                        if($filtre['id'] == $id){
                            $id_present = true;
                            $filtre['valeurs'] = $valeur;
                        }
                    }

                    if(!$id_present)
                       $affichage_filtres['options_liste']['filtres'][] = [
                        'id' => $id,
                        'valeurs' => $valeur
                    ];
            	}

            // Sur un rapport
            } else {

	            $id_rapport = $indicateur_source['id_rapport'];

	            $rapport = Rapport_libre::where('id_rapport', $id_rapport)->first();

	            $parametres_rapport = json_decode($rapport->parametrage_rapport_libre);

	            // on doit récupérer tous les champs libres
	            $champs_libres = Champ_libre::where('type_element', $type_element)->get()->keyBy('nom_sql');

	            $filtres_pour_fiche = $indicateur_source['filtres_pour_fiche'];

                $affichage_filtres['options_liste']['filtres'] = [];

                $filtres_depuis_tableau_de_bord = array();

                foreach($filtres_pour_fiche as $nom_sql_filtre => $valeurs){

                    if(!isset($champs_libres[$nom_sql_filtre]))
                        continue;

                    $filtres_depuis_tableau_de_bord[] = [
                        'id' => isset($filtres_nom_sql[$nom_sql_filtre]) ? $filtres_nom_sql[$nom_sql_filtre] : 'filtre_dynamique_tableau_de_bord_'.$nom_sql_filtre,
                        'valeurs' => $valeurs
                    ];

                    if(!isset($filtres_nom_sql[$nom_sql_filtre])){

                        $affichage_filtres['filtres'][] = array(
                            "id" => 'filtre_dynamique_tableau_de_bord_'.$nom_sql_filtre,
                            "liste_libre_id" => $id_liste,
                            "nom_sql" => $nom_sql_filtre,
                            "type_element" => null,
                            "type_filtre" => null,
                            "methode_filtre" => null,
                            "emplacement" => "0",
                            "ordre" => sizeof($affichage_filtres['filtres']) +1,
                            "champ_de_liaison" => null,
                            "nom_champ" => $champs_libres[$nom_sql_filtre]->nom,
                        );

                        $filtres_nom_sql[$nom_sql_filtre] = 'filtre_dynamique_tableau_de_bord_'.$nom_sql_filtre;
                    }
                }

                foreach($filtres_depuis_tableau_de_bord as $filtre_rapport) {

                    $id_present = false;
                    foreach ($affichage_filtres['options_liste']['filtres'] as &$filtre) {

                        if($filtre['id'] == $filtre_rapport['id']){
                            $id_present = true;
                            $filtre['valeurs'] = $filtre_rapport['valeurs'];
                        }
                    }

                    if(!$id_present)
                       $affichage_filtres['options_liste']['filtres'][] = $filtre_rapport;
                }

	            $filtres_depuis_rapport = array();

	            foreach($champs_libres as $champ_libre) {

	                if(empty($parametres_rapport->{'filtre_applique_'.$champ_libre->type_element.'_'.$champ_libre->nom_sql}))
	                    continue;

	                $valeurs_filtre = (array) $parametres_rapport->{'filtre_applique_'.$champ_libre->type_element.'_'.$champ_libre->nom_sql};

                    if(in_array($champ_libre->type,array(1,20,10))) {
	                    $valeurs_filtre = array_keys($valeurs_filtre);

	                    foreach($valeurs_filtre as &$valeur_filtre){

	                        if($valeur_filtre === '#utilisateur_connecte#')
	                            $valeur_filtre = 'utilisateur_connecte';
	                    }
	                }

	                if(in_array($champ_libre->type, array(4,5))){

	                    $valeurs_filtre = array('variable' => $valeurs_filtre[0]);

	                    if(strpos($valeurs_filtre['variable'], '_plus_') !== false) {

	                        if(substr($valeurs_filtre['variable'], 0, 1) == 'j') {
	                            $le_jour = strtotime('+'.substr($valeurs_filtre['variable'], 7).' days');
	                        } elseif(substr($valeurs_filtre['variable'], 0, 1) == 'm') {
	                            $le_jour = strtotime('+'.substr($valeurs_filtre['variable'], 7).' months');
	                        }

	                        $valeurs_filtre['variable'] = '';
	                        $valeurs_filtre['debut'] = date('d/m/Y', $le_jour);
	                        $valeurs_filtre['fin'] =  date('d/m/Y', $le_jour);
	                    }
	                }

	                $filtres_depuis_rapport[] = [
                        'id' => isset($filtres_nom_sql[$champ_libre->nom_sql]) ? $filtres_nom_sql[$champ_libre->nom_sql] : 'filtre_dynamique_'.$champ_libre->nom_sql,
                        'valeurs' => $valeurs_filtre,
                    ];

	                if(!isset($filtres_nom_sql[$champ_libre->nom_sql])){

	                    $affichage_filtres['filtres'][] = array(
                            "id" => 'filtre_dynamique_'.$champ_libre->nom_sql,
	                        "liste_libre_id" => $id_liste,
	                        "nom_sql" => $champ_libre->nom_sql,
	                        "type_element" => null,
	                        "type_filtre" => null,
	                        "methode_filtre" => null,
	                        "emplacement" => "0",
	                        "ordre" => sizeof($affichage_filtres['filtres']) +1,
	                        "champ_de_liaison" => null,
	                        "nom_champ" => $champ_libre->nom,
	                    );
	                }
	            }

                foreach($filtres_depuis_rapport as $filtre_rapport) {

                    $id_present = false;
                    foreach ($affichage_filtres['options_liste']['filtres'] as &$filtre) {

                        if($filtre['id'] == $filtre_rapport['id']){
                            $id_present = true;
                            $filtre['valeurs'] = $filtre_rapport['valeurs'];
                        }
                    }

                    if(!$id_present)
                       $affichage_filtres['options_liste']['filtres'][] = $filtre_rapport;
                }

            }
        }
    }

    /**
     * @return void
     *
     * Permet de récupérer les données d'une liste lors de l'affichage de celle-ci
     *
     */
    public function initialisation_liste($type_element,$id_liste,$options_liste,$liste_libre){

        $affichage_filtres = array('options_liste' => $options_liste);

        $rapport = Rapport_libre::where('id_rapport',$liste_libre->id_rapport)->first();
        $affichage_filtres['filtres'] = $this->filtres_a_afficher($liste_libre,$rapport);

        $this->liste_libre = $liste_libre;
        $this->rapport = $rapport;

        if(!empty(moi())) {

            $parametres = Rapport_parametre::where('utilisateur_id', moi()->id)->where('id_rapport', 'liste_' . $id_liste)->first();

            if ($parametres !== null) {
                $parametres = unserialize(base64_decode($parametres->parametres));

                foreach($affichage_filtres['options_liste'] as $nom_option => $option){
                    $parametres[$nom_option] = $option;
                }

                $affichage_filtres['options_liste'] = $parametres;

            }

            if(!isset($affichage_filtres['options_liste']['filtres']))
                $affichage_filtres['options_liste']['filtres'] = [];

            // On gére l'ajout des filtres d'un indicateur
            $this->gestion_filtres_indicateur($id_liste,$affichage_filtres,$type_element);
        }

        $affichage_filtres['droits_liste'] = array(
            'profil_creation' => profil_creation($type_element),
        );

        $affichage_filtres['modele_liste_libre'] = $liste_libre;

        $type_element_options = service('vue_sql')->recupere_type_element($type_element);

        if(empty($type_element_options))
            $type_element_options = $type_element;

        $management_element_options = management($type_element_options);

        $actions_pour_composant = array();

        if($management_element_options->_type_element != null)
            $actions_pour_composant = $management_element_options->actions_listes($liste_libre);

        if(isset($actions_pour_composant['supprimer']) && $liste_libre->vue_sql == 1)
            unset($actions_pour_composant['supprimer']);

        $affichage_filtres['composant_actions'] = null;

        if(!empty($actions_pour_composant))
            $affichage_filtres['composant_actions'] = view('eden::listes.includes.actions', ['actions' => $actions_pour_composant, 'type_element' => $type_element])->render();

        $options_pour_composant = null;

        if(empty(table_libre($type_element)->desactiver_options))
            $options_pour_composant = $management_element_options->colonne_options($liste_libre);

        $affichage_filtres['composant_options'] = null;
        $affichage_filtres['composant_options_mobile'] = null;

        if(!empty($options_pour_composant)) {
            $affichage_filtres['composant_options'] = view('eden::listes.includes.options', ['options' => $options_pour_composant, 'type_element_options' => $type_element_options, 'type_element' => $type_element, 'mobile' => false])->render();
            $affichage_filtres['composant_options_mobile'] = view('eden::listes.includes.options', ['options' => $options_pour_composant, 'type_element_options' => $type_element_options, 'type_element' => $type_element, 'mobile' => true])->render();
        }

        $affichage_filtres['colonnes'] = $this->obtenir_colonnes($id_liste, table_libre($type_element));

        foreach($affichage_filtres['colonnes'] as $colonne)
            unset($colonne->management_methode);
        
        $affichage_filtres['calculs'] = $this->obtenir_calculs($id_liste);
        $affichage_filtres['elements_a_copier'] = management($type_element)->sous_elements_a_copier_avec_duplication();

        if(empty($liste_libre->desactiver_recherche_avancee))
            $affichage_filtres['recherche_avancee'] = [
                'recherches_avancees' => Liste_libre_management::informations_recherche_avancee([
                    'type_element' => $type_element,
                    'type' => 'liste',
                    'id_cible' => $liste_libre->id,
                    'utilisateur_id' => moi()->id ?? 0
                ])
            ];

        $recherche_avancee = modele('recherche_avancee')
            ->where('type','filtres_appliques')
            ->where('id_cible',$liste_libre->id)
            ->first();

        if(!empty($recherche_avancee))
            $affichage_filtres['options_liste']['filtres_appliques'] = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)
                ->structure();

        if(empty($affichage_filtres['filtres'])) {

            if(isset($affichage_filtres['options_liste']['filtres']))
                $affichage_filtres['options_liste']['filtres'] = [];

            return $affichage_filtres;
        }

        $ids_filtres = collect($affichage_filtres['filtres'])->pluck('id')->toArray();

        if (isset($affichage_filtres['options_liste']['filtres'])){
            foreach($affichage_filtres['options_liste']['filtres'] as $index_filtre => $filtre){

                if(!in_array($filtre['id'],$ids_filtres))
                    unset($affichage_filtres['options_liste']['filtres'][$index_filtre]);
            }

            $affichage_filtres['options_liste']['filtres'] = array_values($affichage_filtres['options_liste']['filtres']);
        }


        foreach ($affichage_filtres['filtres'] as &$filtre) {

            $filtre = (object)$filtre;

            $filtre->valeur = false;

            $type_element_filtre = !empty($filtre->type_element) ? $filtre->type_element : $type_element;

            if(!empty($filtre->type) && $filtre->type == 'parametres_requete') {
                $filtre->index_traduction = $filtre->nom;
                $filtre->type_element = $type_element;
            }
            else {
                $champ_management = management($type_element_filtre)->champ($filtre->nom_sql);

                $filtre->modele = $champ_management->modele;
                $filtre->index_traduction = $champ_management->modele->index_traduction.'.nom';
                $filtre->type = $champ_management->modele->type;

                if (empty($filtre->type_element))
                    $filtre->type_element = $type_element;

                $filtre->liste_choix = $champ_management->modele->liste_choix;
                $filtre->type_filtre = $champ_management->type_filtre;
            }
        }

		return $affichage_filtres;
    }

    /**
     *
     * Spécifique au vue sql : paramétres de création d'un élément quand la colonne champ est vide
     *
     */
    public function parametres_creation_element_colonne_champ_vide($parametres_champ,$element){

        return [];
    }
    
}
