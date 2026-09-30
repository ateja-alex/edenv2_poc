<?php

namespace App\Eden\Controllers\Parametrage;

use App\Eden\Models\Formulaire_valeur_par_defaut;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_filtre_enregistre;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Colonne;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Utilisateur;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Champs\Champ_multi_selection;
use App\Eden\Managements\Maintenance_management;

use Illuminate\Http\Request;

class Formulaires_controller extends Controller {

	/**
	 *
	 * Affichage des formulaires libres
	 *
	 */
	public function index() {

		$les_formulaires = Formulaire::orderBy('nom_formulaire')->get();

		$les_type_pour_nom_formulaire = Table_libre::get(['type_element']);

		$array_nom_formulaire = array();

		// On crée la liste des nom de formulaires possibles
		foreach ($les_type_pour_nom_formulaire as $type_element) {

			$array_nom_formulaire[] = $type_element['type_element'];

			$array_nom_formulaire[] = 'fiche_'.$type_element['type_element'];

			$array_nom_formulaire[] = 'creation_volee_'.$type_element['type_element'];

			$array_nom_formulaire[] = 'chronometre_'.$type_element['type_element'];
		}


		// On évite les doublons de formulaires
		foreach ($les_formulaires as $formulaire) {

			if (in_array($formulaire['nom_formulaire'], $array_nom_formulaire)) {

				$index_a_supprimer = array_search($formulaire['nom_formulaire'], $array_nom_formulaire);
				unset($array_nom_formulaire[$index_a_supprimer]);
			}

		}

		return view('eden::parametrage.formulaires_libres', ['les_formulaires' => $les_formulaires,
			'array_nom_formulaire' => $array_nom_formulaire
		]);
	}

	/**
	 *
	 * on affiche le parametrage des formulaires
	 *
	 */
	public function parametrage_formulaire_index($nom_formulaire) {

		$le_formulaire = Formulaire::where('nom_formulaire', $nom_formulaire)->first();

		// Cas où le formulaire n'existe pas
		if ($le_formulaire == null) {

			return view('eden::parametrage.formulaires_libre_ajout_inexistant', array(

				'type_element' => $nom_formulaire
			));
		}

		$type_element = $le_formulaire->nom_formulaire;

		if(substr($nom_formulaire, 0, 6) == 'fiche_') {

			$type_element = substr($nom_formulaire, 6);
		}
		elseif(substr($nom_formulaire, 0, 15) == 'creation_volee_') {

			$type_element = substr($nom_formulaire, 15);
		}
        elseif(substr($nom_formulaire, 0, 12) == 'chronometre_') {

			$type_element = substr($nom_formulaire, 12);
		}
		elseif(substr($nom_formulaire, 0, 26) == 'email_facture_fournisseur_') {

			$type_element = substr($nom_formulaire, 26);
		}

		$les_champs = Formulaires_champs::where('nom_formulaire', $nom_formulaire)->orderBy('ordre')->get();

        $champs_libres_par_nom_sql = champs_libres($type_element)->keyBy('nom_sql');

		if(!empty($les_champs)) {

			foreach($les_champs as $champ) {

				$nom_sql = $champ['nom_sql'];
				$champ_libre = $champs_libres_par_nom_sql[$nom_sql];
				$type = $champ_libre['type'];
				$champ['type'] = $type;
			}
		}

		$les_champs_libres = Champ_libre::where('type_element', $type_element)->orderBy('nom')->get();

		return view('eden::parametrage.formulaire_modification', array(

			'les_champs' => $les_champs,
			'type_element' => $type_element,
			'nom_formulaire' =>$nom_formulaire,
			'champs_libres' => $les_champs_libres,
			'formulaire' => $le_formulaire,
		));
	}

	/**
	 *
	 * on affiche le parametrage des formulaires
	 *
	 */
	public function parametrage_formulaire_index_v2($nom_formulaire) {

		$le_formulaire = Formulaire::where('nom_formulaire', $nom_formulaire)->first();

		// Cas où le formulaire n'existe pas
		if ($le_formulaire == null) {

            $type_formulaire = strpos($nom_formulaire,'creation_volee_') !== false ? 'creation_volee' :
                (strpos($nom_formulaire,'extranet_') !== false ? 'extranet' :
                    (strpos($nom_formulaire,'intranet_') !== false ? 'intranet' :
                        (strpos($nom_formulaire,'fiche_') !== false ? 'fiche' :
                            (strpos($nom_formulaire,'chronometre_') !== false ? 'chronometre' : null))));

			return view('eden::parametrage.formulaires_libre_ajout_inexistant', array(

				'type_element' => $nom_formulaire,
				'type_formulaire' => $type_formulaire
			));
		}

		$type_element = $le_formulaire->nom_formulaire;

		if(!empty($le_formulaire->type_element))
			$type_element = $le_formulaire->type_element;


		if(substr($nom_formulaire, 0, 6) == 'fiche_') {

			$type_element = substr($nom_formulaire, 6);
		}
		elseif(substr($nom_formulaire, 0, 15) == 'creation_volee_') {

			$type_element = substr($nom_formulaire, 15);
		}
        elseif(substr($nom_formulaire, 0, 12) == 'chronometre_') {

			$type_element = substr($nom_formulaire, 12);
		}
		elseif(substr($nom_formulaire, 0, 26) == 'email_facture_fournisseur_') {

			$type_element = substr($nom_formulaire, 26);
		}

		$les_champs = Formulaires_champs::select('eden_formulaireslibres_champs.*', 'eden_sous_formulaire.nom_sous_formulaire', 'eden_sous_formulaire.id as id_sous_formulaire')
                                            ->where('eden_formulaireslibres_champs.nom_formulaire', $nom_formulaire)
                                            ->leftJoin('eden_sous_formulaire', function ($join) {
                                                $join->on('eden_formulaireslibres_champs.nom_sous_formulaire', '=', 'eden_sous_formulaire.nom_sous_formulaire')
                                                    ->where(function ($requete){
                                                        $requete->where('eden_sous_formulaire.inactif', 0)
                                                        ->orWhereNull('eden_sous_formulaire.inactif');
                                                    });
                                            })
                                            ->orderBy('ordre')
                                            ->get();

		if(!empty($les_champs)) {

            $champs_libres_par_nom_sql = champs_libres($type_element)->keyBy('nom_sql');

			foreach($les_champs as $champ) {

				$nom_sql = $champ['nom_sql'];

				if ($champ['type_champ'] == 0) {

					$champ_libre = $champs_libres_par_nom_sql[$nom_sql];
					$type = $champ_libre['type'];
					$champ['type'] = $type;

                    // On récupère son nom
                    $le_champ = $champs_libres_par_nom_sql[$nom_sql];
                    $champ['nom'] = $le_champ['nom'];
				}

				$total_taille = $champ['taille_avant']+$champ['taille_apres']+$champ['taille_libelle']+$champ['taille_champ'];

				if($total_taille > 12 || empty($total_taille))
					$total_taille =12;

                // Calcul des pourcentages
                $champ['pourcentage_avant'] = round(($champ['taille_avant'] * 100) / $total_taille, 2);
                $champ['pourcentage_apres'] = round(($champ['taille_apres'] * 100) / $total_taille, 2);
                $champ['pourcentage_libelle'] = round(($champ['taille_libelle'] * 100) / $total_taille, 2);
                $champ['pourcentage_champ'] = round(($champ['taille_champ'] * 100) / $total_taille, 2);
                $champ['taille_total'] = $total_taille;

                if($champ['condition_affichage_v_if'] == null){
                    $champ['condition_affichage_v_if'] = '1';
                }

			}
		}

        $valeurs_par_defaut = Formulaire_valeur_par_defaut::where('nom_formulaire',$nom_formulaire)
            ->get();

        $les_champs_libres = champs_libres($type_element);
        
        $les_champs_libres = $les_champs_libres->sortBy(function($champ) {
            return strtolower(traduction($champ->index_traduction . '.nom'));
        })->values();
        
        $champs_valeurs_par_defaut = [];

        foreach($les_champs_libres as $champ_libre){

            $champs_valeurs_par_defaut[] = [
                'nom_sql' => $champ_libre->nom_sql,
                'management' => management($type_element)->champ($champ_libre->nom_sql)
            ];
        }

        if($le_formulaire->type_formulaire == 'web') {

            $id_liste_domaine = Liste_libre::where('type_element','formulaire_web_domaine')->first()->id ?? null;

            return view('eden::parametrage.formulaire_web', [
                'les_champs' => $les_champs,
                'type_element' => $type_element,
                'valeurs_par_defaut' => $valeurs_par_defaut,
                'nom_formulaire' => $nom_formulaire,
                'champs_libres' => $les_champs_libres->values(),
                'formulaire' => $le_formulaire,
                'champs_valeurs_par_defaut' => $champs_valeurs_par_defaut,
                'id_liste_domaine' => $id_liste_domaine,
            ]);

        }

		// On va chercher les profils
		$utilisateurs = modele('profil')->get();

		//On récupére les vues ajoutables
        $vues_ajoutables = $this->recuperer_vues_ajoutables($nom_formulaire, $type_element);

        $type_elements_pour_sous_formulaire = Champ_libre::where('type',42)
            ->where('type_element_ajax', $type_element)
            ->groupBy('type_element')
            ->get()
            ->pluck('nom_sql', 'type_element');

        $existe_en_dur = view()->exists('eden::formulaires.'.str_replace(['fiche_', 'extranet_', 'chronometre_'], ['fiche.', 'extranet.', 'chronometre.'], $type_element));

		return view('eden::parametrage.formulaire_modification_v2', array(

			'les_champs' => $les_champs,
			'type_element' => $type_element,
			'nom_formulaire' =>$nom_formulaire,
			'champs_libres' => $les_champs_libres->values(),
			'formulaire' => $le_formulaire,
			'utilisateurs' => $utilisateurs,
            'vues_ajoutables' => $vues_ajoutables,
            'existe_en_dur' => $existe_en_dur,
            'type_elements_pour_sous_formulaire' => $type_elements_pour_sous_formulaire,
            'valeurs_par_defaut' => $valeurs_par_defaut,
            'champs_valeurs_par_defaut' => $champs_valeurs_par_defaut,
		));
	}

    /*
     *
     * Récupère les vues que l'on peut ajouter au formulaire du type d'élément passé en paramètre
     *
     */
    private function recuperer_vues_ajoutables($nom_formulaire, $type_element){

        $vues_ajoutables = [];
        $chemins = array();

        if(in_array($type_element, Variables::$documents_gescom)){

            $type_document = in_array($type_element, Variables::$documents_vente_gescom) ? 'vente' : 'achat';

            $chemins[app_path('Eden/Views/formulaires/document')] = 'standard';
            $chemins[resource_path('views/vendor/eden/formulaires/document')] = 'specifique';

            $chemins[app_path('Eden/Views/formulaires/document/'.$type_document)] = 'standard';
            $chemins[resource_path('views/vendor/eden/formulaires/document/'.$type_document)] = 'specifique';
        }

        $chemins[app_path('Eden/Views/formulaires/'.$type_element)] = 'standard';
        $chemins[resource_path('views/vendor/eden/formulaires/'.$type_element)] = 'specifique';

        foreach($chemins as $chemin => $type) {

            if (!is_dir($chemin))
                continue;

            $repertoire = scandir($chemin);

            foreach ($repertoire as $fichier) {

                if ($fichier == '.' || $fichier == '..'|| is_dir($chemin . '/' . $fichier))
                    continue;

                $fichier = str_replace('.blade.php', '', $fichier);

                $champ_vue = Formulaires_champs::where('nom_formulaire', $nom_formulaire)->where('nom_vue', $fichier)->where('type_vue', $type)->first();

                if ($champ_vue != null)
                    continue;

                $nom_lisible = str_replace('_', ' ', $fichier);
                $nom_lisible = ucfirst($nom_lisible);

                $vues_ajoutables[$fichier] = array('nom_fichier' => $fichier, 'nom_lisible' => $nom_lisible, 'type_vue' => $type);
            }
        }

        return array_values($vues_ajoutables);
    }
	/**
	 *
	 * Enregistrement du paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire(Request $formulaire) {

		$nouveau_champ = new Formulaires_champs;

		$champ_avec_plus_grand_ordre = Formulaires_champs::where('nom_formulaire',$formulaire->nom_formulaire)->orderBy('ordre', 'desc')->first();

		if ($champ_avec_plus_grand_ordre == null)
			$champ_avec_plus_grand_ordre['ordre'] = 0;

		$nouveau_champ->nom_formulaire = $formulaire->nom_formulaire;
		$nouveau_champ->type_element = $formulaire->type_element;
		$nouveau_champ->nom_sql = $formulaire->nom_sql;
		$nouveau_champ->ordre = $champ_avec_plus_grand_ordre['ordre'] + 1;
        $nouveau_champ->condition_affichage_v_if = '1';

		if($formulaire->type_champ == 1){

			$nouveau_champ->valeur_html = $formulaire->valeur_html;
			$nouveau_champ->id_editeur = $formulaire->id_editeur;
			$nouveau_champ->type_champ = 1;
			$nouveau_champ->taille_avant = $formulaire->taille_avant;
			$nouveau_champ->taille_champ = $formulaire->taille_champ;
			$nouveau_champ->taille_apres = $formulaire->taille_apres;
			$nouveau_champ->taille_libelle = $formulaire->taille_libelle;

		}
		else if($formulaire->type_champ == 0){

			$nouveau_champ->taille_avant = $formulaire->taille_avant;
			$nouveau_champ->taille_libelle = $formulaire->taille_libelle;
			$nouveau_champ->taille_champ = $formulaire->taille_champ;
			$nouveau_champ->taille_apres = $formulaire->taille_apres;
            $nouveau_champ->type_champ = 0;
		}

		else{

            $nouveau_champ->nom_vue = $formulaire->nom_vue;
            $nouveau_champ->type_champ = 2;
            $nouveau_champ->type_vue = $formulaire->type_vue;
            $nouveau_champ->taille_avant = $formulaire->taille_avant;
            $nouveau_champ->taille_champ = $formulaire->taille_champ;
            $nouveau_champ->taille_apres = $formulaire->taille_apres;
            $nouveau_champ->taille_libelle = $formulaire->taille_libelle;

        }

		$nouveau_champ->save();

        $nouveau_champ->refresh();


		$total_taille = $nouveau_champ->taille_avant+$nouveau_champ->taille_apres+$nouveau_champ->taille_libelle+$nouveau_champ->taille_champ;

		if ($total_taille > 12)
			$total_taille =12;

        $le_formulaire = Formulaire::where('nom_formulaire',$formulaire->nom_formulaire)->first();

		if($nouveau_champ->type_champ != 2 && $le_formulaire->type_formulaire != 'web') {
            // Calcul des pourcentage
            $nouveau_champ->pourcentage_avant = round(($nouveau_champ->taille_avant * 100) / $total_taille, 2);
            $nouveau_champ->pourcentage_apres = round(($nouveau_champ->taille_apres * 100) / $total_taille, 2);
            $nouveau_champ->pourcentage_libelle = round(($nouveau_champ->taille_libelle * 100) / $total_taille, 2);
            $nouveau_champ->pourcentage_champ = round(($nouveau_champ->taille_champ * 100) / $total_taille, 2);
            $nouveau_champ->taille_total = $total_taille;
        }

		if ($nouveau_champ->type_champ == 0) {

			// On récupère son nom
			$le_champ = Champ_libre::where('type_element', $nouveau_champ->type_element)->where('nom_sql', $nouveau_champ->nom_sql)->first();
			$nouveau_champ->nom = $le_champ['nom'];
		}

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('nom_formulaire', $formulaire->nom_formulaire)->first();
		Maintenance_management::generer_fichier_migration_formulaire($le_formulaire['nom_formulaire']);

        if(!empty($formulaire->type_element))
            $type_element = $formulaire->type_element;
        else
		    $type_element = $formulaire->nom_formulaire;

        $this->vider_cache_formulaire($type_element);

        return response()->json(array('retour' => true,'nouveau_champ' => $nouveau_champ));
	}

	/**
	 *
	 * Enregistre un champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_enregistre_un_champ_v2(Request $formulaire) {

        $champ = $formulaire->champ;

        if($champ['type_champ'] == 2)
            $champ_libre = Formulaires_champs::where('nom_formulaire', $champ['nom_formulaire'])->where('nom_vue', $champ['nom_vue'])->first();
        else if($champ['type_champ'] == 3)
            $champ_libre = Formulaires_champs::where('nom_formulaire', $champ['nom_formulaire'])->where('nom_sous_formulaire', $champ['nom_sous_formulaire'])->first();
        else
            $champ_libre = Formulaires_champs::where('nom_formulaire', $champ['nom_formulaire'])->where('nom_sql', $champ['nom_sql'])->first();

        if($champ_libre->type_champ != 3){
            $champ_libre->taille_champ = $champ['taille_champ'];
            $champ_libre->taille_avant = $champ['taille_avant'];
            $champ_libre->taille_apres = $champ['taille_apres'];
        }

        $champ_libre->condition_affichage_v_if = $champ['condition_affichage_v_if'] ?? null;
        $champ_libre->condition_affichage_en_v_show = $champ['condition_affichage_en_v_show'] ?? 0;
        $champ_libre->condition_obligatoire = $champ['condition_obligatoire'] ?? null;
        $champ_libre->condition_lecture_seule = $champ['condition_lecture_seule'] ?? null;

        if(isset($champ['profils']))
            $champ_libre->profils = $champ['profils'];

        // Meme methode pour champ et HTML
        if($champ_libre->type_champ == 0) {

            $champ_libre->taille_libelle = $champ['taille_libelle'];
        }
        else{

            $champ_libre->valeur_html = $champ['valeur_html'];
            $champ_libre->id_editeur = $champ['id_editeur'];
        }

        $champ_libre->save();

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($champ['nom_formulaire']);

        $type_element = $champ['type_element'];

        $this->vider_cache_formulaire($type_element);

		return response()->json(array('retour' => true));
	}


	/**
	 *
	 * Modifier ordre champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_modifier_taille_libelle(Request $formulaire) {
		$champ_libre = Formulaires_champs::where('nom_formulaire', $formulaire->nom_formulaire)->where('nom_sql', $formulaire->nom_sql)->first();

		$champ_libre->taille_libelle = $formulaire->taille_libelle;
		$champ_libre->save();

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($formulaire->nom_formulaire);

        if(!empty($formulaire->type_element))
            $type_element = $formulaire->type_element;
        else
		    $type_element = $formulaire->nom_formulaire;

        $this->vider_cache_formulaire($type_element);
	}

	/**
	 *
	 * Modifier ordre champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_modifier_taille_champ(Request $formulaire) {
		$champ_libre = Formulaires_champs::where('nom_formulaire', $formulaire->nom_formulaire)->where('nom_sql', $formulaire->nom_sql)->first();

		$champ_libre->taille_champ = $formulaire->taille_champ;
		$champ_libre->save();

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($formulaire->nom_formulaire);

        if(!empty($formulaire->type_element))
            $type_element = $formulaire->type_element;
        else
		    $type_element = $formulaire->nom_formulaire;

        $this->vider_cache_formulaire($type_element);
	}

	/**
	 *
	 * Modifier ordre champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_modifier_ordre_champ(Request $formulaire) {

        $formulaire = $formulaire->all();

		$champs_libres = Formulaires_champs::where('nom_formulaire', $formulaire['nom_formulaire'])
            ->get();

        foreach($champs_libres as $champ_libre){

            if(isset($formulaire['ordres'][$champ_libre->id])) {
                $champ_libre->ordre = $formulaire['ordres'][$champ_libre->id];
                $champ_libre->save();
            }
        }

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($formulaire['nom_formulaire']);

        if(!empty($formulaire['type_element']))
            $type_element = $formulaire['type_element'];
        else
		    $type_element = $formulaire['nom_formulaire'];

        $this->vider_cache_formulaire($type_element);

	}



	/**
	 *
	 * Récupères les options du select voulu
	 *
	 */
	public function recuperer_options_ajax(Request $infos) {

		$nom_sql = $infos->nom_sql;
		$type_element = $infos->type_element;

		$les_options = '';

		$liste_options =  management($type_element)->champ($nom_sql)->valeurs_possibles;

		return response()->json($liste_options);
	}


	public function ajouter_conditions_ajax(Request $formulaire){
		$type_element = $formulaire->type_element;
		$nom_sql_champ_libre = $formulaire->nom_sql_champ_libre;
		$les_conditions = $formulaire->les_conditions;

		$champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql_champ_libre)->first();


		$champ_libre->conditions_apparitions_formulaire = json_encode($les_conditions);
		$champ_libre->save();
	}


	/*
	*
	*
	* on supprime le champ du paramétrage de formulaire
	*
	*
	*/
	public function parametrage_formulaire_supprimer(Request $formulaire) {

	    $nouvelle_vue_selectionnable = null;

	    if(isset($formulaire->champ['type_champ']) && $formulaire->champ['type_champ'] ==2) {

	        $fichier = $formulaire->champ['nom_vue'];
            $nom_lisible = str_replace('.', ' ', $fichier);
            $nom_lisible = str_replace('_', ' ', $nom_lisible);
            $nom_lisible = ucfirst($nom_lisible);

            $nouvelle_vue_selectionnable = array('nom_fichier' => $fichier,'nom_lisible' => $nom_lisible,'type_vue' => $formulaire->champ['type_vue']);

        }
		// Check si c'est id dans les data
		if (isset($formulaire->champ['id']))
			$le_champ = Formulaires_champs::where('id', $formulaire->champ['id'])->first();

		else
			$le_champ = Formulaires_champs::where('type_element', $formulaire->champ['type_element'])->where('nom_formulaire',$formulaire->champ['nom_formulaire'])->where('nom_sql',$formulaire->champ['nom_sql'])->first();

		$ordre_champ_supprime = $le_champ->ordre;
		$nom_formulaire = $le_champ->nom_formulaire;

		if($le_champ->type_champ == 3 && isset($le_champ->nom_sous_formulaire)){

            $sous_formulaires = modele('eden_sous_formulaire')->where('nom_sous_formulaire', $le_champ->nom_sous_formulaire)->get();

            if(!empty($sous_formulaires)) {

                foreach ($sous_formulaires as $sous_formulaire) {
                    management('eden_sous_formulaire', $sous_formulaire->id)->supprime();
                }

            }

        }

		$le_champ->delete();

		$champs_a_modifier_ordre = Formulaires_champs::where('ordre','>',$ordre_champ_supprime)->where('nom_formulaire', $nom_formulaire)->get();

        $formulaires_champs = Formulaires_champs::get()->keyBy('id');

		foreach ($champs_a_modifier_ordre as $champ) {

			$le_champ_a_modif = $formulaires_champs[$champ['id']];

			$le_champ_a_modif->ordre = $champ->ordre - 1;
			$le_champ_a_modif->save();
		}

        $type_element = $le_champ->type_element;

        $champs_libres = champs_libres($type_element)->keyBy('nom_sql');

		$les_champs_actualisees = Formulaires_champs::where('nom_formulaire', $nom_formulaire)->orderBy('ordre')->get();

		if(!empty($les_champs_actualisees)) {

			foreach($les_champs_actualisees as $champ) {

				$nom_sql = $champ['nom_sql'];
				if ($champ['type_champ'] == 0) {
					$champ_libre = $champs_libres[$nom_sql];
					$type = $champ_libre['type'];
					$champ['type'] = $type;
				}

				$total_taille = $champ['taille_avant']+$champ['taille_apres']+$champ['taille_libelle']+$champ['taille_champ'];

				if ($total_taille > 12)
					$total_taille =12;

				if($champ['type_champ'] != 2 && $champ['type_champ'] != 3) {

                    // Calcul des pourcentage
                    $champ['pourcentage_avant'] = round(($champ['taille_avant'] * 100) / $total_taille, 2);
                    $champ['pourcentage_apres'] = round(($champ['taille_apres'] * 100) / $total_taille, 2);
                    $champ['pourcentage_libelle'] = round(($champ['taille_libelle'] * 100) / $total_taille, 2);
                    $champ['pourcentage_champ'] = round(($champ['taille_champ'] * 100) / $total_taille, 2);
                    $champ['taille_total'] = $total_taille;

                }

				if ($champ['type_champ'] == 0) {

					// On récupère son nom
					$le_champ = $champs_libres[$nom_sql];
					$champ['nom'] = $le_champ['nom'];
				}
			}
		}

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($nom_formulaire);

        $this->vider_cache_formulaire($type_element);

		return json_encode(array("retour" => true,'les_champs' => $les_champs_actualisees,"nouvelle_vue_selectionnable" => $nouvelle_vue_selectionnable));
	}





	/*
	*
	*
	* Supprimer un formulaire libre
	*
	*
	*/
	public function listes_formulaires_supprimer($id_formulaire) {

		$le_formulaire = Formulaire::where('id',$id_formulaire)->first();

		// On supprimer les champs liés a ce formulaire
		$les_champs = Formulaires_champs::where('nom_formulaire', $le_formulaire->nom_formulaire)->get();

		foreach ($les_champs as $champ) {

			$champ->delete();
		}

        if(!empty($le_formulaire->index_traduction))
            service('traduction')->supprime_index_traduction($le_formulaire->index_traduction);

				$le_formulaire->delete();

				// On supprime le fichier de migration spécifique
				$fichier_formulaire = app_path('/Migrations/Formulaires_libres/'.$le_formulaire->nom_formulaire.'.php');
				if(is_file($fichier_formulaire)) {

					$suppression = unlink($fichier_formulaire);

					if (!$suppression)
						return response()->json(traduction('messages.php.formulaire.fichier_echec_suppression'));
				}

        return response()->json('');
	}

	/*
	*
	*
	* Ajouter un formulaire libre
	*
	*
	*/
	public function listes_formulaires_ajouter(Request $formulaire) {

		$nouveau_formulaire = new Formulaire;

		$nouveau_formulaire->nom_formulaire = $formulaire->nom_formulaire;
		$nouveau_formulaire->vuejs_data = $formulaire->vuejs_data;
		$nouveau_formulaire->vuejs_methods = $formulaire->vuejs_methods;
		$nouveau_formulaire->surcharger_la_vue = $formulaire->surcharger_la_vue;
        $nouveau_formulaire->index_traduction = service('traduction')->calcul_index_traduction(
            12,
            array(
                'formulaire',
                $nouveau_formulaire->nom_formulaire
            ),
            array(
                'titre' => ucfirst(table_libre(str_replace(array('fiche_','creation_volee_','extranet_'),'',$nouveau_formulaire->nom_formulaire))->nom_table),
            )
        );
		$nouveau_formulaire->save();

		$type_element = $nouveau_formulaire->nom_formulaire;

        $this->vider_cache_formulaire($type_element);

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($formulaire->nom_formulaire);

		return redirect()->route('parametrage.formulaire.index', [$formulaire->nom_formulaire]);
	}

	/*
	*
	*
	* Ajouter un formulaire libre à la volée
	*
	*
	*/
	public function listes_formulaires_ajouter_volee($type_element,$type_formulaire = null) {

		$nouveau_formulaire = new Formulaire;

		$nouveau_formulaire->nom_formulaire = $type_element;
		$nouveau_formulaire->type_formulaire = $type_formulaire;
        $nouveau_formulaire->index_traduction = service('traduction')->calcul_index_traduction(
            12,
            array(
                'formulaire',
                $nouveau_formulaire->nom_formulaire
            ),
            array(
                'titre' => ucfirst(table_libre(str_replace(array('fiche_','creation_volee_','extranet_','chronometre_'),'',$nouveau_formulaire->nom_formulaire))->nom_table),
            )
        );
		// $nouveau_formulaire->vuejs_data = $formulaire->vuejs_data;
		// $nouveau_formulaire->vuejs_methods = $formulaire->vuejs_methods;
		// $nouveau_formulaire->surcharger_la_vue = $formulaire->surcharger_la_vue;
		$nouveau_formulaire->save();

		$this->vider_cache_formulaire($type_element);

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($type_element);

		//return redirect()->route('parametrage_formulaire_index', [$type_element]);

		return redirect()->route('parametrage.formulaire.index', [$type_element]);
	}

	public function creer_formulaire_via_parametrage_intranet($type_element, $nom_formulaire){

		$nouveau_formulaire = $this->creation_formulaire($nom_formulaire, $type_element, null);

		return response()->json($nouveau_formulaire);
	}

	/**
	 *
	 * Crée un formulaire paramétrable pour les fiches
	 *
	 */
	public function listes_formulaires_ajouter_volee_parametrable($nom_formulaire, $type_element, $type_formulaire = null) {

		$this->creation_formulaire($nom_formulaire, $type_element, $type_formulaire);

		return redirect()->route('parametrage.formulaire.index', [$nom_formulaire]);
	}

	public function creation_formulaire($nom_formulaire, $type_element, $type_formulaire){
		$nouveau_formulaire = new Formulaire;

		$nouveau_formulaire->nom_formulaire = $nom_formulaire;
		$nouveau_formulaire->type_element = $type_element;
		$nouveau_formulaire->surcharger_la_vue = 1;
		$nouveau_formulaire->type_formulaire = $type_formulaire;
        $nouveau_formulaire->index_traduction = service('traduction')->calcul_index_traduction(
            12,
            array(
                'formulaire',
                $nouveau_formulaire->nom_formulaire
            ),
            array(
                'titre' => 'Nouveau formulaire'.(!empty($type_formulaire) ? ' '.$type_formulaire : ''),
            )
        );
		// $nouveau_formulaire->vuejs_data = $formulaire->vuejs_data;
		// $nouveau_formulaire->vuejs_methods = $formulaire->vuejs_methods;
		// $nouveau_formulaire->surcharger_la_vue = $formulaire->surcharger_la_vue;
		$nouveau_formulaire->save();

		$this->vider_cache_formulaire($type_element);

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($nom_formulaire);

		return ($nouveau_formulaire);
	}

	/*
	*
	*
	* Modifier un formulaire libre
	*
	*
	*/
	public function listes_formulaires_modifier(Request $formulaire) {

		$nouveau_formulaire = Formulaire::where('id', $formulaire->id_du_formulaire)->first();

		$nouveau_formulaire->nom_formulaire = $formulaire->nom_formulaire;
		$nouveau_formulaire->vuejs_data = $formulaire->vuejs_data;
		$nouveau_formulaire->vuejs_methods = $formulaire->vuejs_methods;
		$nouveau_formulaire->surcharger_la_vue = $formulaire->surcharger_la_vue;
		$nouveau_formulaire->css_personnalise = $formulaire->css_personnalise ?? null;
		$nouveau_formulaire->champ_url_source_origine = $formulaire->champ_url_source_origine ?? null;
		$nouveau_formulaire->save();

		// On crée le fichier de migration spécifique
		Maintenance_management::generer_fichier_migration_formulaire($formulaire->nom_formulaire);

		return back();
	}

	/**
	 *
	 * Génère le nouveau fichier de migration pour les champs libres listes
	 *
	 */
	public static function generer_fichier_migration_champ_libre_liste($id_cl) {

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours'))
			return true;

		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Champs_libres_listes';

		if(!\File::isDirectory($chemin_dossier_migrations))
			\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
		if(!is_writable($chemin_dossier_migrations))
			return false;

		$les_champs_liste = Champ_libre_liste::where('id_cl',$id_cl)->get()->toArray();
		$le_champ_libre = Champ_libre::where('id_cl',$id_cl)->first();
		// On crée a le contenu du nouveau fichier de migration

		$texte =
		'<?php
		return [
		\'options\' => [
		\'nom_sql\' => \''.$le_champ_libre['nom_sql'].'\',
		\'type_element\' => \''.$le_champ_libre['type_element'].'\',';

		$texte = $texte.'
		],
		\'champs\' => [
		';
		foreach ($les_champs_liste as $le_champ_liste) {
			$texte = $texte.'"'.$le_champ_liste['valeur'].'" => [
			';
			foreach ($le_champ_liste as $clef => $valeur) {

				if ($clef == 'id_cl' || $clef == 'id_valeur')
					continue;

				$texte = $texte.'"'.$clef.'" => "'.$valeur.'",
				';
			}
			$texte = $texte.'],
			';
		}
		$texte = $texte.'],
		';
		$texte = $texte.'
	];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
	$chemin_avec_nom_document = app_path().'/Migrations/Champs_libres_listes/'.$le_champ_libre['type_element'].'_'.$le_champ_libre['nom_sql'].'.php';

		// Si le fichier existe, on le supprime
	if (file_exists($chemin_avec_nom_document) == true)
		unlink($chemin_avec_nom_document);

	    // Enregistrement du fichier
	$fichier = fopen($chemin_avec_nom_document, "x+");
	fputs($fichier, $texte );
	fclose($fichier);

	return true;

}

	/**
	 *
	 * Permet de supprimer un formulaire en base ( ainsi que ses champs ) et en migration
	 *
	 */
	public function suppression_formulaire(Request $formulaire) {

		$type_element = $formulaire->type_element;
		$nom_formulaire = $formulaire->nom_formulaire;
		$retour = "";

		// On supprime le formulaire en base
		$formulaire_base = Formulaire::where('type_element',$type_element)->where('nom_formulaire',$nom_formulaire)->first();

		if ($formulaire_base !== null) {

			$verification = $formulaire_base->delete();

			if (!$verification)
				$retour = traduction('messages.php.formulaire.probleme_suppression');
		}

		// On supprime en base les champs de ce formulaire
		$champs_formulaire = Formulaires_champs::where('type_element',$type_element)->where('nom_formulaire',$nom_formulaire)->get();
		$erreur_suppression = false;
		if ($champs_formulaire->isNotEmpty()) {

			foreach ($champs_formulaire as $champ) {

				// Suppression individuelle
				$verification = $champ->delete();

				if (!$verification)
					$erreur_suppression = true;
			}
		}

		if ($erreur_suppression)
			$retour = $retour." ".traduction('messages.php.formulaire.champ_non_supprime');


		// On supprime le fichier de migration spécifique
		$repertoire = scandir(app_path('/Migrations/Formulaires_libres'));

		foreach($repertoire as $fichier) {

			$nom_fichier = str_replace('.php', '', $fichier);

			if($fichier == '.' || $fichier == '..' || $nom_fichier != $nom_formulaire)
				continue;

			$suppression = unlink(app_path('/Migrations/Formulaires_libres/').$fichier);

			if (!$suppression)
				$retour = $retour." ".traduction('messages.php.formulaire.fichier_echec_suppression');
		}

		return response()->json($retour);
	}

    /**
     *
     * Permet de vider le cache des formulaires
     *
     */
    public function vider_cache_formulaire($type_element){

        if(session()->has('cache.sous_formulaire.'.$type_element))
            session()->forget('cache.sous_formulaire.'.$type_element);

        if(session()->has('cache.formulaire.'.$type_element))
            session()->forget('cache.formulaire.'.$type_element);

        if(session()->has('cache.formulaire_vuejs.'.$type_element))
            session()->forget('cache.formulaire_vuejs.'.$type_element);

        Cache_management::invalide();
    }

    public function enregistrer_sous_formulaire($id_formulaire, Request $sous_formulaire){

        $sous_formulaire = $sous_formulaire->post('sous_formulaire');

        $nouvelle_data = [];

        if(isset($sous_formulaire['data_vue'])){
            foreach ($sous_formulaire['data_vue'] as $data_vue) {

                $nouvelle_data[$data_vue['nom']] = $data_vue['valeur'];

            }
        }

        if(isset($sous_formulaire['nom_donnee_different']) && $sous_formulaire['nom_donnee_different'] == 0)
            $sous_formulaire['type_element_remplacement'] = null;

        if(!isset($sous_formulaire['remplacement_supplementaire']) || !is_array($sous_formulaire['remplacement_supplementaire']))
            $sous_formulaire['remplacement_supplementaire'] = [];

        $sous_formulaire['data_vue'] = json_encode($nouvelle_data);
        $sous_formulaire['remplacement_supplementaire'] = json_encode($sous_formulaire['remplacement_supplementaire']);

        $formulaire = Formulaire::where('id', $id_formulaire)->first();

        $type_element_remplacement = $sous_formulaire['type_element_enfant'];

        if(!empty($sous_formulaire['type_element_remplacement']))
            $type_element_remplacement = $sous_formulaire['type_element_remplacement'];

        $nom_sous_formulaire = $formulaire->nom_formulaire . '_sous_formulaire_' . $type_element_remplacement;

        $sous_formulaire['nom_formulaire_parent'] = $formulaire->nom_formulaire;
        $sous_formulaire['nom_sous_formulaire'] = $nom_sous_formulaire;

        if(isset($sous_formulaire['id'])) {
            $management_sous_formulaire = management('eden_sous_formulaire', $sous_formulaire['id']);
            $id_champ_sous_formulaire = $sous_formulaire['id_champ'];
            $modification = true;
        }
        else {

            $sous_formulaire_existant = modele('eden_sous_formulaire')->where('nom_sous_formulaire', $nom_sous_formulaire)->first();

            if(!empty($sous_formulaire_existant)) {
                $management_sous_formulaire = management('eden_sous_formulaire', $sous_formulaire_existant->id);
                $modification = false;
            }
            else {
                $management_sous_formulaire = management('eden_sous_formulaire');
                $modification = false;
            }

        }

        $retour = $management_sous_formulaire->enregistre($sous_formulaire);

        $nom_sous_formulaire = $management_sous_formulaire->modele->nom_sous_formulaire;

        if($retour !== true)
            return response()->json(['retour' => $retour]);

        Maintenance_management::generer_fichier_migration_sous_formulaire($nom_sous_formulaire);

        if(!empty($sous_formulaire['nom_affichage_sous_formulaire'])){

            $langue = moi()->langue;

            if(empty($langue))
                $langue = "fr";

            $index_traduction = "formulaire." . $formulaire->nom_formulaire . ".sous_formulaire." . $nom_sous_formulaire . ".titre";

            $traduction_sous_formulaire = modele('traduction_index')
                                ->select('traduction_index.*', 'traduction_valeur.*', 'traduction_valeur.id as id_traduction_valeur')
                                ->join('traduction_valeur', 'traduction_index.index', '=', 'traduction_valeur.index')
                                ->where('traduction_index.index', $index_traduction)
                                ->where('langue', $langue)
                                ->first();

            if(empty($traduction_sous_formulaire)){

                management('traduction_index')->enregistre([
                    'index' => $index_traduction,
                    'categorie' => 12
                ]);

                management('traduction_valeur')->enregistre([
                    'index' => $index_traduction,
                    'langue' => $langue,
                    'traduction_specifique' => $sous_formulaire['nom_affichage_sous_formulaire']
                ]);

            }
            else if($traduction_sous_formulaire->traduction_specifique != $sous_formulaire['nom_affichage_sous_formulaire']){

                management('traduction_valeur', $traduction_sous_formulaire->id_traduction_valeur)->enregistre(['traduction_specifique' => $sous_formulaire['nom_affichage_sous_formulaire']]);

            }

            Cache_management::genere_traductions();

        }

        if($modification === true && isset($id_champ_sous_formulaire))
            $nouveau_champ = Formulaires_champs::where('id', $id_champ_sous_formulaire)->first();
        else
            $nouveau_champ = new Formulaires_champs;

        $champ_avec_plus_grand_ordre = Formulaires_champs::where('nom_formulaire', $formulaire->nom_formulaire)->orderBy('ordre', 'desc')->first();

        if ($champ_avec_plus_grand_ordre == null)
            $champ_avec_plus_grand_ordre['ordre'] = 0;

        $nouveau_champ->nom_formulaire = $formulaire->nom_formulaire;
        $nouveau_champ->type_element = $sous_formulaire['type_element'];
        $nouveau_champ->ordre = $champ_avec_plus_grand_ordre['ordre'] + 1;
        $nouveau_champ->condition_affichage_v_if = '1';
        $nouveau_champ->type_champ = 3;
        $nouveau_champ->id_sous_formulaire = $management_sous_formulaire->modele->id;
        $nouveau_champ->nom_sous_formulaire = $nom_sous_formulaire;
        $nouveau_champ->nom_affichage_sous_formulaire = $sous_formulaire['nom_affichage_sous_formulaire'];

        $nouveau_champ->save();

        Maintenance_management::generer_fichier_migration_formulaire($formulaire->nom_formulaire);

        if($modification === false)
            return response()->json(['retour' => true, 'nouveau_champ' => $nouveau_champ]);

        return response()->json(['retour' => true]);

    }

    public function recupere_sous_formulaire($nom_sous_formulaire){

        return modele('eden_sous_formulaire')->where('nom_sous_formulaire', $nom_sous_formulaire)->first();

    }

    public function enregistrer_valeurs_par_defaut(Request $parametres){

        $parametres = $parametres->all();
        $formulaire = $parametres['formulaire'];

        $valeurs_par_defaut_bdd = Formulaire_valeur_par_defaut::where('nom_formulaire',$formulaire['nom_formulaire'])
            ->get()->keyBy('nom_sql');

        if(!empty($parametres['valeurs_par_defaut'])) {
            foreach ($parametres['valeurs_par_defaut'] as $valeur_par_defaut) {

                if(isset($valeurs_par_defaut_bdd[$valeur_par_defaut['nom_sql']])) {
                    $modele = $valeurs_par_defaut_bdd[$valeur_par_defaut['nom_sql']];
                    unset($valeurs_par_defaut_bdd[$valeur_par_defaut['nom_sql']]);
                }
                else {
                    $modele = new Formulaire_valeur_par_defaut();
                    $modele->nom_formulaire = $formulaire['nom_formulaire'];
                    $modele->nom_sql = $valeur_par_defaut['nom_sql'];
                }

                if(!empty($valeur_par_defaut['valeur']) && is_array($valeur_par_defaut['valeur']))
                    $valeur_par_defaut['valeur'] = json_encode($valeur_par_defaut['valeur']);

                $modele->valeur = $valeur_par_defaut['valeur'] ?? null;

                $modele->save();
            }
        }

        foreach($valeurs_par_defaut_bdd as $valeur_par_defaut_bdd){
            $valeur_par_defaut_bdd->delete();
        }

        Maintenance_management::generer_fichier_migration_formulaire($formulaire['nom_formulaire']);

        return response()->json(['retour' => true]);
    }

    public function generation_iframe(Request $parametres){

        $nom_formulaire = $parametres->nom_formulaire;

        $formulaire = Formulaire::where('nom_formulaire',$nom_formulaire)->first();

        $formulaire->formulaire_web_id = uniqid();

        $formulaire->save();

        return response()->json(['retour' => true,'formulaire' => $formulaire]);
    }

}
