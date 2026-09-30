<?php

namespace App\Eden\Controllers\Parametrage;

use App\Eden\Managements\Parametrage\Rapport_management;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_parametre_requete;
use App\Eden\Models\Rapport_parametre;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Rapport_libre;
 use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Listes_libres_controller extends Controller {

    /**
	 *
	 * Affiche la liste des listes libres
	 *
	 */
    public function index() {

		$liste_libres = Liste_libre::join('eden_tableslibres', 'eden_tableslibres.type_element', 'eden_listeslibres.type_element')
			->select(\DB::raw('eden_listeslibres.*, eden_tableslibres.nom_table'))
			->orderBy('nom_table')->get();


		return view('eden::parametrage.listes_libres', [

			'liste_libres' => $liste_libres,
			'liste_libre_choix' => collect(variable('liste_libre_choix_ajout')),
		]);
    }

	/**
	 *
	 * Affiche la liste des colonne d'une liste libre
	 *
	 */
    public function liste_libre($liste_libre_id) {


		$colonnes = Liste_libre_management::recuperer_liste_colonnes($liste_libre_id);
		$filtres = Liste_libre_filtre::where('liste_libre_id', $liste_libre_id)->orderBy('ordre')->get();
		$calculs = Liste_libre_calcul::where('liste_libre_id', $liste_libre_id)->orderBy('ordre')->get();
		$couleurs = Liste_libre_couleur::where('liste_libre_id', $liste_libre_id)->get();
		$autresvues = Liste_libre_autresvues::where('liste_libre_id_1', $liste_libre_id)->get();

		$profils = modele('profil')->get();

		// on va chercher le type_element de la liste libre
		$champs_libres = array();

		$type_element = Liste_libre::find($liste_libre_id)->type_element;

		if(!empty($type_element)) {

			$champs_libres = Champ_libre::where('type_element', $type_element)
                ->where(function($requete){
                    $requete->where('champ_systeme',null)
                        ->orWhere('champ_systeme',0);
                })
                ->orderBy('nom')->get();
		}

		// on va chercher les champs libres par type pour les colonnes standards
		$champs_libres_par_type = array();

		foreach($champs_libres as $champ_libre) {

			if($champ_libre->type == 0) {

				$champs_libres_par_type['1) Textes'][] = $champ_libre;
			}

			elseif(in_array($champ_libre->type, array(1,20))) {

				$champs_libres_par_type['2) Listes'][] = $champ_libre;
			}

			elseif(in_array($champ_libre->type, array(2,3))) {

				$champs_libres_par_type['3) Nombres'][] = $champ_libre;
			}

			elseif(in_array($champ_libre->type, array(4,5))) {

				$champs_libres_par_type['4) Dates'][] = $champ_libre;
			}

			else {

				$champs_libres_par_type['5) Autres'][] = $champ_libre;
			}
		}

		ksort($champs_libres_par_type);

		// on va chercher les champs libres par type element pour les colonnes de liaison
		$champs_libres_liaison_par_type = array();

		$champs_libres_pour_liaison = array();

		if(!empty($type_element)) {

            $champs_libres_pour_liaison = Champ_libre::where(function($requete) use ($type_element) {
                    $requete->where(function ($sous_requete) use ($type_element) {
                        $sous_requete->where('type_element', $type_element);
                        $sous_requete->where('type', 42);
                    })
                    ->orWhere(function ($sous_requete) use ($type_element) {
                        $sous_requete->where('type_element', $type_element);
                        $sous_requete->where('type', 21);
                        $sous_requete->whereNotNull('contenu');
                        $sous_requete->where('contenu', '!=', '');
                    });
                })
                ->where(function($requete){
                    $requete->where('champ_systeme',null)
                        ->orWhere('champ_systeme',0);
                })
                ->orderBy('nom')
                ->get();

            $id_element_dynamique_pour_liaison = Champ_libre::where('type_element', $type_element)
                ->where('type', 22)
                ->where(function($requete){
                    $requete->where('champ_systeme',null)
                        ->orWhere('champ_systeme',0);
                })
                ->orderBy('nom')
                ->get();

            $array_type_element_liaison = array();

            foreach ($id_element_dynamique_pour_liaison as $champ_id_element){

                if(!isset($array_type_element_liaison[$champ_id_element->contenu]))
                    $array_type_element_liaison[$champ_id_element->contenu] = array();

                $array_type_element_liaison[$champ_id_element->contenu][] = $champ_id_element;
            };
		}

		foreach($champs_libres_pour_liaison as $champ_libre_pour_liaison) {

            // Cas particulié des champs dynamiques
            if($champ_libre_pour_liaison->type != 21) {

                $type_element_ajax = $champ_libre_pour_liaison->type_element_ajax;

				$vues_sql = modele('vue_sql')->where('table_par_defaut',$type_element_ajax)->get()->pluck('nom_sql')->toArray();

                // on va chercher le type_element ajax
                $champs_libres_pour_liaison_par_type_element = Champ_libre::whereIn('type_element', array_merge([$type_element_ajax],$vues_sql))->orderBy('nom')->get()->groupBy('type_element');

                foreach (array_merge([$type_element_ajax],$vues_sql) as $type_element_enfant) {

					$liaison = [
						'type_element' => $type_element_enfant,
						'champ_de_liaison' => $champ_libre_pour_liaison->nom_sql,
						'type' => 42,
						'vue_sql' => in_array($type_element_enfant, $vues_sql),
						'champs_libres' => []
					];

					foreach($champs_libres_pour_liaison_par_type_element[$type_element_enfant] ?? [] as $champ_libre) {

                    	$this->ajout_champs_element_liaison($champ_libre,$liaison['champs_libres']);
					}

					$champs_libres_liaison_par_type[] = $liaison;
                }
            }
            else{


                $contenu = json_decode($champ_libre_pour_liaison->contenu);

                $types_elements = array();

                foreach ($contenu as $type_element_statut){

                    if($type_element_statut->valeur){

                        // On va chercher le champ id element dynamique correspondant si il existe
                        if (isset($array_type_element_liaison[$champ_libre_pour_liaison->nom_sql])) {
                            $types_elements[$type_element_statut->type_element] = $array_type_element_liaison[$champ_libre_pour_liaison->nom_sql];
                        }
                    }
                }

                foreach ($types_elements as $type_element_ajax => $id_element_dynamiques){

                    // on va chercher le type_element ajax
                    $champs_libres_pour_liaison_par_type_element = Champ_libre::where('type_element', $type_element_ajax)->orderBy('nom')->get();

                    foreach ($id_element_dynamiques as $id_element_dynamique) {

						$liaison = [
							'type_element' => $type_element_ajax,
							'champ_de_liaison' => $champ_libre_pour_liaison->nom_sql,
							'id_element_dynamique' => $id_element_dynamique->nom_sql,
							'type' => 21,
							'champs_libres' => []
						];

                        foreach ($champs_libres_pour_liaison_par_type_element as $champ_libre) {

                            $champ_libre = clone $champ_libre;

                            $champ_libre->origine_champ_dynamique = $champ_libre_pour_liaison->nom_sql;
                            $champ_libre->origine_champ_dynamique_id_element = $id_element_dynamique->nom_sql;

                            $this->ajout_champs_element_liaison($champ_libre,$liaison['champs_libres']);

                        }

						$champs_libres_liaison_par_type[] = $liaison;
                    }
                }
            }
		}

		ksort($champs_libres_liaison_par_type);

		$champs_libres_type = Champ_libre::where('type_element', $type_element)
            ->where(function($requete){
                $requete->where('champ_systeme',null)
                    ->orWhere('champ_systeme',0);
            })
            ->whereIn('type', [42,22])->get();

		$liste_libre = Liste_libre::where('id', $liste_libre_id)->first();

		if($liste_libre->filtres_appliques == null) {

			$liste_libre->filtres_appliques = "[]";
			$liste_libre->save();
		}

		$id_rapport = $liste_libre->id_rapport;

		// On ajoute le type element qui est lié dans $autresvues
		if(!$autresvues->isEmpty()){

			foreach ($autresvues as $autrevue) {

				$autre_liste_type_element = Liste_libre::where('id',$autrevue->liste_libre_id_2)->first();
				$autrevue->type_element_2 = $autre_liste_type_element->type_element;
				$autrevue->id_rapport = $autre_liste_type_element->id_rapport;
			}
		}

		$listes_libres = Liste_libre::where('type_element','not like',$liste_libre->type_element)->orderBy('type_element')->get();
		$listes_libres_du_type_element = Liste_libre::where('type_element',$liste_libre->type_element)->get();

		$filtres_appliques = $liste_libre->filtres_appliques;

        if (@unserialize($filtres_appliques) === false)
            $filtres_appliques = json_decode($filtres_appliques);
        else
            $filtres_appliques = unserialize($filtres_appliques);

        $rapport = '{}';
        if(!empty($id_rapport)) {
        	$rapport = Rapport_libre::where('id_rapport', $id_rapport)->first();

        	if(empty($rapport))
        		$rapport = collect([]);

        	if(isset($rapport->kanban_colonnes) && !empty($rapport->kanban_colonnes)) {
                $rapport->kanban_colonnes_decode = json_decode($rapport->kanban_colonnes);
                $rapport->colonnes_kanban = json_decode($rapport->kanban_colonnes);
            }
            
        	if(!isset($rapport->type_element) || empty($rapport->type_element))
        		$rapport->type_element = $type_element;

            if($rapport->type == 'requete_sql')
                $rapport->parametres_requete = Liste_libre_parametre_requete::where('liste_id',$liste_libre->id)->get();

        }

		$champs_libres_pour_select = $this->recupere_champs_libres_pour_select($type_element);

	    $liste_libre->desactiver_options_individuelle = !empty($liste_libre->desactiver_options_individuelle) ?
            json_decode($liste_libre->desactiver_options_individuelle) : [];
	    
		$liste_libre->options_mobile = !empty($liste_libre->options_mobile) ?
            json_decode($liste_libre->options_mobile) : [];

        $options_liste = collect(management($type_element)->liste_colonnes_options());

        $actions_liste = management($type_element)->actions_a_afficher($liste_libre->id);
        $actions_liste = collect(array_keys($actions_liste));

        if(!empty($liste_libre->desactiver_actions_individuelle))
            $liste_libre->desactiver_actions_individuelle = json_decode($liste_libre->desactiver_actions_individuelle, true);
        else
            $liste_libre->desactiver_actions_individuelle = array();

	    $liste_libre->tri_kanban = json_decode($liste_libre->tri_kanban);

	    if(empty($liste_libre->tri_kanban))
	    	$liste_libre->tri_kanban = [];

		return view('eden::parametrage.liste_libre', [

			'liste_libre' => $liste_libre,
			'liste_standard' => Rapport_management::rapport_standard($rapport,$liste_libre),
			'liste_libre_id' => $liste_libre_id,
            'actions_liste' => $actions_liste,
			'colonnes' => $colonnes,
			'filtres' => $filtres,
			'filtres_appliques' => $filtres_appliques,
			'calculs' => $calculs,
			'champs_libres' => $champs_libres,
			'champs_libres_par_type' => $champs_libres_par_type,
			'champs_libres_liaison_par_type' => $champs_libres_liaison_par_type,
			'champs_libres_pour_select' => json_encode($champs_libres_pour_select),
			'champs_libres_type' => $champs_libres_type,
			'type_element' => $type_element,
			'couleurs' => $couleurs,
			'profils' => $profils,
			'id_rapport' => $id_rapport,
			'rapport' => $rapport,
			'autresvues' => $autresvues,
			'listes_libres' => $listes_libres,
			'listes_libres_du_type_element' => $listes_libres_du_type_element,
			'options_liste' => $options_liste,
		]);
    }

	/**
	 *
	 * Enregistre une nouvelle valeur dans les listes libres
	 *
	 */
	public function listes_libres_enregistrer(Request $formulaire, $type_element = false){

        $parametres = $formulaire->all();

        $type_liste = $parametres['type_liste'];

        if($type_liste == 'rapport') {

            $rapport_management = new Rapport_management();
            $retour = $rapport_management->enregistrer_nouveau_rapport($parametres);

            return response()->json($retour);
        }

        $liste_libre_management = new Liste_libre_management();

        $retour = $liste_libre_management->enregistrer_nouvelle_liste($parametres);

        if($retour['retour'] === true)
            return response()->json(array('retour' => true, 'redirection' => route('parametrage.liste_libre.index', [$retour['liste_id']])));

		return response()->json($retour);

	}


	/**
	 *
	 * Ajoute ou modifie une colonne sur une liste libre donnée
	 *
	 */
	public function colonne_enregistrer(Request $formulaire)
    {

        if (empty($formulaire->id)) {

            $ajout = true;
            $colonne = new Colonne;
        } else {

            $ajout = false;
            $colonne = Colonne::find($formulaire->id);
        }

        $la_liste_libre = Liste_libre::find($formulaire->liste_libre_id);

        $les_colonnes_liste = Colonne::where('liste_libre_id', $formulaire->liste_libre_id)->get();

        $colonne->liste_libre_id = $formulaire->input('liste_libre_id');

        if ($formulaire->has('nom'))
            $colonne->nom = $formulaire->input('nom');

        $colonne->ordre = $formulaire->input('ordre');
        $colonne->lien_vers_element = $formulaire->input('lien_vers_element');
        $colonne->lien_vers_autre_element = $formulaire->input('lien_vers_autre_element');
		$colonne->afficher_formulaire_element = $formulaire->input('afficher_formulaire_element');
		$colonne->afficher_avatars_utilisateurs = $formulaire->input('afficher_avatars_utilisateurs');
        $colonne->tri_desactive = $formulaire->input('tri_desactive');
        $colonne->tri_par_defaut = $formulaire->input('tri_par_defaut');
        $colonne->sens_tri_par_defaut = $formulaire->input('sens_tri_par_defaut');
        $colonne->retour_a_la_ligne_impossible = $formulaire->input('retour_a_la_ligne_impossible');
        $colonne->type = $formulaire->input('type');
        $colonne->caracteres_max = $formulaire->input('caracteres_max');
        $colonne->couleur_colonne = $formulaire->input('couleur_colonne') != '#000000' ? $formulaire->input('couleur_colonne') : null;

        if (in_array($formulaire->input('type'),["standard","liaison","methode","concatenation"]))
            $colonne->valeur = $formulaire->input('valeur');
        else
            $colonne->valeur = null;

        if ($formulaire->input('type') == "methode")
            $colonne->methode = $formulaire->input('methode');
        else
            $colonne->methode = null;

        if ($formulaire->input('type') == "champ" || $formulaire->input('type') == "liaison" || $formulaire->input('type') == "standard")
            $colonne->champ = $formulaire->input('champ');
        else
            $colonne->champ = null;

        if ($formulaire->alignement_colonne !== null)
            $colonne->alignement_colonne = $formulaire->alignement_colonne;
        else
            $colonne->alignement_colonne = null;

        if ($formulaire->arguments !== null)
            $colonne->arguments = $formulaire->arguments;
        else
            $colonne->arguments = null;

        // on force la désactivation du tri sur les colonne méthodes
        if (in_array($formulaire->type, array('methode')) && empty($formulaire->valeur))
            $colonne->tri_desactive = 1;

		$filtrages = [];

		if($formulaire->type == 'calcul'){
			$colonne->source_calcul = $formulaire->source_calcul ?? null;
			$colonne->type_calcul = $formulaire->type_calcul ?? null;
			$colonne->champ_calcul = $formulaire->champ_calcul ?? null;
			$colonne->groupement_calcul = $formulaire->groupement_calcul ?? null;
			$colonne->periodicite_calcul = $formulaire->periodicite_calcul ?? null;

			if(!empty($formulaire->filtrages_calcul))
				$filtrages = json_decode($formulaire->filtrages_calcul,true);
		}

        $nom_colonne = strtolower(retraite_caracteres_speciaux($colonne->nom, '_'));

        $base_index_traduction = array(
            !empty($la_liste_libre->id_rapport) ? 'rapport' : 'liste',
            !empty($la_liste_libre->id_rapport) ? $la_liste_libre->id_rapport : $la_liste_libre->type_element,
            'colonne',
            $nom_colonne
        );

        $traductions_index = $les_colonnes_liste->pluck('index_traduction')->toArray();

        $încrement = 1;

        while(in_array(implode('.',$base_index_traduction),$traductions_index)){

            $base_index_traduction[3] = $nom_colonne.'_'.$încrement;

            $încrement++;
        }

        if (empty($colonne->index_traduction) && !empty($colonne->nom) && $colonne->nom != '#') {

            $colonne->index_traduction = service('traduction')->calcul_index_traduction(
                5,
                $base_index_traduction,
                array(
                    'nom' => $colonne->nom
                )
            );
        }

        $colonne->save();

        if ($colonne->tri_par_defaut == 1) {
            foreach ($les_colonnes_liste as $colonne_liste){
                if($colonne_liste->tri_par_defaut == 1 && $colonne_liste->id != $colonne->id) {
                    $colonne_liste->tri_par_defaut = 0;
                    $colonne_liste->sens_tri_par_defaut = 0;
                    $colonne_liste->save();
                }
            }
        }

		$recherches_avancees = modele('recherche_avancee')
            ->where('type', 'liste_libre_colonne_calcul_'.$colonne->id)
            ->get()->keyBy('id_cible');

        foreach($filtrages as $filtrage){

            $id_cible = $filtrage['id_cible'];
            $type_element = $filtrage['type_element'];
            $structure = $filtrage['structure'];

            if(empty($structure))
                continue;

            if(isset($recherches_avancees[$id_cible])){
                $recherche_avancee = $recherches_avancees[$id_cible];
                $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);
                unset($recherches_avancees[$id_cible]);
            }
            else
                $management_recherche_avancee = management('recherche_avancee');

            $management_recherche_avancee->enregistre([
                'type' => 'liste_libre_colonne_calcul_'.$colonne->id,
                'type_element' => $type_element,
                'id_cible' => $id_cible,
                'structure' => $structure
            ]);
        }

        foreach($recherches_avancees as $recherche_avancee){
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

        Cache_management::generation_liste_libre($formulaire->liste_libre_id);

        // On génère la migration spécifique
		Liste_libre_management::generer_fichier_migration_liste_libre($formulaire->liste_libre_id);

		$colonnes = Liste_libre_management::recuperer_liste_colonnes($formulaire->input('liste_libre_id'));

		return json_encode(array('retour' => true, 'colonnes' => $colonnes));
    }

    /**
	 *
	 * Récupère les détails d'une colonne
	 *
	 */
    public function colonne_recuperer($id_colonne) {

		$colonne = Colonne::where('id', $id_colonne)->first();

		if($colonne->type == 'calcul'){
			$recherches_avancees = modele('recherche_avancee')
				->where('type', 'liste_libre_colonne_calcul_'.$colonne->id)
				->get();

			foreach($recherches_avancees as $recherche_avancee){
				$recherche_avancee->structure = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)->structure();
			}

			$colonne->filtrages_calcul = $recherches_avancees;
		}

		// rétrocompatibilité
		if(empty($colonne->type) && !empty($colonne->methode)) {

			$colonne->type = 'methode';
		}
		elseif(empty($colonne->type)) {

			$colonne->type = 'standard';
		}

		if(!empty($colonne->profils))
			$colonne->profils = json_decode($colonne->profils);
		else
			$colonne->profils = [];

		return json_encode($colonne);
    }

	/**
	 *
	 * SUpprime une colonne
	 *
	 */
    public function colonne_supprimer($id) {

		$colonne = Colonne::find($id);
		$id_liste = $colonne->liste_libre_id;

        if(!empty($colonne->index_traduction))
            service('traduction')->supprime_index_traduction($colonne->index_traduction);

		$colonne->delete();

		// On génère la migration spécifique
		Liste_libre_management::generer_fichier_migration_liste_libre($id_liste);

		$colonnes = Liste_libre_management::recuperer_liste_colonnes($id_liste);

        Cache_management::generation_liste_libre($id_liste);

        return json_encode(array('colonnes' => $colonnes));
    }

	/**
	 *
	 *
	 * on modifie l'ordre de la colonne
	 *
	 *
	 */
	public function changement_ordre_liste_libre(Request $formulaire) {

		$tableaux_nouveaux_ordres = $formulaire['tableaux_nouveaux_ordres'];

		foreach($tableaux_nouveaux_ordres as $id => $ordre) {

            if ($formulaire['element'] == 'colonne')
                $element = Colonne::where('id', $id)->first();
            else if ($formulaire['element'] == 'filtre')
                $element = Liste_libre_filtre::where('id', $id)->first();
            else if ($formulaire['element'] == 'calcul')
                $element = Liste_libre_calcul::where('id', $id)->first();

            $element->ordre = $ordre;
            $element->save();
        }

        if($formulaire['element'] == 'colonne') {
            $elements = Liste_libre_management::recuperer_liste_colonnes($element->liste_libre_id);
            $type_element = 'colonnes';
        }
        else if($formulaire['element'] == 'filtre') {
            $elements = Liste_libre_filtre::where('liste_libre_id', $element->liste_libre_id)->orderBy('ordre')->get();
            $type_element = 'filtres';
        }
        else if($formulaire['element'] == 'calcul') {
            $elements = Liste_libre_calcul::where('liste_libre_id', $element->liste_libre_id)->orderBy('ordre')->get();
            $type_element = 'calculs';
        }

        // On génère la migration spécifique
        Liste_libre_management::generer_fichier_migration_liste_libre($element->liste_libre_id);

        Cache_management::generation_liste_libre($element->liste_libre_id);

        return json_encode(array("retour" => true,"type_element" => $type_element,"elements" => $elements));
	}

	/**
	 *
	 * Modification d'un paramètre sur une colonne (responsive, par exemple)
	 *
	 */
	public function changement_etat_colonne() {

		$colonne = Colonne::where('id',request()->id)->first();

		$colonne->{request()->parametre} = request()->nouvelle_valeur;
		$colonne->save();

		Liste_libre_management::generer_fichier_migration_liste_libre($colonne->liste_libre_id);

		$colonnes = Liste_libre_management::recuperer_liste_colonnes($colonne->liste_libre_id);

        Cache_management::generation_liste_libre($colonne->liste_libre_id);

		return json_encode(array('colonnes' => $colonnes));

	}

	/**
	 *
	 * Ajoute ou modifie un filtre sur une liste libre donnée
	 *
	 */

	public function rapport_charger(Request $formulaire) {

		$retour = [];

		$champ = management($formulaire->type_element)->champ($formulaire->kanban);

		if(isset($champ->valeurs_possibles)) {
            
            $temp = $champ->valeurs_possibles;

			$tri_de_base = json_decode($formulaire->kanban_colonnes);
			if(empty($tri_de_base) || $tri_de_base === false) {

				foreach($temp as $clef => $texte) {
					$retour[] = ['valeur' => $clef, 'texte' => $texte];
				}

			} else {

				foreach($tri_de_base as $clef) {
					if(isset($temp[$clef])) {
						$retour[] = ['valeur' => $clef, 'texte' => $temp[$clef]];
						unset($temp[$clef]);
					}
				}

				foreach($temp as $clef => $texte) {
					$retour[] = ['valeur' => $clef, 'texte' => $texte];
				}
			}

			return json_encode(array('retour' => true, 'champ' => $retour));
		}

		return json_encode(array('retour' => traduction('messages.php.liste_libre.champ_incompatible_kanban')));
	}

	/**
	 *
	 * Enregistre les parametres du rapport (kanban)
	 *
	 */
	public function rapport_enregistrer(Request $formulaire) {

		$rapport = Rapport_libre::where('id_rapport', $formulaire['id_rapport'])->first();

        // On met à jour les migrations
        $liste_libre = Liste_libre::where('id_rapport', $rapport->id_rapport)->first();

        if(!is_array($formulaire['colonnes_kanban'])) {
            $temp = urldecode($formulaire['colonnes_kanban']);

            $colonnes_kanban = [];
            foreach (explode('&', $temp) as $piece) {
                $e = explode("=", $piece);

                if (isset($e[1]))
                    $colonnes_kanban[] = intval($e[1]);
            }
        } else
            $colonnes_kanban = $formulaire['colonnes_kanban'];

		$rapport->kanban_colonnes = json_encode($colonnes_kanban);
		$rapport->type = $formulaire['type'];

        if(isset($formulaire['extranet']))
		    $rapport->extranet = $formulaire['extranet'];

        if(isset($formulaire['requete_sql']))
            $rapport->requete_sql = $formulaire['requete_sql'];

        if(isset($formulaire['cle_etrangere']))
            $rapport->cle_etrangere = $formulaire['cle_etrangere'];
        
		$rapport->kanban = $formulaire['kanban'];

		if(isset($formulaire['kanban_entete_calcul_somme'])) {

			$rapport->kanban_entete_calcul_somme = $formulaire['kanban_entete_calcul_somme'];
		}

		if(isset($formulaire['kanban_entete_calcul_champ'])) {

			$rapport->kanban_entete_calcul_champ = $formulaire['kanban_entete_calcul_champ'];
		}

		if(isset($formulaire['kanban_entete_calcul_nombre'])) {

			$rapport->kanban_entete_calcul_nombre = $formulaire['kanban_entete_calcul_nombre'];
		}

		if(isset($formulaire['kanban_entete_calcul_unite'])) {

			$rapport->kanban_entete_calcul_unite = $formulaire['kanban_entete_calcul_unite'];
		}

		if(isset($formulaire['kanban_afficher_utilisateur_1'])) {

			$rapport->kanban_afficher_utilisateur_1 = $formulaire['kanban_afficher_utilisateur_1'];
		}

		if(isset($formulaire['kanban_afficher_utilisateur_2'])) {

			$rapport->kanban_afficher_utilisateur_2 = $formulaire['kanban_afficher_utilisateur_2'];
		}

		if(isset($formulaire['kanban_tri_champ'])) {

			$rapport->kanban_tri_champ = $formulaire['kanban_tri_champ'];
		}

		if(isset($formulaire['kanban_tri_sens'])) {

			$rapport->kanban_tri_sens = $formulaire['kanban_tri_sens'];
		}

		if(isset($formulaire['toutes_les_colonnes']))
			$rapport->toutes_les_colonnes = $formulaire['toutes_les_colonnes'];
		if(isset($formulaire['exclusion_colonnes']))
			$rapport->exclusion_colonnes = $formulaire['exclusion_colonnes'];

        $parametres_requete = Liste_libre_parametre_requete::where('liste_id',$liste_libre->id)->get()->keyBy('id');

        if(isset($formulaire['parametres_requete'])) {

            foreach($formulaire['parametres_requete'] as $parametre){

                if(isset($parametre['id']) && isset($parametres_requete[$parametre['id']])) {

                    $parametre_requete = $parametres_requete[$parametre['id']];

                    unset($parametres_requete[$parametre['id']]);
                }
                else
                    $parametre_requete = new Liste_libre_parametre_requete();

                foreach($parametre as $champ => $valeur){

                    $parametre_requete->{$champ} = $valeur;
                }

                $parametre_requete->liste_id = $liste_libre->id;

                $parametre_requete->save();
            }
        }

        $parametres_requete->map(function($parametre){
            $parametre->delete();
        });

		$rapport->save();

        if(!empty($liste_libre))
            Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre->id);

		return json_encode(array('retour' => true,'parametres_requete' =>
            $rapport->type == 'requete_sql' ? Liste_libre_parametre_requete::where('liste_id',$liste_libre->id)->get() : []
        ));
	}

	/**
	 *
	 * Ajoute ou modifie un filtre sur une liste libre donnée
	 *
	 */
	public function filtre_enregistrer(Request $formulaire) {

		if(empty($formulaire->id))
			$filtre = new Liste_libre_filtre;
        
		else {
            $filtre = Liste_libre_filtre::find($formulaire->id);
            
            $id_liste = $filtre->liste_libre_id;
            
            if($filtre->nom_sql != $formulaire->input('nom_sql')) {
                
                $filtre_enregistre = Rapport_parametre::where('id_rapport', 'liste_' . $id_liste)->first();

                $parametres = unserialize(base64_decode($filtre_enregistre->parametres));
                
                unset($parametres['filtres'][$filtre->nom_sql]);

                $filtre_enregistre->parametres = base64_encode(serialize($parametres));

                $filtre_enregistre->save();
                
            }
        }

		$filtre->liste_libre_id = $formulaire->input('liste_libre_id');
		$filtre->nom_sql = $formulaire->input('nom_sql');
		$filtre->type_filtre = $formulaire->input('type_filtre');
		$filtre->emplacement = $formulaire->input('emplacement');
		$filtre->type_element = $formulaire->input('type_element');
		$filtre->afficher_categories_liste = $formulaire->input('afficher_categories_liste');
		$filtre->ordre = intval($formulaire->input('ordre'));
		$filtre->champ_de_liaison = $formulaire->input('champ_de_liaison');

		$filtre->save();

		// On génère la migration spécifique
		Liste_libre_management::generer_fichier_migration_liste_libre($formulaire->liste_libre_id);

        Cache_management::generation_liste_libre($formulaire->liste_libre_id);

        $filtres = Liste_libre_filtre::where('liste_libre_id', $formulaire->input('liste_libre_id'))->orderBy('ordre')->get();

		return json_encode(array('retour' => true, 'filtres' => $filtres));
    }

    /**
	 *
	 * Récupère les détails d'un filtre
	 *
	 */
    public function filtre_recuperer($id_filtre) {

		$filtre = Liste_libre_filtre::where('id', $id_filtre)->first();

        $type_element_liste = Liste_libre::find($filtre->liste_libre_id)->type_element;

        $type_element = '';
        $champ_de_liaison = $filtre->champ_de_liaison;

		if($filtre->type_element != '' && $filtre->type_element != $type_element_liste){

            $type_element = $filtre->type_element;

            if($champ_de_liaison == null) {
                $champ_libre = Champ_libre::where('type_element', $type_element_liste)->where('type_element_ajax', $filtre->type_element)->first();
                $champ_de_liaison = $champ_libre->nom_sql;
            }
        }

		// on va chercher le type de champ
		$champ = Champ_libre::where('type_element', (empty($type_element)) ? $type_element_liste : $type_element)->where('nom_sql', $filtre->nom_sql)->first();

		$filtre->type_de_champ = $champ->type ?? 0;

        $filtre->tableau_liaison = array('type_element' => $type_element,'champ_de_liaison' => $champ_de_liaison );

		if(!empty($filtre->profils))
			$filtre->profils = json_decode($filtre->profils);
		else
			$filtre->profils = [];

		return json_encode($filtre);
    }

	/**
	 *
	 * Supprime un filtre
	 *
	 */
    public function filtre_supprimer($id) {

        $filtre = Liste_libre_filtre::find($id);
		$id_liste = $filtre->liste_libre_id;


		$filtres_a_reorganiser = array();

		if(!empty($filtre->ordre)) {

			$filtres_a_reorganiser = Liste_libre_filtre::where('liste_libre_id',$filtre->liste_libre_id)->where('ordre','>',$filtre->ordre)->get();

			foreach($filtres_a_reorganiser as $un_filtre_a_reorganiser){

				$un_filtre_a_reorganiser->ordre --;
				$un_filtre_a_reorganiser->save();
			}
		}

		$filtre->delete();

		//On supprime le filtre sauvegardé de cette liste
        $filtres_enregistres = Rapport_parametre::where('id_rapport','liste_'.$id_liste)->get();

        foreach($filtres_enregistres as $filtre_enregistre) {

            $parametres = unserialize(base64_decode($filtre_enregistre->parametres));

            if (isset($parametres['filtres'])) {
                foreach ($parametres['filtres'] as $nom_filtre => $info_filtre) {

                    if ($nom_filtre == $filtre->nom_sql) {
                        unset($parametres['filtres'][$nom_filtre]);
                    }

                }
            }

            $filtre_enregistre->parametres = base64_encode(serialize($parametres));

            $filtre_enregistre->save();

        }

		Liste_libre_management::generer_fichier_migration_liste_libre($id_liste);

        Cache_management::generation_liste_libre($id_liste);

        $filtres = Liste_libre_filtre::where('liste_libre_id', $id_liste)->orderBy('ordre')->get();

		return json_encode(array('filtres' => $filtres));
	}

	/**
	 *
	 * Enregistre les filtres appliqués sur les listes libres
	 *
	 */
	public function enregistrer_filtres_appliques($id,Request $formulaire) {

        $formulaire_filtres_appliques = serialize($formulaire->formulaire_filtres_appliques);

		if($formulaire_filtres_appliques == "null"){

			$formulaire_filtres_appliques = "[]";

		}

        $liste_libre = Liste_libre::where('id',$id)->first();

		$liste_libre->filtres_appliques = $formulaire_filtres_appliques;

		$liste_libre->save();

		// on met à jour les migrations
		Liste_libre_management::generer_fichier_migration_liste_libre($id);
        Cache_management::generation_liste_libre($id);


        return json_encode(true);
	}

	/**
	 *
	 * Ajoute ou modifie un calcul sur une liste libre donnée
	 *
	 */
	public function calcul_enregistrer(Request $formulaire) {

		if(empty($formulaire->id))
			$calcul = new Liste_libre_calcul;
		else
			$calcul = Liste_libre_calcul::find($formulaire->id);

		$la_liste_libre = Liste_libre::find($formulaire->liste_libre_id);
        
		$calcul->liste_libre_id = $formulaire->input('liste_libre_id');

        if(empty($formulaire->input('nom')) && empty($calcul->index_traduction))
			return json_encode(array('retour' => traduction('messages.php.liste_libre.erreur_nom_calcul')));

        if($formulaire->has('nom'))
		    $calcul->nom = $formulaire->input('nom');

		$calcul->type_element = $formulaire->input('type_element');
		$calcul->champ_de_liaison = $formulaire->input('champ_de_liaison');
		$calcul->type_element_split = $formulaire->input('type_element_split');
		$calcul->nom_sql = $formulaire->input('nom_sql');
		$calcul->type_calcul = $formulaire->input('type_calcul');
		$calcul->unite = $formulaire->input('unite');
		$calcul->split = $formulaire->input('split');
        $calcul->afficher_somme = $formulaire->input('afficher_somme');
        $calcul->toujours_deploye = $formulaire->input('toujours_deploye');
		$calcul->top = $formulaire->input('top');
		$calcul->v_if = $formulaire->input('v_if');
        $calcul->ordre = intval($formulaire->input('ordre'));
        $calcul->taille = intval($formulaire->input('taille'));
        $calcul->icone = $formulaire->input('icone');
        $calcul->champ_reference = $formulaire->input('champ_reference') ?? null;

        $nom_calcul = strtolower(retraite_caracteres_speciaux($calcul->nom, '_'));

        $base_index_traduction = array(
            !empty($la_liste_libre->id_rapport) ? 'rapport' : 'liste',
            !empty($la_liste_libre->id_rapport) ? $la_liste_libre->id_rapport : $la_liste_libre->type_element,
            'calcul',
            $nom_calcul
        );

        if(empty($calcul->index_traduction)){

            $calcul->index_traduction = service('traduction')->calcul_index_traduction(
                6,
                $base_index_traduction,
                array(
                    'nom' => $calcul->nom
                )
            );
        }

		$calcul->save();

		// On génère la migration spécifique
		Liste_libre_management::generer_fichier_migration_liste_libre($formulaire->liste_libre_id);

        Cache_management::generation_liste_libre($formulaire->liste_libre_id);

        $calculs = Liste_libre_calcul::where('liste_libre_id', $formulaire->input('liste_libre_id'))->orderBy('ordre')->get();

		return json_encode(array('retour' => true, 'calculs' => $calculs));
    }

    /**
	 *
	 * Récupère les détails d'un calcul
	 *
	 */
    public function calcul_recuperer($id_calcul) {

		$calcul = Liste_libre_calcul::where('id', $id_calcul)->first();

		if(!empty($calcul->profils))
			$calcul->profils = json_decode($calcul->profils);
		else
			$calcul->profils = [];

		$type_element = !empty($calcul->type_element) ? $calcul->type_element : '';
		$champ_de_liaison = !empty($calcul->champ_de_liaison) ? $calcul->champ_de_liaison : '';

		$calcul->table_liaison = array('type_element' => $type_element,'champ_de_liaison' => $champ_de_liaison );

		return json_encode($calcul);
    }

	/**
	 *
	 * Supprime un calcul
	 *
	 */
    public function calcul_supprimer($id) {

		$calcul = Liste_libre_calcul::find($id);
		$id_liste = $calcul->liste_libre_id;
        $calculs_a_reorganises = Liste_libre_calcul::where('liste_libre_id',$calcul->liste_libre_id)->where('ordre','>',$calcul->ordre)->get();

        foreach($calculs_a_reorganises as $calcul_a_reorganise){

            $calcul_a_reorganise->ordre --;
            $calcul_a_reorganise->save();
        }

        if(!empty($calcul->index_traduction))
            service('traduction')->supprime_index_traduction($calcul->index_traduction);

		$calcul->delete();

		Liste_libre_management::generer_fichier_migration_liste_libre($id_liste);

        Cache_management::generation_liste_libre($id_liste);

        $calculs = Liste_libre_calcul::where('liste_libre_id', $id_liste)->orderBy('ordre')->get();

		return json_encode(array('calculs' => $calculs));
    }


	/**
	 * On enregistre les couleurs
	 */
	public function enregistrer_couleurs($id_liste,Request $formulaire) {

        $couleurs = Liste_libre_couleur::where('liste_libre_id',$id_liste)->get()->keyBy('id');

        $recherches_avancees = modele('recherche_avancee')
                            ->where('type', 'listes_libres_couleur')
                            ->whereIn('id_cible',$couleurs->pluck('id')->toArray())
                            ->get()->keyBy('id');

        $la_liste_libre = Liste_libre::find($id_liste);

        if(isset($formulaire['couleurs'])) {
            foreach ($formulaire['couleurs'] as $couleur) {

                $couleur_modele = new Liste_libre_couleur();

                if (isset($couleur['id']) && isset($couleurs[$couleur['id']])) {
                    $couleur_modele = $couleurs[$couleur['id']];
                    unset($couleurs[$couleur['id']]);
                }

                $couleur_modele->couleur = $couleur['couleur'];
                $couleur_modele->liste_libre_id = $id_liste;

                $couleur_modele->save();

                if (!empty($couleur['filtres'])) {

                    $filtres = $couleur['filtres'];
                    $filtres['type_element'] = $la_liste_libre->type_element;
                    $filtres['type'] = 'listes_libres_couleur';
                    $filtres['id_cible'] = $couleur_modele->id;

                    $management = management('recherche_avancee');

                    if (isset($filtres['id']) && isset($recherches_avancees[$filtres['id']])) {
                        $management = management('recherche_avancee', $filtres['id'], $recherches_avancees[$filtres['id']]);

                        unset($recherches_avancees[$filtres['id']]);
                    }

                    if (empty($filtres['structure']) && isset($recherches_avancees[$filtres['id']]))
                        $management->supprime();
                    else
                        $management->enregistre($filtres);
                }
            }
        }

        foreach ($couleurs as $couleur) {
            $couleur->delete();
        }

        foreach ($recherches_avancees as $recherche_avancee) {
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

		// On crée le fichier de migration spécifique
		Liste_libre_management::generer_fichier_migration_liste_libre($id_liste);

		return json_encode(array("retour" => true));
	}

	/**
	 *
	 *
	 * on ajoute ou modifie une autre vue
	 *
	 *
	 */
	public function enregistrer_autrevue($id, Request $formulaire) {

		// Premier lien
		$Liste_libre_autresvues1 = new Liste_libre_autresvues;

		$Liste_libre_autresvues1->liste_libre_id_1 = $formulaire->liste_libre_id;
		$Liste_libre_autresvues1->liste_libre_id_2 = $formulaire->liste_libre_2;

		$Liste_libre_autresvues1->save();

		// Second lien
		$Liste_libre_autresvues2 = new Liste_libre_autresvues;

		$Liste_libre_autresvues2->liste_libre_id_1 = $formulaire->liste_libre_2;
		$Liste_libre_autresvues2->liste_libre_id_2 = $formulaire->liste_libre_id;

		$Liste_libre_autresvues2->save();

		// On crée le fichier de migration spécifique
		Liste_libre_management::generer_fichier_migration_autresvues();

        Cache_management::generation_liste_libre($formulaire->liste_libre_id);

        // On retournes toutes les autres vues en base
		$autresvues = Liste_libre_autresvues::where('liste_libre_id_1', $formulaire->liste_libre_id)->get();

		// On ajoute le type element qui est lié dans $autresvues
		if(!$autresvues->isEmpty()){

			foreach ($autresvues as $autrevue) {

				$autre_liste_type_element = Liste_libre::where('id',$autrevue->liste_libre_id_2)->first();
				$autrevue->type_element_2 = $autre_liste_type_element->type_element;
				$autrevue->id_rapport = $autre_liste_type_element->id_rapport;
			}
		}


		return json_encode(array("retour" => true, "autresvues" => $autresvues));
	}

	/**
	 * on supprime une autre vue
	 *
	 *
	 */
	public function supprimer_autrevue($id) {

		// On supprime la vue ainsi que sa réciproque
		$autrevue_a_supprimer = Liste_libre_autresvues::where('id',$id)->first();

		$liste_1 = $autrevue_a_supprimer->liste_libre_id_1;
		$liste_2 = $autrevue_a_supprimer->liste_libre_id_2;

		$autrevue_a_supprimer_2 = Liste_libre_autresvues::where('liste_libre_id_1',$liste_2)->where('liste_libre_id_2',$liste_1)->first();

		$autrevue_a_supprimer->delete();
		$autrevue_a_supprimer_2->delete();

		// On crée le fichier de migration spécifique
		Liste_libre_management::generer_fichier_migration_autresvues();

        Cache_management::generation_liste_libre($autrevue_a_supprimer->liste_libre_id_1);

        return json_encode(true);
	}

	/**
	 *
	 * On enregistre les autres paramètres de la liste
	 *
	 */
	public function enregistrer_autres_parametres($id) {

		$liste_libre = Liste_libre::find($id);

        $liste_libre->tri_kanban = isset(request()->tri_kanban) ? json_encode(request()->tri_kanban) : null;
		$liste_libre->desactiver_filtres = request()->desactiver_filtres;
		$liste_libre->desactiver_recherche_avancee = request()->desactiver_recherche_avancee;
		$liste_libre->desactiver_recherche = request()->desactiver_recherche;
		$liste_libre->desactiver_actions = request()->desactiver_actions;
		$liste_libre->desactiver_options = request()->desactiver_options;
		$liste_libre->desactiver_export = request()->desactiver_export;
		$liste_libre->desactiver_creation = request()->desactiver_creation;
		$liste_libre->condition_desactiver_creation = request()->condition_desactiver_creation;
		$liste_libre->desactiver_drag_drop_kanban = request()->desactiver_drag_drop_kanban;
		$liste_libre->formulaire_libre = request()->formulaire_libre;
		$liste_libre->desactiver_kanban_sans_valeur = request()->desactiver_kanban_sans_valeur;
        $liste_libre->affichage_kanban_vertical = request()->affichage_kanban_vertical;

        // le tri kanban statique ne sert que pour l'affichage classique ( pas de tri possible en vertical, il ne faut donc pas qu'il reste configuré )
        if($liste_libre->affichage_kanban_vertical == 1)
            $liste_libre->tri_kanban = null;
        $liste_libre->desactiver_options_individuelle = request()->desactiver_options_individuelle;
        $liste_libre->options_mobile = request()->options_mobile;
        $liste_libre->desactiver_actions_individuelle = request()->desactiver_actions_individuelle;
        $liste_libre->lignes_par_page = request()->lignes_par_page;
        $liste_libre->creation_taches_en_masse = request()->creation_taches_en_masse;
        $liste_libre->formulaire_modale = request()->formulaire_modale;
        $liste_libre->afficher_images = request()->afficher_images;
		$liste_libre->modele_email_defaut = request()->modele_email_defaut;
        $liste_libre->afficher_calculs_haut_liste = request()->afficher_calculs_haut_liste;

		$liste_libre->save();

		// On génère la migration spécifique
		Liste_libre_management::generer_fichier_migration_liste_libre($id);

        Cache_management::generation_liste_libre($id);


        return json_encode(array('retour' => true));
	}

	/**
	 *
	 * On enregistre les filtres de recherche avancée de la liste
	 *
	 */
	public function enregistrer_filtres_recherche_avancee($id) {

		$liste_libre = Liste_libre::find($id);

		$liste_libre->recherche_avancee = json_encode(request()->filtres);

		$liste_libre->save();

		// On génère la migration spécifique
		// Liste_libre_management::generer_fichier_migration_liste_libre($id);

        Cache_management::generation_liste_libre($id);


        return json_encode(array('retour' => true));
	}

    public function ajout_champs_element_liaison($champ_libre,&$index_a_remplacer){

        if ($champ_libre->type == 0) {

            $index_a_remplacer['1) Textes'][] = $champ_libre;
        } elseif (in_array($champ_libre->type, array(1, 20))) {

            $index_a_remplacer['2) Listes'][] = $champ_libre;
        } elseif (in_array($champ_libre->type, array(2, 3))) {

            $index_a_remplacer['3) Nombres'][] = $champ_libre;
        } elseif (in_array($champ_libre->type, array(4, 5))) {

            $index_a_remplacer['4) Dates'][] = $champ_libre;
        } else {

            $index_a_remplacer['5) Autres'][] = $champ_libre;
        }
    }

	public function recupere_champs_libres_pour_select($type_element){

        $champs_libres = Champ_libre::where(function($where){
             $where->where('champ_systeme',0)->orWhereNull('champ_systeme');
         })->get()->groupBy('type_element');

        $champs_libres_select = Champ_libre_management::filtrage($type_element, $champs_libres);

		$champs_dynamiques = $champs_libres[$type_element]->where('type',22);

		foreach($champs_dynamiques as $champ_dynamique){

			$champ_type_element = $champs_libres[$type_element]->where('nom_sql',$champ_dynamique->contenu)->first();

			if(empty($champ_type_element))
				continue;

			$types_elements = collect(json_decode($champ_type_element->contenu))->where('valeur',true)->pluck('type_element')->toArray();

			foreach($types_elements as $type_element){

				$champs_libres_liaisons = $champs_libres[$type_element] ?? [];

				foreach($champs_libres_liaisons as $champ_libre){
					$champ_libre->index_traduction_type_element = 'tables_libres.'.$champ_libre->type_element.'.nom_table';
				}

				$champs_libres_select[] = array(
					'type_element' => $type_element,
					'champs_libres' => $champs_libres_liaisons,
					'champ_liaison' => $champ_dynamique->nom_sql.'|'.$type_element,
					'index_traduction' => 'tables_libres.'.$type_element.'.nom_table',
				);
			}
		}

        return $champs_libres_select;
	}
}
