<?php

namespace App\Eden\Controllers\Parametrage;

use App\Eden\Champs\Champ;
use App\Eden\Managements\Services\Listes_formatees_service;
use App\Http\Controllers\Controller;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Table_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champs_liste_formatee;


use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_filtre_enregistre;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Colonne;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Models\Utilisateur;
use App\Eden\Variables;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Champs\Champ_multi_selection;
use App\Eden\Managements\Parametrage\Table_libre_management;
use DB;
use Illuminate\Http\Request;

class Champs_libres_controller extends Controller {

	/**
	 *
	 * Affiche la liste des champs libres pour un type element donné
	 *
	 */
    public function index($type_element) {


		// $champs_libres = Champ_libre::where('type_element', $type_element)->orderBy('ordre')->get();
		$champs_libres = Champ_libre::where('type_element', $type_element);

        if(!editeur())
            $champs_libres->where(function($requete){
                $requete->where('champ_systeme',null)
                    ->orWhere('champ_systeme',0);
            });

        $champs_libres = $champs_libres->get();
		$profils = modele('profil')->get();

		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations';

		// On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations) && \File::isDirectory($chemin_dossier_migrations))
        	$erreur_droit =  traduction('messages.php.attention_droit_ecriture_migrations');
        else
        	$erreur_droit = null;

        // On récupère la table libre pour pouvoir enregistrer les chaines d'affichage

        $table_libre = Table_libre::where('type_element',$type_element)->first();

        foreach($champs_libres as $champ_libre){

            if(empty($champ_libre->champ_liste_libre_liaisons))
                $champ_libre->champ_liste_libre_liaisons = new \StdClass;
            else
                $champ_libre->champ_liste_libre_liaisons = json_decode($champ_libre->champ_liste_libre_liaisons);
        }

		return view('eden::parametrage.champs_libres', [
			'liste_tables' => collect(variable('liste_tables')),
			'type_element' => $type_element,
			'champs_libres' => $champs_libres,
            'table_libre' => $table_libre,
			'types_champs_libres' => collect(variable('types_champs_libres')),
			'liste_formatees' => collect(variable('liste_formatees')),
			'liste_libres' => collect(variable('liste_libres')),
			'profils' => $profils,
			'erreur_droit' => $erreur_droit,
			'erreur_droit_traduction' => null,
			'listes_editables' => collect(variable('liste_formatees_editable')),
		]);
    }

	/**
	*
	* Récupère les données d'un champ libre
	*
	*/
    public function recuperer($type_element, $nom_sql) {

		$champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql)->first();

        $contenu_decode = json_decode($champ_libre->contenu);

		if(is_array($contenu_decode) || is_object($contenu_decode))
			$champ_libre->contenu = $contenu_decode;
        elseif($champ_libre->type == 20 && ($champ_libre->liste_choix == 3 || $champ_libre->liste_choix == 14))
			$champ_libre->contenu = [];

		if ($champ_libre->type == 16) {

			$champ_libre->colonnes_tableau = (json_decode($champ_libre->contenu_tableau)->titres);
			$champ_nom_colonne = "";
		}

        // On retourne les tables libres disponibles pour les champs Type Element + ID Element
        if ($champ_libre->type == 21) {

            $contenu_actuel = $champ_libre->contenu;

            if($contenu_actuel == null)
                $contenu_actuel = array();

            $contenu_actuel_formate = array();

            foreach ($contenu_actuel as $contenu_item){

                $contenu_actuel_formate[] = $contenu_item->type_element;
            }

            $valeurs_possibles_tables_libres = Table_libre::whereNotNull('type_element')->whereNotIn('type_element', $contenu_actuel_formate)->get();

            $champ_libre->types_elements_a_proposer = $valeurs_possibles_tables_libres;
        }

        if($champ_libre->type == 10 && !empty($champ_libre->valeur_defaut))
            $champ_libre->valeur_defaut = json_decode($champ_libre->valeur_defaut, true);

		return json_encode($champ_libre);
    }

	/**
	*
	* Enregistre un champ libre
	*
	*/
    public function enregistrer(Request $formulaire, $type_element, $nom_sql) {

		$modifications = $formulaire->all();

    	if(isset($modifications['contenu']) && is_array($modifications['contenu'])) {

			ksort($modifications['contenu']);

    		$modifications['contenu'] = json_encode($modifications['contenu']);
    	}

        if(isset($modifications['valeur_defaut']) && is_array($modifications['valeur_defaut']))
            $modifications['valeur_defaut'] = json_encode($modifications['valeur_defaut']);

		$retour = Champ_libre_management::enregistre($type_element, $modifications);

		// $champs_libres = Champ_libre::where('type_element', $type_element)->orderBy('ordre')->get();
		$champs_libres = Champ_libre::where('type_element', $type_element)->get();

		Cache_management::partage_oublie_champs_libres($type_element, $nom_sql);
		Cache_management::partage_oublie_traductions();
		Cache_management::vider();

		return json_encode(array('retour' => $retour, 'champs_libres' => $champs_libres));
    }

	/**
	 *
	 * Changement d'etat
	 *
	 */
	public function changement_etat(Request $formulaire, $type_element, $id_cl) {

		if($formulaire->valeur == 1) {

			$valeur_contraire = 0;
		}
		else {

			$valeur_contraire = 1;
		}

		$retour = Champ_libre_management::changement_etat($type_element,$id_cl,$valeur_contraire,$formulaire->parametre);

		$champs_libres = Champ_libre::where('type_element', $type_element)->get();

		Cache_management::partage_oublie_champs_libres($type_element);
		Cache_management::vider();

		return json_encode(array('retour' => $retour, 'champs_libres' => $champs_libres));
	}

	/**
	*
	* Supprime un champ libre
	*
	*/
    public function supprimer($type_element, $nom_sql) {

			$est_dans_formulaire = \DB::table('eden_formulaireslibres_champs')->where('type_element', $type_element)->where('nom_sql', $nom_sql)->first();

			if($est_dans_formulaire == null){

				$champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql)->first();
				$champ_libre->inactif = 1;
				$champ_libre->save();

                if(!empty($champ_libre->index_traduction))
                    service('traduction')->supprime_index_traduction($champ_libre->index_traduction);

				// $champs_libres = Champ_libre::where('type_element', $type_element)->orderBy('ordre')->get();
				$champs_libres = Champ_libre::where('type_element', $type_element)->get();

			    $retour = Table_libre_management::generer_fichier_migration($type_element);

				if (!$retour) {
					return traduction('messages.php.impossibilite_sauvegarde_migrations');
				}

				Cache_management::partage_oublie_champs_libres($type_element, $nom_sql);
				Cache_management::partage_oublie_traductions();
				Cache_management::vider();

				return json_encode(array('champs_libres' => $champs_libres));

			}

			else{

				$champs_libres = Champ_libre::where('type_element', $type_element)->get();

			    $retour = Table_libre_management::generer_fichier_migration($type_element);

				if (!$retour) {
					return traduction('messages.php.impossibilite_sauvegarde_migrations');
				}

				Cache_management::partage_oublie_champs_libres($type_element, $nom_sql);
				Cache_management::partage_oublie_traductions();
				Cache_management::vider();

				return json_encode(array('champs_libres' => $champs_libres ,'erreur' => traduction('messages.php.champ_libre.champ_utilise_dans_formulaire')));

			}
    }

	/**
	 *
	 * Retourne la liste des valeurs possibles pour un champ
	 *
	 */
	public function valeurs_possibles_pour_champ($type_element, $nom_sql) {

		// on va chercher les valeurs et les catégories
		$valeurs = management($type_element)->champ($nom_sql)->liste_valeurs();

		$valeurs_ordre = management($type_element)->champ($nom_sql)->liste_valeurs_ordre();

		$champ_libre = management($type_element)->champ($nom_sql)->modele;

		if(empty($champ_libre->liste_choix)) {

			$categories = Champ_libre_liste::where('id_cl', $champ_libre->id_cl)->orderBy('ordre')->get()->groupBy('categorie');
		}
		else {

			$categories = Champ_libre_liste::where('id_cl', $champ_libre->liste_choix)->orderBy('ordre')->get()->groupBy('categorie');
		}

		if($champ_libre->type == 20 || $champ_libre->type_reference == 20)
			$categories = array();

        if($champ_libre->type_reference == 20 && $champ_libre->liste_choix == 1) {

            if(!super_admin()) {
                $valeurs = modele('utilisateur')->select(\DB::raw("concat(prenom,' ',nom) as nom, id as id"))->zero_ou_null('super_admin')->orderBy('prenom')->get()->pluck('nom', 'id')->toArray();
            }

        }

		return json_encode(array('valeurs' => $valeurs,'valeurs_ordre' => $valeurs_ordre, 'categories' => $categories));
	}

	/**
	 *
	 * Retourne les valeurs d'une liste libre
	 *
	 * @param id_cl INT l'id_cl du champ libre concerné
	 *
	 */
	public function liste_valeurs($id_cl) {

		$champ_libre = Champ_libre::find($id_cl);

        if($champ_libre->liste_choix > 0)
            $id_cl = $champ_libre->liste_choix;

		$valeurs = Champ_libre_liste::where('id_cl', $id_cl)->orderBY('ordre')->get();

		if($valeurs === null)
			return response()->json(array());

		$dernier_ordre = 0;

		foreach($valeurs as $valeur) {

			if(empty($valeur->ordre)) {

				$dernier_ordre++;

				$valeur->ordre = $dernier_ordre;
				$valeur->save();
			}
			else {

				if($valeur->ordre == $dernier_ordre) {

					$dernier_ordre++;

					$valeur->ordre = $dernier_ordre;
					$valeur->save();
				}
				else {

					$dernier_ordre = $valeur->ordre;
				}

			}

			// on va voir cb de fois ils ont été utilisés
			$valeur->utilisations = modele($champ_libre->type_element)->avec_inactifs()->sans_profils()->where($champ_libre->nom_sql, $valeur->id_valeur)->count();
		}

		return json_encode(array('valeurs' => $valeurs));
	}

	/**
	 *
	 *
	 * Retourne les valeurs d'une liste preenregistree
	 *
	 */
	public function liste_preenregistree_valeurs($type_element, $nom_sql,  $liste_choix) {

		$valeurs = collect();

		// On récupère les valeurs initiales de la liste
		$valeurs_possibles = management($type_element)->champ($nom_sql)->valeurs_initiales;

		foreach($valeurs_possibles as  $id_valeur => $valeur) {

			$donnee = [];

			// On vérifie s'il existe une correspondance en bdd
			$champs_liste_formatee = Champs_liste_formatee::where(['id_valeur' => $id_valeur, 'id_liste_choix' => $liste_choix ])->first();

			// Si oui, on affecte les valeurs au tableau
			if($champs_liste_formatee != null) {
				$donnee = $champs_liste_formatee->toArray();
			}
			else {
				// Sinon, on affecte en dur les différentes données
				$donnee = ['id_valeur' => $id_valeur, 'id_liste_choix' => $liste_choix,  'valeur_de_base' => $valeur, 'desactivee' => null, 'couleur' => null, 'couleur_fond' => null, 'couleur_police' => null, 'ordre' => null, 'icone' => null];
			}

			$valeurs->push($donnee);
		}

		return json_encode(array('valeurs' => $valeurs));
	}

	public function enregistrer_liste_preenregistree(Request $request) {

		$nouvelles_valeurs = $request->liste;

		foreach($nouvelles_valeurs as $nouvelle_valeur) {

			// On vérifie s'il existe une correspondance en bdd
			$champs_liste_formatee = Champs_liste_formatee::where(['id_liste_choix' => $nouvelle_valeur['id_liste_choix'], 'id_valeur' => $nouvelle_valeur['id_valeur'] ])->first();

			if($champs_liste_formatee == null)
				$champs_liste_formatee = new Champs_liste_formatee();

			$champs_liste_formatee->id_liste_choix = $nouvelle_valeur['id_liste_choix'];
			$champs_liste_formatee->id_valeur = $nouvelle_valeur['id_valeur'];
			$champs_liste_formatee->couleur_police = $nouvelle_valeur['couleur_police'];
			$champs_liste_formatee->couleur_fond = $nouvelle_valeur['couleur_fond'];
            $champs_liste_formatee->ordre = $nouvelle_valeur['ordre'];
            $champs_liste_formatee->icone = $nouvelle_valeur['icone'];
            $champs_liste_formatee->id_couleur_google = $nouvelle_valeur['id_couleur_google'] ?? null;

			$desactivee = 0;
			if($nouvelle_valeur['desactivee'] == "true" || $nouvelle_valeur['desactivee'] == "1")
				$desactivee = 1;


			$champs_liste_formatee->desactivee = $desactivee;

			$champs_liste_formatee->save();
		}

        $retour = $this->generer_fichier_migration_champ_libre_liste_formatee($champs_liste_formatee->id_liste_choix);

		if($retour === false)
			return response()->json(['erreur' => traduction('messages.php.impossibilite_sauvegarde_migrations')]);

        Cache_management::genere_valeurs_liste_formatees($champs_liste_formatee->id_liste_choix);

		return response()->json(['retour' => true]);
	}

	/**
	 *
	 * Enregistre les modifications sur les valeurs d'une liste libre
	 *
	 */
	public function enregistrer_valeurs_liste_libre(Request $formulaire, $id_cl) {

        $formulaire = $formulaire->all();

        $liste_valeurs = json_decode($formulaire['valeurs'],true);

        $champ_libre = Champ_libre::find($id_cl);

        if($champ_libre->liste_choix > 0)
            $id_cl = $champ_libre->liste_choix;

        $champ_libre_liste_valeurs = Champ_libre_liste::where('id_cl', $id_cl)->get()->keyBy('id_valeur');

        $index_traductions_existants = $champ_libre_liste_valeurs->pluck('index_traduction')->toArray();

        $nouveaux_id_valeur = [];
        $index_traductions_a_creer = [];

        $valeurs_liste_libre_a_enregistrer = [];

        foreach($liste_valeurs as $liste_valeur){

            $nouvelle_valeur = false;

            if(isset($liste_valeur['id_valeur']) && isset($champ_libre_liste_valeurs[$liste_valeur['id_valeur']])) {
                $valeur_liste_libre = $champ_libre_liste_valeurs[$liste_valeur['id_valeur']];
                unset($champ_libre_liste_valeurs[$liste_valeur['id_valeur']]);
            }
            else {
                $valeur_liste_libre = new Champ_libre_liste();
                $valeur_liste_libre->id_cl = $id_cl;
                $nouvelle_valeur = true;
            }

            $valeur_liste_libre->valeur = $liste_valeur['valeur'];
            $valeur_liste_libre->couleur_fond = $liste_valeur['couleur_fond'];
            $valeur_liste_libre->couleur_police = $liste_valeur['couleur_police'];
            $valeur_liste_libre->ordre = $liste_valeur['ordre'];
            $valeur_liste_libre->categorie = $liste_valeur['categorie'];
			
			if(isset($liste_valeur['icone']))
				$valeur_liste_libre->icone = $liste_valeur['icone'];

            if(empty($valeur_liste_libre->index_traduction)){

                $nom_valeur = strtolower(retraite_caracteres_speciaux($valeur_liste_libre->valeur, '_'));

                $informations_traductions = array(
                    'valeurs_listes_libres',
                    $champ_libre->type_element,
                    $champ_libre->nom_sql,
                    $nom_valeur
                );

                $index_traduction = implode('.',$informations_traductions);

                $compteur = 1;

                while(in_array($index_traduction,$index_traductions_existants)){

                    $informations_traductions[3] = $nom_valeur.'_'.$compteur;

                    $compteur++;

                    $index_traduction = implode('.',$informations_traductions);
                }

                $index_traductions_existants[] = $index_traduction;

                $index_traductions_a_creer[] = array(
                    'categorie' => 8,
                    'informations_traductions' => $informations_traductions,
                    'index_a_creer' => array(
                        'nom' => $valeur_liste_libre->valeur,
                        'categorie' => $valeur_liste_libre->categorie,
                    )
                );

                $valeur_liste_libre->index_traduction  = $index_traduction;
            }

            $valeurs_liste_libre_a_enregistrer[] = $valeur_liste_libre;

            if($nouvelle_valeur) {
                $nouveaux_id_valeur[$valeur_liste_libre->ordre] = array(
                    'id_valeur' => $valeur_liste_libre->id_valeur,
                    'index_traduction' => $valeur_liste_libre->index_traduction
                );
            }

        }

        //On supprime les valeurs inexistantes
        foreach($champ_libre_liste_valeurs as $valeur_liste_libre){

            $liaison_existante = Champ_libre::where('type_element',$champ_libre->type_element)
                ->where('champ_liste_libre_liaisons','like','%'.$valeur_liste_libre->index_traduction.'%')
                ->first();

            if($liaison_existante !== null)
                return response()->json(['erreur' => traduction('messages.php.impossibilite_suppression_valeur_liste_libre_liaison',null, [traduction($valeur_liste_libre->index_traduction.'.nom')])]);

            if(!empty($valeur_liste_libre->index_traduction))
                service('traduction')->supprime_index_traduction($valeur_liste_libre->index_traduction);

            $valeur_liste_libre->delete();
        }

        foreach($valeurs_liste_libre_a_enregistrer as $valeur_liste_libre){

            $valeur_liste_libre->save();
        }

        service('traduction')->calcul_multiple_index_traduction($index_traductions_a_creer,env('BASE_TRADUCTION'));

		// on vide le cache
		oublie_cache_eden('listes_libres.'.$id_cl);

        Cache_management::genere_valeurs_liste_libres($id_cl);
		$retour = $this->generer_fichier_migration_champ_libre_liste($id_cl);

		if($retour === false)
			return response()->json(['erreur' => traduction('messages.php.impossibilite_sauvegarde_migrations')]);

		return response()->json(array('nouveaux_id_valeur' => $nouveaux_id_valeur));
	}

    /**
	 *
	 * Enregistre les modifications sur les valeurs d'une liste libre
	 *
	 */
	public function enregistrer_valeurs_liste_libre_liaisons(Request $formulaire) {

        $formulaire = $formulaire->all();

        $formulaire['champ_liste_libre_liaisons'] = json_encode($formulaire['champ_liste_libre_liaisons']);

		Champ_libre_management::enregistre($formulaire['type_element'], $formulaire);

        $id_cl = $formulaire['id_cl'];

        if($formulaire['liste_choix'] > 0)
            $id_cl = $formulaire['liste_choix'];

        Cache_management::genere_valeurs_liste_libres($id_cl);

        $champ_libre_parent = Champ_libre::where('type_element',$formulaire['type_element'])->where('nom_sql',$formulaire['champ_liste_libre_parent'])->first();

        if(!empty($champ_libre_parent)) {

            $id_cl_parent = $champ_libre_parent->id_cl;

            if($champ_libre_parent->liste_choix > 0)
                $id_cl_parent = $champ_libre_parent->liste_choix;

            Cache_management::genere_valeurs_liste_libres($id_cl_parent);

        }

		return response()->json(array('retour' => true));
	}

    /**
     * @param Request $formulaire
     *
     * Supprime les liaisons d'un champ
     *
     */
    public function supprimer_valeurs_liste_libre_liaisons(Request $formulaire){

        $formulaire = $formulaire->all();

        $type_element = $formulaire['type_element'];
        $nom_sql = $formulaire['nom_sql'];

        $champ_libre = Champ_libre::where('type_element', $type_element)->where('nom_sql', $nom_sql)->first();

        $champ_liste_libre_parent = $champ_libre->champ_liste_libre_parent;

        $champ_libre->champ_liste_libre_liaisons = null;
        $champ_libre->champ_liste_libre_parent = null;

        $champ_libre->save();

        $id_cl = $formulaire['id_cl'];

        if($formulaire['liste_choix'] > 0)
            $id_cl = $formulaire['liste_choix'];

        Cache_management::genere_valeurs_liste_libres($id_cl);

        $champ_libre_parent = Champ_libre::where('type_element',$type_element)->where('nom_sql',$champ_liste_libre_parent)->first();

        if(!empty($champ_libre_parent)) {

            $id_cl_parent = $champ_libre_parent->id_cl;

            if($champ_libre_parent->liste_choix > 0)
                $id_cl_parent = $champ_libre_parent->liste_choix;

            Cache_management::genere_valeurs_liste_libres($id_cl_parent);

        }

        $retour = Table_libre_management::generer_fichier_migration($type_element);

		if (!$retour) {
			return 'Le répertoire Migrations n\'est pas  accessible en écriture !';
		}

		return response()->json(array('retour' => $retour));
    }

	/**
	 *
	 * Enregistrement du paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire(Request $formulaire) {


		$nouveau_champ = new Formulaires_champs;

		$champ_avec_plus_grand_ordre = Formulaires_champs::where('formulaire_id',$formulaire->formulaire_id)->orderBy('ordre', 'desc')->first();

		$nouveau_champ->formulaire_id = $formulaire->formulaire_id;
		$nouveau_champ->type_element = $formulaire->type_element;
		$nouveau_champ->nom_sql = $formulaire->nom_sql;
		$nouveau_champ->taille_avant = $formulaire->taille_avant;
		$nouveau_champ->taille_libelle = $formulaire->taille_libelle;
		$nouveau_champ->taille_champ = $formulaire->taille_champ;
		$nouveau_champ->taille_apres = $formulaire->taille_apres;
		$nouveau_champ->ordre = $champ_avec_plus_grand_ordre['ordre'] + 1;

		$nouveau_champ->save();

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('id',$formulaire->formulaire_id)->first();
		$this->generer_fichier_migration_formulaire($le_formulaire['nom_formulaire']);

		return response()->json(array('retour' => true,'nouveau_champ' => $nouveau_champ));
	}

	/**
	 *
	 * Enregistre un champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_enregistre_un_champ(Request $formulaire) {
		$champ_libre = Formulaires_champs::where('formulaire_id', $formulaire->formulaire_id)->where('nom_sql', $formulaire->nom_sql)->first();

		$champ_libre->taille_avant = $formulaire->taille_avant;
		$champ_libre->taille_apres = $formulaire->taille_apres;
		$champ_libre->taille_libelle = $formulaire->taille_libelle;
		$champ_libre->taille_champ = $formulaire->taille_champ;
		$champ_libre->save();

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('id',$formulaire->formulaire_id)->first();
		$this->generer_fichier_migration_formulaire($le_formulaire['nom_formulaire']);

		return response()->json(array('retour' => true));
	}

	/**
	 *
	 * Modifier ordre champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_modifier_taille_libelle(Request $formulaire) {
		$champ_libre = Formulaires_champs::where('formulaire_id', $formulaire->formulaire_id)->where('nom_sql', $formulaire->nom_sql)->first();

		$champ_libre->taille_libelle = $formulaire->taille_libelle;
		$champ_libre->save();

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('id',$formulaire->formulaire_id)->first();
		$this->generer_fichier_migration_formulaire($formulaire['nom_formulaire']);
	}

	/**
	 *
	 * Modifier ordre champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_modifier_taille_champ(Request $formulaire) {
		$champ_libre = Formulaires_champs::where('formulaire_id', $formulaire->formulaire_id)->where('nom_sql', $formulaire->nom_sql)->first();

		$champ_libre->taille_champ = $formulaire->taille_champ;
		$champ_libre->save();

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('id',$formulaire->formulaire_id)->first();
		$this->generer_fichier_migration_formulaire($formulaire['nom_formulaire']);
	}

	/**
	 *
	 * Modifier ordre champ depuis le paramétrage du formulaire
	 *
	 */
	public function parametrage_formulaire_modifier_ordre_champ(Request $formulaire) {
		$champ_libre = Formulaires_champs::where('formulaire_id', $formulaire->formulaire_id)->where('nom_sql', $formulaire->nom_sql)->first();
		$champ_libre->ordre = $formulaire->ordre;
		$champ_libre->save();

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('id',$formulaire->formulaire_id)->first();
		$this->generer_fichier_migration_formulaire($le_formulaire['nom_formulaire']);
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
	* on affiche le parametrage des formulaires
	*
	*
	*/
	public function parametrage_formulaire_index($nom_formulaire) {

		$le_formulaire = Formulaire::where('nom_formulaire',$nom_formulaire)->first();
		$id_formulaire = $le_formulaire['id'];

		if (substr($nom_formulaire, 0, 6) == 'fiche_')
			$nom_formulaire = substr($nom_formulaire, 6);

		else if (substr($nom_formulaire, 0, 15) == 'creation_volee_') {
			$nom_formulaire = substr($nom_formulaire, 15);
		}
        else if (substr($nom_formulaire, 0, 12) == 'chronometre_') {
			$nom_formulaire = substr($nom_formulaire, 12);
		}

		$les_champs = Formulaires_champs::where('formulaire_id', $id_formulaire)->orderBy('ordre')->get();
		if (!empty($les_champs)) {

			$type_element_formulaire = $nom_formulaire;
			foreach ($les_champs as $champ) {

				$nom_sql = $champ['nom_sql'];
				$champ_libre = Champ_libre::where('type_element',$type_element_formulaire)->where('nom_sql',$nom_sql)->first();
				$type = $champ_libre['type'];
				$champ['type'] = $type;
			}
		}
		else{
			$type_element_formulaire = $nom_formulaire;
		}

		$les_champs_libres = Champ_libre::where('type_element',$type_element_formulaire)->get();

		return view('eden::modification_formulaire', ['les_champs' => $les_champs,
														'type_element' => $nom_formulaire,
														'id_formulaire' =>$id_formulaire,
														'champs_libres' => $les_champs_libres,
													]);
	}

	/*
	*
	*
	* on affiche le parametrage des formulaires
	*
	*
	*/
	public function formulaire_index($nom_formulaire) {

		$le_formulaire = Formulaire::where('nom_formulaire',$nom_formulaire)->first();
		$id_formulaire = $le_formulaire['id'];
		$les_champs = Formulaires_champs::where('formulaire_id', $id_formulaire)->orderBy('ordre')->get();

		return view('eden::formulaire', ['les_champs' => $les_champs,
														'nom_formulaire' => $nom_formulaire]);
	}


	/*
	*
	*
	* on supprime le champ du paramétrage de formulaire
	*
	*
	*/
	public function parametrage_formulaire_supprimer(Request $formulaire) {

		$le_champ = Formulaires_champs::where('id', $formulaire->id_champ)->first();

		$ordre_champ_supprime = $le_champ->ordre;
		$id_formulaire = $le_champ->formulaire_id;
		$le_champ->delete();

		$champs_a_modifier_ordre = Formulaires_champs::where('ordre','>',$ordre_champ_supprime)->where('formulaire_id', $id_formulaire)->get();

		foreach ($champs_a_modifier_ordre as $champ) {

			$le_champ_a_modif = Formulaires_champs::where('id', $champ['id'])->first();

			$le_champ_a_modif->ordre = $champ->ordre - 1;
			$le_champ_a_modif->save();
		}

		$les_champs_actualisees = Formulaires_champs::where('formulaire_id', $id_formulaire)->orderBy('ordre')->get();

		// On crée le fichier de migration spécifique
		$le_formulaire = Formulaire::where('id',$id_formulaire)->first();
		$this->generer_fichier_migration_formulaire($le_formulaire['nom_formulaire']);

		return json_encode(array("retour" => true,'les_champs' => $les_champs_actualisees));
	}



	/*
	*
	*
	* Affichage des formulaires libres
	*
	*
	*/
	public function listes_formulaires() {

		$les_formulaires = Formulaire::get();

		$les_type_pour_nom_formulaire = Table_libre::get('type_element');

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

		return view('eden::listes.liste_formulaire', ['les_formulaires' => $les_formulaires,
													  'array_nom_formulaire' => $array_nom_formulaire
													]);
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
		$le_formulaire->delete();

		// On supprimer les champs liés a ce formulaire
		$les_champs = Formulaires_champs::where('formulaire_id',$id_formulaire)->get();
		foreach ($les_champs as $champ) {

			$champ->delete();
		}

		$les_formulaires = Formulaire::get();

		return back();
	}

	/*
	*
	*
	* Ajouter un formulaire libre
	*
	*
	*/
	public function listes_formulaires_ajouter(Request $formulaire) {

		$formulaire = $formulaire->all();

		$nom_formulaire = $formulaire['nom_formulaire'];
		$vuejs_data = $formulaire['vuejs_data'];
		$vuejs_methods = $formulaire['vuejs_methods'];

		$nouveau_formulaire = new Formulaire;

		$nouveau_formulaire->nom_formulaire = $nom_formulaire;
		$nouveau_formulaire->vuejs_data = $vuejs_data;
		$nouveau_formulaire->vuejs_methods = $vuejs_methods;
		$nouveau_formulaire->save();

		if (session()->has('cache.formulaire'))
			session()->forget('cache.formulaire');

		// On crée le fichier de migration spécifique
		$this->generer_fichier_migration_formulaire($formulaire['nom_formulaire']);

		return back();
	}

	/*
	*
	*
	* Modifier un formulaire libre
	*
	*
	*/
	public function listes_formulaires_modifier(Request $formulaire) {

		$formulaire = $formulaire->all();

		$nom_formulaire = $formulaire['nom_formulaire'];
		$vuejs_data = $formulaire['vuejs_data'];
		$vuejs_methods = $formulaire['vuejs_methods'];
		$id_formulaire = $formulaire['id_du_formulaire'];

		$nouveau_formulaire = Formulaire::where('id',$id_formulaire)->first();

		$nouveau_formulaire->nom_formulaire = $nom_formulaire;
		$nouveau_formulaire->vuejs_data = $vuejs_data;
		$nouveau_formulaire->vuejs_methods = $vuejs_methods;
		$nouveau_formulaire->save();

		// On crée le fichier de migration spécifique
		$this->generer_fichier_migration_formulaire($formulaire['nom_formulaire']);

		return back();
	}

	/**
	 *
	 * Génère le nouveau fichier de migration pour les formulaires libres
	 *
	 */
	public static function generer_fichier_migration_formulaire($type_element) {

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours'))
			return true;

		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Formulaires_libres';

		if(!\File::isDirectory($chemin_dossier_migrations))
        	\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
        	return false;

		$le_formulaire = Formulaire::where('nom_formulaire',$type_element)->first();
		$les_champs = Formulaires_champs::where('formulaire_id',$le_formulaire['id'])->get();

		// On crée a le contenu du nouveau fichier de migration
		$le_formulaire = $le_formulaire->toArray();
		$les_champs = $les_champs->toArray();


		$texte =
		'<?php
			return [
				\'vue_js\' => [
						\'vuejs_data\' => "'.$le_formulaire['vuejs_data'].'",
						\'vuejs_methods\' => "'.$le_formulaire['vuejs_methods'].'",
						';
					$texte = $texte.'],
				\'champs_libres\' => [
					';
					foreach ($les_champs as $liste_valeur) {

						$texte = $texte.'[
								';
							foreach ($liste_valeur as $clef => $valeur) {

								$texte = $texte.'\''.$clef.'\' => "'.$valeur.'",
								';
							}
						$texte = $texte.'
						],
						';
					}
			$texte = $texte.'],
					];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Formulaires_libres/'.$type_element.'.php';

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
	 * Génère le nouveau fichier de migration pour les champs libres listes
	 *
	 */
	public static function generer_fichier_migration_champ_libre_liste($id_cl,$forcer_generation = false) {

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours') && $forcer_generation === false)
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
						$texte = $texte.'"'.$le_champ_liste['index_traduction'].'" => [
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
	 * Génère le nouveau fichier de migration pour les champs libres listes formatées
	 *
	 */
	public static function generer_fichier_migration_champ_libre_liste_formatee($id_liste_choix,$forcer_creation = false) {

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours') && $forcer_creation === false)
			return true;

		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Champs_libres_listes_formatees';

		if(!\File::isDirectory($chemin_dossier_migrations))
        	\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
        	return false;

        $valeurs_liste_formatee = Champs_liste_formatee::where('id_liste_choix',$id_liste_choix)->get()->toArray();

        // On crée a le contenu du nouveau fichier de migration
		$texte = "<?php\n";

        $texte .= "\treturn [\n";

        $texte .= "\t\t'id_liste_choix' => '".$id_liste_choix."',\n";

        $texte .= "\t\t'champs' => [\n";

        foreach ($valeurs_liste_formatee as $valeur_liste) {

            $texte .= "\t\t\t'".$valeur_liste['id_valeur']."' => [\n";

            foreach ($valeur_liste as $clef => $valeur) {

                if ($clef == 'id' || $clef == 'id_liste_choix' || $clef == 'id_valeur')
                    continue;

                $texte .= "\t\t\t\t'".$clef."' => \"".$valeur."\",\n";
            }

            $texte .= "\t\t\t],\n";

        }

        $texte .= "\t\t],\n";

		$texte .= "\t];";

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Champs_libres_listes_formatees/liste_formatee_'.$id_liste_choix.'.php';

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
	 * Recupération des champs libres contenant des listes et affichage
	 *
	 */

	public function modification_des_listes_libres(){
		
		$types_elements_vues = Table_libre::where('vue_sql', 1)->get()->pluck('type_element')->toArray();
	

		$champs_libres = Champ_libre::where(function ($query) {
			$query->where('type', 1)
				->orWhere(function ($q) {
					$q->where('type', 10)
						->where('type_reference', 1);
				});
		})->where('inactif', 0)
		->whereNotIn('type_element', $types_elements_vues)
		->orderBy('type_element')
		->get();
		
        foreach($champs_libres as $champ_libre) {
            
            if (empty($champ_libre->champ_liste_libre_liaisons))
                $champ_libre->champ_liste_libre_liaisons = new \StdClass;
            else
                $champ_libre->champ_liste_libre_liaisons = json_decode($champ_libre->champ_liste_libre_liaisons);
        }


        $profils = modele('profil')->get();

        // On vérifie que le dossier migration existe bien en spécifique
        $chemin_dossier_migrations = app_path().'/Migrations';

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations) && \File::isDirectory($chemin_dossier_migrations))
            $erreur_droit =  traduction('messages.php.attention_droit_ecriture_migrations');
        else
            $erreur_droit = null;

        return view('eden::parametrage.modification_champs_libres', [
            'liste_tables' => collect(variable('liste_tables')),
            'champs_libres' => $champs_libres,
            'types_champs_libres' => collect(variable('types_champs_libres')),
            'liste_formatees' => collect(variable('liste_formatees')),
            'liste_libres' => collect(variable('liste_libres')),
            'profils' => $profils,
            'erreur_droit' => $erreur_droit,
        ]);

    }

    /**
	 *
	 * Recupération d'un tableau'
	 *
	 */

	public function recuperer_tableau($type_element,$element_id,$colonne){

	    $donnees = [];

        $tableau = modele('tableaux_libres')->where('type_element',$type_element)->where('element_id',$element_id)->where('colonne',$colonne)->first();

        if($tableau !== null)
            $donnees = json_decode($tableau->contenu);

        $champ = Champ_libre::where('type_element',$type_element)->where('nom_sql',$colonne)->first();

        $colonnes = json_decode($champ->contenu_tableau)->titres;

        return json_encode(array("colonnes" => $colonnes,"donnees_tableau" => $donnees));
    }

     /**
      *
      * Permet de récupérer les champs libres d'un type element
      *
      */
     public function recuperer_champs_libres_type_element($type_element,$nom_sql = null){

         if($nom_sql != null)
             $donnees = champ_libre_modele($type_element,$nom_sql);
         else
             $donnees = champs_libres($type_element);

         return json_encode($donnees);
     }

     /**
      *
      * Permet de récupérer la valeur d'une fonction d'un champ
      *
      */
     public function methode_champ($type_element,$nom_sql,$methode)
     {
         $informations_champs = request()->all();

         $champ = management($type_element)->champ($nom_sql);

         if(isset($informations_champs['attributs'])) {
             foreach ($informations_champs['attributs'] as $nom => $valeur) {

                if($valeur == null)
                    unset($champ->attributs[$nom]);
                else
                    $champ->attributs[$nom] = $valeur;
             }
         }

		 if(isset($informations_champs['valeur']))
			$champ->value($informations_champs['valeur']);
		
         $resultat = $champ->$methode();

         return json_encode($resultat);
     }

     /**
      *
      * Permet de récupérer les tables libres liés à un type element via les champs libres
      *
      */
     public function recuperation_tables_jointes($type_element){

        $tables_jointes = Table_libre::select('eden_tableslibres.*','eden_champslibres.nom_sql as nom_sql_liaison')
            ->join('eden_champslibres','eden_tableslibres.type_element','eden_champslibres.type_element')
            ->where('type_element_ajax',$type_element)
            ->orderBy('nom_table')
            ->get();

		$tables_jointes = $tables_jointes->push(
			Table_libre::selectRaw("eden_tableslibres.*,'element_id' as nom_sql_liaison") 
				->where('type_element','element_piece_jointe')
				->first()
		);
		
        return response()->json($tables_jointes);
     }

    /**
     *
     * Permet de récupérer les champs libres pour la modification en masse
     *
     */
    public function modification_en_masse($type_element){

        $type_element_modification = service('vue_sql')->recupere_type_element($type_element);

        $champs_modif_en_masse = management($type_element_modification)->champs_modifications_en_masse();

        return response()->json(array(
            'champs_modifications' => $champs_modif_en_masse
        ));
    }

     public function recuperer_types_elements(){

         return response()->json(Table_libre::whereNotNull('type_element')->get());
     }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de créer une table libre à partir d'une table pivot
     *
     */
     public function creation_table_libre_pivot(){

        $table_pivot = request()->table_pivot;

        Champ_libre_management::creation_table_libre_pivot($table_pivot);

        return response()->json(true);
     }

     /**
      *
      * Fonction qui renvoie les variables disponibles lors du filtrage des dates
      *
      */
     public function variables_champ_date(){

         return response()->json(Variables::variables_champ_date());
     }

    /**
     * @return void
     *
     * Permet de récupérer tous les champs libres nécessaires pour les filtrages
     *
     */
     public function filtrage($type_element){

         $champs_libres = Champ_libre::where(function($where){
             $where->where('champ_systeme',0)->orWhereNull('champ_systeme');
         })->get()->groupBy('type_element');

         return response()->json(Champ_libre_management::filtrage($type_element,$champs_libres));
     }

     /*
      *
      * Récupère les champs compatibles pour la conversion
      *
      */
     public function conversion(Request $requete){

         $donnees = $requete->all();

         if($donnees['champ_depart'] !== 'id') {

             $champ_depart = Champ_libre::where('nom_sql', $donnees['champ_depart'])->where('type_element', $donnees['type_element_depart'])->first();
             $types_champs_compatibles = Variables::$champs_compatibles[$champ_depart->type];
         }else {

             $champ_depart = new Champ_libre();
             $champ_depart->type_element = $donnees['type_element_depart'];
             $champ_depart->nom_sql = 'id';
             $champ_depart->nom = 'Id';

             $types_champs_compatibles = [
                 ['type' => 42, 'champ_compatible' => 'type_element_ajax', 'valeur_champ_compatible' => $donnees['type_element_depart']],
                 ['type' => 0],
                 ['type' => 2],
             ];
         }

         $champs_libres_element = champs_libres($donnees['type_element_arrivee'])->where('champ_systeme','!=',1);

         // On ne prend que les champs avec un type compatible
         $champs_libres_element = $champs_libres_element->filter(function($champ) use ($types_champs_compatibles, $champ_depart){

             foreach($types_champs_compatibles as $type_champ_compatible){

                 if($champ->type == $type_champ_compatible['type'] && !isset($type_champ_compatible['champ_compatible']))
                     return true;
                 else if($champ->type == $type_champ_compatible['type'] &&
                     isset($type_champ_compatible['champ_compatible'], $type_champ_compatible['valeur_champ_compatible']) &&
                     $champ->{$type_champ_compatible['champ_compatible']} == $type_champ_compatible['valeur_champ_compatible'])
                     return true;
                 else if($champ->type == $type_champ_compatible['type'] && isset($type_champ_compatible['champ_compatible']) &&
                     ($champ->{$type_champ_compatible['champ_compatible']} == $champ_depart->{$type_champ_compatible['champ_compatible']}
                 || ($champ->type == 1 && $champ->{$type_champ_compatible['champ_compatible']} == $champ_depart->id_cl)))
                     return true;
             }

             return false;
         });

         $champs_libres[] = array(
             'type_element' => $donnees['type_element_arrivee'],
             'index_traduction' => 'tables_libres.'.$donnees['type_element_arrivee'].'.nom_table',
             'champs_libres' => array_values($champs_libres_element->toArray()),
         );

         $champs_libres_pour_liaison = Champ_libre::where('type_element_ajax', $donnees['type_element_arrivee'])
             ->whereNotIn('type_element', array_merge(Variables::$documents_gescom,Variables::$documents_gescom_lignes))
             ->where('type', 42)
             ->where(function($requete){
                 $requete->where('champ_systeme',null)
                     ->orWhere('champ_systeme',0);
             })
             ->where(function($requete){
                 $requete->whereNull('inactif')
                     ->orWhere('inactif',0);
             })
             ->orderBy('nom')
             ->get();

        // On ne prend que les champs avec un type compatible
         $champs_libres_liaisons = Champ_libre::whereIn('type_element', $champs_libres_pour_liaison->pluck('type_element'))
             ->where(function($requete){
                 $requete->where('champ_systeme',null)
                     ->orWhere('champ_systeme',0);
             })
             ->where(function($requete) use ($types_champs_compatibles, $champ_depart){

                 foreach($types_champs_compatibles as $type_champ_compatible){

                     if(!isset($type_champ_compatible['champ_compatible']))
                         $requete->orWhere('type', $type_champ_compatible['type']);
                     else if(!isset($type_champ_compatible['valeur_champ_compatible']))
                         $requete->orWhere(function($orWhere) use($type_champ_compatible, $champ_depart){
                             $orWhere->where('type', $type_champ_compatible['type'])
                                 ->where($type_champ_compatible['champ_compatible'], $champ_depart->{$type_champ_compatible['champ_compatible']});
                         });
                     else
                         $requete->orWhere(function($orWhere) use($type_champ_compatible, $champ_depart){
                             $orWhere->where('type', $type_champ_compatible['type'])
                                 ->where($type_champ_compatible['champ_compatible'], $type_champ_compatible['valeur_champ_compatible']);
                         });
                 }
             })
             ->where(function($requete){
                 $requete->whereNull('inactif')
                     ->orWhere('inactif',0);
             })
             ->orderBy('nom')
             ->get()
             ->groupBy('type_element')
             ->toArray();

        foreach ($champs_libres_pour_liaison as $champ_libre_pour_liaison) {

            if (!isset($champs_libres_liaisons[$champ_libre_pour_liaison->type_element]))
                continue;

            $champs_libres_compatibles = $champs_libres_liaisons[$champ_libre_pour_liaison->type_element];

            foreach ($champs_libres_compatibles as $type_element => $champ_libre_compatible) {

                if(gettype($type_element) !== 'string')
                    $champ_libre_compatible['index_traduction_type_element'] = 'tables_libres.' . $champ_libre_compatible['type_element'] . '.nom_table';
                else {

                    foreach ($champ_libre_compatible as $champ_compatible) {

                        $champ_compatible['index_traduction_type_element'] = 'tables_libres.' . $type_element . '.nom_table';
                    }
                }
            }

            $champs_libres[] = array(
                'type_element' => $champ_libre_pour_liaison->type_element,
                'index_traduction' => 'tables_libres.' . $champ_libre_pour_liaison->type_element . '.nom_table',
                'champs_libres' => $champs_libres_compatibles,
                'champ_liaison' => $champ_libre_pour_liaison->nom_sql,
            );
        }

         return response()->json($champs_libres);
     }

	 public function valeurs_multiples(Request $requete){

		$champs = collect($requete->all()['donnees'] ?? [])
			->filter(function($champ){
				return !empty($champ['type_element']) && !empty($champ['nom_sql']);
			})
			->map(function($champ){
				return $champ['type_element'].'_'.$champ['nom_sql'];
			})->all();

		if(empty($champs))
			return response()->json([]);

		$donnees = Champ_libre::
			select('eden_champslibres.*',DB::raw('CONCAT(type_element,"_",nom_sql) AS champ_unique'))
			->whereIn(DB::raw('CONCAT(type_element,"_",nom_sql)'), array_values(array_unique($champs)))
			->get()->keyBy('champ_unique');

        return response()->json($donnees);
	 }
}
